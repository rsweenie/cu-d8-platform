<?php

/**
 * Tugboat specific configuration.
 */

if (isset($_ENV['TUGBOAT_PREVIEW_ID'])) {
  // If this is a grad site preview, enable the grad site config split.
  if (strpos($_ENV['TUGBOAT_GITHUB_HEAD'], 'r2i/grad-site') === 0) {
    $config['config_split.config_split.grad_school']['status'] = 1;
  }
}
