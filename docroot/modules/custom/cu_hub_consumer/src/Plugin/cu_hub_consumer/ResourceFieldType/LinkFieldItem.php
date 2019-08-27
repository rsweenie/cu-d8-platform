<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "link",
 *   label = @Translation("Link"),
 *   description = @Translation("Link."),
 * )
 */
class LinkFieldItem extends ArrayFieldItemBase {

  public function mainProperty() {
    return 'uri';
  }
  
}
