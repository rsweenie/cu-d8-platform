#!/bin/bash

#TUGBOAT_PREVIEW=feature/alliance/wrike-375584714-tugboat-multisite

## this will only match */*/* format, else we assume no site name in branch
regex='^([^\/]*)/([^\/]*)/([^\/]*)$'

# We're writing to a file (that we'll later source from) because it's easier to source from parent process than to
# set parent ENV from here
if [[ $TUGBOAT_PREVIEW =~ $regex ]]; then
  echo "export CU_SITE_NAME=${BASH_REMATCH[2]}" > CU_SITE_NAME.sh
else
  echo "export CU_SITE_NAME=none" > CU_SITE_NAME.sh
fi
