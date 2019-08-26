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
    $hub_reference_type = \Drupal::entityTypeManager()->getDefinition('hub_reference');

    \Drupal::logger('cu_hub_consumer')->notice(print_r($data, TRUE));

    $entity_data = [
      $hub_reference_type->getKey('bundle') => $data->bundle,
      //'bundle' => $data->bundle,
      'hub_uuid' => $data->hub_uuid,
    ];
    \Drupal::logger('cu_hub_consumer')->notice(print_r($entity_data, TRUE));

    $hub_reference = HubReference::create($entity_data);
    $hub_reference->save();
  }
 
}
