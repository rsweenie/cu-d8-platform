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
//use \GuzzleHttp\ClientInterface;
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
   * The hub client.
   *
   * @var \Drupal\cu_hub_consumer\Hub\Client
   */
  protected $hubClient;

  /**
   * The hub resource inspector.
   *
   * @var \Drupal\cu_hub_consumer\Hub\ResourceInspector
   */
  protected $hubResourceInspector;

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
   * @param \Drupal\cu_hub_consumer\Hub\ClientInterface $hub_client
   *   The HTTP client.
   * @param \Drupal\cu_hub_consumer\Hub\ResourceInspector $hub_resource_inspector
   *   The HTTP client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, LoggerInterface $logger, MessengerInterface $messenger, ClientInterface $hub_client, ResourceInspector $hub_resource_inspector) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->hubClient = $hub_client;
    $this->hubResourceInspector = $hub_resource_inspector;

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
      $container->get('logger.channel.cu_hub_consumer'),
      $container->get('messenger'),
      $container->get('cu_hub_consumer.hub_client'),
      $container->get('cu_hub_consumer.hub_resource_inspector')
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

  public function getHubTypeId() {
    return $this->pluginDefinition['hub_type_id'];
  }

  public function getEndpoint() {
    //return rtrim(ltrim($this->pluginDefinition['hub_path'], '/'), '/');
    return $this->hubClient->getEndpoint($this->getHubTypeId());
  }

  public function getResourcePath($hub_uuid) {
    if ($hub_uuid = $this->cleanUuid($hub_uuid)) {
      return $this->getEndpoint() . '/' . $hub_uuid;
    }
  }

  public function getResourceListPath() {
    return $this->getEndpoint();
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
    if ($path = $this->getResourcePath($hub_uuid)) {
      try {
        $response = $this->hubClient->request('GET', $path);
      }
      catch (ClientException $e) {
        throw new ResourceException('Could not retrieve the hub resource.', $e->getUrl(), [], $e);
      }
      
      $resource = Resource::createFromHttpResponse($this, $response);
      if (!$resource) {
        throw new ResourceException('Could not properly decode the hub resource.', $path);
      }

      return $resource;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetchResourceList($path = NULL, $limit=0) {
    $orig_path = $path;

    // If we aren't passed a path for the next page in the list, generate the path.
    if (!$path) {
      $path = $this->getResourceListPath();
    }

    if ($path) {
      $query = [
        'fields[' . $this->pluginDefinition['hub_type_id'] . ']' => 'type,id',
      ];

      // If we didn't get passed in a URL, then we can set a limit.
      if (!$orig_path && $limit) {
        $query['page[limit]'] = $limit;
      }

      try {
        $response = $this->hubClient->request('GET', $path, $query);
      }
      catch (RequestException $e) {
        throw new ResourceException('Could not retrieve the hub resource list.', $path, [], $e);
      }
      
      $resource = ResourceList::createFromHttpResponse($this, $response);
      if (!$resource) {
        throw new ResourceException('Could not properly decode the hub resource list.', $path);
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

  public function getHubFields() {
    return $this->hubResourceInspector->inspect($this->getHubTypeId());
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeTypes() {
    $attribute_types = [];

    $inspection_info = $this->hubResourceInspector->inspect($this->getHubTypeId());
    foreach ($inspection_info as $attribute_name => $attribute_info) {
      $attribute_types[$attribute_name] = $attribute_info['type'];
    }

    return $attribute_types;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeType($attribute) {
    $inspection_info = $this->hubResourceInspector->inspect($this->getHubTypeId());
    return isset($inspection_info[$attribute]['type']) ? $inspection_info[$attribute]['type'] : 'hub_unknown';

    /*
    $attribute_types = $this->getAttributeTypes();
    $attribute_type = isset($attribute_types[$attribute]) ? $attribute_types[$attribute] : FALSE;

    // Default to string if not specifically defined.
    return $attribute_type ? $attribute_type : 'string';
    */
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeMultiple($attribute) {
    $inspection_info = $this->hubResourceInspector->inspect($this->getHubTypeId());
    return isset($inspection_info[$attribute]['multiple']) ? $inspection_info[$attribute]['multiple'] : 'FALSE';
  }

  /**
   * {@inheritdoc}
   */
  public function view(ResourceInterface $resource) {
    //return ['#markup' => print_r($resource->getJsonData(), TRUE)];
    $elements = [];

    //if ($label = $resource->label()) {
    //  $elements = [
    //    '#markup' => $label,
    //  ];
    //}

    $values = $resource->getProcessedData();
    if (is_array($values)) {
      foreach ($values as $field => $field_list) {
        if ($field_list instanceof ResourceFieldItemListInterface) {
          $elements[$field] = $field_list->view();
        }
        elseif ($field_list instanceof ResourceRelationshipListInterface) {
          $elements[$field] = $field_list->view();
        }
        elseif (is_scalar($field_list)) {
          //$elements[$field] = ['#markup' => $field_list];
        }
      }
      $elements = array_filter($elements);
    }

    return $elements;
  }

}
