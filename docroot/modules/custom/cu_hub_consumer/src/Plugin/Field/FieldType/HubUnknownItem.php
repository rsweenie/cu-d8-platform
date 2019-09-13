<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Plugin implementation of the 'hub_unknown' field type.
 *
 * @FieldType(
 *   id = "hub_unknown",
 *   label = @Translation("Hub: Unknown type"),
 *   description = @Translation("This field represents an unknown hub field type."),
 *   category = @Translation("Text"),
 *   default_widget = "text_textarea",
 *   default_formatter = "hub_unknown"
 * )
 */
class HubUnknownItem extends FieldItemBase implements FieldItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    //$properties = parent::propertyDefinitions($field_definition);

    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Hub: Raw JSON data'))
      ->setDescription(t('The raw JSON data provided by hub.'));

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
