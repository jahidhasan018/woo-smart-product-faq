<?php
/**
 * Class Plugin
 *
 * Main plugin bootstrap. Wires all modules together and starts the plugin.
 * Uses the Singleton_Trait so only one instance exists per request.
 *
 * Dependency wiring order:
 * 1. i18n — loads text domain as early as possible.
 * 2. CPT & Taxonomies — must register on 'init'.
 * 3. Admin modules — only loaded in wp-admin context.
 * 4. REST controllers — loaded on 'rest_api_init'.
 * 5. Display modules — loaded on front-end.
 * 6. Blocks — registered on 'init'.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Core
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Core;

use WooSmartFaq\Traits\Singleton_Trait;
use WooSmartFaq\CPT\FAQ_Post_Type;
use WooSmartFaq\CPT\FAQ_Category_Taxonomy;
use WooSmartFaq\CPT\FAQ_Tag_Taxonomy;
use WooSmartFaq\i18n\I18n;
use WooSmartFaq\Cache\FAQ_Cache;
use WooSmartFaq\Repository\FAQ_Repository;
use WooSmartFaq\Repository\Settings_Repository;

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class Plugin {

	use Singleton_Trait;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $version;

	/**
	 * Cache layer instance.
	 *
	 * @since 1.0.0
	 * @var FAQ_Cache
	 */
	private FAQ_Cache $cache;

	/**
	 * FAQ repository instance.
	 *
	 * @since 1.0.0
	 * @var FAQ_Repository
	 */
	private FAQ_Repository $faq_repository;

	/**
	 * Settings repository instance.
	 *
	 * @since 1.0.0
	 * @var Settings_Repository
	 */
	private Settings_Repository $settings_repository;

	/**
	 * Sets up the plugin version from the constant.
	 *
	 * Private — use Plugin::instance() to obtain the singleton.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		global $wpdb;

		$this->version             = defined( 'WSF_VERSION' ) ? WSF_VERSION : '1.0.0';
		$this->cache               = new FAQ_Cache();
		$this->faq_repository      = new FAQ_Repository( $wpdb, $this->cache );
		$this->settings_repository = new Settings_Repository();
	}

	/**
	 * Boots the plugin by initialising all registered modules.
	 *
	 * Modules are initialised in dependency order. Each module's init()
	 * method registers its own hooks — no hooks fire until WordPress
	 * processes its hook queue after this method returns.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		$this->load_i18n();
		$this->register_post_types();
		$this->register_taxonomies();

		do_action( 'wsf_plugin_loaded' );
	}

	/**
	 * Initialises the text domain loader.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function load_i18n(): void {
		( new I18n() )->init();
	}

	/**
	 * Initialises all custom post types.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_post_types(): void {
		( new FAQ_Post_Type() )->init();
	}

	/**
	 * Initialises all custom taxonomies.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_taxonomies(): void {
		( new FAQ_Category_Taxonomy() )->init();
		( new FAQ_Tag_Taxonomy() )->init();
	}

	/**
	 * Returns the plugin version string.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Returns the FAQ cache instance.
	 *
	 * @since 1.0.0
	 * @return FAQ_Cache
	 */
	public function get_cache(): FAQ_Cache {
		return $this->cache;
	}

	/**
	 * Returns the FAQ repository instance.
	 *
	 * @since 1.0.0
	 * @return FAQ_Repository
	 */
	public function get_faq_repository(): FAQ_Repository {
		return $this->faq_repository;
	}

	/**
	 * Returns the settings repository instance.
	 *
	 * @since 1.0.0
	 * @return Settings_Repository
	 */
	public function get_settings_repository(): Settings_Repository {
		return $this->settings_repository;
	}
}
