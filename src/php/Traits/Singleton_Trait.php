<?php
/**
 * Trait Singleton_Trait
 *
 * Provides a thread-safe singleton instance accessor for plugin classes.
 * Only one instance is ever created per class in a single request.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Traits
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Traits;

/**
 * Singleton pattern implementation.
 *
 * Usage: add `use Singleton_Trait;` to any class that should be a singleton.
 *
 * @since 1.0.0
 */
trait Singleton_Trait {

	/**
	 * The single instance of this class.
	 *
	 * @since 1.0.0
	 * @var static|null
	 */
	private static ?self $instance = null;

	/**
	 * Returns (or creates) the single instance of the class.
	 *
	 * @since 1.0.0
	 * @return static
	 */
	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Prevent external instantiation.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning.
	 *
	 * @return void
	 */
	private function __clone(): void {}
}
