<?php


/**
 * Add settings using full file location and name.
 *
 * It is recommended that you use the DRUPAL_ROOT and $site_dir components to
 * provide full pathing to the file in a dynamic manner.
 */


if ($_ENV['LANDO'] == 'ON' ) {
  $additionalSettingsFiles[] .= DRUPAL_ROOT . "/sites/$site_dir/settings/lando.settings.php";
}

foreach ($additionalSettingsFiles as $settingsFile) {
  if (file_exists($settingsFile)) {
    require $settingsFile;
  }
}
