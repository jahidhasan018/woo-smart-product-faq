<?php
/**
 * Class FAQ_Category_Taxonomy
 *
 * Registers the 'wsf_faq_category' hierarchical taxonomy
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
 * FAQ Category taxonomy registration.
 *
 * @since 1.0.0
 */
class FAQ_Category_Taxonomy extends Abstract_Taxonomy {

	/**
	 * The taxonomy slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $taxonomy = 'wsf_faq_category';

	/**
	 * The post type this taxonomy is attached to.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string|array $object_type = 'wsf_faq';

	/**
	 * Returns the registration arguments for wsf_faq_category.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_args(): array {
		$labels = [
			'name'              => _x( 'FAQ Categories', 'taxonomy general name', 'woo-smart-product-faq' ),
			'singular_name'     => _x( 'FAQ Category', 'taxonomy singular name', 'woo-smart-product-faq' ),
			'search_items'      => __( 'Search FAQ Categories', 'woo-smart-product-faq' ),
			'all_items'         => __( 'All FAQ Categories', 'woo-smart-product-faq' ),
			'parent_item'       => __( 'Parent FAQ Category', 'woo-smart-product-faq' ),
			'parent_item_colon' => __( 'Parent FAQ Category:', 'woo-smart-product-faq' ),
			'edit_item'         => __( 'Edit FAQ Category', 'woo-smart-product-faq' ),
			'update_item'       => __( 'Update FAQ Category', 'woo-smart-product-faq' ),
			'add_new_item'      => __( 'Add New FAQ Category', 'woo-smart-product-faq' ),
			'new_item_name'     => __( 'New FAQ Category Name', 'woo-smart-product-faq' ),
			'menu_name'         => __( 'Categories', 'woo-smart-product-faq' ),
			'not_found'         => __( 'No FAQ categories found.', 'woo-smart-product-faq' ),
			'back_to_items'     => __( '&larr; Go to FAQ Categories', 'woo-smart-product-faq' ),
		];

		return [
			'labels'            => $labels,
			'hierarchical'      => true,
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
