<?php

namespace Drupal\cu_hub_api\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'cu_hub_api_field_metadata' field type.
 *
 * @FieldType(
 *   id = "cu_hub_api_field_metadata",
 *   label = @Translation("Field metadata"),
 *   description = @Translation("Computed field metadata"),
 *   no_ui = TRUE,
 *   list_class = "\Drupal\cu_hub_api\Plugin\Field\FieldType\FieldMetadataFieldItemList",
 * )
 */
class FieldMetadataFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['field'] = DataDefinition::create('string')
      ->setLabel(t('Field'))
      ->setRequired(TRUE);
    $properties['attributes'] = DataDefinition::create('any')
      ->setLabel(t('Attributes'))
      ->setRequired(TRUE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('attributes')->getValue();
    return $value === NULL || $value === serialize([]);
  }

}
