<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\cu_hub_consumer\Annotation\HubResourceFieldType;

/**
 * Manages hub resource plugins.
 */
class ResourceFieldTypeManager extends DefaultPluginManager {

  /**
   * Constructs a new ResourceFieldManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/cu_hub_consumer/ResourceFieldType', $namespaces, $module_handler, ResourceFieldItemInterface::class, HubResourceFieldType::class);

    $this->alterInfo('hub_resource_field_type_info');
    $this->setCacheBackend($cache_backend, 'hub_resource_field_type_plugins');
  }

  /**
   * Creates a field item list.
   *
   * @param \Drupal\cu_hub_consumer\Hub\ResourceInterface $resource
   * @param string $field_name
   * @param string $field_type
   * @param bool $singular
   * @return \Drupal\cu_hub_consumer\Hub\ResourceFieldItemList
   */
  /*
  public function createFieldItemList(ResourceInterface $resource, $field_name, $field_type, $singular = TRUE) {
    return new ResourceFieldItemList($resource, $field_name, $field_type, $singular);
  }
  */

}
