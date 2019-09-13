<?php

namespace Drupal\cu_hub_consumer\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\cu_hub_consumer\Entity\HubReference;
use Drupal\cu_hub_consumer\Hub\ResourceException;

/**
 * Provides a form for hub reference refresh.
 *
 * @internal
 */
class HubReferenceRefreshConfirmForm extends ConfirmFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Hub reference we want to refresh.
   *
   * @var  \Drupal\cu_hub_consumer\Entity\HubReference
   */
  protected $hubReference;

  /**
   * Constructs a new HubReferenceTypeDeleteConfirm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "confirm_hub_reference_refresh_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, HubReference $hub_reference = NULL) {
    $this->hubReference = $hub_reference;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $hub_ref_type = $this->entityTypeManager->getStorage('hub_reference_type')->load($this->hubReference->bundle());
    $resource_type = $hub_ref_type->getResourceType();

    try {
      $resource = $resource_type->fetchResource($this->hubReference->hub_uuid->value);
    }
    catch (ResourceException $e) {
      // @TODO: detect a difference between a 404, parse error, etc.
      watchdog_exception('cu_hub_consumer', $e);
      drupal_set_message($this->t('Could not properly fetch the hub resource data.'), 'error');
    }

    if ($resource && $raw_json_data = $resource->getRawJsonData()) {
      // Try to update the hub_reference title.
      if ($key = $resource_type->getKey('label')) {;
        if (!empty($resource->{$key})) {
          $this->hubReference->set('title', $resource->{$key}->getString());
        }
      }

      $this->hubReference->set('hub_data', $raw_json_data);
      $this->hubReference->setChangedTime(\Drupal::time()->getRequestTime());
      $this->hubReference->setPublished(TRUE);

      $this->hubReference->save();

      drupal_set_message($this->t('The hub resource data has been refreshed.'));

      $form_state->setRedirect('entity.hub_reference.canonical', ['hub_reference' => $this->hubReference->id()]);
    }
    else {
      drupal_set_message($this->t('Could not properly fetch the hub resource data.'), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->hubReference->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to refresh the data for %label?', ['%label' => $this->hubReference->label()]);
  }

}
