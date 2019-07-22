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


// Site specific Drupal settings
system($_ENV['TUGBOAT_ROOT'] . '/.tugboat/scripts/get_site_alias.sh > /dev/null', $cu_site_name);

switch ($cu_site_name) {
  case 'alliance':
    break;
  case 'grad-site':
    break;
  default:
    break;
}
