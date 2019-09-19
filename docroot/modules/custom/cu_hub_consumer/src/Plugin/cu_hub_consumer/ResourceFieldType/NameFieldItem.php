<?php

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Annotation\HubResourceFieldType;

/**
 * Generic URI resource field.
 *
 * @HubResourceFieldType(
 *   id = "name",
 *   label = @Translation("Name"),
 *   description = @Translation("Name."),
 * )
 */
class NameFieldItem extends ArrayFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function mainPropertyName() {
    return 'name';
  }

  /**
   * {@inheritDoc}
   */
  public function view() {
    return [];
  }
}
