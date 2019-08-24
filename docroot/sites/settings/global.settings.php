<?php

/**
 * @file
 * Generated by BLT. Serves as an example of global includes.
 */

/**
 * An example global include file.
 *
 * To use this file, rename to global.settings.php.
 */

/**
 * Include settings files in docroot/sites/settings.
 *
 * If instead you want to add settings to a specific site, see BLT's includes
 * file in docroot/sites/{site-name}/settings/default.includes.settings.php.
 */
$additionalSettingsFiles = [
  // e.g,( DRUPAL_ROOT . "/sites/settings/foo.settings.php" )
  __DIR__ . "/tugboat.settings.php",
  __DIR__ . "/tugboat_hash.settings.php",
];

foreach ($additionalSettingsFiles as $settingsFile) {
  if (file_exists($settingsFile)) {
    require $settingsFile;
  }
}
