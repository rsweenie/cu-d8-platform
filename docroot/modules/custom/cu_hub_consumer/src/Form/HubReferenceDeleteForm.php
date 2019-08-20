<?php

namespace Drupal\cu_hub_consumer\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the hub_reference entity edit forms.
 */
class HubReferenceDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion()
  {
    // TODO: Implement getQuestion() method.
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl()
  {
    // TODO: Implement getCancelUrl() method.
  }
}
