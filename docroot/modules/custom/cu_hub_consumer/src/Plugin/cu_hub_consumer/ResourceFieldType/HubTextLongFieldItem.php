<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

/**
 * Generic text resource field.
 * 
 * @HubResourceFieldType(
 *   id = "hub_text_long",
 *   label = @Translation("Hub: Text (long)"),
 *   description = @Translation("Hub: Text (long)."),
 * )
 */
class HubTextLongFieldItem extends TextLongFieldItem {

  /**
   * {@inheritdoc}
   */
  public function getFieldFriendlyValue() {
    $values = $this->value;
    $values['hub_processed'] = $values['processed'];
    unset($values['processed']);
    return $values;
  }

}
