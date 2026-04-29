<?php
/**
 * Class FAQ_Cache
 *
 * Two-layer caching for FAQ data:
 *   Layer 1 — wp_cache_get/set (Redis / Memcached when available).
 *   Layer 2 — get_transient/set_transient (DB-based persistent fallback).
 *
 * All keys are prefixed with 'wsf_' and a language suffix is appended
 * when WPML or Polylang is active so multilingual sites stay isolated.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Cache
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Cache;

use WooSmartFaq\Contracts\Cache_Interface;

/**
 * FAQ data cache implementation.
 *
 * @since 1.0.0
 */
class FAQ_Cache implements Cache_Interface {

	/**
	 * WordPress object-cache group for all plugin entries.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private const GROUP = 'wsf';

	/**
	 * Maximum allowed length for a cache key (object cache compat).
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private const MAX_KEY_LENGTH = 172;

	/**
	 * Retrieves a cached value.
	 *
	 * Tries the object cache first; falls back to transients.
	 *
	 * @since 1.0.0
	 * @param string $key   Cache key (without prefix).
	 * @param string $group Unused — group is always self::GROUP. Kept for interface compat.
	 * @return mixed        Cached value, or false when not found.
	 */
	public function get( string $key, string $group = '' ): mixed {
		$value = wp_cache_get( $key, self::GROUP );

		if ( false !== $value ) {
			return $value;
		}

		// Fall back to transient (persistent across requests even without object cache).
		$transient = get_transient( $key );

		if ( false !== $transient ) {
			// Warm the object cache so subsequent hits in this request are fast.
			wp_cache_set( $key, $transient, self::GROUP );
		}

		return $transient;
	}

	/**
	 * Stores a value in both the object cache and transients.
	 *
	 * @since 1.0.0
	 * @param string $key        Cache key (without prefix).
	 * @param mixed  $value      The data to cache.
	 * @param string $group      Unused — group is always self::GROUP.
	 * @param int    $expiration TTL in seconds. 0 = use wsf_cache_ttl filter default.
	 * @return bool              True on success.
	 */
	public function set( string $key, mixed $value, string $group = '', int $expiration = 0 ): bool {
		if ( 0 === $expiration ) {
			$expiration = (int) apply_filters( 'wsf_cache_ttl', HOUR_IN_SECONDS );
		}

		wp_cache_set( $key, $value, self::GROUP, $expiration );

		return set_transient( $key, $value, $expiration );
	}

	/**
	 * Deletes a single cache entry from both layers.
	 *
	 * @since 1.0.0
	 * @param string $key   Cache key.
	 * @param string $group Unused — group is always self::GROUP.
	 * @return bool         True on success.
	 */
	public function delete( string $key, string $group = '' ): bool {
		wp_cache_delete( $key, self::GROUP );

		return delete_transient( $key );
	}

	/**
	 * Flushes all entries belonging to the plugin's cache group.
	 *
	 * Attempts wp_cache_flush_group() for persistent caches; always also
	 * removes matching transient rows from the options table.
	 *
	 * @since 1.0.0
	 * @param string $group Unused — always flushes self::GROUP.
	 * @return void
	 */
	public function flush( string $group = '' ): void {
		$this->flush_all();
	}

	/**
	 * Removes all cache entries tied to a specific object (product / category / tag / global).
	 *
	 * Call this after any create, update, or delete operation.
	 *
	 * @since 1.0.0
	 * @param int    $object_id   The related object ID (0 for global).
	 * @param string $object_type One of: product, category, tag, global.
	 * @return void
	 */
	public function flush_for_object( int $object_id, string $object_type ): void {
		$key = $this->build_key( 'faqs', $object_type, (string) $object_id );

		wp_cache_delete( $key, self::GROUP );
		delete_transient( $key );
	}

	/**
	 * Flushes ALL wsf_* transients and the entire object-cache group.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb
	 * @return void
	 */
	public function flush_all(): void {
		global $wpdb;

		// Flush object cache group (works when Redis/Memcached supports group flush).
		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( self::GROUP );
		}

		// Always clean transients from the DB as a reliable fallback.
		$like = $wpdb->esc_like( '_transient_wsf_' ) . '%';
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$like
			)
		);
	}

	/**
	 * Builds a normalised, prefixed cache key from one or more parts.
	 *
	 * A language suffix is appended when WPML or Polylang is active so
	 * multilingual sites maintain separate cache entries per language.
	 *
	 * @since 1.0.0
	 * @param string ...$parts Parts joined with underscores.
	 * @return string          Resulting cache key, max self::MAX_KEY_LENGTH chars.
	 */
	public function build_key( string ...$parts ): string {
		$lang   = $this->get_current_language();
		$suffix = $lang ? '_' . $lang : '';
		$key    = 'wsf_' . implode( '_', array_filter( $parts ) ) . $suffix;

		return substr( $key, 0, self::MAX_KEY_LENGTH );
	}

	/**
	 * Returns the current language code for multilingual cache isolation.
	 *
	 * Checks WPML first, then Polylang, then returns an empty string.
	 *
	 * @since 1.0.0
	 * @return string Language code (e.g. 'en', 'fr') or empty string.
	 */
	private function get_current_language(): string {
		// WPML.
		if ( defined( 'ICL_LANGUAGE_CODE' ) && is_string( ICL_LANGUAGE_CODE ) ) {
			return ICL_LANGUAGE_CODE;
		}

		// Polylang.
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
			return is_string( $lang ) ? $lang : '';
		}

		return '';
	}
}

