<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\cu_hub_consumer\HubReferenceTypeInterface;

/**
 * Defines the Media type configuration entity.
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
 *   },
 *   links = {
 *     "add-form" = "/admin/config/services/cu_hub_consumer/reference-types/add",
 *     "edit-form" = "/admin/config/services/cu_hub_consumer/reference-types/{hub_reference_type}",
 *     "delete-form" = "/admin/config/services/cu_hub_consumer/reference-types/{hub_reference_type}/delete",
 *     "collection" = "/admin/config/services/cu_hub_consumer/reference-types",
 *   },
 * )
 */
class HubReferenceType extends ConfigEntityBundleBase implements HubReferenceTypeInterface {

  /**
   * The machine name of this media type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the media type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this media type.
   *
   * @var string
   */
  protected $description;

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

}
