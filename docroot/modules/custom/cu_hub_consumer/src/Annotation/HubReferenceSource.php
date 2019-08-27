<?php

namespace Drupal\cu_hub_consumer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a hub entity source plugin annotation object.
 *
 * Hub entity sources are responsible for implementing all the logic for dealing
 * with a particular type of hub entity. They provide various universal and
 * type-specific metadata about hub entity of the type they handle.
 *
 * Plugin namespace: Plugin\cu_hub_consumer\Plugin\cu_hub_consumer\Source
 *
 * @see \Drupal\cu_hub_consumer\HubReferenceSourceInterface
 * @see \Drupal\cu_hub_consumer\HubReferenceSourceBase
 * @see \Drupal\cu_hub_consumer\HubReferenceSourceManager
 * @see hook_hub_entity_source_info_alter()
 * @see plugin_api
 *
 * @Annotation
 */
class HubReferenceSource extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the hub entity source.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A brief description of the hub entity source.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description = '';

  /**
   * The field types that can be used as a source field for this hub entity source.
   *
   * @var string[]
   */
  public $allowed_field_types = [];

  /**
   * The metadata attribute name to provide the default name.
   *
   * @var string
   */
  public $default_name_metadata_attribute = 'default_name';

  /**
   * The metadata attributes for this hub entity source.
   *
   * @var string[]
   */
  public $metadata_attributes = [];

  /**
   * The hub type id that this source represents.
   *
   * @var string
   */
  public $hub_type_id = '';

  /**
   * List of resource fields that should get an automatic computed field exposed.
   *
   * @var array
   */
  public $exposed_fields = [];

}
