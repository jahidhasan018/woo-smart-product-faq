<?php
/**
 * Class Activator
 *
 * Handles all tasks that run on plugin activation:
 * - Creates the custom DB table via dbDelta.
 * - Seeds default option values.
 * - Flushes rewrite rules after CPT registration.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Core
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Core;

/**
 * Plugin activation handler.
 *
 * @since 1.0.0
 */
class Activator {

	/**
	 * Runs on plugin activation.
	 *
	 * Creates the relationship table, seeds options, and flushes rewrite rules.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate(): void {
		self::create_tables();
		self::seed_default_options();
		flush_rewrite_rules();
	}

	/**
	 * Creates the wsf_faq_relationships table using dbDelta.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 * @return void
	 */
	private static function create_tables(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'wsf_faq_relationships';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			faq_id      BIGINT(20) UNSIGNED NOT NULL,
			object_id   BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			object_type ENUM('product','category','tag','global') NOT NULL,
			sort_order  INT(11)             NOT NULL DEFAULT 0,
			created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY   faq_object   (faq_id, object_id, object_type),
			KEY          object_lookup (object_id, object_type)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'wsf_db_version', WSF_VERSION );
	}

	/**
	 * Seeds default plugin options if they do not yet exist.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function seed_default_options(): void {
		$defaults = [
			'wsf_general_settings'  => [
				'tab_title'     => __( 'FAQs', 'woo-smart-product-faq' ),
				'open_first'    => false,
				'animation'     => true,
				'default_state' => 'collapsed',
			],
			'wsf_display_settings'  => [
				'enable_product'  => true,
				'enable_archive'  => false,
				'enable_shop'     => false,
				'enable_cart'     => false,
				'enable_checkout' => false,
				'tab_priority'    => 25,
			],
			'wsf_style_settings'    => [
				'layout'     => 'accordion',
				'columns'    => 1,
				'custom_css' => '',
			],
			'wsf_advanced_settings' => [
				'schema_markup'   => true,
				'cache_ttl'       => HOUR_IN_SECONDS,
				'wpml_compat'     => true,
				'polylang_compat' => true,
				'rtl_support'     => true,
				'debug_mode'      => false,
			],
		];

		foreach ( $defaults as $option_key => $option_value ) {
			if ( false === get_option( $option_key ) ) {
				add_option( $option_key, $option_value );
			}
		}
	}
}
