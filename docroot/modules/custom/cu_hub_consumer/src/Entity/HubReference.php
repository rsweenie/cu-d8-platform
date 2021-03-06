<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\taxonomy\Entity\Term;
use Drupal\pathauto\PathautoState;
use Drupal\cu_hub_consumer\Hub\Resource;
use Drupal\cu_hub_consumer\Hub\ResourceFieldItemListInterface;
use Drupal\cu_hub_consumer\Hub\ResourceRelationshipListInterface;
use Drupal\cu_hub_consumer\HubFieldStorageDefinition;
use Drupal\cu_hub_consumer\Hub\ResourceInterface;
use Drupal\cu_hub_consumer\Hub\ResourceTypeInterface;

/**
 * The hub reference entity class.
 *
 * This entity type represents data that is stored in the Creighton hub.
 *
 * @ContentEntityType(
 *   id = "hub_reference",
 *   label = @Translation("Hub Reference"),
 *   label_singular = @Translation("Hub Reference item"),
 *   label_plural = @Translation("Hub Reference items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Hub Reference item",
 *     plural = "@count Hub Reference items"
 *   ),
 *   bundle_label = @Translation("Hub Reference type"),
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
 *     "views_data" = "Drupal\cu_hub_consumer\HubReferenceViewsData",
 *     "storage" = "Drupal\cu_hub_consumer\HubReferenceStorage",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\cu_hub_consumer\HubReferenceAccessControlHandler",
 *   },
 *   base_table = "hub_reference",
 *   translatable = FALSE,
 *   fieldable = TRUE,
 *   admin_permission = "administer cu hub references",
 *   field_ui_base_route = "entity.hub_reference_type.edit_form",
 *   common_reference_target = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/hub-reference/{hub_reference}",
 *     "delete-form" = "/hub-reference/{hub_reference}/delete",
 *     "edit-form" = "/hub-reference/{hub_reference}/edit"
 *   },
 * )
 */
class HubReference extends ContentEntityBase implements HubReferenceInterface {

  use EntityChangedTrait; // Implements methods defined by EntityChangedInterface.
  use EntityPublishedTrait; // Implements methods defined by EntityPublishedInterface.

  /**
   * An unserialized version of the JSON data from hub.
   *
   * @var array
   */
  protected $unserializedHubJson;

  /**
   * The hub data loaded into a resource object.
   *
   * @var \Drupal\cu_hub_consumer\Hub\Resource
   */
  protected $resourceObject;

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
      ->setDescription(t('The entity UUID on hub.'))
      ->setSetting('max_length', 128)
      ->setSetting('is_ascii', TRUE)
      ->setSetting('case_sensitive', FALSE);

    $fields['hub_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Hub Data'))
      ->setDescription(t('The JSON data that was last pulled from hub.'))
      ->setSetting('case_sensitive', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time the hub reference item was created.'))
      ->setDefaultValueCallback(static::class . '::getRequestTime');
      /*
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
      */
  
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time the hub reference item was last updated.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  /*
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    $fields = parent::bundleFieldDefinitions($entity_type, $bundle, $base_field_definitions);

    if ($hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($bundle)) {
      if ($reference_source = $hub_ref_type->getSource()) {
        $resource_type = $reference_source->getResourceType();

        $field_types = \Drupal::service('plugin.manager.field.field_type');

        if ($hub_fields = $resource_type->getHubFields()) {
          $field_prefix = HubReferenceInterface::HUB_FIELD_PREFIX;
          foreach ($hub_fields as $field_name => $field_info) {
            // Make sure the plugin actually exists.
            if ($field_def = $field_types->getDefinition($field_info['type'], FALSE)) {
            //if (!in_array($field_info['type'], ['metatags', 'hub_unknown', 'hub_resource'])) {
              //$field_defaults = $field_types->getDefaultFieldSettings($field_info['type']);
              //print_r($field_def);

              $field_definition = HubFieldStorageDefinition::create($field_info['type']) //BaseFieldDefinition::createFromFieldStorageDefinition($field->getFieldStorageDefinition())
                ->setName($field_prefix . $field_name)
                ->setReadOnly(TRUE)
                ->setComputed(TRUE)
                ->setLabel('Hub: ' . $field_name)
                ->setDisplayConfigurable('view', TRUE)
                ->setDisplayOptions('view', [
                  'region' => 'hidden',
                  'type' => $field_def['default_formatter']
                ]);
              $fields[$field_prefix . $field_name] = $field_definition;
            }
          }
        }
      }
    }

    // Clear the list of blocks when a new bundle is created.
    //\Drupal::service('plugin.manager.block')->clearCachedDefinitions();

    return $fields;
  }
  */

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
   * {@inheritdoc}
   */
  public function getResourceTypeId() {
    if ($hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($this->bundle())) {
      if ($reference_source = $hub_ref_type->getSource()) {
        return $reference_source->getResourceTypeId();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceType() {
    if ($hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($this->bundle())) {
      if ($reference_source = $hub_ref_type->getSource()) {
        return $reference_source->getResourceType();
      }
    }
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
  public function getHubUUID() {
    return $this->get('hub_uuid')->first()->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getHubJson() {
    return $this->get('hub_data')->first()->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getHubData() {
    if (!isset($this->unserializedHubJson)) {
      $this->unserializedHubJson = Json::decode($this->getHubJSON());
    }
    return $this->unserializedHubJson;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceObj() {
    if (!isset($this->resourceObject)) {
      $this->resourceObject = NULL;
      if ($hub_data = $this->getHubData()) {
        if ($resource_type = $this->getSource()->getResourceType()) {
          $this->resourceObject = Resource::createFromJson($resource_type, $hub_data);
        }
      }
    }
    return $this->resourceObject;
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

    $hub_reference_source = $this->getSource();
    foreach ($this->translations as $langcode => $data) {
      if ($this->hasTranslation($langcode)) {
        $translation = $this->getTranslation($langcode);
        // Try to set fields provided by the hub reference source and mapped in
        // hub reference type config.
        foreach ($translation->type->entity->getFieldMap() as $metadata_attribute_name => $entity_field_name) {
          if ($translation->hasField($entity_field_name)) {
            $field_def = $translation->get($entity_field_name)->getFieldDefinition();
            $field_type = $field_def->getType();
            $metadata_values = $hub_reference_source->getMetadata($translation, $metadata_attribute_name);
            
            switch ($field_type) {
              case 'entity_reference':
                $this->handleEntityReferencePrepareSave($translation, $entity_field_name, $metadata_values);
                break;

              case 'string':
              case 'string_long':
                if (!empty($metadata_values) && !isset($metadata_values['value'])) {
                  if ($resource = $this->getResourceObj()) {
                    if (isset($resource->{$metadata_attribute_name})) {
                      $metadata_values = ['value' => $resource->{$metadata_attribute_name}->getString()];
                    }
                  }
                }
                $translation->set($entity_field_name, $metadata_values);
                break;

              default:
                $translation->set($entity_field_name, $metadata_values);
                break;
            }
          }
        }

        // Try to set a default name for this hub reference item if no name is provided.
        if ($translation->get('title')->isEmpty()) {
          $translation->setTitle($translation->getTitle());
        }

        if ($path = $hub_reference_source->getMetadata($translation, 'path')) {
          $translation->set('path', [
            'alias' => $path,
            'pathauto' => PathautoState::SKIP,
          ]);
        }
      }
    }
  }

  protected function handleEntityReferencePrepareSave(ContentEntityInterface $entity, $entity_field_name, $values) {
    $field_def = $entity->get($entity_field_name)->getFieldDefinition();
    $target_type = $field_def->getSetting('target_type');

    if ($target_type == 'taxonomy_term') {
      if ($field_def->getSetting('handler') != 'default:taxonomy_term') {
        // We only know how to deal with the one handler.
        return;
      }

      $target_bundles = $field_def->getSetting('handler_settings')['target_bundles'];
      if (count($target_bundles) > 1) {
        // If there are more than one bundle then we don't know which vocabulary to use.
        return;
      }
      $target_bundle = reset($target_bundles);

      // Sanity check.
      if (is_array($values)) {
        $field_values = [];
        foreach ($values as $value) {
          if (isset($value['resource'])) {
            if ($value['resource'] instanceof ResourceInterface) {
              $term_name = trim($value['resource']->label());

              if (!$term_name) {
                continue;
              }

              $term_tooltip = NULL;
              if (isset($value['resource']->field_term_tooltip)) {
                $term_tooltip = $value['resource']->field_term_tooltip->getString();
              }

              $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name]);
              $term = reset($terms);

              // If term doesn't exist yet, try to create it.
              if (!$term) {
                $term = Term::create([
                  'name' => $term_name, 
                  'vid' => $target_bundle,
                ]);

                if ($term_tooltip) {
                  $term->set('field_term_tooltip', $term_tooltip);
                }

                $term->save();
              }
              else {
                if ($term_tooltip) {
                  $term->set('field_term_tooltip', $term_tooltip);
                  $term->save();
                }
              }

              if ($tid = $term->id()) {
                $field_values[] = ['target_id' => $tid];
              }
            }
          }
        }
        $entity->set($entity_field_name, $field_values);
      }
    }
    elseif ($target_type == 'hub_reference') {
      if ($field_def->getSetting('handler') != 'default:hub_reference') {
        // We only know how to deal with the one handler.
        return;
      }

      $target_bundles = $field_def->getSetting('handler_settings')['target_bundles'];

      // Sanity check.
      if (is_array($values)) {
        $field_values = [];
        foreach ($values as $value) {
          $hub_refs = $this->entityTypeManager()->getStorage('hub_reference')->loadByProperties(['type' => $target_bundles, 'hub_uuid' => $value['value']]);
          foreach ($hub_refs as $hub_ref) {
            if ($hrid = $hub_ref->id()) {
              $field_values[] = ['target_id' => $hrid];
            }
          }
        }
        $entity->set($entity_field_name, $field_values);
      }
    }
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

  /**
   * {@inheritdoc}
   */
  public function mapHubFields() {
    $field_types = \Drupal::service('plugin.manager.field.field_type');

    if ($hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($this->bundle())) {
      if ($reference_source = $hub_ref_type->getSource()) {
        $resource_type = $reference_source->getResourceType();

        if ($hub_fields = $resource_type->getHubFields()) {
          $this->tryFetchLiveData();

          $field_prefix = HubReferenceInterface::HUB_FIELD_PREFIX;
          $resource_object = $this->getResourceObj();

          foreach ($hub_fields as $field_name => $field_info) {
            // Make sure the field plugin actually exists.
            if ($field_def = $field_types->getDefinition($field_info['type'], FALSE)) {
              if ($this->hasField($field_prefix . $field_name)) {
                if ($field_list = $resource_object->{$field_name}) {
                  if ($field_list instanceof ResourceFieldItemListInterface) {
                    $this->set($field_prefix . $field_name, $field_list->getFieldFriendlyValues());
                  }
                  elseif ($field_list instanceof ResourceRelationshipListInterface) {
                    $this->set($field_prefix . $field_name, $field_list->getFieldFriendlyValues());
                  }
                }
              }
            }
          }
        }
      }
    }

    return $this;
  }

  /**
   * Attempts to fetch live data from hub for preview purposes.
   *
   * @return void
   */
  protected function tryFetchLiveData() {
    $curr_user = \Drupal::currentUser();

    // Make sure we have permission to do this.
    if (!$curr_user || !$curr_user->hasPermission('refresh cu hub reference')) {
      return;
    }

    $hub_uuid = $this->getHubUUID();
    $resource_type = $this->getResourceType();
    $is_live = \Drupal::request()->query->get('live');

    // Make sure we have the params we need.
    if (!$hub_uuid || !$resource_type || !$is_live) {
      return;
    }

    if ($resource = $resource_type->fetchResource($hub_uuid)) {
      $this->unserializedHubJson = $resource->getJsonData();
      $this->resourceObject = $resource;
    }
  }

}
