#!/bin/bash

# These processes will affect any 'base previews'. Building from a preview will skip these steps. Site specific
# DB/Asset tasks should now go here

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

  # DEFAULT
  # TODO: Possibly do a base db install. Myabe using drush install? Something we can test the core codebase with,
  # something not tied to a specific site. Ideally, we could run drush -r "${DOCROOT}" site-install creighton -y
  # But that doesn't currently result in a working site and so it breaks the tugboat build. For now, we use alliance.
  none)
    drush -r "${DOCROOT}" sql:sync "@alliance.01live" @self -y
    drush -r "${DOCROOT}" rsync "@alliance.01live":%files @self:%files -y
  ;;
  *)
    echo "Could not determine site alias"
    exit 1
  ;;
esac
