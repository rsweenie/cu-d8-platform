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


// Site specific settings

system(dirname(__FILE__) . '/scripts/get_site_alias.sh', $cu_site_name);

print $cu_site_name;

switch ($cu_site_name) {
  case 'alliance':
    break;
  case 'grad-site':

    break;
  default:
    break;
}
