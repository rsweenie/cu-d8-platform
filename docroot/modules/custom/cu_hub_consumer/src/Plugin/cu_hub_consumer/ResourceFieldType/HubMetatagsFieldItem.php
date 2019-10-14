<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;
use Drupal\Component\Serialization\Json;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "hub_metatags",
 *   label = @Translation("Metatags"),
 *   description = @Translation("Metatags."),
 * )
 */
class HubMetatagsFieldItem extends ResourceFieldItemBase {

  protected $mapped_values = [];

  /**
   * {@inheritdoc}
   */
  public function setValue($value) {
    $this->value = $value;

    // Convert the raw values into a nice map.
    $this->mapped_values = [];
    if (is_array($value)) {
      foreach ($value as $item) {
        if (isset($item['tag']) && isset($item['attributes'])) {
          switch ($item['tag']) {
            case 'meta':
              $this->mapped_values[$item['tag'] . '_' . $item['attributes']['name']] = $item['attributes']['content'];
              break;

            case 'link':
              $this->mapped_values[$item['tag'] . '_' . $item['attributes']['rel']] = $item['attributes']['href'];
              break;

            default:
              $this->mapped_values[$item['tag']] = $item['attributes'];
              break;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->value);
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property_name) {
    return isset($this->mapped_values[$property_name]) ? $this->mapped_values[$property_name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($property_name) {
    return isset($this->mapped_values[$property_name]);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    if (!$this->isEmpty()) {
      return (string) Json::encode($this->mapped_values);
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [
      '#markup' => (string) $this,
    ];

    return $elements;
  }

}
