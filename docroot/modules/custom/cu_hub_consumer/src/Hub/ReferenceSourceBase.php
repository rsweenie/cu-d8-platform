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
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\cu_hub_consumer\Hub\Resource;
use Drupal\cu_hub_consumer\Hub\ResourceException;
use Drupal\cu_hub_consumer\Entity\HubReferenceInterface;
use Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface;
use \GuzzleHttp\ClientInterface;

/**
 * Base implementation of hub reference source plugin.
 */
abstract class ReferenceSourceBase extends PluginBase implements ReferenceSourceInterface, ContainerFactoryPluginInterface {

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
   * The hub resource fetcher service.
   *
   * @var \Drupal\cu_hub_consumer\Hub\ResourceTypeManager
   */
  protected $resourceTypes;

  /**
   * The resource type plugin ID.
   *
   * @var string
   */
  protected $resource_type;

  /**
   * Lazy collection for the hub resource type.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $resourceTypePluginCollection;

  /**
   * Resource type configuration.
   *
   * @var array
   */
  protected $resource_type_configuration = [];

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type plugin manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for cu_hub_consumer.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\cu_hub_consumer\Hub\ResourceTypeManager $resource_types
   *   The hub resource types manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, FieldTypePluginManagerInterface $field_type_manager, ConfigFactoryInterface $config_factory, LoggerInterface $logger, MessengerInterface $messenger, ClientInterface $http_client, ResourceTypeManager $resource_types) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->fieldTypeManager = $field_type_manager;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->httpClient = $http_client;
    $this->resourceTypes = $resource_types;

    // Add the default configuration of the hub_reference source to the plugin.
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
      $container->get('plugin.manager.field.field_type'),
      $container->get('config.factory'),
      $container->get('logger.channel.cu_hub_consumer'),
      $container->get('messenger'),
      $container->get('http_client'),
      $container->get('plugin.manager.cu_hub_consumer.hub_resource_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'resource_type_configuration' => $this->resourceTypePluginCollection(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceType() {
    return $this->resourceTypePluginCollection()->get($this->resource_type);
  }

  /**
   * Returns hub resource type lazy plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection|null
   *   The tag plugin collection or NULL if the plugin ID was not set yet.
   */
  protected function resourceTypePluginCollection() {
    // Try to find a resource type plugin to use.
    /*
    $this->resource_type = 'fallback';
    $plugins = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type')->getDefinitions();
    foreach ($plugins as $plugin_id => $definition) {
      if ($this->pluginDefinition['hub_type_id'] == $definition['hub_type_id']) {
        $this->resource_type = $plugin_id;
      }
    }
    */
    $this->resource_type = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type')->findPluginByHubTypeId($this->pluginDefinition['hub_type_id']);

    if (!$this->resourceTypePluginCollection && $this->resource_type) {
      $this->resourceTypePluginCollection = new DefaultSingleLazyPluginCollection($this->resourceTypes, $this->resource_type, $this->resource_type_configuration);
    }
    return $this->resourceTypePluginCollection;
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
  public function getMetadataAttributes() {
    return [
      'type' => $this->t('Entity type'),
      'uuid' => $this->t('Entity UUID'),
    ] + $this->pluginDefinition['metadata_attributes'];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(HubReferenceInterface $hub_reference, $attribute_name) {
    $resource_data = &drupal_static(__FUNCTION__);
    
    $hub_uuid = $this->getSourceFieldValue($hub_reference);
    $resource_type = $this->getResourceType();

    if (!isset($resource_data[$hub_reference->id()])) {
      try {
        $resource = $resource_type->fetchResource($hub_uuid);
        $resource_data[$hub_reference->id()] = $resource->getProcessedData();
      }
      catch (ResourceException $e) {
        $this->messenger->addError($e->getMessage());
        return NULL;
      }
    }

    switch ($attribute_name) {
      case 'default_name':
        if ($title = $this->getMetadata($hub_reference, 'title')) {
          return $title;
        }
        elseif (($type = $this->getMetadata($hub_reference, 'type')) && ($uuid = $this->getMetadata($hub_reference, 'uuid'))) {
          return $type . ':' . $uuid;
        }
        return 'hub_reference:' . $hub_reference->bundle() . ':' . $hub_reference->uuid();

      case 'type':
        return $resource_data[$hub_reference->id()]['type'];
      
      case 'uuid':
        return $resource_data[$hub_reference->id()]['id'];

      case 'title':
        /*
        if (!empty($this->pluginDefinition['entity_keys']['label'])) {
          $resource_field = $this->pluginDefinition['entity_keys']['label'];
          if (!empty($resource_data[$hub_reference->id()][$resource_field])) {
            return $resource_data[$hub_reference->id()][$resource_field];
          }
        }
        */
        if ($key = $resource_type->getKey('label')) {
          if (!empty($resource_data[$hub_reference->id()][$key])) {
            return $resource_data[$hub_reference->id()][$key];
          }
        }
        return NULL;

      default:
        break;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Get the source field options for the hub_reference type form.
   *
   * This returns all fields related to hub_reference entities, filtered by the allowed
   * field types in the hub_reference source annotation.
   *
   * @return string[]
   *   A list of source field options for the hub_reference type form.
   */
  protected function getSourceFieldOptions() {
    // If there are existing fields to choose from, allow the user to reuse one.
    $options = [];
    foreach ($this->entityFieldManager->getFieldStorageDefinitions('hub_reference') as $field_name => $field) {
      $allowed_type = in_array($field->getType(), $this->pluginDefinition['allowed_field_types'], TRUE);
      if ($allowed_type && !$field->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getExposedFields() {
    //return $this->pluginDefinition['exposed_fields'];
    $resource_type = $this->getResourceType();
    $hub_fields = $resource_type->getHubFields();
    return is_array($hub_fields) ? array_keys($hub_fields) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = $this->getSourceFieldOptions();
    $form['source_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field with source information'),
      '#default_value' => $this->configuration['source_field'],
      '#empty_option' => $this->t('- Create -'),
      '#options' => $options,
      '#description' => $this->t('Select the field that will store essential information about the hub_reference item. If "Create" is selected a new field will be automatically created.'),
    ];

    if (!$options && $form_state->get('operation') === 'add') {
      $form['source_field']['#access'] = FALSE;
      $field_definition = $this->fieldTypeManager->getDefinition(reset($this->pluginDefinition['allowed_field_types']));
      $form['source_field_message'] = [
        '#markup' => $this->t('%field_type field will be automatically created on this type to store the essential information about the hub_reference item.', [
          '%field_type' => $field_definition['label'],
        ]),
      ];
    }
    elseif ($form_state->get('operation') === 'edit') {
      $form['source_field']['#access'] = FALSE;
      $fields = $this->entityFieldManager->getFieldDefinitions('hub_reference', $form_state->get('type')->id());
      $form['source_field_message'] = [
        '#markup' => $this->t('%field_name field is used to store the essential information about the hub_reference item.', [
          '%field_name' => $fields[$this->configuration['source_field']]->getLabel(),
        ]),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    foreach (array_intersect_key($form_state->getValues(), $this->configuration) as $config_key => $config_value) {
      $this->configuration[$config_key] = $config_value;
    }

    // If no source field is explicitly set, create it now.
    if (empty($this->configuration['source_field'])) {
      $field_storage = $this->createSourceFieldStorage();
      $field_storage->save();
      $this->configuration['source_field'] = $field_storage->getName();
    }
  }

  /**
   * Creates the source field storage definition.
   *
   * By default, the first field type listed in the plugin definition's
   * allowed_field_types array will be the generated field's type.
   *
   * @return \Drupal\field\FieldStorageConfigInterface
   *   The unsaved field storage definition.
   */
  protected function createSourceFieldStorage() {
    return $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->create([
        'entity_type' => 'hub_reference',
        'field_name' => $this->getSourceFieldName(),
        'type' => reset($this->pluginDefinition['allowed_field_types']),
      ]);
  }

  /**
   * Returns the source field storage definition.
   *
   * @return \Drupal\Core\Field\FieldStorageDefinitionInterface|null
   *   The field storage definition or NULL if it doesn't exists.
   */
  protected function getSourceFieldStorage() {
    // Nothing to do if no source field is configured yet.
    $field = $this->configuration['source_field'];
    if ($field) {
      // Even if we do know the name of the source field, there's no
      // guarantee that it exists.
      $fields = $this->entityFieldManager->getFieldStorageDefinitions('hub_reference');
      return isset($fields[$field]) ? $fields[$field] : NULL;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceFieldDefinition(HubReferenceTypeInterface $type) {
    // Nothing to do if no source field is configured yet.
    $field = $this->configuration['source_field'];
    if ($field) {
      // Even if we do know the name of the source field, there is no
      // guarantee that it already exists.
      $fields = $this->entityFieldManager->getFieldDefinitions('hub_reference', $type->id());
      return isset($fields[$field]) ? $fields[$field] : NULL;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function createSourceField(HubReferenceTypeInterface $type) {
    $storage = $this->getSourceFieldStorage() ?: $this->createSourceFieldStorage();
    return $this->entityTypeManager
      ->getStorage('field_config')
      ->create([
        'field_storage' => $storage,
        'bundle' => $type->id(),
        'label' => $this->pluginDefinition['label'],
        'required' => TRUE,
      ]);
  }

  /**
   * Determine the name of the source field.
   *
   * @return string
   *   The source field name. If one is already stored in configuration, it is
   *   returned. Otherwise, a new, unused one is generated.
   */
  protected function getSourceFieldName() {
    // Some hub_reference sources are using a deriver, so their plugin IDs may contain
    // a separator (usually ':') which is not allowed in field names.
    $base_id = 'field_hub_reference_' . str_replace(static::DERIVATIVE_SEPARATOR, '_', $this->getPluginId());
    $tries = 0;
    $storage = $this->entityTypeManager->getStorage('field_storage_config');

    // Iterate at least once, until no field with the generated ID is found.
    do {
      $id = $base_id;
      // If we've tried before, increment and append the suffix.
      if ($tries) {
        $id .= '_' . $tries;
      }
      $field = $storage->load('hub_reference.' . $id);
      $tries++;
    } while ($field);

    return $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceFieldValue(HubReferenceInterface $hub_reference) {
    $source_field = $this->configuration['source_field'];
    if (empty($source_field)) {
      throw new \RuntimeException('Source field for hub_reference source is not defined.');
    }

    /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
    $field_item = $hub_reference->get($source_field)->first();
    return $field_item->{$field_item->mainPropertyName()};
  }

  /*
  public function getSourceUrl(HubReferenceInterface $hub_reference) {
    $hub_reference_source = $this->getSourceFieldValue($hub_reference);

    try {
      return $this->urlResolver->getResourceUrl($hub_reference_source);
    }
    catch (ResourceException $e) {
      $this->messenger->addError($e->getMessage());
      return NULL;
    }
  }
  */

  /**
   * {@inheritdoc}
   */
  public function prepareViewDisplay(HubReferenceTypeInterface $type, EntityViewDisplayInterface $display) {
    $display->setComponent($this->getSourceFieldDefinition($type)->getName());
  }

  /**
   * {@inheritdoc}
   */
  public function prepareFormDisplay(HubReferenceTypeInterface $type, EntityFormDisplayInterface $display) {
    // Make sure the source field is placed just after the "title" basefield.
    $name_component = $display->getComponent('title');
    $source_field_weight = ($name_component && isset($name_component['weight'])) ? $name_component['weight'] + 5 : -50;
    $display->setComponent($this->getSourceFieldDefinition($type)->getName(), [
      'weight' => $source_field_weight,
    ]);
  }

}
