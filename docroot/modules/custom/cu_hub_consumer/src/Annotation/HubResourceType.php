<?php

namespace Drupal\cu_hub_consumer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a hub resource type plugin annotation object.
 *
 * Plugin namespace: Plugin\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceType
 *
 * @see \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface
 * @see \Drupal\cu_hub_consumer\Hub\ResourceTypeBase
 * @see \Drupal\cu_hub_consumer\Hub\ResourceTypeManager
 * @see hook_hub_entity_source_info_alter()
 * @see plugin_api
 *
 * @Annotation
 */
class HubResourceType extends Plugin {

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
   * The hub type id.
   *
   * @var string
   */
  public $hub_type_id = '';

  /**
   * The base URI for this resource type.
   *
   * @var string
   */
  public $hub_path = '';

  /**
   * Map of attribute data types
   *
   * @var array
   */
  public $attribute_map = [];

}
