<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;

/**
 * Plugin implementation of the 'hub_resource_link' formatter.
 *
 * @FieldFormatter(
 *   id = "hub_resource_link",
 *   label = @Translation("Hub resource link"),
 *   field_types = {
 *     "hub_resource"
 *   }
 * )
 */
class HubResourceLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $resource_obj = $item->value;
      if ($resource_obj instanceof ResourceInterface) {
        if (isset($resource_obj->metatag_normalized->link_canonical)) {
          // By default use the full URL as the link text.
          $url = Url::fromUri($resource_obj->metatag_normalized->link_canonical, []);
          $link_title = $url->toString();

          if ($resource_obj->label()) {
            $link_title = $resource_obj->label();
          }

          // We output just the preprocessed version fo the text.
          $elements = [
            '#type' => 'link',
            '#title' => $link_title,
            '#url' => $url,
          ];
        }
      }
    }

    return $elements;
  }

}
