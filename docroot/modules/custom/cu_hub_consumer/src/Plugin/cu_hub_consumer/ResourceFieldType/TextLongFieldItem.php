<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "text_long",
 *   label = @Translation("Text (long)"),
 *   description = @Translation("Text (long)."),
 * )
 */
class TextLongFieldItem extends ArrayFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function mainPropertyName() {
    return 'processed';
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [];

    if (!$this->isEmpty()) {
      // We output just the preprocessed version fo the text.
      $elements = [
        '#type' => 'inline_template',
        '#template' => '{{ value|raw }}',
        '#context' => [
          // @TODO: Can we do some XSS checking here?
          'value' => $this->processed,
        ],
      ];
    }

    return $elements;
  }

}
