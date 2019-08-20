<?php

namespace Drupal\cu_hub_consumer\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\media\Entity\MediaType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for hub reference type forms.
 *
 * @internal
 */
class HubReferenceTypeForm extends EntityForm {

  /**
   * Entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if ($this->operation === 'add') {
      $form['#title'] = $this->t('Add hub reference type');
    }

    $form['label'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('The human-readable name of this hub reference type.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#maxlength' => 32,
      '#disabled' => !$this->entity->isNew(),
      '#machine_name' => [
        'exists' => [MediaType::class, 'load'],
      ],
      '#description' => $this->t('A unique machine-readable name for this hub reference type.'),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $this->entity->getDescription(),
      '#description' => $this->t('Describe this hub reference type.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $actions['submit']['#value'] = $this->t('Save');
    $actions['delete']['#value'] = $this->t('Delete');
    $actions['delete']['#access'] = $this->entity->access('delete');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);
    /** @var \Drupal\cu_hub_consumer\HubReferenceTypeInterface $hub_reference_type */
    $hub_reference_type = $this->entity;

    $t_args = ['%name' => $hub_reference_type->label()];
    if ($status === SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('The hub reference type %name has been updated.', $t_args));
    }
    elseif ($status === SAVED_NEW) {
      $this->messenger()->addStatus($this->t('The hub reference type %name has been added.', $t_args));
      $this->logger('media')->notice('Added hub reference type %name.', $t_args);
    }

    $form_state->setRedirectUrl($hub_reference_type->toUrl('collection'));
  }

}
