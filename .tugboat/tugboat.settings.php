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
$cu_site_name = exec($_ENV['TUGBOAT_ROOT'] . '/.tugboat/scripts/get_site_alias.sh');

switch ($cu_site_name) {
  case 'alliance':
    break;
  case 'grad-site':
  case 'grad':
    $config['config_split.config_split.grad']['status'] = 1;
    break;
  case 'hub':
    $config['config_split.config_split.hub']['status'] = 1;
    break;
  default:
    break;
}
