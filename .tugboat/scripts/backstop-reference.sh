#!/bin/bash
# Run Backstop test

site_name=$($TUGBOAT_ROOT/.tugboat/scripts/get_site_alias.sh)

cd /$TUGBOAT_ROOT/tests/backstopjs

cmd_ref="./node_modules/backstopjs/cli/index.js reference --configPath=backstop.js --pathfile=paths/$site_name --refhost=http://$site_name.creighton.acsitefactory.com"

eval $cmd_ref 

echo "Copy and paste the below to run the backstop test:
/bin/bash ${TUGBOAT_ROOT}/.tugboat/scripts/backstop-test.sh"

