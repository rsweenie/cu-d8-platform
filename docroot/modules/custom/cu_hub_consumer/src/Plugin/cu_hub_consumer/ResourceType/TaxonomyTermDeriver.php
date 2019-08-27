<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Derives hub resource type plugin definitions for supported taxonomy term types.
 */
class TaxonomyTermDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [
      'program_availability' => [
        'id' => 'program_availability',
        'label' => t('Program Availability'),
        'description' => t('Program availability term resource type.'),
        'hub_type_id' => 'taxonomy_term--program_availability',
        'hub_path' => 'taxonomy_term/program_availability',
        'attribute_types' => [],
        'entity_keys' => [
          'label' => 'name',
        ],
      ] + $base_plugin_definition,

    ];

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
