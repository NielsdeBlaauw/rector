#!/bin/bash
########################################################################
# This bash script downgrades Rector code and its vendor to PHP 7.1
########################################################################

# show errors
set -e

bin/rector process src packages rules --config build/config/config-downgrade.php
bin/rector vendor --config build/config/config-downgrade.php
