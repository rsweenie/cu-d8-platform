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

    //build the form inputs for all content types
    foreach (NodeType::loadMultiple() as $machine_name => $content_type) {
      // Add a checkbox to content type form w/default_value set to db value
      $form[$this->getFormId()][$machine_name] = [
        '#type' => 'checkbox',
        '#title' => t($content_type->get('name')),
        //get breadcrumb config apply by content type uuid
        '#default_value'=> $config->get($machine_name)['apply'],
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

    //iterate over machine names
    foreach ($this->getFormChanged($form,$form_state) as $machine_name){
      //set new value in config obj
      $config->set($machine_name,['uuid'=>NodeType::load($machine_name)->get('uuid'),'apply'=>$form_state->getValue($machine_name)]);
    }
    //save them
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * return machine names who's attrs changed
   * used to stop unnecessary db calls for settings that didn't change
   */
  private function getFormChanged(array &$form, FormStateInterface $form_state){
    $changed = [];
    foreach($form[$this->getFormId()] as $machine_name => $attributes){
      //is a checkbox
      if(isset($attributes['#type']) && $attributes['#type'] === 'checkbox'){
        //check if changed
        if($attributes['#default_value'] !== $form_state->getValue($machine_name))
          array_push($changed,$machine_name);
      }
    }
    return $changed;
  }

}