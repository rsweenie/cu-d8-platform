<?php 

namespace Drupal\cu_hub_consumer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cu_hub_consumer\hub\ResourceException;

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
    $state = \Drupal::state();

    $time_options = [
      '0'    => 'Disabled',
      '60'   => '1 hour',
      '120'  => '2 hours',
      '240'  => '4 hours',
      '360'  => '6 hours',
      '720'  => '12 hours',
      '1440' => '24 hours',
      '2160' => '36 hours',
      '2880' => '2 days',
      '4320' => '3 days',
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('This is a master kill switch. Disabling this will stop ALL requests to the hub API as well as all cron/queue tasks.'),
      '#default_value' => $this->getSetting('enabled', FALSE),
    ];

    $form['hub_base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Base URL'),
      '#description' => $this->t('Example: %hub_base_url. Do not include /jsonapi in the URL.', ['%hub_base_url' => 'https://hub.creighton.com']),
      '#default_value' => $this->getSetting('hub_base_url'),
    ];

    $form['hub_site_uuid'] = [
      '#type' => 'select',
      '#title' => $this->t('Hub Site'),
      '#description' => $this->t('This is the site on hub that will be used to filter what is imported.'),
      '#options' => $this->getSiteOptions(),
      '#default_value' => $this->getSetting('hub_site_uuid', ''),
    ];

    $form['cron'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Cron'),
      '#tree' => TRUE,
    );
    $form['cron']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Disabling this will disable the cron process but still allow other operations.'),
      '#default_value' => $this->getSetting('cron.enabled', TRUE),
    ];
    $form['cron']['refresh_freq'] = [
      '#type' => 'select',
      '#title' => $this->t('Refresh frequency'),
      '#description' => $this->t('The amount of time between doing a full refresh of all hub reference data.'),
      '#default_value' => $this->getSetting('cron.refresh_freq', 1440),
      '#options' => $time_options,
    ];
    $form['cron']['refresh_fetch_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Refresh fetch size'),
      '#description' => $this->t('The number of results to request with each call to the hub API.'),
      '#default_value' => $this->getSetting('cron.refresh_fetch_limit', 20),
      '#min' => 1,
      '#step' => 1,
    ];
    $form['cron']['unpublish_age'] = [
      '#type' => 'select',
      '#title' => $this->t('Age before unpublish'),
      '#description' => $this->t('The amount of time a published hub reference can go unupdated before being unpublished.'),
      '#default_value' => $this->getSetting('cron.unpublish_age', 2880),
      '#options' => $time_options,
    ];
    $form['cron']['delete_age'] = [
      '#type' => 'select',
      '#title' => $this->t('Age before delete'),
      '#description' => $this->t('The amount of time an unpublished hub reference can go unupdated before being deleted.'),
      '#default_value' => $this->getSetting('cron.delete_age', 0),
      '#options' => $time_options,
    ];

    $last_run = $state->get('cu_hub_consumer.cron.last_refresh', 0);
    $form['cron']['last_run_info'] = [
      '#markup' => '<b>Last Run:</b> ' . ($last_run ? \Drupal::service('date.formatter')->format($last_run) : '-'),
    ];

    $form['queue'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Queue workers'),
      '#tree' => TRUE,
    );
    $form['queue']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Disabling this will disable the queue workers but still allow other operations.'),
      '#default_value' => $this->getSetting('queue.enabled', TRUE),
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
      ->set('hub_site_uuid', $form_state->getValue('hub_site_uuid'))
      ->set('cron.enabled', $form_state->getValue(['cron', 'enabled']))
      ->set('cron.refresh_freq', $form_state->getValue(['cron', 'refresh_freq']))
      ->set('cron.refresh_fetch_limit', $form_state->getValue(['cron', 'refresh_fetch_limit']))
      ->set('cron.unpublish_age', $form_state->getValue(['cron', 'unpublish_age']))
      ->set('cron.delete_age', $form_state->getValue(['cron', 'delete_age']))
      ->set('queue.enabled', $form_state->getValue(['queue', 'enabled']))
      ->save();

    // We want to reset the last_refresh time on settings changes.
    $state = \Drupal::state();
    $state->set('cu_hub_consumer.cron.last_refresh', 0);

    // We also need to make sure to invalidate the endpoint cache.
    \Drupal::cache()->invalidate('cu_hub_consumer:hub_endpoints');

    parent::submitForm($form, $form_state);
  }

  /**
   * Returns a config setting with optional default value.
   *
   * @param string $key
   * @param mixed $default_value
   * @return mixed
   */
  protected function getSetting($key, $default_value = NULL) {
    $config = $this->config(static::SETTINGS);
    $setting = $config->get($key);

    if ($setting === NULL) {
      return $default_value;
    }
    return $setting;
  }

  protected function getSiteOptions() {
    $options = [
      '' => ' - ',
    ];

    if ($resource_type_manager = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type')) {
      if ($resource_type_plugin_id = $resource_type_manager->findPluginByHubTypeId('node--hub_site')) {
        if ($resource_type = $resource_type_manager->getResourceType($resource_type_plugin_id)) {
          try {
            if ($list = $resource_type->fetchResourceList(NULL, $limit=0, ['field_hub_site_title'])) {
              foreach ($list as $site_uuid => $site_info) {
                $options[$site_uuid] = $site_info['field_hub_site_title'] . ' [' . $site_uuid . ']';
              }
            }
          }
          catch (ResourceException $e) {
            watchdog_exception('cu_hub_consumer', $e);
            drupal_set_message('Error: ' . $e->getMessage(), 'error');
          }
        }
      }
    }

    return $options;
  }

}
