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

run_build() {
    readonly sPhpVersion="${1?One parameter require: <PHP_VERSION>}"

    local bRunLinters

    case "${sPhpVersion}" in

        7.1*)
            bRunLinters=true
        ;;

        *)
            bRunLinters=false
        ;;
    esac

    if [[ ${sRunLinters} = true ]];then
        find . -type f -name "*.json" -print0 | xargs -0 -n1 jsonlint -q
        find . -type f -name "*.php" -print0 | xargs -0 -n1 php -l
        php "${HOME}/bin/phpcs.phar" --standard=psr2 src/
        composer validate
    fi

    travis_wait composer install --no-interaction --no-progress --no-scripts --no-suggest --optimize-autoloader --prefer-dist --verbose

    if [[ ${sRunLinters} = true ]];then
        php "${HOME}/bin/security-checker.phar" -n security:check --end-point=http://security.sensiolabs.org/check_lock
    fi

    php phpunit.phar
}

# ==============================================================================
#                               RUN LOGIC
# ------------------------------------------------------------------------------
if [[ "${BASH_SOURCE[0]}" != "${0}" ]]; then
  export -f run_build
else
  run_build "${@}"
  exit $?
fi
# ==============================================================================
