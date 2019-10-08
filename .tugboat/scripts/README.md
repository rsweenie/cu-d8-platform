## Site specific Tugboat configurations

### get_site_alias.sh

This script extracts a site alias based on the current branch name and prints
it out. We do it this way because we cannot reliably set any ENV variables
when doing a Tugboat build.

The value extracted will be the 'middle' section of the branch name.

Example: The value for a branch named 'feature/sitename/ticket-12345' would be
'sitename'.

'Site aliases' are based on our Acquia drush aliases which you can see by
running `drush sa`. You can see what aliases we are currently using (and
add your own) in the tugboat-build.sh and tugboat-*.sh scripts.

This script is called on by each of the following three scripts.

### tugboat-build.sh

Called during the Tugboat 'build' phase. Contains any build steps that are
specific to the current site alias.


### tugboat-update.sh

Called during the Tugboat 'update' phase. Contains any build steps that are
specific to the current site alias.

### ../tugboat.settings.php

This is a custom drupal settings.php file used only in the Tugboat environment.
Any Drupal configuration that is specific to a site alias should be set here.

## Default Database

Where there is no site alias passed in via our branch naming convention,
a default installation takes place for that branch.  Ideally we would
simply do a clean install of our default profile using `drush site-install
creighton`. Unfortunately, this does not currently result in a working site,
and our tugboat build process breaks. So for the meanwhile, our generic
previews are being built with a copy of the alliance site database. When
custom site previews are built on top of this, they simply overwrite the
database and import fresh settings.

