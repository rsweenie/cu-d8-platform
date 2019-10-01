#!/bin/bash
# Run Backstop test

site_name=$($TUGBOAT_ROOT/.tugboat/scripts/get_site_alias.sh)

cd $TUGBOAT_ROOT/tests/backstopjs

if [[ $site_name = "alliance" ]]
then
    site_url=$site_name'.creighton.edu'
else
    site_url=$site_name'.creighton.acsitefactory.com'
fi

cmd_ref="./node_modules/backstopjs/cli/index.js reference --configPath=backstop.js --pathfile=paths/$site_name --refhost=$site_name'.creighton.acsitefactory.com'"
cmd_test="./node_modules/backstopjs/cli/index.js test --configPath=backstop.js --pathfile=paths/$site_name --testhost=$TUGBOAT_SERVICE_URL"


echo $site_name
eval $cmd_ref 
eval $cmd_test
