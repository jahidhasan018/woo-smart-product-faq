<?php
/**
 * Interface Cache_Interface
 *
 * Defines the contract for the plugin's two-layer caching system.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Contracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Contracts;

/**
 * Contract for plugin cache implementations.
 *
 * @since 1.0.0
 */
interface Cache_Interface {

	/**
	 * Retrieves a value from cache.
	 *
	 * @since 1.0.0
	 * @param string $key   Cache key.
	 * @param string $group Cache group.
	 * @return mixed        Cached value or false if not found.
	 */
	public function get( string $key, string $group = '' ): mixed;

	/**
	 * Stores a value in cache.
	 *
	 * @since 1.0.0
	 * @param string $key        Cache key.
	 * @param mixed  $value      Value to store.
	 * @param string $group      Cache group.
	 * @param int    $expiration TTL in seconds.
	 * @return bool              True on success.
	 */
	public function set( string $key, mixed $value, string $group = '', int $expiration = 0 ): bool;

	/**
	 * Deletes a single cache entry.
	 *
	 * @since 1.0.0
	 * @param string $key   Cache key.
	 * @param string $group Cache group.
	 * @return bool         True on success.
	 */
	public function delete( string $key, string $group = '' ): bool;

	/**
	 * Flushes all entries in a cache group.
	 *
	 * @since 1.0.0
	 * @param string $group Cache group to flush.
	 * @return void
	 */
	public function flush( string $group = '' ): void;
}
