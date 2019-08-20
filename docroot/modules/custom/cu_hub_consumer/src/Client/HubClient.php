<?php 

namespace Drupal\cu_hub_consumer\Client;

use \GuzzleHttp\ClientInterface;
use \GuzzleHttp\Exception\RequestException;
use Drupal\Core\Config\ConfigFactory;
use \Drupal\Core\Config\ImmutableConfig;

class HubClient implements HubClientInterface {

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client, ConfigFactory $config_factory) {
    $this->httpClient = $http_client;
    $this->config = $config_factory->get('cu_hub_consumer.settings');
  }

  public function request($method, $endpoint, $query=[], $body='') {
    $response = $this->httpClient->request(
      $method,
      $this->buildUri($endpoint),
      $this->buildOptions($query, $body)
    );
  }

  /**
   * Gets the base URI.
   *
   * @return string
   */
  public function getBaseUri() {
    return $this->config->get('base_uri');
  }

  /**
   * Build full URI from base URI and the endpoint.
   *
   * @param string $endpoint
   * @return string
   */
  private function buildUri($endpoint) {
    return rtrim($this->getBaseUri(), '/') . '/' . ltrim($endpoint, '/');
  }

  /**
   * Build options for the client
   *
   * @param array $query
   * @param string $body
   * @return array
   */
  private function buildOptions($query=[], $body='') {
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
}
