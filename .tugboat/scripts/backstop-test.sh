#!/bin/bash
# Run Backstop test

site_name=$($TUGBOAT_ROOT/.tugboat/scripts/get_site_alias.sh)

cd $TUGBOAT_ROOT/tests/backstopjs

cmd_ref="./node_modules/backstopjs/cli/index.js reference --configPath=backstop.js --pathfile=paths/demo --refhost=http://demo.creighton.acsitefactory.com"
#cmd_test="./node_modules/backstopjs/cli/index.js test --configPath=backstop.js --pathfile=paths/demo --testhost=$TUGBOAT_SERVICE_URL"


echo $site_name
eval $cmd_ref 
#eval $cmd_test
