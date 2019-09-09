<?php

/**
 * Default database configuration for a standard Lando installation
 */
$databases = array(
  'default' =>
  array(
    'default' =>
    array(
      'database' => 'drupal8',
      'username' => 'drupal8',
      'password' => 'drupal8',
      'host' => 'database',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
);

