<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Xss;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;

/**
 * Plugin implementation of the 'hub_unknown' formatter.
 *
 * @FieldFormatter(
 *   id = "hub_unknown",
 *   label = @Translation("Hub unknown type"),
 *   field_types = {
 *     "hub_unknown"
 *   }
 * )
 */
class HubUnknownFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    return $elements;
  }

}
