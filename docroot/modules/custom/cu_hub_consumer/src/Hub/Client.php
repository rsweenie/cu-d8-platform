<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use \GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;

/**
 * Hub client.
 */
class Client implements ClientInterface {

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
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The base URL to use when fetching from hub.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * The endpoint URls on hub.
   *
   * @var string[]
   */
  protected $endpoints;

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
  public function __construct(ConfigFactoryInterface $config_factory, LoggerInterface $logger, GuzzleClientInterface $http_client) {
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseUrl() {
    if (!isset($this->baseUrl)) {
      $this->baseUrl = $this->configFactory
        ->get('cu_hub_consumer.settings')
        ->get('hub_base_url');
      
      if ($this->baseUrl) {
        $this->baseUrl = rtrim($this->baseUrl, '/') . '/jsonapi/';
      }
    }

    return $this->baseUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function request($method, $endpoint = '', $query=[], $body='') {
    if ($url = $this->buildRequestUrl($endpoint)) {
      try {
        return $this->httpClient->request(
          $method,
          $url,
          $this->buildRequestOptions($query, $body)
        );
      }
      catch (RequestException $e) {
        throw new ClientException($e->getMessage(), $url, $e);
      }
    }
    throw new ClientException('Could not build a request URL.', $url);
  }

  /**
   * {@inheritdoc}
   */
  public function get($endpoint = '', $query=[], $body='') {
    return $this->request('GET', $endpoint, $query, $body);
  }

  /**
   * Build a request URL.
   *
   * @param string $endpoint
   * @return string
   */
  protected function buildRequestUrl($endpoint = '') {
    if ($base_url = $this->getBaseUrl()) {
      if (!is_scalar($endpoint)) {
        $this->logger->warning(str_replace(__NAMESPACE__ . '\\', '', __CLASS__) . ':' . __LINE__ .': Endpoint is not a scalar value - ' . print_r($endpoint, TRUE));
      }
      return $base_url . trim($endpoint, " \t\n\r\0\x0B\/");
    }
  }

  /**
   * Build options for the client
   *
   * @param array $query
   * @param string $body
   * @return array
   */
  protected function buildRequestOptions($query=[], $body='') {
    $options = [];
    //$options['auth'] = $this->auth();
    if ($body) {
      $options['body'] = $body;
    }

    if ($query) {
      $options['query'] = $query;
    }

    return $options;
  }

  /**
   * Fetch endpoint list from JSON API.
   *
   * @return void
   */
  protected function fetchEndpoints() {
    $response = $this->get('');
    $json = Json::decode($response->getBody());

    // We shouldn't have anything in data.
    if (empty($json['data']) && !empty($json['links'])) {
      $endpoints = [];

      if (is_array($json['links'])) {
        foreach ($json['links'] as $resource => $info) {
          if (!empty($info['href'])) {
            $resource_url = $info['href'];

            // Make sure the resource URL actually starts with the base URL.
            if ($resource_path = $this->stripBaseUrl($resource_url)) {
              $endpoints[$resource] = $resource_path;
            }
          }
        }
      }

      return $endpoints;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEndpoints() {
    if (!isset($this->endpoints)) {
      $this->endpoints = [];

      $cid = 'cu_hub_consumer:hub_endpoints';
      if ($cache = \Drupal::cache()->get($cid)) {
        $this->endpoints = $cache->data;
      }
      else {
        try {
          $this->endpoints = $this->fetchEndpoints();
        }
        catch (ClientException $e) {
          $this->logger->error('Error fetching endpoints. Msg: %msg URL: %url', ['%msg' => $e->getMessage(), '%url' => $e->getUrl()]);
        }

        \Drupal::cache()->set($cid, $this->endpoints);
      }
    }
    return $this->endpoints;
  }

  public function getEndpoint($resource_type_id) {
    if ($endpoints = $this->getEndpoints()) {
      if (isset($endpoints[$resource_type_id])) {
        return $endpoints[$resource_type_id];
      }
    }
  }

  public function stripBaseUrl($url) {
    if (strpos($url, $this->getBaseUrl()) === 0) {
      $url = substr($url, strlen($this->getBaseUrl()));
      $url = trim($url, " \t\n\r\0\x0B\/");
      return $url;
    }
  }
}
