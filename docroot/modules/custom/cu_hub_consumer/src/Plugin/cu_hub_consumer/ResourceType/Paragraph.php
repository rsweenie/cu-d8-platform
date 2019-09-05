<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\cu_hub_consumer\Hub\ResourceTypeBase;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;

/**
 * Undocumented class
 * 
 * @HubResourceType(
 *   id = "paragraph",
 *   label = @Translation("Paragraph resource type"),
 *   description = @Translation("Resource type for paragraphs."),
 *   deriver = "Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType\ParagraphDeriver",
 * )
 */
class Paragraph extends ResourceTypeBase {

  /**
   * {@inheritdoc}
   */
  /*
  public function view(ResourceInterface $resource) {
    return ['#markup' => print_r($resource->getJsonData(), TRUE)];

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
