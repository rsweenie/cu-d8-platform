<?php

namespace Drupal\cu_hub_consumer\Hub;

/**
 * Defines an interface for a hub resource object.
 */
interface ResourceListInterface extends \ArrayAccess, \IteratorAggregate {

  /**
   * Create an instance from an HTTP response object.
   *
   * @param \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface $resource_type
   * @param \Psr\Http\Message\ResponseInterface $response
   * @return \Drupal\cu_hub_consumer\Hub\ResourceInterface
   */
  public static function createFromHttpResponse(ResourceTypeInterface $resource_type, \Psr\Http\Message\ResponseInterface $response);

  /**
   * Returns the resource type that generated this object.
   *
   * @return \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface
   */
  public function getResourceType();

  /**
   * Returns the parsed JSON data.
   *
   * @return array
   */
  public function getJsonData();

  /**
   * Returns the processed data.
   *
   * @return array
   */
  public function getProcessedData();

  /**
   * Returns the URL to the next list page, if exists.
   *
   * @return string
   */
  public function getNextUrl();

  /**
   * Returns the URL to the previous list page, if exists.
   *
   * @return string
   */
  public function getPrevUrl();

}
