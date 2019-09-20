<?php

namespace Drupal\cu_slate_forms\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'slate_form_default' formatter.
 *
 * @FieldFormatter(
 *   id = "slate_form_default",
 *   label = @Translation("Slate form"),
 *   field_types = {
 *     "slate_form_id"
 *   }
 * )
 */
class SlateFormDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = array(
        '#theme' => 'slate_form',
        '#form_domain' => 'https://choose.creighton.edu', // @TODO: Make this configurable.
        '#form_id' => $item->id,
        '#form_redirect' => $item->redirect,
      );
    }

    return $element;
  }

}
