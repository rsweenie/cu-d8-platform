<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;

/**
 * Plugin implementation of the 'hub_resource_label' formatter.
 *
 * @FieldFormatter(
 *   id = "hub_resource_label",
 *   label = @Translation("Hub resource label"),
 *   field_types = {
 *     "hub_resource"
 *   }
 * )
 */
class HubResourceLabelFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $resource_obj = $item->resource;
      if ($resource_obj instanceof ResourceInterface) {
        if ($resource_label = $resource_obj->label()) {
          $elements[$delta] = [
            '#markup' => $resource_label,
          ];
        }
      }
    }

    return $elements;
  }

}
