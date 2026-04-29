<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package WooSmartFaq\Tests
 */

// Load Composer autoloader.
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Load WP test suite.
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find WordPress test suite at '{$_tests_dir}/includes/functions.php'.\n";
    echo "Run: bash bin/install-wp-tests.sh\n";
    exit( 1 );
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
    'muplugins_loaded',
    static function () {
        require dirname( __DIR__, 2 ) . '/woo-smart-product-faq.php';
    }
);

require $_tests_dir . '/includes/bootstrap.php';
