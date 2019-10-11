<?php

namespace Drupal\cu_hub_consumer\Hub;

/**
 * Defines an interface for a hub resource object.
 */
interface ResourceInterface {

  /**
   * Create an instance from an HTTP response object.
   *
   * @param \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface $resource_type
   * @param \Psr\Http\Message\ResponseInterface $response
   * @return \Drupal\cu_hub_consumer\Hub\ResourceInterface
   */
  public static function createFromHttpResponse(ResourceTypeInterface $resource_type, \Psr\Http\Message\ResponseInterface $response);

  /**
   * Create an instance from a data.
   *
   * @param \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface $resource_type
   * @param array $data
   * @return \Drupal\cu_hub_consumer\Hub\ResourceInterface
   */
  public static function createFromData(ResourceTypeInterface $resource_type, $data);

  public function label();

  /**
   * Returns the id of the resource type that generated this object.
   *
   * @return string
   */
  public function getResourceTypeId();

  /**
   * Returns the resource type that generated this object.
   *
   * @return \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface
   */
  public function getResourceType();

  /**
   * Returns the raw JSON data.
   *
   * @return array
   */
  public function getRawJsonData();

  /**
   * Returns the decoded JSON data.
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
   * Returns a values array friendly to Drupal fields.
   *
   * @return array
   */
  public function getFieldFriendlyValue();

  /**
   * Builds a renderable array for a fully themed field item.
   *
   * @return array
   *   A renderable array for a themed field item.
   */
  public function view();

  /**
   * Returns a string representation of the resource.
   *
   * @return string
   */
  public function getString();

}
