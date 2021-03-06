<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for a hub resource type plugin.
 */
interface ResourceTypeInterface extends PluginInspectionInterface, DependentPluginInterface {

  /**
   * Fetches data from hub.
   *
   * @param string $hub_uuid
   *   The UUID of the object on hub.
   * @return \Drupal\cu_hub_consumer\Hub\ResourceInterface
   */
  public function fetchResource($hub_uuid);

  /**
   * Fetches a list of resources of this type
   *
   * @param string $url
   * @param integer $limit
   * @param array $fields
   * @return \Drupal\cu_hub_consumer\Hub\ResourceListInterface
   */
  public function fetchResourceList($url = NULL, $limit = 0, $fields = NULL);

  /**
   * Gets an array of entity keys.
   *
   * @return array
   */
  public function getKeys();

  /**
   * Gets a specific entity key.
   *
   * @param string $key
   * @return string
   */
  public function getKey($key);

  /**
   * Returns the resource type definition.
   *
   * @return \Drupal\cu_hub_consumer\Entity\HubResourceTypeDefinitionInterface
   */
  public function getResourceTypeDefinition();

  /**
   * Gets a list of the fields returned by hub.
   *
   * @return array
   */
  public function getHubFields();

  /**
   * Gets an array of attribute type mapping.
   *
   * @return array
   */
  public function getAttributeTypes();

  /**
   * Gets a specific attribute type mapping.
   *
   * @param string $key
   * @return string
   */
  public function getAttributeType($attribute);

  /**
   * Gets if a specific attribute is multi-value.
   *
   * @param string $key
   * @return boolean
   */
  public function getAttributeMultiple($attribute);

  /**
   * Builds a renderable array for a fully themed field item.
   *
   * @return array
   *   A renderable array for a themed field item.
   */
  public function view(ResourceInterface $resource);
  
  /**
   * Returns a string representation of a resource.
   *
   * @return string
   */
  public function getString(ResourceInterface $resource);

  /**
   * Returns a values array friendly to Drupal fields.
   *
   * @return array
   */
  public function getFieldFriendlyValue(ResourceInterface $resource);

}
