<?php

namespace Drupal\cu_hub_consumer\Hub;

/**
 * Defines an interface for a hub client.
 */
interface ClientInterface {

  /**
   * Returns the configured base URL.
   *
   * @return string
   */
  public function getBaseUrl();

  /**
   * Makes an HTTP request against an endpoint.
   *
   * @param string $method
   * @param string $endpoint
   * @param array $query
   * @param string $body
   * @return mixed
   */
  public function request($method, $endpoint = '', $query=[], $body='');

  /**
   * Performs a GET against an endpoint.
   *
   * @param string $endpoint
   * @param array $query
   * @param string $body
   * @return mixed
   */
  public function get($endpoint = '', $query=[], $body='');

  /**
   * Gets a list of endpoints.
   * 
   * @param boolean $safe
   *   If TRUE it will capture and log client exceptions rather than emitting an exception.
   * @param boolean $skip_cache
   *   If TRUE it will skip the cached version of the endpoints.
   * @return string[]
   */
  public function getEndpoints($safe=TRUE, $skip_cache=FALSE);

  /**
   * Gets a a specific endpoint.
   * 
   * @param string $resource_type_id
   *   The hub resource type ID.
   * @param boolean $safe
   *   If TRUE it will capture and log client exceptions rather than emitting an exception.
   * @param boolean $skip_cache
   *   If TRUE it will skip the cached version of the endpoints.
   * @return string[]
   */
  public function getEndpoint($resource_type_id, $safe=TRUE, $skip_cache=FALSE);

  /**
   * Strips the configured base URL from a given URL.
   *
   * @param string $url
   * @return string
   */
  public function stripBaseUrl($url);

}
