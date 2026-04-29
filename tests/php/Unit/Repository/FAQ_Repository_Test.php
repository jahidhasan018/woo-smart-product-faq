<?php
/**
 * Unit tests for FAQ_Repository.
 *
 * All WordPress functions are stubbed via Brain Monkey so the suite
 * runs without a live database.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Tests\Unit\Repository
 */

declare( strict_types=1 );

namespace WooSmartFaq\Tests\Unit\Repository;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use WooSmartFaq\Cache\FAQ_Cache;
use WooSmartFaq\Repository\FAQ_Repository;
use wpdb;

/**
 * FAQ_Repository unit tests.
 *
 * @since  1.0.0
 * @covers \WooSmartFaq\Repository\FAQ_Repository
 */
class FAQ_Repository_Test extends TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Mocked wpdb.
	 *
	 * @var Mockery\MockInterface&wpdb
	 */
	private $wpdb;

	/**
	 * Mocked cache.
	 *
	 * @var Mockery\MockInterface&FAQ_Cache
	 */
	private $cache;

	/**
	 * System under test.
	 *
	 * @var FAQ_Repository
	 */
	private FAQ_Repository $repo;

	/**
	 * @inheritDoc
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->wpdb         = Mockery::mock( wpdb::class );
		$this->wpdb->prefix = 'wp_';
		$this->wpdb->posts  = 'wp_posts'; // @phpstan-ignore-line

		$this->cache = Mockery::mock( FAQ_Cache::class );
		$this->repo  = new FAQ_Repository( $this->wpdb, $this->cache );
	}

	/**
	 * @inheritDoc
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// get_by_product()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function get_by_product_returns_empty_array_when_no_faqs(): void {
		$this->cache->allows( 'build_key' )->andReturn( 'wsf_faqs_product_42' );
		$this->cache->allows( 'get' )->andReturn( false );

		$this->wpdb->allows( 'prepare' )->andReturn( 'SELECT ...' );
		$this->wpdb->allows( 'get_results' )->andReturn( [] );

		$this->cache->allows( 'set' );

		$result = $this->repo->get_by_product( 42 );

		$this->assertSame( [], $result );
	}

	/**
	 * @test
	 */
	public function get_by_product_returns_cached_result_without_db_query(): void {
		$cached = [
			[ 'faq_id' => 1, 'question' => 'Q?', 'answer' => 'A.', 'sort_order' => 0 ],
		];

		$this->cache->allows( 'build_key' )->andReturn( 'wsf_faqs_product_42' );
		$this->cache->allows( 'get' )->andReturn( $cached );

		// DB must NOT be queried.
		$this->wpdb->expects( 'get_results' )->never();

		$result = $this->repo->get_by_product( 42 );

		$this->assertSame( $cached, $result );
	}

	// -------------------------------------------------------------------------
	// get_single()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function get_single_returns_null_for_nonexistent_post(): void {
		Functions\when( 'get_post' )->justReturn( null );

		$result = $this->repo->get_single( 999 );

		$this->assertNull( $result );
	}

	/**
	 * @test
	 */
	public function get_single_returns_null_for_wrong_post_type(): void {
		$post            = Mockery::mock( \WP_Post::class );
		$post->ID        = 10;
		$post->post_type = 'post';

		Functions\when( 'get_post' )->justReturn( $post );

		$result = $this->repo->get_single( 10 );

		$this->assertNull( $result );
	}

	/**
	 * @test
	 */
	public function get_single_returns_dto_array_for_valid_faq(): void {
		$post               = Mockery::mock( \WP_Post::class );
		$post->ID           = 5;
		$post->post_type    = 'wsf_faq';
		$post->post_title   = 'What is shipping?';
		$post->post_content = 'Free over $50.';

		Functions\when( 'get_post' )->justReturn( $post );

		$result = $this->repo->get_single( 5 );

		$this->assertIsArray( $result );
		$this->assertSame( 5, $result['faq_id'] );
		$this->assertSame( 'What is shipping?', $result['question'] );
	}

	// -------------------------------------------------------------------------
	// create()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function create_returns_wp_error_when_question_is_missing(): void {
		Functions\when( '__' )->returnArg( 1 );

		$result = $this->repo->create( [ 'answer' => 'Some answer.' ] );

		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	/**
	 * @test
	 */
	public function create_returns_post_id_on_success(): void {
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'wp_kses_post' )->returnArg( 1 );
		Functions\when( 'wp_insert_post' )->justReturn( 7 );
		Functions\when( 'absint' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->returnArg( 1 );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'do_action' )->justReturn( null );

		$this->wpdb->allows( 'prepare' )->andReturn( 'INSERT ...' );
		$this->wpdb->allows( 'query' )->andReturn( 1 );

		$this->cache->allows( 'build_key' )->andReturn( 'key' );
		$this->cache->allows( 'flush_for_object' );

		$result = $this->repo->create(
			[
				'question'    => 'What is return policy?',
				'answer'      => '30 days.',
				'object_id'   => 0,
				'object_type' => 'global',
			]
		);

		$this->assertSame( 7, $result );
	}

	// -------------------------------------------------------------------------
	// delete()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function delete_returns_false_when_wp_delete_post_fails(): void {
		$this->wpdb->allows( 'prepare' )->andReturn( 'SELECT ...' );
		$this->wpdb->allows( 'get_results' )->andReturn( [] );
		$this->wpdb->allows( 'delete' )->andReturn( 0 );

		Functions\when( 'wp_delete_post' )->justReturn( false );

		$result = $this->repo->delete( 999 );

		$this->assertFalse( $result );
	}

	/**
	 * @test
	 */
	public function delete_flushes_cache_for_all_related_objects(): void {
		$relationships = [
			[ 'object_id' => '1', 'object_type' => 'product' ],
			[ 'object_id' => '0', 'object_type' => 'global' ],
		];

		$this->wpdb->allows( 'prepare' )->andReturn( 'SELECT ...' );
		$this->wpdb->allows( 'get_results' )->andReturn( $relationships );
		$this->wpdb->allows( 'delete' )->andReturn( 2 );

		$post          = Mockery::mock( \WP_Post::class );
		$post->post_type = 'wsf_faq';

		Functions\when( 'wp_delete_post' )->justReturn( $post );
		Functions\when( 'do_action' )->justReturn( null );

		$this->cache->expects( 'flush_for_object' )
			->twice();

		$result = $this->repo->delete( 5 );

		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// get_for_product() — priority merge
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function get_for_product_deduplicates_faqs_across_sources(): void {
		// Return the same FAQ from both product and global sources.
		$faq = [ 'faq_id' => 1, 'question' => 'Q?', 'answer' => 'A.', 'sort_order' => 0 ];

		$this->cache->allows( 'build_key' )->andReturn( 'wsf_key' );
		$this->cache->allows( 'get' )->andReturn( false );
		$this->cache->allows( 'set' );

		$this->wpdb->allows( 'prepare' )->andReturn( 'SELECT ...' );
		// First call = product FAQs (returns $faq), subsequent calls return same faq or [].
		$this->wpdb->allows( 'get_results' )->andReturn( [ $faq ] );

		Functions\when( 'wp_get_post_terms' )->justReturn( [] );
		Functions\when( 'is_wp_error' )->justReturn( false );

		$result = $this->repo->get_for_product( 42 );

		// faq_id 1 should appear only once.
		$ids = array_column( $result, 'faq_id' );
		$this->assertCount( count( array_unique( $ids ) ), $ids, 'Duplicate faq_ids found in merged result.' );
	}
}

