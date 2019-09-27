<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ReferenceSource;

use Drupal\cu_hub_consumer\Hub\ReferenceSourceBase;

/**
 * Undocumented class
 * 
 * @HubReferenceSource(
 *   id = "node",
 *   label = @Translation("Node source"),
 *   description = @Translation("Source for nodes."),
 *   allowed_field_types = {"string"},
 *   deriver = "Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ReferenceSource\NodeDeriver",
 *   metadata_attributes = {
 *     "created" = @Translation("Created timestmap"),
 *     "changed" = @Translation("Changed timestamp"),
 *     "revision_timestamp" = @Translation("Revision timestamp"),
 *     "langcode" = @Translation("Language code"),
 *     "metatag_normalized" = @Translation("Metadata (normalized)"),
 *   }
 * )
 */
class Node extends ReferenceSourceBase {

}
