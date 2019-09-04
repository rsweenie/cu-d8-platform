<?php
/**
 * Augment Creighton development environments with additional settings.
 *
 */

$additional_settings_files = [];

if (isset($_ENV['LANDO']) && $_ENV['LANDO'] == 'ON') {
  $additional_settings_files[] .= DRUPAL_ROOT . "/sites/$site_dir/settings/lando.settings.php";
}

foreach ($additional_settings_files as $settings_file) {
  if (file_exists($settings_file)) {
    require $settings_file;
  }
}