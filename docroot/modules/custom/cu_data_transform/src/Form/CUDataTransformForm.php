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
    foreach(CUDataTransformation::TRANSFORM_TYPES as $transform_machine_name => $transform_type){
      // Add a buttons
      $form[$this->getFormId()][$transform_machine_name] = [
        '#type' => 'submit',
        '#name' => $transform_machine_name,
        '#value' => t('Run '.$transform_type['title']),
      ];
    }
    return $form;
  }

    /** 
   * {@inheritdoc}
   * 
   */
  public function validateForm(array &$form, FormStateInterface $form_state) { 
    return true;
  }

  /** 
   * {@inheritdoc}
   * 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    CUDataTransformation::{$form_state->getTriggeringElement()['#name']}();
  }
}