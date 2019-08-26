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

    //$reference_sources = \Drupal::service('plugin.manager.cu_hub_consumer.hub_reference_source');
    //dsm($reference_sources->getDefinitions());

    $resource_types = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type');
    //dsm($resource_types->getDefinitions());

    $resource_type = $resource_types->createInstance('node:site');
    //dsm($resource_type);

    dsm($resource_type->getResourceListUrl());

    try {
      $resource_list = $resource_type->fetchResourceList();
      //dsm($resource_list->jsonData);
      dsm($resource_list->getProcessedData());
    }
    catch (ResourceException $e) {
      dsm($e);
    }

    //$resource_uuid = 'b3d19d1e-a72a-4dee-82e1-f2955087b22f';
    $resource_uuid = '9243a05e-396e-4aec-aed2-14f365b668aB';

    dsm($resource_type->getResourceUrl($resource_uuid));

    try {
      $resource = $resource_type->fetchResource($resource_uuid);
      dsm($resource->getJsonData());
      dsm($resource->getProcessedData());
    }
    catch (ResourceException $e) {
      dsm($e);
    }

    return $build;
  }

}
