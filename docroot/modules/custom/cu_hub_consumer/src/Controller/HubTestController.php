<?php
 
namespace Drupal\cu_hub_consumer\Controller;
 
use Drupal\Core\Controller\ControllerBase;
use Drupal\cu_hub_consumer\Hub\ResourceException;
 
/**
 * Provides controller methods for the Hub API integration overview.
 */
class HubTestController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function showOverview() {
    $build = [];

    /*
    list($response, $json) = $this->pingEndpoint($build);
    // If response data was built and returned, display it with a sample of the
    // objects returned
    if (isset($response)) {
      $build['response'] = [
        '#theme' => 'item_list',
        '#title' => t('Response: @r', [
          '@r' => $response->getReasonPhrase(),
        ]),
        '#items' => [
          'code' => t('Code: @c', ['@c' => $response->getStatusCode()]),
        ],
      ];
    }
    if (isset($json)) {
      $build['response_data'] = [
        '#theme' => 'item_list',
        '#title' => t('Response Data:'),
        '#items' => [
          'response-type' => t('Response Type: @t', [
            '@t' => $json->response_type,
          ]),
          'total-count' => t('Total Count: @c', [
            '@c' => $json->pagination->total_count,
          ]),
        ],
      ];
      $this->displayPaginationData($json, $build);
      $this->displayDataSample($json, $build);
    }
    */

    //$client = \Drupal::service('cu_hub_consumer.hub_client');
    //dsm($client->getEndpoints());

    $inspector = \Drupal::service('cu_hub_consumer.hub_resource_inspector');
    //dsm($inspector->inspect('node--hub_program', TRUE));
    //dsm($inspector->inspect('node--hub_degree', TRUE));
    //dsm($inspector->inspect('taxonomy_term--program_interests', TRUE));
    //dsm($inspector->inspect('media--image', TRUE));
    //dsm($inspector->inspect('file--file', TRUE));

    $this->testResourceList('program');

    //$this->testResource('program', '0d40a955-8399-42e9-8be8-c9544b84bb5e');

    $degree = $this->testResource('degree', 'fb6d4104-dc7f-4604-9447-cdc9a2ced203');
    dsm($degree->field_hub_degree_hero_image[0]->image[0]->uri[0]);

    return $build;
  }

  protected function testResourceList($resource_type) {
    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($resource_type);
    $resource_type = $hub_ref_type->getResourceType();
    dsm($resource_type->getResourceListPath());

    try {
      $resource_list = $resource_type->fetchResourceList();
      //dsm($resource_list->jsonData);
      dsm($resource_list->getProcessedData());
    }
    catch (ResourceException $e) {
      dsm($e);
    }
  }

  protected function testResource($resource_type, $resource_uuid) {
    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($resource_type);
    $resource_type = $hub_ref_type->getResourceType();
    dsm($resource_type->getResourcePath($resource_uuid));

    try {
      $resource = $resource_type->fetchResource($resource_uuid);
      //dsm($resource->getJsonData());
      $resource->getProcessedData();
    }
    catch (ResourceException $e) {
      dsm($e);
    }

    if ($resource) {
      dsm($resource->type);
      dsm($resource->id);
      //dsm($resource->field_hub_degree_title->getString());
      //dsm($resource->field_hub_degree_availability->getString());
      //dsm($resource->field_hub_degree_availability);

      //dsm($resource->getProcessedData());

      return $resource;
    }
  }

}
