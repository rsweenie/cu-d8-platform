<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;

/**
 * Abstract scalar field implementation.
 */
abstract class ScalarFieldItemBase extends ResourceFieldItemBase {

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

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return ScalarFieldItemBase::castToString($this->value);
  }

}
