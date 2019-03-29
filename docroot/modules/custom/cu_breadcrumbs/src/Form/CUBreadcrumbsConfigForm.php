<?php
namespace Drupal\CUBreadcrumbs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class CUBreadcreumbsSettingsForm extends ConfigFormBase {
    /** @var string Config settings */
  const SETTINGS = 'cu_breadcrumb.settings';

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
      static::SETTINGS,
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    // Add a checkbox to content type form w/default_value set to db value
    $form['cu_breadcrumbs_apply'] = array(
      '#type' => 'checkbox',
      '#title' => t("Apply custom breadcrumb"),
      //get breadcrumb config apply by content type uuid
      '#default_value'=> \Drupal::config('cu_breadcrumb.config')
                                  ->get($form_state->getFormObject()
                                                    ->getEntity()
                                                    ->get('uuid'))['apply'],
    );

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      
      //get the breadcrumbs editable configs
      $this->configFactory->getEditable(static::SETTINGS)
      //set values
      ->set($attributes['uuid'],['uuid'=>$form_state->getFormObject()->getEntity()->get('uuid'),'apply'=>$form_state->getValue('cu_breadcrumbs_apply')])
      //save them
      ->save();

    parent::submitForm($form, $form_state);
  }
}