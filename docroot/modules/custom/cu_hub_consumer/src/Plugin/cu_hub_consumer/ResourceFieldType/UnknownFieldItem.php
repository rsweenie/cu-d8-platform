<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;
use Drupal\Component\Serialization\Json;

/**
 * Unknown resource field.
 * 
 * @HubResourceFieldType(
 *   id = "hub_unknown",
 *   label = @Translation("Unknown"),
 *   description = @Translation("Unknown."),
 * )
 */
class UnknownFieldItem extends ResourceFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    return [];
  }

}
