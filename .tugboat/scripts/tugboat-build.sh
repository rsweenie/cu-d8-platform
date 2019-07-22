#!/bin/bash

#
# Site specific build steps. Does NOT include anything done globally. That goes in config.yml. This script will
# probably contain little or nothing.
#

CU_SITE_ALIAS=$(`dirname "$0"`/get_site_alias.sh)

case $CU_SITE_ALIAS in
  alliance)
  ;;
  grad-site)
  ;;
  none)
  ;;
  *)
    echo "Could not determine site alias"
    exit 1
  ;;
esac