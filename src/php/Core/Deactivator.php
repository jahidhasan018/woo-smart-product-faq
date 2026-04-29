<?php
/**
 * Class Deactivator
 *
 * Handles tasks that run when the plugin is deactivated.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Core
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Core;

/**
 * Plugin deactivation handler.
 *
 * @since 1.0.0
 */
class Deactivator {

	/**
	 * Runs on plugin deactivation.
	 *
	 * Flushes rewrite rules so that CPT permalinks are removed cleanly.
	 * Transients and cached data are NOT deleted on deactivation — only
	 * on uninstall (see uninstall.php).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
