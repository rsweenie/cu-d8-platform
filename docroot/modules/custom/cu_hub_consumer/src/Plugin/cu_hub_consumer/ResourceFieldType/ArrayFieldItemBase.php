<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;

abstract class ArrayFieldItemBase extends ResourceFieldItemBase {

  /**
   * The main property of the property array.
   *
   * @return void
   */
  public function mainPropertyName() {
    return 'value';
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->{$this->mainPropertyName()});
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property_name) {
    return isset($this->value[$property_name]) ? $this->value[$property_name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function __set($property_name, $value) {
    $this->value[$property_name] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($property_name) {
    return isset($this->value[$property_name]);
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($property_name) {
    unset($this->value[$property_name]);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    if (!$this->isEmpty()) {
      return ScalarFieldItemBase::castToString($this->{$this->mainPropertyName()});
    }
  }

}
