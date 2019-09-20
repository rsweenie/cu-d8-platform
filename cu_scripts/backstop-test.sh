#!/bin/bash
# Run Backstop test
set -e
echo 'Please enter in the site you would like to test: '

read site_name

if [[ $site_name = "alliance" ]]
then
    site_url=$site_name'.creighton.edu'
else
    site_url=$site_name'.creighton.acsitefactory.com'
fi

cmd_ref="backstop reference --configPath=backstop.js --pathfile=paths/$site_name --refhost=http://$site_name.creighton.acsitefactory.com/"
cmd_test="backstop test --configPath=backstop.js --pathfile=paths/$site_name --testhost=http://local.creighton.com/"

eval $cmd_ref 
eval $cmd_test
