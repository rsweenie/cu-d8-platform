<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Defines the hub reference type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "hub_reference_type",
 *   label = @Translation("Hub reference type"),
 *   label_collection = @Translation("Hub reference types"),
 *   label_singular = @Translation("hub reference type"),
 *   label_plural = @Translation("hub reference types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count hub reference type",
 *     plural = "@count hub reference types"
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
 *   config_prefix = "type",
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
}
