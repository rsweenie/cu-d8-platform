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
        //'hub_path' => 'node/hub_site',
        //'attribute_types' => $base_plugin_definition['attribute_types'] + [
        //  'field_hub_site_base_uri' => 'link',
        //],
        'entity_keys' => [
          'label' => 'field_hub_site_title',
        ],
      ] + $base_plugin_definition,

      'program' => [
        'id' => 'program',
        'label' => t('Academic Program'),
        'description' => t('Academic Program node resource type.'),
        'hub_type_id' => 'node--hub_program',
        //'hub_path' => 'node/hub_program',
        //'attribute_types' => $base_plugin_definition['attribute_types'] + [
        //  'field_hub_site' => 'resource',
        //  'field_hub_program_title' => 'string',
        //  'field_hub_program_description' => 'text_long',
        //],
        'entity_keys' => [
          'label' => 'field_hub_program_title',
        ],
      ] + $base_plugin_definition,

      'degree' => [
        'id' => 'degree',
        'label' => t('Academic Degree'),
        'description' => t('Academic Degree node resource type.'),
        'hub_type_id' => 'node--hub_degree',
        /*
        'hub_path' => 'node/hub_degree',
        'attribute_types' => $base_plugin_definition['attribute_types'] + [
          'field_hub_site' => 'resource',
          'field_hub_program' => 'string',
          'field_hub_degree_title' => 'string',
          'field_hub_degree_title_short' => 'string',
          'field_hub_degree_description' => 'text_long',
          'field_hub_degree_details' => 'string',
          'field_hub_degree_requirements' => 'text_long',
          'field_hub_degree_availability' => 'resource[]',
          'field_hub_degree_hero_image' => 'string',
          'field_hub_degree_interests' => 'string',
          'field_hub_degree_related_degrees' => 'resource[]',
          'field_hub_degree_type' => 'string',
          'field_hub_degree_other_programs' => 'link[]',
        ],
        */
        'entity_keys' => [
          'label' => 'field_hub_degree_title',
        ],
      ] + $base_plugin_definition,
    ];

    /*
    $this->derivatives = [];

    $hub_client = \Drupal::service('cu_hub_consumer.hub_client');
    $hub_resource_inspector = \Drupal::service('cu_hub_consumer.hub_resource_inspector');

    if ($endpoints = $hub_client->getEndpoints()) {
      foreach ($endpoints as $resource_type => $endpoint_path) {
        if (strpos($resource_type, 'node--') === 0) {
          $name = substr($resource_type, strlen('node--'));
          $this->derivatives[$name] = [
            'id' => $name,
            'label' => $name,
            'description' => $name,
            'hub_type_id' => $resource_type,
          ];

          $this->derivatives[$name] = $this->derivatives[$name] + $base_plugin_definition;
        }
      }
    }
    */

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
