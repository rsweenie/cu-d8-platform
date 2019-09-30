<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "boolean",
 *   label = @Translation("Boolean"),
 *   description = @Translation("Boolean."),
 * )
 */
class BooleanFieldItem extends ScalarFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [
      '#markup' => $this->value ? 'True' : 'False',
    ];

    return $elements;
  }

}
