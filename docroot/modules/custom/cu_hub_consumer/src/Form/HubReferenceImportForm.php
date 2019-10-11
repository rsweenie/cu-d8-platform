<?php
 
namespace Drupal\cu_hub_consumer\Form;
 
//use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\cu_hub_consumer\Hub\ResourceException;
 
/**
 * Defines a form that triggers batch operations to download and process
 * data from the Hub API.
 * Batch operations are included in this class as methods.
 */
class HubReferenceImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hub_reference_import_form';
  }
 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('hub_reference');
    $bundle_item_list = [];
    foreach ($bundles as $bundle => $bundle_info) {
      $bundle_item_list[$bundle] = $bundle_info['label'];
    }
    $form['bundle_info'] = [
      '#theme' => 'item_list',
      '#title' => t('Hub references types to import'),
      '#items' => $bundle_item_list,
    ];

    // Refresh the lock on cron.
    $lock = \Drupal::lock();
    $form['cron_lock_status'] = [
      '#type'  => 'item',
      '#title' => t('Cron lock status'),
      'markup'  => [
        '#markup' => ($lock->lockMayBeAvailable('cu_hub_consumer.cron')) ? 'unlocked' : 'locked',
      ]
    ];
 
    $nums   = [
      5, 10, 25, 50, 75, 100, 150, 200, 250, 300, 400, 500, 600, 700, 800, 900,
    ];
    $limits = array_combine($nums, $nums);
    $desc   = 'This is the number of resources the API should return each call ' .
      'as the operation pages through the data.';
    $form['fetch_limit'] = [
      '#type'          => 'select',
      '#title'         => t('Fetch Throttle'),
      '#options'       => $limits,
      '#default_value' => 100,
      '#description'   => t($desc),
    ];
    $desc = 'This is the number of resources to analyze and save to Drupal as ' .
      'the operation pages through the data.<br />This is labor intensive so ' .
      'usually a lower number than the above throttle';
    $form['process_limit'] = [
      '#type'          => 'select',
      '#title'         => t('Process Throttle'),
      '#options'       => $limits,
      '#default_value' => 25,
      '#description'   => t($desc),
    ];

    $form['actions']['#type'] = 'actions';
 
    $form['actions']['submit'] = [
      '#type'     => 'submit',
      '#value'    => t('Import References'),
      //'#disabled' => !$lock->lockMayBeAvailable('cu_hub_consumer.cron'),
    ];
 
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We can't use here the Dependency Injection solution
    // so we load the necessary services in the other way
    $queue_factory = \Drupal::service('queue');

    $list_fetch_queue = $queue_factory->get('hub_resource_list_fetch_worker');
    $resource_process_queue = $queue_factory->get('hub_resource_process_worker');

    // Delete existing queues
    $list_fetch_queue->deleteQueue();
    $resource_process_queue->deleteQueue();

    $batch = [
      'title'      => t('Downloading Resource List Data'),
      'operations' => [
        [
          [static::class, 'acquireLock'], // Static method notation
          [],
        ],
        [
          [static::class, 'fetchResourceLists'], // Static method notation
          [
            $form_state->getValue('fetch_limit', 0),
          ],
        ],
        [
          [static::class, 'processResourceLists'], // Static method notation
          [
            $form_state->getValue('process_limit', 0),
          ],
        ]
      ],
      'finished' => [static::class, 'finishedBatch'], // Static method notation
    ];

    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('hub_reference');
    foreach ($bundles as $bundle => $bundle_info) {
      // Create new queue item
      $item = new \stdClass();
      $item->bundle = $bundle;
      $item->url = '';
      $list_fetch_queue->createItem($item);
    }

    batch_set($batch);
  }

  public static function acquireLock(&$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox'] = [
        'progress' => 0,
        'max'      => 1,
      ];
      $context['results']['lists'] = 0;
    }
    $sandbox = &$context['sandbox'];

    // Attempt to acquire a lock on the cron to avoid overlap.
    // We will just keep running this batch operation until we get it.
    $lock = \Drupal::lock();
    if ($lock->wait('cu_hub_consumer.cron', 5)) {
      if ($lock->acquire('cu_hub_consumer.cron')) {
        $context['finished'] = 1;
      }
    }
  }

  public static function fetchResourceLists($limit, &$context) {
    // Refresh the lock on cron.
    $lock = \Drupal::lock();
    $lock->acquire('cu_hub_consumer.cron');

    // We can't use here the Dependency Injection solution
    // so we load the necessary services in the other way
    $queue_factory = \Drupal::service('queue');
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');

    $list_fetch_queue = $queue_factory->get('hub_resource_list_fetch_worker');
    $list_fetch_queue_worker = $queue_manager->createInstance('hub_resource_list_fetch_worker');

    if (!isset($context['results']['warnings'])) {
      $context['results']['warnings'] = [];
    }

    if (!isset($context['sandbox']['progress'])) {
      $context['finished'] = 0;
      $context['sandbox'] = [
        'progress' => 0,
        'limit'    => $limit,
        'max'      => 1,
      ];
      $context['results']['lists'] = 0;
    }
    $sandbox = &$context['sandbox'];

    $run_size = ($list_fetch_queue->numberOfItems() < $limit) ? $list_fetch_queue->numberOfItems() : $limit;

    for ($i = 0; $i < $run_size; $i++) {
      if ($item = $list_fetch_queue->claimItem()) {
        // Build a message so this isn't entirely boring for admins
        $context['message'] = '<h2>' . t('Fetching @bundle list...', ['@bundle' => $item->data->bundle]) . '</h2>';

        try {
          // Process it
          $list_fetch_queue_worker->processItem($item->data);
          // If everything was correct, delete the processed item from the queue
          $list_fetch_queue->deleteItem($item);

          $context['results']['lists']++;
          $sandbox['progress']++;
        }
        catch (ResourceException $e) {
          $context['results']['warnings'][] = $e->getMessage();

          // If there was an Exception thrown because of an error
          // release the item that the worker could not process.
          // Another worker can come and process it
          $list_fetch_queue->releaseItem($item);
        }
        catch (SuspendQueueException $e) {
          $context['results']['warnings'][] = $e->getMessage();

          // If there was an Exception thrown because of an error
          // release the item that the worker could not process.
          // Another worker can come and process it
          $list_fetch_queue->releaseItem($item);
          break;
        }
      }
      else {
        $context['finished'] = 1;
      }
    }

    // If completely done downloading, set the last time it was done, so that
    // cron can keep the data up to date with smaller queries
    if ($context['finished'] >= 1) {
      $last_time = \Drupal::time()->getRequestTime();
      \Drupal::state()->set('cu_hub_consumer.hub_resource_list_fetch_last', $last_time);
    }
  }

  public static function processResourceLists($limit, &$context) {
    // Refresh the lock on cron.
    $lock = \Drupal::lock();
    $lock->acquire('cu_hub_consumer.cron');

    // We can't use here the Dependency Injection solution
    // so we load the necessary services in the other way
    $queue_factory = \Drupal::service('queue');
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');

    $resource_process_queue = $queue_factory->get('hub_resource_process_worker');
    $resource_process_queue_worker = $queue_manager->createInstance('hub_resource_process_worker');

    if (!isset($context['results']['warnings'])) {
      $context['results']['warnings'] = [];
    }

    if (!isset($context['sandbox']['progress'])) {
      $context['finished'] = 0;
      $context['sandbox'] = [
        'progress' => 0,
        'limit'    => $limit,
        'max'      => 1,
      ];
      $context['results']['resources'] = 0;
    }
    $sandbox = &$context['sandbox'];

    $run_size = ($resource_process_queue->numberOfItems() < $limit) ? $resource_process_queue->numberOfItems() : $limit;

    for ($i = 0; $i < $run_size; $i++) {
      if ($item = $resource_process_queue->claimItem()) {
        // Build a message so this isn't entirely boring for admins
        $context['message'] = '<h2>' . t('Fetching @bundle a list...', ['@bundle' => $item->data->bundle]) . '</h2>';

        try {
          // Process it
          $resource_process_queue_worker->processItem($item->data);
          // If everything was correct, delete the processed item from the queue
          $resource_process_queue->deleteItem($item);

          $context['results']['resources']++;
          $sandbox['progress']++;
        }
        catch (ResourceException $e) {
          $context['results']['warnings'][] = $e->getMessage();

          // If there was an Exception thrown because of an error
          // release the item that the worker could not process.
          // Another worker can come and process it
          $resource_process_queue->releaseItem($item);
        }
        catch (SuspendQueueException $e) {
          $context['results']['warnings'][] = $e->getMessage();

          // If there was an Exception thrown because of an error
          // release the item that the worker could not process.
          // Another worker can come and process it
          $resource_process_queue->releaseItem($item);
          break;
        }
      }
      else {
        $context['finished'] = 1;
      }
    }

    // If completely done downloading, set the last time it was done, so that
    // cron can keep the data up to date with smaller queries
    if ($context['finished'] >= 1) {
      $last_time = \Drupal::time()->getRequestTime();
      \Drupal::state()->set('cu_hub_consumer.hub_resource_list_fetch_last', $last_time);
    }
  }

  /**
   * Reports the results of the import operations.
   *
   * @param bool  $success
   * @param array $results
   * @param array $operations
   */
  public static function finishedBatch($success, $results, $operations) {
    // Unlock to allow cron to run again.
    $lock = \Drupal::lock();
    $lock->release('cu_hub_consumer.cron');

    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    $lists = t('Finished with an error.');
    $resources  = FALSE;

    if ($success) {
      $lists = \Drupal::translation()->formatPlural(
        $results['lists'],
        'One resource list fetched.',
        '@count resource lists fetched.'
      );
      $resources  = \Drupal::translation()->formatPlural(
        $results['resources'],
        'One resource fetched/processed.',
        '@count resources fetched/processed.'
      );
    }

    if (!empty($results['warnings'])) {
      foreach ($results['warnings'] as $warning) {
        drupal_set_message($warning, 'warning');
      }
    }

    drupal_set_message($lists);
    if ($resources) {
      drupal_set_message($resources);
    };

  }

}
