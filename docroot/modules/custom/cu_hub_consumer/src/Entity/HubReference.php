<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\cu_hub_consumer\HubReferenceInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * The hub reference entity class.
 *
 * This entity type represents data that is stored in the Creighton hub.
 *
 * @ContentEntityType(
 *   id = "hub_reference",
 *   label = @Translation("Hub Reference Entity"),
 *   bundle_label = @Translation("Hub Reference Entity type"),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "form" = {
 *       "default" = "Drupal\cu_hub_consumer\Form\HubReferenceForm",
 *       "delete" = "Drupal\cu_hub_consumer\Form\HubReferenceDeleteForm",
 *       "edit" = "Drupal\cu_hub_consumer\Form\HubReferenceForm"
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "storage_schema" = "\Drupal\cu_hub_consumer\HubReferenceStorageSchema",
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
 *     "id" = "rid",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "bundle" = "bundle",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/hub-reference/{hub_reference}",
 *     "edit-form" = "/hub-reference/{hub_reference}/edit",
 *     "delete-form" = "/hub-reference/{hub_reference}/delete",
 *     "delete-multiple-form" = "/admin/config/services/cu_hub_consumer/references/delete",
 *     "add-form" = "/admin/config/services/cu_hub_consumer/references/add/{hub_reference_type}",
 *     "add-page" = "/admin/config/services/cu_hub_consumer/references/add",
 *     "collection" = "/admin/config/services/cu_hub_consumer/references",
 *   },
 * )
 */
class HubReference extends ContentEntityBase implements HubReferenceInterface {

  use EntityChangedTrait; // Implements methods defined by EntityChangedInterface.

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['rid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Reference ID'))
      ->setDescription(t('The hub reference ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The record UUID.'))
      ->setReadOnly(TRUE);

    $fields['bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The hub reference bundle.'));

    // The hash field is used to check for duplicates.
    $fields['hash'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hash'))
      ->setSetting('max_length', 64)
      ->setDescription(t('The reference hash.'));

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The hub reference title.'));

    // The hash field is used to check for duplicates.
    $fields['hub_uuid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hub UUID'))
      ->setSetting('max_length', 128)
      ->setSetting('is_ascii', TRUE)
      ->setSetting('case_sensitive', false)
      ->setDescription(t('The entity UUID on hub.'));

    $fields['hub_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hub type'))
      ->setDescription(t('The entity type on hub.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ));

    $fields['hub_bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hub bundle'))
      ->setDescription(t('The entity bundle on hub.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ));

    /*
    $fields['redirect_redirect'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Redirect target'))
      ->setRequired(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_DISABLED,
      ])
      ->setDisplayOptions('form', [
        'type' => 'link',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE);
    */

    // Website field for the advertiser.
    $fields['hub_uri'] = BaseFieldDefinition::create('string')
        ->setLabel(t("The advertiser's website"))
        ->setDescription(t('The website address of the advertiser.'))
        ->setSettings(array(
          'default_value' => '',
          'max_length' => 2083,
          'text_processing' => 0,
        ))
        // https://drupalwatchdog.com/volume-5/issue-2/introducing-drupal-8s-entity-validation-api
        ->addPropertyConstraints('value', ['Url' => []]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The date when the hub reference was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The date when the hub reference was changed.'));

    return $fields;
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
    $this->set('hash', HubReference::generateHash($this->hub_type, $this->hub_uuid));
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
  /*
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $entity = $this->entity;
    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The hub reference %hub_reference has been updated.', ['%hub_reference' => $entity->toLink()->toString()]));
    } else {
      drupal_set_message($this->t('The hub reference %hub_reference has been added.', ['%hub_reference' => $entity->toLink()->toString()]));
    }

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $status;
  }
  */

}
