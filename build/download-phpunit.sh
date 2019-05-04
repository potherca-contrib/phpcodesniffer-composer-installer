#!/usr/bin/env bash

# ==============================================================================
# MIT License - Copyright (c) 2017 Dealerdirect B.V.
# ==============================================================================

# ==============================================================================
set -o errexit
set -o errtrace
set -o nounset
set -o pipefail
#-------------------------------------------------------------------------------
readonly EX_OK=0
readonly EX_NOT_ENOUGH_PARAMETERS=65
readonly EX_UNSUPPORTED_PHP_VERSION=66
readonly EX_COULD_NOT_CREATE_SYMLINK=67
# ==============================================================================

# ==============================================================================
download_phpunit() {

    local -i iExitCode iPhpUnitVersion
    local sLinkName sPhpVersion

    iExitCode="${EX_OK}"

    #readonly sLinkName='phpunit.phar'

    if [[ "$#" != 1 ]];then

        iExitCode="${EX_NOT_ENOUGH_PARAMETERS}"

        echo ' !     ERROR: Not enough parameters given' >&2
        echo '              One parameter expected: the PHP version download PhpUnit for' >&2

    elif [[ "$1" = '--help' ]];then

        echo "Usage: $0 <php-version>"
        echo ''
        echo 'Will download the appropriate PhpUnit PHAR file for a given PHP'

    else
        readonly sPhpVersion="${1}"

        case "${sPhpVersion}" in
            5.3*|5.4*|5.5*)
                iPhpUnitVersion=4
            ;;

            5.6*)
                iPhpUnitVersion=5
            ;;

            7.0*|7.1*)
                iPhpUnitVersion=6
            ;;

            *)
                iExitCode="${EX_UNSUPPORTED_PHP_VERSION}"

                echo " !     ERROR: Given PHP version '${sPhpVersion}' is not supported" >&2
                echo '              Use one of 5.3, 5.4, 5.5, 5.6, 7.0 or 7.1' >&2
            ;;
        esac

        if [[ "${iExitCode}" = "${EX_OK}" ]];then
            echo "-----> Fetching available PhpUnit versions"

            sUrl=$(curl --silent 'https://phar.phpunit.de/' | grep -o -E "https://[^/]+/phpunit-${iPhpUnitVersion}(.[0-9+]){2}.phar" | tail -n1)

            echo "-----> Downloading $(basename $sUrl) for PHP${sPhpVersion}"

            curl --silent "${sUrl}" -o "${sLinkName}" || {
                iExitCode="${EX_COULD_NOT_CREATE_SYMLINK}"

                echo ' !     ERROR: Could not create symlink' >&2
            }
        fi
    fi

    return "${iExitCode}"
}
# ==============================================================================

# ==============================================================================
#                               RUN LOGIC
# ------------------------------------------------------------------------------
if [[ "${BASH_SOURCE[0]}" != "${0}" ]]; then
  export -f download_phpunit
else
  download_phpunit "${@}"
  exit $?
fi
# ==============================================================================
