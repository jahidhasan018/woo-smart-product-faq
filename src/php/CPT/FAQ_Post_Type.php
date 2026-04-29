<?php
/**
 * Class FAQ_Post_Type
 *
 * Registers the 'wsf_faq' custom post type.
 *
 * @since   1.0.0
 * @package WooSmartFaq\CPT
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\CPT;

use WooSmartFaq\Abstracts\Abstract_Post_Type;

/**
 * Wsf_faq CPT registration.
 *
 * @since 1.0.0
 */
class FAQ_Post_Type extends Abstract_Post_Type {

	/**
	 * The post type slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $post_type = 'wsf_faq';

	/**
	 * Returns the registration arguments for the wsf_faq post type.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_args(): array {
		$labels = [
			'name'                  => _x( 'FAQs', 'post type general name', 'woo-smart-product-faq' ),
			'singular_name'         => _x( 'FAQ', 'post type singular name', 'woo-smart-product-faq' ),
			'menu_name'             => _x( 'Smart FAQs', 'admin menu', 'woo-smart-product-faq' ),
			'name_admin_bar'        => _x( 'FAQ', 'add new on admin bar', 'woo-smart-product-faq' ),
			'add_new'               => __( 'Add New', 'woo-smart-product-faq' ),
			'add_new_item'          => __( 'Add New FAQ', 'woo-smart-product-faq' ),
			'new_item'              => __( 'New FAQ', 'woo-smart-product-faq' ),
			'edit_item'             => __( 'Edit FAQ', 'woo-smart-product-faq' ),
			'view_item'             => __( 'View FAQ', 'woo-smart-product-faq' ),
			'all_items'             => __( 'All FAQs', 'woo-smart-product-faq' ),
			'search_items'          => __( 'Search FAQs', 'woo-smart-product-faq' ),
			'parent_item_colon'     => __( 'Parent FAQ:', 'woo-smart-product-faq' ),
			'not_found'             => __( 'No FAQs found.', 'woo-smart-product-faq' ),
			'not_found_in_trash'    => __( 'No FAQs found in Trash.', 'woo-smart-product-faq' ),
			'featured_image'        => __( 'FAQ Featured Image', 'woo-smart-product-faq' ),
			'set_featured_image'    => __( 'Set featured image', 'woo-smart-product-faq' ),
			'remove_featured_image' => __( 'Remove featured image', 'woo-smart-product-faq' ),
			'use_featured_image'    => __( 'Use as featured image', 'woo-smart-product-faq' ),
			'archives'              => __( 'FAQ Archives', 'woo-smart-product-faq' ),
			'insert_into_item'      => __( 'Insert into FAQ', 'woo-smart-product-faq' ),
			'uploaded_to_this_item' => __( 'Uploaded to this FAQ', 'woo-smart-product-faq' ),
			'items_list'            => __( 'FAQs list', 'woo-smart-product-faq' ),
			'items_list_navigation' => __( 'FAQs list navigation', 'woo-smart-product-faq' ),
			'filter_items_list'     => __( 'Filter FAQs list', 'woo-smart-product-faq' ),
		];

		return [
			'labels'             => $labels,
			'description'        => __( 'Frequently Asked Questions for WooCommerce products.', 'woo-smart-product-faq' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'woo-smart-faq',
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => true,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => [ 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions' ],
			'menu_icon'          => 'dashicons-editor-help',
		];
	}
}
