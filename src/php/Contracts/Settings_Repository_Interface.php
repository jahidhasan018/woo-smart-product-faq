<?php
/**
 * Interface Settings_Repository_Interface
 *
 * Defines the contract for plugin settings persistence.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Contracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Contracts;

/**
 * Contract for settings repository implementations.
 *
 * @since 1.0.0
 */
interface Settings_Repository_Interface {

	/**
	 * Retrieves all settings for a given section (option key).
	 *
	 * @since 1.0.0
	 * @param string $section  Option key, e.g. 'wsf_general_settings'.
	 * @param array  $defaults Default values to merge if option is missing.
	 * @return array           Associative array of settings.
	 */
	public function get( string $section, array $defaults = [] ): array;

	/**
	 * Persists settings for a given section.
	 *
	 * @since 1.0.0
	 * @param string $section Option key.
	 * @param array  $data    Settings data to save.
	 * @return bool           True if updated, false if unchanged or failed.
	 */
	public function update( string $section, array $data ): bool;

	/**
	 * Deletes all settings stored under a given section.
	 *
	 * @since 1.0.0
	 * @param string $section Option key to delete.
	 * @return bool           True on success, false on failure.
	 */
	public function delete_section( string $section ): bool;
}
