<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'hub_text_long' field type.
 *
 * @FieldType(
 *   id = "hub_text_long",
 *   label = @Translation("Hub: Text (formatted, long)"),
 *   description = @Translation("This field stores a long text with a text format and preprocessed text."),
 *   category = @Translation("Text"),
 *   default_widget = "text_textarea",
 *   default_formatter = "hub_text_processed"
 * )
 */
class HubTextLongItem extends TextItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['hub_processed'] = DataDefinition::create('string')
      ->setLabel(t('Hub: Processed text'))
      ->setDescription(t('The text with the text format preapplied by hub.'));

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
        'hub_processed' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'format' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'format' => ['format'],
      ],
    ];
  }

}
