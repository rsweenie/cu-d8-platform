<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Plugin implementation of the 'hub_resource' field type.
 *
 * @FieldType(
 *   id = "hub_resource",
 *   label = @Translation("Hub: Resource"),
 *   description = @Translation("This field stores resource data."),
 *   category = @Translation("Text"),
 *   default_widget = "text_textarea",
 *   default_formatter = "hub_resource"
 * )
 */
class HubResourceItem extends FieldItemBase implements FieldItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    //$properties = parent::propertyDefinitions($field_definition);

    $properties['value'] = DataDefinition::create('map')
      ->setLabel(t('Hub: Resource data'))
      ->setDescription(t('The resource data provided by hub.'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'big',
        ],
      ],
    ];
  }

}
