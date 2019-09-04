<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use \GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;

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

  protected $inspectionInfo;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for cu_hub_consumer.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerInterface $logger, ClientInterface $hub_client) {
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->hubClient = $hub_client;
  }

  /**
   * Inspect the hub API for field typing info.
   *
   * @param string $resource_type_id
   * @return array
   */
  public function inspect($resource_type_id) {
    $cid = 'cu_hub_consumer:hub_resource_inspection';

    if (!isset($this->inspectionInfo)) {
      if ($cache = \Drupal::cache()->get($cid)) {
        $this->inspectionInfo = $cache->data;
      }
      else {
        $this->inspectionInfo = [];
      }
    }

    if (!isset($this->inspectionInfo[$resource_type_id])) {
      $this->inspectionInfo[$resource_type_id] = [];

      if ($endpoint = $this->hubClient->getEndpoint($resource_type_id)) {
        $response = $this->hubClient->get($endpoint);
        if ($json = Json::decode($response->getBody())) {
          if (!empty($json['data']) && is_array($json['data'])) {
            foreach ($json['data'] as $resource) {
              if (!empty($resource['attributes']) && is_array($resource['attributes'])) {
                $this->inspectAttributes($resource['attributes'], $this->inspectionInfo[$resource_type_id]);
              }
            }
          }
        }
      }

      \Drupal::cache()->set($cid, $this->inspectionInfo);
    }

    return $this->inspectionInfo[$resource_type_id];
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
          'type' => 'unknown',
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

      return 'string';
    }

    if (is_array($value)) {
      // Is this array associative?
      if ($this->arrayIsAssociative($value)) {
        // Is this a text field?
        if (isset($value['value']) && isset($value['format'])) {
          return 'text';
        }

        // Is this a link field?
        if (isset($value['uri'])) {
          return 'link';
        }
      }
      // Else, if array is not associative then it's likely a multi-value attribute.
      else {
        // Let's run through all values and try to find most specific typing.
        $type = 'unknown';
        foreach ($value as $sub_value) {
          $type = $this->getBetterType($type, $this->detectAttributeType($sub_value));
        }
        return $type;
      }
    }

    return 'unknown';
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
      'text' => 6,
      'link' => 5,
      'date' => 4,
      'int'=> 3,
      'float' => 2,
      'string' => 1,
      'unknown' => 0,
    ];

    $type1_specificity = isset($specificity[$type1]) ? $specificity[$type1] : -1;
    $type2_specificity = isset($specificity[$type2]) ? $specificity[$type2] : -1;

    if (!$type1_specificity && !$type2_specificity) {
      return 'unknown';
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
