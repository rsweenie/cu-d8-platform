#!/bin/bash
# Copy files down from selected Acquia site

echo 'Please enter the name of the site you would like to copy files from: '
read site_name
echo 'Please select the environment to copy files from: '
options=('01dev' '01test' '01live')
declare env
select option in "${options[@]}"
do
    case $option in 
        '01dev'|'01test'|'01live')
            env=$option
            break
            ;;
        *)
            echo "$REPLY is not a valid option. Please try again: "
            ;;
    esac
done

site_alias="@$site_name.$env"

# Finds public file path for selected Acquia site
public_files="Files, Public : "
declare file_path
while read line
do
    if echo $line | grep -q "$public_files";
    then
        prefix=$(echo "$public_files" | sed 's/ //g')
        new_line=$(echo "$line" | sed 's/ //g')
        file_path=$(echo "${new_line#$prefix}" | tr '\r' ' ' | sed 's/ //g')
    fi;
done < <(drush $site_alias core:status)

# If alias is incorrect, the return will be
# [preflight] The alias @$site_alias could not be found.

# Copies images with image styles from the selected Acquia site to the local environment
eval drush rsync --debug --exclude-paths /acsf_tmp:/bootstrap:/media-icons:/xmlsitemap:/.htaccess "$site_alias:/var/www/html/creighton.$env/docroot/$file_path @self:/var/www/creighton/docroot/sites/default/ -y"
