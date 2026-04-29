<?php
/**
 * Trait Hookable_Trait
 *
 * Provides helper wrappers around WordPress add_action() and add_filter()
 * that automatically bind callbacks to the current object instance.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Traits
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Traits;

/**
 * Helper wrappers for registering WordPress hooks on the current instance.
 *
 * @since 1.0.0
 */
trait Hookable_Trait {

	/**
	 * Registers an action hook bound to a method on this instance.
	 *
	 * @since 1.0.0
	 * @param string $hook          The WordPress action hook name.
	 * @param string $method        The method name on this class to call.
	 * @param int    $priority      Optional. Hook priority. Default 10.
	 * @param int    $accepted_args Optional. Number of arguments. Default 1.
	 * @return void
	 */
	protected function add_action(
		string $hook,
		string $method,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		add_action( $hook, [ $this, $method ], $priority, $accepted_args );
	}

	/**
	 * Registers a filter hook bound to a method on this instance.
	 *
	 * @since 1.0.0
	 * @param string $hook          The WordPress filter hook name.
	 * @param string $method        The method name on this class to call.
	 * @param int    $priority      Optional. Hook priority. Default 10.
	 * @param int    $accepted_args Optional. Number of arguments. Default 1.
	 * @return void
	 */
	protected function add_filter(
		string $hook,
		string $method,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		add_filter( $hook, [ $this, $method ], $priority, $accepted_args );
	}
}
