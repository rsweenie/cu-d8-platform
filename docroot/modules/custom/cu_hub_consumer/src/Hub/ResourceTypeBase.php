<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \GuzzleHttp\ClientInterface;
use \GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Base implementation of hub resource type plugin.
 */
abstract class ResourceTypeBase extends PluginBase implements ResourceTypeInterface, ContainerFactoryPluginInterface {

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

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
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a new hub resource type instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for cu_hub_consumer.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, LoggerInterface $logger, MessengerInterface $messenger, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->httpClient = $http_client;

    // Add the default configuration of the hub reference source to the plugin.
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.factory')->get('cu_hub_consumer'),
      $container->get('messenger'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'source_field' => 'hub_uuid',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  public function getBaseUrl() {
    if ($url = rtrim($this->configFactory->get('cu_hub_consumer.settings')->get('hub_base_url'), '/')) {
      if ($this->pluginDefinition['hub_path']) {
        $url .= '/' . ltrim($this->pluginDefinition['hub_path'], '/');
        return rtrim($url, '/');
      }
    }
  }

  public function getResourceUrl($hub_uuid) {
    if ($base_url = $this->getBaseUrl()) {
      if ($hub_uuid = $this->cleanUuid($hub_uuid)) {
        return $base_url . '/' . $hub_uuid;
      }
    }
  }

  public function getResourceListUrl() {
    return $this->getBaseUrl();
  }

  protected function cleanUuid($uuid) {
    // Only return a valid UUID string.
    if (preg_match('/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/', strtolower($uuid), $matches)) {
      return reset($matches);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetchResource($hub_uuid) {
    if ($url = $this->getResourceUrl($hub_uuid)) {
      try {
        $response = $this->request('GET', $url);
      }
      catch (RequestException $e) {
        throw new ResourceException('Could not retrieve the hub resource.', $url, [], $e);
      }
      
      $resource = Resource::createFromHttpResponse($this, $response);
      if (!$resource) {
        throw new ResourceException('Could not properly decode the hub resource.', $url);
      }

      return $resource;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetchResourceList($url = NULL, $limit=0) {
    $orig_url = $url;

    // If we aren't passed a URL for the next page in the list, generate the URL.
    if (!$url) {
      $url = $this->getResourceListUrl();
    }

    if ($url) {
      $query = [
        'fields[' . $this->pluginDefinition['hub_type_id'] . ']' => 'type,id',
      ];

      // If we didn't get passed in a URL, then we can set a limit.
      if (!$orig_url && $limit) {
        $query['page[limit]'] = $limit;
      }

      try {
        $response = $this->request('GET', $url, $query);
      }
      catch (RequestException $e) {
        throw new ResourceException('Could not retrieve the hub resource list.', $url, [], $e);
      }
      
      $resource = ResourceList::createFromHttpResponse($this, $response);
      if (!$resource) {
        throw new ResourceException('Could not properly decode the hub resource list.', $url);
      }

      return $resource;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getKeys() {
    return $this->pluginDefinition['entity_keys'];
  }

  /**
   * {@inheritdoc}
   */
  public function getKey($key) {
    $keys = $this
      ->getKeys();
    return isset($keys[$key]) ? $keys[$key] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeTypes() {
    return $this->pluginDefinition['attribute_types'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeType($attribute) {
    $attribute_types = $this
      ->getKeys();
    $attribute_type = isset($attribute_types[$attribute]) ? $attribute_types[$attribute] : FALSE;

    // Default to string if not specifically defined.
    return $attribute_type ? $attribute_type : 'string';
  }

  /**
   * Make a request against hub.
   *
   * @param string $method
   * @param string $url
   * @param array $query
   * @param string $body
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function request($method, $url, $query=[], $body='') {
    return $this->httpClient->request(
      $method,
      $url,
      $this->buildRequestOptions($query, $body)
    );
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

}
