<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\Component\Utility\DiffArray;
use Drupal\cu_hub_consumer\Hub\ResourceTypeInterface;

/**
 * Defines the hub resource type info configuration entity.
 *
 * @ConfigEntityType(
 *   id = "hub_resource_type_definition",
 *   label = @Translation("Hub resource type info"),
 *   handlers = {
 *     "list_builder" = "Drupal\cu_hub_consumer\HubResourceTypeInfoListBuilder",
 *   },
 *   admin_permission = "administer cu hub consumer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "type_id",
 *   },
 *   config_prefix = "resource_type_definition",
 *   config_export = {
 *     "id",
 *     "type_id",
 *     "fields",
 *   },
 * )
 */
class HubResourceTypeDefinition extends ConfigEntityBase implements HubResourceTypeDefinitionInterface {

  /**
   * The machine name of this hub resource type.
   *
   * @var string
   */
  protected $id;

  /**
   * The hub resource type as given by JSON API.
   *
   * @var string
   */
  protected $type_id;

  /**
   * Information about the fields.
   *
   * @var array
   */
  protected $fields;

  /** 
   * Information about the fields before being changed.
   * 
   * This is used when updating bundles, etc.
   */
  protected $orig_fields;

  /**
   * {@inheritdoc}
   */
  public function set($property_name, $value) {
    if ($property_name == 'fields') {
      $this->orig_fields = $this->fields;
    }
    return parent::set($property_name, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function hasChanged() {
    /*
    // If orig_fields is not even set then we can assume nothing has changed.
    if (!isset($this->orig_fields)) {
      return FALSE;
    }

    // If we find differences in the arrays, we've changed.
    if (DiffArray::diffAssocRecursive($this->fields, $this->orig_fields)) {
      return TRUE;
    }
    if (DiffArray::diffAssocRecursive($this->orig_fields, $this->fields)) {
      return TRUE;
    }
    */

    $changes = $this->getChanges();
    return !empty($changes);
  }

  /**
   * {@inheritdoc}
   */
  public function getChanges() {
    $changes = [];

    // If orig_fields is not even set then we can assume nothing has changed.
    if (!isset($this->orig_fields)) {
      return $changes;
    }

    // If we find differences in the arrays, we've changed.
    if ($diffs = DiffArray::diffAssocRecursive($this->fields, $this->orig_fields)) {
      $changes = array_merge($changes, array_keys($diffs));
    }
    if ($diffs = DiffArray::diffAssocRecursive($this->orig_fields, $this->fields)) {
      $changes = array_merge($changes, array_keys($diffs));
    }
    $changes = array_unique($changes);

    return $changes;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldInfo($field_name) {
    return isset($this->fields[$field_name]) ? $this->fields[$field_name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setFieldInfo($field_name, $info) {
    if (!isset($this->fields)) {
      $this->fields = [];
    }
    if (!isset($this->orig_fields)) {
      $this->orig_fields = $this->fields;
    }
    $this->fields[$field_name] = $info;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (!$update || !empty($this->orig_fields)) {
      // @TODO: We may need to rebuild hub_resource bundle information.
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // @TODO: We may need to rebuild hub_resource bundle information.
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
