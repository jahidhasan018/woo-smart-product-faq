<?php
/**
 * Class FAQ_Tag_Taxonomy
 *
 * Registers the 'wsf_faq_tag' non-hierarchical taxonomy
 * attached to the wsf_faq post type.
 *
 * @since   1.0.0
 * @package WooSmartFaq\CPT
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\CPT;

use WooSmartFaq\Abstracts\Abstract_Taxonomy;

/**
 * FAQ Tag taxonomy registration.
 *
 * @since 1.0.0
 */
class FAQ_Tag_Taxonomy extends Abstract_Taxonomy {

	/**
	 * The taxonomy slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $taxonomy = 'wsf_faq_tag';

	/**
	 * The post type this taxonomy is attached to.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string|array $object_type = 'wsf_faq';

	/**
	 * Returns the registration arguments for wsf_faq_tag.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_args(): array {
		$labels = [
			'name'                       => _x( 'FAQ Tags', 'taxonomy general name', 'woo-smart-product-faq' ),
			'singular_name'              => _x( 'FAQ Tag', 'taxonomy singular name', 'woo-smart-product-faq' ),
			'search_items'               => __( 'Search FAQ Tags', 'woo-smart-product-faq' ),
			'popular_items'              => __( 'Popular FAQ Tags', 'woo-smart-product-faq' ),
			'all_items'                  => __( 'All FAQ Tags', 'woo-smart-product-faq' ),
			'edit_item'                  => __( 'Edit FAQ Tag', 'woo-smart-product-faq' ),
			'update_item'                => __( 'Update FAQ Tag', 'woo-smart-product-faq' ),
			'add_new_item'               => __( 'Add New FAQ Tag', 'woo-smart-product-faq' ),
			'new_item_name'              => __( 'New FAQ Tag Name', 'woo-smart-product-faq' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'woo-smart-product-faq' ),
			'add_or_remove_items'        => __( 'Add or remove FAQ tags', 'woo-smart-product-faq' ),
			'choose_from_most_used'      => __( 'Choose from the most used FAQ tags', 'woo-smart-product-faq' ),
			'not_found'                  => __( 'No FAQ tags found.', 'woo-smart-product-faq' ),
			'menu_name'                  => __( 'Tags', 'woo-smart-product-faq' ),
			'back_to_items'              => __( '&larr; Go to FAQ Tags', 'woo-smart-product-faq' ),
		];

		return [
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_in_rest'      => true,
			'rewrite'           => false,
			'query_var'         => false,
		];
	}
}
