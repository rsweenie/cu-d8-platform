<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Component\Serialization\Json;

/**
 * Hub resource data object.
 */
class Resource implements ResourceInterface {

  /**
   * Resource type
   *
   * @var \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface
   */
  protected $resourceType;

  /**
   * Decoded JSON data.
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
    $resource = new static($resource_type);

    $resource->jsonData = Json::decode($response->getBody());
    //$resource->jsonData = json_decode($response->getBody());
    $resource->getProcessedData();

    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromJson(ResourceTypeInterface $resource_type, $data) {
    if (!is_array($data)) {
      return NULL;
    }

    $resource = new static($resource_type);

    $resource->jsonData = $data;
    //$resource->jsonData = $data;
    $resource->getProcessedData();

    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromData(ResourceTypeInterface $resource_type, $data) {
    if (!is_array($data)) {
      return NULL;
    }

    $resource = new static($resource_type);

    $resource->jsonData = ['data' => $data];
    //$resource->jsonData = $data;
    $resource->getProcessedData();

    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceTypeId() {
    return $this->resourceType->getPluginId();
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

        $this->processAttributes();
        $this->processRelationships();
      }
    }

    return $this->processedData;
  }

  protected function processAttributes() {
    if (!empty($this->jsonData['data']['attributes'])) {
      foreach ($this->jsonData['data']['attributes'] as $attribute_name => $attribute_data) {
        $this->processedData[$attribute_name] = $this->parseAttribute($attribute_name, $attribute_data);
      }
    }
  }

  protected function parseAttribute($attribute_name, $attribute_data) {
    //$parsed = NULL;
    $attribute_type = $this->getResourceType()->getAttributeType($attribute_name);
    //$resource_field_type_manager = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_field_type');

    // Handle case of multi-value attributes.
    /*
    $multiple = FALSE;
    if ($attribute_type && preg_match('/(.*)\[\]$/', $attribute_type, $matches)) {
      $multiple = TRUE;
      $attribute_type = $matches[1];
    }
    */
    $multiple = $this->getResourceType()->getAttributeMultiple($attribute_name);

    //$item_list = $resource_field_type_manager->createFieldItemList($this, $attribute, $attribute_type, $multiple);
    $item_list = new ResourceFieldItemList($this, $attribute_name, $attribute_type, $multiple);

    if (!$multiple) {
      $attribute_data = [$attribute_data];
    }
    foreach ($attribute_data as $value) {
      $item_list->appendItem($value);
    }
    
    return $item_list;
  }

  protected function processRelationships() {
    if (!empty($this->jsonData['data']['relationships'])) {
      foreach ($this->jsonData['data']['relationships'] as $relationship_name => $relationship_data) {
        $this->processedData[$relationship_name] = $this->parseRelationship($relationship_name, $relationship_data);
      }
    }
  }

  protected function parseRelationship($relationship_name, $relationship_data) {
    // If nothing in the data, we skip processing it.
    if (!empty($relationship_data['data'])) {
      // If the relationship data is a single value realtionship, make it look like a multi value one.
      if (isset($relationship_data['data']['type'])) {
        $relationship_data['data'] = [$relationship_data['data']];
      }

      // Try to get the resource type of the first item.
      $relationship_resource_type = 'fallback';
      if ($first = reset($relationship_data['data'])) {
        if (!empty($first['type'])) {
          $relationship_resource_type = $first['type'];
        }
      }
      
      $relationship_list = new ResourceRelationshipList($this, $relationship_name, $relationship_resource_type);

      // Try to pull in full data from the included section.
      if (!empty($this->jsonData['included']) && is_array($this->jsonData['included'])) {
        foreach ($this->jsonData['included'] as $included) {
          foreach ($relationship_data['data'] as &$relationship_data_item) {
            if ($included['type'] == $relationship_data_item['type'] && $included['id'] == $relationship_data_item['id']) {
              $relationship_data_item = $included;
            }
          }
        }
      }

      // Now we can actually append the resources to the list.
      foreach ($relationship_data['data'] as $relationship_data_item) {
        $relationship_list->appendItem($relationship_data_item);
      }

    }
    else {
      // Better to be an empty fallback than nothing.
      $relationship_list = new ResourceRelationshipList($this, $relationship_name, 'fallback');
    }

    return $relationship_list;
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property_name) {
    return isset($this->getProcessedData()[$property_name]) ? $this->getProcessedData()[$property_name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($property_name) {
    return isset($this->getProcessedData()[$property_name]);
  }

}
