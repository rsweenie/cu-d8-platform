<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "integer",
 *   label = @Translation("Integer"),
 *   description = @Translation("Integer."),
 * )
 */
class IntegerFieldItem extends NumericFieldItemBase {

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number) {
    $thousand_separator = '';
    return number_format($number, 0, '', $thousand_separator);
  }

}
