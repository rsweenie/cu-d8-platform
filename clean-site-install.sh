#!/bin/bash
# Rub bare site install
set -e
echo 'Please select the local development environment you are using (vagrant, lando): '
devoptions=('vagrant' 'lando')
declare local
select option in "${devoptions[@]}"
do
    case $option in 
        'vagrant'|'lando')
            local=$option
            break
            ;;
        *)
            echo "$REPLY is not a valid option. Please try again: "
            ;;
    esac
done

if [[ $local = "lando" ]]
then
    local=$local
elif [[ $local = "vagrant" ]]
then
    local=""
fi

echo 'Please enter the name of the site you would like to copy files from (alliance, hrnew, demo, etc.): '
read site_name

echo 'Please select the site environment you want to use (live, test, dev): '
options=('01live' '01test' '01dev')
declare env
select option in "${options[@]}"
do
    case $option in 
        '01live'|'01test'|'01dev')
            env=$option
            break
            ;;
        *)
            echo "$REPLY is not a valid option. Please try again: "
            ;;
    esac
done

site_alias="@$site_name.$env"

# Does all the things from the selected Acquia site to the local environment
eval $local drush sql-drop -y; $local drush sql:sync $site_alias @self -y; $local drush rsync $site_alias:%files @self:%files -y; $local composer install; $local drush cim -y; $local drush updatedb -y; $local drush cron; $local drush cr; $local drush uli 

