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
   * Constructs a new ResourceTypeManager.
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

  /*
  public function getByHubTypeId($hub_type_id) {
    // Try to find a resource type plugin to use.
    $resource_type = 'fallback';
    $plugins = $this->getDefinitions();
    foreach ($plugins as $plugin_id => $definition) {
      if ($definition['hub_type_id'] == $hub_type_id) {
        $resource_type = $plugin_id;
      }
    }

    // If we found a plugin, try to load it.
    if ($this->getDefinition($resource_type)) {
      return $this->createInstance($resource_type, []);
    }
  }
  */

  public function findPluginByHubTypeId($hub_type_id) {
    // Try to find a resource type plugin that matches.
    $plugins = $this->getDefinitions();
    foreach ($plugins as $plugin_id => $definition) {
      if ($definition['hub_type_id'] == $hub_type_id) {
        return $plugin_id;
      }
    }

    return 'fallback';
  }

}
