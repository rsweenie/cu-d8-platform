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
    //$entity_type_manager = \Drupal::service('entity_type.manager');
    //dsm($entity_type_manager->getDefinitions());
    //dsm($entity_type_manager->getDefinition('hub_reference'));

    /*
    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load('site');
    $hub_ref_source = $hub_ref_type->getSource();
    $resource_type = $hub_ref_source->getResourceType();
    //dsm($hub_ref_resource_type);

    //$bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('hub_reference');
    //dsm($bundles);

    //$resource_types = \Drupal::service('plugin.manager.cu_hub_consumer.hub_resource_type');
    //dsm($resource_types->getDefinitions());

    //$resource_type = $resource_types->createInstance('node:site');
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

    $resource_uuid = 'b3d19d1e-a72a-4dee-82e1-f2955087b22f';
    //$resource_uuid = '9243a05e-396e-4aec-aed2-14f365b668aB';

    dsm($resource_type->getResourceUrl($resource_uuid));

    try {
      $resource = $resource_type->fetchResource($resource_uuid);
      dsm($resource->getJsonData());
      dsm($resource->getProcessedData());
    }
    catch (ResourceException $e) {
      dsm($e);
    }
    */




    $hub_ref_type = \Drupal::entityTypeManager()->getStorage('hub_reference_type')->load('program');
    //$hub_ref_source = $hub_ref_type->getSource();
    //$resource_type = $hub_ref_source->getResourceType();
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

    $resource_uuid = '0d40a955-8399-42e9-8be8-c9544b84bb5e';

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
