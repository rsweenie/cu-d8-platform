#!/bin/bash

#
# Site specific post-build steps. Does NOT include anything done globally (the stuff in config.yml).
#

CU_SITE_ALIAS=$(`dirname "$0"`/get_site_alias.sh)

echo "Doing Tugboat post-build steps for $CU_SITE_ALIAS"

case $CU_SITE_ALIAS in
  demo|hrnew|alliance|grad)
    echo "Nothing to do for ${CU_SITE_ALIAS}"
  ;;
  grad-site)
    echo "Reinstalling grad features"
    # Temporary fix to make sure features aren't overridden by config import.
    #drush dre cu_next_steps,cu_grad_interior_page,cu_grad_landing_page -y
  ;;
  none)
    echo "Nothing to do for generic install"
  ;;
  *)
    echo "ERROR: Could not determine site alias"
    exit 1
  ;;
esac
