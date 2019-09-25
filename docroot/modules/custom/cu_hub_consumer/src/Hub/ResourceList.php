<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Component\Serialization\Json;

/**
 * Hub resource data object.
 */
class ResourceList implements ResourceListInterface {

  protected $resourceType;

  protected $jsonData;

  protected $processedData;

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
          $this->processedData[$item['id']] = $item['links']['self']['href'];
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

}
