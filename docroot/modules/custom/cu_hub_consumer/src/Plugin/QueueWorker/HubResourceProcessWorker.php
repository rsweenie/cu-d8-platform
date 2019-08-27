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
    //\Drupal::logger('cu_hub_consumer')->notice(str_replace(__NAMESPACE__ . '\\', '', __CLASS__) . ':' . __LINE__ .': ' . print_r($data, TRUE));

    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($data->bundle);
    $resource_type = $hub_ref_type->getResourceType();

    try {
      $resource = $resource_type->fetchResource($data->hub_uuid);
      //$resource_data = $resource->getProcessedData();
      $resource_data = $resource->getJsonData();
    }
    catch (ResourceException $e) {
      watchdog_exception('cu_hub_consumer', $e);
      throw new SuspendQueueException('Could not properly fetch the hub resource data.');
    }

    if ($resource_data) {
      //\Drupal::logger('cu_hub_consumer')->notice(str_replace(__NAMESPACE__ . '\\', '', __CLASS__) . ':' . __LINE__ .': ' . print_r($resource_data, TRUE));

      $query = \Drupal::entityQuery('hub_reference')
        ->condition('type', $data->bundle)
        ->condition('hub_uuid', $data->hub_uuid);
      $ref_ids = $query->execute();

      // Is this an update, or an insert?
      if ($ref_ids) {
        $hub_references = \Drupal::entityTypeManager()->getStorage('hub_reference')->loadMultiple($ref_ids);
        foreach ($hub_references as $hub_reference) {
          $hub_reference->set('hub_data', $resource_data);
          $hub_reference->save();
        }
      }
      else {
        $entity_data = [
          'type' => $data->bundle,
          'hub_uuid' => $data->hub_uuid,
          'hub_data' => $resource_data,
        ];
        //\Drupal::logger('cu_hub_consumer')->notice(str_replace(__NAMESPACE__ . '\\', '', __CLASS__) . ':' . __LINE__ .': ' . print_r($entity_data, TRUE));

        // @TODO: We want to update if a reference with the UUID already exists.
        // We should have a constraint on souce+uuid to keep it unique.

        $hub_reference = HubReference::create($entity_data);
        $hub_reference->save();
      }
    }
  }
 
}
