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

    $this->testResourceList('program');

    $this->testResource('program', '0d40a955-8399-42e9-8be8-c9544b84bb5e');
    $this->testResource('degree', '9243a05e-396e-4aec-aed2-14f365b668ab');

    return $build;
  }

  protected function testResourceList($resource_type) {
    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load($resource_type);
    $resource_type = $hub_ref_type->getResourceType();
    dsm($resource_type->getResourceListUrl());

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
    dsm($resource_type->getResourceUrl($resource_uuid));

    try {
      $resource = $resource_type->fetchResource($resource_uuid);
      //dsm($resource->getJsonData());
      $resource->getProcessedData();
    }
    catch (ResourceException $e) {
      dsm($e);
    }

    if ($resource->getProcessedData()) {
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
