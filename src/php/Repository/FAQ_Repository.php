<?php
/**
 * Class FAQ_Repository
 *
 * Handles all FAQ data-access operations against the custom
 * {prefix}wsf_faq_relationships table and the wsf_faq CPT.
 *
 * Priority resolution for get_for_product():
 *   1. Product-specific FAQs
 *   2. Category FAQs (deepest term first)
 *   3. Tag FAQs
 *   4. Global FAQs
 * Results are merged, deduplicated by faq_id, and sorted by sort_order ASC.
 *
 * @since   1.0.0
 * @package WooSmartFaq\Repository
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace WooSmartFaq\Repository;

use WooSmartFaq\Cache\FAQ_Cache;
use WooSmartFaq\Contracts\FAQ_Repository_Interface;
use wpdb;

/**
 * FAQ data repository.
 *
 * @since 1.0.0
 */
class FAQ_Repository implements FAQ_Repository_Interface {

	/**
	 * WordPress database abstraction object.
	 *
	 * @since 1.0.0
	 * @var wpdb
	 */
	private wpdb $wpdb;

	/**
	 * Cache layer.
	 *
	 * @since 1.0.0
	 * @var FAQ_Cache
	 */
	private FAQ_Cache $cache;

	/**
	 * Fully-qualified relationship table name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $table;

	/**
	 * Injects dependencies.
	 *
	 * @since 1.0.0
	 * @param wpdb      $wpdb  WordPress database object.
	 * @param FAQ_Cache $cache Cache layer.
	 */
	public function __construct( wpdb $wpdb, FAQ_Cache $cache ) {
		$this->wpdb  = $wpdb;
		$this->cache = $cache;
		$this->table = $wpdb->prefix . 'wsf_faq_relationships';
	}

	// -------------------------------------------------------------------------
	// Public interface methods
	// -------------------------------------------------------------------------

	/**
	 * Returns FAQs directly assigned to a specific product.
	 *
	 * @since 1.0.0
	 * @param int   $product_id WooCommerce product post ID.
	 * @param array $args       Optional query arguments.
	 * @return array            Array of FAQ data arrays.
	 */
	public function get_by_product( int $product_id, array $args = [] ): array {
		$cache_key = $this->cache->build_key( 'faqs', 'product', (string) $product_id );
		$cached    = $this->cache->get( $cache_key );

		if ( false !== $cached ) {
			return (array) $cached;
		}

		$faqs = $this->query_by_object( $product_id, 'product' );
		$this->cache->set( $cache_key, $faqs );

		return $faqs;
	}

	/**
	 * Returns FAQs assigned to a product category term.
	 *
	 * @since 1.0.0
	 * @param int   $category_id product_cat term ID.
	 * @param array $args        Optional query arguments.
	 * @return array             Array of FAQ data arrays.
	 */
	public function get_by_category( int $category_id, array $args = [] ): array {
		$cache_key = $this->cache->build_key( 'faqs', 'category', (string) $category_id );
		$cached    = $this->cache->get( $cache_key );

		if ( false !== $cached ) {
			return (array) $cached;
		}

		$faqs = $this->query_by_object( $category_id, 'category' );
		$this->cache->set( $cache_key, $faqs );

		return $faqs;
	}

	/**
	 * Returns FAQs assigned to a product tag term.
	 *
	 * @since 1.0.0
	 * @param int   $tag_id product_tag term ID.
	 * @param array $args   Optional query arguments.
	 * @return array        Array of FAQ data arrays.
	 */
	public function get_by_tag( int $tag_id, array $args = [] ): array {
		$cache_key = $this->cache->build_key( 'faqs', 'tag', (string) $tag_id );
		$cached    = $this->cache->get( $cache_key );

		if ( false !== $cached ) {
			return (array) $cached;
		}

		$faqs = $this->query_by_object( $tag_id, 'tag' );
		$this->cache->set( $cache_key, $faqs );

		return $faqs;
	}

	/**
	 * Returns global FAQs (not tied to any specific object).
	 *
	 * @since 1.0.0
	 * @param array $args Optional query arguments.
	 * @return array      Array of FAQ data arrays.
	 */
	public function get_global( array $args = [] ): array {
		$cache_key = $this->cache->build_key( 'faqs', 'global', '0' );
		$cached    = $this->cache->get( $cache_key );

		if ( false !== $cached ) {
			return (array) $cached;
		}

		$faqs = $this->query_by_object( 0, 'global' );
		$this->cache->set( $cache_key, $faqs );

		return $faqs;
	}

	/**
	 * Returns all FAQs for a product using priority resolution.
	 *
	 * Merges product → category → tag → global FAQs, deduplicates by
	 * faq_id, then sorts by sort_order ASC.
	 *
	 * @since 1.0.0
	 * @param int $product_id WooCommerce product post ID.
	 * @return array          Merged, deduplicated, sorted FAQ data arrays.
	 */
	public function get_for_product( int $product_id ): array {
		$cache_key = $this->cache->build_key( 'faqs', 'resolved', (string) $product_id );
		$cached    = $this->cache->get( $cache_key );

		if ( false !== $cached ) {
			return (array) $cached;
		}

		$product_faqs  = $this->get_by_product( $product_id );
		$category_faqs = $this->get_category_faqs_for_product( $product_id );
		$tag_faqs      = $this->get_tag_faqs_for_product( $product_id );
		$global_faqs   = $this->get_global();

		$merged = $this->merge_and_deduplicate(
			$product_faqs,
			$category_faqs,
			$tag_faqs,
			$global_faqs
		);

		$this->cache->set( $cache_key, $merged );

		return $merged;
	}

	/**
	 * Returns a single FAQ as an array DTO.
	 *
	 * @since 1.0.0
	 * @param int $faq_id FAQ post ID.
	 * @return array|null  FAQ data array, or null when not found.
	 */
	public function get_single( int $faq_id ): ?array {
		$post = get_post( $faq_id );

		if ( ! $post || 'wsf_faq' !== $post->post_type ) {
			return null;
		}

		return $this->post_to_dto( $post );
	}

	/**
	 * Creates a new FAQ entry and its relationship record.
	 *
	 * @since 1.0.0
	 * @param array $data {
	 *     @type string $question    Required. Question text.
	 *     @type string $answer      Required. HTML answer.
	 *     @type int    $object_id   Optional. Object ID to assign to. Default 0 (global).
	 *     @type string $object_type Optional. One of product/category/tag/global. Default global.
	 *     @type int    $sort_order  Optional. Display order. Default 0.
	 * }
	 * @return int|\WP_Error New FAQ post ID, or WP_Error on failure.
	 */
	public function create( array $data ): int|\WP_Error {
		if ( empty( $data['question'] ) ) {
			return new \WP_Error( 'wsf_missing_question', __( 'Question is required.', 'woo-smart-product-faq' ) );
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => 'wsf_faq',
				'post_status'  => 'publish',
				'post_title'   => sanitize_text_field( $data['question'] ),
				'post_content' => wp_kses_post( $data['answer'] ?? '' ),
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$object_id   = absint( $data['object_id'] ?? 0 );
		$object_type = sanitize_key( $data['object_type'] ?? 'global' );
		$sort_order  = absint( $data['sort_order'] ?? 0 );

		$this->assign_to_object( $post_id, $object_id, $object_type, $sort_order );

		do_action( 'wsf_faq_saved', $post_id, $data );

		$this->cache->flush_for_object( $object_id, $object_type );

		return $post_id;
	}

	/**
	 * Updates an existing FAQ entry.
	 *
	 * @since 1.0.0
	 * @param int   $faq_id FAQ post ID.
	 * @param array $data   Fields to update (question, answer).
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function update( int $faq_id, array $data ): bool|\WP_Error {
		$update_data = [ 'ID' => $faq_id ];

		if ( isset( $data['question'] ) ) {
			$update_data['post_title'] = sanitize_text_field( $data['question'] );
		}

		if ( isset( $data['answer'] ) ) {
			$update_data['post_content'] = wp_kses_post( $data['answer'] );
		}

		$result = wp_update_post( $update_data, true );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		do_action( 'wsf_faq_saved', $faq_id, $data );

		// Flush all caches tied to this FAQ's relationships.
		$this->flush_faq_caches( $faq_id );

		return true;
	}

	/**
	 * Deletes a FAQ post and all its relationship records.
	 *
	 * @since 1.0.0
	 * @param int $faq_id FAQ post ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $faq_id ): bool {
		// Gather related objects before deleting so we can flush their caches.
		$relationships = $this->get_faq_relationships( $faq_id );

		// Remove relationship rows.
		$this->wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$this->table,
			[ 'faq_id' => $faq_id ],
			[ '%d' ]
		);

		$deleted = wp_delete_post( $faq_id, true );

		if ( ! $deleted ) {
			return false;
		}

		do_action( 'wsf_faq_deleted', $faq_id );

		foreach ( $relationships as $rel ) {
			$this->cache->flush_for_object( (int) $rel['object_id'], $rel['object_type'] );
		}

		return true;
	}

	/**
	 * Bulk-updates sort_order for a set of FAQ relationship rows.
	 *
	 * @since 1.0.0
	 * @param array $order_map [ faq_id => sort_order, ... ]
	 * @param int   $object_id   Object the order applies to.
	 * @param string $object_type Object type.
	 * @return bool              True on success.
	 */
	public function reorder( array $order_map, int $object_id, string $object_type ): bool {
		foreach ( $order_map as $faq_id => $sort_order ) {
			$this->wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$this->table,
				[ 'sort_order' => absint( $sort_order ) ],
				[
					'faq_id'      => absint( $faq_id ),
					'object_id'   => $object_id,
					'object_type' => $object_type,
				],
				[ '%d' ],
				[ '%d', '%d', '%s' ]
			);
		}

		$this->cache->flush_for_object( $object_id, $object_type );

		return true;
	}

	/**
	 * Creates a relationship record linking a FAQ to an object.
	 *
	 * Uses INSERT IGNORE so duplicate assignments are silently skipped.
	 *
	 * @since 1.0.0
	 * @param int    $faq_id      FAQ post ID.
	 * @param int    $object_id   Object ID (0 for global).
	 * @param string $object_type One of: product, category, tag, global.
	 * @param int    $sort_order  Display order. Default 0.
	 * @return bool               True on success.
	 */
	public function assign_to_object(
		int $faq_id,
		int $object_id,
		string $object_type,
		int $sort_order = 0
	): bool {
		$result = $this->wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$this->wpdb->prepare(
				"INSERT IGNORE INTO {$this->table}
					(faq_id, object_id, object_type, sort_order)
				VALUES (%d, %d, %s, %d)",
				$faq_id,
				$object_id,
				$object_type,
				$sort_order
			)
		);

		$this->cache->flush_for_object( $object_id, $object_type );

		return false !== $result;
	}

	/**
	 * Removes a relationship record linking a FAQ to an object.
	 *
	 * @since 1.0.0
	 * @param int    $faq_id      FAQ post ID.
	 * @param int    $object_id   Object ID.
	 * @param string $object_type Object type.
	 * @return bool               True on success.
	 */
	public function unassign_from_object( int $faq_id, int $object_id, string $object_type ): bool {
		$result = $this->wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$this->table,
			[
				'faq_id'      => $faq_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
			],
			[ '%d', '%d', '%s' ]
		);

		$this->cache->flush_for_object( $object_id, $object_type );

		return false !== $result;
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Queries the relationship table + post data for a given object.
	 *
	 * @since 1.0.0
	 * @param int    $object_id   Object ID (0 for global).
	 * @param string $object_type One of: product, category, tag, global.
	 * @return array              Array of FAQ data arrays sorted by sort_order.
	 */
	private function query_by_object( int $object_id, string $object_type ): array {
		$rows = $this->wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$this->wpdb->prepare(
				"SELECT r.faq_id, r.sort_order, p.post_title AS question, p.post_content AS answer
				FROM {$this->table} r
				INNER JOIN {$this->wpdb->posts} p ON p.ID = r.faq_id AND p.post_status = 'publish'
				WHERE r.object_id = %d AND r.object_type = %s
				ORDER BY r.sort_order ASC",
				$object_id,
				$object_type
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return [];
		}

		return array_map( [ $this, 'cast_row' ], $rows );
	}

	/**
	 * Returns category FAQs for a product by resolving its product_cat terms.
	 *
	 * Categories are ordered deepest-first (highest term_taxonomy_id depth)
	 * so the most specific category's FAQs appear before parent categories.
	 *
	 * @since 1.0.0
	 * @param int $product_id WooCommerce product post ID.
	 * @return array          Array of FAQ data arrays.
	 */
	private function get_category_faqs_for_product( int $product_id ): array {
		$terms = wp_get_post_terms( $product_id, 'product_cat', [ 'orderby' => 'term_taxonomy_id', 'order' => 'DESC' ] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		$term_ids = wp_list_pluck( $terms, 'term_id' );

		return $this->query_by_objects( array_map( 'absint', $term_ids ), 'category' );
	}

	/**
	 * Returns tag FAQs for a product by resolving its product_tag terms.
	 *
	 * @since 1.0.0
	 * @param int $product_id WooCommerce product post ID.
	 * @return array          Array of FAQ data arrays.
	 */
	private function get_tag_faqs_for_product( int $product_id ): array {
		$terms = wp_get_post_terms( $product_id, 'product_tag' );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		$term_ids = wp_list_pluck( $terms, 'term_id' );

		return $this->query_by_objects( array_map( 'absint', $term_ids ), 'tag' );
	}

	/**
	 * Queries FAQs for multiple object IDs of the same type in a single query.
	 *
	 * @since 1.0.0
	 * @param int[]  $object_ids  Array of object IDs.
	 * @param string $object_type One of: category, tag.
	 * @return array              Array of FAQ data arrays.
	 */
	private function query_by_objects( array $object_ids, string $object_type ): array {
		if ( empty( $object_ids ) ) {
			return [];
		}

		$placeholders = implode( ', ', array_fill( 0, count( $object_ids ), '%d' ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$rows = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT r.faq_id, r.sort_order, p.post_title AS question, p.post_content AS answer
				FROM {$this->table} r
				INNER JOIN {$this->wpdb->posts} p ON p.ID = r.faq_id AND p.post_status = 'publish'
				WHERE r.object_id IN ({$placeholders}) AND r.object_type = %s
				ORDER BY r.sort_order ASC",
				array_merge( $object_ids, [ $object_type ] )
			),
			ARRAY_A
		);
		// phpcs:enable

		if ( ! is_array( $rows ) ) {
			return [];
		}

		return array_map( [ $this, 'cast_row' ], $rows );
	}

	/**
	 * Merges FAQ arrays in priority order and deduplicates by faq_id.
	 *
	 * Higher-priority sources (earlier arguments) win on duplicate faq_id.
	 *
	 * @since 1.0.0
	 * @param array ...$sources Arrays of FAQ data arrays.
	 * @return array            Merged and sorted array.
	 */
	private function merge_and_deduplicate( array ...$sources ): array {
		$seen   = [];
		$merged = [];

		foreach ( $sources as $source ) {
			foreach ( $source as $faq ) {
				$id = (int) $faq['faq_id'];

				if ( isset( $seen[ $id ] ) ) {
					continue;
				}

				$seen[ $id ] = true;
				$merged[]    = $faq;
			}
		}

		usort(
			$merged,
			static fn( array $a, array $b ): int => (int) $a['sort_order'] <=> (int) $b['sort_order']
		);

		return $merged;
	}

	/**
	 * Returns all relationship records for a given FAQ ID.
	 *
	 * @since 1.0.0
	 * @param int $faq_id FAQ post ID.
	 * @return array      Array of relationship rows.
	 */
	private function get_faq_relationships( int $faq_id ): array {
		$rows = $this->wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$this->wpdb->prepare(
				"SELECT object_id, object_type FROM {$this->table} WHERE faq_id = %d",
				$faq_id
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Flushes all object caches related to a given FAQ's relationships.
	 *
	 * @since 1.0.0
	 * @param int $faq_id FAQ post ID.
	 * @return void
	 */
	private function flush_faq_caches( int $faq_id ): void {
		$relationships = $this->get_faq_relationships( $faq_id );

		foreach ( $relationships as $rel ) {
			$this->cache->flush_for_object( (int) $rel['object_id'], $rel['object_type'] );
		}
	}

	/**
	 * Converts a WP_Post object to a plain FAQ DTO array.
	 *
	 * @since 1.0.0
	 * @param \WP_Post $post WordPress post object.
	 * @return array         FAQ DTO.
	 */
	private function post_to_dto( \WP_Post $post ): array {
		return [
			'faq_id'     => (int) $post->ID,
			'question'   => $post->post_title,
			'answer'     => $post->post_content,
			'sort_order' => 0,
		];
	}

	/**
	 * Casts a raw DB row to the correct PHP types.
	 *
	 * @since 1.0.0
	 * @param array $row Raw DB row.
	 * @return array     Type-cast FAQ data array.
	 */
	private function cast_row( array $row ): array {
		return [
			'faq_id'     => (int) $row['faq_id'],
			'question'   => (string) $row['question'],
			'answer'     => (string) $row['answer'],
			'sort_order' => (int) $row['sort_order'],
		];
	}
}

