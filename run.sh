#!/usr/bin/env sh
set -ex

# Make dokku env variables available to php
USER=$SHACKUSER PASS=$SHACKPASS DB_HOST=$DB_HOST DB_USER=$DB_USER DB_PASS=$DB_PASS php RUN_CLI.php
