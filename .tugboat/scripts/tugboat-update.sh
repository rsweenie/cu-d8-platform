#!/bin/bash

# These processes will affect any 'base previews'. Building from a preview will skip these steps.
# DB/Asset tasks for a generic site (often used for base previews should go here).

CU_SITE_ALIAS=$(`dirname "$0"`/get_site_alias.sh)

echo "Doing Tugboat update steps for $CU_SITE_ALIAS"

case $CU_SITE_ALIAS in
  alliance)
    echo "Nothing to do for Alliance"
  ;;
  grad-site)
    echo "Nothing to do for Grad Site"
  ;;

  # DEFAULT
  # TODO: Possibly do a base db install. Myabe using drush install? Something we can test the core codebase with,
  # something not tied to a specific site. Ideally, we could run drush -r "${DOCROOT}" site-install creighton -y
  # But that doesn't currently result in a working site and so it breaks the tugboat build. For now, we use alliance.
  none)
    echo "Syncing Alliance site DB and assets for standard build"
    drush -r "${DOCROOT}" sql:sync "@alliance.01live" @self -y
    drush -r "${DOCROOT}" rsync "@alliance.01live":%files @self:%files -y
  ;;
  *)
    echo "Could not determine site alias"
    exit 1
  ;;
esac
