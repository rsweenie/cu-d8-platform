<?php
 
namespace Drupal\cu_hub_consumer\Form;
 
//use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

 
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
    return 'iguana_tea_import_form';
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

    /*
    $connection = new IguanaConnection();
    $data       = $connection->queryEndpoint('teasDetailFull', [
      'limit'     => 1,
      'url_query' => [
        'sort' => 'gid asc',
      ]
    ]);
 
    if (empty($data->pagination->total_count)) {
      $msg  = 'A total count of Teas was not returned, indicating that there';
      $msg .= ' is a problem with the connection. See ';
      $msg .= '<a href="/admin/config/services/iguana">the Overview page</a>';
      $msg .= 'for more details.';
      drupal_set_message(t($msg), 'error');
    }
 
    $form['count_display'] = [
      '#type'  => 'item',
      '#title' => t('Teas Found'),
      'markup'  => [
        '#markup' => $data->pagination->total_count,
      ]
    ];
 
    $form['count'] = [
      '#type'  => 'value',
      '#value' => $data->pagination->total_count,
    ];
    */
 
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
      '#default_value' => 200,
      '#description'   => t($desc),
    ];
    $desc = 'This is the number of resources to analyze and save to Drupal as ' .
      'the operation pages through the data.<br />This is labor intensive so ' .
      'usually a lower number than the above throttle';
    $form['process_limit'] = [
      '#type'          => 'select',
      '#title'         => t('Process Throttle'),
      '#options'       => $limits,
      '#default_value' => 50,
      '#description'   => t($desc),
    ];

    $form['actions']['#type'] = 'actions';
 
    $form['actions']['submit'] = [
      '#type'     => 'submit',
      '#value'    => t('Import References'),
      //'#disabled' => empty($data->pagination->total_count),
    ];
 
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
    $connection = Database::getConnection();
    $queue      = \Drupal::queue('iguana_tea_import_worker');
    $class      = 'Drupal\iguana\Form\IguanaTeaImportForm';
    $batch      = [
      'title'      => t('Downloading & Processing Iguana Tea Data'),
      'operations' => [
        [ // Operation to download all of the teas
          [$class, 'downloadTeas'], // Static method notation
          [
            $form_state->getValue('count', 0),
            $form_state->getValue('download_limit', 0),
          ],
        ],
        [ // Operation to process & save the tea data
          [$class, 'processTeas'], // Static method notation
          [
            $form_state->getValue('process_limit', 0),
          ],
        ],
      ],
      'finished' => [$class, 'finishedBatch'], // Static method notation
    ];
    batch_set($batch);
    // Lock cron out of processing while these batch operations are being
    // processed
    \Drupal::state()->set('iguana.tea_import_semaphore', TRUE);
    // Delete existing queue
    while ($worker = $queue->claimItem()) {
      $queue->deleteItem($worker);
    }
    // Clear out the staging table for fresh, whole data
    $connection->truncate('iguana_tea_staging')->execute();
    */

    $queue_factory = \Drupal::service('queue');
    $list_fetch_queue = $queue_factory->get('hub_resource_list_fetch_worker');
    $resource_process_queue = $queue_factory->get('hub_resource_process_worker');

    // Lock cron out of processing while these batch operations are being
    // processed
    \Drupal::state()->set('cu_hub_consumer.hub_resource_list_fetch_semaphore', TRUE);
    // Delete existing queues
    $list_fetch_queue->deleteQueue();
    $resource_process_queue->deleteQueue();
    /*
    while ($item = $list_fetch_queue->claimItem()) {
      $list_fetch_queue->deleteItem($item);
    }
    while ($item = $resource_process_queue->claimItem()) {
      $resource_process_queue->deleteItem($item);
    }
    */

    $batch = [
      'title'      => t('Downloading Resource List Data'),
      /*
      'operations' => [
        [ // Operation to download all of the lists data
          [static::class, 'fetchResourceLists'], // Static method notation
          [
            $form_state->getValue('fetch_limit', 0),
          ],
        ],
        [ // Operation to process & save the list data
          [static::class, 'processResourceLists'], // Static method notation
          [
            $form_state->getValue('process_limit', 0),
          ],
        ],
      ],
      */
      'operations' => [
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

      /*
      $batch['operations'][] = [
        [static::class, 'fetchResourceLists'], // Static method notation
        [
          $bundle,
          $form_state->getValue('fetch_limit', 0),
        ],
      ];
      */
    }

    batch_set($batch);
  }

  public static function fetchResourceLists($limit, &$context) {
    // We can't use here the Dependency Injection solution
    // so we load the necessary services in the other way
    $queue_factory = \Drupal::service('queue');
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');

    $list_fetch_queue = $queue_factory->get('hub_resource_list_fetch_worker');
    $list_fetch_queue_worker = $queue_manager->createInstance('hub_resource_list_fetch_worker');

    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox'] = [
        'progress' => 0,
        'limit'    => $limit,
        'max'      => 1,
        'next_url' => NULL,
      ];
      $context['results']['fetched'] = 0;
    }
    $sandbox = &$context['sandbox'];

    //$context['finished'] = 0;
    $run_size = ($list_fetch_queue->numberOfItems() < $limit) ? $list_fetch_queue->numberOfItems() : $limit;

    for ($i = 0; $i < $run_size; $i++) {
      if ($item = $list_fetch_queue->claimItem()) {
        try {
          // Process it
          $list_fetch_queue_worker->processItem($item->data);
          // If everything was correct, delete the processed item from the queue
          $list_fetch_queue->deleteItem($item);
        }
        catch (SuspendQueueException $e) {
          // If there was an Exception trown because of an error
          // Releases the item that the worker could not process.
          // Another worker can come and process it
          $list_fetch_queue->releaseItem($item);
          break;
        }

        //$context['results']['fetched']++;
        //$sandbox['progress']++;
        // Build a message so this isn't entirely boring for admins
        $context['message'] = '<h2>' . t('Fetching @bundle a list...', ['@bundle' => $item->data->bundle]) . '</h2>';
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
    // We can't use here the Dependency Injection solution
    // so we load the necessary services in the other way
    $queue_factory = \Drupal::service('queue');
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');

    $resource_process_queue = $queue_factory->get('hub_resource_process_worker');
    $resource_process_queue_worker = $queue_manager->createInstance('hub_resource_process_worker');

    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox'] = [
        'progress' => 0,
        'limit'    => $limit,
        'max'      => 1,
        'next_url' => NULL,
      ];
      $context['results']['fetched'] = 0;
    }
    $sandbox = &$context['sandbox'];

    //$context['finished'] = 0;
    $run_size = ($resource_process_queue->numberOfItems() < $limit) ? $resource_process_queue->numberOfItems() : $limit;

    for ($i = 0; $i < $run_size; $i++) {
      if ($item = $resource_process_queue->claimItem()) {
        try {
          // Process it
          $resource_process_queue_worker->processItem($item->data);
          // If everything was correct, delete the processed item from the queue
          $resource_process_queue->deleteItem($item);
        }
        catch (SuspendQueueException $e) {
          // If there was an Exception trown because of an error
          // Releases the item that the worker could not process.
          // Another worker can come and process it
          $resource_process_queue->releaseItem($item);
          break;
        }

        //$context['results']['fetched']++;
        //$sandbox['progress']++;
        // Build a message so this isn't entirely boring for admins
        $context['message'] = '<h2>' . t('Fetching @bundle a list...', ['@bundle' => $item->data->bundle]) . '</h2>';
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

  public static function fetchResourceListsBak($bundle, $limit, &$context) {
    //$resource_types = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type');
    //$hub_reference_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($bundle);

    $list_fetch_queue = \Drupal::queue('hub_resource_list_fetch_worker');

    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($bundle);
    $resource_type = $hub_ref_type->getSource()->getResourceType();

    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox'] = [
        'progress' => 0,
        'limit'    => $limit,
        'max'      => 1,
        'next_url' => NULL,
      ];
      $context['results']['fetched'] = 0;
    }
    $sandbox = &$context['sandbox'];

    $resource_list = NULL;
    $resource_list_data = NULL;
    try {
      $resource_list = $resource_type->fetchResourceList($sandbox['next_url'], $sandbox['limit']);

      // Keep the batch operation going if there are more pages of results.
      if ($next_url = $resource_list->getNextUrl()) {
        $sandbox['next_url'] = $next_url;
      }
      else {
        $context['finished'] = 1;
      }
    }
    catch (ResourceException $e) {
      // On error exit the batch operation.
      dsm($e);
      $context['finished'] = 1;
    }

    if ($resource_list && !$context['finished']) {
      $resource_list_data = $resource_list->getProcessedData();

      foreach ($resource_list_data as $resource_uuid => $resource_url) {
        // Create new queue item
        $item = new \stdClass();
        $item->bundle = $bundle;
        $item->uuid = $resource_uuid;
        $item->url = $resource_url;
        $list_fetch_queue->createItem($item);
      }

      $context['results']['fetched'] += count($resource_list_data);
      $sandbox['progress']++;
      // Build a message so this isn't entirely boring for admins
      $context['message'] = '<h2>' . t('Fetching @bundle lists...', ['@bundle' => $bundle]) . '</h2>';
      $context['message'] .= t('Found @count resources.', [
        '@count' => count($resource_list_data),
      ]);
    }

    // If completely done downloading, set the last time it was done, so that
    // cron can keep the data up to date with smaller queries
    if ($context['finished'] >= 1) {
      $last_time = \Drupal::time()->getRequestTime();
      \Drupal::state()->set('cu_hub_consumer.hub_resource_list_fetch_last', $last_time);
    }
  }

  /**
   * Common batch processing callback for all operations.
   */
  public static function batchProcess(&$context) {

    // We can't use here the Dependency Injection solution
    // so we load the necessary services in the other way
    $queue_factory = \Drupal::service('queue');
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');

    // Get the queue implementation for import_content_from_xml queue
    $queue = $queue_factory->get('import_content_from_xml');
    // Get the queue worker
    $queue_worker = $queue_manager->createInstance('import_content_from_xml');

    // Get the number of items
    $number_of_queue = ($queue->numberOfItems() < IMPORT_XML_BATCH_SIZE) ? $queue->numberOfItems() : IMPORT_XML_BATCH_SIZE;

    // Repeat $number_of_queue times
    for ($i = 0; $i < $number_of_queue; $i++) {
      // Get a queued item
      if ($item = $queue->claimItem()) {
        try {
          // Process it
          $queue_worker->processItem($item->data);
          // If everything was correct, delete the processed item from the queue
          $queue->deleteItem($item);
        }
        catch (SuspendQueueException $e) {
          // If there was an Exception trown because of an error
          // Releases the item that the worker could not process.
          // Another worker can come and process it
          $queue->releaseItem($item);
          break;
        }
      }
    }
  }

  /**
   * Batch operation to download all of the Tea data from Iguana and store
   * it in the iguana_tea_staging database table.
   *
   * @param string $bundle
   * @param array $context
   */
  public static function downloadTeas($api_count, $limit, &$context) {
    $database = Database::getConnection();
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox'] = [
        'progress' => 0,
        'limit'    => $limit,
        'max'      => $api_count,
      ];
      $context['results']['downloaded'] = 0;
    }
    $sandbox = &$context['sandbox'];
 
    $iguana = new IguanaConnection();
    $data   = $iguana->queryEndpoint('teasDetailFull', [
      'limit'     => $sandbox['limit'],
      'url_query' => [
        'offset' => (string) $sandbox['progress'],
        'sort'   => 'gid asc',
      ],
    ]);
 
    foreach ($data->response_data as $tea_data) {
      // Check for empty or non-numeric GIDs
      if (empty($tea_data->gid)) {
        $msg = t('Empty GID at progress @p for the data:', [
          '@p' => $sandbox['progress'],
        ]);
        $msg .= '<br /><pre>' . print_r($tea_data, TRUE) . '</pre>';
        \Drupal::logger('iguana')->warning($msg);
        $sandbox['progress']++;
        continue;
      } elseif (!is_numeric($tea_data->gid)) {
        $msg = t('Non-numeric GID at progress progress @p for the data:', [
          '@p' => $sandbox['progress'],
        ]);
        $msg .= '<br /><pre>' . print_r($tea_data, TRUE) . '</pre>';
        \Drupal::logger('iguana')->warning($msg);
        $sandbox['progress']++;
        continue;
      }
      // Store the data
      $database->merge('iguana_tea_staging')
        ->key(['gid' => (int) $tea_data->gid])
        ->insertFields([
          'gid'  => (int) $tea_data->gid,
          'data' => serialize($tea_data),
        ])
        ->updateFields(['data' => serialize($tea_data)])
        ->execute()
      ;
      $context['results']['downloaded']++;
      $sandbox['progress']++;
      // Build a message so this isn't entirely boring for admins
      $context['message'] = '<h2>' . t('Downloading API data...') . '</h2>';
      $context['message'] .= t('Queried @c of @t Tea entries.', [
        '@c' => $sandbox['progress'],
        '@t' => $sandbox['max'],
      ]);
    }
 
    if ($sandbox['max']) {
      $context['finished'] = $sandbox['progress'] / $sandbox['max'];
    }
    // If completely done downloading, set the last time it was done, so that
    // cron can keep the data up to date with smaller queries
    if ($context['finished'] >= 1) {
      $last_time = \Drupal::time()->getRequestTime();
      \Drupal::state()->set('iguana.tea_import_last', $last_time);
    }
  }

  /**
   * Batch operation to extra data from the iguana_tea_staging table and
   * save it to a new node or one found via GID.
   *
   * @param array $context
   */
  public static function processTeas($limit, &$context) {
    $connection = Database::getConnection();
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox'] = [
        'progress' => 0,
        'limit'    => $limit,
        'max'      => (int)$connection->select('iguana_tea_staging', 'its')
          ->countQuery()->execute()->fetchField(),
      ];
      $context['results']['teas'] = 0;
      $context['results']['nodes']  = 0;
      // Count new versus existing
      $context['results']['nodes_inserted'] = 0;
      $context['results']['nodes_updated']  = 0;
    }
    $sandbox = &$context['sandbox'];
 
    $query = $connection->select('iguana_tea_staging', 'its')
      ->fields('its')
      ->range(0, $sandbox['limit'])
    ;
    $results = $query->execute();
 
    foreach ($results as $row) {
      $gid        = (int) $row->gid;
      $tea_data   = unserialize($row->data);
      $tea        = new IguanaTea($tea_data);
      $node_saved = $tea->processTea(); // Custom data-to-node processing
 
      $connection->merge('iguana_tea_previous')
        ->key(['gid' => $gid])
        ->insertFields([
          'gid'  => $gid,
          'data' => $row->data,
        ])
        ->updateFields(['data' => $row->data])
        ->execute()
      ;
 
      $query = $connection->delete('iguana_tea_staging');
      $query->condition('gid', $gid);
      $query->execute();
 
      $sandbox['progress']++;
      $context['results']['teas']++;
      // Tally only the nodes saved
      if ($node_saved) {
        $context['results']['nodes']++;
        $context['results']['nodes_' . $node_saved]++;
      }
 
      // Build a message so this isn't entirely boring for admins
      $msg = '<h2>' . t('Processing API data to site content...') . '</h2>';
      $msg .= t('Processed @p of @t Teas, @n new & @u updated', [
        '@p' => $sandbox['progress'],
        '@t' => $sandbox['max'],
        '@n' => $context['results']['nodes_inserted'],
        '@u' => $context['results']['nodes_updated'],
      ]);
      $msg .= '<br />';
      $msg .= t('Last tea: %t %g %n', [
        '%t' => $tea->getTitle(),
        '%g' => '(GID:' . $gid . ')',
        '%n' => '(node:' . $tea->getNode()->id() . ')',
      ]);
      $context['message'] = $msg;
    }
 
    if ($sandbox['max']) {
      $context['finished'] = $sandbox['progress'] / $sandbox['max'];
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
    // Unlock to allow cron to update the data later
    \Drupal::state()->set('cu_hub_consumer.hub_resource_list_fetch_semaphore', FALSE);

    /*
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    $downloaded = t('Finished with an error.');
    $processed  = FALSE;
    $saved      = FALSE;
    $inserted   = FALSE;
    $updated    = FALSE;
    if ($success) {
      $downloaded = \Drupal::translation()->formatPlural(
        $results['downloaded'],
        'One tea downloaded.',
        '@count teas downloaded.'
      );
      $processed  = \Drupal::translation()->formatPlural(
        $results['teas'],
        'One tea processed.',
        '@count teas processed.'
      );
      $saved      = \Drupal::translation()->formatPlural(
        $results['nodes'],
        'One node saved.',
        '@count nodes saved.'
      );
      $inserted   = \Drupal::translation()->formatPlural(
        $results['nodes_inserted'],
        'One was created.',
        '@count were created.'
      );
      $updated    = \Drupal::translation()->formatPlural(
        $results['nodes_updated'],
        'One was updated.',
        '@count were updated.'
      );
    }
    drupal_set_message($downloaded);
    if ($processed) {
      drupal_set_message($processed);
    };
    if ($saved) {
      drupal_set_message($saved);
    };
    if ($inserted) {
      drupal_set_message($inserted);
    };
    if ($updated) {
      drupal_set_message($updated);
    };
    */
  }

}
