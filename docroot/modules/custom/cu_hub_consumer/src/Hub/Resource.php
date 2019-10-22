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
   * Raw JSON data.
   *
   * @var string
   */
  protected $jsonDataRaw;

  /**
   * Unserialized JSON data.
   *
   * @var array
   */
  protected $jsonData;

  /**
   * Processed JSON data.
   *
   * @var array
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

    $resource->jsonDataRaw = $response->getBody();
    $resource->jsonData = Json::decode($resource->jsonDataRaw);

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

    // Try to pull from the static cache if possible.
    if (!empty($data['data']['id'])) {
      if ($resource = $resource_type->getFromStaticCache($data['data']['id'])) {
        return $resource;
      }
    }

    $resource = new static($resource_type);

    $resource->jsonData = $data;
    $resource->jsonDataRaw = Json::encode($resource->jsonData);

    // Try to set from the static cache if possible.
    if (!empty($data['data']['id'])) {
      $resource_type->setStaticCache($data['data']['id'], $resource);
    }

    // This must come after setting the static cache to avoid recursion.
    $resource->getProcessedData();

    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromData(ResourceTypeInterface $resource_type, $data, $included=[]) {
    if (!is_array($data)) {
      return NULL;
    }

    $meta = isset($data['meta']) ? $data['meta'] : [];

    // Try to pull from the static cache if possible.
    if (!empty($data['id'])) {
      if ($resource = $resource_type->getFromStaticCache($data['id'], $meta)) {
        return $resource;
      }
    }

    $resource = new static($resource_type);

    $resource->jsonData = ['data' => $data];

    if (!empty($included)) {
      $resource->jsonData['included'] = $included;
    }

    // Try to set from the static cache if possible.
    if (!empty($data['id'])) {
      $resource_type->setStaticCache($data['id'], $resource, $meta);
    }

    // This must come after setting the static cache to avoid recursion.
    $resource->getProcessedData();

    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $resource_type = $this->getResourceType();
    if ($resource_type->getKey('label') && isset($this->{$resource_type->getKey('label')})) {
      return $this->{$resource_type->getKey('label')}->getString();
    }
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
  public function getRawJsonData() {
    return $this->jsonDataRaw;
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
        $this->processedData['meta'] = isset($this->jsonData['data']['meta']) ? $this->jsonData['data']['meta'] : [];

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

    // If the field type plugin doesn't exist, try to find more a general one.
    //$resource_field_types = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_field_type');
    //if (!$resource_field_types->getDefinition($attribute_type, FALSE)) {
    //  list($attribute_type, $attribute_sub_type) = explode('--', $attribute_type, 2);
    //}

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

    if (!is_array($attribute_data) || !$multiple) {
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
      $resource_type = $resource_main_type = 'fallback';
      if ($first = reset($relationship_data['data'])) {
        if (!empty($first['type'])) {
          $resource_type = $first['type'];
          list($resource_main_type, $resource_sub_type) = explode('--', $resource_type, 2);
        }
      }

      $multiple = $this->getResourceType()->getAttributeMultiple($relationship_name);
      
      $relationship_list = new ResourceRelationshipList($this, $relationship_name, $resource_main_type, $multiple);

      // Try to pull in full data from the included section.
      if (!empty($this->jsonData['included']) && is_array($this->jsonData['included'])) {
        foreach ($relationship_data['data'] as &$relationship_data_item) {
          foreach ($this->jsonData['included'] as $included) {
            if (($included['type'] == $relationship_data_item['type']) && ($included['id'] == $relationship_data_item['id'])) {
              $meta = isset($relationship_data_item['meta']) ? $relationship_data_item['meta'] : NULL;
              $relationship_data_item = $included;

              if ($meta) {
                $relationship_data_item['meta'] = $meta;
              }

              break;
            }
          }
        }
      }

      // Now we can actually append the resources to the list.
      foreach ($relationship_data['data'] as &$relationship_data_item) {
        $included = isset($this->jsonData['included']) ? $this->jsonData['included'] : [];
        $relationship_list->appendItem($relationship_data_item, $included);
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

  /**
   * {@inheritdoc}
   */
  public function getString() {
    return $this->getResourceType()->getString($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFriendlyValue() {
    return $this->getResourceType()->getFieldFriendlyValue($this);
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    return $this->getResourceType()->view($this);
  }
}
