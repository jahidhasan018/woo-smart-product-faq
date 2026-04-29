<?php
/**
 * PHPUnit bootstrap for the Unit test suite.
 *
 * Uses Brain Monkey to stub WordPress functions without needing
 * the full WordPress test suite installed. This makes the unit
 * suite runnable in any CI environment with just `composer install`.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Tests
 */

declare( strict_types=1 );

// Composer autoloader — loads plugin classes + Brain Monkey + Mockery.
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// ---------------------------------------------------------------------------
// WordPress constants required by production code
// ---------------------------------------------------------------------------
defined( 'HOUR_IN_SECONDS' ) || define( 'HOUR_IN_SECONDS', 3600 );
defined( 'DAY_IN_SECONDS' )  || define( 'DAY_IN_SECONDS', 86400 );
defined( 'ARRAY_A' )         || define( 'ARRAY_A', 'ARRAY_A' );
defined( 'ARRAY_N' )         || define( 'ARRAY_N', 'ARRAY_N' );
defined( 'OBJECT' )          || define( 'OBJECT', 'OBJECT' );

// ---------------------------------------------------------------------------
// Minimal WordPress class stubs (not provided by Brain Monkey)
// ---------------------------------------------------------------------------
if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Minimal WP_Error stub for unit tests.
	 */
	class WP_Error { // phpcs:ignore
		/** @var string */
		private string $code;
		/** @var string */
		private string $message;

		/**
		 * @param string $code    Error code.
		 * @param string $message Human-readable message.
		 * @param mixed  $data    Optional error data.
		 */
		public function __construct( string $code = '', string $message = '', mixed $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
		}

		/** @return string */
		public function get_error_code(): string {
			return $this->code;
		}

		/** @return string */
		public function get_error_message(): string {
			return $this->message;
		}
	}
}

if ( ! class_exists( 'WP_Post' ) ) {
	/**
	 * Minimal WP_Post stub for unit tests.
	 */
	class WP_Post { // phpcs:ignore
		/** @var int */
		public int $ID = 0;
		/** @var string */
		public string $post_type = '';
		/** @var string */
		public string $post_title = '';
		/** @var string */
		public string $post_content = '';
		/** @var string */
		public string $post_status = 'publish';
	}
}

// Brain Monkey sets up a Mockery container and stubs WP functions.
// Individual test cases call Brain\Monkey\setUp() / tearDown() in
// their own setUp/tearDown methods via the provided trait or directly.
