<?php

namespace Drupal\cu_layout_tweaks\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * @Layout(
 *   id = "program_detail_2col",
 *   label = @Translation("Program Detail - 2 Column"),
 *   category = @Translation("CU 2019"),
 *   template = "templates/layout--program-detail-2col",
 *   regions = {
 *     "main" = {
 *       "label" = @Translation("Main content")
 *     },
 *     "right" = {
 *       "label" = @Translation("Right sidebar")
 *     }
 *   },
 *   icon_map = {
 *     "main",
 *     "main",
 *     "right"
 *   }
 * )
 */
class ProgramDetail2Column extends LayoutDefault implements PluginFormInterface {

  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'sidebar_classes' => '',
      'sidebar_title' => '',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();

    $form['sidebar_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sidebar Classes'),
      '#default_value' => $configuration['sidebar_classes'],
    ];

    $form['sidebar_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sidebar Title'),
      '#default_value' => $configuration['sidebar_title']
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) { }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['sidebar_classes'] = $form_state->getValue('sidebar_classes');
    $this->configuration['sidebar_title'] = $form_state->getValue('sidebar_title');
  }
}
