<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Core\Render\Element;

/**
 * Represents a resource field; that is, a list of field item objects.
 *
 * An resource field is a list of field items, each containing a set of
 * properties. Note that even single-valued entity fields are represented as
 * list of field items, however for easy access to the contained item the entity
 * field delegates __get() and __set() calls directly to the first item.
 */
class ResourceFieldItemList implements ResourceFieldItemListInterface {

  /**
   * Numerically indexed array of field items.
   *
   * @var \Drupal\cu_hub_consumer\Hub\ResourceFieldItemInterface[]
   */
  protected $list = array();

  /**
   * The resource object that contains this list.
   *
   * @var \Drupal\cu_hub_consumer\Hub\ResourceFieldItemInterface[]
   */
  protected $parent;

  /**
   * The name of the field on the parent resource.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The field type ID.
   *
   * @var string
   */
  protected $fieldType;

  /**
   * Whether field is singular or multiple value.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * Constructs a new ResourceFieldItemList.
   *
   * @param ResourceInterface $parent
   * @param string $field_name
   * @param string $field_type
   * @param boolean $singular
   */
  public function __construct(ResourceInterface $parent, $field_name, $field_type, $multiple = FALSE) {
    $this->parent = $parent;
    $this->fieldName = $field_name;
    $this->fieldType = $field_type;
    $this->multiple = $multiple;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentResource() {
    return $this->parent;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return $this->fieldName;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldType() {
    return $this->fieldType;
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiple() {
    return $this->multiple;
  }

  /**
   * {@inheritdoc}
   */
  public function getString() {
    $strings = array();
    foreach ($this->list as $item) {
      $strings[] = $item
        ->getString();
    }

    // Remove any empty strings resulting from empty items.
    return implode(', ', array_filter($strings, '\\Drupal\\Component\\Utility\\Unicode::strlen'));
  }

  /**
   * {@inheritdoc}
   */
  public function get($index) {
    if (!is_numeric($index)) {
      throw new \InvalidArgumentException('Unable to get a value with a non-numeric delta in a list.');
    }

    return isset($this->list[$index]) ? $this->list[$index] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function set($index, $value) {
    if (!is_numeric($index)) {
      throw new \InvalidArgumentException('Unable to set a value with a non-numeric delta in a list.');
    }

    // Ensure indexes stay sequential. We allow assigning an item at an existing
    // index, or at the next index available.
    if ($index < 0 || $index > count($this->list)) {
      throw new \InvalidArgumentException('Unable to set a value to a non-subsequent delta in a list.');
    }

    // If needed, create the item at the next position.
    $item = isset($this->list[$index]) ? $this->list[$index] : $this
      ->appendItem();
    $item
      ->setValue($value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeItem($index) {
    if (isset($this->list) && array_key_exists($index, $this->list)) {

      // Remove the item, and reassign deltas.
      unset($this->list[$index]);
      $this
        ->rekey($index);
    }
    else {
      throw new \InvalidArgumentException('Unable to remove item at non-existing index.');
    }
    return $this;
  }

  /**
   * Renumbers the items in the list.
   *
   * @param int $from_index
   *   Optionally, the index at which to start the renumbering, if it is known
   *   that items before that can safely be skipped (for example, when removing
   *   an item at a given index).
   */
  protected function rekey($from_index = 0) {

    // Re-key the list to maintain consecutive indexes.
    $this->list = array_values($this->list);

    // Each item holds its own index as a "name", it needs to be updated
    // according to the new list indexes.
    for ($i = $from_index; $i < count($this->list); $i++) {
      $this->list[$i]
        ->setContext($i, $this);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function first() {
    return $this
      ->get(0);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {

    // We do not want to throw exceptions here, so we do not use get().
    return isset($this->list[$offset]);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    $this
      ->removeItem($offset);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    return $this
      ->get($offset);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    if (!isset($offset)) {

      // The [] operator has been used.
      $this
        ->appendItem($value);
    }
    else {
      $this
        ->set($offset, $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function appendItem($value = NULL) {
    $offset = count($this->list);
    $item = $this
      ->createItem($value);
    $this->list[$offset] = $item;
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  protected function createItem($value = NULL) {
    $resource_field_type_manager = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_field_type');

    if ($field_item = $resource_field_type_manager->createInstance($this->getFieldType(), [])) {
      $field_item->setParentList($this);
      $field_item->setValue($value);

      return $field_item;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->list);
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->list);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    foreach ($this->list as $item) {
      if ($item instanceof ResourceFieldItemInterface) {
        if (!$item
          ->isEmpty()) {
          return FALSE;
        }
      }
      elseif ($item
        ->getValue() !== NULL) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function filter($callback) {
    if (isset($this->list)) {
      $removed = FALSE;

      // Apply the filter, detecting if some items were actually removed.
      $this->list = array_filter($this->list, function ($item) use ($callback, &$removed) {
        if (call_user_func($callback, $item)) {
          return TRUE;
        }
        else {
          $removed = TRUE;
        }
      });
      if ($removed) {
        $this
          ->rekey();
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function filterEmptyItems() {
    $this
      ->filter(function ($item) {
      return !$item
        ->isEmpty();
    });
    return $this;
  }

  /**
   * {@inheritdoc}
   * @todo Revisit the need when all entity types are converted to NG entities.
   */
  public function getValue($include_computed = FALSE) {
    $values = array();
    foreach ($this->list as $delta => $item) {
      $values[$delta] = $item
        ->getValue($include_computed);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {

    // Support passing in only the value of the first item, either as a literal
    // (value of the first property) or as an array of properties.
    if (isset($values) && (!is_array($values) || !empty($values) && !is_numeric(current(array_keys($values))))) {
      $values = array(
        0 => $values,
      );
    }
    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property_name) {

    // For empty fields, $entity->field->property is NULL.
    if ($item = $this
      ->first()) {
      return $item
        ->__get($property_name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __set($property_name, $value) {

    // For empty fields, $entity->field->property = $value automatically
    // creates the item before assigning the value.
    $item = $this
      ->first() ?: $this
      ->appendItem();
    $item
      ->__set($property_name, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($property_name) {
    if ($item = $this
      ->first()) {
      return $item
        ->__isset($property_name);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($property_name) {
    if ($item = $this
      ->first()) {
      $item
        ->__unset($property_name);
    }
  }

  /**
   * Magic method: Implements a deep clone.
   */
  public function __clone() {
    foreach ($this->list as $delta => $item) {
      $this->list[$delta] = clone $item;
      $this->list[$delta]
        ->setContext($delta, $this);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function equals(ResourceFieldItemListInterface $list_to_compare) {
    $value1 = $this
      ->getValue();
    $value2 = $list_to_compare
      ->getValue();
    if ($value1 === $value2) {
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = $this->viewElements();

    // If there are actual renderable children, use #theme => hub_resource_field, otherwise,
    // let access cacheability metadata pass through for correct bubbling.
    if (Element::children($elements)) {
      /*
      $info = array(
        '#theme' => 'hub_resource_field',
        '#title' => '',
        '#label_display' => 'hidden',
        '#field_name' => $this->getFieldName(),
        '#field_type' => $this->getFieldType(),
        '#resource_type' => $this->getParentResource()->getResourceTypeId(),
        '#resource' => $this->getParentResource(),
        '#items' => $this->list,
        '#is_multiple' => $this->isMultiple(),
      );
      */
      /*
      $info = [
        '#theme' => 'hub_resource_field',
        '#title' => '',
        '#label_display' => 'hidden',
        '#field_list' => $this,
        '#items' => $this->list,
        '#is_multiple' => $this->isMultiple(),
      ];
      
      $elements = array_merge($info, $elements);
      */

      $elements = [
        '#theme' => 'hub_resource_field',
        '#title' => '',
        '#label_display' => 'hidden',
        '#field_list' => $this,
        '#field_items' => $this->list,
        '#is_multiple' => $this->isMultiple(),
        '#elements' => $elements,
      ];
    }
    
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements() {
    $elements = [];

    foreach ($this->list as $delta => $item) {
      $elements[$delta] = $item->view();
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFriendlyValues() {
    $values = array();
    foreach ($this->list as $delta => $item) {
      $values[$delta] = $item
        ->getFieldFriendlyValue();
    }
    return $values;
  }

}
