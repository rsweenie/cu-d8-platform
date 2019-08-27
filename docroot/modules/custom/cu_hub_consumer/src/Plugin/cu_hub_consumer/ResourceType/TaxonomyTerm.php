<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\cu_hub_consumer\Hub\ResourceTypeBase;

/**
 * Undocumented class
 * 
 * @HubResourceType(
 *   id = "taxonomy_term",
 *   label = @Translation("Taxonomy term resource type"),
 *   description = @Translation("Resource type for taxonomy terms."),
 *   deriver = "Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType\TaxonomyTermDeriver",
 * )
 */
class TaxonomyTerm extends ResourceTypeBase {

}
