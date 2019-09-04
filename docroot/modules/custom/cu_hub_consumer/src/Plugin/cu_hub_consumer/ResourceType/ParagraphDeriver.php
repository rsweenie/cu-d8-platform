<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Derives hub resource type plugin definitions for supported paragraph types.
 */
class ParagraphDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [
      'copy' => [
        'id' => 'copy',
        'label' => t('Copy'),
        'description' => t('Copy paragraph resource type.'),
        'hub_type_id' => 'paragraph--copy',
        //'hub_path' => 'paragraph/copy',
        //'attribute_types' => [
        //  'field_copy_body' => 'text_long',
        //],
      ] + $base_plugin_definition,
    ];

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
