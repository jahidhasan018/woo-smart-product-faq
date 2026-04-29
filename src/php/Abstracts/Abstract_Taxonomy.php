<?php
/**
 * Abstract class Abstract_Taxonomy
 *
 * Base class for all custom taxonomy registrations in the plugin.
 * Concrete subclasses define the taxonomy slug, the object type it attaches to,
 * and its full registration arguments.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Abstracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Abstracts;

/**
 * Base taxonomy registration class.
 *
 * @since 1.0.0
 */
abstract class Abstract_Taxonomy {

	/**
	 * The taxonomy slug (e.g. 'wsf_faq_category').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $taxonomy = '';

	/**
	 * The post type slug(s) this taxonomy is attached to.
	 *
	 * @since 1.0.0
	 * @var string|string[]
	 */
	protected string|array $object_type = '';

	/**
	 * Hooks the registration callback onto the WordPress 'init' action.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Registers the taxonomy with WordPress.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		register_taxonomy( $this->taxonomy, $this->object_type, $this->get_args() );
	}

	/**
	 * Returns the registration arguments array for this taxonomy.
	 *
	 * @since 1.0.0
	 * @return array Registration arguments passed to register_taxonomy().
	 */
	abstract protected function get_args(): array;
}
