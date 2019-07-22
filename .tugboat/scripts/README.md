## Site specific Tugboat configurations

### get_site_alias.sh

This script extracts a site alias based on the current branch name and prints it
out. We do it this way because we cannot reliably set any ENV variables when
doing a Tugboat build. 

The value extracted will be the 'middle' section of the branch name. 

Example: The value for a branch named 'feature/sitename/ticket-12345' would be
'sitename'.

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
