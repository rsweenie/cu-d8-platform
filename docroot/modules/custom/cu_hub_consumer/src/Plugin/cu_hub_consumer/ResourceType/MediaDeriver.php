<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Derives hub resource type plugin definitions for supported media types.
 */
class MediaDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [
      'image' => [
        'id' => 'image',
        'label' => t('Image media'),
        'description' => t('Image media resource type.'),
        'hub_type_id' => 'media--image',
        'hub_path' => 'media/image',
        'attribute_types' => [],
      ] + $base_plugin_definition,

      'video' => [
        'id' => 'video',
        'label' => t('Video media'),
        'description' => t('Video media resource type.'),
        'hub_type_id' => 'media--video',
        'hub_path' => 'media/video',
        'attribute_types' => [],
      ] + $base_plugin_definition,
    ];

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
