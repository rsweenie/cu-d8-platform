<?php

namespace Drupal\cu_hub_api\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;

/**
 * @FieldWidget(
 *   id = "cu_hub_api_site_path",
 *   label = @Translation("Select with path alias"),
 *   description = @Translation("An select field with an associated path alias."),
 *   field_types = {
 *     "cu_hub_api_site_path"
 *   }
 * )
 */
class HubSitePathWidget extends OptionsSelectWidget {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element[$this->column] = $element;
    $element[$this->column]['#default_value'] = empty($items[$delta]->{$this->column}) ? '_none' : $items[$delta]->{$this->column};
    $element[$this->column]['#multiple'] = FALSE;
    unset($element['#type']);
    unset($element[$this->column]['#element_validate']);

    $element['alias'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path alias'),
      '#default_value' => $items[$delta]->alias,
      '#maxlength' => 255,
      '#description' => $this->t('Specify a path by which this data can be accessed. For example, type "/program/new-program" when writing a program degree page.'),
      '#weight' => 10,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(array $element, FormStateInterface $form_state) {
    if ($element[$element['#key_column']]['#required'] && $element[$element['#key_column']]['#value'] == '_none') {
      $form_state->setError($element, t('@name field is required.', ['@name' => $element[$element['#key_column']]['#title']]));
    }

    if (is_array($element[$element['#key_column']]['#value'])) {
      $values = array_values($element[$element['#key_column']]['#value']);
    }
    else {
      $values = [$element[$element['#key_column']]['#value']];
    }

    // Filter out the 'none' option. Use a strict comparison, because
    // 0 == 'any string'.
    $index = array_search('_none', $values, TRUE);
    if ($index !== FALSE) {
      unset($values[$index]);
    }

    if (!is_array($element[$element['#key_column']]['#value'])) {
      //$items = reset($items);
      $values = reset($values);
    }
    $form_state->setValueForElement($element[$element['#key_column']], $values);

    // Trim the submitted value of whitespace and slashes.
    $alias = rtrim(trim($element['alias']['#value']), " \\/");
    if (!empty($alias)) {
      $form_state->setValueForElement($element['alias'], $alias);
    }

    if ($alias && $alias[0] !== '/') {
      $form_state->setError($element['alias'], t('The alias needs to start with a slash.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {
      if (empty($value[$this->column])) {
        unset($values[$key][$this->column]);
      }
      if (empty($value['alias'])) {
        unset($values[$key]['alias']);
      }
    }

    return $values;
  }

}
