<?php
/**
 * Class Settings_Repository
 *
 * Handles persistence of plugin settings stored in wp_options.
 *
 * Each settings section maps to a single option key:
 *   general  → wsf_general_settings
 *   display  → wsf_display_settings
 *   style    → wsf_style_settings
 *   advanced → wsf_advanced_settings
 *
 * @since   1.0.0
 * @package WooSmartFaq\Repository
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Repository;

use WooSmartFaq\Contracts\Settings_Repository_Interface;

/**
 * Plugin settings repository.
 *
 * @since 1.0.0
 */
class Settings_Repository implements Settings_Repository_Interface {

	/**
	 * Allowed section slugs and their option keys.
	 *
	 * @since 1.0.0
	 * @var array<string, string>
	 */
	private const SECTIONS = [
		'general'  => 'wsf_general_settings',
		'display'  => 'wsf_display_settings',
		'style'    => 'wsf_style_settings',
		'advanced' => 'wsf_advanced_settings',
	];

	/**
	 * Retrieves all settings for a given section, with defaults merged in.
	 *
	 * Accepts either the short section slug ('general') or the full option
	 * key ('wsf_general_settings') for flexibility.
	 *
	 * @since 1.0.0
	 * @param string $section  Short section slug or full option key.
	 * @param array  $defaults Default values to merge when option is missing.
	 * @return array           Merged settings array.
	 */
	public function get( string $section, array $defaults = [] ): array {
		$option_key = $this->resolve_option_key( $section );

		if ( null === $option_key ) {
			return $defaults;
		}

		$stored = get_option( $option_key, [] );

		if ( ! is_array( $stored ) ) {
			$stored = [];
		}

		return array_merge( $defaults, $stored );
	}

	/**
	 * Persists settings for a given section.
	 *
	 * Input values are sanitized via the 'wsf_sanitize_settings' filter
	 * before storage, allowing third-party code to add validation.
	 *
	 * @since 1.0.0
	 * @param string $section Short section slug or full option key.
	 * @param array  $data    Settings data to save.
	 * @return bool           True if updated, false if unchanged or section unknown.
	 */
	public function update( string $section, array $data ): bool {
		$option_key = $this->resolve_option_key( $section );

		if ( null === $option_key ) {
			return false;
		}

		/**
		 * Filters the settings array before it is persisted.
		 *
		 * @since 1.0.0
		 * @param array  $data    Settings data about to be saved.
		 * @param string $section The section slug being updated.
		 */
		$data = (array) apply_filters( 'wsf_sanitize_settings', $data, $section );

		$result = update_option( $option_key, $data );

		do_action( 'wsf_settings_saved', $section, $data );

		return $result;
	}

	/**
	 * Deletes all settings stored under a given section.
	 *
	 * @since 1.0.0
	 * @param string $section Short section slug or full option key.
	 * @return bool           True on success, false if section unknown.
	 */
	public function delete_section( string $section ): bool {
		$option_key = $this->resolve_option_key( $section );

		if ( null === $option_key ) {
			return false;
		}

		return delete_option( $option_key );
	}

	/**
	 * Returns all four settings sections merged into one array.
	 *
	 * @since 1.0.0
	 * @return array Associative array keyed by section slug.
	 */
	public function get_all(): array {
		$all = [];

		foreach ( array_keys( self::SECTIONS ) as $section ) {
			$all[ $section ] = $this->get( $section );
		}

		return $all;
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Resolves a section identifier to its wp_options key.
	 *
	 * Accepts either the short slug ('general') or the full key
	 * ('wsf_general_settings'). Returns null for unknown sections.
	 *
	 * @since 1.0.0
	 * @param string $section Short slug or full option key.
	 * @return string|null    Option key, or null if unrecognised.
	 */
	private function resolve_option_key( string $section ): ?string {
		// Short slug lookup.
		if ( isset( self::SECTIONS[ $section ] ) ) {
			return self::SECTIONS[ $section ];
		}

		// Full option key lookup.
		if ( in_array( $section, self::SECTIONS, true ) ) {
			return $section;
		}

		return null;
	}
}

