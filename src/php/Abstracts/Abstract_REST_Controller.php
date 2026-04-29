<?php
/**
 * Abstract class Abstract_REST_Controller
 *
 * Base class for all REST API endpoint controllers in this plugin.
 * Concrete subclasses implement register_routes() and define their
 * own endpoint handlers with permission callbacks and schema validation.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Abstracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Abstracts;

/**
 * Base REST controller.
 *
 * @since 1.0.0
 */
abstract class Abstract_REST_Controller {

	/**
	 * REST API namespace (e.g. 'woo-smart-faq/v1').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $namespace = 'woo-smart-faq/v1';

	/**
	 * Route base for this controller (e.g. 'faqs').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $rest_base = '';

	/**
	 * Hooks route registration onto the rest_api_init action.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Registers all REST routes for this controller.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	abstract public function register_routes(): void;

	/**
	 * Returns the full route path prefixed with namespace and base.
	 *
	 * @since 1.0.0
	 * @param string $suffix Optional path suffix to append.
	 * @return string        Full route path string.
	 */
	protected function get_route( string $suffix = '' ): string {
		return '/' . $this->namespace . '/' . $this->rest_base . $suffix;
	}

	/**
	 * Returns a standardised WP_REST_Response for error conditions.
	 *
	 * @since 1.0.0
	 * @param string $code    Machine-readable error code.
	 * @param string $message Human-readable error message.
	 * @param int    $status  HTTP status code.
	 * @return \WP_REST_Response
	 */
	protected function send_error( string $code, string $message, int $status = 400 ): \WP_REST_Response {
		return new \WP_REST_Response(
			[
				'code'    => $code,
				'message' => $message,
				'data'    => [ 'status' => $status ],
			],
			$status
		);
	}

	/**
	 * Permission callback that allows any authenticated user with manage_woocommerce.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function admin_permissions_check(): bool {
		return current_user_can( 'manage_woocommerce' );
	}
}
