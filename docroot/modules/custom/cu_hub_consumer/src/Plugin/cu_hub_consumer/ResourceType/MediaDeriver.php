<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\cu_hub_consumer\Entity\HubResourceTypeDefinition;

/**
 * Derives hub resource type plugin definitions for supported media types.
 */
class MediaDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /*
    $this->derivatives = [
      'image' => [
        'id' => 'image',
        'label' => t('Image media'),
        'description' => t('Image media resource type.'),
        'hub_type_id' => 'media--image',
        //'hub_path' => 'media/image',
        'attribute_types' => [],
      ] + $base_plugin_definition,

      'video' => [
        'id' => 'video',
        'label' => t('Video media'),
        'description' => t('Video media resource type.'),
        'hub_type_id' => 'media--video',
        //'hub_path' => 'media/video',
        'attribute_types' => [],
      ] + $base_plugin_definition,
    ];
    */

    $this->derivatives = [];

    $resource_type_defs = HubResourceTypeDefinition::loadMultiple();
    foreach ($resource_type_defs as $resource_type_def) {
      $resource_type = $resource_type_def->get('type_id');
      if (strpos($resource_type, 'media--') === 0) {
        list($resource_main_type, $resource_sub_type) = explode('--', $resource_type, 2);
        $this->derivatives[$resource_sub_type] = [
          'id' => $resource_sub_type,
          'label' => $resource_sub_type,
          'description' => $resource_sub_type,
          'hub_type_id' => $resource_type,
          'entity_keys' => [
            'label' => 'name',
          ],
        ] + $base_plugin_definition;
      }
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
