<?php

namespace Drupal\cu_hub_api\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'cu_hub_api_field_metadata' field type.
 *
 * @FieldType(
 *   id = "cu_hub_api_site_path",
 *   label = @Translation("Hub site reference with path alias"),
 *   description = @Translation("An entity field containing an entity reference with a path unique to the referenced entity."),
 *   category = @Translation("Reference"),
 *   default_widget = "cu_hub_api_site_path",
 *   default_formatter = "cu_hub_api_site_path_label",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 *   constraints = {"HubSitePath" = {}},
 * )
 */
class HubSitePathItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['alias'] = DataDefinition::create('string')
      ->setLabel(t('Path alias'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['alias'] = [
      'type' => 'varchar',
      'length' => '255',
      'not null' => TRUE,
      'default' => '',
    ];

    return $schema;
  }

}
