#!/bin/bash
# Run Backstop test

site_name=$($TUGBOAT_ROOT/.tugboat/scripts/get_site_alias.sh)

cd $TUGBOAT_ROOT/tests/backstopjs

cmd_test="./node_modules/backstopjs/cli/index.js test --configPath=backstop.js --pathfile=paths/$site_name --testhost=$TUGBOAT_SERVICE_URL"

eval $cmd_test

echo "Copy and paste the below to run the test:
cd $TUGBOAT_ROOT/tests/backstopjs; ./node_modules/backstopjs/cli/index.js test --configPath=backstop.js --pathfile=paths/$site_name --testhost=$TUGBOAT_SERVICE_URL
"
echo $TUGBOAT_ROOT'/sites/default/files/html_report/'

