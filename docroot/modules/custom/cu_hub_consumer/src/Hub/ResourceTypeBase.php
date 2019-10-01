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
use Drupal\cu_hub_consumer\Entity\HubResourceTypeDefinition;
use Drupal\cu_hub_consumer\Entity\HubResourceTypeDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use \GuzzleHttp\ClientInterface;
//use GuzzleHttp\Exception\RequestException;
//use GuzzleHttp\Exception\ClientException;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * Hub resource type definition.
   *
   * @var \Drupal\cu_hub_consumer\Entity\HubResourceTypeDefinitionInterface
   */
  protected $hubResourceTypeDefinition;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for cu_hub_consumer.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\cu_hub_consumer\Hub\ClientInterface $hub_client
   *   The HTTP client.
   * @param \Drupal\cu_hub_consumer\Hub\ResourceInspector $hub_resource_inspector
   *   The HTTP client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, MessengerInterface $messenger, ClientInterface $hub_client, ResourceInspector $hub_resource_inspector) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
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
        $response = $this->hubClient->request('GET', $path, $this->buildFetchQuery());
      }
      catch (ClientException $e) {
        throw new ResourceException('Could not retrieve the hub resource: ' . $e->getMessage(), $e->getUrl(), [], $e);
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
      catch (ClientException $e) {
        throw new ResourceException('Could not retrieve the hub resource list: ' . $e->getMessage(), $path, [], $e);
      }
      
      $resource = ResourceList::createFromHttpResponse($this, $response);
      if (!$resource) {
        throw new ResourceException('Could not properly decode the hub resource list.', $path);
      }

      return $resource;
    }
  }

  protected function buildFetchQuery() {
    $query = [];

    if ($type_def = $this->getResourceTypeDefinition()) {
      //if ($includes = $this->findIncludes($type_def)) {
        //$flat = $this->collapseIncludes($includes);
        //$query['include'] = implode(',', $flat);
      //}
    }

    return $query;
  }

  protected function findIncludes(HubResourceTypeDefinitionInterface $type_def, $depth = 0) {
    $includes = [];

    // Prevent recursing too deep.
    if ($depth > 2) {
      return $includes;
    }

    $fields = $type_def->get('fields');
    foreach ($fields as $field_name => $field_info) {
      if ($field_info['type'] == 'hub_resource') {
        // Handle standard media includes.
        if ($field_info['hub_type'] == 'media') {
          $includes[$field_name] = [
            'image',
            'field_media_video_embed_field',
            'field_document',
          ];
        }

        // Handle paragraph references.
        if ($field_info['hub_type'] == 'paragraph') {
          $includes[$field_name] = [];

          // Since we don't know which paragraph bundles might be involved, we just have to include all possible variants.
          $resource_type_defs = HubResourceTypeDefinition::loadMultiple();
          foreach ($resource_type_defs as $resource_type_def) {
            $resource_type = $resource_type_def->get('type_id');
            if (strpos($resource_type, 'paragraph--') === 0) {
              $includes[$field_name] = array_merge($includes[$field_name], $this->findIncludes($resource_type_def, $depth+1));
            }
          }
        }

        if ($field_info['hub_type'] == 'node') {
          $includes[$field_name] = [];

          // Since we don't know which paragraph bundles might be involved, we just have to include all possible variants.
          $resource_type_defs = HubResourceTypeDefinition::loadMultiple();
          foreach ($resource_type_defs as $resource_type_def) {
            $resource_type = $resource_type_def->get('type_id');
            if (strpos($resource_type, 'node--') === 0) {
              $includes[$field_name] = array_merge($includes[$field_name], $this->findIncludes($resource_type_def, $depth+1));
            }
          }
        }
      }
    }

    return $includes;
  }

  protected function collapseIncludes($includes) {
    $flat_includes = [];

    if (!is_array($includes)) {
      return $flat_includes;
    }

    foreach ($includes as $field_name => $children) {
      if (empty($children)) {
        $flat_includes[] = $field_name;
      }
      else {
        $flat_children = $this->collapseIncludes($children);
        foreach ($flat_children as $child) {
          $flat_includes[] = $field_name . '.' . $child;
        }
      }
    }

    return array_unique($flat_includes);
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
  public function getResourceTypeDefinition() {
    if (!isset($this->hubResourceTypeDefinition)) {
      $this->hubResourceTypeDefinition = NULL;

      $def_storage = $this->entityTypeManager->getStorage('hub_resource_type_definition');
      $type_defs = $def_storage->loadByProperties(['type_id' => $this->getHubTypeId()]);
      if ($type_def = reset($type_defs)) {
        $this->hubResourceTypeDefinition = $type_def;
      }
    }

    return $this->hubResourceTypeDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getHubFields() {
    if ($type_def = $this->getResourceTypeDefinition()) {
      return $type_def->get('fields');
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeTypes() {
    $attribute_types = [];

    //$inspection_info = $this->hubResourceInspector->inspect($this->getHubTypeId());
    $inspection_info = $this->getHubFields();
    foreach ($inspection_info as $attribute_name => $attribute_info) {
      $attribute_types[$attribute_name] = $attribute_info['type'];
    }

    return $attribute_types;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeType($attribute) {
    //$inspection_info = $this->hubResourceInspector->inspect($this->getHubTypeId());
    $inspection_info = $this->getHubFields();
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
    //$inspection_info = $this->hubResourceInspector->inspect($this->getHubTypeId());
    $inspection_info = $this->getHubFields();
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

  /**
   * {@inheritdoc}
   */
  public function getString(ResourceInterface $resource) {
    return $resource->label();
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFriendlyValue(ResourceInterface $resource) {
    if ($processed_data = $resource->getProcessedData()) {
      return [
        'value' => $processed_data['id'],
        'resource' => $resource,
      ];
    }
  }
  
}
