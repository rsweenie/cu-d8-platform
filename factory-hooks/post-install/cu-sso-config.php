<?php

/**
 * @file
 * Factory Hook: cu-sso-config.
 *
 * automatically generates sso login config when a site is created
 *
 */

$site = getenv('AH_SITE_GROUP');
$env = getenv('AH_SITE_ENVIRONMENT');
$target_env = $site . $env;
// The public domain name of the website.
// Run updates against requested domain rather than acsf primary domain.
$domain = $_SERVER['HTTP_HOST'];
$domain_fragments = explode('.', $domain);
$site_name = array_shift($domain_fragments);

exec("/mnt/www/html/$site.$env/vendor/acquia/blt/bin/blt drupal:update --environment=$env --site=$site_name --define drush.uri=$domain --verbose --no-interaction");
echo "SITE: $site ";
echo "DOMAIN: $domain ";
echo "ENVIRONMENT: $env ";
// Run sso-config.sh to set sp_entity_id in config when a new site is created.
exec("bash ".DRUPAL_ROOT."/../cu_scripts/sso-config.sh $site_name $env");