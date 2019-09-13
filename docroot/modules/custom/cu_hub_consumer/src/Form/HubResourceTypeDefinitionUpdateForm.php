<?php
 
namespace Drupal\cu_hub_consumer\Form;
 
//use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\DiffArray;
use Drupal\cu_hub_consumer\Hub\ResourceInspector;
use Drupal\cu_hub_consumer\Entity\HubResourceTypeDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that triggers batch operations to download and update
 * hub resource type definitions.
 * Batch operations are included in this class as methods.
 */
class HubResourceTypeDefinitionUpdateForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Undocumented variable
   *
   * @var \Drupal\cu_hub_consumer\Hub\ResourceInspector
   */
  protected $resourceInspector;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\cu_hub_consumer\Hub\ResourceInspector $resource_inspector
   *   Hub resource inspector service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, ResourceInspector $resource_inspector) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->resourceInspector = $resource_inspector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('cu_hub_consumer.hub_resource_inspector')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hub_resource_type_definition_update_form';
  }
 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    try {
      $resource_types = $this->resourceInspector->getResourceTypes(FALSE);
    }
    catch (ClientException $e) {
      $this->messenger->addError($e->getMessage());
    }

    if ($resource_types) {
      $resource_type_options = [];
      foreach ($resource_types as $resource_type => $resource_type_info) {
        $resource_type_options[$resource_type] = $resource_type;
      }

      $tracked_types = HubResourceTypeDefinition::loadMultiple();
      $tracked_type_ids = [];
      $changed_tracked_type_fields = [];
      foreach ($tracked_types as $tracked_type) {
        $tracked_type_ids[$tracked_type->get('type_id')] = $tracked_type->get('type_id');

        // We grab the latest inspection info, update the data in the def object, but then we
        // don't actually save it. We are just using it to detect differences. Saving of changes
        // will happen on form submit.
        $inspection_info = $this->resourceInspector->inspect($tracked_type->get('type_id'));
        $tracked_type->set('fields', $inspection_info);
        if ($tracked_type->hasChanged()) {
          $changed_tracked_type_fields[$tracked_type->get('type_id')] = $tracked_type->getChanges();
        }
      }

      $form['resource_types_message'] = [
        '#type' => 'item',
        '#title' => $this->t('Resource types to track'),
        '#markup' => $this->t('A resource type must be tracked here if it will be displayed or processed.'),
      ];
      if (!empty($changed_tracked_type_fields)) {
        $form['resource_types_changed_fields'] = [
          '#type' => 'details',
          '#title' => $this->t('Tracked definition changes'),
          '#description' => $this->t('The following resource type definitions differ from the inspection of hub.'),
          '#open' => TRUE,
        ];
        foreach ($changed_tracked_type_fields as $type_id => $field_names) {
          $form['resource_types_changed_fields'][$type_id] = [
            '#type' => 'item',
            '#markup' => '<b>' . $type_id . '</b>: ' . implode(', ', $field_names),
          ];
        }
      }
      $form['resource_types'] = [
        '#type' => 'checkboxes',
        '#options' => $resource_type_options,
        '#default_value' => $tracked_type_ids,
      ];
    }

    $form['actions']['#type'] = 'actions';
 
    $form['actions']['submit'] = [
      '#type'     => 'submit',
      '#value'    => t('Update Resource Defintions'),
    ];
 
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $def_storage = $this->entityTypeManager->getStorage('hub_resource_type_definition');

    $resource_types = array_filter($form_state->getValue('resource_types', []));
    $tracked_types = HubResourceTypeDefinition::loadMultiple();
    $has_changed = FALSE;

    foreach ($resource_types as $resource_type) {
      if ($resource_type_info = $this->resourceInspector->inspect($resource_type)) {
        // Are we updating?
        if ($type_defs = $def_storage->loadByProperties(['type_id' => $resource_type])) {
          foreach ($type_defs as $type_def) {
            $type_def->set('fields', $resource_type_info);
            if ($type_def->hasChanged()) {
              $has_changed = TRUE;
              if ($type_def->save()) {
                $this->messenger->addStatus($this->t('Updated %type_id definition.', ['%type_id' => $resource_type]));
              }
              else {
                $this->messenger->addWarning($this->t('Failed to update %type_id definition.', ['%type_id' => $resource_type]));
              }
            }
          }
        }
        // Else, we need to create a new one.
        else {
          $has_changed = TRUE;
          $type_def = HubResourceTypeDefinition::create([
            'id' => $this->resourceInspector->getResourceTypeMachineId($resource_type),
            'type_id' => $resource_type,
            'fields' => $resource_type_info,
          ]);
          if ($type_def->save()) {
            $this->messenger->addStatus($this->t('Created %type_id definition.', ['%type_id' => $resource_type]));
          }
          else {
            $this->messenger->addWarning($this->t('Failed to create %type_id definition.', ['%type_id' => $resource_type]));
          }
        }
      }
      else {
        $this->messenger->addWarning($this->t('Inspection failed for %type_id. At least one must exist on hub for this to be inspected.', ['%type_id' => $resource_type]));
      }
    }

    // Now we need to go through and delete any resource type definitions we no longer need.
    foreach ($tracked_types as $tracked_type) {
      if (!in_array($tracked_type->get('type_id'), $resource_types)) {
        $has_changed = TRUE;
        $tracked_type->delete();
        $this->messenger->addStatus($this->t('Deleted %type_id definition.', ['%type_id' => $tracked_type->get('type_id')]));
      }
    }

    if (!$has_changed) {
      $this->messenger->addStatus($this->t('No definition changes made.'));
    }
  }

}
