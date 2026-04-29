<?php
/**
 * Abstract class Abstract_Block
 *
 * Base class for all Gutenberg block registrations in this plugin.
 * Concrete subclasses provide block registration arguments and
 * implement the server-side render callback.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Abstracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Abstracts;

/**
 * Base Gutenberg block registration class.
 *
 * @since 1.0.0
 */
abstract class Abstract_Block {

	/**
	 * The block name including namespace (e.g. 'wsf/faq-block').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $block_name = '';

	/**
	 * Hooks block registration onto init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Registers the block using block.json metadata + server-side render callback.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		$args                    = $this->get_block_args();
		$args['render_callback'] = [ $this, 'render_callback' ];
		register_block_type( $this->block_name, $args );
	}

	/**
	 * Returns extra registration arguments merged with those from block.json.
	 *
	 * Override to add server-side-only arguments like render_callback.
	 * Return an empty array to rely entirely on block.json.
	 *
	 * @since 1.0.0
	 * @return array Extra block registration arguments.
	 */
	protected function get_block_args(): array {
		return [];
	}

	/**
	 * Server-side render callback for the block.
	 *
	 * @since 1.0.0
	 * @param array     $attributes Block attributes from the editor.
	 * @param string    $content    Inner block content (unused for dynamic blocks).
	 * @param \WP_Block $block      Block instance.
	 * @return string               Rendered HTML output.
	 */
	abstract public function render_callback( array $attributes, string $content, \WP_Block $block ): string;
}
