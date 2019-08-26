<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\cu_hub_consumer\Hub\ResourceTypeBase;

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

}
