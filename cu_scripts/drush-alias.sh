#!/bin/sh
# Generate ACSF site aliases for all three environments

echo 'Please enter the site name: '
read site_name

echo "01dev:" > "drush/sites/$site_name.site.yml"
echo "  root: /var/www/html/creighton.01dev/docroot" >> "drush/sites/$site_name.site.yml"
echo "  uri: $site_name.dev-creighton.acsitefactory.com" >> "drush/sites/$site_name.site.yml"
echo "  host: staging-1749.enterprise-g1.hosting.acquia.com" >> "drush/sites/$site_name.site.yml"
echo "  options: {  }" >> "drush/sites/$site_name.site.yml"
echo "  paths: { dump-dir: /mnt/tmp }" >> "drush/sites/$site_name.site.yml"
echo "  user: creighton.01dev" >> "drush/sites/$site_name.site.yml"
echo "  ssh: { options: '-p 22' }" >> "drush/sites/$site_name.site.yml"
echo "01test:" >> "drush/sites/$site_name.site.yml"
echo "  root: /var/www/html/creighton.01test/docroot" >> "drush/sites/$site_name.site.yml"
echo "  uri: $site_name.test-creighton.acsitefactory.com" >> "drush/sites/$site_name.site.yml"
echo "  host: staging-1749.enterprise-g1.hosting.acquia.com" >> "drush/sites/$site_name.site.yml"
echo "  options: {  }" >> "drush/sites/$site_name.site.yml"
echo "  paths: { dump-dir: /mnt/tmp }" >> "drush/sites/$site_name.site.yml"
echo "  user: creighton.01test" >> "drush/sites/$site_name.site.yml"
echo "  ssh: { options: '-p 22' }" >> "drush/sites/$site_name.site.yml"
echo "01live:" >> "drush/sites/$site_name.site.yml"
echo "  root: /var/www/html/creighton.01live/docroot" >> "drush/sites/$site_name.site.yml"
echo "  uri: $site_name.creighton.acsitefactory.com" >> "drush/sites/$site_name.site.yml"
echo "  host: web-1747.enterprise-g1.hosting.acquia.com" >> "drush/sites/$site_name.site.yml"
echo "  options: {  }" >> "drush/sites/$site_name.site.yml"
echo "  paths: { dump-dir: /mnt/tmp }" >> "drush/sites/$site_name.site.yml"
echo "  user: creighton.01live" >> "drush/sites/$site_name.site.yml"
echo "  ssh: { options: '-p 22' }" >> "drush/sites/$site_name.site.yml"