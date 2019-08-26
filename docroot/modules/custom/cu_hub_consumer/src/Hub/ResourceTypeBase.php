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
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The field type plugin manager service.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type plugin manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for cu_hub_consumer.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory, FieldTypePluginManagerInterface $field_type_manager, LoggerInterface $logger, MessengerInterface $messenger, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $field_type_manager, $config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->fieldTypeManager = $field_type_manager;
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
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory'),
      $container->get('plugin.manager.field.field_type'),
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
  public function fetchResourceList() {
    if ($url = $this->getResourceListUrl()) {
      try {
        $response = $this->request('GET', $url, [
          'fields[' . $this->pluginDefinition['hub_type_id'] . ']' => 'type,id',
        ]);
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
