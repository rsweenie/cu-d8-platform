<?php
namespace Drupal\cu_breadcrumbs\Breadcrumb;
/**
 * CUBreadcrumbModel class
 * Max McCoy 3.18.19
 * TODO: abstract to model class and extend
 */
class CUBreadcrumbModel extends Model{

  static $table = 'cu_breadcrumbs';
  protected $primary_key = 'uuid';
  //database schema
  static $schema = ['cu_breadcrumbs'=>
    [
      'description' => 'Stores values related to customer breadcrums module.',
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

  protected $connection;

  //fillable attributes
  public $fillable = [
    'uuid'=>null,
    'apply'=>0,//defualt to false
  ];
  //you can send a connection if you want
  public function __construct($connection = null)
  {
    $this->setConnection($connection);
  }
  //get model attribute from fillable array
  public function get(string $key){
    return $this->fillable[$key];
  }
  //set one attribute in fillable array
  public function setAttribute($key, $value){
    $this->fillable[$key] = $value;
  }
  //fill fillable array
  public function fill(array $attributes)
  {
    foreach ($attributes as $key => $value) {
      if ($this->isFillable($key))
        $this->setAttribute($key, $value);
    }
    return $this;
  }
  //check if key is in fillable array
  private function isFillable($key){
    return in_array($key,array_keys($this->fillable));
  }
  //get table from model
  static function getTable(){
    return Self::$table;
  }

  public function getPrimaryKey(){
    return $this->primary_key;
  }
  //set db connection
  public function setConnection($connection){
    $this->connection = $connection;
  }
  //get the db connection
  public function getConnection(){
    if(!empty($this->connection)){
      return $this->connection;
    }
    $this->setConnection(\Drupal::database());
    return $this->connection;
  }
  //"save"(upsert) the model
  public function save(){
    var_dump($this->fillable);
    $query = $this->getConnection()->upsert(Self::$table)
    //save fillable properties
      ->fields($this->fillable);
    //upsert needs to know the pk for the table you are updating            
    $query->key($this->primary_key);
    $query->execute();
  }
  //get record from table fill and return model
  public function getById($uuid){
    //get from table
    $query = $this->getConnection()->select(Self::$table, 't')
    //using id
      ->condition('t.'.$this->primary_key,$uuid, '=')
      ->fields('t', array_keys($this->fillable));
    //fetch the record, there should only be one
    return $this->fill((array) $query->execute()->fetch());
  }
  //static get schema from model
  static function getSchema(){
    return Self::$schema;
  }
}