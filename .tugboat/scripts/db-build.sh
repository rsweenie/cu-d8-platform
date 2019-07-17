#!/bin/bash

DB_FILE="${TUGBOAT_ROOT}/.tugboat/db/${TUGBOAT_GITHUB_HEAD}.sql.gz"

#
# Tries to import a branch specific DB first, else falls back on a base copy.
#
if [[ -f "${DB_FILE}" ]]; then
  zcat "${DB_FILE}" | mysql tugboat
elif [[ -f "${TUGBOAT_ROOT}/.tugboat/db/base.sql.gz" ]]; then
  zcat "${TUGBOAT_ROOT}/.tugboat/db/base.sql.gz" | mysql tugboat
fi
