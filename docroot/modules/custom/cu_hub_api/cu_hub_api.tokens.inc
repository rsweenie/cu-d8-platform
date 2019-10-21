<?php

use Drupal\Core\Url;

/**
 * Implements hook_token_info().
 */
function cu_hub_api_token_info() {
  $types = [];
  $tokens = [];

  $types['cu-hub-api'] = [
    'name' => t('CU Hub API'),
    'description' => t('Custom tokens for CU Hub API module.'),
  ];

  $tokens['cu-hub-api']['external-canonical'] = [
    'name' => t('Canonical URL'),
    'description' => t('The custom canonical URL.'),
    'type' => 'url',
    'needs-data' => 'node',
    'dynamic' => TRUE,
  ];

  return [
    'types' => $types,
    'tokens' => $tokens,
  ];
}

/**
 * Implements hook_tokens().
 */
function cu_hub_api_tokens($type, $tokens, array $data, array $options, \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();
  $url_options = array(
    'absolute' => TRUE,
  );

  $replacements = [];

  if ($type == 'cu-hub-api' && !empty($data['node'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];

    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'external-canonical':
          $replacements[$original] = _cu_hub_api_get_external_canonical($node, $url_options);
          break;
      }
    }
  }

  return $replacements;
}

function _cu_hub_api_get_external_canonical(\Drupal\node\NodeInterface $node, array $url_options = []) {
  if ($node->hasField('field_hub_sites') && !$node->field_hub_sites->isEmpty()) {
    if ($first = $node->field_hub_sites->first()) {
      if ($first->entity->field_hub_site_base_uri->first()) {
        $site_url = $first->entity->field_hub_site_base_uri->first()->getUrl();
        $site_uri = rtrim($site_url->getUri(), '/');

        if ($alias = $first->alias) {
          $site_uri .= (string) $alias;
        }

        $url = Url::fromUri($site_uri);

        return $url->toString();
      }
    }
  }

  return '';
}
