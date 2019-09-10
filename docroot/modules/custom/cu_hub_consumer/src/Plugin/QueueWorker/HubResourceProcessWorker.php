<?php
 
namespace Drupal\cu_hub_consumer\Plugin\QueueWorker;
 
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\cu_hub_consumer\Entity\HubReference;
 
/**
 * Fetches resource list data from hub.
 *
 * @QueueWorker(
 *   id = "hub_resource_process_worker",
 *   title = @Translation("Hub Resource Process Worker"),
 *   cron = {"time" = 60}
 * )
 */
class HubResourceProcessWorker extends QueueWorkerBase {
 
  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Make sure this action is allowed.
    if (!\Drupal::config('cu_hub_consumer.settings')->get('enabled')) {
      throw new SuspendQueueException('Hub operations are currently disabled.');
    }
    if (!\Drupal::config('cu_hub_consumer.settings')->get('queue.enabled')) {
      throw new SuspendQueueException('Queue operations are currently disabled.');
    }

    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($data->bundle);
    $resource_type = $hub_ref_type->getResourceType();

    try {
      $resource = $resource_type->fetchResource($data->hub_uuid);
    }
    catch (ResourceException $e) {
      watchdog_exception('cu_hub_consumer', $e);
      throw new SuspendQueueException('Could not properly fetch the hub resource data.');
    }

    if ($raw_json_data = $resource->getRawJsonData()) {
      $query = \Drupal::entityQuery('hub_reference')
        ->condition('type', $data->bundle)
        ->condition('hub_uuid', $data->hub_uuid);
      $ref_ids = $query->execute();

      // Is this an update, or an insert?
      if ($ref_ids) {
        $hub_references = \Drupal::entityTypeManager()->getStorage('hub_reference')->loadMultiple($ref_ids);
        foreach ($hub_references as $hub_reference) {
          // Try to update the hub_reference title.
          if ($key = $resource_type->getKey('label')) {
            if (!empty($resource->{$key})) {
              $hub_reference->set('title', $resource->{$key}->getString());
            }
          }

          $hub_reference->set('hub_data', $raw_json_data);
          //$hub_reference->set('changed', \Drupal::time()->getRequestTime());
          $hub_reference->setChangedTime(\Drupal::time()->getRequestTime());
          $hub_reference->setPublished(TRUE);
          $hub_reference->save();
        }
      }
      else {
        $entity_data = [
          'type' => $data->bundle,
          'hub_uuid' => $data->hub_uuid,
          'hub_data' => $raw_json_data,
        ];

        $hub_reference = HubReference::create($entity_data);
        $hub_reference->setPublished(TRUE);
        $hub_reference->save();
      }
    }
  }
 
}
