<?php
/**
 * Abstract class Abstract_Post_Type
 *
 * Base class for all custom post type registrations in the plugin.
 * Concrete subclasses define the CPT slug and its registration arguments.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Abstracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Abstracts;

/**
 * Base CPT registration class.
 *
 * @since 1.0.0
 */
abstract class Abstract_Post_Type {

	/**
	 * The post type slug (e.g. 'wsf_faq').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $post_type = '';

	/**
	 * Hooks the registration callback onto the WordPress 'init' action.
	 *
	 * Call this method from Plugin::init() to schedule CPT registration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Registers the custom post type with WordPress.
	 *
	 * Calls get_args() to retrieve the registration arguments and passes
	 * them to register_post_type().
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		register_post_type( $this->post_type, $this->get_args() );
	}

	/**
	 * Returns the registration arguments array for this post type.
	 *
	 * @since 1.0.0
	 * @return array Registration arguments passed to register_post_type().
	 */
	abstract protected function get_args(): array;
}
