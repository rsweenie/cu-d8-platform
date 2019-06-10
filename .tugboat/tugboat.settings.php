<?php

$databases['default']['default'] = array (
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

/**
 * Public file path:
 *
 * A local file system path where public files will be stored. This directory
 * must exist and be writable by Drupal. This directory must be relative to
 * the Drupal installation directory and be accessible over the web.
 */
$settings['file_public_path'] = 'sites/default/files';

/**
 * Private file path:
 *
 * A local file system path where private files will be stored. This directory
 * must be absolute, outside of the Drupal installation directory and not
 * accessible over the web.
 *
 * Note: Caches need to be cleared when this value is changed to make the
 * private:// stream wrapper available to the system.
 *
 * See https://www.drupal.org/documentation/modules/file for more information
 * about securing private files.
 */
# $settings['file_private_path'] = '';

/**
 * Temporary file path:
 *
 * A local file system path where temporary files will be stored. This
 * directory should not be accessible over the web.
 *
 * Note: Caches need to be cleared when this value is changed.
 *
 * See https://www.drupal.org/node/1928898 for more information
 * about global configuration override.
 */
$config['system.file']['path']['temporary'] = '/tmp';

// Settings based on preview base branch.
if (getenv('TUGBOAT_GITHUB_BASE') == 'r2i/master-integration') {

}
elseif (getenv('TUGBOAT_GITHUB_BASE') == 'r2i/grad-site') {
  
}

// Settings based on current branch.
if (getenv('TUGBOAT_GITHUB_HEAD') == 'r2i/grad-site') {

}
elseif (getenv('TUGBOAT_GITHUB_HEAD') == 'r2i/grad-site-dev') {

}
