<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('ReyCore_WooCommerce_Search')):
	/**
	 * Rey Search.
	 *
	 * @since   1.0.0
	 */

	class ReyCore_WooCommerce_Search {

		private static $_instance = null;

		const REST_SEARCH = '/product_search';

		private function __construct()
		{
			add_action('init', [$this, 'init']);
			add_action('reycore/customizer/init', [$this, 'add_search_types_option'], 11);
			add_action( 'wp_ajax_reycore_ajax_search', [$this, 'ajax_search']);
			add_action( 'wp_ajax_nopriv_reycore_ajax_search', [$this, 'ajax_search']);
			add_filter( 'wcml_multi_currency_ajax_actions', [$this, 'multi_currency_ajax_actions'] );
		}

		function init(){

			// return if search is disabled
			if( ! get_theme_mod('header_enable_search', true) ) {
				return;
			}

			add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
			add_action( 'rey/search_form', [ $this, 'search_form' ], 10);
			add_action( 'reycore/search_panel/after_search_form', [ $this, 'results_html' ], 10);
			add_filter( 'reycore/cover/get_cover', [$this, 'search_page_cover'], 40);
			add_filter( 'theme_mod_header_position', [$this, 'search_page_header_position'], 40);
			add_filter( 'rey_acf_option_header_position', [$this, 'search_page_header_position'], 40);
			add_filter( 'acf/load_value', [$this, 'search_page_reset_header_text_color'], 10, 3);
			add_filter( 'posts_clauses', [$this, 'product_search_sku'], 11, 2);
			add_filter( 'posts_where', [$this, '__search_where']);
			add_filter( 'posts_join', [$this, '__search_join']);
			add_filter( 'posts_groupby', [$this, '__search_groupby']);

		}

		function add_search_types_option() {

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'repeater',
				'settings'    => 'search_supported_post_types_list',
				'label'       => esc_html__('Post Type list in Search form', 'rey-core'),
				'description' => __('Choose multiple post types to add to the search form select list. List will display if more than one is selected', 'rey-core'),
				'section'     => 'header_search_options',
				'row_label' => [
					'type' => 'field',
					'value' => esc_html__('Post Type', 'rey-core'),
					'field' => 'post_type',
				],
				'button_label' => esc_html__('New Post Type', 'rey-core'),
				'default'      => [
					[
						'post_type' => 'product',
						'title' => 'SHOP'
					],
				],
				'fields' => [
					'post_type' => [
						'type'        => 'select',
						'label'       => esc_html__('Post Type', 'rey-core'),
						'choices'     => [
							'' => '-- Select --'
						] + reycore__get_post_types_list(),
					],
					'title' => [
						'type'        => 'text',
						'label'       => esc_html__('Title', 'rey-core'),
					]
				],
			] );

		}

		/**
		 * Filter main script's params
		 *
		 * @since 1.0.0
		 **/
		public function script_params($params)
		{
			$params['rest_search_url'] = self::REST_SEARCH;
			$params['search_texts'] = [
				'NO_RESULTS' => esc_html__('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'rey-core'),
			];
			$params['ajax_search_only_title'] = false;
			$params['ajax_search'] = get_theme_mod('header_enable_ajax_search', true);


			return $params;
		}

		function ajax_search(){

			// return if search is disabled
			if( ! get_theme_mod('header_enable_search', true) ) {
				wp_send_json_error();
			}

			$search_string = reycore__clean( $_REQUEST['s'] );

			if( empty($search_string) ) {
				wp_send_json_error( esc_html__('Empty string!', 'rey-core') );
			}

			wc_set_loop_prop( 'is_search', true );

			$args = array_merge(
				WC()->query->get_catalog_ordering_args('relevance'),
				[
					's'             => $search_string,
					'cache_results' => true,
					'post_type'     => isset($_REQUEST['post_type']) ? reycore__clean( $_REQUEST['post_type'] ) : ''
				]
			);

			if( defined('WP_DEBUG') && WP_DEBUG ){
				$args['cache_results'] = false;
			}

			if( isset($_REQUEST['product_cat']) && $product_cat = reycore__clean($_REQUEST['product_cat']) ){
				$args['product_cat'] = $product_cat;
			}

			/**
			 * Todo:
			 * deprecate filter;
			 */
			$args = apply_filters('reycore/woocommerce/search/rest_args', $args );

			$results = $this->json_results( $this->search_products_query( $args ) );

			wp_send_json_success( $results );

		}


		/**
		 * Query
		 *
		 * @since   1.0.0
		 */
		public function search_products_query( $args = [] )
		{
			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$args = apply_filters('reycore/woocommerce/search/ajax_args', wp_parse_args( $args, [
						'post_type'           => 'product',
						'post_status'         => 'publish',
						's'                   => '',
						'paged'               => 0,
						'orderby'             => 'relevance',
						'order'               => 'asc',
						'posts_per_page'      => 5,
						'cache_results'       => false,
						'cache_timeout'       => 4,
					] )
			);

			if ( 'product' === $args['post_type'] ) {

				$args['tax_query'][] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => ['exclude-from-catalog', 'exclude-from-search'],
					'operator' => 'NOT IN',
				];

				if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
					$args['meta_query'][] = [
						'key'     => '_stock_status',
						'value'   => 'outofstock',
						'compare' => 'NOT LIKE',
					];
				}

				if( isset($args['product_cat']) && $cat = $args['product_cat'] ){
					$args['tax_query'][] = [
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $cat,
						'operator' => 'IN',
					];
				}
			}

			set_query_var('rey_search', true );
			set_query_var('search_terms', explode(' ', $args['s']) );

			// add_action( 'parse_query', function($query){
			// 	$query->is_search = true;
			// } );

			if ( true === $args['cache_results'] ) {
				$dynamic_key    = md5( sanitize_text_field( $args['s'] ) );
				$transient_name = "rey-wc-search-results_{$dynamic_key}";
				$timeout        = absint( sanitize_text_field( $args['cache_timeout'] ) );
				if ( false === ( $the_query = get_transient( $transient_name ) ) ) {
					$the_query = new WP_Query( $args );
					set_transient( $transient_name, $the_query, $timeout * HOUR_IN_SECONDS );
				}
			} else {
				$the_query = new WP_Query( $args );
			}

			do_action('reycore/woocommerce/search/search_products_query', $the_query);

			return $the_query;
		}


		/**
		 * Gets the default search value linking to a search page with an "s" query string
		 * @since   1.0.0
		 */
		protected function get_default_search_value( \WP_Query $query, $result ) {
			$search_permalink = add_query_arg( array(
				's'         => sanitize_text_field( $query->query_vars['s'] ),
				'post_type' => 'product',
			), get_home_url() );

			$result['items'][] = array(
				'default'   => true,
				'id'        => get_the_ID(),
				'text'      => sprintf( esc_html__('View all results (%d)', 'rey-core'), $query->found_posts ),
				'permalink' => $search_permalink,
			);

			return $result;
		}

		/**
		 * Converts a wp_query result in a select 2 format result
		 * @since   1.0.0
		 */
		public function json_results( \WP_Query $query, $args = [] )
		{
			$result = array(
				'items' => array(),
				'total_count' => 0
			);

			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					add_filter('reycore/woocommerce/loop/prevent_product_items_slideshow', '__return_true');

					$query->the_post();

					do_action('reycore/woocommerce/search/before_get_data');

					$result['items'][] = [
						'id'        => get_the_ID(),
						'text'      => get_the_title(),
						'permalink' => get_permalink( get_the_ID() ),
						'img'       => get_the_post_thumbnail( get_the_ID(), 'shop_catalog' ),
						'price'     => apply_filters('reycore/woocommerce/ajax_search/price', $this->get_product_price() ),
					];

				}

				$result['total_count']    = $query->found_posts;
				$result['posts_per_page'] = $query->query_vars['posts_per_page'];

				wp_reset_postdata();

				if ( $result['total_count'] > $result['posts_per_page']) {
					$result = $this->get_default_search_value( $query, $result );
				}
			}

			return $result;
		}

		public function get_product_price() {

			if( $product = wc_get_product() ) {
				return $product->get_price_html();
			}

			return '';
		}

		/**
		 * Make search form product type
		 * @since 1.0.0
		 **/
		public function search_form(){

			if( apply_filters('reycore/woocommerce/search/prevent_post_type', false) ){
				return;
			}

			reyCoreAssets()->add_scripts(['reycore-wc-header-ajax-search', 'imagesloaded', 'wp-util']);

			$post_type_field = '<input type="hidden" name="post_type" value="product" />';

			if( get_theme_mod('header_enable_categories', false) ){

				$cat_list_text = esc_html__('Category', 'woocommerce');

				printf('<label class="rey-searchForm-list rey-searchForm-cats"><span>%1$s</span>', $cat_list_text);

				wc_product_dropdown_categories( apply_filters( 'reycore/search/categories_list_args', [
					'hierarchical'       => true,
					'show_uncategorized' => 0,
					'show_count'         => true,
					'show_option_none'  => $cat_list_text,
					'class'         => 'rey-searchForm-catList',
				] ) );

				echo '</label>';

			}

			if( $post_types = get_theme_mod('search_supported_post_types_list', []) ){

				if( count($post_types) === 1 && !empty($post_types[0]['post_type']) ){
					$post_type_field = sprintf('<input type="hidden" name="post_type" value="%s" />', $post_types[0]['post_type']);
				}

				else {

					$plist = reycore__get_post_types_list();
					$options = '';
					$first = '';

					foreach ($post_types as $key => $value) {
						$post_type = $value['post_type'] ? $value['post_type'] : 'post';
						$title = isset($value['title']) && !empty($value['title']) ? $value['title'] : ( isset($plist[$post_type]) ? $plist[$post_type] : '' );
						$options .= sprintf('<option value="%1$s">%2$s</option>', esc_attr($post_type), $title );
						if( $key === 0 ){
							$first = $title;
						}
					}

					printf('<label class="rey-searchForm-list rey-searchForm-postType"><span>%2$s</span><select name="post_type" >%1$s</select></label>', $options, $first);
					return;
				}
			}

			echo $post_type_field;
		}

		/**
		 * Adds markup for Ajax Search's results
		 *
		 * @since 1.0.0
		 */
		function results_html()
		{
			$classes = [];

			if( class_exists('ReyCore_WooCommerce_Loop') && ReyCore_WooCommerce_Loop::getInstance()->is_custom_image_height() ) {
				$classes[] = '--customImageContainerHeight';
			} ?>

			<div class="rey-searchResults js-rey-searchResults <?php echo esc_attr( implode(' ', $classes) ) ?>"></div>
			<div class="rey-lineLoader"></div>
			<?php

			add_action( 'wp_footer', [ $this, 'search_template' ], 10);
		}

		/**
		 * Search template used for search drop panel.
		 * @since 1.0.0
		 */
		function search_template(){
			reycore__get_template_part('template-parts/woocommerce/search-panel-results');
		}

		function product_search_sku( $args, $wp_query ){

			if( ! apply_filters('reycore/search/enable_sku', true) ){
				return $args;
			}

			$where = $args['where'];

			global $pagenow, $wpdb;

			if (
				( ! wp_doing_ajax() && is_admin() && 'edit.php' != $pagenow ) ||
				! ($wp_query->is_search || get_query_var('rey_search') ) ||
				! isset($wp_query->query_vars['s'])
			) {
				return $args;
			}

			if( isset($wp_query->query_vars['post_type']) && ($post_type = $wp_query->query_vars['post_type']) ){

				if( is_array($post_type) ){
					$is_product = in_array('product', $post_type, true);
				}
				else {
					$is_product = 'product' == $post_type;
				}

				if( ! $is_product ){
					return $args;
				}
			}

			$search_ids = [];
			$terms = explode(',', $wp_query->query_vars['s']);

			foreach ($terms as $term) {

				$term = trim($term);

				//Include search by id if admin area.
				if (is_admin() && is_numeric($term)) {
					$search_ids[] = $term;
				}

				// search variations with a matching sku and return the parent.
				$sku_to_parent_id = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT p.post_parent as post_id FROM {$wpdb->posts} as p join {$wpdb->postmeta} pm on p.ID = pm.post_id and pm.meta_key='_sku' and pm.meta_value LIKE '%%%s%%' where p.post_parent <> 0 group by p.post_parent",
						wc_clean($term)
					)
				);

				//Search a regular product that matches the sku.
				$sku_to_id = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE '%%%s%%';",
						wc_clean($term)
					)
				);

				$search_ids = array_merge($search_ids, $sku_to_id, $sku_to_parent_id);

			}

			$search_ids = array_unique(array_filter(array_map('absint', $search_ids)));

			if (!empty($search_ids)) {
				$where = str_replace('))', ") OR ({$wpdb->posts}.ID IN (" . implode(',', $search_ids) . ")))", $where);
			}

			$args['where'] = $where;

			reycore__remove_filters_for_anonymous_class('posts_search', 'WC_Admin_Post_Types', 'product_search', 10);

			return $args;
		}

		function search_includes(){
			return apply_filters('reycore/woocommerce/search_taxonomies', get_theme_mod('search__include', []));
		}

		function __search_where($where){
			global $wpdb, $wp_query;

			if( empty($this->search_includes()) ){
				return $where;
			}

			if ( ! (is_search() || get_query_var('rey_search')) ) {
				return $where;
			}

			$search_terms = get_query_var( 'search_terms' );

			if( empty($search_terms) ){
				return $where;
			}

			$where .= " OR (";
			$i = 0;
			foreach ($search_terms as $search_term) {
				$i++;
				if ($i>1) $where .= " OR";
				// if ($i>1) $where .= " AND"; // OR if you prefer requiring all search terms to match taxonomies (not really recommended)
				$where .= $wpdb->prepare( ' (t.name LIKE %s)', '%' . $wpdb->esc_like( $search_term ) . '%' );
			}

			$where .= " )";

			return $where;
		}

		function __search_join($join){

			global $wpdb;

			if( empty($this->search_includes()) ){
				return $join;
			}

			if ( ! (is_search() || get_query_var('rey_search')) ) {
				return $join;
			}

			$includes = $this->search_includes();

			foreach ($includes as $key => $inc) {
				if( ! taxonomy_exists($inc) ){
					continue;
				}
				$on[] = sprintf("tt.taxonomy = '%s'", esc_sql($inc));
			}

			// build our final string
			$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';
			$join .= " LEFT JOIN {$wpdb->term_relationships} AS tr ON ({$wpdb->posts}.ID = tr.object_id) LEFT JOIN {$wpdb->term_taxonomy} AS tt ON ( " . $on . " AND tr.term_taxonomy_id = tt.term_taxonomy_id) LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id) ";

			return $join;
		}

		function __search_groupby($groupby){

			global $wpdb;

			if( empty($this->search_includes()) ){
				return $groupby;
			}

			// we need to group on post ID
			$groupby_id = "{$wpdb->posts}.ID";

			if( ! (is_search() || get_query_var('rey_search')) || strpos($groupby, $groupby_id) !== false) {
				return $groupby;
			}

			// groupby was empty, use ours
			if(!strlen(trim($groupby))) {
				return $groupby_id;
			}

			// wasn't empty, append ours
			return $groupby.", ".$groupby_id;
		}


		public function search_page_cover( $cover ){

			if( ! is_search() ){
				return $cover;
			}

			$search_cover = get_theme_mod('cover__search_page', 'no');

			if( $search_cover === 'no' ){
				return false;
			}

			return $search_cover;
		}

		public function search_page_header_position( $pos ){

			if( ! is_search() ){
				return $pos;
			}

			if( $search_header_pos = get_theme_mod('search__header_position', 'rel') ){
				return $search_header_pos;
			}

			return $pos;
		}

		public function search_page_reset_header_text_color( $value, $post_id, $field ){

			if( $field['name'] === 'header_text_color' && is_search() ){
				return '';
			}

			return $value;
		}

		function multi_currency_ajax_actions( $ajax_actions ) {
			$ajax_actions[] = 'reycore_ajax_search';
			return $ajax_actions;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_WooCommerce_Search
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	ReyCore_WooCommerce_Search::getInstance();
endif;
