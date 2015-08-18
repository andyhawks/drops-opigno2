<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Table\d7\Block.
 *
 * THIS IS A GENERATED FILE. DO NOT EDIT.
 *
 * @see core/scripts/migrate-db.sh
 * @see https://www.drupal.org/sandbox/benjy/2405029
 */

namespace Drupal\migrate_drupal\Tests\Table\d7;

use Drupal\migrate_drupal\Tests\Dump\DrupalDumpBase;

/**
 * Generated file to represent the block table.
 */
class Block extends DrupalDumpBase {

  public function load() {
    $this->createTable("block", array(
      'primary key' => array(
        'bid',
      ),
      'fields' => array(
        'bid' => array(
          'type' => 'serial',
          'not null' => TRUE,
          'length' => '11',
        ),
        'module' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '64',
          'default' => '',
        ),
        'delta' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '32',
          'default' => '0',
        ),
        'theme' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '64',
          'default' => '',
        ),
        'status' => array(
          'type' => 'int',
          'not null' => TRUE,
          'length' => '11',
          'default' => '0',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'length' => '11',
          'default' => '0',
        ),
        'region' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '64',
          'default' => '',
        ),
        'custom' => array(
          'type' => 'int',
          'not null' => TRUE,
          'length' => '11',
          'default' => '0',
        ),
        'visibility' => array(
          'type' => 'int',
          'not null' => TRUE,
          'length' => '11',
          'default' => '0',
        ),
        'pages' => array(
          'type' => 'text',
          'not null' => TRUE,
          'length' => 100,
        ),
        'title' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '255',
          'default' => '',
        ),
        'cache' => array(
          'type' => 'int',
          'not null' => TRUE,
          'length' => '11',
          'default' => '1',
        ),
      ),
      'mysql_character_set' => 'utf8',
    ));
    $this->database->insert("block")->fields(array(
      'bid',
      'module',
      'delta',
      'theme',
      'status',
      'weight',
      'region',
      'custom',
      'visibility',
      'pages',
      'title',
      'cache',
    ))
    ->values(array(
      'bid' => '1',
      'module' => 'system',
      'delta' => 'main',
      'theme' => 'bartik',
      'status' => '1',
      'weight' => '0',
      'region' => 'content',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '2',
      'module' => 'search',
      'delta' => 'form',
      'theme' => 'bartik',
      'status' => '1',
      'weight' => '-1',
      'region' => 'sidebar_first',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '3',
      'module' => 'node',
      'delta' => 'recent',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '10',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '4',
      'module' => 'user',
      'delta' => 'login',
      'theme' => 'bartik',
      'status' => '1',
      'weight' => '0',
      'region' => 'sidebar_first',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '5',
      'module' => 'system',
      'delta' => 'navigation',
      'theme' => 'bartik',
      'status' => '1',
      'weight' => '0',
      'region' => 'sidebar_first',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '6',
      'module' => 'system',
      'delta' => 'powered-by',
      'theme' => 'bartik',
      'status' => '1',
      'weight' => '10',
      'region' => 'footer',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '7',
      'module' => 'system',
      'delta' => 'help',
      'theme' => 'bartik',
      'status' => '1',
      'weight' => '0',
      'region' => 'help',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '8',
      'module' => 'system',
      'delta' => 'main',
      'theme' => 'seven',
      'status' => '1',
      'weight' => '0',
      'region' => 'content',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '9',
      'module' => 'system',
      'delta' => 'help',
      'theme' => 'seven',
      'status' => '1',
      'weight' => '0',
      'region' => 'help',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '10',
      'module' => 'user',
      'delta' => 'login',
      'theme' => 'seven',
      'status' => '1',
      'weight' => '10',
      'region' => 'content',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '11',
      'module' => 'user',
      'delta' => 'new',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '12',
      'module' => 'search',
      'delta' => 'form',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '-10',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '13',
      'module' => 'comment',
      'delta' => 'recent',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '1',
    ))->values(array(
      'bid' => '14',
      'module' => 'node',
      'delta' => 'syndicate',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '15',
      'module' => 'node',
      'delta' => 'recent',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '1',
    ))->values(array(
      'bid' => '16',
      'module' => 'shortcut',
      'delta' => 'shortcuts',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '17',
      'module' => 'system',
      'delta' => 'management',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '18',
      'module' => 'system',
      'delta' => 'user-menu',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '19',
      'module' => 'system',
      'delta' => 'main-menu',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '20',
      'module' => 'user',
      'delta' => 'new',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '1',
    ))->values(array(
      'bid' => '21',
      'module' => 'user',
      'delta' => 'online',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '22',
      'module' => 'comment',
      'delta' => 'recent',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '1',
    ))->values(array(
      'bid' => '23',
      'module' => 'node',
      'delta' => 'syndicate',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '24',
      'module' => 'shortcut',
      'delta' => 'shortcuts',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '25',
      'module' => 'system',
      'delta' => 'powered-by',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '10',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '26',
      'module' => 'system',
      'delta' => 'navigation',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '27',
      'module' => 'system',
      'delta' => 'management',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '28',
      'module' => 'system',
      'delta' => 'user-menu',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '29',
      'module' => 'system',
      'delta' => 'main-menu',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '30',
      'module' => 'user',
      'delta' => 'online',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '31',
      'module' => 'blog',
      'delta' => 'recent',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '1',
    ))->values(array(
      'bid' => '32',
      'module' => 'book',
      'delta' => 'navigation',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '5',
    ))->values(array(
      'bid' => '33',
      'module' => 'locale',
      'delta' => 'language',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '34',
      'module' => 'forum',
      'delta' => 'active',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-2',
    ))->values(array(
      'bid' => '35',
      'module' => 'forum',
      'delta' => 'new',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-2',
    ))->values(array(
      'bid' => '36',
      'module' => 'blog',
      'delta' => 'recent',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '1',
    ))->values(array(
      'bid' => '37',
      'module' => 'book',
      'delta' => 'navigation',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '5',
    ))->values(array(
      'bid' => '38',
      'module' => 'locale',
      'delta' => 'language',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '39',
      'module' => 'forum',
      'delta' => 'active',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-2',
    ))->values(array(
      'bid' => '40',
      'module' => 'forum',
      'delta' => 'new',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-2',
    ))->values(array(
      'bid' => '41',
      'module' => 'menu',
      'delta' => 'menu-test-menu',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '42',
      'module' => 'statistics',
      'delta' => 'popular',
      'theme' => 'bartik',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '43',
      'module' => 'menu',
      'delta' => 'menu-test-menu',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->values(array(
      'bid' => '44',
      'module' => 'statistics',
      'delta' => 'popular',
      'theme' => 'seven',
      'status' => '0',
      'weight' => '0',
      'region' => '-1',
      'custom' => '0',
      'visibility' => '0',
      'pages' => '',
      'title' => '',
      'cache' => '-1',
    ))->execute();
  }

}
#74042b5093754da56e33ac51a867f531
