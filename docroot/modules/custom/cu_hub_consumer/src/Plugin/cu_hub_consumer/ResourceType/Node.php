<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\cu_hub_consumer\Hub\ResourceTypeBase;

/**
 * Undocumented class
 * 
 * @HubResourceType(
 *   id = "node",
 *   label = @Translation("Node resource type"),
 *   description = @Translation("Resource type for nodes."),
 *   deriver = "Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType\NodeDeriver",
 * )
 */
class Node extends ResourceTypeBase {

}
