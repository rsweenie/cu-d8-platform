<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ReferenceSource;

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
        'description' => t('Site node source.'),
        'hub_type_id' => 'node--hub_site',
        'metadata_attributes' => $base_plugin_definition['metadata_attributes'] + [
          'field_hub_site_title' => $this->t('Title'),
          'field_hub_site_base_uri' => $this->t('Base URL'),
        ],
      ] + $base_plugin_definition,

      'program' => [
        'id' => 'program',
        'label' => t('Academic Program'),
        'description' => t('Program node source.'),
        'hub_type_id' => 'node--hub_program',
        'metadata_attributes' => $base_plugin_definition['metadata_attributes'] + [
          'field_hub_site' => $this->t('Site'),
          'field_hub_program_title' => $this->t('Title'),
          'field_hub_program_description' => $this->t('Description'),
        ],
      ] + $base_plugin_definition,

      'degree' => [
        'id' => 'degree',
        'label' => t('Academic Degree'),
        'description' => t('Degree node source.'),
        'hub_type_id' => 'node--hub_degree',
        'metadata_attributes' => $base_plugin_definition['metadata_attributes'] + [
          'field_hub_site' => $this->t('Site'),
          'field_hub_program' => $this->t('Academic program'),
          'field_hub_degree_title' => $this->t('Title'),
          'field_hub_degree_title_short' => $this->t('Title (short)'),
          'field_hub_degree_description' => $this->t('Description'),
          'field_hub_degree_details' => $this->t('Details'),
          'field_hub_degree_requirements' => $this->t('Requirements'),
          'field_hub_degree_availability' => $this->t('Availability'),
          'field_hub_degree_hero_image' => $this->t('Hero image'),
          'field_hub_degree_interests' => $this->t('Interestes'),
          'field_hub_degree_related_degrees' => $this->t('Related degrees'),
          'field_hub_degree_type' => $this->t('Degree type'),
        ],
      ] + $base_plugin_definition,
    ];

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

  // @TODO: best way to pull in t() into this context?
  protected function t($string, $options=[]) {
    return $string;
  }

}
