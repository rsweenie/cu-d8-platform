<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "decimal",
 *   label = @Translation("Decimal"),
 *   description = @Translation("Decimal."),
 * )
 */
class IntegerFieldItem extends NumericFieldItemBase {

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number) {
    $scale = 2;
    $decimal_separator = '.';
    $thousand_separator = '';
    return number_format($number, $scale, $decimal_separator, $thousand_separator);
  }

}
