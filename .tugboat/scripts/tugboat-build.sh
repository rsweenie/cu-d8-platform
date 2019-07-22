#!/bin/bash

#
# Site specific build steps. Does NOT include anything done globally. That goes in config.yml. DB/Asset sync for
# none generic sites should take place here (PR builds start here)
#

CU_SITE_ALIAS=$(`dirname "$0"`/get_site_alias.sh)

case $CU_SITE_ALIAS in
  alliance)
    # DB sync MUST come before filesync always
    drush -r "${DOCROOT}" sql:sync "@alliance.01live" @self -y
    drush -r "${DOCROOT}" rsync "@alliance.01live":%files @self:%files -y
  ;;
  grad-site)
    DB_FILE="${TUGBOAT_ROOT}/.tugboat/db/${TUGBOAT_GITHUB_HEAD}.sql.gz"

    #
    # Tries to import a branch specific DB first, else falls back on a base copy.
    #
    if [[ -f "${DB_FILE}" ]]; then
      zcat "${DB_FILE}" | mysql tugboat
    elif [[ -f "${TUGBOAT_ROOT}/.tugboat/db/base.sql.gz" ]]; then
      zcat "${TUGBOAT_ROOT}/.tugboat/db/base.sql.gz" | mysql tugboat
    fi
  ;;
  none)
  ;;
  *)
    echo "Could not determine site alias"
    exit 1
  ;;
esac