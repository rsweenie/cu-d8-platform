<?php

namespace Drupal\cu_ccp_migration\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * This is an example of a simple SQL-based source plugin.
 *
 * Source plugins are classes which deliver source data to the processing
 * pipeline. For SQL sources, the SqlBase class provides most of the
 * functionality needed - for a specific migration, you are required to
 * implement the three simple public methods you see below.
 *
 * This annotation tells Drupal that the name of the MigrateSource plugin
 * implemented by this class is "beer_term". This is the name that the migration
 * configuration references with the source "plugin" key.
 *
 * @MigrateSource(
 *   id = "copy_box"
 * )
 */
class CopyBox extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // The most important part of a SQL source plugin is the SQL query to
    // retrieve the data to be imported. Note that the query is not executed
    // here - the migration process will control execution of the query. Also
    // note that it is constructed from a $this->select() call - this ensures
    // that the query is executed against the database configured for this
    // source plugin.
    $fields = ['uid' , 'pid', 'sorting', 'crdate', 'cruser_id', 'title', 'doktype', 'hidden', 'tstamp'];
    return $this->select('pages', 'p')
      ->fields('p', $fields)
      ->condition('p.deleted','0','=')
      ->condition('p.pid','-1','<>')
      ->condition('p.hidden',[0],'IN')
      
      ->leftJoin('tt_content','tt','tt.pid = p.uid')
      ->condition('tt.deleted','0','=')
      ->condition('tt.hidden','0','=')
      ->groupBy('p.uid')
      ->addExpression("GROUP_CONCAT(concat(IF(STRCMP(tt.header,''),CONCAT('<div class=\"header-layout-',tt.header_layout,'\">'),''),tt.header,IF(STRCMP(tt.header,''),'</div>',''),
      tt.bodytext,
      IF(STRCMP(tt.header,'') , concat('<img src=\"/sites/default/files/' , tt.image , '\"/><p>' , tt.imagecaption , '</p>') , '')
      ) ORDER BY tt.sorting ASC separator '<p style=\"display:none;\"></p>')",'sourceBody');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // This method simply documents the available source fields provided by the
    // source plugin, for use by front-end tools. It returns an array keyed by
    // field/column name, with the value being a translated string explaining
    // to humans what the field represents.
    $fields = [
      'uid' => t( 'Unique ID' ),
      'pid' => t( 'Parent ID' ), 
      'sorting' => t(' Sorting' ), 
      'crdate' => t( 'Create date' ), 
      'cruser_id' => t( 'Creator ID' ), 
      'title' => t( 'Title' ), 
      'doktype' => t( 'Doc Type' ), 
      'hidden' => t( 'Hidden (status)' ), 
      'tstamp' => t( 'Time Stamp' )
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    // This method indicates what field(s) from the source row uniquely identify
    // that source row, and what their types are. This is critical information
    // for managing the migration. The keys of the returned array are the field
    // names from the query which comprise the unique identifier. The values are
    // arrays indicating the type of the field, used for creating compatible
    // columns in the map tables that track processed items.
    return [
      'UID' => [
        'type' => 'int',
        // 'alias' is the alias for the table containing 'style' in the query
        // defined above. Optional in this case, but necessary if the same
        // column may occur in multiple tables in a join.
        'alias' => 'p',
      ],
    ];
  }

}
