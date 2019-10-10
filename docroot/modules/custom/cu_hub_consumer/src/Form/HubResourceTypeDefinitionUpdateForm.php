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
use Drupal\cu_hub_consumer\Hub\ClientException;
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
      $resource_types = $this->resourceInspector->getResourceTypes(FALSE, TRUE);
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
      foreach ($tracked_types as $tracked_type) {
        $tracked_type_ids[$tracked_type->get('type_id')] = $tracked_type->get('type_id');
      }

      $form['resource_types_message'] = [
        '#type' => 'item',
        '#title' => $this->t('Resource types to track'),
        '#markup' => $this->t('A resource type must be tracked here if it will be displayed or processed.'),
      ];

      $form['resource_types_changed_fields'] = [
        '#type' => 'details',
        '#title' => $this->t('Tracked definition changes'),
        '#description' => $this->t('The following resource type definitions differ from the inspection of hub.'),
        '#open' => TRUE,
      ];

      foreach ($tracked_types as $tracked_type) {
        $type_id = $tracked_type->get('type_id');

        // We grab the latest inspection info, update the data in the def object, but then we
        // don't actually save it. We are just using it to detect differences. Saving of changes
        // will happen on form submit.
        try {
          $inspection_info = $this->resourceInspector->inspect($type_id);
          $tracked_type->set('fields', $inspection_info);
          if (empty($inspection_info)) {
            $form['resource_types_changed_fields'][$type_id] = [
              '#type' => 'item',
              '#markup' => '<b>' . $type_id . '</b>: Empty inspection results. At least one must exist on hub for this to be inspected.',
            ];
          }
          elseif ($changes = $tracked_type->getChanges()) {
            $changed_fields = [];
            if (!empty($changes['added'])) {
              foreach ($changes['added'] as $field_name => $props) {
                $changed_fields[] = '[+] ' . $field_name;
              }
            }
            if (!empty($changes['removed'])) {
              foreach ($changes['removed'] as $field_name => $props) {
                $changed_fields[] = '[-] ' . $field_name;
              }
            }
            if (!empty($changes['modified'])) {
              foreach ($changes['modified'] as $field_name => $props) {
                $changed_fields[] = '[m] ' . $field_name;
              }
            }
            $form['resource_types_changed_fields'][$type_id] = [
              '#type' => 'item',
              '#markup' => '<b>' . $type_id . '</b>: ' . implode(', ', $changed_fields),
            ];
          }
        }
        catch (ClientException $e) {
          $this->messenger->addError($e->getMessage());
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
      $resource_type_info = $this->resourceInspector->inspect($resource_type);
      $resource_type_defs = $def_storage->loadByProperties(['type_id' => $resource_type]);
      
      // Are we updating?
      if ($resource_type_info && $resource_type_defs) {
        foreach ($resource_type_defs as $type_def) {
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
      // Else, if we already have a def, but the inspection is empty, we change nothing and give a warning.
      elseif (!$resource_type_info && $resource_type_defs) {
        $this->messenger->addWarning($this->t('Failed to update %type_id definition. The inspection info was empty.', ['%type_id' => $resource_type]));
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

    // Now we need to go through and delete any resource type definitions we no longer need.
    foreach ($tracked_types as $tracked_type) {
      if (!in_array($tracked_type->get('type_id'), $resource_types)) {
        $has_changed = TRUE;
        $tracked_type->delete();
        $this->messenger->addStatus($this->t('Deleted %type_id definition.', ['%type_id' => $tracked_type->get('type_id')]));
      }
    }

    \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type')->clearCachedDefinitions();
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
    \Drupal::service('plugin.manager.block')->clearCachedDefinitions();

    if (!$has_changed) {
      $this->messenger->addStatus($this->t('No definition changes made.'));
    }
  }

}
