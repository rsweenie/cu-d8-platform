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

        $this->processAttributes();
        $this->processRelationships();

        /*
        if (!empty($this->jsonData['data']['relationships'])) {
          foreach ($this->jsonData['data']['relationships'] as $field_name => $field_data) {
            if (!empty($this->jsonData['included'])) {
              
            }
            $this->processedData[$field_name] = $field_data;
          }
        }
        */
      }
    }

    return $this->processedData;
  }

  protected function processAttributes() {
    if (!empty($this->jsonData['data']['attributes'])) {
      foreach ($this->jsonData['data']['attributes'] as $attribute => $attribute_data) {
        $this->processedData[$attribute] = $this->parseAttribute($attribute, $attribute_data);
      }
    }
  }

  protected function parseAttribute($attribute, $attribute_data) {
    $parsed = NULL;
    $attribute_type = $this->getResourceType()->getAttributeType($attribute);

    $resource_field_type_manager = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_field_type');
    //$resource_type_id = $resource_field_type_manager->findPluginByHubTypeId($relationship_data['data']['type']);
    //if ($resource_type = $resource_type_manager->createInstance($attribute_type, [])) {
    //}

    

    // Handle case of multi-value attributes.
    $singular = TRUE;
    if ($attribute_type && preg_match('/(.*)\[\]$/', $attribute_type, $matches)) {
      $singular = FALSE;
      $attribute_type = $matches[1];
    }

    $item_list = $resource_field_type_manager->createFieldItemList($this, $attribute, $attribute_type, $singular);

    if ($singular) {
      $attribute_data = [$attribute_data];
    }
    foreach ($attribute_data as $value) {
      $item_list->appendItem($value);
    }
    
    return $item_list;

    /*
    if ($attribute_type && preg_match('/(.*)_array$/', $attribute_type, $matches)) {
      $parsed = [];
      if (is_array($attribute_data)) {
        foreach ($attribute_data as $value) {
          $parsed[] = parseAttribute($matches[1], $value);
        }
      }
    }
    else {
      switch ($attribute_type) {
        case 'link':
          $parsed = $this->castToString((isset($attribute_data['uri']) ? $attribute_data['uri'] : ''));
          break;
  
        case 'text':
        case 'text_long':
        case 'text_with_summary':
          $parsed = $this->castToString((isset($attribute_data['processed']) ? $attribute_data['processed'] : ''));
          break;
  
        case 'string':
        default:
          $parsed = $this->castToString($attribute_data);
          break;
      }
    }
    */

    //return $parsed;
  }

  /**
   * Helper function to safely cast a variable to a string.
   *
   * @param mixed $value
   * @return string|FALSE
   */
  protected function castToString($value) {
    if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
      return (string) $value;
    }

    return FALSE;
  }

  protected function processRelationships() {
    if (!empty($this->jsonData['data']['relationships'])) {
      foreach ($this->jsonData['data']['relationships'] as $relationship => $relationship_data) {
        $this->processedData[$relationship] = [];

        //print_r($relationship_data);
        //$this->processedData[$relationship] = $relationship_data;
        //$this->processedData[$relationship] = Resource::createFromData($resource_type, $data);

        // If nothing in the data field, move to the next one.
        if (empty($relationship_data['data'])) {
          continue;
        }

        // If the relationship data is single value realtionship, make it look like a multi value one.
        if (isset($relationship_data['data']['type'])) {
          $relationship_data['data'] = [$relationship_data['data']];
        }

        // Try to pull in full data from the included section.
        if (!empty($this->jsonData['included']) && is_array($this->jsonData['included'])) {
          foreach ($this->jsonData['included'] as $included) {
            if (!is_array($relationship_data['data'])) {
              dsm(__LINE__);
              dsm($relationship_data);
              dsm(__LINE__);
              dsm($orig_data);
            }
            foreach ($relationship_data['data'] as &$relationship_data_item) {
              if ($included['type'] == $relationship_data_item['type'] && $included['id'] == $relationship_data_item['id']) {
                $relationship_data_item = $included;
              }
            }
          }
        }
        //print_r($relationship_data);

        // Create the appropriate resource.
        /*
        $resource_type_manager = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type');
        $resource_type_id = $resource_type_manager->findPluginByHubTypeId($relationship_data['data']['type']);
        if ($resource_type = $resource_type_manager->createInstance($resource_type_id, [])) {
          $this->processedData[$relationship] = Resource::createFromData($resource_type, $relationship_data);
        }
        */

        $resource_type_manager = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type');
        foreach ($relationship_data['data'] as $relationship_data_item) {
          $resource_type_id = $resource_type_manager->findPluginByHubTypeId($relationship_data_item['type']);
          if ($resource_type = $resource_type_manager->createInstance($resource_type_id, [])) {
            $this->processedData[$relationship][] = Resource::createFromData($resource_type, $relationship_data_item);
          }
        }
      }
    }
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
