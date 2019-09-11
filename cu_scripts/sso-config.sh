#!/bin/bash
# Sets sp_entity id on site install

# takes site name and environment as an argument
# $1 = site name
# $2 = environment
# Finds site id for selected Acquia site

function config_status {
    while read line
    do
        if echo $line | grep -q "$site_path";
        then
            echo $line
            new_line=$(echo "$line" | sed 's/ //g')
            prefix=$(echo "$site_path:sites/g/files/indaly" | sed 's/ //g')
            if [ "$2" == "01dev" ];
            then
                suffix="dev"
                site_id=$(echo "${new_line#$prefix}" | tr '\r' ' ' | sed 's/ //g')
                site_id=$(echo "${site_id%$suffix}")
            elif [ "$2" == "01test" ];
            then
                suffix="test"
                site_id=$(echo "${new_line#$prefix}" | tr '\r' ' ' | sed 's/ //g')
                site_id=$(echo "${site_id%$suffix}")
            else
                site_id=$(echo "${new_line#$prefix}" | tr '\r' ' ' | sed 's/ //g')
            fi; 
        fi;
    done < <(/mnt/www/html/creighton"$2"/vendor/bin/drush @self core:status --uri="$1"."$uri_prefix"creighton.acsitefactory.com --no-interaction -v --ansi)
}

site_path="Site path"
uri_prefix=""

if [ "$2" == "01dev" ] || [ "$2" == "01test" ];
then
    env_prefix="01"
    uri_prefix="${2#$env_prefix}-"
fi;

config_status $1 $2

# Runs a config import so that samlauth.authentication exists on the new site prior to attempting to modify the values inside the file
eval "/mnt/www/html/creighton"$2"/vendor/bin/drush @self cim -y --uri="$1"."$uri_prefix"creighton.acsitefactory.com --no-interaction -v --ansi"

# Sets sp_entity_id, idp_entity_id, idp_single_sign_on_service, and idp_single_log_out_service for ACSF SSO into the selected site
eval "/mnt/www/html/creighton"$2"/vendor/bin/drush @self cset samlauth.authentication sp_entity_id urn:acquia:acsf:saml:sp:creighton:"$2":"$site_id" -y --uri="$1"."$uri_prefix"creighton.acsitefactory.com --no-interaction -v --ansi"
eval "/mnt/www/html/creighton"$2"/vendor/bin/drush @self cset samlauth.authentication idp_entity_id https://www."$uri_prefix"creighton.acsitefactory.com/sso/saml2/idp/metadata.php -y --uri="$1"."$uri_prefix"creighton.acsitefactory.com --no-interaction -v --ansi"
eval "/mnt/www/html/creighton"$2"/vendor/bin/drush @self cset samlauth.authentication idp_single_sign_on_service https://www."$uri_prefix"creighton.acsitefactory.com/sso/saml2/idp/SSOService.php -y --uri="$1"."$uri_prefix"creighton.acsitefactory.com --no-interaction -v --ansi"
eval "/mnt/www/html/creighton"$2"/vendor/bin/drush @self cset samlauth.authentication idp_single_log_out_service https://www."$uri_prefix"creighton.acsitefactory.com/sso/saml2/idp/SingleLogoutService.php -y --uri="$1"."$uri_prefix"creighton.acsitefactory.com --no-interaction -v --ansi"
