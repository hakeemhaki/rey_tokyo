<?php
/**
 * Necessary functions in Rey Ajax Product Filter plugin.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if(!function_exists('reyajaxfilter_search_query')):

	function reyajaxfilter_search_query($args = []){

		global $wpdb;

		$search = [];

		if( is_main_query() && ( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) ) ){
			$search[] = WC_Query::get_main_search_query_sql();
		}

		// Products on sale
		if( (isset($_GET['on-sale']) && 1 === absint($_GET['on-sale'])) || (isset($args['onsale']) && $args['onsale']) ){
			if( $sale_ids = reyajaxfilters_get_sale_products() ){
				$search[] = esc_sql( sprintf(" {$wpdb->posts}.ID IN (%s) ", implode(',', $sale_ids)) );
			}
		}

		// Featured
		if( (isset($_GET['is-featured']) && 1 === absint($_GET['is-featured'])) || (isset($args['featured']) && $args['featured']) ){
			if( $featured_ids = wc_get_featured_product_ids() ){
				$search[] = esc_sql( sprintf(" {$wpdb->posts}.ID IN (%s) ", implode(',', $featured_ids)) );
			}
		}

		return implode(' AND ', array_filter( apply_filters('reycore/ajaxfilters/search_query', $search) ) );
	}
endif;

if(!function_exists('reyajaxfilter_meta_query')):
	function reyajaxfilter_meta_query($args = []){

		$meta_query = [];

		if( is_main_query() && ( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) ) ){
			$meta_query  = WC_Query::get_main_meta_query();
		}

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$meta_query['stock_query'] = [
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT LIKE',
			];
		}
		if ( isset($args['stock']) && $args['stock'] ) {
			$meta_query['stock_query'] = [
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => 'LIKE',
			];
		}

		if( isset($args['hash']) && !empty($args['hash']) ){
			if( ($rmq = reyajaxfilter_get_registered_meta_query($args['hash'])) && !empty($rmq) ){
				$meta_query['rey-product-meta'] = $rmq;
			}
		}

		if( isset($args['surpress_filter']) && $args['surpress_filter'] ){
			return $meta_query;
		}

		return apply_filters('reycore/ajaxfilters/meta_query', $meta_query, $args);
	}
endif;

if(!function_exists('reyajaxfilter_tax_query')):
	function reyajaxfilter_tax_query($args = []){

		$tax_query = [];

		if( is_main_query() && ( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) ) ){
			$tax_query  = WC_Query::get_main_tax_query();
		}

		if ( isset($args['query_type']) && 'or' === $args['query_type'] && is_array($tax_query) && !empty($tax_query) ) {
			foreach ( $tax_query as $key => $query ) {
				if ( is_array( $query ) && isset($query['taxonomy']) && isset($args['taxonomy']) && $args['taxonomy'] === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}

		if( isset($args['surpress_filter']) && $args['surpress_filter'] ){
			return $tax_query;
		}

		return apply_filters('reycore/ajaxfilters/tax_query', $tax_query);
	}
endif;


if(!function_exists('reyajaxfilter_post_types_count')):

	function reyajaxfilter_post_types_count(){

		$collect_post_types = apply_filters('reycore/ajaxfilters/post_types_count', ['product']);

		$post_types = [];

		foreach ($collect_post_types as $value) {

			$post_types[] = "'" . esc_sql($value) . "'";
		}

		return implode(',', $post_types);
	}

endif;


if(!function_exists('reyajaxfilter_get_filtered_term_product_counts')):
	/**
	 * Count products within certain terms, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 *
	 * @param  array  $term_ids Term IDs.
	 * @param  string $taxonomy Taxonomy.
	 * @param  string $query_type Query Type.
	 * @return array
	 */
	function reyajaxfilter_get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {

		global $wpdb;

		$meta_query     = new WP_Meta_Query( reyajaxfilter_meta_query() );
		$tax_query      = new WP_Tax_Query( reyajaxfilter_tax_query([
			'term_ids' => $term_ids,
			'taxonomy' => $taxonomy,
			'query_type' => $query_type,
		]) );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && isset($meta_query_sql['join']) && empty($meta_query_sql['join']) ) {
		// 	$meta_query_sql['join'] = "INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )";
		// }

		// Generate query.
		$query           = [];
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$post_types = reyajaxfilter_post_types_count();

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( $post_types )
			AND {$wpdb->posts}.post_status = 'publish'"
			. $tax_query_sql['where'] . $meta_query_sql['where'] .
			'AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

		$search = reyajaxfilter_search_query();

		if ( $search ) {
			$query['where'] .= ' AND ' . $search;
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = apply_filters( 'reycore/woocommerce_get_filtered_term_product_counts_query', $query );
		$query             = implode( ' ', $query );

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $query );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', reyajaxfilter_transient_lifespan() !== false );
		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'reyajaxfilter_counts_' . sanitize_title( $taxonomy ) );
		} else {
			$cached_counts = array();
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			$results                      = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
			$counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			if ( true === $cache ) {
				set_transient( 'reyajaxfilter_counts_' . sanitize_title( $taxonomy ), $cached_counts, DAY_IN_SECONDS );
			}
		}

		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}
endif;


if(!function_exists('reyajaxfilter_get_filtered_meta_product_counts')):
	/**
	 * Count products with meta fields, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 */
	function reyajaxfilter_get_filtered_meta_product_counts($hash) {

		global $wpdb;

		$meta_query     = new WP_Meta_Query( reyajaxfilter_meta_query([
			'hash' => $hash
		]) );
		$tax_query      = new WP_Tax_Query( reyajaxfilter_tax_query() );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && isset($meta_query_sql['join']) && empty($meta_query_sql['join']) ) {
		// 	$meta_query_sql['join'] = "INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )";
		// }
		$post_types = reyajaxfilter_post_types_count();

		$sql  = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type IN ( $post_types ) AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		$search = reyajaxfilter_search_query();

		if ( $search ) {
			$sql .= ' AND ' . $search;
		}

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $sql );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', reyajaxfilter_transient_lifespan() !== false );

		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'reyajaxfilter_prod_meta_counts');
		} else {
			$cached_counts = [];
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {

			$cached_counts[ $query_hash ] = absint( $wpdb->get_var( $sql ) );

			if ( true === $cache ) {
				set_transient( 'reyajaxfilter_prod_meta_counts', $cached_counts, DAY_IN_SECONDS );
			}
		}

		return $cached_counts[ $query_hash ];
	}
endif;


if(!function_exists('reyajaxfilter_get_filtered_product_counts__general')):

	function reyajaxfilter_get_filtered_product_counts__general($args = []) {

		$args = reycore__wp_parse_args($args, [
			'tax_query' => [],
			'meta_query' => [],
			'search' => [],
			'cache_key' => ''
		]);

		global $wpdb;

		$meta_query     = new WP_Meta_Query( reyajaxfilter_meta_query($args['meta_query']) );
		$tax_query      = new WP_Tax_Query( reyajaxfilter_tax_query($args['tax_query']) );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$post_types = reyajaxfilter_post_types_count();

		$sql  = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type IN ( $post_types ) AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		$search = reyajaxfilter_search_query($args['search']);

		if ( $search ) {
			$sql .= ' AND ' . $search;
		}

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $sql );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', reyajaxfilter_transient_lifespan() !== false );

		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'reyajaxfilter_prod_counts' . $args['cache_key']);
		} else {
			$cached_counts = [];
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {

			$cached_counts[ $query_hash ] = absint( $wpdb->get_var( $sql ) );

			if ( true === $cache ) {
				set_transient( 'reyajaxfilter_prod_counts' . $args['cache_key'], $cached_counts, DAY_IN_SECONDS );
			}
		}

		return $cached_counts[ $query_hash ];
	}
endif;


/**
 * Get child term ids for given term.
 *
 * @param  int $term_id
 * @param  string $taxonomy
 * @return array
 */
if (!function_exists('reyajaxfilter_get_term_childs')) {
	function reyajaxfilter_get_term_childs($term_id, $taxonomy, $hide_empty, $order = 'name') {

		if( reyajaxfilter_transient_lifespan() === false ){
			$term_childs = get_terms( $taxonomy, [
				'child_of' => $term_id,
				'fields' => 'ids',
				'hide_empty' => $hide_empty,
				'orderby' => $order
			] );
			return (array)$term_childs;
		}

		$transient_name = 'reyajaxfilter_term_childs_' . md5(sanitize_key($taxonomy) . sanitize_key($term_id));

		if (false === ($term_childs = get_transient($transient_name))) {
			$term_childs = get_terms( $taxonomy, [
				'child_of' => $term_id,
				'fields' => 'ids',
				'hide_empty' => $hide_empty,
				'orderby' => $order
			] );
			set_transient($transient_name, $term_childs, reyajaxfilter_transient_lifespan());
		}

		return (array)$term_childs;
	}
}

/**
 * Get details for given term.
 *
 * @param  int $term_id
 * @param  string $taxonomy
 * @return mixed
 */
if (!function_exists('reyajaxfilter_get_term_data')) {

	function reyajaxfilter_get_term_data($term_value, $taxonomy, $by = 'id') {

		if( $by === 'id' ){

			if( reyajaxfilter_transient_lifespan() === false ){
				return get_term($term_value, $taxonomy);
			}

			$transient_name = 'reyajaxfilter_term_data_' . md5(sanitize_key($taxonomy) . sanitize_key($term_value));

			if (false === ($term_data = get_transient($transient_name))) {
				$term_data = get_term($term_value, $taxonomy);
				set_transient($transient_name, $term_data, reyajaxfilter_transient_lifespan());
			}
		}

		else {

			if( reyajaxfilter_transient_lifespan() === false ){
				return get_term_by($by, esc_attr( $term_value ), $taxonomy);
			}

			$transient_name = 'reyajaxfilter_term_data_' . md5(sanitize_key($taxonomy) . sanitize_key($term_value));

			if (false === ($term_data = get_transient($transient_name))) {
				$term_data = get_term_by($by, esc_attr( $term_value ), $taxonomy);
				set_transient($transient_name, $term_data, reyajaxfilter_transient_lifespan());
			}
		}

		return $term_data;
	}
}


/**
 * Function for clearing old transients stored.
 */
if (!function_exists('reyajaxfilter_clear_transients')) {
	function reyajaxfilter_clear_transients() {
		global $wpdb;
		$transient_name = 'reyajaxfilter';
		$like_main = '%transient_' . $wpdb->esc_like( $transient_name ) . '%';
		$like_timeout = '%transient_timeout_' . $wpdb->esc_like( $transient_name ) . '%';
		$query = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s ", $like_main, $like_timeout );
		return $wpdb->query($query);
	}
}
// clear old transients
add_action('create_term', 'reyajaxfilter_clear_transients');
add_action('edit_term', 'reyajaxfilter_clear_transients');
add_action('delete_term', 'reyajaxfilter_clear_transients');
add_action('save_post', 'reyajaxfilter_clear_transients');
add_action('delete_post', 'reyajaxfilter_clear_transients');
add_action('rey/flush_cache_after_updates', 'reyajaxfilter_clear_transients');


if(!function_exists('reyajaxfilter_ajax_clear_transients')):
	/**
	 * Clear transients ajax
	 *
	 * @since 1.5.0
	 **/
	function reyajaxfilter_ajax_clear_transients()
	{
		if( !current_user_can('manage_options') ){
			wp_send_json_error();
		}

		if( reyajaxfilter_clear_transients() ) {
			wp_send_json_success();
		}
	}
	add_action('wp_ajax_ajaxfilter_clear_transient', 'reyajaxfilter_ajax_clear_transients');
endif;


if (!function_exists('reyajaxfilter_terms_output')):
	/**
	 * Lists terms based on taxonomy
	 *
	 * @since 1.6.2
	 */
	function reyajaxfilter_terms_output($args) {

		$reyAjaxFilters = reyAjaxFilters();
		$chosen_filters = $reyAjaxFilters->chosen_filters;

		$args = wp_parse_args($args, [
			'taxonomy'          => '',
			'data_key'          => '',
			'url_array'         => '',
			'query_type'        => '',
			'placeholder'       => '',
			'enable_multiple'   => false,
			'show_count'        => false,
			'enable_hierarchy'  => false,
			'cat_structure'     => '',
			'hide_empty'        => true,
			'order_by'          => 'name',
			'custom_height'     => '',
			'show_back_btn'     => false,
			'alphabetic_menu'   => false,
			'search_box'        => false,
			'drop_panel'        => false,
			'drop_panel_button' => '',
			'dropdown'          => false,
			'show_tooltips'     => false,
			'accordion_list'    => false,
			'show_checkboxes'   => false,
			'show_checkboxes__radio'   => false,
			'manually_pick_ids' => '',
			'widget_id'         => '',
			'terms'             => [],
			'has_filters'       => (isset($chosen_filters['chosen']) && !empty($chosen_filters['chosen'])),
			'selected_term_ids' => isset($chosen_filters['chosen'][$args['taxonomy']]) ? $chosen_filters['chosen'][$args['taxonomy']]['terms'] : [],
		]);

		$args['multiple_all'] = get_theme_mod('ajaxfilters__multiple_all', false);

		if( !empty($args['selected_term_ids']) ){
			$args['selected_term_ids'] = array_map('absint', $args['selected_term_ids']);
		}

		$parent_args = [
			'fields'       => 'ids',
			'taxonomy'     => $args['taxonomy'],
			'hide_empty'   => $args['hide_empty'],
			'orderby'      => $args['order_by'],
			'order'        => 'ASC',
			'hierarchical' => $args['enable_hierarchy'],
		];

		// Show all including parents
		if ($args['enable_hierarchy']) {
			$parent_args['parent'] = 0;
		}

		// Show all on first timers
		$cat_structure = 'all';

		/**
		 * Legacy.
		 * Show Current if previous "show_children_only" was enabled
		 */
		if( ! $args['cat_structure'] ){
			if( isset($args['show_children_only']) && $args['show_children_only'] ){
				$cat_structure = 'current';

				// show Ancestors if previous "show_children_only__ancestors" was enabled
				if( isset($args['show_children_only__ancestors']) && $args['show_children_only__ancestors'] ){
					$cat_structure = 'all_ancestors';
				}
			}
		}
		else {
			$cat_structure = $args['cat_structure'];
		}
		// End legacy.

		// we're inside a category
		if ( $args['taxonomy'] === 'product_cat') {

			// get current category object
			$current_cat   = get_queried_object();

			// Only show current category's children
			if ( $cat_structure === 'current' ){

				$parent_args['parent'] = 0;

				// only needs to query current category's direct children
				// by pointing out the parent is the current category.
				if( is_shop() ){
					if( !empty($args['selected_term_ids']) ){
						$parent_args['parent'] = $args['selected_term_ids'][0];
					}
				}
				elseif( is_product_category() ){
					if( ! empty($args['selected_term_ids']) ){
						$parent_args['parent'] = $args['selected_term_ids'][0];
					}
					else {
						$parent_args['parent'] = $current_cat->term_id;
					}
				}

			}
			else if ( $cat_structure === 'all_current' ){

				$parent_args['parent'] = 0;
				// only needs to query current category's direct children
				// by pointing out the parent is the current category.
				if( is_shop() ){
					if( ! empty($args['selected_term_ids']) ){
						// commented bc all the other items are hidden in shop page
						// $parent_args['parent'] = $args['selected_term_ids'][0];
					}
				}
				if( is_product_category() ){
					$parent_args['parent'] = $current_cat->term_id;
				}
			}
			else if ( $cat_structure === 'all_ancestors' ){
				$parent_args['parent'] = 0;
			}
			else if ( $cat_structure === 'all' ){
				$parent_args['parent'] = 0;
			}

			// Is likely an attribute page. Force show All categories.
			if( ! is_product_category() && (is_post_type_archive('product') || is_tax(get_object_taxonomies('product'))) ){
				$args['cat_structure'] = 'all';
			}
		}

		$parent_args = apply_filters('reycore/ajaxfilters/terms_args', $parent_args, $args);

		// TAGS only
		if( $manual_ids = $args['manually_pick_ids'] ){
			$parent_args['include'] = explode(',', $manual_ids);
		}

		// On-sale page only. Likely built with Elementor.
		if( isset($args['url_array']['on-sale']) && $args['url_array']['on-sale'] ){

			if( is_shop() ){
				// Since it's not a tax/shop page, remove parent entirely.
				unset($parent_args['parent']);
			}

			$get_sale_terms = wp_get_object_terms( reyajaxfilters_get_sale_products(), $args['taxonomy'], $parent_args );

			if( $args['taxonomy'] === 'product_cat' ){

				if( $args['enable_hierarchy'] ){
					$args['terms'] = $get_sale_terms;
				}

				// show only child terms
				else {

					$sale_terms = [];

					foreach ($get_sale_terms as $term_id) {
						if( $has_ancestors = get_ancestors( $term_id, $args['taxonomy'] ) ){
							$sale_terms[] = $term_id;
						}
					}

					$args['terms'] = $sale_terms;
				}

			}
			// others than categories
			else {
				$args['terms'] = $get_sale_terms;
			}

		}
		else {

			$args['terms'] = get_terms($parent_args);

			/**
			 * If inside a category that doesn't have any children, show the parent ancestor
			 */

			if( empty($args['terms']) && $args['taxonomy'] === 'product_cat' ){

				if( is_shop() && !empty($args['selected_term_ids']) ){
					$current_id = $args['selected_term_ids'][0];
				}
				else if( is_tax('product_cat') || is_post_type_archive('product') || is_tax(get_object_taxonomies('product'))) {
					$current_id = get_queried_object_id();
				}

				$cat_ancestors = get_ancestors( $current_id, 'product_cat' );

				// get first parent ancestor
				if( isset($cat_ancestors[0]) ){
					$parent_args['parent'] = $cat_ancestors[0];
					$args['terms'] = get_terms($parent_args);
				}
			}
		}

		if ( ! (is_array($args['terms']) && !empty($args['terms'])) ) {
			return [
				'html'  => '',
				'found' => false
			];
		}

		if( $args['dropdown'] ){
			return reyajaxfilter_dropdown_terms($args);
		}

		return apply_filters('reycore/ajaxfilters/main_terms_output', reyajaxfilter_main_terms_output($args), $args);
	}
endif;


if (!function_exists('reyajaxfilter_jump_to_cat')) {
	function reyajaxfilter_jump_to_cat($term, $args){

		$output = '';

		if( $args['taxonomy'] !== 'product_cat' ){
			return $output;
		}

		if( $args['enable_multiple'] ){
			return $output;
		}

		// jump to category
		if( ! is_product_category() ){
			return $output;
		}

		$current_cat = get_queried_object_id();

		if( $term->term_id === $current_cat ){
			return $output;
		}

		if( $term->parent === $current_cat ){
			return $output;
		}

		$anc = get_ancestors($term->term_id, $args['taxonomy']);

		if( in_array($current_cat, $anc, true) ){
			return $output;
		}

		$output = 'data-jump="1"';
		// $output .= ' style="color:red"';

		return $output;
	}
}


if (!function_exists('reyajaxfilter_sub_terms_output')) {
	/**
	 * Render Sub-terms
	 *
	 * @param  array $sub_term_args
	 * @param  bool $found used for widgets to determine if they should hide entirely
	 * @return mixed
	 */
	function reyajaxfilter_sub_terms_output($args, $found) {

		$html = '';

		$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['sub_term_ids'], $args['taxonomy'], $args['query_type'] );
		$allow_all_multiple = true;

		foreach ($args['sub_term_ids'] as $sub_term_id) {

			$term = reyajaxfilter_get_term_data($sub_term_id, $args['taxonomy']);

			if ($term && ($term->parent == $args['parent_term_id'])) {

				$_term_id = $term->term_id;
				$_term_name = $term->name;
				$_term_parent = $term->parent;

				$count = isset( $term_counts[ $sub_term_id ] ) ? $term_counts[ $sub_term_id ] : 0;

				$in_filters = in_array( $_term_id, $args['selected_term_ids'] );
				$show_term = $in_filters;

				// Make sure categories are selected
				if( ! empty($args['selected_term_ids']) ){

					// it's cat widget
					if( $args['taxonomy'] === 'product_cat' ) {

						$current_ancestors = get_ancestors( $args['selected_term_ids'][0], $args['taxonomy'] );

						// Force all categories of the most higher active category
						if ( $args['cat_structure'] === 'all' ) {
							$term_ancestors = get_ancestors( $_term_id, $args['taxonomy'] );
							$show_term = !empty( array_intersect($args['selected_term_ids'], $term_ancestors) ) || !empty( array_intersect($current_ancestors, $term_ancestors) );
						}

						// show all subcategories of the current one
						elseif ( $args['cat_structure'] === 'all_current' ) {

							$term_ancestors = get_ancestors( $_term_id, $args['taxonomy'] );
							$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($_term_parent, $current_ancestors) || $in_filters;

							if( is_shop() ){
								$show_term = $show_term || in_array($_term_id, $current_ancestors);
							}
						}

						// show selected's sub-categories and all ancestors and sibligns
						elseif ( $args['cat_structure'] === 'all_ancestors' ) {
							// this shows active siblings of this cat
							$show_siblings = in_array($_term_parent, $current_ancestors);
							$show_term = in_array($_term_parent, $args['selected_term_ids']) || $show_siblings;
						}
					}
				}
				else {

					// default landing shop/tax, without active cat filters
					if( ! is_product_category() && ( is_shop() || is_post_type_archive('product') || is_tax(get_object_taxonomies('product'))) ){

						// it's cat widget
						if( $args['taxonomy'] === 'product_cat' ) {

							if ( $args['cat_structure'] === 'all' ) {
								$show_term = true;
							}
							elseif ( $args['cat_structure'] === 'all_current' ) {
								$show_term = true;
							}
							elseif ( $args['cat_structure'] === 'all_ancestors' ) {
								$term_ancestors = get_ancestors( $_term_id, $args['taxonomy'] );
								$show_term = $_term_parent === 0 || count($term_ancestors) === 1;
							}
							// no need
							elseif ( $args['cat_structure'] === 'current' ) {}
						}

						// Just hide if no count.
						if( ! $count ){
							$show_term = false;
						}
					}

				}

				/**
				 * To be used for exceptions, such as adjusting the display on the Shop page,
				 * so that it shouldn't follow the `cat_structure`.
				 * @since 2.0.0
				 */
				$show_term = apply_filters('reycore/ajaxfilters/subterm_display', $show_term, $term, $args, $term_counts);

				if ( $show_term ) {

					$found = true;
					$_sub_term_ids = [];

					if ( in_array( $args['cat_structure'], ['all', 'all_current', 'all_ancestors'], true) ) {

						// get sub term ids for this term
						$_sub_term_ids = reyajaxfilter_get_term_childs($_term_id, $args['taxonomy'], $args['hide_empty'], $args['order_by'] );

						if ( ! empty($_sub_term_ids) ) {
							$sub_args = [
								'taxonomy'          => $args['taxonomy'],
								'data_key'          => $args['data_key'],
								'query_type'        => $args['query_type'],
								'enable_multiple'   => $args['enable_multiple'],
								'multiple_all'      => $args['multiple_all'],
								'show_count'        => $args['show_count'],
								'enable_hierarchy'  => $args['enable_hierarchy'],
								'cat_structure'     => $args['cat_structure'],
								'selected_term_ids' => $args['selected_term_ids'],
								'hide_empty'        => $args['hide_empty'],
								'order_by'          => $args['order_by'],
								'parent_term_id'    => $_term_id,
								'sub_term_ids'      => $_sub_term_ids,
								'dropdown'          => $args['dropdown'],
							];
						}
					}

					// List
					if( ! $args['dropdown'] ){

						$li_attribute = $before_text = $after_text = '';

						if( ! empty($args['selected_term_ids']) ){
							// only if term id is in active filters
							$is_active_term = in_array($_term_id, $args['selected_term_ids'], true);
						}
						else {
							// if it's the current category
							$is_active_term = is_tax( $args['taxonomy'], $_term_id );
						}

						// Is active term?
						$li_classes = $is_active_term ? 'chosen' : '';

						if( $args['alphabetic_menu'] && strlen($_term_name) > 0 ){
							$li_attribute = sprintf('data-letter="%s"', mb_substr($_term_name, 0, 1, 'UTF-8') );
						}

						if ($args['show_tooltips']) {
							$li_attribute .= sprintf('data-rey-tooltip="%s"', $_term_name);
						}

						$html .= sprintf('<li class="%s" %s>', $li_classes, $li_attribute);

						// show accordion list icon
						if( !empty($_sub_term_ids) && $args['accordion_list'] ){
							$html .= '<button class="__toggle">'. reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']) .'</button>';
						}

						// show checkboxes
						if( $args['show_checkboxes'] ){
							$radio = $args['show_checkboxes__radio'] ? '--radio' : '';
							$before_text .= sprintf('<span class="__checkbox %s"></span>', $radio);
						}

						// show counter
						$after_text .= ($args['show_count'] ? sprintf('<span class="__count">%s</span>', $count) : '');

						$link_attributes = [];
						$link_attributes['key'] = sprintf('data-key="%s"', esc_attr($args['data_key']));
						$link_attributes['value'] = sprintf('data-value="%s"', esc_attr($_term_id));
						$link_attributes['slug'] = sprintf('data-slug="%s"', esc_attr($term->slug));
						$link_attributes['jump'] = reyajaxfilter_jump_to_cat($term, $args);

						if( $args['enable_multiple'] && ! empty($args['selected_term_ids']) ){

							// only active's siblings can support multiple
							$selected_parents = [];

							foreach($args['selected_term_ids'] as $selected_item_id){

								$selected_item = reyajaxfilter_get_term_data($selected_item_id, $args['taxonomy']);
								$selected_parents[] = $selected_item->parent;
							}

							if( in_array($_term_parent, array_unique($selected_parents), true) || $args['multiple_all'] ){
								$link_attributes['multiple-filter'] = sprintf('data-multiple-filter="%s"', esc_attr($args['enable_multiple']));
							}
						}

						$link = get_term_link( $_term_id, $args['taxonomy'] );

						$term_html = sprintf(
							'<a href="%1$s" %5$s>%4$s %2$s %3$s</a>',
							$link,
							$_term_name,
							$after_text,
							$before_text,
							implode(' ', $link_attributes)
						);

						$html .= apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $term, $link, $count );

						if (!empty($_sub_term_ids)) {

							$sub_args['alphabetic_menu'] = $args['alphabetic_menu'];
							$sub_args['show_tooltips'] = $args['show_tooltips'];
							$sub_args['accordion_list'] = $args['accordion_list'];
							$sub_args['show_checkboxes'] = $args['show_checkboxes'];
							$sub_args['show_checkboxes__radio'] = $args['show_checkboxes__radio'];

							$results = reyajaxfilter_sub_terms_output($sub_args, $found);

							$html .= $results['html'];
							$found = $results['found'];
						}

						$html .= '</li>';
					}

					// dropdown
					else {

						$html .= sprintf(
							'<option value="%1$s" %2$s data-depth="%5$s" data-count="%4$s">%3$s</option>',
							$_term_id,
							(in_array($_term_id, $args['selected_term_ids'], true)) ? 'selected="selected"' : '',
							$_term_name,
							$args['show_count'] ? $count : '',
							$args['depth']
						);

						if (!empty($_sub_term_ids)) {

							$sub_args['depth'] = $args['depth'] + 1;
							$results = reyajaxfilter_sub_terms_output($sub_args, $found);

							$html .= $results['html'];
							$found = $results['found'];
						}
					}
				}
			}
		}

		if( ! $args['dropdown'] && $args['enable_hierarchy'] && !empty($html) ){
			$html = '<ul class="children">' . $html . '</ul>';
		}

		return array(
			'html'  => $html,
			'found' => $found
		);
	}
}


if (!function_exists('reyajaxfilter_main_terms_output')):
	/**
	 * Lists terms based on taxonomy
	 *
	 * @since 1.5.0
	 */
	function reyajaxfilter_main_terms_output($args) {

		$html = $list_html = '';
		$found = false;
		$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['terms'], $args['taxonomy'], $args['query_type'] );
		$is_attribute = 'pa_' === substr( $args['taxonomy'], 0, 3 );
		$allow_all_multiple = true;
		$has_selection = false;

		foreach ($args['terms'] as $term_id) {

			$count = isset( $term_counts[ $term_id ] ) ? $term_counts[ $term_id ] : 0;

			$should_display_empty = apply_filters('reycore/ajaxfilters/force_empty', (bool) $count, $args );

			$show_term = $should_display_empty;

			if( ! empty($args['selected_term_ids']) ){

				// if this term id is in active filters we will force
				$in_filters = in_array($term_id, $args['selected_term_ids'], true);
				$show_term = $in_filters;

				if( $in_filters ){
					$has_selection = true;
				}

				// is attribute / custom taxonomy
				if( ($is_attribute || isset($args['taxonomy_name'])) && $count !== 0 ){
					// $attr_term_children = reyajaxfilter_get_term_childs($term_id, $args['taxonomy'], $args['hide_empty'], $args['order_by']);
					$show_term = true;
				}

				// For hierarchical + no support for multiple-filters
				if( $args['taxonomy'] === 'product_cat' ){

					$term = reyajaxfilter_get_term_data($term_id, $args['taxonomy']);

					$current_ancestors = get_ancestors( $args['selected_term_ids'][0], $args['taxonomy'] );
					$term_ancestors = get_ancestors( $term_id, $args['taxonomy'] );

					if ( $args['cat_structure'] === 'all' ) {
						$show_term = true;
					}

					elseif ( $args['cat_structure'] === 'all_current' ) {

						$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;

					}

					elseif ( $args['cat_structure'] === 'all_ancestors' ) {
						$show_term = in_array($term_id, $current_ancestors) || $in_filters;
					}

					elseif ( $args['cat_structure'] === 'current' ) {
						$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;
					}

					if( $args['hide_empty'] && $count === 0 ){
						$show_term = false;
					}
				}
			}

			if ( $show_term ) {

				// flag widgets to output HTML
				$found = true;
				$li_attribute = $before_text = $after_text = '';

				if( ! empty($args['selected_term_ids']) ){
					// only if term id is in active filters
					$is_active_term = in_array($term_id, $args['selected_term_ids'], true);
				}
				else {
					// if it's the current category
					$is_active_term = is_tax( $args['taxonomy'], $term_id );
				}

				// Is active term?
				$li_classes = $is_active_term ? 'chosen' : '';

				$sub_term_ids = [];
				$term = get_term_by( 'id', $term_id, $args['taxonomy'] );

				/**
				 * Determines if sub-terms should be loaded.
				 * Useful for Hierarchical taxonomies.
				 * @since 2.0.0
				 */
				$maybe_get_subterms = $args['taxonomy'] === 'product_cat' && in_array( $args['cat_structure'], ['all', 'all_current', 'all_ancestors'], true);

				if ( apply_filters('reycore/ajaxfilters/show_subterms', $maybe_get_subterms, $args, $show_term ) ) {
					$sub_term_ids = reyajaxfilter_get_term_childs($term_id, $args['taxonomy'], $args['hide_empty'], $args['order_by']);
				}

				$sub_term_list = '';

				if (!empty($sub_term_ids)) {

					$sub_term_args = [
						'taxonomy'          => $args['taxonomy'],
						'data_key'          => $args['data_key'],
						'query_type'        => $args['query_type'],
						'enable_multiple'   => $args['enable_multiple'],
						'multiple_all'      => $args['multiple_all'],
						'show_count'        => $args['show_count'],
						'enable_hierarchy'  => $args['enable_hierarchy'],
						'cat_structure'     => $args['cat_structure'],
						'parent_term_id'    => $term_id,
						'sub_term_ids'      => $sub_term_ids,
						'selected_term_ids' => $args['selected_term_ids'],
						'hide_empty'        => $args['hide_empty'],
						'order_by'          => $args['order_by'],
						'alphabetic_menu'   => $args['alphabetic_menu'],
						'show_tooltips'     => $args['show_tooltips'],
						'accordion_list'    => $args['accordion_list'],
						'show_checkboxes'   => $args['show_checkboxes'],
						'show_checkboxes__radio'   => $args['show_checkboxes__radio'],
						'dropdown'          => false,
					];

					$results = reyajaxfilter_sub_terms_output($sub_term_args, $found);

					$sub_term_list = $results['html'];
					$found = $results['found'];
				}

				if( $args['alphabetic_menu'] && strlen($term->name) > 0 ){
					$li_attribute = sprintf('data-letter="%s"', mb_substr($term->name, 0, 1, 'UTF-8') );
				}

				if ($args['show_tooltips']) {
					$li_attribute .= sprintf('data-rey-tooltip="%s"', $term->name);
				}

				$html .= sprintf('<li class="%s" %s>', $li_classes, $li_attribute);

				// show accordion list icon
				if( !empty($sub_term_list) && $args['accordion_list'] ){
					$html .= '<button class="__toggle">'. reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']) .'</button>';
				}

				// show checkboxes
				if( $args['show_checkboxes'] ){
					$radio = $args['show_checkboxes__radio'] ? '--radio' : '';
					$before_text .= sprintf('<span class="__checkbox %s"></span>', $radio);
				}

				// show counter
				$after_text .= ($args['show_count'] ? sprintf('<span class="__count">%s</span>', $count) : '');

				$link = get_term_link( $term, $args['taxonomy'] );

				$link_attributes = [];
				$link_attributes['key'] = sprintf('data-key="%s"', esc_attr($args['data_key']));
				$link_attributes['value'] = sprintf('data-value="%s"', esc_attr($term_id));
				$link_attributes['slug'] = sprintf('data-slug="%s"', esc_attr($term->slug));
				$link_attributes['jump'] = reyajaxfilter_jump_to_cat($term, $args);

				if( $args['enable_multiple'] ){

					// only active's siblings can support multiple
					$selected_parents = [];

					if( ! empty($args['selected_term_ids']) ) {
						foreach($args['selected_term_ids'] as $selected_item_id){
							$selected_item = reyajaxfilter_get_term_data($selected_item_id, $args['taxonomy']);
							if( isset($selected_item->parent) ){
								$selected_parents[] = $selected_item->parent;
							}
						}
					}

					if( in_array($term->parent, array_unique($selected_parents), true) || $is_attribute || $args['multiple_all'] ){
						$link_attributes['multiple-filter'] = sprintf('data-multiple-filter="%s"', esc_attr($args['enable_multiple']));
					}

				}

				$name = $term->name;

				if( isset($args['display_type']) && $args['display_type'] === 'color' ){
					$name = '';
				}

				$term_html = sprintf(
					'<a href="%1$s" %5$s>%4$s %2$s %3$s</a>',
					$link,
					$name,
					$after_text,
					$before_text,
					implode(' ', $link_attributes)
				);

				$html .= apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $term, $link, $count );

				$html .= $sub_term_list;

				$html .= '</li>';
			}
		}

		$list_classes = $list_wrapper_styles = $list_attributes = [];

		if( ! $args['accordion_list'] && $custom_height = absint($args['custom_height'] ) ){
			$list_wrapper_styles[] = sprintf('height:%spx', $custom_height);
			$list_attributes[] = sprintf('data-height="%s"', $custom_height);
			reyCoreAssets()->add_scripts('simple-scrollbar');
			reyCoreAssets()->add_styles('simple-scrollbar');
		}

		if( $args['enable_hierarchy'] ){
			$list_classes[] = '--hierarchy';

			if( $args['accordion_list'] ){
				$list_classes[] = '--accordion';
			}
		}

		$list_classes[] = '--style-' . ($args['show_checkboxes'] ? 'checkboxes' : 'default');

		if( $args['enable_multiple'] && get_theme_mod('ajaxfilter_apply_filter', false) ){
			$list_classes[] = '--apply-multiple';
		}

		if( $args['alphabetic_menu'] ){
			$list_html .= sprintf('<div class="reyajfilter-alphabetic"><span class="reyajfilter-alphabetic-all %3$s" data-key="%2$s">%1$s</span></div>',
				esc_html__('All', 'rey-core'),
				esc_attr($args['data_key']),
				$args['has_filters'] ? '--reset-filter' : ''
			);
		}

		if( $args['search_box'] ){
			$list_html .= '<div class="reyajfilter-searchbox js-reyajfilter-searchbox">';
			$list_html .= reycore__get_svg_icon__core(['id'=>'reycore-icon-search']);
			$taxonomy_object = get_taxonomy( $args['taxonomy'] );
			$searchbox_label = sprintf(esc_html__('Search %s', 'rey-core'), strtolower($taxonomy_object->label));
			$list_html .= sprintf('<input type="text" placeholder="%s">', $searchbox_label);
			$list_html .= '</div>';
		}

		if( $args['show_back_btn'] ){

			$list_html .= '<div class="reyajfilter-backBtn">';

			$sb_parent_term_id = $url = $term_name = $sb_attributes = '';

			if( is_shop() && !empty($args['selected_term_ids']) ){
				$sb_parent_term__shop = reyajaxfilter_get_term_data($args['selected_term_ids'][0], 'product_cat');
				if( isset($sb_parent_term__shop->parent) ){
					$sb_parent_term_id = $sb_parent_term__shop->parent;
				}
				$sb_attributes .= ' data-shop';
			}
			else if( is_product_category() ){

				$sb_parent_term_id = get_queried_object()->parent;

				if( !empty($args['selected_term_ids']) ){
					$sb_parent_term__cat = reyajaxfilter_get_term_data($args['selected_term_ids'][0], 'product_cat');
					if( isset($sb_parent_term__cat->parent) ){
						$sb_parent_term_id = $sb_parent_term__cat->parent;
					}
				}
			}

			if( $sb_parent_term_id ):
				$sb_parent_term = reyajaxfilter_get_term_data($sb_parent_term_id, 'product_cat');
				$url = get_term_link( $sb_parent_term->term_id, 'product_cat' );
				$term_name = $sb_parent_term->name;
				$sb_attributes .= sprintf(' data-key="product-cato" data-value="%d" data-slug="%s"', $sb_parent_term->term_id, $sb_parent_term->slug);

				$list_html .= sprintf('<a href="%1$s" %5$s>%4$s<span>%2$s %3$s</span></a>',
					$url,
					esc_html__('Back to', 'rey-core'),
					$term_name,
					reycore__arrowSvg(false),
					$sb_attributes
				);
			endif;

			$list_html .= '</div>';
		}

		$list_attributes[] = sprintf('data-taxonomy="%s"', esc_attr($args['taxonomy']));
		$list_attributes[] = sprintf('data-shop="%s"', esc_url( get_permalink( wc_get_page_id('shop') ) ) );

		$list_html .= sprintf('<div class="reyajfilter-layered-nav %s" %s>', implode(' ', $list_classes), implode(' ', $list_attributes));

			$list_html .= sprintf('<div class="reyajfilter-layered-navInner" style="%s">', implode(' ', $list_wrapper_styles));
			$list_html .= '<ul class="reyajfilter-layered-list">';
			$list_html .= $html;
			$list_html .= '</ul>';
			$list_html .= '</div>';

			if( ! $args['accordion_list'] && $custom_height ){
				$list_html .= '<span class="reyajfilter-customHeight-all">'. esc_html__('Show All +', 'rey-core') .'</span>';
			}

		$list_html .= '</div>';

		$widget_output = '';

		if( $args['drop_panel'] ){

			$widget_output .= reyajaxfilter_droppanel_output( $list_html, [
				'button' => $args['drop_panel_button'],
				'keep_active' => $args['drop_panel_keep_active'],
				'key' => $args['data_key'],
				'selection' => $has_selection
			] );

		}
		else {
			$widget_output .= $list_html;
		}

		return [
			'html'  => $widget_output,
			'found' => $found
		];
	}
endif;

if(!function_exists('reyajaxfilter_droppanel_output')):
	/**
	 * Drop panel markup
	 *
	 * @since 2.0.0
	 **/
	function reyajaxfilter_droppanel_output( $html, $args = [] )
	{

		$args = wp_parse_args($args, [
			'button'      => '',
			'keep_active' => false,
			'key'         => false,
			'selection'   => false,
			'clear_text'  =>  esc_html__('Clear all', 'rey-core')
		]);

		reyCoreAssets()->add_scripts('reyajfilter-droppanel');
		reyCoreAssets()->add_styles(['reyajfilter-droppanel']);

		$drop_output = '<div class="reyajfilter-dp">';

		$drop_output .= sprintf(
			'<button class="reyajfilter-dp-btn %3$s" data-keep-active="%4$s"><span class="reyajfilter-dpText">%1$s</span>%2$s</button>',
			$args['button'],
			reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']),
			$args['selection'] ? '--selection' : '',
			$args['keep_active'] ? 1 : 0
		);

		$drop_output .= '<div class="reyajfilter-dp-drop" aria-hidden="true">';
		$drop_output .= $html;

		if( $args['selection'] && $args['key'] ){
			$key = is_array($args['key']) ? implode(',', $args['key']) : $args['key'];
			$drop_output .= sprintf('<button class="reyajfilter-dp-clear" data-key="%2$s">%1$s</button>', $args['clear_text'], esc_attr( $key ));
		}

		$drop_output .= '</div>';
		$drop_output .= '</div>';

		return $drop_output;
	}
endif;

/**
 * reyajaxfilter_dropdown_terms function
 *
 * @param  array $args
 * @return mixed
 */
if (!function_exists('reyajaxfilter_dropdown_terms')):
	function reyajaxfilter_dropdown_terms($args) {

		$html = '';
		$found = false;

		$placeholder = $args['placeholder'];

		if( empty($placeholder) ):
			if (preg_match('/^attr/', $args['data_key'])) {
				$attr = str_replace(['attra-', 'attro-'], '', $args['data_key']);
				$placeholder = sprintf(__('Choose %s', 'rey-core'), reyajaxfilter_get_attribute_name( $attr ));
			} elseif (preg_match('/^product-cat/', $args['data_key'])) {
				$placeholder = sprintf(__('Choose category', 'rey-core'));
			}
			elseif (preg_match('/^product-tag/', $args['data_key'])) {
				$placeholder = sprintf(__('Choose tag', 'rey-core'));
			}
			elseif ( isset($args['taxonomy_name']) && !empty($args['taxonomy_name']) ) {
				$placeholder = sprintf(__('Choose %s', 'rey-core'), $args['taxonomy_name']);
			}
		endif;

		if (!empty($args['terms'])) {

			// required scripts
			reyCoreAssets()->add_scripts('reyajfilter-select2');
			reyCoreAssets()->add_styles(['reyajfilter-dropdown', 'reyajfilter-select2']);

			$html .= '<div class="reyajfilter-dropdown-nav">';

				$attributes = ($args['enable_multiple'] ? 'multiple="multiple"' : '');

				if( $args['search_box'] ):
					$attributes .= ' data-search="true"';
				endif;

				if( $args['show_checkboxes'] ):

					if( $args['enable_multiple'] ) {
						reyCoreAssets()->add_scripts('reyajfilter-select2-multi-checkboxes');
					}

					$attributes .= ' data-checkboxes="true"';
				endif;

				if( isset($args['dd_width']) && $dropdown_width = $args['dd_width'] ){
					$attributes .= sprintf(' data-ddcss=\'%s\'', wp_json_encode([
						'min-width' => $dropdown_width . 'px'
					]));
				}

				$html .= sprintf( '<select class="%1$s" name="%2$s" style="width: 100%%;" %3$s data-placeholder="%4$s">',
					'reyajfilter-select2 ' . (($args['enable_multiple'] ? 'reyajfilter-select2-multiple' : 'reyajfilter-select2-single')),
					$args['data_key'],
					$attributes,
					$placeholder
				);

				if (!$args['enable_multiple']) {
					$html .= '<option value=""></option>';
				}

				$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['terms'], $args['taxonomy'], $args['query_type'] );

				foreach ($args['terms'] as $term_id) {

					$count = isset( $term_counts[ $term_id ] ) ? $term_counts[ $term_id ] : 0;

					$should_display_empty = apply_filters('reycore/ajaxfilters/force_empty', (bool) $count, $args );
					$show_term = $should_display_empty;

					if( ! empty($args['selected_term_ids']) ){

						// if this term id is in active filters we will force
						$in_filters = in_array($term_id, $args['selected_term_ids'], true);
						$show_term = $in_filters;

						// For hierarchical + no support for multiple-filters
						if( $args['taxonomy'] === 'product_cat'){

							$term = reyajaxfilter_get_term_data($term_id, $args['taxonomy']);

							$current_ancestors = get_ancestors( $args['selected_term_ids'][0], $args['taxonomy'] );
							$term_ancestors = get_ancestors( $term_id, $args['taxonomy'] );

							if ( $args['cat_structure'] === 'all' ) {
								$show_term = true;
							}

							elseif ( $args['cat_structure'] === 'all_current' ) {

								$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;

							}

							elseif ( $args['cat_structure'] === 'all_ancestors' ) {
								$show_term = in_array($term_id, $current_ancestors) || $in_filters;
							}

							elseif ( $args['cat_structure'] === 'current' ) {
								$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;
							}
						}
					}

					if ($show_term) {

						$found = true;

						if( ! empty($args['selected_term_ids']) ){
							// only if term id is in active filters
							$is_active_term = in_array($term_id, $args['selected_term_ids'], true);
						}
						else {
							// if it's the current category
							$is_active_term = is_tax( $args['taxonomy'], $term_id );
						}

						$term = get_term_by( 'id', $term_id, $args['taxonomy'] );

						$html .= sprintf( '<option value="%1$s" %2$s data-count="%4$s">%3$s</option>',
							$term_id,
							($is_active_term ? 'selected="selected"' : ''),
							$term->name,
							($args['show_count'] ? $count : '')
						);

						/**
						 * Determines if sub-terms should be loaded.
						 * Useful for Hierarchical taxonomies.
						 * @since 2.0.0
						 */
						$maybe_get_subterms = $args['taxonomy'] === 'product_cat' && in_array( $args['cat_structure'], ['all', 'all_current', 'all_ancestors'], true);

						if ( apply_filters('reycore/ajaxfilters/show_subterms', $maybe_get_subterms, $args, $show_term ) ) {

							// get sub term ids for this term
							$sub_term_ids = reyajaxfilter_get_term_childs($term_id, $args['taxonomy'], $args['hide_empty']);

							if (!empty($sub_term_ids)) {
								$sub_term_args = [
									'taxonomy'          => $args['taxonomy'],
									'data_key'          => $args['data_key'],
									'query_type'        => $args['query_type'],
									'enable_multiple'   => $args['enable_multiple'],
									'multiple_all'      => $args['multiple_all'],
									'show_count'        => $args['show_count'],
									'enable_hierarchy'  => $args['enable_hierarchy'],
									'cat_structure'     => $args['cat_structure'],
									'parent_term_id'    => $term_id,
									'sub_term_ids'      => $sub_term_ids,
									'selected_term_ids' => $args['selected_term_ids'],
									'hide_empty'        => $args['hide_empty'],
									'order_by'          => $args['order_by'],
									'dropdown'          => true,
									'depth'             => 1,
								];

								$results = reyajaxfilter_sub_terms_output($sub_term_args, $found);

								$html .= $results['html'];
								$found = $results['found'];
							}
						}
					}
				}

				$html .= '</select>';
			$html .= '</div>';
		}

		return [
			'html'  => $html,
			'found' => $found
		];
	}
endif;


if( !function_exists('reyajaxfilter_get_attribute_name') ):
	/**
	 * Pulls label from attributes
	 *
	 * @since 3.0.1
	 */
	function reyajaxfilter_get_attribute_name( $attr ){

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if( is_array($attribute_taxonomies) && !empty($attribute_taxonomies) ){

			$attribute = array_filter($attribute_taxonomies, function($v, $k) use ($attr){
				return $v->attribute_name === $attr;
			}, ARRAY_FILTER_USE_BOTH);

			$attribute = array_values($attribute);

			if( isset($attribute[0]) && isset($attribute[0]->attribute_label) ){
				return $attribute[0]->attribute_label;
			}
		}

		return $attr;
	}
endif;


/**
 * Transient lifespan
 *
 * @return int
 */
if (!function_exists('reyajaxfilter_transient_lifespan')) {
	function reyajaxfilter_transient_lifespan() {

		if( ! get_theme_mod('ajaxfilter_transients', true) ){
			return false;
		}

		return apply_filters('reycore/ajaxfilters/transient_lifespan', REYAJAXFILTERS_CACHE_TIME);
	}
}

if(!function_exists('reyajaxfilter_filter_color_attr_html')):
	/**
	 * Override Color Attribute html in filter nav
	 *
	 * @since 1.5.0
	 **/
	function reyajaxfilter_filter_color_attr_html($term_html, $term, $link, $count)
	{
		if( $taxonomy = $term->taxonomy ) {

			if( $taxonomy == 'product_cat' ) {
				return $term_html;
			}

			$colors = $color = '';

			if( class_exists('ReyCore_WooCommerce_Variations') ){

				// Customize color attributes
				$colors = ReyCore_WooCommerce_Variations::getInstance()->get_color_attributes();

				$term_id = $term->term_id;

				if( is_array($colors) && !empty($colors) && isset($colors[$term_id]) ) {

					$swatch_tag = ReyCore_WooCommerce_Variations::parse_attribute_color([
						'value' => $term->slug,
						'name' => $term->name
					], $colors[$term_id]['color'] );

					// $term_html = str_replace( $term->name, $swatch_tag, $term_html );
					$term_html = str_replace( '</a>', $swatch_tag . '</a>', $term_html );
				}
			}
		}

		return $term_html;
	}
endif;

if(!function_exists('reyajaxfilter_filter_color_list_attr_html')):
	/**
	 * Override Color Attribute html in filter nav
	 *
	 * @since 1.5.0
	 **/
	function reyajaxfilter_filter_color_list_attr_html($term_html, $term, $link, $count)
	{
		if( $taxonomy = $term->taxonomy ) {

			if( $taxonomy == 'product_cat' ) {
				return $term_html;
			}

			$colors = $color = '';

			if( class_exists('ReyCore_WooCommerce_Variations') ){

				// Customize color attributes
				$colors = ReyCore_WooCommerce_Variations::getInstance()->get_color_attributes();

				$term_id = $term->term_id;

				if( is_array($colors) && !empty($colors) && isset($colors[$term_id]) ) {

					$swatch_tag = ReyCore_WooCommerce_Variations::parse_attribute_color([
						'value' => $term->slug,
						'name' => $term->name
					], $colors[$term_id]['color'] );

					// $term_html = str_replace( $term->name, $swatch_tag, $term_html );
					$term_html = str_replace( '</a>', $swatch_tag . '</a>', $term_html );
				}
			}
		}

		return $term_html;
	}
endif;

if(!function_exists('reyajaxfilter_filter_image_attr_html')):
	/**
	 * Override Image Attribute html in filter nav
	 *
	 * @since 1.5.4
	 **/
	function reyajaxfilter_filter_image_attr_html($term_html, $term, $link, $count)
	{
		if( $taxonomy = $term->taxonomy ) {

			if( $taxonomy == 'product_cat' ) {
				return $term_html;
			}

			if( class_exists('ReyCore_WooCommerce_Variations') ){

				// get all image attributes
				$images = ReyCore_WooCommerce_Variations::getInstance()->get_image_attributes();

				if( is_array($images) && !empty($images) && isset($images[$term->term_id]) ) {

					$image_size = function_exists('woo_variation_swatches') ? woo_variation_swatches()->get_option( 'attribute_image_size' ) : 'thumbnail';
					$image = '';

					array_walk($images, function($key) use(&$image, $term, $image_size){
						if( $key['slug'] === $term->slug ){
							$image = wp_get_attachment_image( absint($key['image'] ), $image_size);
						}
					});

					$term_html = str_replace( $term->name, $image, $term_html );
				}
			}
		}

		return $term_html;
	}
endif;


if(!function_exists('reyajaxfilter__prevent_search_redirect')):
	/**
	 * Prevent redirect in Search, when filtering
	 *
	 * @since 1.9.7
	 **/
	function reyajaxfilter__prevent_search_redirect($status) {
		if( is_filtered() ){
			return false;
		}
		return $status;
	}
	add_filter( 'woocommerce_redirect_single_search_result', 'reyajaxfilter__prevent_search_redirect' );
endif;


if(!function_exists('reyajaxfilter_load_simple_template')):
	function reyajaxfilter_load_simple_template($template) {

		if( ! apply_filters('reycore/woocommerce/products/minimal_tpl', true) ){
			return $template;
		}

		if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtoupper( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'XMLHTTPREQUEST' &&
			isset($_REQUEST['reynotemplate']) && absint( $_REQUEST['reynotemplate'] ) === 1 ){
			return REYAJAXFILTERS_DIR . '/includes/notemplate.php';
		}

		return $template;
	}
	add_filter( 'template_include', 'reyajaxfilter_load_simple_template', 20 );
endif;


if(!function_exists('reyajaxfilters_get_sale_products')):
	/**
	 * Get filtered sale products ids
	 *
	 * @since 1.6.3
	 **/
	function reyajaxfilters_get_sale_products() {
		$products = apply_filters('reycore/ajaxfilters/product_ids_on_sale', wc_get_product_ids_on_sale());
		return array_unique($products);
	}
endif;


if(!function_exists('reyajaxfilters__filter_sidebar_classes')):
	/**
	 * Filter "Top Filters Sidebar" classes
	 *
	 * @since 1.6.6
	 **/
	function reyajaxfilters__filter_sidebar_classes( $classes, $position )
	{
		if( $position === 'filters-top-sidebar' && get_theme_mod('ajaxfilter_topbar_sticky', false) ){
			$classes['topbar_sticky'] = '--sticky';
		}

		return $classes;
	}
	add_filter('rey/content/sidebar_class', 'reyajaxfilters__filter_sidebar_classes', 10, 2);
endif;


if(!function_exists('reyajaxfilters__filter_admin_titles')):
	/**
	 * add custom titles in widget title
	 *
	 * @since 1.6.6
	 **/
	function reyajaxfilters__filter_admin_titles( $categs, $sh = 'hide' )
	{
		$published_categories = [];
		foreach ( $categs as $key => $slug) {
			$term = get_term_by('slug', $slug, 'product_cat');
			$published_categories[] = $term->name;
		}
		if( !empty($published_categories) ): ?>
			<span class="rey-widgetToTitle --hidden" data-class="__published-on-categ">
				<span><?php printf(esc_html__('%s on: ', 'rey-core'), ucfirst($sh)) ?></span>
				<span><?php echo implode(', ', $published_categories); ?></span>
			</span>
		<?php endif;
	}
endif;


if(!function_exists('reyajaxfilter_widget__option')):
/**
 * Generate widget option
 *
 * @since 1.6.7
 **/
function reyajaxfilter_widget__option( $widget, $instance = [], $args = [] )
{
	if( empty($args) ){
		return;
	}

	$args = wp_parse_args($args, [
		'name' => '',
		'type' => '',
		'label' => '',
		'value' => '',
		'conditions' => [],
		'wrapper_class' => '',
		'field_class' => 'widefat',
		'separator' => '',
		'placeholder' => '',
		'suffix' => '',
	]);

	if( !empty($args['type']) ):

		if( empty($args['name']) ){
			$args['name'] = 'widget-title-' . sanitize_title($args['label']);
		}

		if( !empty($args['conditions'])  ){
			$conditions = [];
			foreach ($args['conditions'] as $key => $value) {
				$condition = $value;
				$condition['name'] = $widget->get_field_name($value['name']);
				$conditions[] = $condition;
			}
			$args['conditions'] = $conditions;
		}

		$func = "reyajaxfilter_widget__option_{$args['type']}";

		// calc. separator
		if( !empty($args['separator']) ){
			$args['wrapper_class'] .= '--separator-' . $args['separator'];
		}

		if( function_exists($func) ){

			printf('<div id="%1$s-wrapper" class="rey-widget-field %3$s" %2$s>',
				$widget->get_field_id($args['name']),
				!empty($args['conditions']) ? sprintf("data-condition='%s'", wp_json_encode($args['conditions'])) : '',
				$args['wrapper_class']
			);

				$func($widget, $instance, $args);

				if( $suffix = $args['suffix'] ){
					printf('<span class="__suffix">%s</span>', $suffix);
				}

			echo '</div>';

		}
	endif;
}
endif;

if(!function_exists('reyajaxfilter_widget__option_checkbox')):
	/**
	 * Generate widget checkbox
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_checkbox( $widget, $instance = [], $args = [] )
	{
		printf( '<input class="checkbox %5$s" type="checkbox" id="%1$s" name="%2$s" %3$s value="%4$s" >',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']),
			isset($instance[$args['name']]) ? checked( $instance[$args['name']], true, false ) : '',
			$args['value'],
			$args['field_class']
		);
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_text')):
	/**
	 * Generate widget text
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_text( $widget, $instance = [], $args = [] )
	{
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);

		$attributes = [];

		if( $placeholder = $args['placeholder'] ){
			$attributes[] = sprintf('placeholder="%s"', $placeholder);
		}

		$value = $args['value'];

		if( isset( $instance[$args['name']] ) ){
			$value = $instance[$args['name']];
		}

		printf( '<input class="%4$s" type="text" id="%1$s" name="%2$s" value="%3$s" %5$s>',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']),
			esc_attr($value),
			$args['field_class'],
			implode(' ', $attributes)
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_number')):
	/**
	 * Generate widget number
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_number( $widget, $instance = [], $args = [] )
	{
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);

		$attributes = [];

		if( isset($args['options']) && !empty($args['options']) ){

			if( isset($args['options']['step']) ){
				$attributes[] = sprintf('step="%s"', $args['options']['step']);
			}

			if( isset($args['options']['min']) ){
				$attributes[] = sprintf('min="%s"', $args['options']['min']);
			}

			if( isset($args['options']['max']) ){
				$attributes[] = sprintf('max="%s"', $args['options']['max']);
			}
		}

		printf( '<input class="%4$s" type="number" id="%1$s" name="%2$s" value="%3$s" %5$s>',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']),
			esc_attr($instance[$args['name']]),
			$args['field_class'],
			implode(' ', $attributes)
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_select')):
	/**
	 * Generate widget select list
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_select( $widget, $instance = [], $args = [] )
	{
		printf(
			'<label for="%1$s">%2$s</label>',
			$widget->get_field_id($args['name']),
			$args['label']
		);

		$is_multiple = isset( $args['multiple'] ) && $args['multiple'];

		$options = '';

		if( isset($args['options']) && !empty($args['options']) ){
			foreach ($args['options'] as $key => $value) {

				$saved = $instance[$args['name']];

				if( ! $instance[$args['name']] && $args['value'] ){
					$saved = $args['value'] ;
				}

				if( $is_multiple ){
					$is_selected = in_array( $key, $saved, true ) ? 'selected' : '';
				}
				else {
					$is_selected = selected( $saved, $key, false);
				}

				$options .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $key, $value, $is_selected );
			}
		}

		printf( '<select class="%4$s" id="%1$s" name="%2$s" %5$s>%3$s</select>',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']) . ( $is_multiple ? '[]' : '' ),
			$options,
			$args['field_class'],
			$is_multiple ? 'multiple' : ''
		);
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_title')):
	/**
	 * Generate widget title
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_title( $widget, $instance = [], $args = [] )
	{
		printf('<span class="%s">%s</span>', $args['field_class'], $args['label'] );
	}
endif;

if(!function_exists('reyajaxfilter_widget__option_range_points')):
	/**
	 * Generate widget range_points
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_range_points( $widget, $instance = [], $args = [] )
	{
		$field_id = $widget->get_field_id($args['name']);
		$start_name = $widget->get_field_name($args['name'] . '_start');
		$field_name = $widget->get_field_name($args['name']);
		$end_name = $widget->get_field_name($args['name'] . '_end');
		$start_enabled = $instance[ $args['name'] . '_start']['enable'] == 1;
		$end_enabled = $instance[ $args['name'] . '_end']['enable'] == 1;
		$supports_label = isset($args['supports']) && in_array('labels', $args['supports'], true);
		?>

		<div class="rey-widgetRangePoints-wrapper" >

			<p class="rey-widget-innerTitle"><?php echo $args['label']; ?></p>

			<p class="rey-widgetRangePoints-list --start <?php echo ! $start_enabled ? '--hidden' : ''; ?>">
				<input type="hidden" name="<?php echo $start_name; ?>[enable]" value="<?php echo $instance[ $args['name'] . '_start']['enable']; ?>" />
				<input type="text" class="widefat __text" name="<?php echo $start_name; ?>[text]" value="<?php echo $instance[ $args['name'] . '_start']['text']; ?>" placeholder="<?php _e('eg: Under', 'rey-core'); ?>" />
				<input type="text" class="widefat __max" name="<?php echo $start_name; ?>[max]" value="<?php echo $instance[ $args['name'] . '_start']['max']; ?>" placeholder="<?php _e('eg: 100', 'rey-core'); ?>" />
				<a href="#" class="rey-widgetRangePoints-remove">&times;</a>
			</p>

			<div id="<?php echo $field_id; ?>-wrapper" data-id="<?php echo $field_id; ?>" class="rey-widgetRangePoints-listWrapper">
				<?php if (isset($instance[ $args['name'] ]) && !empty($instance[ $args['name'] ])): ?>
					<?php
						$items = array_values($instance[ $args['name'] ]);
						foreach ($items as $key => $item): ?>
						<p class="rey-widgetRangePoints-list --default" data-key="<?php echo $key ?>">
							<?php if( $supports_label ): ?>
								<input type="text" class="widefat __label" name="<?php printf('%s[%s][label]', $field_name, $key) ; ?>" value="<?php echo isset($item['label']) ? $item['label'] : ''; ?>" placeholder="<?php _e('Label', 'rey-core'); ?>" />
							<?php endif; ?>
							<input type="text" class="widefat __min" name="<?php printf('%s[%s][min]', $field_name, $key) ; ?>" value="<?php echo isset($item['min']) ? $item['min'] : ''; ?>" placeholder="<?php _e('Min value', 'rey-core'); ?>" />
							<input type="text" class="widefat __to" name="<?php printf('%s[%s][to]', $field_name, $key) ; ?>" value="<?php echo isset($item['to']) ? $item['to'] : ''; ?>" placeholder="<?php _e('to', 'rey-core'); ?>" />
							<input type="text" class="widefat __max" name="<?php printf('%s[%s][max]', $field_name, $key) ; ?>" value="<?php echo isset($item['max']) ? $item['max'] : ''; ?>" placeholder="<?php _e('Max value', 'rey-core'); ?>" />
							<a href="javascript:void(0)" class="rey-widgetRangePoints-remove">&times;</a>
						</p>
					<?php endforeach ?>
				<?php else: ?>
					<p class="rey-widgetRangePoints-list --default">
						<?php if( $supports_label ): ?>
							<input type="text" class="widefat __label" name="<?php echo $field_name; ?>[0][label]" value="" placeholder="<?php _e('Label', 'rey-core'); ?>" />
						<?php endif; ?>
						<input type="text" class="widefat __min" name="<?php echo $field_name; ?>[0][min]" value="" placeholder="<?php _e('Min value', 'rey-core'); ?>" />
						<input type="text" class="widefat __to" name="<?php echo $field_name; ?>[0][to]" value="" placeholder="<?php _e('to', 'rey-core'); ?>" />
						<input type="text" class="widefat __max" name="<?php echo $field_name; ?>[0][max]" value="" placeholder="<?php _e('Max value', 'rey-core'); ?>" />
						<a href="javascript:void(0)" class="rey-widgetRangePoints-remove">&times;</a>
					</p>
				<?php endif ?>
			</div>

			<p class="rey-widgetRangePoints-list --end <?php echo ! $end_enabled ? '--hidden' : ''; ?>">
				<input type="hidden" name="<?php echo $end_name; ?>[enable]" value="<?php echo $instance[ $args['name'] . '_end']['enable']; ?>" />
				<input type="text" class="widefat __text" name="<?php echo $end_name; ?>[text]" value="<?php echo $instance[ $args['name'] . '_end']['text']; ?>" placeholder="<?php _e('eg: Over', 'rey-core'); ?>" />
				<input type="text" class="widefat __min" name="<?php echo $end_name; ?>[min]" value="<?php echo $instance[ $args['name'] . '_end']['min']; ?>" placeholder="<?php _e('eg: 1000', 'rey-core'); ?>" />
				<a href="#" class="rey-widgetRangePoints-remove">&times;</a>
			</p>

			<p class="rey-widgetRangePoints-addWrapper">
				<a href="javascript:void(0)" class="button rey-widgetRangePoints-add"><?php _e('Add', 'rey-core'); ?></a>
				&nbsp;&nbsp; <a href="javascript:void(0)" class="rey-widgetRangePoints-add-start <?php echo $start_enabled ? '--inactive' : ''; ?>"><?php _e('Add start', 'rey-core'); ?></a>
				&nbsp;&nbsp; <a href="javascript:void(0)" class="rey-widgetRangePoints-add-end <?php echo $end_enabled ? '--inactive' : ''; ?>"><?php _e('Add end', 'rey-core'); ?></a>
			</p>

			<script type="text/html" id="tmpl-rey-<?php echo $field_id; ?>">
				<p class="rey-widgetRangePoints-list --default" data-key="{{data.int}}">
					<?php if( $supports_label ): ?>
						<input type="text" class="widefat __label" name="<?php echo $field_name; ?>[{{data.int}}][label]" value="" placeholder="<?php _e('Label', 'rey-core'); ?>" />
					<?php endif; ?>
					<input type="text" class="widefat __min" name="<?php echo $field_name; ?>[{{data.int}}][min]" value="" placeholder="<?php _e('Min value', 'rey-core'); ?>" />
					<input type="text" class="widefat __to" name="<?php echo $field_name; ?>[{{data.int}}][to]" value="" placeholder="<?php _e('to', 'rey-core'); ?>" />
					<input type="text" class="widefat __max" name="<?php echo $field_name; ?>[{{data.int}}][max]" value="" placeholder="<?php _e('Max value', 'rey-core'); ?>" />
					<a href="javascript:void(0)" class="rey-widgetRangePoints-remove">&times;</a>
				</p>
			</script>

		</div>
		<?php
	}
endif;


if(!function_exists('reyajaxfilter_widget__option_repeater__fields')):
	/**
	 * Generate widget repeater
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_repeater__fields( $field_name, $fields, $key = 0, $item = [] )
	{
		$output = '';

		foreach ($fields as $k => $field){

			$classes = [
				'widefat',
				'__field-' . $k,
				isset($field['size']) ? 'size-' . $field['size'] : ''
			];

			$value = !empty($item) && isset($item[$field['key']]) ? $item[$field['key']] : '';

			if( $field['type'] === 'select' ){

				$output .= sprintf( '<select class="%2$s" name="%1$s">',
					sprintf('%s[%s][%s]', $field_name, $key, $field['key']),
					implode(' ', $classes)
				);
					foreach ($field['choices'] as $choice_key => $choice_value) {
						$output .= sprintf( '<option value="%1$s" %3$s>%2$s</option>',
							$choice_key,
							$choice_value,
							selected($value, $choice_key, false)
						);
					}
				$output .= '</select>';
			}

			elseif ($field['type'] === 'text') {
				$output .= sprintf( '<input type="text" class="%4$s" name="%1$s" value="%2$s" placeholder="%3$s" />',
					sprintf('%s[%s][%s]', $field_name, $key, $field['key']),
					$value,
					$field['title'],
					implode(' ', $classes)
				);
			}
		}

		$output .= '<a href="javascript:void(0)" class="rey-widgetRepeater-remove">&times;</a>';

		return $output;
	}
endif;


if(!function_exists('reyajaxfilter_widget__option_repeater')):
	/**
	 * Generate widget repeater
	 *
	 * @since 1.6.7
	 **/
	function reyajaxfilter_widget__option_repeater( $widget, $instance = [], $args = [] )
	{
		$field_id = $widget->get_field_id($args['name']);
		$field_name = $widget->get_field_name($args['name']);
		?>

		<div class="rey-widgetRepeater-wrapper" >

			<p class="rey-widget-innerTitle"><?php echo $args['label']; ?></p>

			<div id="<?php echo $field_id; ?>-wrapper" data-id="<?php echo $field_id; ?>" class="rey-widgetRepeater-listWrapper">
				<?php if (isset($instance[ $args['name'] ]) && !empty($instance[ $args['name'] ])): ?>
					<?php
						$items = array_values($instance[ $args['name'] ]);
						foreach ($items as $key => $item): ?>
						<p class="rey-widgetRepeater-list --default" data-key="<?php echo $key ?>">
							<?php
							echo reyajaxfilter_widget__option_repeater__fields($field_name, $args['fields'], $key, $item); ?>
						</p>
					<?php endforeach ?>
				<?php else: ?>
					<p class="rey-widgetRepeater-list --default">
						<?php
						echo reyajaxfilter_widget__option_repeater__fields($field_name, $args['fields']); ?>
					</p>
				<?php endif ?>
			</div>

			<p class="rey-widgetRepeater-addWrapper">
				<a href="javascript:void(0)" class="button rey-widgetRepeater-add"><?php _e('Add', 'rey-core'); ?></a>
			</p>

			<script type="text/html" id="tmpl-rey-<?php echo $field_id; ?>">
				<p class="rey-widgetRepeater-list --default" data-key="{{data.int}}">
					<?php
					echo reyajaxfilter_widget__option_repeater__fields($field_name, $args['fields'], '{{data.int}}'); ?>
				</p>
			</script>

		</div>
		<?php
	}
endif;


if(!function_exists('reyajaxfilter_get_registered_meta_query')):
/**
 * Get Registered meta query by hash
 *
 * @since 1.9.4
 **/
function reyajaxfilter_get_registered_meta_query( $hash )
{
	$data = reyajaxfilter_get_registered_meta_query_data($hash);

	if( empty($data) ){
		return [];
	}

	$current_meta_query = [
		'key'           => reycore__clean($data['key']),
		'value'         => reycore__clean($data['value']),
		'compare'       => reycore__clean($data['operator']),
	];

	switch($data['operator']):

		// Is not empty
		case "!=empty":
			$current_meta_query['compare'] = '!=';
			$current_meta_query['value'] = '';
			break;

		// Is empty
		case "==empty":
			$current_meta_query['compare'] = '=';
			$current_meta_query['value'] = '';
			break;

		case "==":
			$current_meta_query['compare'] = '=';
			break;

		case "!=":
			$current_meta_query['compare'] = '!=';
			break;

		case ">":
			$current_meta_query['type'] = 'DECIMAL';
			break;

		case "<":
			$current_meta_query['type'] = 'DECIMAL';
			break;

	endswitch;

	return $current_meta_query;
}
endif;


if(!function_exists('reyajaxfilter_get_registered_meta_query_data')):
/**
 * Get Registered meta query by hash
 *
 * @since 1.9.4
 **/
function reyajaxfilter_get_registered_meta_query_data( $hash )
{
	$registered_mq = get_theme_mod('ajaxfilters_meta_queries', []);
	$data = [];

	foreach($registered_mq as $mq){

		$registered_hash = substr( md5( wp_json_encode( $mq ) ), 0, 10 );

		if( $registered_hash !== $hash ){
			continue;
		}

		$data = $mq;
	}

	return $data;
}
endif;


if(!function_exists('reyajaxfilter_widget__option_hidden')):
	/**
	 * Generate widget hidden
	 *
	 * @since 2.1.0
	 **/
	function reyajaxfilter_widget__option_hidden( $widget, $instance = [], $args = [] )
	{
		$attributes = [];

		$value = $args['value'];

		if( isset( $instance[$args['name']] ) ){
			$value = $instance[$args['name']];
		}

		printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s">',
			$widget->get_field_id($args['name']),
			$widget->get_field_name($args['name']),
			esc_attr($value)
		);
	}
endif;


if(!function_exists('reyajaxfilter_show_active_tax__deprecated')):
	/**
	 * Fallback for Show active taxonomy option.
	 *
	 * @since 2.0.5
	 **/
	function reyajaxfilter_show_active_tax__deprecated()
	{
		return get_theme_mod('ajaxfilters__hide_active_tax_filter', false);
		// return false;
	}
endif;
