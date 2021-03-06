<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\cu_hub_consumer\Hub\ResourceTypeBase;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;

/**
 * Undocumented class
 * 
 * @HubResourceType(
 *   id = "node",
 *   label = @Translation("Node resource type"),
 *   description = @Translation("Resource type for nodes."),
 *   deriver = "Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType\NodeDeriver",
 *   attribute_types = {
 *     "created" = "datetime",
 *     "changed" = "datetime",
 *     "revision_timestamp" = "datetime",
 *     "langcode" = "string",
 *     "metatag_normalized" = "metatags",
 *   }
 * )
 */
class Node extends ResourceTypeBase {

  /**
   * {@inheritdoc}
   */
  /*
  public function view(ResourceInterface $resource) {
    $element = [];

    if ($label = $resource->label()) {
      $element = [
        '#markup' => $label,
      ];
    }

    return $element;
  }
  */

}
