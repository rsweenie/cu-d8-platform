<?php

namespace Drupal\cu_hub_consumer;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the hub reference schema, based on the storage schema of contrib module.
 */
class HubReferenceStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    /* @TODO: Figure out appropriate indexes.
    // Add indexes.
    $schema['hub_reference']['unique keys'] += [
      'hash' => ['hash'],
    ];
    $schema['hub_reference']['indexes'] += [
      // Limit length to 191.
      'source_language' => [['hub_reference_source', 191], 'language'],
    ];
    */

    return $schema;
  }

}
