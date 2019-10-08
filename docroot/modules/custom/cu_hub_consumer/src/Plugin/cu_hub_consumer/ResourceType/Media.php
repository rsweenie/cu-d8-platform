<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\cu_hub_consumer\Hub\ResourceTypeBase;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;
use Drupal\cu_hub_consumer\Hub\ResourceFieldItemListInterface;
use Drupal\cu_hub_consumer\Hub\ResourceRelationshipListInterface;

/**
 * Undocumented class
 * 
 * @HubResourceType(
 *   id = "media",
 *   label = @Translation("Media resource type"),
 *   description = @Translation("Resource type for media."),
 *   deriver = "Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType\MediaDeriver",
 * )
 */
class Media extends ResourceTypeBase {

  /**
   * {@inheritdoc}
   */
  /*
  public function view(ResourceInterface $resource) {
    //return ['#markup' => print_r($resource->getJsonData(), TRUE)];
    $elements = [];

    //if ($label = $resource->label()) {
    //  $elements = [
    //    '#markup' => $label,
    //  ];
    //}

    $values = $resource->getProcessedData();
    if (is_array($values)) {
      foreach ($values as $field => $field_list) {
        dsm($field);

        if ($field_list instanceof ResourceFieldItemListInterface) {
          $elements[$field] = $field_list->view();
        }
        elseif ($field_list instanceof ResourceRelationshipListInterface) {
          $elements[$field] = $field_list->view();
        }
        elseif (is_scalar($field_list)) {
          $elements[$field] = ['#markup' => $field_list];
        }
      }
    }
    dsm($elements);

    return $elements;
  }
  */

}
