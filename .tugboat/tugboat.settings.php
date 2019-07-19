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

$cu_site_name = $_ENV['CU_SITE_NAME'];

switch ($cu_site_name) {
  case 'alliance':
    break;
  case '':

    break;
  default:
    break;
}
