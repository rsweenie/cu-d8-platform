<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "text_long",
 *   label = @Translation("Text (long)"),
 *   description = @Translation("Text (long)."),
 * )
 */
class TextLongFieldItem extends ArrayFieldItemBase {

  public function mainProperty() {
    return 'processed';
  }

}
