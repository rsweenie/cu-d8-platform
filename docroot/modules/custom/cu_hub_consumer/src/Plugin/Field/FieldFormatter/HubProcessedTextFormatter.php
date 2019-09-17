<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Xss;

/**
 * Plugin implementation of the 'hub_text_processed' formatter.
 *
 * @FieldFormatter(
 *   id = "hub_text_processed",
 *   label = @Translation("Hub processed text"),
 *   field_types = {
 *     "hub_text_long"
 *   }
 * )
 */
class HubProcessedTextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // We output just the preprocessed version fo the text.
      $elements[$delta] = [
        '#type' => 'inline_template',
        '#template' => '{{ value|raw }}',
        '#context' => [
          // We use a very permissive XSS filter as we assume the source is pretty safe.
          'value' => XSS::filterAdmin($item->hub_processed),
        ],
      ];
    }

    return $elements;
  }

}
