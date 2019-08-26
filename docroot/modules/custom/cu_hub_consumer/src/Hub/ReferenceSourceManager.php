<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\cu_hub_consumer\Annotation\HubReferenceSource;

/**
 * Manages hub reference source plugins.
 */
class ReferenceSourceManager extends DefaultPluginManager {

  /**
   * Constructs a new HubReferenceSourceManager.
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
    parent::__construct('Plugin/cu_hub_consumer/ReferenceSource', $namespaces, $module_handler, ReferenceSourceInterface::class, HubReferenceSource::class);

    $this->alterInfo('hub_reference_source_info');
    $this->setCacheBackend($cache_backend, 'hub_reference_source_plugins');
  }

}
