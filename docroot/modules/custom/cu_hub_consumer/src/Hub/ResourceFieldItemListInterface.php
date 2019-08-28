<?php

namespace Drupal\cu_hub_consumer\Hub;

/**
 * Interface for resource fields, being lists of resource field items.
 *
 * This interface must be implemented by every resource field, whereas contained
 * field items must implement the ResourceFieldInterface.
 * Some methods of the fields are delegated to the first contained item, in
 * particular get() and set() as well as their magic equivalences.
 */
interface ResourceFieldItemListInterface extends \ArrayAccess, \IteratorAggregate {

  /**
   * Gets the resource the list belongs to.
   *
   * @return \Drupal\cu_hub_consumer\Hub\ResourceInterface
   *   The resource object.
   */
  public function getParentResource();

  /**
   * Gets the field name on the parent resource.
   *
   * @return void
   */
  public function getFieldName();

  /**
   * Gets the field type stored in this list.
   *
   * @return void
   */
  public function getFieldType();

  /**
   * Gets whether this field is supposed to contain a single item or multiple.
   *
   * @return boolean
   */
  public function isMultiple();

  /**
   * Filters out empty field items and re-numbers the item deltas.
   *
   * @return $this
   */
  public function filterEmptyItems();

  /**
   * Append a field item to the list.
   *
   * @param mixed $value
   * @return \Drupal\cu_hub_consumer\Hub\ResourceFieldItemInterface
   */
  public function appendItem($value = NULL);

  /**
   * Magic method: Gets a property value of to the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__set()
   */
  public function __get($property_name);

  /**
   * Magic method: Sets a property value of the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__get()
   */
  public function __set($property_name, $value);

  /**
   * Magic method: Determines whether a property of the first field item is set.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__unset()
   */
  public function __isset($property_name);

  /**
   * Magic method: Unsets a property of the first field item.
   *
   * @see \Drupal\Core\Field\FieldItemInterface::__isset()
   */
  public function __unset($property_name);

  /**
   * Determines equality to another object implementing ResourceFieldItemListInterface.
   *
   * @param \Drupal\cu_hub_consumer\Hub\ResourceFieldItemListInterface $list_to_compare
   *   The field item list to compare to.
   *
   * @return bool
   *   TRUE if the field item lists are equal, FALSE if not.
   */
  public function equals(ResourceFieldItemListInterface $list_to_compare);

  /**
   * Builds a renderable array for a fully themed field list.
   *
   * @return array
   *   A renderable array for a themed field with its label and all its values.
   */
  public function view();

  /**
   * Builds a renderable array for a field value.
   *
   * @return array
   *   A renderable array for $items, as an array of child elements keyed by
   *   consecutive numeric indexes starting from 0.
   */
  public function viewElements();

}
