<?php

namespace Drupal\cu_hub_api\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'hub path alias' widget.
 *
 * @FieldWidget(
 *   id = "hub_path_alias",
 *   label = @Translation("Hub path alias"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class HubPathWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();

    $element += [
      '#element_validate' => [[get_class($this), 'validateFormElement']],
    ];

    $element['value'] = [
      '#type' => 'textfield',
      '#title' => $element['#title'],
      '#default_value' => $items[$delta]->value,
      '#required' => $element['#required'],
      '#maxlength' => 255,
      '#description' => $this->t('Specify a path by which this data can be accessed. For example, type "/program/new-program" when writing a program degree page.'),
    ];

    return $element;
  }

  /**
   * Form element validation handler for URL alias form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateFormElement(array &$element, FormStateInterface $form_state) {
    // Trim the submitted value of whitespace and slashes.
    $alias = rtrim(trim($element['value']['#value']), " \\/");
    if (!empty($alias)) {
      $form_state->setValueForElement($element['value'], $alias);

      // Validate that the submitted alias does not exist yet.
      // TODO: how might we prevent duplicates? Would need to key off both field_hub_site and this field.
      //$is_exists = \Drupal::service('path.alias_storage')->aliasExists($alias, $element['langcode']['#value'], $element['source']['#value']);
      //if ($is_exists) {
      //  $form_state->setError($element['alias'], t('The alias is already in use.'));
      //}
    }

    if ($alias && $alias[0] !== '/') {
      $form_state->setError($element['value'], t('The alias needs to start with a slash.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element['value'];
  }

}
