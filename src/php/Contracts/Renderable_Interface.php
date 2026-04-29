<?php
/**
 * Interface Renderable_Interface
 *
 * Marks a class as capable of producing an HTML string for output.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Contracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Contracts;

/**
 * Contract for classes that render HTML output.
 *
 * @since 1.0.0
 */
interface Renderable_Interface {

	/**
	 * Returns the rendered HTML string.
	 *
	 * @since 1.0.0
	 * @return string Escaped HTML ready for output.
	 */
	public function render(): string;
}
