<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Xss;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;

/**
 * Plugin implementation of the 'hub_resource' formatter.
 *
 * @FieldFormatter(
 *   id = "hub_resource",
 *   label = @Translation("Hub resource"),
 *   field_types = {
 *     "hub_resource"
 *   }
 * )
 */
class HubResourceFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $resource_obj = $item->value;
      if ($resource_obj instanceof ResourceInterface) {
        $resource_type = $resource_obj->getResourceTypeId();

        // Replace any characters that aren't allowed.
        $resource_type = preg_replace('/:/i', '__', $resource_type);
        $resource_type = preg_replace('/[^a-z0-9_-]/i', '_', $resource_type);

        $elements[$delta] = [
          '#theme' => 'hub_resource__' . $resource_type,
          '#resource_obj' => $resource_obj,
        ];
      }
    }

    return $elements;
  }

}
