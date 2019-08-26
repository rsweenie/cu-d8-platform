<?php
 
namespace Drupal\cu_hub_consumer\Plugin\QueueWorker;
 
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
 
/**
 * Fetches resource list data from hub.
 *
 * @QueueWorker(
 *   id = "hub_resource_list_fetch_worker",
 *   title = @Translation("Hub Resource List Fetch Worker"),
 *   cron = {"time" = 60}
 * )
 */
class HubResourceListFetchWorker extends QueueWorkerBase {
 
  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $queue_factory = \Drupal::service('queue');
    $list_fetch_queue = $queue_factory->get('hub_resource_list_fetch_worker');
    $resource_process_queue = $queue_factory->get('hub_resource_process_worker');

    \Drupal::logger('cu_hub_consumer')->notice(print_r($data, TRUE));

    // @TODO: make this configurable.
    $limit = 20;

    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($data->bundle);
    $resource_type = $hub_ref_type->getSource()->getResourceType();

    $resource_list = NULL;
    try {
      $resource_list = $resource_type->fetchResourceList($data->url, $limit);
    }
    catch (ResourceException $e) {
      watchdog_exception('cu_hub_consumer', $e);
      throw new SuspendQueueException('Could not properly fetch the hub resource list.');
    }

    if ($resource_list) {
      $resource_list_data = $resource_list->getProcessedData();

      foreach ($resource_list_data as $resource_uuid => $resource_url) {
        // Queue up each resource for individual processing.
        $resource_item = new \stdClass();
        $resource_item->bundle = $data->bundle;
        $resource_item->hub_uuid = $resource_uuid;
        $resource_item->hub_url = $resource_url;
        $resource_process_queue->createItem($resource_item);
      }

      // Queue up the next page of resource list if available.
      if ($next_url = $resource_list->getNextUrl()) {
        $list_item = new \stdClass();
        $list_item->bundle = $data->bundle;
        $list_item->url = $next_url;
        $list_fetch_queue->createItem($list_item);
      }
    }
  }
 
}
