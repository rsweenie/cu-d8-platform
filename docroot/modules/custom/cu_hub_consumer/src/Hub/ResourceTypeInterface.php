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
   * Fetches a list of all resources of this type.
   *
   * @return []
   */
  public function fetchResourceList();

}
