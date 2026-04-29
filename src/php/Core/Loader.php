<?php
/**
 * Class Loader
 *
 * Collects all action and filter hook registrations and applies them
 * to WordPress in a single pass when run() is called.
 *
 * This decouples hook registration from hook execution, making the
 * plugin bootstrap easier to test and trace.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Core
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Core;

/**
 * WordPress hook registry.
 *
 * @since 1.0.0
 */
class Loader {

	/**
	 * Queued action hook registrations.
	 *
	 * Each element is an array: [ hook, callback, priority, accepted_args ].
	 *
	 * @since 1.0.0
	 * @var array[]
	 */
	private array $actions = [];

	/**
	 * Queued filter hook registrations.
	 *
	 * Each element is an array: [ hook, callback, priority, accepted_args ].
	 *
	 * @since 1.0.0
	 * @var array[]
	 */
	private array $filters = [];

	/**
	 * Queues an action hook for later registration.
	 *
	 * @since 1.0.0
	 * @param string   $hook          WordPress action hook name.
	 * @param callable $callback      Callback to execute.
	 * @param int      $priority      Optional. Hook priority. Default 10.
	 * @param int      $accepted_args Optional. Number of accepted args. Default 1.
	 * @return void
	 */
	public function add_action(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->actions[] = compact( 'hook', 'callback', 'priority', 'accepted_args' ); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	}

	/**
	 * Queues a filter hook for later registration.
	 *
	 * @since 1.0.0
	 * @param string   $hook          WordPress filter hook name.
	 * @param callable $callback      Callback to execute.
	 * @param int      $priority      Optional. Hook priority. Default 10.
	 * @param int      $accepted_args Optional. Number of accepted args. Default 1.
	 * @return void
	 */
	public function add_filter(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->filters[] = compact( 'hook', 'callback', 'priority', 'accepted_args' ); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	}

	/**
	 * Registers all queued actions and filters with WordPress.
	 *
	 * Call this once from the main plugin bootstrap after all modules
	 * have queued their hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run(): void {
		foreach ( $this->filters as $filter ) {
			add_filter(
				$filter['hook'],
				$filter['callback'],
				$filter['priority'],
				$filter['accepted_args']
			);
		}

		foreach ( $this->actions as $action ) {
			add_action(
				$action['hook'],
				$action['callback'],
				$action['priority'],
				$action['accepted_args']
			);
		}
	}
}
