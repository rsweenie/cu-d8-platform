<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

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
          'entity_keys' => [
            //'label' => 'parent_field_name',
          ],
        ] + $base_plugin_definition;

        // Try to automatically set the title field mapping.
        $title_fields = [
          'field_' . $resource_sub_type . '_title',
          'field_' . $resource_sub_type . '_name',
          'title',
        ];
        foreach ($title_fields as $title_field) {
          if ($resource_type_def->getFieldInfo($title_field)) {
            $this->derivatives[$resource_sub_type]['entity_keys']['label'] = $title_field;
            break;
          }
        }

        // Overrides by type.
        if ($resource_sub_type == 'hub_faculty') {
          $this->derivatives[$resource_sub_type]['entity_keys']['label'] = 'field_hub_faculty_name';
        }
      }
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
