#!/usr/bin/env bash

# ==============================================================================
# MIT License - Copyright (c) 2017 Dealerdirect B.V.
# ==============================================================================

# ==============================================================================
set -o errexit
set -o errtrace
set -o nounset
set -o pipefail
# ==============================================================================

run_before_install() {

    readonly sPhpVersion="${1?One parameter require: <PHP_VERSION>}"


    case "${sPhpVersion}" in

        7.1*)
            npm set loglevel error

            npm set progress false

            bash ./download_phpunit.sh "${sPhpVersion}"
        ;;
    esac
}

# ==============================================================================
#                               RUN LOGIC
# ------------------------------------------------------------------------------
if [[ "${BASH_SOURCE[0]}" != "${0}" ]]; then
  export -f run_before_install
else
  run_before_install "${@}"
  exit $?
fi
# ==============================================================================
