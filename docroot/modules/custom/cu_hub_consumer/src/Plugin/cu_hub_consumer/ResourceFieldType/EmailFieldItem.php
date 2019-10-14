<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

/**
 * Generic email resource field.
 * 
 * @HubResourceFieldType(
 *   id = "email",
 *   label = @Translation("Email"),
 *   description = @Translation("Email."),
 * )
 */
class EmailFieldItem extends ScalarFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function view() {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    $elements = [
      '#type' => 'inline_template',
      '#template' => '{{ value|nl2br }}',
      '#context' => [
        'value' => (string) $this,
      ],
    ];

    return $elements;
  }

}
