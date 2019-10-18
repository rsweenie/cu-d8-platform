#!/bin/bash

#
# Site specific build steps. Does NOT include anything done globally (the stuff in config.yml).
# DB/Asset sync for aliased sites should take place here (PR builds start here)
#

CU_SITE_ALIAS=$(`dirname "$0"`/get_site_alias.sh)

echo "Doing Tugboat build steps for $CU_SITE_ALIAS"

case $CU_SITE_ALIAS in
  demo|hrnew|alliance|grad|hub)
    echo "Syncing DB and assets for $CU_SITE_ALIAS"
    # DB sync MUST come before filesync always
    drush -r "${DOCROOT}" sql:drop -y
    drush -r "${DOCROOT}" sql:sync "@${CU_SITE_ALIAS}.01live" @self -y
    drush -r "${DOCROOT}" rsync "@${CU_SITE_ALIAS}.01live":%files @self:%files -y
  ;;
  grad-site)
    echo "Syncing DB and assets for $CU_SITE_ALIAS"
    # DB sync MUST come before filesync always
    drush -r "${DOCROOT}" sql:drop -y
    drush -r "${DOCROOT}" sql:sync "@grad.01live" @self -y
    drush -r "${DOCROOT}" rsync "@grad.01live":%files @self:%files -y  -- --exclude '/styles/'
  ;;
  none)
    echo "Nothing to do for generic install"
  ;;
  *)
    echo "Could not determine site alias. Please check Tugboat scripts!"
    exit 1
  ;;
esac
