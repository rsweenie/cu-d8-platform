<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;

/**
 * The hub reference entity class.
 *
 * This entity type represents data that is stored in the Creighton hub.
 *
 * @ContentEntityType(
 *   id = "hub_reference",
 *   label = @Translation("Hub Reference Entity"),
 *   label_singular = @Translation("hub reference item"),
 *   label_plural = @Translation("hub reference items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count hub reference item",
 *     plural = "@count hub reference items"
 *   ),
 *   bundle_label = @Translation("Hub reference type"),
 *   bundle_entity_type = "hub_reference_type",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cu_hub_consumer\HubReferenceListBuilder",
 *     "form" = {
 *       "default" = "Drupal\cu_hub_consumer\Form\HubReferenceForm",
 *       "edit" = "Drupal\cu_hub_consumer\Form\HubReferenceForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "storage" = "Drupal\cu_hub_consumer\HubReferenceStorage",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "hub_reference",
 *   data_table = "hub_reference_field_data",
 *   translatable = FALSE,
 *   fieldable = TRUE,
 *   admin_permission = "administer cu hub references",
 *   field_ui_base_route = "entity.hub_reference_type.edit_form",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/hub-reference/{hub_reference}",
 *     "edit-form" = "/hub-reference/{hub_reference}/edit",
 *     "delete-form" = "/hub-reference/{hub_reference}/delete",
 *     "delete-multiple-form" = "/admin/config/services/cu_hub_consumer/references/delete",
 *     "add-page" = "/admin/config/services/cu_hub_consumer/references/add",
 *     "add-form" = "/admin/config/services/cu_hub_consumer/references/add/{hub_reference_type}",
 *     "collection" = "/admin/config/services/cu_hub_consumer/references",
 *   },
 * )
 */
class HubReference extends ContentEntityBase implements HubReferenceInterface {

  use EntityChangedTrait; // Implements methods defined by EntityChangedInterface.
  use EntityPublishedTrait; // Implements methods defined by EntityPublishedInterface.

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The hub reference title.'));

    $fields['hub_uuid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hub UUID'))
      ->setSetting('max_length', 128)
      ->setSetting('is_ascii', TRUE)
      ->setSetting('case_sensitive', false)
      ->setDescription(t('The entity UUID on hub.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time the hub reference item was created.'))
      ->setDefaultValueCallback(static::class . '::getRequestTime')
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time the hub reference item was last updated.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    $title = $this->getEntityKey('label');

    if (empty($title)) {
      $hub_reference_source = $this->getSource();
      return $hub_reference_source->getMetadata($this, $hub_reference_source->getPluginDefinition()['default_name_metadata_attribute']);
    }

    return $title;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getTitle();
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    return $this->set('title', $title);
  }

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    return $this->type->entity->getSource();
  }

  /**
   * Determines if the source field value has changed.
   *
   * @return bool
   *   TRUE if the source field value changed, FALSE otherwise.
   *
   * @internal
   */
  protected function hasSourceFieldChanged() {
    $source_field_name = $this->getSource()->getConfiguration()['source_field'];
    $current_items = $this->get($source_field_name);
    return isset($this->original) && !$current_items->equals($this->original->get($source_field_name));
  }

  /**
   * Generates a unique hash for identification purposes.
   *
   * @param string $hub_type
   *   Hub type of the reference.
   * @param string $hub_uuid
   *   Hub UUID of the reference.
   *
   * @return string
   *   Base 64 hash.
   */
  public static function generateHash($hub_type, $hub_uuid) {
    $hash = [
      'hub_type' => mb_strtolower($hub_type),
      'hub_uuid' => mb_strtolower($hub_uuid),
    ];

    redirect_sort_recursive($hash, 'ksort');
    return Crypt::hashBase64(serialize($hash));
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    //$values += [
    //  'type' => 'cu_hub_reference',
    //];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage_controller) {
    parent::preSave($storage_controller);
    //$this->set('hash', HubReference::generateHash($this->hub_type, $this->hub_uuid));
  }

  /**
   * Sets the hub reference entity's field values from the source's metadata.
   *
   * Fetching the metadata could be slow (e.g., if requesting it from a remote
   * API), so this is called by \Drupal\cu_hub_consumer\HubReferenceStorage::save() prior to it
   * beginning the database transaction, whereas static::preSave() executes
   * after the transaction has already started.
   */
  public function prepareSave() {
    // @todo This code might be performing a fair number of HTTP requests. This is dangerously
    // brittle and should probably be handled by a queue, to avoid doing HTTP
    // operations during entity save. See
    // https://www.drupal.org/project/drupal/issues/2976875 for more.

    // In order for metadata to be mapped correctly, $this->original must be
    // set. However, that is only set once parent::save() is called, so work
    // around that by setting it here.
    if (!isset($this->original) && $id = $this->id()) {
      $this->original = $this->entityTypeManager()
        ->getStorage('hub_reference')
        ->loadUnchanged($id);
    }

    \Drupal::logger('cu_hub_consumer')->notice(print_r($this->bundle(), TRUE));

    $hub_reference_source = $this->getSource();
    foreach ($this->translations as $langcode => $data) {
      if ($this->hasTranslation($langcode)) {
        $translation = $this->getTranslation($langcode);
        // Try to set fields provided by the hub reference source and mapped in
        // hub reference type config.
        foreach ($translation->type->entity->getFieldMap() as $metadata_attribute_name => $entity_field_name) {
          if ($translation->hasField($entity_field_name)) {
            $translation->set($entity_field_name, $hub_reference_source->getMetadata($translation, $metadata_attribute_name));
          }
        }

        // Try to set a default name for this hub reference item if no name is provided.
        if ($translation->get('title')->isEmpty()) {
          $translation->setTitle($translation->getTitle());
        }
      }
    }

    \Drupal::logger('cu_hub_consumer')->notice(print_r($this->bundle(), TRUE));
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $hub_reference_source = $this->getSource();

    /* @TODO: do we need this?
    if ($hub_reference_source instanceof MediaSourceEntityConstraintsInterface) {
      $entity_constraints = $hub_reference_source->getEntityConstraints();
      $this->getTypedData()->getDataDefinition()->setConstraints($entity_constraints);
    }

    if ($hub_reference_source instanceof MediaSourceFieldConstraintsInterface) {
      $source_field_name = $hub_reference_source->getConfiguration()['source_field'];
      $source_field_constraints = $hub_reference_source->getSourceFieldConstraints();
      $this->get($source_field_name)->getDataDefinition()->setConstraints($source_field_constraints);
    }
    */

    return parent::validate();
  }

  /**
   * {@inheritdoc}
   */
  public static function getRequestTime() {
    return \Drupal::time()->getRequestTime();
  }

}
