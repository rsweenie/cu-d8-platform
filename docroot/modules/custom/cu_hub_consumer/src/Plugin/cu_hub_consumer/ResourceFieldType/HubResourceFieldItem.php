<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;
use Drupal\Component\Serialization\Json;

/**
 * Text resource field.
 * 
 * @HubResourceFieldType(
 *   id = "hub_resource",
 *   label = @Translation("Hub resource"),
 *   description = @Translation("Hub resource."),
 * )
 */
class HubResourceFieldItem extends ResourceFieldItemBase {

  /**
   * Helper function to safely cast a variable to a string.
   *
   * @param mixed $value
   * @return string|FALSE
   */
  public static function castToString($value) {
    if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
      return (string) $value;
    }
    elseif (is_array($value)) {
      return Json::encode($value);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return (string) static::castToString($this->value);
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [
      '#markup' => (string) $this,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFriendlyValue() {
    return ['value' => $this->value];
  }

}
