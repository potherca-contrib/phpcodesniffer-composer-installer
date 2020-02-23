#!/usr/bin/env bash

set -o errexit -o errtrace -o nounset -o pipefail

generate_changelog() {
    local -r sVersion="${1?One parameter required: <release-to-generate>}"

    sChangelog="$(
        github_changelog_generator                                                                              \
            --user Dealerdirect                                                                                 \
            --project phpcodesniffer-composer-installer                                                         \
            --token "$(cat ~/.github-token)"                                                                    \
            --future-release "${sVersion}"                                                                      \
            --enhancement-label '### Changes'                                                                   \
            --bugs-label '### Fixes'                                                                            \
            --issues-label '### Closes'                                                                         \
            --usernames-as-github-logins                                                                        \
            --bug-labels 'bug - confirmed'                                                                      \
            --enhancement-labels  'improvement','documentation','builds / deploys / releases','feature request' \
            --exclude-labels 'bug - unconfirmed',"can't reproduce / won't fix",'invalid','triage'               \
            --unreleased-only                                                                                   \
            --output '' 2>/dev/null
    )"

    echo "${sChangelog}" | sed -E 's/\[\\(#[0-9]+)\]\([^)]+\)/\1/' | head -n -3
}

if [[ "${BASH_SOURCE[0]}" != "$0" ]]; then
    export -f generate_changelog
else
    generate_changelog "${@}"
    exit $?
fi
