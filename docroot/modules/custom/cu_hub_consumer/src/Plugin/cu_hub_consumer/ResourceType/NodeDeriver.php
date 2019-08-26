<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Derives hub resource type plugin definitions for supported node types.
 */
class NodeDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [
      'site' => [
        'id' => 'site',
        'label' => t('Site'),
        'description' => t('Site node resource type.'),
        'hub_type_id' => 'node--hub_site',
        'hub_path' => 'node/hub_site',
        'attribute_map' => [
          'field_hub_site_base_uri' => 'link',
        ],
        'entity_keys' => [
          'label' => 'field_hub_site_title',
        ],
      ] + $base_plugin_definition,

      'program' => [
        'id' => 'program',
        'label' => t('Academic Program'),
        'description' => t('Academic Program node resource type.'),
        'hub_type_id' => 'node--hub_program',
        'hub_path' => 'node/hub_program',
        'attribute_map' => [],
        'entity_keys' => [
          'label' => 'field_hub_program_title',
        ],
      ] + $base_plugin_definition,

      'degree' => [
        'id' => 'degree',
        'label' => t('Academic Degree'),
        'description' => t('Academic Degree node resource type.'),
        'hub_type_id' => 'node--hub_degree',
        'hub_path' => 'node/hub_degree',
        'attribute_map' => [
          'field_hub_degree_description' => 'text_long',
          'field_hub_degree_requirements' => 'text_long',
          'field_hub_degree_other_programs' => 'link_array',
        ],
        'entity_keys' => [
          'label' => 'field_hub_degree_title',
        ],
      ] + $base_plugin_definition,
    ];

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
