#!/bin/bash

# These processes will affect any 'base previews'. Building from a preview will skip these steps. Site specific
# DB/Asset tasks should now go into the tugboat-build.sh script

CU_SITE_ALIAS=$(`dirname "$0"`/get_site_alias.sh)


case $CU_SITE_ALIAS in
  alliance)
  ;;
  grad-site)
  ;;
  # DEFAULT
  *)

  ;;
esac
