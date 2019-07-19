#!/bin/bash

# These processes will affect our 'base preview'. These should be processes required by all sites, not a specific
# site. Site specific DB/Asset tasks should now go into the tugboat-build.sh script

#if [ ${TUGBOAT_GITHUB_HEAD} == "master" ]; then
  # DB sync MUST come before filesync always
  drush -r "${DOCROOT}" sql:sync "@alliance.01live" @self -y
  drush -r "${DOCROOT}" rsync "@alliance.01live":%files @self:%files -y

#fi
