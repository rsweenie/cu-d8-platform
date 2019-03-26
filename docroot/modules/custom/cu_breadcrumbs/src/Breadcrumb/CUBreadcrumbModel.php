<?php
namespace Drupal\cu_breadcrumbs\Breadcrumb;

use Drupal\cu_model\Model\Model;
/**
 * CUBreadcrumbModel class
 * Max McCoy 3.18.19
 */
class CUBreadcrumbModel extends Model{
  //define a table
  protected $table = 'cu_breadcrumbs';
  //define the pk
  protected $primary_key = 'uuid';
  //database schema
  protected $schema = ['cu_breadcrumbs'=>
    [
      'description' => 'Stores values related to customer breadcrumbs module.',
      'fields' => [
        'uuid' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'description' => "Node UUID",
        ],
        'apply' => [
          'type' => 'int',
          'size'=> 'tiny',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Add cu_breadcrumbs to content_type?',
        ],
      ],
      'primary key' => ['uuid'],
      'indexes' => [],
    ]
  ];

  //fillable attributes
  public $fillable = [
    'uuid'=>null,
    'apply'=>0,//defualt to false
  ];
}