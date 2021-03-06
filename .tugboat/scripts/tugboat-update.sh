#!/bin/bash

# These processes will affect any 'base previews'. Building from a base preview will skip these steps.
# It is likely that nothing will be done in here.

CU_SITE_ALIAS=$(`dirname "$0"`/get_site_alias.sh)

echo "Doing Tugboat update steps for $CU_SITE_ALIAS"

case $CU_SITE_ALIAS in
  demo|hrnew|alliance|grad|hub)
    echo "Nothing to do for ${CU_SITE_ALIAS}"
  ;;
  *)
    echo "Could not determine site alias. Please check Tugboat scripts!"
    exit 1
  ;;
esac
