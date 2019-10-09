#!/bin/bash
# Run Backstop test

site_name=$($TUGBOAT_ROOT/.tugboat/scripts/get_site_alias.sh)

cd $TUGBOAT_ROOT/tests/backstopjs

cmd_test="/usr/local/lib/node_modules/backstopjs/cli/index.js test --configPath=backstop.js --pathfile=paths/$site_name --testhost=$TUGBOAT_SERVICE_URL"

eval $cmd_test


echo $TUGBOAT_SERVICE_URL'/sites/default/files/html_report/'

