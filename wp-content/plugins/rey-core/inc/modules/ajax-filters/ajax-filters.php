<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('WooCommerce') && !class_exists('ReyAjaxFilters') ) {

	class ReyAjaxFilters
	{
		/**
		 * A reference to an instance of this class.
		 *
		 * @var ReyAjaxFilters
		 */
		private static $_instance = null;

		public $pg_settings;
		public $is_filter_page;
		public $filters_are_supported;
		public $chosen_filters = [];

		const TRANSIENT_PRICE_RANGE = 'reyajaxfilter_price_range';

		/**
		 * Initialize the plugin.
		 */
		public function __construct()
		{
			$this->define_constants();
			$this->includes();

			add_action( 'pre_get_posts', [ $this, 'pre_get_posts'], 9);
			add_filter( 'reycore/ajaxfilters/chosen_filters', [$this, 'set_tax_filters'], 10);
			add_action( 'woocommerce_product_query', [ $this, 'woocommerce_product_query'], 10);
			add_action( 'woocommerce_product_query', [ $this, 'woocommerce_product_query__fix_out_of_stock'], 1000);
			add_action( 'wp', [$this, 'run_hooks']);
			add_filter( 'reycore/ajaxfilters/widgets_support', [$this, 'check_widgets_support'], 10);
			add_action( 'elementor/element/reycore-menu/section_settings/before_section_end', [ $this, 'elementor_menu_cat_skin_option' ], 20 );
			add_action( 'wp_ajax_reyajaxfilter_get_applied_products', [ $this, 'get_applied_products' ] );
			add_action( 'wp_ajax_nopriv_reyajaxfilter_get_applied_products', [ $this, 'get_applied_products' ] );

			// add_action('admin_notices', function (){
			// 	if ( ($current = get_current_screen()) && $current->id === 'widgets' && $current->parent_base === 'themes' ) {
			// 		printf(
			// 			__('<div class="notice notice-info is-dismissible"><p>To learn more how to add or customize Rey\'s filter widgets, please visit <a href="%s" target="_blank">Rey\'s Documentation</a>.</p></div>', 'rey-core'),
			// 			'https://support.reytheme.com/kb/add-filters-in-woocommerce/'
			// 		);
			// 	}
			// });
		}

		/**
		 * Returns an instance of this class.
		 *
		 * @return ReyAjaxFilters
		 */
		public static function instance()
		{
			if (!isset(self::$_instance)) {
				self::$_instance = new ReyAjaxFilters();
			}

			return self::$_instance;
		}

		/**
		 * Define constants if not already defined.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		public function define($name, $value)
		{
			if (!defined($name)) {
				define($name, $value);
			}
		}

		/**
		 * Defind constants for this plugin.
		 */
		public function define_constants()
		{
			$this->define('REYAJAXFILTERS_DIR', REY_CORE_MODULE_DIR . basename(__DIR__));
			$this->define('REYAJAXFILTERS_PATH', REY_CORE_MODULE_URI . basename(__DIR__));
			$this->define('REYAJAXFILTERS_CACHE_TIME', 60 * 60 * 12 );
		}

		/**
		 * Include required core files.
		 */
		public function includes()
		{
			require_once REYAJAXFILTERS_DIR . '/includes/functions.php';
			require_once REYAJAXFILTERS_DIR . '/includes/options.php';
			require_once REYAJAXFILTERS_DIR . '/includes/migration.php';
			require_once REYAJAXFILTERS_DIR . '/includes/elementor-products-grid.php';

			$widgets = $this->widgets_list();

			foreach( $widgets as $widget ){
				require_once REYAJAXFILTERS_DIR . "/widgets/{$widget}.php";
			}
		}

		public function pre_get_posts( $query ){

			if( isset( $_REQUEST['wc-ajax'] ) && $_REQUEST['wc-ajax'] === 'get_refreshed_fragments' ){
				return;
			}

			if( isset( $_REQUEST['elementor-preview'] ) && $_REQUEST['elementor-preview'] !== '' ){
				return;
			}

			if( is_admin() ){
				return;
			}

			if( ! $query->is_main_query() ){
				return;
			}

			if( apply_filters('reycore/ajaxfilters/prevent_support', false) ){
				return;
			}

			// there must be at least one filter widget in one sidebar
			if( ! (class_exists('ReyCore_WooCommerce_Base') && (reycore_wc__check_filter_panel() || reycore_wc__check_filter_sidebar_top() || $this->check_shop_sidebar_for_filters())) ){
				return;
			}

			// product post types
			if( $query->is_post_type_archive('product') || $query->is_tax(get_object_taxonomies('product')) || $this->is_shop_frontpage__pre_get_posts( $query ) ){
				$this->filters_are_supported = true;
			}

			// pages
			elseif ( $query->is_page() ){

				// checks if this Elementor page has a Product grid element
				// and page filters option is enabled.
				$this->is_filter_page =
					( $queried_id = $query->get_queried_object_id() ) &&
					class_exists('\\Elementor\\Plugin') && \Elementor\Plugin::$instance->db->is_built_with_elementor( $queried_id ) &&
					($this->pg_settings = get_post_meta( $queried_id, ReyAjaxFilters_ProductGrid::META_KEY, true )) &&
					$this->pg_settings !== '' &&
					isset($this->pg_settings['page_uses_filters']) && 'yes' === $this->pg_settings['page_uses_filters'];

				if( $this->is_filter_page ){
					$this->filters_are_supported = true;
				}
			}

			do_action('reycore/ajaxfilters/pre_get_posts');
		}

		/**
		 * Tell widgets wether to show or not
		 *
		 * @since 1.5.4
		 */
		function check_widgets_support(){
			return $this->filters_are_supported;
		}

		function show_widgets_notice_for_pages(){

			// It shows up on any Elementor page containing PG
			return;

			if( current_user_can('administrator') && is_page() ){
				$msg = sprintf(
					__('You\'re probably trying to use Filter widgets into a page. Please access the %1$s element\'s options and enable the Filter option eg: <a href="%2$s" target="_blank">%2$s</a>.', 'rey-core'),
					esc_html__('Product Grid', 'rey-core'),
					'https://d.pr/i/reqYT1'
				);
				echo "<p><small>{$msg}</small></p>";
			}
		}

		function run_hooks(){
			add_action( 'woocommerce_before_shop_loop', [$this, 'beforeProductsHolder'], 0);
			add_action( 'woocommerce_after_shop_loop', [$this, 'afterProductsHolder'], 200);
			add_action( 'woocommerce_before_template_part', [$this, 'beforeNoProducts'], 0);
			add_action( 'woocommerce_after_template_part', [$this, 'afterNoProducts'], 200);
			add_filter( 'paginate_links', [$this, 'paginateLinks']);
			add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
			add_filter( 'reycore/woocommerce/get_active_filters', [$this, 'get_active_filters']);
			add_filter( 'reycore/woocommerce/brands/url', [$this, 'brand_url'], 10, 3);
			add_filter( 'reycore/elementor/menu/product_categories_skin/render_menu', [$this, 'elementor_menu_cat_skin_html'], 10, 4);
			add_action( 'reycore/elementor/product_grid/show_header', [ $this, 'elementor_product_grid_add_scripts' ] );
			add_action( 'reycore/filters_sidebar/before_panel', [ $this, 'apply_button_filter_panel_sidebar' ] );
			add_action( 'dynamic_sidebar_after', [ $this, 'apply_button_filter_general_sidebar' ], 200 );
			add_action( 'reycore/loop_products/before_header_end', [$this, 'active_filters_top'] );
			add_filter( 'rey/woocommerce/loop/header_classes', [$this, 'active_filters_top_classes']);
		}

		public function widgets_list(){
			return [
				'active-filters',
				'attribute-filter',
				'category-filter',
				'featured-filter',
				'price-filter',
				'sale-filter',
				'search-filter',
				'stock-filter',
				'tag-filter',
				'taxonomy-filter',
				'custom-fields-filter',
			];
		}

		public function register_assets(){

			$styles[ 'reyajfilter-style' ] = [
				'src'     => REYAJAXFILTERS_PATH . '/assets/css/styles'. reyCoreAssets()::rtl() .'.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			];

			$styles[ 'nouislider' ] = [
				'src'     => REYAJAXFILTERS_PATH . '/assets/css/nouislider.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			];

			$styles[ 'reyajfilter-dropdown' ] = [
				'src'     => REYAJAXFILTERS_PATH . '/assets/css/drop-down.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			];

			$styles[ 'reyajfilter-droppanel' ] = [
				'src'     => REYAJAXFILTERS_PATH . '/assets/css/drop-panel.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			];

			$styles[ 'reyajfilter-select2' ] = [
				'src'     => REYAJAXFILTERS_PATH . '/assets/css/select2.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			];

			reyCoreAssets()->register_asset('styles', $styles);

			$scripts = [

				'reyajfilter-script' => [
					'src'     => REYAJAXFILTERS_PATH . '/assets/js/scripts.js',
					'deps'    => ['jquery', 'rey-script', 'reycore-scripts'],
					'version'   => REY_CORE_VERSION,
					'localize' => [
						'name' => 'reyajaxfilter_params',
						'params' => apply_filters('reycore/ajaxfilters/js_params', [
							'shop_loop_container'  => '.rey-siteMain .reyajfilter-before-products',
							'not_found_container'  => '.rey-siteMain .reyajfilter-before-products',
							'pagination_container' => '.woocommerce-pagination',
							'animation_type'       => get_theme_mod('ajaxfilter_animation_type', 'default'),
							'sorting_control'      => get_theme_mod('ajaxfilter_product_sorting', true),
							'scroll_to_top'        => get_theme_mod('ajaxfilter_scroll_to_top', true),
							'scroll_to_top_offset' => get_theme_mod('ajaxfilter_scroll_to_top_offset', 100),
							'scroll_to_top_from' => get_theme_mod('ajaxfilter_scroll_to_top_from', 'grid'),
							'apply_filter_fixed'   => true,
							'dd_search_threshold'  => 5,
							'prevent_mobile_popstate'   => true,
							'page_url' => reycore__page_url(),
							'minimal_tpl' => apply_filters('reycore/woocommerce/products/minimal_tpl', true),
							'slider_margin' => 10,
							'slider_step' => 1,
							'apply_live_results' => get_theme_mod('ajaxfilter_apply_filter_live', false)
						]),
					],
				],

				'nouislider' => [
					'src'     => REYAJAXFILTERS_PATH . '/assets/js/nouislider.min.js',
					'deps'    => ['jquery', 'reyajfilter-script'],
					'version'   => '13.0.0',
				],

				'reyajfilter-select2' => [
					'src'     => REYAJAXFILTERS_PATH . '/assets/js/select2.min.js',
					'deps'    => ['jquery', 'reyajfilter-script'],
					'version'   => '4.0.13',
				],

				'reyajfilter-select2-multi-checkboxes' => [
					'src'     => REYAJAXFILTERS_PATH . '/assets/js/select2-multi-checkboxes.js',
					'deps'    => ['reyajfilter-select2'],
					'version'   => '1.0.0',
				],

				'reyajfilter-droppanel' => [
					'src'     => REYAJAXFILTERS_PATH . '/assets/js/drop-panel.js',
					'deps'    => ['jquery', 'reyajfilter-script'],
					'version'   => '1.0.0',
				],

			];

			reyCoreAssets()->register_asset('scripts', $scripts);

		}

		public static function load_scripts(){
			reyCoreAssets()->add_scripts('reyajfilter-script');
			reyCoreAssets()->add_styles('reyajfilter-style');
		}

		protected function is_shop_frontpage__pre_get_posts( $query ){

			$front_page_id        = get_option( 'page_on_front' );
			$current_page_id      = $query->get( 'page_id' );
			$shop_page_id         = apply_filters( 'woocommerce_get_shop_page_id' , get_option( 'woocommerce_shop_page_id' ) );

			if( isset( $_GET['orderby'] ) ){
				$current_page_id = url_to_postid((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			}

			// Detect if it's a static front page and the current page is the front page, then use our work around
			if ( ('page' == get_option( 'show_on_front' )) && $front_page_id == $current_page_id  ) {
				return ( $current_page_id == $shop_page_id ) ? true : false;
			}
			else {
				if( is_404() || ! get_queried_object() ){
					return false;
				}
				return is_shop();
			}
		}

		/**
		 * Set chosen filters.
		 *
		 * @since 1.5.4
		 */
		public function set_chosen_filters( $url_query = [] )
		{
			if( !empty($url_query) ) {
				$query = $url_query;
			}
			else {
				$url = $_SERVER['QUERY_STRING'];
				parse_str($url, $query);
			}

			$query = apply_filters('reycore/ajaxfilters/query_url', $query);
			$is_product_grid = isset($query['pg']) && $query['pg'];

			$chosen = [];
			$active_filters = [];

			// keyword
			if (isset($_GET['keyword'])) {
				$keyword = (!empty($_GET['keyword'])) ? $_GET['keyword'] : '';
				$active_filters['keyword'] = $keyword;
			}

			// orderby
			if (isset($_GET['orderby'])) {
				$orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : '';
				$active_filters['orderby'] = $orderby;
			}

			// Parse From URL

			/**
			 * Product Category
			 */

			// it's filtering a category
			if ( (($category_filters = reycore__preg_grep_keys('/product-cat/', $query)) && !empty($category_filters)) ) {

				$category_filters_keys = [
					'product-cata' => 'and',
					'product-cato' => 'or',
				];

				$category_filters_key = array_keys( array_intersect_key($category_filters_keys, $category_filters ) )[0];
				$category_filters_query_type = $category_filters_keys[$category_filters_key];

				if( ! empty($category_filters[$category_filters_key]) ) {

					$category_terms = explode(',', $category_filters[ $category_filters_key ]);
					$category_taxonomy = 'product_cat';

					$chosen[$category_taxonomy] = [
						'terms'      => $category_terms,
						'query_type' => $category_filters_query_type
					];

					foreach ($category_terms as $term_id) {
						$active_filters['term'][$category_filters_key][$term_id] = $category_taxonomy;
					}
				}
			}


			/**
			 * Product Tag
			 */
			if ( ($tag_filters = reycore__preg_grep_keys('/product-tag/', $query)) && !empty($tag_filters)) {

				$tag_filters_keys = [
					'product-taga' => 'and',
					'product-tago' => 'or',
				];

				$tag_filters_key = array_keys( array_intersect_key($tag_filters_keys, $tag_filters ) )[0];
				$tag_filters_query_type = $tag_filters_keys[$tag_filters_key];

				if( ! empty($tag_filters[$tag_filters_key]) ) {

					$tag_terms = explode(',', $tag_filters[ $tag_filters_key ]);
					$tag_taxonomy = 'product_tag';

					$chosen[$tag_taxonomy] = array(
						'terms'      => $tag_terms,
						'query_type' => $tag_filters_query_type
					);

					foreach ($tag_terms as $term_id) {
						$active_filters['term'][$tag_filters_key][$term_id] = $tag_taxonomy;
					}
				}
			}

			/**
			 * Product Attributes
			 */
			if ( ($attribute_filters = reycore__preg_grep_keys('/^attr/', $query)) && !empty($attribute_filters)) {

				$attribute_filters_keys = [
					'attra' => 'and',
					'attro' => 'or',
				];

				foreach ($attribute_filters as $akey => $avalue) {

					if( $avalue === '' ){
						continue;
					}

					$attribute_taxonomy_slug = '';
					$attribute_filters_query_type = 'and';

					foreach ($attribute_filters_keys as $k => $v) {
						if( strpos($akey, $k ) !== false ){
							$attribute_filters_query_type = $v;
							$attribute_taxonomy_slug = str_replace( $k . '-', '', $akey );
						}
					}

					if( !empty($attribute_taxonomy_slug) ){

						$attribute_terms = explode(',', $avalue);

						$attribute_taxonomy_slug_clean = wc_sanitize_taxonomy_name ( $attribute_taxonomy_slug );
						$attribute_taxonomy = $attribute_taxonomy_slug_clean;

						if( strpos($attribute_taxonomy_slug_clean, 'pa_') === false ){
							$attribute_taxonomy = wc_attribute_taxonomy_name($attribute_taxonomy_slug_clean);
						}

						$chosen[$attribute_taxonomy] = array(
							'terms'      => $attribute_terms,
							'query_type' => $attribute_filters_query_type
						);

						foreach ($attribute_terms as $term_id) {
							$active_filters['term'][$akey][$term_id] = $attribute_taxonomy;
						}
					}
				}
			}

			/**
			 * Range Attributes
			 */

			if ( ($range_attribute_filters = reycore__preg_grep_keys('/^max-range-/', $query)) && !empty($range_attribute_filters)) {
				foreach ($range_attribute_filters as $akey => $term) {
					if( strpos($akey, 'max-range' ) !== false ){
						$attribute_taxonomy_slug = wc_sanitize_taxonomy_name ( str_replace( 'max-range-', '', $akey ) );
						$attribute_taxonomy = $attribute_taxonomy_slug;

						if( strpos($attribute_taxonomy_slug, 'pa_') === false ){
							$attribute_taxonomy = wc_attribute_taxonomy_name($attribute_taxonomy_slug);
						}

						$chosen[$attribute_taxonomy]['range_max'] = floatval($term);
						$active_filters['range_max'][$attribute_taxonomy] = $term;
					}
				}
			}

			if ( ($range_attribute_filters = reycore__preg_grep_keys('/^min-range-/', $query)) && !empty($range_attribute_filters)) {
				foreach ($range_attribute_filters as $akey => $term) {
					if( strpos($akey, 'min-range' ) !== false ){
						$attribute_taxonomy_slug = wc_sanitize_taxonomy_name ( str_replace( 'min-range-', '', $akey ) );
						$attribute_taxonomy = $attribute_taxonomy_slug;

						if( strpos($attribute_taxonomy_slug, 'pa_') === false ){
							$attribute_taxonomy = wc_attribute_taxonomy_name($attribute_taxonomy_slug);
						}

						$chosen[$attribute_taxonomy]['range_min'] = floatval($term);
						$active_filters['range_min'][$attribute_taxonomy] = $term;
					}
				}
			}

			// Custom taxonomies
			$custom_taxonomies = $this->get_registered_taxonomies();

			foreach ($custom_taxonomies as $taxonomy) {

				$tid = str_replace('-', '', sanitize_title( $taxonomy['name'] ));

				if ( ($ctax_filters = reycore__preg_grep_keys("/product-{$tid}/", $query)) && !empty($ctax_filters)) {

					$ctax_filters_keys = [
						"product-{$tid}a" => "and",
						"product-{$tid}o" => "or",
					];

					$ctax_filters_key = array_keys( array_intersect_key($ctax_filters_keys, $ctax_filters ) )[0];
					$ctax_filters_query_type = $ctax_filters_keys[$ctax_filters_key];

					$ctax_terms = explode(',', $ctax_filters[ $ctax_filters_key ]);
					$ctax_taxonomy = $taxonomy['id'];

					$chosen[$ctax_taxonomy] = array(
						'terms'      => $ctax_terms,
						'query_type' => $ctax_filters_query_type
					);

					foreach ($ctax_terms as $term_id) {
						$active_filters['term'][$ctax_filters_key][$term_id] = $ctax_taxonomy;
					}
				}
			}

			// min-price
			if (isset($_GET['min-price'])) {
				$active_filters['min_price'] = $_GET['min-price'];
			}

			// max-price
			if (isset($_GET['max-price'])) {
				$active_filters['max_price'] = $_GET['max-price'];
			}

			if (isset($_GET['in-stock']) ) {
				if( 0 === absint($_GET['in-stock']) ) {
					$active_filters['in-stock'] = 0;
				}
				else if( 1 === absint($_GET['in-stock']) ) {
					$active_filters['in-stock'] = 1;
				}
				else if( 2 === absint($_GET['in-stock']) ) {
					$active_filters['in-stock'] = 2;
				}
			}

			if ( $this->queryIsOnSale() ) {
				$active_filters['on-sale'] = 1;
			}

			if ( $this->queryIsFeatured() ) {
				$active_filters['is-featured'] = 1;
			}

			// Product Meta
			if ( isset($query['product-meta']) && !empty($query['product-meta']) && $mq_hashes = reycore__clean($query['product-meta']) ) {
				$chosen['product-meta'] = explode(',', $mq_hashes);
				$active_filters['product-meta'] = $chosen['product-meta'];
			}

			if( !empty($chosen) ){
				add_filter('woocommerce_is_filtered', '__return_true');
			}

			$this->chosen_filters = wc_clean( apply_filters('reycore/ajaxfilters/chosen_filters', [
				'chosen'         => $chosen,
				'active_filters' => $active_filters
			]) );

		}

		function get_available_taxonomies(){

			$available_taxonomies = [
				'product_cat' => 'product-cato',
				'product_tag' => 'product-tago',
			];

			// attributes
			$wc_attributes = wc_get_attribute_taxonomies();
			$wc_attributes = wp_list_pluck($wc_attributes, 'attribute_name');
			foreach ($wc_attributes as $key => $attribute) {
				$wc_attribute_sanitized = wc_sanitize_taxonomy_name ( $attribute );
				$attribute_taxonomy = $wc_attribute_sanitized;

				if( strpos($wc_attribute_sanitized, 'pa_') === false ){
					$attribute_taxonomy = wc_attribute_taxonomy_name($wc_attribute_sanitized);
				}

				$available_taxonomies[ $attribute_taxonomy ] = 'attro-' . $wc_attribute_sanitized;
			}

			// custom taxonomies
			$custom_taxonomies = $this->get_registered_taxonomies();
			foreach ($custom_taxonomies as $taxonomy) {
				$custom_tax_sanitized = str_replace('-', '', sanitize_title( $taxonomy['name'] ));
				$available_taxonomies[ $custom_tax_sanitized ] = "product-{$custom_tax_sanitized}o";
			}

			return $available_taxonomies;
		}

		function set_tax_filters( $chosen_filters ){

			$is_current__tax = is_tax() ? get_queried_object() : false;

			if( !$is_current__tax ){
				return $chosen_filters;
			}

			$available_taxonomies = $this->get_available_taxonomies();

			foreach ($available_taxonomies as $tax_name => $filter_type) {
				if( isset($is_current__tax->taxonomy) && $is_current__tax->taxonomy === $tax_name ) {
					$chosen_filters['chosen'][$tax_name]['terms'][] = $is_current__tax->term_id;
					$chosen_filters['chosen'][$tax_name]['query_type'] = 'or';
					$chosen_filters['active_filters']['term'][$filter_type][ $is_current__tax->term_id ] = $tax_name;
				}
			}

			return $chosen_filters;
		}

		/**
		 * Filtered product ids for given terms.
		 *
		 * @return array
		 */
		public function queryForTax( $q = null )
		{
			$tax_query = [];

			if( empty($this->chosen_filters) ){
				return $tax_query;
			}

			$chosen_filters = $this->chosen_filters['chosen'];

			// 99% copy of WC_Query
			if (is_array($chosen_filters) && sizeof($chosen_filters) > 0) {

				global $wp_query;
				$main_query = $wp_query->is_main_query();

				$tax_query = [
					'relation' => 'AND',
				];

				foreach ( $chosen_filters as $taxonomy => $data ) {

					if( isset($data['terms']) && !empty($data['terms']) ){

						$tq = [
							'taxonomy'         => $taxonomy,
							'field'            => 'term_id',
							'terms'            => array_map('absint', array_unique($data['terms'])),
							'operator'         => 'and' === $data['query_type'] ? 'AND' : 'IN',
							'include_children' => false,
						];

						if( $taxonomy === 'product_cat' ){

							$tq['include_children'] = true;

							// Different scenarios when in categories
							if( is_product_category() && count($tq['terms']) > 1 ){

								$current_cat_id = get_queried_object_id();

								// This forces the main query to allow multiple categories,
								// including the current one (not just the current one)
								if( $q && empty( get_term_children( $current_cat_id , 'product_cat' ) ) ){
									unset($q->query_vars['product_cat']);
								}

								// exclude ancestors when in category
								else {

									$tq['terms'] = array_filter($tq['terms'], function($id) use ($current_cat_id){
										$anc = get_ancestors($id, 'product_cat');
										return ! empty($anc) && in_array( $current_cat_id, $anc, true );
									});
								}
							}

						}

						$tax_query[] = $tq;
					}

					else {

						if( !(isset($data['range_max']) || isset($data['range_min'])) ){
							continue;
						}

						$get_ranges_terms = get_terms([
							'taxonomy' => $taxonomy,
							'hide_empty' => true,
						] );

						$clean_range_terms = wp_list_pluck($get_ranges_terms, 'name', 'term_id');

						$__ranges = array_filter($clean_range_terms, function($item) use ($data) {

							$cond = [];

							if( isset($data['range_max']) && $max = $data['range_max'] ){
								$cond[] = floatval($item) <= floatval($max);
							}

							if( isset($data['range_min']) && $min = $data['range_min'] ){
								$cond[] = floatval($item) >= floatval($min);
							}

							return ! in_array(false, $cond, true);
						});

						if( empty($__ranges) ){
							continue;
						}

						$tax_query[] = [
							'taxonomy'         => $taxonomy,
							'field'            => 'term_id',
							'terms'            => array_keys($__ranges),
							'operator'         => 'IN',
							'include_children' => false,
						];
					}
				}

				$product_visibility_terms  = wc_get_product_visibility_term_ids();
				$product_visibility_not_in = array( is_search() && $main_query ? $product_visibility_terms['exclude-from-search'] : $product_visibility_terms['exclude-from-catalog'] );

				// Hide out of stock products.
				if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
					$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
				}

				if ( ! empty( $product_visibility_not_in ) ) {
					$tax_query[] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'term_taxonomy_id',
						'terms'    => $product_visibility_not_in,
						'operator' => 'NOT IN',
					);
				}

			}

			return array_filter( $tax_query );
		}

		public function queryForPostIn(){
			$post__in = [];

			if ( $this->queryIsOnSale() ) {
				$post__in = array_merge( $post__in, reyajaxfilters_get_sale_products() );
			}

			if ( $this->queryIsFeatured() ) {
				$post__in = array_merge( $post__in, wc_get_featured_product_ids() );
			}

			return $post__in;
		}

		public function queryIsOnSale(){
			return apply_filters('reycore/ajaxfilters/query/on_sale', isset($_GET['on-sale']) && 1 === absint($_GET['on-sale']) );
		}

		public function queryIsFeatured(){
			return apply_filters('reycore/ajaxfilters/query/featured', isset($_GET['is-featured']) && 1 === absint($_GET['is-featured']) );
		}

		/**
		 * OK
		 * Query for meta that should be set to the main query.
		 *
		 * @return array
		 */
		public function queryForMeta( $custom_args = [] )
		{
			$meta_query = [];

			$_req_args = $_REQUEST;

			if( ! empty($custom_args) ){
				$_req_args = $custom_args;
			}

			// rating filter
			if (isset($_req_args['min_rating'])) {
				$meta_query[] = [
					'key'           => '_wc_average_rating',
					'value'         => isset($_req_args['min_rating']) ? floatval($_req_args['min_rating']) : 0,
					'compare'       => '>=',
					'type'          => 'DECIMAL',
					'rating_filter' => true,
				];
			}

			if (isset($_req_args['in-stock']) ) {
				if (1 === absint($_req_args['in-stock']) ) {
					$meta_query[] = [
						'key'           => '_stock_status',
						'value'         => 'instock',
					];
				}
				else if (2 === absint($_req_args['in-stock']) ) {
					$meta_query[] = [
						'key'           => '_stock_status',
						'value'         => 'outofstock',
					];
				}
			}

			if (isset($_req_args['min-price']) || isset($_req_args['max-price'])) {

				// $price_range = $this->get_price_range();
				$step = max( apply_filters( 'woocommerce_price_filter_widget_step', 10 ), 1 );

				$min_price = (!empty($_req_args['min-price'])) ? absint($_req_args['min-price']) : 0;
				$max_price = (!empty($_req_args['max-price'])) ? absint($_req_args['max-price']) : 0;

				if( $min_price !== $max_price ) {

					// Check to see if we should add taxes to the prices if store are excl tax but display incl.
					$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

					if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === $tax_display_mode ) {
						$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
						$tax_rates = WC_Tax::get_rates( $tax_class );

						if ( $tax_rates ) {
							$min_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $min_price, $tax_rates ) );
							$max_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max_price, $tax_rates ) );
						}
					}

					$min_price = apply_filters( 'woocommerce_price_filter_widget_min_amount', floor( $min_price / $step ) * $step, $min_price, $step );
					$max_price = apply_filters( 'woocommerce_price_filter_widget_max_amount', ceil( $max_price / $step ) * $step, $max_price, $step );

					// get max price from range
					if( ! (bool) $max_price ){
						if( ($prices = self::get_prices_range(['avoid_recursiveness' => true])) && isset($prices['max_price']) ) {
							$max_price = ceil( floatval( wp_unslash( $prices['max_price'] ) ) / $step ) * $step;
						}
					}

					// the_prices_range
					$meta_query[] = [
						'key'          => '_price',
						'value'        => [ $min_price, $max_price ],
						'type'         => 'numeric',
						'compare'      => 'BETWEEN',
						'price_filter' => true,
					];

				}
			}

			if ( isset($_req_args['product-meta']) && !empty($_req_args['product-meta']) && $mq_hashes = reycore__clean($_req_args['product-meta']) ) {

				$active_hashes = explode(',', $mq_hashes);

				foreach ($active_hashes as $hash) {

					if( ($rmq = reyajaxfilter_get_registered_meta_query($hash)) && !empty($rmq) ){
						$meta_query['rey-product-meta'] = $rmq;
					}

				}
			}

			return apply_filters('reycore/ajaxfilters/products/meta_query', $meta_query);
		}

		/**
		 * Get filtered min price for current products.
		 *
		 * @return int
		 */
		public static function get_prices_range($args = []) {

			global $wpdb, $wp_query;

			$tax_query  = reyajaxfilter_tax_query();

			$meta_query_args = [];
			if( isset($args['avoid_recursiveness']) && $args['avoid_recursiveness']){
				$meta_query_args = [
					'surpress_filter' => true
				];
			}
			$meta_query  = reyajaxfilter_meta_query($meta_query_args);

			foreach ( $meta_query + $tax_query as $key => $query ) {
				if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
					unset( $meta_query[ $key ] );
				}
			}

			$meta_query = new WP_Meta_Query( $meta_query );
			$tax_query  = new WP_Tax_Query( $tax_query );
			$search = reyajaxfilter_search_query();

			$meta_query_sql   = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
			$tax_query_sql    = $tax_query->get_sql( $wpdb->posts, 'ID' );
			$search_query_sql = $search ? ' AND ' . $search : '';

			$sql = "
				SELECT min( min_price ) as min_price, MAX( max_price ) as max_price
				FROM {$wpdb->wc_product_meta_lookup}
				WHERE product_id IN (
					SELECT ID FROM {$wpdb->posts}
					" . $tax_query_sql['join'] . $meta_query_sql['join'] . "
					WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
					AND {$wpdb->posts}.post_status = 'publish'
					" . $tax_query_sql['where'] . $meta_query_sql['where'] . $search_query_sql . '
				)';

			$sql = apply_filters( 'woocommerce_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );

			return (array) $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
		}


		/**
		 * Retrive Product ids for given keyword.
		 *
		 * @return array
		 */
		public function queryForKeywordSearch()
		{
			if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
				return wc_clean( $_GET['keyword'] );
			}

			return '';
		}

		/**
		 * Set filter.
		 *
		 * @param wp_query $q
		 */
		public function woocommerce_product_query( $q )
		{
			if( ! $this->filters_are_supported ){
				return;
			}

			$this->set_chosen_filters();

			if( $meta_query = $this->queryForMeta() ){
				$q->set( 'meta_query', $meta_query );
			}

			if( $tax_query = $this->queryForTax($q) ){
				$q->set( 'tax_query', $tax_query );
			}

			if( $post__in = $this->queryForPostIn() ){
				$q->set('post__in', $post__in);
			}

			if( isset($this->chosen_filters['active_filters']['keyword']) && $keyword = reycore__clean($this->chosen_filters['active_filters']['keyword']) ){
				$q->set('s', $keyword);
			}

			/**
			 * Scenario: while in a custom taxonomy,
			 * make sure to show products of all selected tax terms (not just the active tax)
			 */
			if( $q->is_tax() && ($current_tax = $q->get_queried_object()) && isset($current_tax->taxonomy) ){
				if( ! in_array( $current_tax->taxonomy, apply_filters('reycore/ajaxfilters/tax_reset_query', [ 'product_cat' ]) , true ) ){
					$q->set($current_tax->taxonomy, '');
				}
			}

		}

		function queryForPostNotIn(){

			$p = [];

			if( $out_of_stock_variations = $this->get_out_of_stock_variations() ){
				$p = $out_of_stock_variations;
			}

			return apply_filters('reycore/ajaxfilters/query_posts_not_in', $p);
		}

		public function woocommerce_product_query__fix_out_of_stock( $q )
		{
			if( ! $this->filters_are_supported ){
				return;
			}

			if( ( $post__not_in = $this->queryForPostNotIn() ) && !empty($post__not_in) ){
				$q->set('post__not_in', $post__not_in);
			}
		}

		public function get_out_of_stock_variations( $results = [] ){

			global $wpdb;

			if( ! get_theme_mod('ajaxfilters_exclude_outofstock_variables', false) ){
				return $results;
			}

			$chosen_filters = $this->chosen_filters['chosen'];

			if( empty($chosen_filters) ){
				return $results;
			}

			$meta_keys = [];

			$query_args = [
				'post_type'     => 'product_variation',
				'meta_query'    => [
					'relation' => 'AND',
					[
						'relation' => 'OR',
						[
							'key'     => '_stock_status',
							'value'   => 'outofstock',
							'compare' => 'IN',
						],
						[
							'key'     => '_stock',
							'value'   => '0',
							'compare' => 'IN',
						],
					],
				],
				'fields'         => 'id=>parent',
				'posts_per_page' => 100,
				'groupby'        => 'post_parent',
			];

			$meta_query = [];

			foreach ($chosen_filters as $wc_tax => $value) {

				$taxonomy = $wc_tax;

				if( $wc_tax !== 'product_cat' ){
					$taxonomy = 'attribute_' . $wc_tax;
				}

				foreach ($value['terms'] as $term_id) {

					$term_object = get_term_by('id', $term_id, $wc_tax);

					if( isset($term_object->slug) ){
						$meta_query[] = [
								'key'     => $taxonomy,
								'value'   => $term_object->slug,
								'compare' => 'IN',
						];
					}
				}
			}

			if( !empty($meta_query) ){
				$meta_query['relation'] = 'OR';
				$query_args['meta_query'][] = $meta_query;
			}

			$q = new WP_Query( apply_filters('reycore/ajaxfilters/out_of_stock_variations/query_args', $query_args, $chosen_filters ) );

			if( empty($q->posts) ){
				return $results;
			}

			$results = wp_list_pluck($q->posts, 'post_parent');

			return $results;
		}

		/**
		 * HTML wrapper to insert before the shop loop.
		 *
		 * @return string
		 */
		public function beforeProductsHolder()
		{
			if( ! $this->filters_are_supported ){
				return;
			}

			$anim_type = get_theme_mod('ajaxfilter_animation_type', 'default');

			printf('<div class="reyajfilter-before-products --anim-%s">', $anim_type);

			if( $anim_type === 'default' ){
				echo '<div class="reyajfilter-updater"><div class="rey-lineLoader"></div></div>';
				self::load_scripts();
			}
		}

		/**
		 * HTML wrapper to insert after the shop loop.
		 *
		 * @return string
		 */
		public function afterProductsHolder()
		{
			if( ! $this->filters_are_supported ){
				return;
			}

			echo '</div>';
		}

		/**
		 * HTML wrapper to insert before the not found product loops.
		 *
		 * @param  string $template_name
		 * @param  string $template_path
		 * @param  string $located
		 * @return string
		 */
		public function beforeNoProducts($template_name = '', $template_path = '', $located = '') {
			if( ! $this->filters_are_supported ){
				return;
			}
		    if ($template_name == 'loop/no-products-found.php') {
		        $this->beforeProductsHolder();
		    }
		}

		/**
		 * HTML wrapper to insert after the not found product loops.
		 *
		 * @param  string $template_name
		 * @param  string $template_path
		 * @param  string $located
		 * @return string
		 */
		public function afterNoProducts($template_name = '', $template_path = '', $located = '') {
			if( ! $this->filters_are_supported ){
				return;
			}
		    if ($template_name == 'loop/no-products-found.php') {
		        echo '</div>';
		    }
		}



		/**
		 * Decode pagination links.
		 *
		 * @param string $link
		 *
		 * @return string
		 */
		public function paginateLinks($link)
		{
			if( ! $this->filters_are_supported ){
				return $link;
			}
			$link = urldecode($link);
			$link = str_replace('?reynotemplate=1', '', $link);
			$link = str_replace('&reynotemplate=1', '', $link);
			return $link;
		}

		/**
		 * Get active filters
		 *
		 * @since 1.0.0
		 */
		public function get_active_filters(){
			$total_filters = 0;

			if( empty($this->chosen_filters) ){
				return $total_filters;
			}

			$active_filters = $this->chosen_filters['active_filters'];

			if( ! apply_filters('reycore/ajaxfilters/active_filters/order_display', false) ){
				unset($active_filters['orderby']);
			}

			if( ! reyajaxfilter_show_active_tax__deprecated() && is_tax() && $current_tax = get_queried_object() ){
				if( isset($current_tax->term_id) && isset($active_filters['term']) ){
					foreach ($active_filters['term'] as $key => $term_group) {
						if( array_key_exists($current_tax->term_id, $term_group) ){
							if( count($term_group) === 1 ){
								unset($active_filters['term'][$key]);
							}
							else {
								unset($active_filters['term'][$key][$current_tax->term_id]);
							}

							if( empty($active_filters['term']) ){
								unset($active_filters['term']);
							}
						}
					}
				}
			}

			if( isset($active_filters['term']) && $active_filters['term'] > 1 ){
				$term_count = count($active_filters['term']);
				unset($active_filters['term']);
				return count($active_filters) + $term_count;
			}

			return count($active_filters);
		}

		function brand_url( $brand_url, $brand_attribute_name, $url ){

			if( ! $this->filters_are_supported ){
				return $brand_url;
			}

			$attribute_taxonomy = $brand_attribute_name;

			if( strpos($brand_attribute_name, 'pa_') === false ){
				$attribute_taxonomy = wc_attribute_taxonomy_name($brand_attribute_name);
			}

			$brand_id = wc_get_product_terms( get_the_ID(), $attribute_taxonomy, [ 'fields' => 'ids' ] );

			if( isset($brand_id[0]) ){
				$brand_url = $url . '?attro-'. $brand_attribute_name .'='. $brand_id[0];
			}

			return $brand_url;
		}

		function elementor_menu_cat_skin_option($element){

			$element->add_control(
				'reycore_ajaxify',
				[
					'label' => __( 'Use Ajax', 'rey-core' ),
					'description' => __( 'To enable this option, make sure there are filter widgets published. Please know it\'s only compatible with archive/category pages.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => '',
					'condition' => [
						'_skin' => 'product-categories',
					],
				]
			);
		}

		public function elementor_menu_cat_skin_html( $html, $cats, $settings, $element )
		{
			if( ! $this->filters_are_supported ){
				return $html;
			}

			if( !(isset($settings['reycore_ajaxify']) && $settings['reycore_ajaxify'] === 'yes') ){
				return $html;
			}

			reyCoreAssets()->add_scripts('reyajfilter-script');

			$html = sprintf('<ul class="reyEl-menu-nav rey-navEl reyajfilter-ajax-term-filter --menuHover-%2$s" id="reyajfilter-ajax-term-filter-%1$s">', $element->get_id(), $settings['hover_style']);

			if( $settings['all_button'] !== '' && $settings['pcat_type'] === 'all' ){
				$html .= sprintf(
					'<li class="menu-item %3$s"><a href="%2$s"><span>%1$s</span></a></li>',
					$settings['all_button_text'],
					get_permalink( wc_get_page_id('shop') ),
					is_shop() ? 'current-menu-item' : ''
				);
			}

			$current_qo = get_queried_object();

			foreach ($cats as $i => $cat) {

				$get_term_by = 'slug';

				if( in_array( $settings['pcat_type'], ['manual_order'], true ) ){
					$get_term_by = 'id';
				}

				$term = get_term_by($get_term_by, $i, 'product_cat' );
				$term_id = isset($term->term_id) ? $term->term_id : 0;

				$attributes = 'data-key="product-cato" data-taxonomy-type="product_cat" data-value="'. $term_id .'"';

				$is_active = isset($current_qo->$get_term_by) && $current_qo->$get_term_by === $i;

				if( $this->chosen_filters && isset($this->chosen_filters['chosen']['product_cat']['terms']) &&
					in_array($term_id, $this->chosen_filters['chosen']['product_cat']['terms'] )
				){
					$is_active = true;
				}

				$html .= sprintf(
					'<li class="menu-item %3$s"><a href="%2$s" %4$s><span>%1$s</span></a></li>',
					$cat,
					get_term_link( $i, 'product_cat' ),
					$is_active ? 'current-menu-item' : '',
					$attributes
				);
			}

			$html .= '</ul>';

			return $html;
		}

		function elementor_product_grid_add_scripts($elementor_edit_mode){

			if( ! $this->filters_are_supported ){
				return;
			}

			if( ! $elementor_edit_mode ) {
				self::load_scripts();
			}
		}

		function apply_button_filter_panel_sidebar(){

			if( ! $this->filters_are_supported ){
				return;
			}

			if(
				get_theme_mod('ajaxfilter_apply_filter', false) &&
				reycore_wc__check_filter_panel()
			){
				$this->print_apply_filters_button('filter-panel');
			}

			reyCoreAssets()->add_styles('reycore-side-panel');

		}

		function apply_button_filter_general_sidebar( $index ){

			if( ! $this->filters_are_supported ){
				return;
			}

			if(
				get_theme_mod('ajaxfilter_apply_filter', false) &&
				( ($index === 'filters-top-sidebar' && reycore_wc__check_filter_sidebar_top()) || ($index === 'shop-sidebar' && $this->check_shop_sidebar_for_filters()) )
			){
				$this->print_apply_filters_button($index);
			}
		}

		function print_apply_filters_button($sidebar_id){

			if(is_admin()){
				return;
			}

			$btn_style = 'btn-primary';
			if( $sidebar_id === 'filters-top-sidebar' ){
				$btn_style = 'btn-line-active';
			}

			printf(
				'<div class="rey-applyFilters-btn-wrapper" id="rey-applyFilters-btn-wrapper-%2$s"><button class="rey-applyFilters-btn js-rey-applyFilters-btn btn %3$s  --disabled"><span class="rey-applyFilters-btnText">%1$s</span><span class="rey-lineLoader"></span></button></div>',
				get_theme_mod('ajaxfilter_apply_filter_text', esc_html__('Apply Filters', 'rey-core')),
				$sidebar_id,
				$btn_style
			);

		}

		function check_shop_sidebar_for_filters() {

			if( ! is_active_sidebar('shop-sidebar') ) {
				return false;
			}

			$widgets = $this->widgets_list();

			foreach( $widgets as $widget ){
				if( ($sidebar = is_active_widget( false, false, 'reyajfilter-' . $widget )) && $sidebar === 'shop-sidebar' ){
					return true;
				}
			}

			return false;
		}

		public function get_registered_taxonomies(){
			return apply_filters('reycore/ajaxfilters/registered_taxonomies', get_theme_mod('ajaxfilters_taxonomies', []));
		}

		public function active_filters_top(){

			if( ! $this->filters_are_supported ){
				return;
			}

			if( get_theme_mod('ajaxfilter_active_position', '') === '' ){
				return;
			}

			the_widget( 'REYAJAXFILTERS_Active_Filters_Widget', array(
				'title' => '',
				'button_text' => get_theme_mod('ajaxfilter_active_clear_text', esc_html__('Clear all', 'rey-core')),
			));
		}

		function active_filters_top_classes( $classes ){

			if( ! $this->filters_are_supported ){
				return $classes;
			}

			$pos = get_theme_mod('ajaxfilter_active_position', '');

			if( $pos === '' ){
				return $classes;
			}

			$classes['active_filter_pos'] = '--active-pos-' . $pos;

			return $classes;
		}

		function should_hide_widget( $instance ){


			// bail if set to exclude on certain category
			if( !empty($instance['show_only_on_categories']) ) {
				$show_hide = $instance['show_hide_categories'];

				if ( $show_hide === 'hide' && is_tax( 'product_cat', $instance['show_only_on_categories'] ) ){
					return true;
				}
				elseif ( $show_hide === 'show' && !is_tax( 'product_cat', $instance['show_only_on_categories'] ) ){
					return true;
				}
			}

			if( isset($instance['selective_display']) && $selective_display = $instance['selective_display'] ){

				if( $selective_display === 'shop' ){
					if( ! is_shop() ){
						return true;
					}
				}
				elseif( $selective_display === 'cat' ){
					if( ! is_product_category() ){
						return true;
					}
				}
				elseif( $selective_display === 'attr' ){
					if( ! is_product_taxonomy() ){
						return true;
					}
				}
				elseif( $selective_display === 'tag' ){
					if( ! is_product_tag() ){
						return true;
					}
				}
				elseif( $selective_display === 'cat_attr_tag' ){
					if( ! ( is_product_category() || is_product_taxonomy() || is_product_tag() ) ){
						return true;
					}
				}
			}

			return apply_filters('reycore/ajaxfilters/should_hide_widget', false, $instance);
		}

		function get_applied_products(){

			if( ! get_theme_mod('ajaxfilter_apply_filter_live', false) ){
				wp_send_json_error();
			}

			if ( ! ( isset($_REQUEST['url']) && $url = reycore__clean($_REQUEST['url']) ) ){
				wp_send_json_error();
			}

			if( $url ){
				$url = parse_url($url, PHP_URL_QUERY);
				parse_str($url, $url_query);
			}

			$this->set_chosen_filters( $url_query );

			$chosen_filters = $this->chosen_filters;

			$query_args = [];

			if( $meta_query = $this->queryForMeta( $url_query ) ){
				$query_args['meta_query'] = $meta_query;
			}

			if( $tax_query = $this->queryForTax() ){
				$query_args['tax_query'] = $tax_query;
			}

			if( $post__in = $this->queryForPostIn() ){
				$query_args['post__in'] = $post__in;
			}

			/**
			 * Scenario: while in a custom taxonomy,
			 * make sure to show products of all selected tax terms (not just the active tax)
			 */
			if( is_tax() && ($current_tax = get_queried_object()) && isset($current_tax->taxonomy) ){
				if( ! in_array( $current_tax->taxonomy, apply_filters('reycore/ajaxfilters/tax_reset_query', [ 'product_cat' ]) , true ) ){
					$query_args[$current_tax->taxonomy] = '';
				}
			}

			$query = new WP_Query(array_merge([
				'post_status' => 'publish',
				'post_type'   => 'product',
				'fields'      => 'ids',
			], $query_args));

			if( isset($query->found_posts) ){
				wp_send_json_success($query->found_posts);
			}

			wp_send_json_error();

		}

	}

	function reyAjaxFilters(){
		return ReyAjaxFilters::instance();
	}

	reyAjaxFilters();

}
