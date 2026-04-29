<?php
/**
 * Plugin Name:       WooCommerce Smart FAQ
 * Plugin URI:        https://github.com/jahidhasan018/woo-smart-product-faq
 * Description:       Add unlimited FAQs to WooCommerce products, categories, and globally with schema markup, shortcodes, and Gutenberg blocks.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Jahid Hasan
 * Author URI:        https://github.com/jahidhasan018
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woo-smart-product-faq
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:      9.0
 *
 * @package WooSmartFaq
 */

declare( strict_types=1 );

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version constant.
define( 'WSF_VERSION', '1.0.0' );

// Absolute path to the plugin directory (with trailing slash).
define( 'WSF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// URL to the plugin directory (with trailing slash).
define( 'WSF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Absolute path to the main plugin file.
define( 'WSF_PLUGIN_FILE', __FILE__ );

// Minimum required WooCommerce version.
define( 'WSF_MIN_WC_VERSION', '8.0' );

// Declare HPOS (High-Performance Order Storage) compatibility.
add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
		}
	}
);

/**
 * Checks whether WooCommerce is active and meets the minimum version requirement.
 *
 * @since 1.0.0
 * @return bool
 */
function wsf_is_woocommerce_active(): bool {
	return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, WSF_MIN_WC_VERSION, '>=' );
}

/**
 * Displays an admin notice when WooCommerce is missing or too old.
 *
 * @since 1.0.0
 * @return void
 */
function wsf_missing_wc_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: plugin name, 2: minimum WC version */
		esc_html__(
			'%1$s requires WooCommerce %2$s or higher. Please install or update WooCommerce.',
			'woo-smart-product-faq'
		),
		'<strong>WooCommerce Smart FAQ</strong>',
		esc_html( WSF_MIN_WC_VERSION )
	);

	printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
}

/**
 * Loads the Composer autoloader and boots the plugin.
 *
 * Bailed early if WooCommerce is not active or the autoloader is missing
 * (the latter only happens in a badly packaged build).
 *
 * @since 1.0.0
 * @return void
 */
function wsf_run(): void {
	$autoloader = WSF_PLUGIN_DIR . 'vendor/autoload.php';

	if ( ! file_exists( $autoloader ) ) {
		add_action(
			'admin_notices',
			static function (): void {
				echo '<div class="notice notice-error"><p>' .
					esc_html__( 'WooCommerce Smart FAQ: Composer dependencies are missing. Run `composer install`.', 'woo-smart-product-faq' ) .
					'</p></div>';
			}
		);
		return;
	}

	require_once $autoloader;

	if ( ! wsf_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'wsf_missing_wc_notice' );
		return;
	}

	\WooSmartFaq\Core\Plugin::instance()->init();
}

/**
 * Activation hook callback — loads autoloader then calls Activator::activate().
 *
 * @since 1.0.0
 * @return void
 */
function wsf_activate(): void {
	$autoloader = WSF_PLUGIN_DIR . 'vendor/autoload.php';
	if ( file_exists( $autoloader ) ) {
		require_once $autoloader;
	}
	\WooSmartFaq\Core\Activator::activate();
}

/**
 * Deactivation hook callback.
 *
 * @since 1.0.0
 * @return void
 */
function wsf_deactivate(): void {
	$autoloader = WSF_PLUGIN_DIR . 'vendor/autoload.php';
	if ( file_exists( $autoloader ) ) {
		require_once $autoloader;
	}
	\WooSmartFaq\Core\Deactivator::deactivate();
}

// Register activation / deactivation hooks before anything else fires.
register_activation_hook( __FILE__, 'wsf_activate' );
register_deactivation_hook( __FILE__, 'wsf_deactivate' );

// Boot the plugin after all other plugins have loaded.
add_action( 'plugins_loaded', 'wsf_run' );
