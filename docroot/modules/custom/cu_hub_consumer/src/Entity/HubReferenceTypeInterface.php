<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface defining a hub reference type entity.
 *
 * Hub reference types are bundles for hub reference items. They are used to group
 * hub references with the same semantics.
 *
 */
interface HubReferenceTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Returns the hub reference source plugin.
   *
   * @return \Drupal\cu_hub_consumer\HubReferenceSourceInterface
   *   The hub reference source.
   */
  public function getSource();

  /**
   * Returns the matching ResourceType object from the source plugin.
   *
   * @return \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface
   */
  public function getResourceType();

  /**
   * Returns the metadata field map.
   *
   * Field mapping allows site builders to map hub reference item-related metadata to
   * entity fields. This information will be used when saving a given hub reference item
   * and if metadata values will be available they are going to be automatically
   * copied to the corresponding entity fields.
   *
   * @return array
   *   Field mapping array provided by hub reference source with metadata attribute
   *   names as keys and entity field names as values.
   */
  public function getFieldMap();

  /**
   * Sets the metadata field map.
   *
   * @param array $map
   *   Field mapping array with metadata attribute names as keys and entity
   *   field names as values.
   *
   * @return $this
   */
  public function setFieldMap(array $map);

}
