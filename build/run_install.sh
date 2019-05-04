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

run_install() {
    readonly sPhpVersion="${1?One parameter require: <PHP_VERSION>}"

    case "${sPhpVersion}" in

        7.1*)
            [[ -f "${HOME}/bin/phpcs.phar" ]] || curl -L -o "${HOME}/bin/phpcs.phar" 'https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar'

            [[ -f "${HOME}/bin/security-checker.phar" ]] || curl -L -o "${HOME}/bin/security-checker.phar" 'http://get.sensiolabs.org/security-checker.phar'

            npm install -g jsonlint
        ;;
    esac
}

# ==============================================================================
#                               RUN LOGIC
# ------------------------------------------------------------------------------
if [[ "${BASH_SOURCE[0]}" != "${0}" ]]; then
  export -f run_install
else
  run_install "${@}"
  exit $?
fi
# ==============================================================================
