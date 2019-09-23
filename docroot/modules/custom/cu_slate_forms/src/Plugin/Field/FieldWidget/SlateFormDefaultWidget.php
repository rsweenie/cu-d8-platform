<?php

namespace Drupal\cu_slate_forms\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\link\LinkItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'slate_form_default' widget.
 *
 * @FieldWidget(
 *   id = "slate_form_default",
 *   label = @Translation("Slate form"),
 *   field_types = {
 *     "slate_form_id"
 *   }
 * )
 */
class SlateFormDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'placeholder_id' => '',
      'placeholder_redirect' => '',
    ] + parent::defaultSettings();
  }

  /**
   * Form element validation handler for the 'title' element.
   *
   * Conditionally requires the link title if a URL value was filled in.
   */
  public static function validateIdElement(&$element, FormStateInterface $form_state, $form) {
    if (empty($element['id']['#value'])) {
      return;
    }

    if (preg_match('/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/', $element['id']['#value'], $matches)) {
      $form_id = trim($matches[0]);
      $form_state->setValueForElement($element['id'], $form_id);
    }
    else {
      // We expect the field name placeholder value to be wrapped in t() here,
      // so it won't be escaped again as it's already marked safe.
      //$form_state->setError($element['id'], t('@id field is required if there is @redirect input.', ['@id' => $element['id']['#title'], '@redirect' => $element['redirect']['#title']]));
      $form_state->setError($element['id'], t("The slate form ID or embed code is not valid. Try entering a form ID that looks like '@form_id_format'.", [
        '@form_id' => $element['id']['#value'],
        '@form_id_format' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
      ]));
    }
  }

  /**
   * Form element validation handler for the 'redirect' element.
   *
   * Disallows saving inaccessible or untrusted URLs.
   */
  public static function validateRedirectElement($element, FormStateInterface $form_state, $form) {
    if ($element['id']['#value'] !== '') {
      $uri = trim($element['redirect']['#value']);
      $form_state->setValueForElement($element['redirect'], $uri);

      if (parse_url($uri, PHP_URL_SCHEME) === 'internal' && !in_array($element['redirect']['#value'][0], ['/', '?', '#'], TRUE) && substr($element['redirect']['#value'], 0, 7) !== '<front>') {
        $form_state->setError($element['redirect'], t('Manually entered paths should start with one of the following characters: / ? #'));
        return;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\cu_slate_forms\Plugin\Field\FieldType\SlateFormIdItemInterface $item */
    $item = $items[$delta];

    $element['id'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Form ID / Embed Code'),
      '#placeholder' => $this->getSetting('placeholder_id'),
      '#default_value' => isset($items[$delta]->id) ? $items[$delta]->id : NULL,
      //'#element_validate' => [[get_called_class(), 'validateIdElement']],
      '#rows' => 3,
      '#maxlegnth' => 750,
      '#required' => $element['#required'],
    ];

    $element['redirect'] = [
      '#type' => 'url',
      '#title' => $this->t('Redirect URL'),
      '#description' => $this->t('This must be an absolute URL such as %url.', ['%url' => 'http://example.com']),
      '#placeholder' => $this->getSetting('placeholder_redirect'),
      '#default_value' => (!$items[$delta]->isEmpty()) ? $items[$delta]->redirect : NULL,
      '#maxlength' => 2048,
      '#required' => FALSE,
      '#link_type' => LinkItemInterface::LINK_EXTERNAL,
    ];

    $element['#element_validate'][] = [get_called_class(), 'validateIdElement'];
    $element['#element_validate'][] = [get_called_class(), 'validateRedirectElement'];

    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() == 1) {
      $element += [
        '#type' => 'fieldset',
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['placeholder_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder for form ID'),
      '#default_value' => $this->getSetting('placeholder_title'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    $elements['placeholder_redirect'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder for redirect URL'),
      '#default_value' => $this->getSetting('placeholder_url'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $placeholder_id = $this->getSetting('placeholder_id');
    $placeholder_redirect = $this->getSetting('placeholder_redirect');
    if (empty($placeholder_id) && empty($placeholder_redirect)) {
      $summary[] = $this->t('No placeholders');
    }
    else {
      if (!empty($placeholder_id)) {
        $summary[] = $this->t('Form ID placeholder: @placeholder_id', ['@placeholder_id' => $placeholder_id]);
      }
      if (!empty($placeholder_redirect)) {
        $summary[] = $this->t('Redirect URL placeholder: @placeholder_redirect', ['@placeholder_redirect' => $placeholder_redirect]);
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => &$value) {
      $value['redirect'] = trim($value['redirect']);
      if (empty($value['redirect'])) {
        unset($values[$key]['redirect']);
      }
    }
    return $values;
  }

}
