<?php

namespace Drupal\opigno_group_manager\Controller;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\media\Entity\Media;
use Drupal\opigno_course\Plugin\OpignoGroupManagerContentType\ContentTypeCourse;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedLink;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\opigno_learning_path\LearningPathValidator;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_moxtra\Entity\Workspace;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for all the actions of the Opigno group manager app.
 */
class OpignoGroupManagerController extends ControllerBase {

  private $content_types_manager;

  /**
   * {@inheritdoc}
   */
  public function __construct(OpignoGroupContentTypesManager $content_types_manager) {
    $this->content_types_manager = $content_types_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('opigno_group_manager.content_types.manager')
    );
  }

  /**
   * Root page for angular app.
   */
  public function index(Group $group, Request $request) {
    // Check if user has uncompleted steps.
    LearningPathValidator::stepsValidate($group);

    if ($group instanceof GroupInterface) {
      $current_step = opigno_learning_path_get_current_step();
      $next_step = ($current_step < 5) ? $current_step + 1 : NULL;
      $link_text = !$next_step ? $this->t('Publish') : $this->t('Next');
      $next_link = Link::createFromRoute($link_text, 'opigno_learning_path.content_steps', [
        'group' => $group->id(),
        'current' => ($current_step) ? $current_step : 0,
      ], [
        'attributes' => [
          'class' => [
            'btn',
            'btn-success',
            'color-white',
          ],
        ],
      ])->toRenderable();
    }

    $tempstore = \Drupal::service('user.private_tempstore')->get('opigno_group_manager');

    return [
      '#theme' => 'opigno_group_manager',
      '#attached' => ['library' => ['opigno_group_manager/manage_app']],
      '#base_path' => $request->getBasePath(),
      '#base_href' => $request->getPathInfo(),
      '#group_id' => $group->id(),
      '#next_link' => isset($next_link) ? render($next_link) : NULL,
      '#user_has_info_card' => $tempstore->get('hide_info_card') ? FALSE : TRUE,
    ];
  }

  /**
   * Returns next link.
   */
  public function getNextLink(Group $group) {
    $next_link = NULL;

    if ($group instanceof GroupInterface) {
      $current_step = opigno_learning_path_get_current_step();
      $next_step = ($current_step < 5) ? $current_step + 1 : NULL;
      $link_text = !$next_step ? $this->t('Publish') : $this->t('Next');
      $next_link = Link::createFromRoute($link_text, 'opigno_learning_path.content_steps', [
        'group' => $group->id(),
        'current' => ($current_step) ? $current_step : 0,
      ], [
        'attributes' => [
          'class' => [
            'btn',
            'btn-success',
            'color-white',
          ],
        ],
      ])->toRenderable();
    }

    return $next_link;
  }

  /**
   * Method called when the LP manager needs a create or edit form.
   */
  public function getItemForm(Group $group, $type = NULL, $item = 0) {
    // Get the good form from the corresponding content type.
    $content_type = $this->content_types_manager->createInstance($type);
    $form = $content_type->getFormObject($item);

    if ($type === 'ContentTypeModule'
      && $item !== NULL
      && ($module = OpignoModule::load($item)) !== NULL) {
      /** @var \Drupal\opigno_module\Entity\OpignoModuleInterface $module */
      if (!$module->access('update')) {
        throw new AccessDeniedHttpException();
      }
    }
    elseif ($type === 'ContentTypeCourse'
      && $item !== NULL
      && ($group = Group::load($item)) !== NULL) {
      /** @var \Drupal\group\Entity\GroupInterface $group */
      if (!$group->access('update')) {
        throw new AccessDeniedHttpException();
      }
    }

    // Adds some information used
    // in the method opigno_learning_path_form_alter().
    $form_build = \Drupal::formBuilder()->getForm($form, [
      'opigno_group_info' => [
        'group_id' => ($group) ? $group->id() : NULL,
        'opigno_group_content_type' => $type,
      ],
    ]);

    // Returns the form.
    return $form_build;
  }

  /**
   * Form ajax callback.
   */
  public static function ajaxFormEntityCallback(&$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If errors, returns the form with errors and messages.
    if ($form_state->hasAnyErrors()) {
      // Add class for displaying form errors in iframe.
      $form['#attributes']['class'][] = 'lp-content-item-errors';
      return $form;
    }

    $entity = $form_state->getBuildInfo()['callback_object']->getEntity();
    // Load image.
    if ($entity->hasField('field_course_media_image')) {
      $media = $entity->get('field_course_media_image')->entity;
      $file = isset($media)
        ? File::load($media->get('field_media_image')->target_id)
        : NULL;
    };

    $item = [];
    $item['cid'] = $entity->id();
    $item['contentType'] = \Drupal::routeMatch()->getParameter('type');
    $item['entityId'] = $entity->id();
    $item['entityBundle'] = \Drupal::routeMatch()->getParameter('type');
    $item['title'] = $entity->label();
    $item['imageUrl'] = (isset($file) && $file) ? file_create_url($file->getFileUri()) : '';
    $item['in_skills_system'] = FALSE;
    $item['isMandatory'] = FALSE;

    if ($item['contentType'] == 'ContentTypeModule') {
      $item['in_skills_system'] = $entity->getSkillsActive();
      $group = \Drupal::routeMatch()->getParameter('group');

      $managed_contents = OpignoGroupManagedContent::loadByProperties([
        'group_content_type_id' => 'ContentTypeModule',
        'group_id' => $group->id(),
        'entity_id' => $entity->id(),
      ]);

      if (!empty($managed_contents)) {
        $content = reset($managed_contents);
        $item['isMandatory'] = $content->isMandatory();
      }
    }

    $response->addCommand(
      new SettingsCommand([
        'formValues' => $item,
        'messages' =>  \Drupal::messenger()->all(),
      ], TRUE)
    );

    \Drupal::messenger()->deleteAll();

    return $response;
  }

  /**
   * Submit handler added by opigno_learning_path_form_alter().
   *
   * @see opigno_learning_path_form_alter()
   */
  public static function ajaxFormEntityFormSubmit($form, FormState &$form_state) {
    // Gets back the content type and learning path id.
    $build_info = $form_state->getBuildInfo();
    foreach ($build_info['args'][0] as $arg_key => $arg_value) {
      if ($arg_key === 'opigno_group_info') {
        $lp_id = $arg_value['group_id'];
        $lp_content_type_id = $arg_value['opigno_group_content_type'];
        break;
      }
    }

    // If one information missing, return an error.
    if (!isset($lp_id) || !isset($lp_content_type_id)) {
      // TODO: Add an error message here.
      return;
    }

    // Get the newly or edited entity.
    $entity = $build_info['callback_object']->getEntity();
    $moduleHandler = \Drupal::service('module_handler');

    if ($moduleHandler->moduleExists('opigno_skills_system') && $lp_content_type_id == 'ContentTypeModule') {
      $managed_contents = OpignoGroupManagedContent::loadByProperties([
        'group_content_type_id' => 'ContentTypeModule',
        'group_id' => $lp_id,
        'entity_id' => $entity->id(),
      ]);

      if (!empty($managed_contents)) {
        $content = reset($managed_contents);
        $content->setSkillSystem($entity->getSkillsActive());

        if ($entity->getSkillsActive()) {
          $content->setMandatory(0);
        }

        $content->save();
      }
    }

    // Clear user input.
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';
    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys) && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }

    // Store new entity for display in the AJAX callback.
    $input['entity'] = $entity;
    $form_state->setUserInput($input);

    // Rebuild the form state values.
    $form_state->setRebuild();
    $form_state->setStorage([]);
  }

  /**
   * Method called when a step is set as mandatory or not.
   */
  public function updateItemMandatory(Group $group, Request $request) {
    // Get the data and ensure that all data are okay.
    $datas = json_decode($request->getContent());
    if (empty($datas->cid) || isset($datas->isMandatory) === FALSE) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }
    $cid = $datas->cid;
    $mandatory = $datas->isMandatory;

    // Load the good content, update it and save it.
    $content = OpignoGroupManagedContent::load($cid);
    $content->setMandatory($mandatory);
    $content->save();

    // Finally, return the JSON response.
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * Method called when an item success score is set or not.
   */
  public function updateItemMinScore(Group $group, Request $request) {
    // Ensure all data are okay.
    $datas = json_decode($request->getContent());
    if (empty($datas->cid)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }
    $cid = $datas->cid;
    $success_score_min = empty($datas->successScoreMin) ? 0 : $datas->successScoreMin;

    // Update the item.
    $content = OpignoGroupManagedContent::load($cid);
    $content->setSuccessScoreMin($success_score_min);
    $content->save();

    // Return the JSON response.
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * This method is called on learning path load.
   *
   * It returns all the steps and their links in JSON format.
   */
  public function getItems(Group $group) {
    $courses_storage = \Drupal::entityTypeManager()->getStorage('group');

    // Init the response and get all the contents from this learning path.
    $entities = [];
    $managed_contents = OpignoGroupManagedContent::loadByProperties(['group_id' => $group->id()]);
    // TODO: Maybe extend the class LPManagedContent
    // with LearningPathContent
    // (and use Parent::__constructor() to fill the params).
    // Convert all the LPManagedContent to LearningPathContent
    // and convert it to an array.
    foreach ($managed_contents as $managed_content) {
      // Need the content type object to get the LearningPathContent object.
      $content_type_id = $managed_content->getGroupContentTypeId();
      $content_type = $this->content_types_manager->createInstance($content_type_id);
      $lp_content = $content_type->getContent($managed_content->getEntityId());
      if ($lp_content === FALSE) {
        continue;
      }
      // Create the array that is ready for JSON.
      $manager_array = $lp_content->toManagerArray($managed_content);
      if ($lp_content->getGroupContentTypeId() == 'ContentTypeCourse') {
        $course = $courses_storage->load($lp_content->getEntityId());
        $group_content = $course->getContent('opigno_module_group');
        $manager_array['modules_count'] = count($group_content);
      }
      $entities[] = $manager_array;
    }

    // Return all the contents in JSON format.
    return new JsonResponse($entities, Response::HTTP_OK);
  }

  /**
   * This function is called on learning path load.
   *
   * It return the coordinates of every steps.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPositions(Group $group) {
    // Get the positions from DB.
    $entityPositions = [];
    try {
      $managed_contents = OpignoGroupManagedContent::loadByProperties(['group_id' => $group->id()]);
    }
    catch (InvalidPluginDefinitionException $e) {
      return new JsonResponse(NULL, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Then, create a big array with the positions and return OK.
    foreach ($managed_contents as $managed_content) {
      $entityPositions[] = [
        'cid' => $managed_content->id(),
        'col' => $managed_content->getCoordinateX() ? $managed_content->getCoordinateX() : 1,
        'row' => $managed_content->getCoordinateY() ? $managed_content->getCoordinateY() : 1,
      ];
    }
    return new JsonResponse($entityPositions, Response::HTTP_OK);
  }

  /**
   * Called after each update on learning path structure (add/remove/move node).
   *
   * It update the position of a content.
   */
  public function setPositions(Request $request) {
    // Get the data and check if it's correct.
    $datas = json_decode($request->getContent());
    if (empty($datas->mainItemPositions)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }
    $content_positions = $datas->mainItemPositions;

    // Then, for each content, update the position in DB and return OK.
    foreach ($content_positions as $content_position) {
      $content = OpignoGroupManagedContent::load($content_position->cid);
      $content->setCoordinateX($content_position->col);
      $content->setCoordinateY($content_position->row);
      $content->save();
    }
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * This method adds an item (content) in the learning path.
   */
  public function addItem(Group $group, Request $request) {
    $moduleHandler = \Drupal::service('module_handler');

    // First, check if all parameters are here.
    $datas = json_decode($request->getContent());
    if (
      empty($datas->entityId)
      || empty($datas->contentType)
    ) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }

    // Get the params.
    $entityId = $datas->entityId;
    $contentType = $datas->contentType;
    $parentCid = empty($datas->parentCid) ? NULL : $datas->parentCid;

    // Create the added item as an LP content.
    $new_content = OpignoGroupManagedContent::createWithValues(
      $group->id(),
      $contentType,
      $entityId
    );

    // Check if the content type is a module.
    // If yes then check if the module in the skills system.
    if ($moduleHandler->moduleExists('opigno_skills_system') && $contentType === 'ContentTypeModule') {
      $module = \Drupal::entityTypeManager()->getStorage('opigno_module')->load($entityId);
      $new_content->set('in_skills_system', $module->getSkillsActive());
    }

    $new_content->save();

    // Then, create the links to the parent content.
    if (!empty($parentCid)) {
      OpignoGroupManagedLink::createWithValues(
        $group->id(),
        $parentCid,
        $new_content->id()
      )->save();
    }
    // Add created entity as Group content.
    $plugin_definition = $this->content_types_manager->getDefinition($datas->contentType);
    if (!empty($plugin_definition['group_content_plugin_id'])) {
      // Load Course (Group) entity and save as content using specific plugin.
      $added_entity = \Drupal::entityTypeManager()
        ->getStorage($plugin_definition['entity_type'])
        ->load($datas->entityId);
      $group->addContent($added_entity, $plugin_definition['group_content_plugin_id']);
    }

    return new JsonResponse(['cid' => $new_content->id()], Response::HTTP_OK);
  }

  /**
   * Remove item from learning path.
   */
  public function removeItem(Request $request) {
    // Get and check the params of the ajax request.
    $datas = json_decode($request->getContent());
    if (empty($datas->cid)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }
    $cid = $datas->cid;
    // Load Learning path content entity.
    $lp_content_entity = OpignoGroupManagedContent::load($cid);
    $learning_path_plugin = $lp_content_entity->getGroupContentType();
    $plugin_definition = $this->content_types_manager->getDefinition($learning_path_plugin->getPluginId());
    if (!empty($plugin_definition['group_content_plugin_id'])) {
      $lp_group = \Drupal::entityTypeManager()
        ->getStorage('group')
        ->load($lp_content_entity->get('group_id')->entity->id());
      // Remove Learning path course if it's exist.
      $group_contents = $lp_group->getContentByEntityId($plugin_definition['group_content_plugin_id'], $lp_content_entity->get('entity_id')->value);
      if (!empty($group_contents)) {
        // Probably, there can be a few same items. Get only last.
        $group_content = end($group_contents);
        $group_content->delete();
      }
    }

    // Then delete the content and return OK.
    $lp_content_entity->delete();
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * Add a new link in the Learning Path.
   */
  public function addLink(Group $group, Request $request) {
    // First, check if all params are okay.
    $datas = json_decode($request->getContent());
    if (
      empty($datas->parentCid)
      || empty($datas->childCid)
    ) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }

    // Get the request params.
    $parentCid = $datas->parentCid;
    $childCid = $datas->childCid;

    // Create the new link and return OK.
    $new_link = OpignoGroupManagedLink::createWithValues(
      $group->id(),
      $parentCid,
      $childCid,
      0
    );
    $new_link->save();
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * Update a link minimum score to go to next step.
   */
  public function updateLink(Group $group, Request $request) {
    // First, check the params.
    $datas = json_decode($request->getContent());
    if (empty($datas->parentCid) || empty($datas->childCid)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }

    // Then get the params.
    $parentCid = $datas->parentCid;
    $childCid = $datas->childCid;
    $requiredScore = !empty($datas->requiredScore) ? $datas->requiredScore : 0;
    $requiredActivities = !empty($datas->requiredActivities) ? serialize($datas->requiredActivities) : NULL;

    // Get the links that use the same LP ID,
    // parent CID and child CID. Should be only one.
    try {
      $links = OpignoGroupManagedLink::loadByProperties([
        'group_id' => $group->id(),
        'parent_content_id' => $parentCid,
        'child_content_id' => $childCid,
      ]);
    }
    catch (InvalidPluginDefinitionException $e) {
      return new JsonResponse(NULL, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // If no link returned, create it and return OK.
    if (empty($links)) {
      $new_link = OpignoGroupManagedLink::createWithValues(
        $group->id(),
        $parentCid,
        $childCid,
        $requiredScore,
        $requiredActivities
      );
      $new_link->save();

      return new JsonResponse(NULL, Response::HTTP_OK);
    }

    // If the link is found, update it and return OK.
    foreach ($links as $link) {
      $link->setRequiredScore($requiredScore);
      $link->setRequiredActivities($requiredActivities);
      $link->save();
    }
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * Removes a link.
   */
  public function removeLink(Group $group, Request $request) {
    // First, check that the params are okay.
    $datas = json_decode($request->getContent());
    if (
      empty($datas->parentCid)
      || empty($datas->childCid)
    ) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }

    // Get the params.
    $parentCid = $datas->parentCid;
    $childCid = $datas->childCid;

    // Get the links. Should be only one.
    try {
      $links = OpignoGroupManagedLink::loadByProperties([
        'group_id' => $group->id(),
        'parent_content_id' => $parentCid,
        'child_content_id' => $childCid,
      ]);
    }
    catch (InvalidPluginDefinitionException $e) {
      return new JsonResponse(NULL, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Delete the links and return OK.
    foreach ($links as $link) {
      $link->delete();
    }
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * Return contents availables when you want add content to learning path.
   *
   * @param int $mainItem
   *   The main level content ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getAvailableItems($mainItem = NULL) {
    // Init the return array and get all the content types available.
    $available_contents = [];
    $content_types_definitions = $this->content_types_manager->getDefinitions();

    // For each content type,
    // get the available contents from them and store it in the return array.
    foreach ($content_types_definitions as $content_type_id => $content_type_definition) {
      // Get the available contents from the content type.
      $content_type = $this->content_types_manager->createInstance($content_type_id);
      $content_type_contents = $content_type->getAvailableContents();

      // For each content, convert it to an array.
      foreach ($content_type_contents as $content_type_content) {
        $available_contents[] = $content_type_content->toManagerArray();
      }
    }

    // Return the available contents in JSON.
    return new JsonResponse($available_contents, Response::HTTP_OK);
  }

  /**
   * Return content types available for learning paths.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group object.
   * @param bool $json_output
   *   JSON format flag.
   *
   * @return array|\Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  public function getItemTypes(Group $group, $json_output = TRUE) {
    // Init the return array and get all the content types available.
    $available_types = [];
    $content_types_definitions = $this->content_types_manager->getDefinitions();

    // Get the group bundle name.
    $group_type = $group->getGroupType();

    // Check if module 'Opigno Moxtra' is enabled and configured.
    $moxtra_status = \Drupal::moduleHandler()->moduleExists('opigno_moxtra') && _opigno_moxtra_get_organization_status();

    // For each content type available,
    // convert it to an array and store it in the return array.
    foreach ($content_types_definitions as $content_type_definition) {
      // Add available content type only if it's allowed by plugin.
      if (in_array($group_type->id(), $content_type_definition['allowed_group_types'])) {
        if ($content_type_definition['id'] == 'ContentTypeMeeting' && !$moxtra_status) {
          continue;
        }

        $available_types[] = [
          'bundle' => $content_type_definition['id'],
          'contentType' => $content_type_definition['id'],
          'name' => $content_type_definition['readable_name'],
        ];
      }
    }

    // If a JSON response is asked, return JSON.
    // Else, return the array.
    if ($json_output) {
      return new JsonResponse($available_types, Response::HTTP_OK);
    }
    else {
      return $available_types;
    }
  }

  /**
   * TODO: Create item from ajax drupal entity form.
   */
  public function createItem(Request $request, $type = NULL) {
    // TODO: Wait for Fred move first.
  }

  /**
   * Update entities ancestors.
   *
   * Eg. if user switch two entities in learning path.
   */
  public function updateEntities(Group $group, Request $request) {
    $datas = Json::decode($request->getContent());

    // TODO Update parents links if changed.
    foreach ($datas['entities'] as $entity) {
      if (!$entity['cid']) {
        return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
      };
      $current_cid = $entity['cid'];
      $content = OpignoGroupManagedContent::load($current_cid);

      // Parent content id.
      if (!empty($entity['parents'])) {
        $parent_cid = $entity['parents'][0]['cid'];
      }
      else {
        $parent_cid = NULL;
      };
      // Can be null if there is not parent.
      $parent_link = $content->getParentsLinks();
      if ($parent_link) {
        $parent_link = reset($parent_link);
        // Update parent link if was changed.
        if ($parent_cid == NULL) {
          // Delete parent link.
          $parent_link->delete();
        }
        elseif ($parent_link->getParentContentId() != $parent_cid) {
          $parent_link->setParentContentId($parent_cid);
          $parent_link->save();
        }
      }
      else {
        if ($parent_cid) {
          // Create new parent link instance.
          $new_link = OpignoGroupManagedLink::createWithValues(
            $group->id(),
            $parent_cid,
            $current_cid,
            $entity['successScoreMin']
          );
          $new_link->save();
        };
      };
    }

    return new JsonResponse([], Response::HTTP_OK);
  }

  /**
   * Update Learning Path guided navigation field.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group object.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request array.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response array.
   */
  public function updateGuidedNavigation(Group $group, Request $request) {
    $data = json_decode($request->getContent());
    if (!isset($data->guidedNavigation)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }

    $guidedNavigation = 0;
    if ($group->hasField('field_guided_navigation')) {
      $guidedNavigation = $data->guidedNavigation;
      $group->set('field_guided_navigation', $guidedNavigation);

      try {
        $group->save();
      }
      catch (EntityStorageException $e) {
        return new JsonResponse(NULL, Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    }

    return new JsonResponse(['guidedNavigation' => $guidedNavigation], Response::HTTP_OK);
  }

  /**
   * Return Learning Path guided navigation field.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response array.
   */
  public function getGuidedNavigationResponse(Group $group) {
    $guidedNavigation = self::getGuidedNavigation($group);

    // Return the JSON response.
    return new JsonResponse($guidedNavigation, Response::HTTP_OK);
  }

  /**
   * Get Learning Path guided navigation field.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group object.
   *
   * @return bool
   *   Training guided navigation flag
   */
  public static function getGuidedNavigation(Group $group) {
    if ($group->getGroupType()->id() == 'opigno_course') {
//      $route = \Drupal::routeMatch();
//      $route_name = $route->getRouteName();
      $group_id = OpignoGroupContext::getCurrentGroupId();
      if ($group_id) {
        $group = Group::load($group_id);
      }
//      if (in_array($route_name, [
//          'entity.group.canonical',
//          'opigno_learning_path.steps.start',
//          'opigno_module.take_module',
//          'opigno_module.group.answer_form',
//          'opigno_module.module_result',
//          'opigno_learning_path.steps.next',
//        ]) &&
//        (($entity = $route->getParameter('group')) !== NULL)) {
//        $group = $entity;
//      }
//      if ($route_name == 'opigno_module.module_result') {
//        $group_id = OpignoGroupContext::getCurrentGroupId();
//        $group = Group::load($group_id);
//      }
    }

    $guidedNavigation = NULL;
    if (is_object($group) && $group->hasField('field_guided_navigation')) {
      $guidedNavigation = $group->get('field_guided_navigation')->value;
    }

    return $guidedNavigation ? FALSE : TRUE;
  }

  /**
   * Duplicate course.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group object.
   */
  public function courseDuplicate(Group $group) {
    $duplicate = $group->createDuplicate();
    $current_name = $duplicate->label();
    $duplicate->set('label', $this->t('Duplicate of ') . $current_name);
    $current_time = \Drupal::time()->getCurrentTime();

    $duplicate->setOwnerId(\Drupal::currentUser()->id());
    $duplicate->set('created', $current_time);
    $duplicate->set('changed', $current_time);
    $duplicate->save();
    $duplicate_id = $duplicate->id();

    $course_content = $group->getContentEntities();

    foreach ($course_content as $content) {
      if ($content instanceof OpignoModule) {
        $duplicate->addContent($content, 'opigno_module_group');

        $managed_content = reset(OpignoGroupManagedContent::loadByProperties([
          'group_content_type_id' => 'ContentTypeModule',
          'entity_id' => $content->id(),
          'group_id' => $group->id(),
        ]));

        $parent_links = $managed_content->getParentsLinks();

        $new_content = OpignoGroupManagedContent::createWithValues(
          $duplicate->id(),
          $managed_content->getGroupContentTypeId(),
          $content->id(),
          $managed_content->getSuccessScoreMin(),
          $managed_content->isMandatory(),
          $managed_content->getCoordinateX(),
          $managed_content->getCoordinateY()
        );

        $new_content->save();

        foreach ($parent_links as $link) {
          $parent_old_content = OpignoGroupManagedContent::load($link->getParentContentId());
          $parent_module_id = $parent_old_content->getEntityId();

          $parent_new_content = reset(OpignoGroupManagedContent::loadByProperties([
            'group_content_type_id' => 'ContentTypeModule',
            'entity_id' => $parent_module_id,
            'group_id' => $duplicate_id,
          ]));

          if ($parent_new_content) {
            OpignoGroupManagedLink::createWithValues(
              $duplicate_id,
              $parent_new_content->id(),
              $new_content->id(),
              $link->getRequiredScore(),
              serialize($link->getRequiredActivities())
            )->save();
          }
        }
      }
    }



    $duplicate->save();

    return $this->redirect('entity.group.edit_form', [
      'group' => $duplicate_id,
    ]);
  }

  /**
   * Duplicate training.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group object.
   */
  public function trainingDuplicate(Group $group) {
    $duplicate = $group->createDuplicate();
    $current_name = $duplicate->label();
    $new_name = $this->t('Duplicate of ') . $current_name;
    $duplicate->set('label', $new_name);
    $current_time = \Drupal::time()->getCurrentTime();
    $user = \Drupal::currentUser();
    $user_id = $user->id();

    $duplicate->setOwnerId($user_id);
    $duplicate->set('created', $current_time);
    $duplicate->set('changed', $current_time);
    $duplicate->set('field_learning_path_enable_forum', 0);
    $duplicate->set('field_learning_path_forum', NULL);
    $duplicate->set('field_learning_path_folder', NULL);
    $duplicate->save();

    // Create workspace.
    $moxtra_api = _opigno_moxtra_get_moxtra_api();
    $response = $moxtra_api->createWorkspace($user_id, $new_name);
    $binder_id = $response['data']['id'];

    $workspace = Workspace::create();
    $workspace->setName($new_name);
    $workspace->setBinderId($binder_id);
    $workspace->save();

    // Attach workspace to the training.
    $duplicate->set('field_workspace', [
      'target_id' => $workspace->id(),
    ]);

    // Duplicate documents library.
    $tid = $group->get('field_learning_path_folder')->getString();
    $items_list = $this->documentsLibraryList($tid);

    $main_tid = $duplicate->get('field_learning_path_folder')->getString();
    $tids_relashionships = [];
    $tids_relashionships[$tid] = $main_tid;

    foreach ($items_list as $item) {
      if ($item['type'] == 'file') {
        $media = Media::load($item['id']);
        $media_copy = $media->createDuplicate();

        $file_id = $media->get('tft_file')->getValue()[0]['target_id'];
        $old_tid = $media->get('tft_folder')->getValue()[0]['target_id'];
        $file = File::load($file_id);
        $file_copy = $file->createDuplicate();
        $new_name = $file->id(). '-' . $file->label();
        $new_name = substr($new_name, 0, 200);
        $new_uri = str_replace($file->label(), $new_name, $file->getFileUri());
        $file_copy->setFilename($new_name);
        $file_copy->setFileUri($new_uri);
        $file_copy->save();
        $file_copy_id = $file_copy->id();

        if (isset($tids_relashionships[$old_tid])) {
          $new_tid = $tids_relashionships[$old_tid];
        }
        else {
          $new_tid = $main_tid;
        }

        $media_copy->set('tft_file', $file_copy_id);
        $media_copy->set('tft_folder', $new_tid);

        copy($file->getFileUri(), $new_uri);
        $media_copy->save();
      }
      elseif($item['type'] == 'term') {
        $old_term = Term::load($item['id']);

        if ($old_term) {
          $legacy_parent_id = $old_term->get('parent')->getValue()[0]['target_id'];
          if ($tids_relashionships[$legacy_parent_id]) {
            $parent = $tids_relashionships[$legacy_parent_id];
          }
          else {
            $parent = $main_tid;
          }

          $new_term = Term::create([
            'vid' => 'tft_tree',
            'name' => $item['name'],
            'parent' => $parent,
          ]);
          $new_term->save();
          $new_term_id = $new_term->id();

          $tids_relashionships[$item['id']] = $new_term_id;
        }
      }
    }

    $duplicate_id = $duplicate->id();

    $group_content = $group->getContentEntities();

    foreach ($group_content as $content) {
      if ($content instanceof OpignoModule || $content instanceof Group) {

        if ($content instanceof OpignoModule) {
          $duplicate->addContent($content, 'opigno_module_group');
          $group_content_type_id = 'ContentTypeModule';
        }
        else {
          $duplicate->addContent($content, 'subgroup:opigno_course');
          $group_content_type_id = 'ContentTypeCourse';
        }

        $managed_content = reset(OpignoGroupManagedContent::loadByProperties([
          'group_content_type_id' => $group_content_type_id,
          'entity_id' => $content->id(),
          'group_id' => $group->id(),
        ]));

        $parent_links = $managed_content->getParentsLinks();

        $new_content = OpignoGroupManagedContent::createWithValues(
          $duplicate->id(),
          $managed_content->getGroupContentTypeId(),
          $content->id(),
          $managed_content->getSuccessScoreMin(),
          $managed_content->isMandatory(),
          $managed_content->getCoordinateX(),
          $managed_content->getCoordinateY()
        );

        $new_content->save();

        foreach ($parent_links as $link) {
          $parent_old_content = OpignoGroupManagedContent::load($link->getParentContentId());
          $parent_module_id = $parent_old_content->getEntityId();

          $parent_new_content = reset(OpignoGroupManagedContent::loadByProperties([
            'group_content_type_id' => 'ContentTypeModule',
            'entity_id' => $parent_module_id,
            'group_id' => $duplicate_id,
          ]));

          if ($parent_new_content) {
            OpignoGroupManagedLink::createWithValues(
              $duplicate_id,
              $parent_new_content->id(),
              $new_content->id(),
              $link->getRequiredScore(),
              serialize($link->getRequiredActivities())
            )->save();
          }
        }
      }
    }

    $duplicate->save();

    return $this->redirect('entity.group.edit_form', [
      'group' => $duplicate_id,
    ]);
  }

  /**
   * Helper function.
   *
   * Get a list of all files and folders from the documents library.
   */
  public function documentsLibraryList($tid) {
    $main_content = _tft_folder_content($tid);
    $all_content = [];

    foreach ($main_content as $content) {
      $all_content[] = $content;

      if ($content['type'] == 'term') {
        $term_content = $this->documentsLibraryList($content['id']);
        $all_content = array_merge($all_content, $term_content);
      }
    }

    return $all_content;
  }
}
