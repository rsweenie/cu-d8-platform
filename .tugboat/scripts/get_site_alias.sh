#!/bin/bash

#TUGBOAT_PREVIEW=feature/alliance/wrike-375584714-tugboat-multisite

## We are going to grab a value based on the current branch name to use a a switch in various parts of
## the tugboat setup. Example: The value for a branch named 'feature/sitename/ticket-12345' would be 'sitename'.

## this will only match */*/* format, else we assume no site name in branch.
regex='^([^\/]*)/([^\/]*)/([^\/]*)$'

## Default to our baseline 'CCP' site
DEFAULT_ALIAS='demo';

# We cannot reliably set ENV vars in the tugboat environment, so our scripts
# will each call this script directly.

# If this is a PR, Tugboat sets this
if [[ $TUGBOAT_GITHUB_HEAD  =~ $regex ]]; then
  echo -n ${BASH_REMATCH[2]}
# If this is manual branch build, Tugboat sets this
elif [[ $TUGBOAT_PREVIEW =~ $regex ]]; then
  echo -n ${BASH_REMATCH[2]}
else
  echo -n $DEFAULT_ALIAS;
fi
