<?php
/**
 * Class I18n
 *
 * Loads the plugin text domain on the plugins_loaded hook so that
 * all translatable strings are available as early as possible.
 *
 * @since   1.0.0
 * @package WooSmartFaq\i18n
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\i18n;

/**
 * Internationalisation loader.
 *
 * @since 1.0.0
 */
class I18n {

	/**
	 * Hooks the text domain loader onto plugins_loaded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * The /languages directory is located at WSF_PLUGIN_DIR . 'languages/'.
	 * WP will also look in wp-content/languages/plugins/ for community-contributed
	 * translations before falling back to the plugin's bundled .mo files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'woo-smart-product-faq',
			false,
			dirname( plugin_basename( WSF_PLUGIN_FILE ) ) . '/languages'
		);
	}
}
