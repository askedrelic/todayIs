#!/usr/bin/env sh
set -ex

USER=$USER PASS=$PASS DB_HOST=$DB_HOST  DB_USER=$DB_USER DB_PASS=$DB_PASS php RUN_CLI.php
