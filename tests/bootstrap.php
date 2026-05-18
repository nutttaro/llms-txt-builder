<?php
/**
 * PHPUnit bootstrap file.
 *
 * Requires the WordPress test library. Set WP_TESTS_DIR to the path of your
 * wordpress-develop/tests/phpunit/ checkout, or install it via:
 *   bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- bootstrap-only file, not distributed

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', false );
}

$ntllms_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $ntllms_tests_dir ) {
	$ntllms_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( "{$ntllms_tests_dir}/includes/functions.php" ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI only
	echo "Could not find {$ntllms_tests_dir}/includes/functions.php. Set WP_TESTS_DIR." . PHP_EOL;
	exit( 1 );
}

require_once "{$ntllms_tests_dir}/includes/functions.php";

/**
 * Load the plugin during the test bootstrap.
 */
function ntllms_txt_builder_manually_load_plugin() {
	require dirname( __DIR__ ) . '/nt-llms-txt-builder.php';
}
tests_add_filter( 'muplugins_loaded', 'ntllms_txt_builder_manually_load_plugin' );

require "{$ntllms_tests_dir}/includes/bootstrap.php";
