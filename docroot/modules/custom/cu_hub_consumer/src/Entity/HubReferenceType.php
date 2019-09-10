<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\cu_hub_consumer\Hub\ResourceTypeInterface;

/**
 * Defines the hub reference type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "hub_reference_type",
 *   label = @Translation("Hub Reference type"),
 *   label_collection = @Translation("Hub Reference types"),
 *   label_singular = @Translation("Hub Reference type"),
 *   label_plural = @Translation("Hub Reference types"),
 *   label_count = @PluralTranslation(
 *     singular = "@countHub Reference type",
 *     plural = "@count Hub Reference types"
 *   ),
 *   handlers = {
 *     "access" = "Drupal\cu_hub_consumer\HubReferenceTypeAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\cu_hub_consumer\Form\HubReferenceTypeForm",
 *       "edit" = "Drupal\cu_hub_consumer\Form\HubReferenceTypeForm",
 *       "delete" = "Drupal\cu_hub_consumer\Form\HubReferenceTypeDeleteConfirmForm"
 *     },
 *     "list_builder" = "Drupal\cu_hub_consumer\HubReferenceTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer cu hub consumer",
 *   config_prefix = "hub_reference_type",
 *   bundle_of = "hub_reference",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "source",
 *     "source_configuration",
 *     "field_map",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/services/cu_hub_consumer/reference-types/add",
 *     "edit-form" = "/admin/config/services/cu_hub_consumer/reference-types/{hub_reference_type}",
 *     "delete-form" = "/admin/config/services/cu_hub_consumer/reference-types/{hub_reference_type}/delete",
 *     "collection" = "/admin/config/services/cu_hub_consumer/reference-types",
 *   },
 * )
 */
class HubReferenceType extends ConfigEntityBundleBase implements HubReferenceTypeInterface, EntityWithPluginCollectionInterface {

  /**
   * The machine name of this hub reference type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the hub reference type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this hub reference type.
   *
   * @var string
   */
  protected $description;

  /**
   * The hub reference source ID.
   *
   * @var string
   */
  protected $source;

  /**
   * The hub reference source configuration.
   *
   * A hub reference source can provide a configuration form with source plugin-specific
   * configuration settings, which must at least include a source_field element
   * containing a the name of the source field for the hub reference type. The source
   * configuration is defined by, and used to load, the source plugin. See
   * \Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface for an explanation of hub reference sources.
   *
   * @var array
   *
   * @see \Drupal\cu_hub_consumer\HubReferenceTypeInterface::getSource()
   */
  protected $source_configuration = [];

  /**
   * Lazy collection for the hub reference source.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $sourcePluginCollection;

  /**
   * The metadata field map.
   *
   * @var array
   *
   * @see \Drupal\cu_hub_consumer\HubReferenceTypeInterface::getFieldMap()
   */
  protected $field_map = [];

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    return $this->set('description', $description);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'source_configuration' => $this->sourcePluginCollection(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    return $this->sourcePluginCollection()->get($this->source);
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceType() {
    if ($source = $this->getSource()) {
      return $source->getResourceType();
    }
  }

  /**
   * Returns hub reference source lazy plugin collection.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection|null
   *   The tag plugin collection or NULL if the plugin ID was not set yet.
   */
  protected function sourcePluginCollection() {
    if (!$this->sourcePluginCollection && $this->source) {
      $this->sourcePluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.cu_hub_consumer.hub_reference_source'), $this->source, $this->source_configuration);
    }
    return $this->sourcePluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldMap() {
    return $this->field_map;
  }

  /**
   * {@inheritdoc}
   */
  public function setFieldMap(array $map) {
    return $this->set('field_map', $map);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Clear the entity type definitions cache so changes flow through to the
    // related entity types.
    $this->entityTypeManager()->clearCachedDefinitions();

    // Clear the router cache to prevent RouteNotFoundException errors caused
    // by the Field UI module.
    \Drupal::service('router.builder')->rebuild();

    // Rebuild local actions so that the 'Add field' action on the 'Manage
    // fields' tab appears.
    \Drupal::service('plugin.manager.menu.local_action')->clearCachedDefinitions();

    // Clear the static and persistent cache.
    $storage->resetCache();

    $edit_link = $this->link(t('Edit entity type'));

    if ($update) {
      $this->logger($this->id())->notice(
        'Entity type %label has been updated.',
        ['%label' => $this->label(), 'link' => $edit_link]
      );
    }
    else {
      // Notify storage to create the database schema.
      //$entity_type = $this->entityTypeManager()->getDefinition($this->id());
      //\Drupal::service('entity_type.listener')
      //  ->onEntityTypeCreate($entity_type);

      $this->logger($this->id())->notice(
        'Entity type %label has been added.',
        ['%label' => $this->label(), 'link' => $edit_link]
      );
    }
  }

  /**
   * Gets the logger for a specific channel.
   *
   * @param string $channel
   *   The name of the channel.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger for this channel.
   */
  protected function logger($channel) {
    return \Drupal::getContainer()->get('logger.factory')->get($channel);
  }
}
