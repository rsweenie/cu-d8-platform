<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ReferenceSource;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\cu_hub_consumer\Entity\HubResourceTypeDefinition;

/**
 * Derives hub resource type plugin definitions for supported node types.
 */
class NodeDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    $resource_type_defs = HubResourceTypeDefinition::loadMultiple();
    foreach ($resource_type_defs as $resource_type_def) {
      $resource_type = $resource_type_def->get('type_id');
      if (strpos($resource_type, 'node--') === 0) {
        list($resource_main_type, $resource_sub_type) = explode('--', $resource_type, 2);
        $this->derivatives[$resource_sub_type] = [
          'id' => $resource_sub_type,
          'label' => $resource_sub_type,
          'description' => $resource_sub_type,
          'hub_type_id' => $resource_type,
          'metadata_attributes' => $base_plugin_definition['metadata_attributes'],
        ] + $base_plugin_definition;

        if ($fields = $resource_type_def->get('fields')) {
          foreach (array_keys($fields) as $field_name) {
            $this->derivatives[$resource_sub_type]['metadata_attributes'][$field_name] = $field_name;
          }
        }
      }
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

  // @TODO: best way to pull in t() into this context?
  protected function t($string, $options=[]) {
    return $string;
  }

}
