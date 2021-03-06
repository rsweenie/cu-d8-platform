<?php
namespace Drupal\cu_data_transform\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cu_data_transform\Transformation\CUDataTransformation;

class CUDataTransformForm implements FormInterface {

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cu_data_transform_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //add a title to the form
    $form[$this->getFormId()]['title'] = [
      '#type' => 'item',
      '#title' => t('Run Data Transformations'),
    ];
    foreach(CUDataTransformation::getTransformations() as $transform_machine_name){
      //check boxes would be better here
      //words on a button
      $button_value = str_replace('_',' ',$transform_machine_name);
      // Add a buttons
      $form[$this->getFormId()][$transform_machine_name] = [
        '#type' => 'submit',
        '#name' => $transform_machine_name,
        '#value' => t('Run '.$button_value),
      ];
    }
    return $form;
  }

    /** 
   * {@inheritdoc}
   * 
   */
  public function validateForm(array &$form, FormStateInterface $form_state) { 
    return in_array($form_state->getTriggeringElement()['#name'],CUDataTransformation::getTransformations());
  }

  /** 
   * {@inheritdoc}
   * 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message(t(CUDataTransformation::{$form_state->getTriggeringElement()['#name']}()));
  }
}