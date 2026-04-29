<?php
/**
 * Interface FAQ_Repository_Interface
 *
 * Defines the contract for all FAQ data-access operations.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Contracts
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Contracts;

/**
 * Contract for FAQ repository implementations.
 *
 * @since 1.0.0
 */
interface FAQ_Repository_Interface {

	/**
	 * Returns FAQs directly assigned to a specific product.
	 *
	 * @since 1.0.0
	 * @param int   $product_id WooCommerce product post ID.
	 * @param array $args       Optional query arguments.
	 * @return array            Array of FAQ data arrays.
	 */
	public function get_by_product( int $product_id, array $args = [] ): array;

	/**
	 * Returns FAQs assigned to a product category term.
	 *
	 * @since 1.0.0
	 * @param int   $category_id WooCommerce product_cat term ID.
	 * @param array $args        Optional query arguments.
	 * @return array             Array of FAQ data arrays.
	 */
	public function get_by_category( int $category_id, array $args = [] ): array;

	/**
	 * Returns FAQs assigned to a product tag term.
	 *
	 * @since 1.0.0
	 * @param int   $tag_id WooCommerce product_tag term ID.
	 * @param array $args   Optional query arguments.
	 * @return array        Array of FAQ data arrays.
	 */
	public function get_by_tag( int $tag_id, array $args = [] ): array;

	/**
	 * Returns global FAQs (not tied to any specific object).
	 *
	 * @since 1.0.0
	 * @param array $args Optional query arguments.
	 * @return array      Array of FAQ data arrays.
	 */
	public function get_global( array $args = [] ): array;

	/**
	 * Creates a new FAQ entry.
	 *
	 * @since 1.0.0
	 * @param array $data FAQ data: question, answer, object_id, object_type, sort_order.
	 * @return int|\WP_Error  New FAQ post ID on success, WP_Error on failure.
	 */
	public function create( array $data ): int|\WP_Error;

	/**
	 * Updates an existing FAQ entry.
	 *
	 * @since 1.0.0
	 * @param int   $faq_id FAQ post ID.
	 * @param array $data   Fields to update.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function update( int $faq_id, array $data ): bool|\WP_Error;

	/**
	 * Deletes a FAQ entry and its relationship records.
	 *
	 * @since 1.0.0
	 * @param int $faq_id FAQ post ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $faq_id ): bool;
}
