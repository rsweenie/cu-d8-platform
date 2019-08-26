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
use Drupal\cu_hub_consumer\Entity\HubReferenceType;
use Drupal\cu_hub_consumer\Hub\ReferenceSourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for hub reference type forms.
 *
 * @internal
 */
class HubReferenceTypeForm extends EntityForm {

  /**
   * Hub reference source plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $sourceManager;


  /**
   * Entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $source_manager
   *   Hub reference source plugin manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   */
  public function __construct(PluginManagerInterface $source_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->sourceManager = $source_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.cu_hub_consumer.hub_reference_source'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * Ajax callback triggered by the type provider select element.
   */
  public function ajaxHandlerData(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#source-dependent', $form['source_dependent']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Source is not set when the entity is initially created.
    /** @var \Drupal\cu_hub_consumer\Hub\ReferenceSourceInterface $source */
    $source = $this->entity->get('source') ? $this->entity->getSource() : NULL;

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
        'exists' => [HubReferenceType::class, 'load'],
      ],
      '#description' => $this->t('A unique machine-readable name for this hub reference type.'),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $this->entity->getDescription(),
      '#description' => $this->t('Describe this hub reference type.'),
    ];

    $plugins = $this->sourceManager->getDefinitions();
    $options = [];
    foreach ($plugins as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }

    $form['source_dependent'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'source-dependent'],
    ];

    if (!$this->entity->isNew()) {
      $source_description = $this->t('<em>The hub reference source cannot be changed after the hub reference type is created.</em>');
    }
    else {
      $source_description = $this->t('Hub reference source that is responsible for additional logic related to this hub reference type.');
    }
    $form['source_dependent']['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Hub reference source'),
      '#default_value' => $source ? $source->getPluginId() : NULL,
      '#options' => $options,
      '#description' => $source_description,
      '#ajax' => ['callback' => '::ajaxHandlerData'],
      '#required' => TRUE,
      // Once the hub reference type is created, its source plugin cannot be changed
      // anymore.
      '#disabled' => !$this->entity->isNew(),
    ];

    if ($source) {
      // Hub reference source plugin configuration.
      $form['source_dependent']['source_configuration'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Hub reference source configuration'),
        '#tree' => TRUE,
      ];

      $form['source_dependent']['source_configuration'] = $source->buildConfigurationForm($form['source_dependent']['source_configuration'], $this->getSourceSubFormState($form, $form_state));
    }

    // Field mapping configuration.
    $form['source_dependent']['field_map'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Field mapping'),
      '#tree' => TRUE,
      'description' => [
        '#markup' => '<p>' . $this->t('Hub reference sources can provide metadata fields such as title, description, etc. We can automatically save this metadata information to entity fields, which can be configured below.') . '</p>',
      ],
    ];

    if (empty($source) || empty($source->getMetadataAttributes())) {
      $form['source_dependent']['field_map']['#access'] = FALSE;
    }
    else {
      $options = [ReferenceSourceInterface::METADATA_FIELD_EMPTY => $this->t('- Skip field -')];
      foreach ($this->entityFieldManager->getFieldDefinitions('hub_reference', $this->entity->id()) as $field_name => $field) {
        if (!($field instanceof BaseFieldDefinition) || $field_name === 'name') {
          $options[$field_name] = $field->getLabel();
        }
      }

      $field_map = $this->entity->getFieldMap();
      foreach ($source->getMetadataAttributes() as $metadata_attribute_name => $metadata_attribute_label) {
        $form['source_dependent']['field_map'][$metadata_attribute_name] = [
          '#type' => 'select',
          '#title' => $metadata_attribute_label,
          '#options' => $options,
          '#default_value' => isset($field_map[$metadata_attribute_name]) ? $field_map[$metadata_attribute_name] : ReferenceSourceInterface::METADATA_FIELD_EMPTY,
        ];
      }
    }

    return $form;
  }

  /**
   * Gets subform state for the hub reference source configuration subform.
   *
   * @param array $form
   *   Full form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Parent form state.
   *
   * @return \Drupal\Core\Form\SubformStateInterface
   *   Sub-form state for the hub reference source configuration form.
   */
  protected function getSourceSubFormState(array $form, FormStateInterface $form_state) {
    return SubformState::createForSubform($form['source_dependent']['source_configuration'], $form, $form_state)
      ->set('operation', $this->operation)
      ->set('type', $this->entity);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (isset($form['source_dependent']['source_configuration'])) {
      // Let the selected plugin validate its settings.
      $this->entity->getSource()->validateConfigurationForm($form['source_dependent']['source_configuration'], $this->getSourceSubFormState($form, $form_state));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('field_map', array_filter(
      $form_state->getValue('field_map', []),
      function ($item) {
        return $item != ReferenceSourceInterface::METADATA_FIELD_EMPTY;
      }
    ));

    parent::submitForm($form, $form_state);

    /* @TODO: Any queing we need to do to get more data?
    $this->entity->setQueueThumbnailDownloadsStatus((bool) $form_state->getValue(['options', 'queue_thumbnail_downloads']))
      ->setStatus((bool) $form_state->getValue(['options', 'status']))
      ->setNewRevision((bool) $form_state->getValue(['options', 'new_revision']));
    */

    if (isset($form['source_dependent']['source_configuration'])) {
      // Let the selected plugin save its settings.
      $this->entity->getSource()->submitConfigurationForm($form['source_dependent']['source_configuration'], $this->getSourceSubFormState($form, $form_state));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    // If the hub reference source has not been chosen yet, turn the submit button into
    // a button. This rebuilds the form with the hub reference source's configuration
    // form visible, instead of saving the hub reference type. This allows users to
    // create a hub reference type without JavaScript enabled. With JavaScript enabled,
    // this rebuild occurs during an AJAX request.
    // @see \Drupal\cu_hub_consumer\Form\HubReferenceTypeForm::ajaxHandlerData()
    if (empty($this->getEntity()->get('source'))) {
      $actions['submit']['#type'] = 'button';
    }

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

    // If the hub reference source is using a source field, ensure it's
    // properly created.
    $source = $hub_reference_type->getSource();
    $source_field = $source->getSourceFieldDefinition($hub_reference_type);
    if (!$source_field) {
      $source_field = $source->createSourceField($hub_reference_type);
      /** @var \Drupal\field\FieldStorageConfigInterface $storage */
      $storage = $source_field->getFieldStorageDefinition();
      if ($storage->isNew()) {
        $storage->save();
      }
      $source_field->save();

      // Add the new field to the default form and view displays for this
      // hub reference type.
      if ($source_field->isDisplayConfigurable('form')) {
        // @todo Replace entity_get_form_display() when #2367933 is done.
        // https://www.drupal.org/node/2872159.
        $display = entity_get_form_display('hub_reference', $hub_reference_type->id(), 'default');
        $source->prepareFormDisplay($hub_reference_type, $display);
        $display->save();
      }
      if ($source_field->isDisplayConfigurable('view')) {
        // @todo Replace entity_get_display() when #2367933 is done.
        // https://www.drupal.org/node/2872159.
        $display = entity_get_display('hub_reference', $hub_reference_type->id(), 'default');
        $source->prepareViewDisplay($hub_reference_type, $display);
        $display->save();
      }
    }

    $t_args = ['%name' => $hub_reference_type->label()];
    if ($status === SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('The hub reference type %name has been updated.', $t_args));
    }
    elseif ($status === SAVED_NEW) {
      $this->messenger()->addStatus($this->t('The hub reference type %name has been added.', $t_args));
      $this->logger('cu_hub_consumer')->notice('Added hub reference type %name.', $t_args);
    }

    $form_state->setRedirectUrl($hub_reference_type->toUrl('collection'));
  }

}
