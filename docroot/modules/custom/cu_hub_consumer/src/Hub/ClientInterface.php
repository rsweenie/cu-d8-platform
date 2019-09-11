<?php

namespace Drupal\cu_hub_consumer\Hub;

/**
 * Defines an interface for a hub client.
 */
interface ClientInterface {

  public function getBaseUrl();

  public function request($method, $endpoint = '', $query=[], $body='');

  public function get($endpoint = '', $query=[], $body='');

  /**
   * Gets a list of endpoints.
   *
   * @return string[]
   */
  public function getEndpoints();
}
