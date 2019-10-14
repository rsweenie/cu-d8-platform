<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Component\Serialization\Json;

/**
 * Hub resource data object.
 */
class ResourceList implements ResourceListInterface {

  /**
   * Resource type
   *
   * @var \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface
   */
  protected $resourceType;

  /**
   * Unserialized JSON data.
   *
   * @var [type]
   */
  protected $jsonData;

  /**
   * Processed JSON data.
   */
  protected $processedData;

  /**
   * Construct a new Resource.
   *
   * @param ResourceTypeInterface $resource_type
   */
  public function __construct(ResourceTypeInterface $resource_type) {
    $this->resourceType = $resource_type;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromHttpResponse(ResourceTypeInterface $resource_type, \Psr\Http\Message\ResponseInterface $response) {
    $resource = new static(
      $resource_type
    );

    $resource->jsonData = Json::decode($response->getBody());

    $resource->getProcessedData();

    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceType() {
    return $this->resourceType;
  }

  /**
   * {@inheritdoc}
   */
  public function getJsonData() {
    return $this->jsonData;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessedData() {
    if (!isset($this->processedData)) {
      $this->processedData = [];

      // Sanity check.
      if (!empty($this->jsonData['data'][0]['id'])) {
        foreach ($this->jsonData['data'] as $item) {
          $this->processedData[$item['id']] = [
            'url' => $item['links']['self']['href'],
          ];

          if (!empty($item['attributes'])) {
            $this->processedData[$item['id']] += $item['attributes'];
          }
        }
      }
    }

    return $this->processedData;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextUrl() {
    if (!empty($this->jsonData['links']['next'])) {
      return $this->jsonData['links']['next'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPrevUrl() {
    if (!empty($this->jsonData['links']['prev'])) {
      return $this->jsonData['links']['prev'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->processedData);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {

    // We do not want to throw exceptions here, so we do not use get().
    return isset($this->processedData[$offset]);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    unset($this->processedData[$offset]);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    return isset($this->processedData[$offset]) ? $this->processedData[$offset] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    $this->processedData[$offset] = $value;
  }

}
