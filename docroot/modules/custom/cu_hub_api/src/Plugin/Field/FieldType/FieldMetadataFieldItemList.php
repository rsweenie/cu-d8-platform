<?php

namespace Drupal\cu_hub_api\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Represents the computed field metadata for an entity.
 */
class FieldMetadataFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $entity = $this->getEntity();
    if (!$entity->id()) {
      return;
    }

    /*
    $metatag_manager = \Drupal::service('metatag.manager');
    $metatags_for_entity = $metatag_manager->tagsFromEntityWithDefaults($entity);
    $tags = $metatag_manager->generateRawElements($metatags_for_entity, $entity);
    $offset = 0;

    foreach ($tags as $tag) {
      $item = [
        'tag' => $tag['#tag'],
        'attributes' => $tag['#attributes'],
      ];
      $this->list[] = $this->createItem($offset, $item);
      $offset++;
    }
    */

    //$entityFieldManager = \Drupal::service('entity_field.manager');
    //$fields = $entityFieldManager->getFieldDefinitions($entity_type, $bundle);

    $offset = 0;
    $field_defs = $entity->getFieldDefinitions();

    $excluded_fields = [
      'field_metadata',
    ];

    if (\Drupal::service('module_handler')->moduleExists('jsonapi_extras')) {
      $entity_type_id = $entity->getEntityTypeId();
      $bundle = $entity->bundle();

      // If jsonapi_extras is used, exclude adding metadata for disabled fields.
      if ($resource_type = \Drupal::service('jsonapi.resource_type.repository')->get($entity_type_id, $bundle)) {
        $resource_config_id = sprintf('%s--%s', $entity_type_id, $bundle);
        if ($resource_config = \Drupal::service('entity_type.manager')->getStorage('jsonapi_resource_config')->load($resource_config_id)) {
          $resource_fields = $resource_config->get('resourceFields') ?: [];
          foreach ($resource_fields as $resource_field) {
            if ($resource_field['disabled']) {
              $excluded_fields[] = $resource_field['fieldName'];
            }
          }
        }
      }
    }

    foreach ($field_defs as $field_name => $definition) {
      if (!in_array($field_name, $excluded_fields)) {
        $field_type = $definition->getType();

        $item = [
          'field' => $field_name,
          'attributes' => [
            'type' => $field_type,
          ],
        ];

        if ($storage = $definition->getFieldStorageDefinition()) {
          if ($storage->getCardinality() !== 1) {
            $item['attributes']['multiple'] = TRUE;
          }
        }

        if (in_array($field_type, ['entity_reference', 'entity_reference_revisions', 'cu_hub_api_site_path'])) {
          $target_type = $definition->getSetting('target_type');

          $handler_settings = $definition->getSetting('handler_settings');
          $target_bundles = !empty($handler_settings['target_bundles']) ? array_keys($handler_settings['target_bundles']) : [$target_type];

          $item['attributes']['target_types'] = [];
          foreach ($target_bundles as $target_bundle) {
            $item['attributes']['target_types'][] = sprintf('%s--%s', $target_type, $target_bundle);
          }
        }

        $this->list[] = $this->createItem($offset, $item);
        $offset++;
      }
    }
  }

}
