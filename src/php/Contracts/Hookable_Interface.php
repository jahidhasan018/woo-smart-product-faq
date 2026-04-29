<?php
/**
 * Interface Hookable_Interface
 *
 * Marks a class as registering its own WordPress action/filter hooks.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Contracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Contracts;

/**
 * Contract for classes that self-register WordPress hooks.
 *
 * @since 1.0.0
 */
interface Hookable_Interface {

	/**
	 * Registers all WordPress action and filter hooks for this class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks(): void;
}
