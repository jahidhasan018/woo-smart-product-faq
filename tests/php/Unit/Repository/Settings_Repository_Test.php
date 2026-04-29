<?php
/**
 * Unit tests for Settings_Repository.
 *
 * WordPress option functions are stubbed via Brain Monkey.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Tests\Unit\Repository
 */

declare( strict_types=1 );

namespace WooSmartFaq\Tests\Unit\Repository;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use WooSmartFaq\Repository\Settings_Repository;

/**
 * Settings_Repository unit tests.
 *
 * @since  1.0.0
 * @covers \WooSmartFaq\Repository\Settings_Repository
 */
class Settings_Repository_Test extends TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * System under test.
	 *
	 * @var Settings_Repository
	 */
	private Settings_Repository $repo;

	/**
	 * @inheritDoc
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->repo = new Settings_Repository();
	}

	/**
	 * @inheritDoc
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
	public function get_returns_empty_array_for_unknown_section(): void {
		$result = $this->repo->get( 'nonexistent_section' );

		$this->assertSame( [], $result );
	}

	/**
	 * @test
	 */
	public function get_merges_defaults_with_stored_values(): void {
		Functions\when( 'get_option' )->justReturn( [ 'tab_title' => 'Questions' ] );

		$result = $this->repo->get( 'general', [ 'tab_title' => 'FAQs', 'open_first' => false ] );

		// Stored value should override default.
		$this->assertSame( 'Questions', $result['tab_title'] );
		// Default not in stored should be preserved.
		$this->assertFalse( $result['open_first'] );
	}

	/**
	 * @test
	 */
	public function get_returns_defaults_when_option_is_not_array(): void {
		// Simulate a corrupted option value.
		Functions\when( 'get_option' )->justReturn( 'not-an-array' );

		$result = $this->repo->get( 'general', [ 'tab_title' => 'FAQs' ] );

		$this->assertSame( 'FAQs', $result['tab_title'] );
	}

	/**
	 * @test
	 */
	public function get_accepts_full_option_key(): void {
		Functions\when( 'get_option' )->justReturn( [ 'layout' => 'list' ] );

		$result = $this->repo->get( 'wsf_style_settings' );

		$this->assertSame( 'list', $result['layout'] );
	}

	// -------------------------------------------------------------------------
	// update()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function update_returns_false_for_unknown_section(): void {
		$result = $this->repo->update( 'unknown', [ 'key' => 'value' ] );

		$this->assertFalse( $result );
	}

	/**
	 * @test
	 */
	public function update_calls_update_option_and_fires_action(): void {
		$data = [ 'tab_title' => 'My FAQs' ];

		Functions\expect( 'apply_filters' )
			->with( 'wsf_sanitize_settings', $data, 'general' )
			->andReturn( $data );

		Functions\expect( 'update_option' )
			->once()
			->with( 'wsf_general_settings', $data )
			->andReturn( true );

		Functions\expect( 'do_action' )
			->once()
			->with( 'wsf_settings_saved', 'general', $data );

		$result = $this->repo->update( 'general', $data );

		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// delete_section()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function delete_section_returns_false_for_unknown_section(): void {
		$result = $this->repo->delete_section( 'unknown' );

		$this->assertFalse( $result );
	}

	/**
	 * @test
	 */
	public function delete_section_calls_delete_option(): void {
		Functions\expect( 'delete_option' )
			->once()
			->with( 'wsf_display_settings' )
			->andReturn( true );

		$result = $this->repo->delete_section( 'display' );

		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// get_all()
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function get_all_returns_all_four_sections(): void {
		Functions\when( 'get_option' )->justReturn( [] );

		$result = $this->repo->get_all();

		$this->assertArrayHasKey( 'general', $result );
		$this->assertArrayHasKey( 'display', $result );
		$this->assertArrayHasKey( 'style', $result );
		$this->assertArrayHasKey( 'advanced', $result );
	}
}
