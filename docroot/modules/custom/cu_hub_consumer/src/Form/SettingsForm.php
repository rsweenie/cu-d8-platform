<?php 

namespace Drupal\cu_hub_consumer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'cu_hub_consumer.settings';

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cu_hub_consumer_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $config->get('enabled'),
    ];

    $form['hub_base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Base URL'),
      '#description' => $this->t('Example: %hub_base_url. Do not include /jsonapi in the URL.', ['%hub_base_url' => 'https://hub.creighton.com']),
      '#default_value' => $config->get('hub_base_url'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('hub_base_url', $form_state->getValue('hub_base_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
