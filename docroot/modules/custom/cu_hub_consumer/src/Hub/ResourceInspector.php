<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use \GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Hub resource inspector.
 */
class ResourceInspector {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger channel for cu_hub_consumer.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The hub client.
   *
   * @var \Drupal\cu_hub_consumer\Hub\Client
   */
  protected $hubClient;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface
   */
  protected $memCacheBackend;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Cached inspection info.
   *
   * @var array
   */
  protected $inspectionInfo;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for cu_hub_consumer.
   * @param \Drupal\cu_hub_consumer\Hub\Client $hub_client
   *   The hub client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LoggerInterface $logger,
    ClientInterface $hub_client,
    CacheBackendInterface $cache,
    TimeInterface $time
  ) {
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->hubClient = $hub_client;
    $this->cacheBackend = $cache;
    $this->time = $time;
  }

  /**
   * Returns a list of API endpoints, keyed by resource type ID.
   *
   * @param boolean $safe
   *   If TRUE it will capture and log client exceptions rather than emitting an exception.
   * @return void
   */
  public function getResourceTypes($safe = TRUE) {
    return $this->hubClient->getEndpoints($safe);
  }

  /**
   * Returns a machine name version of a hub resource type ID.
   *
   * @param string $resource_type_id
   * @return string
   */
  public function getResourceTypeMachineId($resource_type_id) {
    $resource_type_id = preg_replace('/[^a-z0-9_]/i', '_', $resource_type_id);
    return $resource_type_id;
  }

  /**
   * Inspect the hub API for field typing info.
   *
   * @param string $resource_type_id
   * @return array
   */
  public function inspect($resource_type_id, $skip_cache=TRUE) {
    if (!isset($this->inspectionInfo)) {
      $this->inspectionInfo = [];
    }

    // We use a static cache to avoid recursion.
    if (!isset($this->inspectionInfo[$resource_type_id])) {
      if (!$skip_cache && $cache = $this->cacheBackend->get($this->buildCacheId($resource_type_id))) {
        $this->inspectionInfo[$resource_type_id] = $cache->data;
      }
      else {
        $this->inspectionInfo[$resource_type_id] = [];

        if ($endpoint = $this->hubClient->getEndpoint($resource_type_id)) {
          $response = $this->hubClient->get($endpoint);
          if ($json = Json::decode($response->getBody())) {
            if (!empty($json['data']) && is_array($json['data'])) {
              foreach ($json['data'] as $resource) {
                if (!empty($resource['attributes']) && is_array($resource['attributes'])) {
                  $this->inspectAttributes($resource['attributes'], $this->inspectionInfo[$resource_type_id]);
                }
                if (!empty($resource['relationships']) && is_array($resource['relationships'])) {
                  $this->inspectRelationships($resource['relationships'], $this->inspectionInfo[$resource_type_id], $skip_cache);
                }
              }
            }
          }
        }

        $cache_tags = [
          //$this->entityTypeId . '_values',
          'entity_field_info',
        ];

        // $max_age = Cache::PERMANENT;
        $max_age = 10;
        $expire = $max_age === Cache::PERMANENT
          ? Cache::PERMANENT
          : $this->time->getRequestTime() + $max_age;
        $this->cacheBackend->set($this->buildCacheId($resource_type_id), $this->inspectionInfo[$resource_type_id], $expire, $cache_tags);
      }
    }

    return $this->inspectionInfo[$resource_type_id];
  }

  protected function buildCacheId($id) {
    return 'cu_hub_consumer:hub_resource_inspection:' . $id;
  }

  /**
   * Inspect the attributes.
   *
   * @param array $attributes
   * @param array $inspection_info
   * @return void
   */
  protected function inspectAttributes($attributes, &$inspection_info) {
    foreach ($attributes as $attribute_name => $attribute_value) {
      if (!isset($inspection_info[$attribute_name]['type'])) {
        $inspection_info[$attribute_name] = [
          'type' => 'hub_unknown',
          'multiple' => FALSE,
        ];
      }
      switch ($attribute_name) {
        case 'metatag_normalized':
          // Metatags are a special case.
          $inspection_info['metatag_normalized']['type'] = 'metatags';
          break;

        default:
          $inspection_info[$attribute_name] = [
            'type' => $this->getBetterType($inspection_info[$attribute_name]['type'], $this->detectAttributeType($attribute_value)),
            'multiple' => $this->isMultiple($attribute_value),
          ];
          break;
      }
    }
  }

  /**
   * Inspect the relationships.
   *
   * @param array $attributes
   * @param array $inspection_info
   * @param boolean $skip_cache
   * @return void
   */
  protected function inspectRelationships($relationships, &$inspection_info, $skip_cache=FALSE) {
    foreach ($relationships as $relationship_name => $relationship_info) {
      if (!empty($relationship_info['data']) && is_array($relationship_info['data'])) {
        if ($this->isMultiple($relationship_info['data'])) {
          $inspection_info[$relationship_name] = [
            'type' => 'hub_resource',
            'hub_type' => $relationship_info['data'][0]['type'],
            'multiple' => TRUE,
          ];

          $this->inspect($relationship_info['data'][0]['type'], $skip_cache);
        }
        else {
          $inspection_info[$relationship_name] = [
            'type' => 'hub_resource',
            'hub_type' => $relationship_info['data']['type'],
            'multiple' => FALSE,
          ];

          $this->inspect($relationship_info['data']['type'], $skip_cache);
        }
      }
    }
  }

  /**
   * Detect the attribute type by the data it contains.
   *
   * @param mixed $value
   * @return string
   */
  protected function detectAttributeType($value) {
    if (is_scalar($value)) {
      // Is this a datetime?
      if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}$/i', $value)) {
        // Example value: 2019-08-13T03:38:57+00:00
        return 'datetime';
      }

      // Is this a number?
      if (is_numeric($value)) {
        if (intval($value) == $value) {
          return 'integer';
        }
        return 'decimal';
      }

      return 'string_long';
    }

    if (is_array($value)) {
      // Is this array associative?
      if ($this->arrayIsAssociative($value)) {
        // Is this a preprocessed text field?
        if (isset($value['value']) && isset($value['format']) && isset($value['processed'])) {
          return 'hub_text_long';
        }

        // Is this a text field?
        if (isset($value['value']) && isset($value['format'])) {
          return 'text_long';
        }

        // Is this a link field?
        if (isset($value['value']) && isset($value['url'])) {
          return 'uri';
        }

        // Is this a link field?
        if (isset($value['uri'])) {
          return 'link';
        }

        // Is this a name field?
        // keys { title, given, middle, family, generational, credentials }
        if (array_key_exists('given', $value) && array_key_exists('middle', $value) && array_key_exists('family', $value)) {
          return 'name';
        }
      }
      // Else, if array is not associative then it's likely a multi-value attribute.
      else {
        // Let's run through all values and try to find most specific typing.
        $type = 'hub_unknown';
        foreach ($value as $sub_value) {
          $type = $this->getBetterType($type, $this->detectAttributeType($sub_value));
        }
        return $type;
      }
    }

    return 'hub_unknown';
  }

  /**
   * Gets the better between two types based on specificity.
   *
   * @param string $type1
   * @param string $type2
   * @return string
   */
  protected function getBetterType($type1, $type2) {
    $specificity = [
      'name' => 13,
      'hub_resource' => 12,
      'hub_text_long' => 11,
      'text_long' => 10,
      'text' => 9,
      'uri' => 8,
      'link' => 7,
      'datetime' => 6,
      'date' => 5,
      'integer'=> 4,
      'decimal' => 3,
      'string_long' => 2,
      'string' => 1,
      'hub_unknown' => 0,
    ];

    $type1_specificity = isset($specificity[$type1]) ? $specificity[$type1] : -1;
    $type2_specificity = isset($specificity[$type2]) ? $specificity[$type2] : -1;

    if (!$type1_specificity && !$type2_specificity) {
      return 'hub_unknown';
    }

    if ($type1_specificity > $type2_specificity) {
      return $type1;
    }
    return $type2;
  }

  /**
   * Returns TRUE if value is a multi-value attribute.
   *
   * @param mixed $value
   * @return boolean
   */
  protected function isMultiple($value) {
    if (is_array($value)) {
      if ($this->arrayIsAssociative($value)) {
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns TRUE if array is associative.
   *
   * @param array $array
   * @return boolean
   */
  protected function arrayIsAssociative($array) {
    if (count(array_filter(array_keys($array), 'is_string')) > 0) {
      return TRUE;
    }
    return FALSE;
  }

}
