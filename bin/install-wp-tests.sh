#!/usr/bin/env bash
# Installs the WordPress PHPUnit test suite.
# Usage: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]

set -e

DB_NAME=${1:-wordpress_test}
DB_USER=${2:-root}
DB_PASS=${3:-}
DB_HOST=${4:-localhost}
WP_VERSION=${5:-latest}

WP_TESTS_DIR=${WP_TESTS_DIR:-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR:-/tmp/wordpress/}

install_wp() {
    if [ -d "$WP_CORE_DIR" ]; then
        return;
    fi

    mkdir -p "$WP_CORE_DIR"

    if [ "$WP_VERSION" = 'latest' ]; then
        local ARCHIVE_NAME='latest'
    else
        local ARCHIVE_NAME="wordpress-$WP_VERSION"
    fi

    curl -s "https://wordpress.org/$ARCHIVE_NAME.tar.gz" | tar --strip-components=1 -zx -C "$WP_CORE_DIR"
}

install_test_suite() {
    if [ -d "$WP_TESTS_DIR" ]; then
        return;
    fi

    mkdir -p "$WP_TESTS_DIR"
    svn co --quiet "https://develop.svn.wordpress.org/tags/$WP_VERSION/tests/phpunit/includes/" "$WP_TESTS_DIR/includes" 2>/dev/null || \
    svn co --quiet "https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
    svn co --quiet "https://develop.svn.wordpress.org/tags/$WP_VERSION/tests/phpunit/data/" "$WP_TESTS_DIR/data" 2>/dev/null || \
    svn co --quiet "https://develop.svn.wordpress.org/trunk/tests/phpunit/data/" "$WP_TESTS_DIR/data"

    cat > "$WP_TESTS_DIR/wp-tests-config.php" << EOF
<?php
define( 'ABSPATH', '${WP_CORE_DIR}' );
define( 'WP_DEFAULT_THEME', 'storefront' );
define( 'DB_NAME', '${DB_NAME}' );
define( 'DB_USER', '${DB_USER}' );
define( 'DB_PASSWORD', '${DB_PASS}' );
define( 'DB_HOST', '${DB_HOST}' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
\$table_prefix = 'wptests_';
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
EOF
}

create_db() {
    mysql -u "$DB_USER" --password="$DB_PASS" --host="$DB_HOST" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" 2>/dev/null
}

install_wp
install_test_suite
create_db
