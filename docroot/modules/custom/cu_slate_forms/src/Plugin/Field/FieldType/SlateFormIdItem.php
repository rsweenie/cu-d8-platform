<?php

namespace Drupal\cu_slate_forms\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Component\Utility\Random;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'slate_form_id' field type.
 *
 * @FieldType(
 *   id = "slate_form_id",
 *   label = @Translation("Slate form"),
 *   description = @Translation("Stores a Slate form ID."),
 *   default_widget = "slate_form_default",
 *   default_formatter = "slate_form_default",
 *   constraints = {"SlateFormIdFormat" = {}}
 * )
 */
class SlateFormIdItem extends FieldItemBase implements SlateFormIdItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'id' => [
          'type' => 'varchar',
          'length' => 256,
        ],
        'redirect' => [
          'description' => 'The URI of the redirect.',
          'type' => 'varchar',
          'length' => 2048,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['id'] = DataDefinition::create('string')
      ->setLabel(t('Slate form ID'))
      ->setRequired(TRUE);

    $properties['redirect'] = DataDefinition::create('uri')
      ->setLabel(t('Redirect URI'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get(static::mainPropertyName())->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();

    $values['id'] = \Drupal::service('uuid')->generate();

    // Set of possible top-level domains.
    $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
    // Set random length for the domain name.
    $domain_length = mt_rand(7, 15);

    $values['redirect'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];

    return $values;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'id';
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Treat the values as property value of the main property, if no array is
    // given.
    if (isset($values) && !is_array($values)) {
      $values = [static::mainPropertyName() => $values];
    }

    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectUrl() {
    return Url::fromUri($this->redirect, ['absolute' => TRUE]);
  }

}
