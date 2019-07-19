#!/bin/bash

#TUGBOAT_PREVIEW=feature/alliance/wrike-375584714-tugboat-multisite

## this will only match */*/* format, else we assume no site name in branch
regex='^([^\/]*)/([^\/]*)/([^\/]*)$'

if [[ $TUGBOAT_PREVIEW =~ $regex ]]; then
  export CU_SITE_NAME=${BASH_REMATCH[2]}
else
  export CU_SITE_NAME=none
fi
