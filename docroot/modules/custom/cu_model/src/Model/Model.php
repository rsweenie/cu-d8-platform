<?php
namespace Drupal\cu_model\Model;
/**
 * Model class
 * abstract data model for easy OOP database interactions
 * Max McCoy 3.19.19
 */
abstract class Model {
  //drupal db table name
  protected $table;
  //primary key
  protected $primary_key;
  //database schema
  protected $schema;
  //drupal db connection
  protected $connection;
  //fillable attributes
  public $fillable = [];

  //you can send a connection if you want
  public function __construct($connection = null)
  {
    $this->setConnection($connection);
  }
  //get model attribute(property) from fillable array
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
      //check if the key is in the fillable array
      if ($this->isFillable($key))
        $this->setAttribute($key, $value);
    }
    // return "this" but with full properties(attributes, I need to decide what I call them)!(or as full as they can be)
    // this function will only fill properties that exist in the fillable array.
    return $this;
  }

  //check if key is in fillable array
  private function isFillable($key){
    return in_array($key,array_keys($this->fillable));
  }

  //static get schema from model
  static function getSchema(){
    //this is called late static binding
    //it allows the extending class to define static properties
    $self = new static;
    return $self->schema;
  }

  //get table from model
  static function getTable(){
    $self = new static;
    return $self->table;
  }

  //get the pimary key
  static function getPrimaryKey(){
    $self = new static;
    return $self->primary_key;
  }

  //set db connection
  public function setConnection($connection){
    $this->connection = $connection;
  }

  //get the db connection
  public function getConnection(){
    if(empty($this->connection)){
      $this->setConnection(\Drupal::database());
      return $this->connection;
    }
    return $this->connection;
  }

  //"save"(upsert) the model
  public function save(){
    $query = $this->getConnection()->upsert(Self::getTable())
    //save fillable properties
      ->fields($this->fillable);
    //upsert needs to know the pk for the table you are updating            
    $query->key(Self::getPrimaryKey());
    $query->execute();
  }

  //get record from table fill and return model
  static function getByUuid($uuid){
    $self = new static;
    //get from table
    $query = $self->getConnection()->select(Self::getTable(), 't')
    //using id
      ->condition('t.'.Self::getPrimaryKey(),$uuid, '=')
      ->fields('t', array_keys($self->fillable));
    //fetch the record, there should only be one
    return $self->fill((array) $query->execute()->fetch());
  }
}