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

    $form['base_uri'] = [
      '#type' => 'url',
      '#title' => $this->t('Base URI'),
      '#description' => $this->t('Example: %base_uri', ['%base_uri' => 'https://hub.creighton.com']),
      '#default_value' => $config->get('base_uri'),
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
      ->set('base_uri', $form_state->getValue('base_uri'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
