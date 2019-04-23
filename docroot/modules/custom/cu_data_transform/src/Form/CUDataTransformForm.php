<?php
namespace Drupal\cu_data_tranform\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class CUDataTransformForm extends ConfigFormBase {
  //move to controller
  CONST TRANSFORM_TYPES = ['Paragraph Links'];

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
    foreach(Self::TRANSFORM_TYPES as $transform_type){
      // Add a buttons
      $form[$this->getFormId()][$machine_name] = [
        '#type' => 'button',
        '#title' => t('Run '.$transform_type),
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   * 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);
  }

}