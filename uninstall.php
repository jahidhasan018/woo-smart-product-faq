<?php
/**
 * Uninstall routine — runs when the plugin is deleted from WP Admin.
 *
 * Removes ALL plugin data: custom table, options, and transients.
 * Full implementation is completed in Phase 8.
 *
 * @since   1.0.0
 * @package WooSmartFaq
 * @license GPL-2.0-or-later
 */

// Abort if not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Drop the custom relationship table.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wsf_faq_relationships" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// Delete plugin options.
$options = [
	'wsf_general_settings',
	'wsf_display_settings',
	'wsf_style_settings',
	'wsf_advanced_settings',
	'wsf_db_version',
];
foreach ( $options as $option ) {
	delete_option( $option );
}

// Delete all wsf_* transients.
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_wsf_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_wsf_' ) . '%'
	)
);
