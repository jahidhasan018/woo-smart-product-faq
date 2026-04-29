<?php
/**
 * Trait Cache_Helper_Trait
 *
 * Provides shared helpers for building cache keys and flushing cache groups.
 * Works with both the object cache (Redis/Memcached) and the transient fallback.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Traits
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Traits;

/**
 * Cache key construction and group-flush helpers.
 *
 * @since 1.0.0
 */
trait Cache_Helper_Trait {

	/**
	 * The default cache group used by this plugin.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $cache_group = 'wsf';

	/**
	 * Builds a normalised, prefixed cache key from one or more string parts.
	 *
	 * @since 1.0.0
	 * @param string ...$parts Parts to join with underscores.
	 * @return string          Resulting cache key (max 172 chars for compat).
	 */
	protected function build_cache_key( string ...$parts ): string {
		$key = implode( '_', array_filter( $parts ) );
		return substr( 'wsf_' . $key, 0, 172 );
	}

	/**
	 * Flushes all transient-based cache entries belonging to a group.
	 *
	 * Because WP object cache groups can only be flushed by a persistent
	 * cache backend (Redis/Memcached), this method targets the transient
	 * entries stored in the options table as a reliable fallback.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 * @param string $group Cache group prefix to flush.
	 * @return void
	 */
	protected function flush_group( string $group ): void {
		global $wpdb;

		// Flush object cache group if a persistent cache is available.
		wp_cache_flush_group( $group );

		// Also clear matching transients from the options table.
		$like = $wpdb->esc_like( '_transient_wsf_' ) . '%';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$like
			)
		);
	}
}
