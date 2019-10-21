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
interface HubResourceTypeDefinitionInterface extends ConfigEntityInterface {

  /**
   * Returns if the definition has changed at all.
   *
   * @return boolean
   */
  public function hasChanged();

  /**
   * Returns an array of changed fields.
   *
   * @return array
   */
  public function getChanges();

  /**
   * Gets the field information.
   *
   * @param string $field_name
   * @return mixed
   */
  public function getFieldInfo($field_name);

  /**
   * Sets the field information.
   *
   * @param string $field_name
   * @param array $info
   * @return void
   */
  public function setFieldInfo($field_name, $info);

  /**
   * Returns if field info exists.
   *
   * @param string $field_name
   * @return boolean
   */
  public function hasFieldInfo($field_name);

}
