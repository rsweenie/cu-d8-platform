<?php

namespace Drupal\cu_hub_consumer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a hub resource field type plugin annotation object.
 *
 * Plugin namespace: Plugin\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceField
 *
 * @see \Drupal\cu_hub_consumer\Hub\ResourceFieldItemInterface
 * @see \Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase
 * @see \Drupal\cu_hub_consumer\Hub\ResourceFieldTypeManager
 * @see hook_hub_entity_source_info_alter()
 * @see plugin_api
 *
 * @Annotation
 */
class HubResourceFieldType extends Plugin {

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

}
