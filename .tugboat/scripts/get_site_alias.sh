#!/bin/bash

#TUGBOAT_PREVIEW=feature/alliance/wrike-375584714-tugboat-multisite

## this will only match */*/* format, else we assume no site name in branch
regex='^([^\/]*)/([^\/]*)/([^\/]*)$'

# We cannot reliably set ENV vars in the tugboat environment, so our scripts
# will each call this script directly.
if [[ $TUGBOAT_PREVIEW =~ $regex ]]; then
  echo -n ${BASH_REMATCH[2]} 
else
  echo -n "none" 
fi
