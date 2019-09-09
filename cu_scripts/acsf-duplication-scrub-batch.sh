#!/bin/bash
# Run scrubbing functions that fix issues with ACSF SSO login
# Assumes alias files are up-to-date - uses drush/sites to find site names and environments

environments=( dev test live )
for file in "drush/sites"/*
do
    site_name=${file#drush/sites/}
    site_name=${site_name%.site.yml}
    if [ $site_name != README.md ] && [ ${site_name:0:2} != 01 ]; 
    then
        for env in ${environments[@]}
        do 
            if grep -wq "$env" $file ;
            then
                echo "Scrubbing $site_name $env ..."
                drush @"$site_name"."$env" acsf-duplication-scrub-batch "$site_name" creighton
            fi;
        done
    fi;
done
