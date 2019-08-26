<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\cu_hub_consumer\Annotation\HubResourceType;

/**
 * Manages hub resource plugins.
 */
class ResourceTypeManager extends DefaultPluginManager {

  /**
   * Constructs a new ResourceManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/cu_hub_consumer/ResourceType', $namespaces, $module_handler, ResourceTypeInterface::class, HubResourceType::class);

    $this->alterInfo('hub_resource_type_info');
    $this->setCacheBackend($cache_backend, 'hub_resource_type_plugins');
  }
}
