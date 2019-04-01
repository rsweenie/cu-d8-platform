<?php
namespace Drupal\cu_breadcrumbs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Configure example settings for this site.
 */
class CUBreadcrumbsSettingsForm extends ConfigFormBase {
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
      static::SETTINGS,
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    //add a title to the form
    $form[$this->getFormId()]['title'] = [
      '#type' => 'item',
      '#title' => t('Enable/Disable CU Breadcrumbs'),
    ];

    //build the form inputs
    foreach (NodeType::loadMultiple() as $machine_name => $content_type) {
      // Add a checkbox to content type form w/default_value set to db value
      $form[$this->getFormId()][$machine_name] = [
        '#type' => 'checkbox',
        '#title' => t($machine_name),
        //get breadcrumb config apply by content type uuid
        '#default_value'=> $config->get($content_type->get('uuid'))['apply'],
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   * 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
            //get the breadcrumbs editable configs
      $config = $this->configFactory->getEditable(static::SETTINGS);
      foreach (NodeType::loadMultiple() as $machine_name => $content_type){
        $returned_value = $form_state->getValue($machine_name);
        $config->set($content_type->get('uuid'),['uuid'=>$content_type->get('uuid'),'apply'=>$returned_value]);
      }
      //save them
      $config->save();

    parent::submitForm($form, $form_state);
  }
}