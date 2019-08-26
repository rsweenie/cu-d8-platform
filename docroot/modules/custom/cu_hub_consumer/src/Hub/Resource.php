<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Component\Serialization\Json;

/**
 * Hub resource data object.
 */
class Resource implements ResourceInterface {

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
    //$resource->jsonData = json_decode($response->getBody());

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
      if (!empty($this->jsonData['data']['id'])) {
        $this->processedData['type'] = $this->jsonData['data']['type'];
        $this->processedData['id'] = $this->jsonData['data']['id'];

        if (!empty($this->jsonData['data']['attributes'])) {
          foreach ($this->jsonData['data']['attributes'] as $field_name => $field_data) {
            $this->processedData[$field_name] = $field_data;
          }
        }

        if (!empty($this->jsonData['data']['relationships'])) {
          foreach ($this->jsonData['data']['relationships'] as $field_name => $field_data) {
            $this->processedData[$field_name] = $field_data;
          }
        }
      }
    }

    return $this->processedData;
  }

}
