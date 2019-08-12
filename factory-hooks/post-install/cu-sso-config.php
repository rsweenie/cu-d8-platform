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
echo "ENVIRONMENT: $env";
// The public domain name of the website.
// Run updates against requested domain rather than acsf primary domain.
$domain = getenv('HTTP_HOST');
$domain_fragments = explode('.', $domain);
$site_name = array_shift($domain_fragments);

exec("/mnt/www/html/$site.$env/vendor/acquia/blt/bin/blt drupal:update --environment=$env --site=$site_name --define drush.uri=$domain --verbose --yes");
echo "ENVIRONMENT: $env";
// Run sso-config.sh to set sp_entity_id in config when a new site is created.
exec("bash ../../sso-config.sh $site_name $env");