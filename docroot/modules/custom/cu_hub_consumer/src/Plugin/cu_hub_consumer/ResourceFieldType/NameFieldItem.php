<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;

/**
 * Name resource field.
 * 
 * @HubResourceFieldType(
 *   id = "name",
 *   label = @Translation("Name"),
 *   description = @Translation("Name."),
 * )
 */
class NameFieldItem extends ArrayFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function mainPropertyName() {
    return 'given';
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
          'value' => $this->given . ' ' . $this->family,
        ],
      ];
    }

    return $elements;
  }

}
