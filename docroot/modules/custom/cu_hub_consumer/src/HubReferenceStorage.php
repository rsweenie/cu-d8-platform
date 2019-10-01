<?php

namespace Drupal\cu_hub_consumer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;


/**
 * Defines the storage handler class for hub references.
 *
 * The default storage is overridden to handle metadata fetching outside of the
 * database transaction.
 */
class HubReferenceStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  protected function postLoad(array &$entities) {
    // Map hub field data onto entity fields.
    foreach ($entities as $entity) {
      $entity->mapHubFields();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $hub_reference) {
    if (method_exists($hub_reference, 'prepareSave')) {
      $hub_reference->prepareSave();
    }
    return parent::save($hub_reference);
  }

}
