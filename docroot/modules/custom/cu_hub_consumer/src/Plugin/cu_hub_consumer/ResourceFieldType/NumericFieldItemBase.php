<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

/**
 * Abstract numeric field implementation.
 */
abstract class NumericFieldItemBase extends ScalarFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [
      '#markup' => $this->numberFormat($this->value),
    ];

    return $elements;
  }

  /**
   * Formats a number.
   *
   * @param mixed $number
   *   The numeric value.
   *
   * @return string
   *   The formatted number.
   */
  protected abstract function numberFormat($number);

}
