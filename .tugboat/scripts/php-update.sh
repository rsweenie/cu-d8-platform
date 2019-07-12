#!/bin/bash

if [ ${TUGBOAT_GITHUB_HEAD} == "master" ]; then
  # DB sync MUST come before filesync always
  drush -r "${DOCROOT}" sql:sync "@alliance.01live" @self -y
  drush -r "${DOCROOT}" rsync "@alliance.01live":%files @self:%files -y

  # The rsync breaks settings, so them fix again
  chown -R www-data:www-data "${DOCROOT}/sites/default/files"
  find "${DOCROOT}/sites/default/files" -type d -exec chmod 775 {} \;
  find "${DOCROOT}/sites/default/files" -type f -exec chmod 664 {} \;

#elif [ ${TUGBOAT_GITHUB_HEAD} == "other" ]; then
  # do something

fi
