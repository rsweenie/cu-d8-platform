<?php
namespace Drupal\cu_breadcrumbs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Configure example settings for this site.
 */
class CUBreadcrumbsSettingsForm extends ConfigFormBase {

  /** @var string Global config settings */
  const GLOBAL_SETTINGS = 'cu_breadcrumbs.global_settings';

  /** @var string Config settings */
  const SETTINGS = 'cu_breadcrumbs.settings';

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cu_breadcrumbs_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::GLOBAL_SETTINGS,
      static::SETTINGS,
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $global_config = $this->config(static::GLOBAL_SETTINGS);
    $config = $this->config(static::SETTINGS);

    $breadcrumb_builder = $global_config->get('builder');
    $breadcrumb_builder = !empty($breadcrumb_builder) ? $breadcrumb_builder : 'system';

    $breadcrumb_builder_options = [
      'system' => $this->t('- System Default -'),
      'empty' => $this->t('Empty breadcrumbs'),
      'menu' => $this->t('Menu-based'),
    ];
    if (\Drupal::service('module_handler')->moduleExists('easy_breadcrumb')) {
      $breadcrumb_builder_options['path'] = $this->t('Path-based (extends easy_breadcrumb)');
    }

    $form['builder'] = [
      '#type' => 'select',
      '#title' => $this->t('Default breadcrumb builder'),
      '#options' => $breadcrumb_builder_options,
      '#default_value'=> $breadcrumb_builder,
    ];

    $form['node_types'] = [
      '#type' => 'fieldset',
      '#title' => t('Enable/Disable CU Breadcrumbs by node type'),
      '#tree' => TRUE,
    ];

    // build the form inputs for all content types
    foreach (NodeType::loadMultiple() as $machine_name => $content_type) {
      $content_type_config = $config->get($machine_name);

      $form['node_types'][$machine_name] = [
        '#type' => 'fieldset',
        '#title' => $this->t($content_type->get('name')),
        '#tree' => TRUE,
      ];

      $breadcrumb_builder = $content_type_config['builder'];
      $breadcrumb_builder = !empty($breadcrumb_builder) ? $breadcrumb_builder : 'default';

      $form['node_types'][$machine_name]['builder'] = [
        '#type' => 'select',
        '#title' => $this->t('Breadcrumb builder'),
        '#options' => ['default' => $this->t('- Global Default -')] + $breadcrumb_builder_options,
        '#default_value'=> $breadcrumb_builder,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   * 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // get the breadcrumbs editable configs
    $global_config = $this->configFactory->getEditable(static::GLOBAL_SETTINGS);
    $config = $this->configFactory->getEditable(static::SETTINGS);

    $global_config->set('builder', $form_state->getValue('builder'));

    // iterate over machine names
    foreach ($form_state->getValue('node_types') as $machine_name => $values) {
      $config->set($machine_name, [
        'uuid' => NodeType::load($machine_name)->get('uuid'),
        'builder' => $form_state->getValue(['node_types', $machine_name, 'builder']),
      ]);
    }

    // save them
    $global_config->save();
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
