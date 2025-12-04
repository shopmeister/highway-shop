#!/usr/bin/env bash

#
# 888888ba                 dP  .88888.                    dP
# 88    `8b                88 d8'   `88                   88
# 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
# 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
# 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
# dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
#
#                          m a g n a l i s t e r
#                                      boost your Online-Shop
#
# -----------------------------------------------------------------------------
# (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
#     Released under the MIT License (Expat)
# -----------------------------------------------------------------------------
#

PLATFORM_ROOT="$(git rev-parse --show-toplevel)"
PROJECT_ROOT="${PROJECT_ROOT:-"$(cd "$PLATFORM_ROOT"/.. && git rev-parse --show-toplevel)"}"
AUTOLOAD_FILE="$PROJECT_ROOT/vendor/autoload.php"

function onExit {
    if [[ $? != 0 ]]
    then
        echo "Fix the error before commit."
    fi
}
trap onExit EXIT

PHP_FILES="$(git diff --cached --name-only --diff-filter=ACMR HEAD | grep -E '\.(php)$')"
JS_FILES="$(git diff --cached --name-only --diff-filter=ACMR HEAD | grep -E '\.(js)$')"

# exit on non-zero return code
set -e

if [[ -z "$PHP_FILES" && -z "$JS_FILES" ]]
then
    exit 0
fi

if [[ -n "$PHP_FILES" ]]
then
    for FILE in ${PHP_FILES}
    do
        php -l -d display_errors=0 "$FILE" 1> /dev/null
    done

    php "`dirname \"$0\"`"/../../bin/phpstan-config-generator.php
    php ../../../dev-ops/analyze/vendor/bin/phpstan analyze --no-progress --configuration phpstan.neon --autoload-file="$AUTOLOAD_FILE" ${PHP_FILES}
fi

UNSTAGED_FILES="$(git diff --name-only -- ${PHP_FILES} ${JS_FILES})"

if [[ -n "$UNSTAGED_FILES" ]]
then
    echo "Error: There are staged files with unstaged changes. We cannot automatically fix and add those.

Please add or revert the following files:

$UNSTAGED_FILES
"
    exit 1
fi

if [[ -n "$PHP_FILES" ]]
then
    # fix code style and update the commit
    php ../../../dev-ops/analyze/vendor/bin/php-cs-fixer fix --config=../../../vendor/shopware/platform/.php_cs.dist --quiet -vv ${PHP_FILES}
fi

if [[ -n "$JS_FILES" && -x ../../../vendor/shopware/platform/src/Administration/Resources/administration/node_modules/.bin/eslint ]]
then
    ../../../vendor/shopware/platform/src/Administration/Resources/administration/node_modules/.bin/eslint --config ../../../vendor/shopware/platform/src/Administration/Resources/administration/.eslintrc.js --ext .js,.vue --fix ${JS_FILES}
fi

git add ${JS_FILES} ${PHP_FILES}
