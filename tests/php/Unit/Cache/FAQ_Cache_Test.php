<?php
/**
 * Unit tests for FAQ_Cache.
 *
 * Uses Brain Monkey to stub WordPress cache and transient functions
 * without requiring a running WordPress installation.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Tests\Unit\Cache
 */

declare( strict_types=1 );

namespace WooSmartFaq\Tests\Unit\Cache;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use WooSmartFaq\Cache\FAQ_Cache;

/**
 * FAQ_Cache unit tests.
 *
 * @since  1.0.0
 * @covers \WooSmartFaq\Cache\FAQ_Cache
 */
class FAQ_Cache_Test extends TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * System under test.
	 *
	 * @var FAQ_Cache
	 */
	private FAQ_Cache $cache;

	/**
	 * Sets up Brain Monkey before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->cache = new FAQ_Cache();
	}

	/**
	 * Tears down Brain Monkey after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// get()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function get_returns_false_when_not_in_object_cache_or_transient(): void {
		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'get_transient' )->justReturn( false );

		$result = $this->cache->get( 'wsf_faqs_product_42' );

		$this->assertFalse( $result );
	}

	/**
	 * @test
	 */
	public function get_returns_value_from_object_cache_without_hitting_transient(): void {
		$expected = [ [ 'faq_id' => 1, 'question' => 'Q?', 'answer' => 'A.', 'sort_order' => 0 ] ];

		Functions\expect( 'wp_cache_get' )
			->once()
			->andReturn( $expected );

		// get_transient must NOT be called when object cache hits.
		Functions\expect( 'get_transient' )->never();

		$result = $this->cache->get( 'wsf_faqs_product_42' );

		$this->assertSame( $expected, $result );
	}

	/**
	 * @test
	 */
	public function get_falls_back_to_transient_and_warms_object_cache(): void {
		$expected = [ [ 'faq_id' => 2, 'question' => 'Q2?', 'answer' => 'A2.', 'sort_order' => 0 ] ];

		Functions\when( 'wp_cache_get' )->justReturn( false );
		Functions\when( 'get_transient' )->justReturn( $expected );

		// On fallback, the object cache should be warmed.
		Functions\expect( 'wp_cache_set' )->once();

		$result = $this->cache->get( 'wsf_faqs_product_42' );

		$this->assertSame( $expected, $result );
	}

	// -------------------------------------------------------------------------
	// set()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function set_writes_to_object_cache_and_transient(): void {
		$value = [ 'faq_id' => 1 ];

		Functions\expect( 'apply_filters' )
			->with( 'wsf_cache_ttl', \Mockery::any() )
			->andReturn( 3600 );

		Functions\expect( 'wp_cache_set' )->once();
		Functions\expect( 'set_transient' )->once()->andReturn( true );

		$result = $this->cache->set( 'wsf_faqs_product_42', $value );

		$this->assertTrue( $result );
	}

	/**
	 * @test
	 */
	public function set_uses_provided_expiration_instead_of_filter(): void {
		// When expiration > 0 is passed, apply_filters('wsf_cache_ttl') must NOT be called.
		Functions\expect( 'apply_filters' )->never();
		Functions\when( 'wp_cache_set' )->justReturn( true );
		Functions\when( 'set_transient' )->justReturn( true );

		$result = $this->cache->set( 'wsf_test_key', 'value', 'wsf', 1800 );

		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// delete()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function delete_removes_from_both_object_cache_and_transient(): void {
		Functions\expect( 'wp_cache_delete' )->once();
		Functions\expect( 'delete_transient' )->once()->andReturn( true );

		$result = $this->cache->delete( 'wsf_faqs_product_42' );

		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// flush_for_object()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function flush_for_object_deletes_the_correct_cache_key(): void {
		// No WPML/Polylang constants defined in unit-bootstrap, so lang suffix = ''.
		Functions\expect( 'wp_cache_delete' )
			->once()
			->with( \Mockery::type( 'string' ), 'wsf' );

		Functions\expect( 'delete_transient' )->once()->andReturn( true );

		$this->cache->flush_for_object( 42, 'product' );

		$this->addToAssertionCount( 1 );
	}

	// -------------------------------------------------------------------------
	// build_key()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function build_key_prefixes_with_wsf_and_joins_parts(): void {
		// No WPML/Polylang constants defined in unit-bootstrap, so lang suffix = ''.
		$key = $this->cache->build_key( 'faqs', 'product', '42' );

		$this->assertStringStartsWith( 'wsf_', $key );
		$this->assertStringContainsString( 'faqs_product_42', $key );
	}

	/**
	 * @test
	 */
	public function build_key_respects_max_length(): void {
		$long_key = $this->cache->build_key( str_repeat( 'a', 200 ) );

		$this->assertLessThanOrEqual( 172, strlen( $long_key ) );
	}
}

