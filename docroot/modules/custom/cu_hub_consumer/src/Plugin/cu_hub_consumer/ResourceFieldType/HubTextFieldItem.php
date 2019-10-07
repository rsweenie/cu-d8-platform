<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

/**
 * Generic text resource field.
 * 
 * @HubResourceFieldType(
 *   id = "hub_text",
 *   label = @Translation("Hub: Text"),
 *   description = @Translation("Hub: Text."),
 * )
 */
class HubTextFieldItem extends TextFieldItem {

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
