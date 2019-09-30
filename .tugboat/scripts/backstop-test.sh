#!/bin/bash
# Run Backstop test
set -e

site_name=$(`dirname "$0"`/get_site_alias.sh)


if [[ $site_name = "alliance" ]]
then
    site_url=$site_name'.creighton.edu'
else
    site_url=$site_name'.creighton.acsitefactory.com'
fi

cmd_ref="./node_modules/backstopjs/cli/index.js reference --configPath=backstop.js --pathfile=paths/$site_name --refhost=http://$site_url"
cmd_test="./node_modules/backstopjs/cli/index.js test --configPath=backstop.js --pathfile=paths/$site_name --testhost=$TUGBOAT_SERVICE_URL"

eval $cmd_ref 
eval $cmd_test