<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists('ReyCore_WooCommerce_ProductArchive') ):

class ReyCore_WooCommerce_ProductArchive {

	public $_args = [];

	public $_settings = [];

	public $_products = [];

	public $query_args = [];

	public static $_selectors_to_replace = [];

	public static $btn_styles = [];

	function __construct( $args, $settings = [] ){

		if( empty( $settings ) ){
			return;
		}

		$this->_settings = wp_parse_args($settings, [
			'_skin' => ''
		]);

		$this->_args = wp_parse_args($args, [
			'name'        => '',
			'filter_name' => '',
			'main_class'  => '',
			'filter_button'  => false,
			'attributes'  => []
		]);

		$this->get_query_args();

		return $this;
	}

	/**
	 * Retrieves current loop skin
	 */
	public function get_loop_skin(){

		if( isset($this->_settings['loop_skin']) && $loop_skin = $this->_settings['loop_skin']){
			return $loop_skin;
		}

		return get_theme_mod('loop_skin', 'basic');
	}

	public function get_default_limit(){
		return apply_filters( 'loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page() );
	}

	/**
	 * Get query arguments based on settings.
	 *
	 * @since 1.0.0
	 */
	public function get_query_args(){

		$force_failed_query = false;

		if ( $this->_settings['query_type'] === 'current_query' && (is_post_type_archive('product') || is_tax(get_object_taxonomies('product'))) ) {

			$query_args = array_filter($GLOBALS['wp_query']->query_vars);

			if( isset($this->_settings['limit']) ){
				$query_args['posts_per_page'] = !empty($this->_settings['limit'])  ? absint($this->_settings['limit']) : $this->get_default_limit();
			}
			else if ( isset($this->_settings['rows_per_page']) ){
				$query_args['posts_per_page'] = !empty($this->_settings['rows_per_page']) ?
					(absint($this->_settings['rows_per_page']) * absint($this->_settings['per_row'])) : $this->get_default_limit();
			}

			if( isset($this->_settings['orderby']) && $this->_settings['orderby'] ){
				$query_args['orderby'] = $this->_settings['orderby'];
			}

			if( isset($this->_settings['order']) && $this->_settings['order'] ){
				$query_args['order'] = $this->_settings['order'];
			}

			$query_args['post_type'] = 'product';
			$query_args['fields'] = 'ids';

		}

		else {

			/**
			 * Create attributes for WC Shortcode Products
			 */
			$type = 'products';

			$atts = [
				// Number of columns.
				'columns'        => $this->_settings['per_row'],
				// menu_order, title, date, rand, price, popularity, rating, or id.
				'orderby'        => isset($this->_settings['orderby']) ? $this->_settings['orderby'] : 'menu_order',
				// ASC or DESC.
				'order'          => isset($this->_settings['order']) ? $this->_settings['order'] : 'DESC',
				// Should shortcode output be cached.
				'cache'          => false,
				// Paginate
				'paginate'       => $this->_settings['paginate'],
			];

			$atts['limit'] = !empty($this->_settings['limit'])  ? absint($this->_settings['limit']) : $this->get_default_limit();

			// Manual selection settings
			if ( $this->_settings['query_type'] == 'manual-selection' ) {
				if( is_array($this->_settings['include']) ){
					$atts['ids'] = implode(',',$this->_settings['include']);
				}
				else {
					$atts['ids'] = trim($this->_settings['include']);
				}
			}

			// Related
			elseif ( $this->_settings['query_type'] == 'related' ) {

				$product_id = get_the_ID();

				if( $custom_product_id = $this->_settings['custom_product_id'] ){
					$product_id = $custom_product_id;
				}

				$related_args = apply_filters( 'woocommerce_output_related_products_args', [
					'posts_per_page' => 4,
					'columns'        => 4,
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
					'order'          => 'desc',
				] );

				$excludes = [];

				if( ($product = wc_get_product( $product_id )) && ($up_sells = $product->get_upsell_ids()) && ! empty($up_sells) ){
					$excludes = $up_sells;
				}

				$related_products = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product_id, $related_args['posts_per_page'], $excludes ) ), 'wc_products_array_filter_visible' );

				$related_products = wc_products_array_orderby( $related_products, $related_args['orderby'], $related_args['order'] );

				if( ! empty($related_products) ){

					$related_ids = [];

					foreach ($related_products as $related_product) {
						$related_ids[] = $related_product->get_id();
					}

					$atts['ids'] = implode(',', $related_ids);
				}
				else {
					$force_failed_query = true;
				}

			}

			// Cross Sells
			elseif ( $this->_settings['query_type'] == 'cross-sells' ) {

				$product_id = get_the_ID();

				if( $custom_product_id = $this->_settings['custom_product_id'] ){
					$product_id = $custom_product_id;
				}

				$cross_sells = get_post_meta( absint($product_id), '_crosssell_ids', true );

				if( ! empty($cross_sells) ){
					$atts['ids'] = implode(',', $cross_sells);
				}
				else {
					$force_failed_query = true;
				}
			}

			// Up Sells
			elseif ( $this->_settings['query_type'] == 'up-sells' ) {

				$product_id = get_the_ID();

				if( $custom_product_id = $this->_settings['custom_product_id'] ){
					$product_id = $custom_product_id;
				}

				$product = wc_get_product( $product_id );

				if( $product && ($up_sells = $product->get_upsell_ids()) ){
					if( ! empty($up_sells) ){
						$atts['ids'] = implode(',', $up_sells);
					}
					else {
						$force_failed_query = true;
					}
				}

			}
			else {

				// categories
				if( !empty($this->_settings['categories']) ) {

					$atts['category'] = implode(',',$this->_settings['categories']);

					if( !in_array($this->_settings['query_type'], ['manual-selection', 'recently-viewed', 'current_query', 'cross-sells', 'up-sells'], true) ){
						if(  'and' === $this->_settings['categories_query_type'] ){
							$atts['cat_operator'] = 'AND';
						}
						else if(  'not_in' === $this->_settings['categories_query_type'] ){
							$atts['cat_operator'] = 'NOT IN';
						}
					}

				}
				// tags
				if( !empty($this->_settings['tags']) ) {
					$atts['tag'] = implode(',',$this->_settings['tags']);

					if( !in_array($this->_settings['query_type'], ['manual-selection', 'recently-viewed', 'current_query', 'cross-sells', 'up-sells'], true) ){
						if(  'and' === $this->_settings['tags_query_type'] ){
							$atts['tag_operator'] = 'AND';
						}
						else if(  'not_in' === $this->_settings['tags_query_type'] ){
							$atts['tag_operator'] = 'NOT IN';
						}
					}

				}
				// attributes
				if( isset($this->_settings['attribute']) && ($attribute = $this->_settings['attribute']) && ($terms = $this->_settings[ 'attribute__' . $attribute ]) ) {
					$atts['attribute'] = $attribute;
					$atts['terms'] = implode(',',$terms);
				}
			}

			// Recent
			if ( $this->_settings['query_type'] == 'recent' ) {
				$atts['orderby'] = 'date';
				$atts['order'] = 'DESC';
				$type = 'recent_products';
			}
			elseif ( $this->_settings['query_type'] == 'sale' ) {
				$type = 'sale_products';
			}
			elseif ( $this->_settings['query_type'] == 'best-selling' ) {
				$type = 'best_selling_products';
			}
			elseif ( $this->_settings['query_type'] == 'featured' ) {
				$atts['visibility'] = 'featured';
				$type = 'featured_products';
			}

			if( is_user_logged_in() && $this->_settings['debug__show_query'] === 'yes' ){
				echo '<pre><h4>Atts:</h4>';
				var_dump( $atts );
				echo '</pre>';
			}

			$shortcode = new WC_Shortcode_Products( $atts, $type );
			$query_args = $shortcode->get_query_args();

		}

		// Exclude duplicates
		$to_exclude = [];
		if( isset($this->_settings['exclude_duplicates']) &&
			$this->_settings['exclude_duplicates'] !== '' &&
			isset($GLOBALS["rey_exclude_products"]) &&
			!empty($GLOBALS["rey_exclude_products"]) ){
			$to_exclude = $GLOBALS["rey_exclude_products"];
		}

		/**
		* If we have products on sale, and we want to exclude,
		* override the query args.
		*/
		if( $this->_settings['query_type'] == 'sale' && !empty($this->_settings['exclude']) && isset($query_args['post__in']) && !empty($query_args['post__in']) ) {
			$excludes = array_map( 'trim', explode( ',', $this->_settings['exclude'] ) );
			$excludes = array_merge($excludes, $to_exclude);
			$query_args['post__in'] = array_diff( $query_args['post__in'], array_map( 'absint', $excludes ) );
		}
		/**
		* Get Top Rated
		*/
		elseif ( $this->_settings['query_type'] == 'top' ) {
			$query_args['meta_key'] = '_wc_average_rating';
			$query_args['orderby'] = 'meta_value_num';
			$query_args['order'] = 'DESC';
		}
		/**
		* Recently Viewed
		*/
		elseif ( $this->_settings['query_type'] == 'recently-viewed' ) {
			$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : [];
			$query_args['post__in'] = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
			$query_args['orderby']  = 'post__in';
			// $query_args['order'] = 'DESC';
		}
		/**
		* Add excludes for the rest
		*/
		else {

			if ( $this->_settings['query_type'] != 'manual-selection' && (!empty($this->_settings['exclude']) || !empty($to_exclude))) {
				$query_args['post__not_in'] = array_map( 'trim', explode( ',', $this->_settings['exclude'] ) );
				$query_args['post__not_in'] = array_merge($query_args['post__not_in'], $to_exclude);
			}

		}

		if ( (isset($this->_settings['hide_out_of_stock']) && '' !== $this->_settings['hide_out_of_stock']) || 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT LIKE',
			);
		}

		if( isset($_REQUEST['orderby']) && $_REQUEST['orderby'] === 'price' ){
			$ordering_args = WC()->query->get_catalog_ordering_args( $query_args['orderby'], 'ASC' );
			$query_args['orderby'] = $ordering_args['orderby'];
			$query_args['order']   = $ordering_args['order'];
			if ( $ordering_args['meta_key'] ) {
				$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}
		}

		if( isset($this->_settings['offset']) && $offset = $this->_settings['offset'] ){
			$query_args['offset'] = $offset;
		}

		if( $force_failed_query ){
			$query_args['post__in'] = [0];
		}

		return $this->query_args = $query_args;
	}

	/**
	 * Get the query results,
	 * based on $query_args.
	 *
	 * @since 1.0.0
	 */
	public function get_query_results()
	{

		if( empty($this->query_args) ){
			return;
		}

		$element_id = $this->_args['el_instance']->get_id();

		$query_args = apply_filters( "reycore/elementor/{$this->_args['filter_name']}/query_args", $this->query_args, $element_id, $this->_settings );

		if( is_user_logged_in() && isset($this->_settings['debug__show_query']) && $this->_settings['debug__show_query'] === 'yes' ){
			echo '<pre><h4>Query args:</h4>';
			var_dump( $query_args );
			echo '</pre>';
		}

		/**
		 * Cancel default query and override all results
		 * @since 1.6.3
		 */
		if( $pre_results = apply_filters( "reycore/elementor/{$this->_args['filter_name']}/pre_results", [], $element_id ) ){

			$results = (object) $pre_results;

		}

		else {

			$query = reyCoreHelper()->get_products_query( $query_args );

			$paginated = ! $query->get( 'no_found_rows' );

			$results = (object) apply_filters( "reycore/elementor/{$this->_args['filter_name']}/results", [
				'ids'          => wp_parse_id_list( $query->posts ),
				'total'        => $paginated ? (int) $query->found_posts : count( $query->posts ),
				'total_pages'  => $paginated ? (int) $query->max_num_pages : 1,
				'per_page'     => (int) $query->get( 'posts_per_page' ),
				'current_page' => $paginated ? (int) max( 1, $query->get( 'paged', 1 ) ) : 1,
			], $element_id, $query );

		}

		$GLOBALS["rey_exclude_products"] = $results->ids;

		// Remove ordering query arguments which may have been added by get_catalog_ordering_args.
		WC()->query->remove_ordering_args();

		return $this->_products = $results;
	}

	/**
	 * Get Grid type
	 */
	public function get_grid_type(){

		$grid = get_theme_mod('loop_grid_layout', 'default');

		if( isset($this->_settings['grid_layout']) && $this->_settings['grid_layout'] !== '' ){
			$grid = $this->_settings['grid_layout'];
		}

		return esc_attr( $grid );
	}

	public function allow_css_classes_elementor_edit_mode( $status ){

		if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
			return false;
		}

		return $status;
	}


	public function resize_images( $size ){

		if( isset($this->_settings['image_size']) &&
			($image_size = $this->_settings['image_size']) &&
			$image_size != 'custom' &&
			isset($this->_settings['hide_thumbnails']) && $this->_settings['hide_thumbnails'] != 'yes'
		){
			$size = $image_size;
		}

		return $size;
	}

	public function resize_images__custom( $image, $product ){

		if ( isset($this->_settings['image_custom_dimension']) && $image_custom_dimension = $this->_settings['image_custom_dimension'] ) {

			$image_id = 0;

			if ( $product->get_parent_id() ) {
				$parent_product = wc_get_product( $product->get_parent_id() );
				if ( $parent_product ) {
					$image_id = $parent_product->get_image_id();
				}
			}
			else {
				$image_id = $product->get_image_id();
			}

			return reycore__get_attachment_image( [
				'image' => [
					'id' => $image_id
				],
				'size' => 'custom',
				'attributes' => [ 'class' => "rey-thumbImg attachment-custom size-custom" ],
				'settings' => $this->_settings,
			] );
		}

		return $image;
	}

	public function resize_second_images( $image, $product, $image_id ){

		if ( isset($this->_settings['image_custom_dimension']) && $image_custom_dimension = $this->_settings['image_custom_dimension'] ) {

			return reycore__get_attachment_image( [
				'image' => [
					'id' => $image_id
				],
				'size' => 'custom',
				'attributes' => [ 'class' => "rey-productThumbnail__second" ],
				'settings' => $this->_settings,
			] );
		}

		return $image;
	}

	function default_orderby($orderby){

		if( isset($this->_settings['query_type']) ){
			if( $this->_settings['query_type'] === 'recent' ){
				return 'date';
			}
		}

		return $orderby;
	}

	public function prevent_extra_media($mod){

		if( get_theme_mod('loop_extra_media', 'second') !== 'no' &&
			isset($this->_settings['prevent_2nd_image']) &&
			$this->_settings['prevent_2nd_image'] === 'yes' ){
				return true;
		}

		return $mod;
	}

	public function disable_before_after($mod){

		if( isset($this->_settings['prevent_ba_content']) ){
			if( $this->_settings['prevent_ba_content'] === 'yes' || in_array($this->_settings['_skin'], ['carousel-section', 'carousel', 'mini']) ){
				return false;
			}
		}

		return $mod;
	}

	public function disable_stretched_products($mod){

		if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
			return false;
		}

		if( isset($this->_settings['prevent_stretched']) ){
			if( $this->_settings['prevent_stretched'] === 'yes' || in_array($this->_settings['_skin'], ['carousel-section', 'carousel', 'mini']) ){
				return false;
			}
		}

		return $mod;
	}


	/**
	 * Sets individual loop prop based on settings
	 *
	 * @since 1.3.0
	 */
	function set_loop_prop( $flag, $component, $status ){

		switch ( $flag ) {
			case '':
				$prop_status = $status;
				break;
			case 'yes':
				$prop_status = false;
				break;
			case 'no':
				$prop_status = true;
				break;
			default:
		}

		wc_set_loop_prop( $component, $prop_status );
	}

	/**
	 * Sets loop * item components * props based on settings
	 *
	 * @since 1.3.0
	 */
	public function set_loop_props(){

		$all_components = reycore_wc_get_loop_components();

		foreach ($all_components as $component => $status) {

			if( ! isset( $this->_settings['hide_' . $component] ) ){
				continue;
			}

			$component_setting = $this->_settings['hide_' . $component];

			if( !is_array($status) ){ // regular
				$this->set_loop_prop($component_setting, $component, $status);
			}
			// has subcomponents
			else {

				// if NO (don't hide) is selected, revert to these default positons
				$subcomponents_positions = [
					'quickview' => get_theme_mod('loop_quickview_position', 'bottom'),
					'discount' => get_theme_mod('loop_discount_label', 'price') === 'price' ? 'price' : 'top',
					'wishlist' => ReyCore_WooCommerce_Wishlist::catalog_default_position(),
				];

				foreach ($status as $subcomponent => $substatus) {

					// when component is disabled globally
					// force enable that one only
					if( $component_setting === 'no' &&
						isset($subcomponents_positions[$component]) && ($spos = $subcomponents_positions[$component]) &&
						$subcomponent !== $spos ) {
						continue;
					}

					$this->set_loop_prop($component_setting, $component . '_' . $subcomponent, $substatus);
				}
			}
		}

		/**
		 * Loop components
		 */


		$loop_components = [
			'columns'                      => $this->_settings['per_row'],
			'is_paginated'                 => $this->_settings['paginate']           === 'yes',
			'result_count'                 => $this->_settings['show_header']        === 'yes',
			'catalog_ordering'             => $this->_settings['show_header']        === 'yes',
			'view_selector'                => $this->_settings['show_view_selector'] === 'yes',
			'filter_button'                => $this->_args['filter_button'],
			'filter_top_sidebar'           => false,
			'product_thumbnails_slideshow' => get_theme_mod('loop_extra_media', 'second') === 'slideshow',
		];

		if( isset($this->_settings['show_count']) && $this->_settings['show_count'] === '' ){
			$loop_components['result_count'] = false;
		}

		if( isset($this->_settings['show_sorting']) && $this->_settings['show_sorting'] === '' ){
			$loop_components['catalog_ordering'] = false;
		}

		// Tweak
		if( in_array($this->_settings['_skin'], ['carousel-section', 'carousel'] ) ){
			// disable thumbnails slideshow
			$loop_components['product_thumbnails_slideshow'] = false;
			// Remove entry animation class
			$loop_components['entry_animation'] = false;
		}

		// Tweak
		if( in_array($this->_settings['_skin'], ['carousel-section'] ) ){
			// disable thumbnails
			$loop_components['thumbnails'] = false;
		}

		$loop_components = apply_filters("reycore/elementor/{$this->_args['filter_name']}/loop_components", $loop_components, $this->_settings );

		foreach ($loop_components as $component => $status) {
			wc_set_loop_prop( $component, $status );
		}

	}

	public function loop_header_tweaks(){
		if( $this->_settings['show_header'] === 'yes' ){
			do_action("reycore/elementor/{$this->_args['filter_name']}/show_header", \Elementor\Plugin::$instance->editor->is_edit_mode());
		}
	}

	/**
	 * Render Start
	 *
	 * @since 1.0.0
	 */
	public function render_start()
	{
		$this->before_products();

		if ( is_null(WC()->session) ) {
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
		}

		// Include WooCommerce frontend stuff
		wc()->frontend_includes();

		// Prime caches to reduce future queries.
		if ( is_callable( '_prime_post_caches' ) ) {
			_prime_post_caches( $this->_products->ids );
		}

		ReyCore_WooCommerce_Loop::getInstance()->load_scripts([
			'grid_type' => $this->get_grid_type()
		]);

		add_filter('reycore/woocommerce/loop/prevent_2nd_image', [$this, 'prevent_extra_media']);
		add_filter('reycore/woocommerce/loop/prevent_product_items_slideshow', [$this, 'prevent_extra_media']);
		add_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', [$this, 'allow_css_classes_elementor_edit_mode'], 10 );
		add_filter( 'single_product_archive_thumbnail_size', [$this, 'resize_images'], 10 );
		add_filter( 'woocommerce_product_get_image', [$this, 'resize_images__custom'], 10, 2 );
		add_filter( 'reycore/woocommerce/loop/second_image', [$this, 'resize_second_images'], 10, 3 );
		add_filter( 'woocommerce_default_catalog_orderby', [$this, 'default_orderby'], 10 );
		add_filter( 'reycore/woocommerce/catalog/before_after/enable', [$this, 'disable_before_after'], 10);
		add_filter( 'reycore/woocommerce/catalog/stretch_product/enable', [$this, 'disable_stretched_products'], 10);

		if( isset($this->_settings['hide_notices']) && $this->_settings['hide_notices'] !== '' ){
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
		}

		add_action( 'woocommerce_before_shop_loop', [$this, 'set_loop_props'], 5); // after initial `set_loop_props`
		add_action( 'woocommerce_before_shop_loop', [$this, 'loop_header_tweaks'], 6);

		// Setup the loop.
		wc_setup_loop(
			[
				'is_shortcode' => ! is_tax(),
				'is_product_grid' => true,
				'is_search'    => false,
				'total'        => $this->_products->total,
				'total_pages'  => $this->_products->total_pages,
				'per_page'     => $this->_products->per_page,
				'current_page' => $this->_products->current_page,
			]
		);

		$wrapper_classes = [
			'woocommerce',
			'rey-element',
			$this->_args['main_class'],
			$this->_args['main_class'] ? $this->_args['main_class'] . '--' . ( isset($this->_settings['hide_thumbnails']) && $this->_settings['hide_thumbnails'] == 'yes' ? 'no-thumbs' : 'has-thumbs' ) : '',
			$this->_settings['_skin'] ? 'reyEl-productGrid--skin-' . $this->_settings['_skin'] : '',
			$this->_settings['show_header'] === 'yes' ? '--show-header' : ''
		];

		// Vertical align in middle for
		// uncropped images.
		if(
			get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
			isset( $this->_settings['uncropped_vertical_align'] ) && $this->_settings['uncropped_vertical_align'] !== '' ){
				$wrapper_classes[] = '--vertical-middle-thumbs';
		}

		if( $this->_settings['paginate'] === 'yes' ){

			add_filter( 'reycore/load_more_pagination/product', '__return_true');

			add_filter( 'reycore/load_more_pagination_args', function($args){

				$pagenum =  wc_get_loop_prop( 'current_page' ) + 1;

				if( wc_get_loop_prop( 'total_pages' ) >= $pagenum ) {

					$path = add_query_arg( 'product-page', $pagenum, false );

					if( is_multisite() ){
						$args['url'] = esc_url_raw ( network_site_url( $path ) );
					}
					else {
						$args['url'] = esc_url_raw ( site_url( $path ) );
					}
				}
				else {
					$args['url'] = '';
				}

				$args['post_type'] = 'product';
				return $args;
			});
		}

		$this->_args['attributes']['data-qt'] = $this->_settings['query_type'];

		?>

		<div class="<?php echo implode( ' ', $wrapper_classes ) ?>" <?php echo reycore__implode_html_attributes($this->_args['attributes']) ?>>

		<?php
		do_action( 'woocommerce_before_shop_loop' );
	}

	/**
	 * Grid CSS Classes
	 */
	public function get_css_classes(){

		// Grid Gap CSS class
		$gap_size = get_theme_mod('loop_gap_size', 'default');

		if( isset($this->_settings['gaps']) && $this->_settings['gaps'] !== '' ){
			$gap_size = $this->_settings['gaps'];
		}

		$classes['grid_gap'] = 'rey-wcGap-' . esc_attr( $gap_size );

		// Product Grid CSS class
		$classes['grid_layout'] = 'rey-wcGrid-' . $this->get_grid_type();

		if( $skin = $this->get_loop_skin() ){
			$classes['skin'] = '--skin-' . esc_attr( $this->get_loop_skin() );
		}

		if( wc_get_loop_prop( 'is_paginated' ) && wc_get_loop_prop( 'total_pages' ) ){

			$classes['paginated'] = '--paginated';

			if( in_array(get_theme_mod('loop_pagination', 'paged'), ['load-more', 'infinite'], true) ){
				$classes['paginated_infinite'] = '--infinite';
			}

		}

		return $classes;
	}


	public function loop_start()
	{

		wc_set_loop_prop( 'name', $this->_args['name'] );
		wc_set_loop_prop( 'loop', 0 );

		do_action('reycore/woocommerce/loop/before_grid');
		do_action('reycore/woocommerce/loop/before_grid/name=' . $this->_args['name']);

		$classes = $this->get_css_classes();

		$classes['columns'] = 'columns-' . wc_get_loop_prop('columns');

		$cols_per_tablet = absint( isset($this->_settings['per_row_tablet'] ) && $this->_settings['per_row_tablet'] ? $this->_settings['per_row_tablet'] : reycore_wc_get_columns('tablet') );
		$classes['columns-tablet'] = 'columns-tablet-' . $cols_per_tablet;
		wc_set_loop_prop( 'pg_columns_tablet', $cols_per_tablet );

		$cols_per_mobile = absint( isset($this->_settings['per_row_mobile'] ) && $this->_settings['per_row_mobile'] ? $this->_settings['per_row_mobile'] : reycore_wc_get_columns('mobile') );
		$classes['columns-mobile'] = 'columns-mobile-' . $cols_per_mobile;
		wc_set_loop_prop( 'pg_columns_mobile', $cols_per_mobile );

		$cols = wc_get_loop_prop( 'columns' );

		if( $cols >= wc_get_loop_prop( 'total' ) ){
			$classes['prevent_margin'] = '--no-margins';
		}

		if(
			isset($this->_settings['ajax_load_more']) && $this->_settings['ajax_load_more'] !== '' &&
			! wc_get_loop_prop( 'is_paginated' ) &&
			$this->_settings['_skin'] === '' ){
			unset($classes['prevent_margin']);
		}

		printf('<ul class="products %s" data-cols="%d" %s>',
			implode(' ', apply_filters('reycore/woocommerce/product_loop_classes', $classes)),
			esc_attr($cols),
			apply_filters('reycore/woocommerce/product_loop_attributes', '', $this->_settings)
		);
	}

	public function loop_end(){
		echo '</ul>';

		do_action('reycore/woocommerce/loop/after_grid');
		do_action('reycore/woocommerce/loop/after_grid/name=' . $this->_args['name']);

	}

	public function product_css_classes($classes){

		if(
			('basic' === $this->_settings['loop_skin'] && ($hover_anim = $this->_settings['basic_hover_animation']) ) ||
			('wrapped' === $this->_settings['loop_skin'] && ($hover_anim = $this->_settings['wrapped_hover_animation']) ) ||
			('cards' === $this->_settings['loop_skin'] && ($hover_anim = $this->_settings['cards_hover_animation']) ) ||
			('iconized' === $this->_settings['loop_skin'] && ($hover_anim = $this->_settings['iconized_hover_animation']) ) ||
			('proto' === $this->_settings['loop_skin'] && ($hover_anim = $this->_settings['proto_hover_animation']) )
		){
			if( 'yes' === $hover_anim ){
				$classes['hover-animated'] = 'is-animated';
			}
			else {
				unset( $classes['hover-animated'] );
			}
		}

		if( 'cards' === $this->_settings['loop_skin'] && ($cards__expand_thumbs = $this->_settings['cards_expand_thumbs']) ){
			if( 'yes' === $cards__expand_thumbs ){
				$classes['cards_expand_thumbs'] = '--expand-thumbs';
			}
			else {
				unset( $classes['cards_expand_thumbs'] );
			}
		}


		if( 'proto' === $this->_settings['loop_skin'] ){
			if ($proto__loop_shadow = $this->_settings['proto_loop_shadow']) {
				if( 'no' === $proto__loop_shadow ){
					unset( $classes['shadow_active'] );
				}
				else {
					$classes['shadow_active'] = '--shadow-' . $proto__loop_shadow;
				}
			}
			if ($proto__loop_shadow_hover = $this->_settings['proto_loop_shadow_hover']) {
				if( 'no' === $proto__loop_shadow_hover ){
					unset( $classes['shadow_hover'] );
				}
				else {
					$classes['shadow_hover'] = '--shadow-h-' . $proto__loop_shadow_hover;
				}
			}
		}


		// If both Add to cart & Quickview buttons are disabled
		// remove the is-animated class as it breaks the layout.
		if( array_key_exists('hover-animated', $classes) ){
			if( isset($this->_settings['hide_add_to_cart']) && $this->_settings['hide_add_to_cart'] === 'yes' &&
				isset($this->_settings['hide_quickview']) && $this->_settings['hide_quickview'] === 'yes' ) {
				unset( $classes['hover-animated'] );
			}
		}

		// if text align is selected
		// remove global class and replace with selected text align
		if( isset($this->_settings['text_align']) && !empty($this->_settings['text_align']) ){
			if( array_key_exists( 'rey-wc-loopAlign-' . get_theme_mod('loop_alignment', 'left') , $classes) ){
				unset( $classes['text-align'] );
			}
			$classes['text-align'] = 'rey-wc-loopAlign-' . $this->_settings['text_align'];
		}

		// custom height for cropped image layout
		$unsupported_grids_custom_container_height = ['metro'];
		if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
			! in_array( $this->get_grid_type(), $unsupported_grids_custom_container_height) &&
			isset($this->_settings['custom_image_container_height']) && $this->_settings['custom_image_container_height']['size'] !== '' ) {
			$classes[] = '--customImageContainerHeight';
		}

		if( in_array($this->_settings['_skin'], ['carousel', 'carousel-section'], true) ) {
			$classes['splide-item-class'] = 'splide__slide';
		}

		return $classes;
	}


	/**
	 * Product Loop
	 *
	 * @since 1.0.0
	 */
	public function render_products()
	{
		ob_start();

		if( isset($GLOBALS['post']) ) {
			$original_post = $GLOBALS['post'];
		}

		if ( wc_get_loop_prop( 'total' ) ) {

			if( ( isset($this->_settings['entry_animation']) && $this->_settings['entry_animation'] !== 'yes') ||
				(
					(isset($this->_settings['horizontal_desktop']) && $this->_settings['horizontal_desktop'] !== '') ||
					(isset($this->_settings['horizontal_tablet']) && $this->_settings['horizontal_tablet'] !== '') ||
					(isset($this->_settings['horizontal_mobile']) && $this->_settings['horizontal_mobile'] !== '')
				)
			){
				wc_set_loop_prop( 'entry_animation', false );
			}

			add_filter( 'post_class', [$this, 'product_css_classes'], 20 );

			foreach ( $this->_products->ids as $product_id ) {
				$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
				setup_postdata( $GLOBALS['post'] );
				// Hook: woocommerce_shop_loop.
				do_action( 'woocommerce_shop_loop' );
				// Render product template.
				wc_get_template_part( 'content', 'product' );
			}

			remove_filter( 'post_class', [$this, 'product_css_classes'], 20 );

		}

		if( isset($original_post) ) {
			$GLOBALS['post'] = $original_post; // WPCS: override ok.
		}

		$output = ob_get_clean();

		if( !empty( self::$_selectors_to_replace ) ){
			foreach (self::$_selectors_to_replace as $selector_to_search => $to_replace) {
				$output = str_replace( $selector_to_search, $selector_to_search . ' ' . $to_replace, $output );
			}
		}

		echo $output;
	}


	/**
	 * End rendering the widget
	 * Reset components at the end
	 *
	 * @since 1.0.0
	 */
	public function render_end(){

		wp_reset_postdata();

		do_action( 'woocommerce_after_shop_loop' );

		remove_action( 'woocommerce_before_shop_loop', [$this, 'set_loop_props'], 5); // after initial `set_loop_props`
		remove_action( 'woocommerce_before_shop_loop', [$this, 'loop_header_tweaks'], 6);
		remove_filter( 'reycore/woocommerce/loop/prevent_2nd_image', [$this, 'prevent_extra_media']);
		remove_filter( 'reycore/woocommerce/loop/prevent_product_items_slideshow', [$this, 'prevent_extra_media']);
		remove_filter( 'single_product_archive_thumbnail_size', [$this, 'resize_images'], 10 );
		remove_filter( 'woocommerce_product_get_image', [$this, 'resize_images__custom'], 10 );
		remove_filter( 'reycore/woocommerce/loop/second_image', [$this, 'resize_second_images'], 10 );
		remove_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', [$this, 'allow_css_classes_elementor_edit_mode'], 10 );
		remove_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false', 10);
		remove_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false', 10);
		remove_filter( 'woocommerce_default_catalog_orderby', [$this, 'default_orderby'], 10 );
		remove_filter( 'reycore/woocommerce/catalog/before_after/enable', [$this, 'disable_before_after'], 10);
		remove_filter( 'reycore/woocommerce/catalog/stretch_product/enable', [$this, 'disable_stretched_products'], 10);

		$this->after_products();
		$this->ajax_load_more();

		?></div><?php

	}

	public static function add_extra_data_controls( $element ){

		/**
		 * Extra Data
		 */
		$element->start_controls_section(
			'section_extra_data',
			[
				'label' => __( 'Extra Data', 'rey-core' ),
			]
		);

			$extra = new \Elementor\Repeater();

			$extra->add_control(
				'component',
				[
					'label' => esc_html__( 'Component', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => '- Select -',
						'acf' => esc_html__('ACF Field', 'rey-core'),
						'dimensions' => esc_html__('Product Dimensions', 'rey-core'),
						'weight' => esc_html__('Product Weight', 'rey-core'),
						'attribute' => esc_html__('Product Attribute', 'rey-core'),
						'sku' => esc_html__('SKU', 'rey-core'),
						'stock' => esc_html__('Stock amount', 'rey-core'),
						'placeholder' => esc_html__('Placeholder', 'rey-core'),
					],
				]
			);

			$extra->add_control(
				'acf_field',
				[
					'label' => esc_html__( 'Select ACF Field', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'acf',
						'field_types' => [
							'text',
							'textarea',
							'number',
							'wysiwyg',
							'url',
							'image',
						],
					],
					'condition' => [
						'component' => 'acf',
					],
				]
			);

			$extra->add_control(
				'acf_display',
				[
					'label' => esc_html__( 'Display as', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'text',
					'options' => [
						'text'  => esc_html__( 'Text', 'rey-core' ),
						'image'  => esc_html__( 'Image', 'rey-core' ),
					],
					'condition' => [
						'component' => 'acf',
					],
				]
			);

			$extra->add_control(
				'placeholder_hook',
				[
					'label' => esc_html__( 'Placeholder Hook', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: unique_key', 'rey-core' ),
					'description' => 'You can use the filter hook "reycore/woocommerce/products/extra_data/placeholder=unique_key" to add any <a href="https://d.pr/n/wqCcKN">custom data</a>.',
					'condition' => [
						'component' => 'placeholder',
					],
				]
			);

			$extra->add_control(
				'stock_text',
				[
					'label' => esc_html__( 'Stock Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '%d in stock',
					'placeholder' => esc_html__( 'eg: %d in stock', 'rey-core' ),
					'condition' => [
						'component' => 'stock',
					],
				]
			);

			$attrs = [];

			foreach( wc_get_attribute_taxonomies() as $attribute ) {
				$attribute_name = $attribute->attribute_name;
				$attrs[$attribute_name] = $attribute->attribute_label;
			}

			$extra->add_control(
				'attribute',
				[
					'label' => esc_html__( 'Attribute', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => '- Select -',
					] + $attrs,
					'condition' => [
						'component' => 'attribute',
					],
				]
			);

			$extra->add_control(
				'position',
				[
					'label' => esc_html__( 'Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => '- Select -',
						'top_left' => esc_html__('Thumb Top-Left', 'rey-core'),
						'top_right' => esc_html__('Thumb Top-Right', 'rey-core'),
						'bottom_left' => esc_html__('Thumb Bottom-Left', 'rey-core'),
						'bottom_right' => esc_html__('Thumb Bottom-Right', 'rey-core'),
						'before_title' => esc_html__('Before Title', 'rey-core'),
						'after_title' => esc_html__('After Title', 'rey-core'),
						'after_price' => esc_html__('After Price', 'rey-core'),
						'after_content' => esc_html__('After all content', 'rey-core'),
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_control(
				'title',
				[
					'label' => esc_html__( 'Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => [
						'component!' => '',
					],
				]
			);

			// General

			$extra->add_control(
				'color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'color: {{VALUE}}',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => '{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}',
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_control(
				'radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'border-radius: {{VALUE}}px',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_responsive_control(
				'padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_responsive_control(
				'margin',
				[
					'label' => __( 'Margin', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);


			$extra->add_control(
				'img_size',
				[
					'label' => esc_html__( 'Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'width: {{VALUE}}px',
					],
					'condition' => [
						'component' => 'acf',
						'acf_display' => 'image',
						'acf_field!' => '',
					],
				]
			);

			$extra->add_control(
				'mobile',
				[
					'label' => esc_html__( 'Show on mobiles', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_control(
				'stretch',
				[
					'label' => esc_html__( 'Stretch', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'block',
					'default' => '',
					'condition' => [
						'component!' => '',
					],
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'display:{{VALUE}};',
					],
				]
			);

			// $extra->add_control(
			// 	'hover',
			// 	[
			// 		'label' => esc_html__( 'Hover only', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'default' => '',
			// 		'condition' => [
			// 			'component!' => '',
			// 		],
			// 	]
			// );

			$element->add_control(
				'extra_data',
				[
					'label' => __( 'Extra data items', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $extra->get_controls(),
					'default' => [],
					'title_field' => '{{{ component }}}',
					'prevent_empty' => false,
				]
			);

		$element->end_controls_section();

	}

	public static function add_component_display_controls( $element ){

		$element->start_controls_section(
			'section_layout_components',
			[
				'label' => __( 'Components Display', 'rey-core' ),
			]
		);

		$yesno_opts = [
			''  => esc_html__( '- Inherit -', 'rey-core' ),
			'no'  => esc_html__( 'Show', 'rey-core' ),
			'yes'  => esc_html__( 'Hide', 'rey-core' ),
		];

		$element->add_control(
			'hide_brands',
			[
				'label' => __( 'Brand', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_category',
			[
				'label' => __( 'Category', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_excerpt',
			[
				'label' => __( 'Short Description', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_quickview',
			[
				'label' => __( 'Quickview', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_wishlist',
			[
				'label' => __( 'Wishlist Icon', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_prices',
			[
				'label' => __( 'Price', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_discount',
			[
				'label' => __( 'Discount Label', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'hide_prices!' => 'yes',
				],
			]
		);

		$element->add_control(
			'hide_ratings',
			[
				'label' => __( 'Ratings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_add_to_cart',
			[
				'label' => __( 'Add To Cart', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_thumbnails',
			[
				'label' => __( 'Thumbnails', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'_skin!' => 'carousel-section',
				],
			]
		);

		$element->add_control(
			'hide_new_badge',
			[
				'label' => __( '"New" Badge', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_variations',
			[
				'label' => __( 'Product Variations', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'separator' => 'after'
			]
		);

		if( get_theme_mod('loop_extra_media', 'second') !== 'no' ):
			$element->add_control(
				'prevent_2nd_image',
				[
					'label' => __( 'Prevent extra images', 'rey-core' ),
					'description' => __( 'This option will disable showing extra images inside the product item. The option overrides the one located in Customizer > WooCommerce > Product catalog - Layout.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
		endif;

		$element->add_control(
			'prevent_ba_content',
			[
				'label' => __( 'Prevent Before/After Content', 'rey-core' ),
				'description' => __( 'This option will disable showing products that have other Products or Global sections, assigned before or after them.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$element->add_control(
			'prevent_stretched',
			[
				'label' => __( 'Prevent Stretched Products', 'rey-core' ),
				'description' => __( 'This option will disable showing products that are Stretched in the catalog.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_notices',
			[
				'label' => __( 'Hide Notices', 'rey-core' ),
				'description' => __( 'This option will disable showing the notices that are usually added for any product loop (archive).', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$element->add_control(
			'entry_animation',
			[
				'label' => __( 'Animate on scroll', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin!' => ['carousel', 'carousel-section'],
				],
				'separator' => 'before'
			]
		);

		$element->end_controls_section();
	}

	public static function add_common_styles_controls( $element ){

		$element->start_controls_section(
			'section_styles_general',
			[
				'label' => __( 'General Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$element->add_control(
			'loop_skin',
			[
				'label' => esc_html__( 'Product Item Skin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
				] + apply_filters('reycore/woocommerce/loop/get_skins', []),
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$element->add_control(
			'color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--body-color: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'link_color',
			[
				'label' => __( 'Links Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--link-color: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'link_hover_color',
			[
				'label' => __( 'Links Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--link-color-hover: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'text_align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'condition' => [
					'_skin!' => 'mini',
				],
			]
		);

		if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' ){

			$element->add_control(
				'uncropped_vertical_align',
				[
					'label' => esc_html__( 'Middle Vertical Align', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'_skin!' => 'mini',
					],
				]
			);

			$element->add_control(
				'custom_image_container_height',
				[
				   'label' => esc_html__( 'Custom Image Container Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 100,
							'max' => 1000,
							'step' => 1,
						],
						'em' => [
							'min' => 3,
							'max' => 15.0,
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-custom-image-height: {{SIZE}}{{UNIT}};',
					],
					'render_type' => 'template',
					'condition' => [
						'_skin!' => 'mini',
					],
				]
			);
		}

		$element->add_control(
			'grid_styles_settings_title', [
				'label' => __( 'GRID STYLES', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_control(
			'grid_layout',
			[
				'label' => esc_html__( 'Grid Layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''           => esc_html__( 'Inherit', 'rey-core' ),
					'default'    => esc_html__( 'Default', 'rey-core' ),
					'masonry'    => esc_html__( 'Masonry', 'rey-core' ),
					'masonry2'   => esc_html__( 'Masonry V2', 'rey-core' ),
					'metro'      => esc_html__( 'Metro', 'rey-core' ),
					'scattered'  => esc_html__( 'Scattered', 'rey-core' ),
					'scattered2' => esc_html__( 'Scattered Mixed & Random', 'rey-core' ),
				],
				'condition' => [
					'_skin!' => 'mini',
				],
			]
		);

		$element->add_control(
			'gaps',
			[
				'label' => __( 'Grid Gaps', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					''         => __( 'Inherit', 'rey-core' ),
					'no'       => __( 'No gaps', 'rey-core' ),
					'line'     => __( 'Line', 'rey-core' ),
					'narrow'   => __( 'Narrow', 'rey-core' ),
					'default'  => __( 'Default', 'rey-core' ),
					'extended' => __( 'Extended', 'rey-core' ),
					'wide'     => __( 'Wide', 'rey-core' ),
					'wider'    => __( 'Wider', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'grid_mb',
			[
				'label' => __( 'Grid Vertical Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 180,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
					'rem' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ul.products.columns-1 li.product:nth-child(1) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-2 li.product:nth-child(2) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-3 li.product:nth-child(3) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-4 li.product:nth-child(4) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-5 li.product:nth-child(5) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ul.products.columns-6 li.product:nth-child(6) ~ li.product' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$element->add_control(
			'misc_styles_settings_title', [
				'label' => __( 'MISC. STYLES', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_responsive_control(
			'th_distance',
			[
				'label' => esc_html__( 'Thumbnails badges distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'_skin' => ['', 'carousel'],
				],
				'selectors' => [
					'{{WRAPPER}} ul.products li.product .rey-thPos' => '--woocomerce-thpos-distance: {{VALUE}}px;',
				],
			]
		);

		$element->end_controls_section();

		self::add_components_styles_controls( $element );
		self::add_skin_controls__basic( $element );
		self::add_skin_controls__wrapped( $element );
		self::add_skin_controls__cards( $element );
		self::add_skin_controls__iconized( $element );
		self::add_skin_controls__proto( $element );
	}

	public static function add_components_styles_controls( $element ){

		$element->start_controls_section(
			'section_component_styles',
			[
				'label' => __( 'Component Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$components = new \Elementor\Repeater();
			$conditions = [];

			$component_options = [
				''  => esc_html__( '- Select -', 'rey-core' )
			];

			foreach (self::component_mapping() as $key => $comp) {

				$component_options[ $key ] = $comp['name'];

				if( isset($comp['supports']) ){

					foreach ( $comp['supports'] as $support) {

						$conditions[$support]['relation'] = 'or';
						$conditions[$support]['terms'][] = [
							'name' => 'component',
							'operator' => '==',
							'value' => $key,
						];

					}

				}
			}

			$components->add_control(
				'component',
				[
					'label' => esc_html__( 'Component', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $component_options,
				]
			);

			$components->add_control(
				'btn_style',
				[
					'label' => esc_html__( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => esc_html__( '- Inherit -', 'rey-core' ),
						'under' => esc_html__( 'Default (underlined)', 'rey-core' ),
						'hover' => esc_html__( 'Hover Underlined', 'rey-core' ),
						'primary' => esc_html__( 'Primary', 'rey-core' ),
						'primary-out' => esc_html__( 'Primary Outlined', 'rey-core' ),
						'clean' => esc_html__( 'Clean', 'rey-core' ),
					],
					'conditions' => $conditions['btn_style']
				]
			);

			$components->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'color: {{VALUE}}',
					],
				]
			);

			$components->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'background-color: {{VALUE}}',
					],
					'conditions' => $conditions['bg_color']
				]
			);

			$components->add_control(
				'hover_color',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}:hover' => 'color: {{VALUE}}',
					],
					'conditions' => $conditions['hover_color']
				]
			);

			$components->add_control(
				'hover_bg_color',
				[
					'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}:hover' => 'background-color: {{VALUE}}',
					],
					'conditions' => $conditions['hover_bg_color']
				]
			);

			$components->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => '{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}',
				]
			);

			$components->add_control(
				'minheight',
				[
					'label' => esc_html__( 'Min. Height', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'min-height: {{VALUE}}px',
					],
					'conditions' => $conditions['minheight']
				]
			);

			$element->add_control(
				'comp_styles',
				[
					'label' => __( 'Component Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $components->get_controls(),
					'default' => [],
					'title_field' => '{{{ component }}}',
					'prevent_empty' => false,
				]
			);


		$element->end_controls_section();

	}

	public static function component_mapping(){
		return [
			'title'     => [
				'name' => esc_html__('Title', 'rey-core'),
				'selector' => 'woocommerce-loop-product__title',
				'supports' => ['minheight']
			],
			'price'     => [
				'name' => esc_html__('Price', 'rey-core'),
				'selector' => 'rey-loopPrice',
			],
			'brand'     => [
				'name' => esc_html__('Brand', 'rey-core'),
				'selector' => 'rey-brandLink',
			],
			'category'  => [
				'name' => esc_html__('Categories', 'rey-core'),
				'selector' => 'rey-productCategories',
			],
			'excerpt'   => [
				'name' => esc_html__('Description', 'rey-core'),
				'selector' => 'woocommerce-product-details__short-description',
			],
			'atc'       => [
				'name' => esc_html__('Add to cart button', 'rey-core'),
				'selector' => 'add_to_cart_button',
				'supports' => ['btn_style', 'hover_color', 'bg_color', 'hover_bg_color']
			],
			'quickview' => [
				'name' => esc_html__('Quickview button', 'rey-core'),
				'selector' => 'rey-quickviewBtn',
				'supports' => ['btn_style', 'hover_color', 'bg_color', 'hover_bg_color']
			],
			'wishlist'  => [
				'name' => esc_html__('Wishlist', 'rey-core'),
				'selector' => 'rey-wishlistBtn-link',
				'supports' => ['hover_color', 'bg_color', 'hover_bg_color']
			],
			'compare'   => [
				'name' => esc_html__('Compare', 'rey-core'),
				'selector' => 'rey-compareBtn-link',
				'supports' => ['hover_color', 'bg_color', 'hover_bg_color']
			],
			'new'       => [
				'name' => esc_html__('New Badge', 'rey-core'),
				'selector' => 'rey-new-badge',
				'supports' => ['bg_color'],
			],
			'soldout'   => [
				'name' => esc_html__('Stock Badge', 'rey-core'),
				'selector' => 'rey-soldout-badge',
				'supports' => ['bg_color'],
			],
			'featured'  => [
				'name' => esc_html__('Featured Badge', 'rey-core'),
				'selector' => 'rey-featured-badge',
				'supports' => ['bg_color'],
			],
			'sale'      => [
				'name' => esc_html__('Discount badge', 'rey-core'),
				'selector' => 'rey-discount',
				'supports' => ['bg_color'],
			],
			'rating'      => [
				'name' => esc_html__('Star rating', 'rey-core'),
				'selector' => 'star-rating',
			],
		];
	}

	function before_products(){

		if( isset($this->_settings['comp_styles']) && !empty( $this->_settings['comp_styles'] ) ){

			$all_components = self::component_mapping();

			foreach ($this->_settings['comp_styles'] as $comp) {

				if( isset( $all_components[ $comp[ 'component' ] ] ) && $component = $all_components[ $comp[ 'component' ] ] ){
					self::$_selectors_to_replace[ $component['selector'] ] = 'elementor-repeater-item-' . esc_attr($comp['_id']);
				}

				if( isset($comp[ 'btn_style' ]) && $btn_style = $comp[ 'btn_style' ] ){
					self::$btn_styles[ $comp[ 'component' ] ] = $btn_style;
				}

			}
		}

		add_filter( 'theme_mod_loop_skin', [$this, 'set_loop_skin'] );
		add_filter( 'theme_mod_loop_add_to_cart_style', [$this, 'atc_button_style'] );
		add_filter( 'theme_mod_loop_quickview_style', [$this, 'qv_button_style'] );
		add_filter( 'theme_mod_proto_loop_padded', [$this, 'proto_loop_padded'] );

		$this->add_remove_extra_data();

	}

	function add_remove_extra_data( $add = true ){

		if( ! (isset($this->_settings['extra_data']) && !empty($this->_settings['extra_data'])) ){
			return;
		}

		$positions = [
			'top_left'      => 'reycore/loop_inside_thumbnail/top-left',
			'top_right'     => 'reycore/loop_inside_thumbnail/top-right',
			'bottom_left'   => 'reycore/loop_inside_thumbnail/bottom-left',
			'bottom_right'  => 'reycore/loop_inside_thumbnail/bottom-right',
			'before_title'  => ['woocommerce_before_shop_loop_item_title', 13],
			'after_title'   => ['woocommerce_after_shop_loop_item_title', 10],
			'after_price'   => ['woocommerce_after_shop_loop_item_title', 11],
			'after_content' => ['woocommerce_after_shop_loop_item', 999],
		];

		// Basic & Cards have the hover effects
		if( in_array($this->_settings['loop_skin'], ['basic', 'cards']) ){
			$positions['after_price'] = $positions['after_content'];
		}
		else {

			// get price hooks based on item skin
			$price_hook = ReyCore_WooCommerce_Loop::getInstance()->get_default_component_hooks('prices');

			if( ! empty($price_hook) ){
				$positions['after_price'] = [
					$price_hook['tag'],
					$price_hook['priority'] + 1,
				];
			}
		}

		foreach ($positions as $name => $hook) {

			if( is_array($hook) ){
				$hook_position = $hook[0];
				$hook_priority = $hook[1];
			}
			else {
				$hook_position = $hook;
				$hook_priority = 10;
			}

			$method = 'add_action';

			if( ! $add ){
				$method = 'remove_action';
			}

			if( method_exists($this, "render_extra_data__{$name}") ){
				call_user_func( $method, $hook_position, [ $this, "render_extra_data__{$name}"], $hook_priority );
			}
		}
	}

	function render_extra_data__top_left(){
		$this->render_extra_data('top_left');
	}

	function render_extra_data__top_right(){
		$this->render_extra_data('top_right');
	}

	function render_extra_data__bottom_left(){
		$this->render_extra_data('bottom_left');
	}

	function render_extra_data__bottom_right(){
		$this->render_extra_data('bottom_right');
	}

	function render_extra_data__before_title(){
		$this->render_extra_data('before_title');
	}

	function render_extra_data__after_price(){
		$this->render_extra_data('after_price');
	}

	function render_extra_data__after_title(){
		$this->render_extra_data('after_title');
	}

	function render_extra_data__after_content(){
		$this->render_extra_data('after_content');
	}

	function render_extra_data( $position ){

		if( ! ($extra_data = $this->_settings['extra_data']) ){
			return;
		}

		foreach( $extra_data as $edata ){

			if( ! ($component = $edata['component']) ){
				continue;
			}

			if( $edata['position'] === '' ){
				continue;
			}

			if( $edata['position'] !== $position ){
				continue;
			}

			if( method_exists($this, "render_extra_data_item__{$component}") ){

				$classes[] = 'rey-peItem';
				$classes[] = 'elementor-repeater-item-' . esc_attr($edata['_id']);
				$classes[] = 'rey-pe-' . esc_attr($component);
				$classes[] = '--pos-' . esc_attr($position);

				// if yes show on mobiles
				if( $edata['mobile'] === '' ){
					$classes[] = '--dnone-mobile';
				}

				$title = isset($edata['title']) && ! empty($edata['title']) ? $edata['title'] : '';

				call_user_func([$this, "render_extra_data_item__{$component}"], [
					'data' => $edata,
					'class' => implode(' ', $classes),
					'title' => $title ? "<span class='rey-peItem-title'>{$title}</span>" : '',
				]);
			}

		}
	}

	function render_extra_data_item__acf( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($product = wc_get_product()) ){
			return;
		}

		if( ! ($acf_field = $args['data']['acf_field']) ){
			return;
		}

		$parts = explode(':', $acf_field);

		if( ! ($field_val = get_field($parts[0])) ){
			return;
		}

		if( 'text' === $args['data']['acf_display'] ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $field_val, $args['class']);
		}

		elseif( 'image' === $args['data']['acf_display'] && is_array($field_val) && isset($field_val['id']) ){
			$thumb_size = apply_filters('reycore/woocommerce/products/extra_data/acf_image_size', 'medium');
			$img = str_replace('width="1" height="1"', '', wp_get_attachment_image($field_val['id'], $thumb_size) );
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $img, $args['class']);
		}

	}

	function render_extra_data_item__sku( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ($product = wc_get_product()) && $sku = $product->get_sku() ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $sku, $args['class']);
		}
	}

	function render_extra_data_item__dimensions( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ($product = wc_get_product()) && $dimensions = wc_format_dimensions( $product->get_dimensions( false ) ) ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $dimensions, $args['class'] );
		}
	}

	function render_extra_data_item__weight( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ($product = wc_get_product()) && $weight = wc_format_weight( $product->get_weight() ) ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $weight, $args['class'] );
		}
	}

	function render_extra_data_item__stock( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($product = wc_get_product()) ){
			return;
		}

		if( ! $product->managing_stock() ){
			return;
		}

		if( ! ($stock_quantity = $product->get_stock_quantity()) ){
			return;
		}

		$stock_text = sprintf($args['data']['stock_text'], $stock_quantity);
		$stock_html = sprintf( '<div class="%2$s">%1$s</div>', $args['title'] . $stock_text, $args['class'] );

		echo apply_filters('reycore/woocommerce/products/extra_data/stock', $stock_html, $args, $stock_quantity);
	}

	function render_extra_data_item__placeholder( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($placeholder_hook = $args['data']['placeholder_hook']) ){
			return;
		}

		echo apply_filters("reycore/woocommerce/products/extra_data/placeholder={$placeholder_hook}", '', $args);
	}

	function render_extra_data_item__attribute( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($attribute = $args['data']['attribute']) ){
			return;
		}

		if( ! ($product = wc_get_product()) ){
			return;
		}

		$attribute_name = wc_attribute_taxonomy_name( $attribute );

		if ( ! taxonomy_exists( $attribute_name ) ) {
			return;
		}

		$attribute_taxonomy = get_taxonomy($attribute_name);

		$attribute_values   = wc_get_product_terms( $product->get_id(), $attribute_name, ['fields' => 'all'] );

		$values = [];

		foreach ( $attribute_values as $attribute_value ) {
			$value_name = esc_html( $attribute_value->name );

			if ( isset($attribute_taxonomy->attribute_public) && $attribute_taxonomy->attribute_public ) {
				$values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute_name ) ) . '" rel="tag">' . $value_name . '</a>';
			} else {
				$values[] = $value_name;
			}
		}

		if( empty($values) ){
			return;
		}

		if( $attrs = wptexturize( implode( ', ', $values ) ) ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . "<span>{$attrs}</span>", $args['class'] );
		}
	}

	function set_loop_skin($mod){

		if( isset($this->_settings['loop_skin']) && $loop_skin = $this->_settings['loop_skin']){
			return $loop_skin;
		}

		return $mod;
	}


	function atc_button_style($mod){

		if( isset(self::$btn_styles['atc']) && $style = self::$btn_styles['atc'] ){
			return $style;
		}

		return $mod;
	}

	function qv_button_style($mod){

		if( isset(self::$btn_styles['quickview']) && $style = self::$btn_styles['quickview'] ){
			return $style;
		}

		return $mod;
	}

	function proto_loop_padded($mod){

		if( isset($this->_settings['proto_loop_padded']) && $padded = $this->_settings['proto_loop_padded'] ){
			return $padded === 'yes';
		}

		return $mod;
	}

	function after_products(){

		remove_filter( 'theme_mod_loop_skin', [$this, 'set_loop_skin'] );
		remove_filter( 'theme_mod_loop_add_to_cart_style', [$this, 'atc_button_style'] );
		remove_filter( 'theme_mod_loop_quickview_style', [$this, 'qv_button_style'] );

		$this->add_remove_extra_data(false);

	}

	public static function add_skin_controls__basic( $element ){

		$element->start_controls_section(
			'section_basic_skin_styles',
			[
				'label' => __( 'Basic Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'basic',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'basic_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'basic_content_inner_padding',
				[
					'label' => esc_html__( 'Content Inner Padding', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-basic-padding: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'basic_border_color',
				[
					'label' => esc_html__( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products.--skin-basic' => '--woocommerce-loop-basic-bordercolor: {{VALUE}}',
					],
					'condition' => [
						'gaps' => 'no',
					],
				]
			);

			$element->add_control(
				'basic_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-basic-bgcolor: {{VALUE}}',
					],
				]
			);


		$element->end_controls_section();
	}

	public static function add_skin_controls__wrapped( $element ){

		$element->start_controls_section(
			'section_wrapper_styles',
			[
				'label' => __( 'Wrapper Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'wrapped',
					'_skin' => '',
				],
			]
		);

		$element->add_control(
			'wrapped_hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'yes'  => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
			]
		);

		$element->add_responsive_control(
			'wrapped_inner_padding',
			[
				'label' => __( 'Inner Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-loopWrapper-details' => 'bottom: {{BOTTOM}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-new-badge' => 'top: {{TOP}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'.rtl {{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-loopWrapper-details' => 'bottom: {{BOTTOM}}{{UNIT}}; right: {{LEFT}}{{UNIT}}; left: auto;',
					'.rtl {{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-new-badge' => 'top: {{TOP}}{{UNIT}}; right: {{LEFT}}{{UNIT}}; left: auto;',
				],
			]
		);

		$element->start_controls_tabs('wrapped_colors_tabs');

			$element->start_controls_tab(
				'wrapped_colors_active',
				[
					'label' => __( 'Active', 'rey-core' ),
			]);
			// Active
			$element->add_control(
				'wrapped_text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-wc-skin--wrapped, {{WRAPPER}} .rey-wc-skin--wrapped a {{WRAPPER}} .rey-wc-skin--wrapped a:hover, {{WRAPPER}} .rey-wc-skin--wrapped .button, {{WRAPPER}} .rey-wc-skin--wrapped .reyEl-productGrid-cs-dots' => 'color: {{VALUE}}',
					],
				]
			);
			$element->add_control(
				'wrapped_overlay_color',
				[
					'label' => __( 'Overlay Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product.rey-wc-skin--wrapped .woocommerce-loop-product__link:after' => 'background-color: {{VALUE}}',
					],
				]
			);
			$element->end_controls_tab();

			$element->start_controls_tab(
				'wrapped_colors_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
			]);
			// Hover
			$element->add_control(
				'wrapped_text_color_hover',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-wc-skin--wrapped a:hover, {{WRAPPER}} .rey-wc-skin--wrapped .button' => 'color: {{VALUE}}',
					],
				]
			);
			$element->add_control(
				'wrapped_overlay_color_hover',
				[
					'label' => __( 'Overlay Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product.rey-wc-skin--wrapped:hover .woocommerce-loop-product__link:after' => 'background-color: {{VALUE}}',
					],
				]
			);
			$element->end_controls_tab();
		$element->end_controls_tabs();

		$element->end_controls_section();
	}


	public static function add_skin_controls__cards( $element ){

		$element->start_controls_section(
			'section_cards_skin_styles',
			[
				'label' => __( 'Cards Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'cards',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'cards_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'cards_content_inner_padding',
				[
					'label' => esc_html__( 'Content Inner Padding', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-cards-padding: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'cards_border_color',
				[
					'label' => esc_html__( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products.--skin-cards' => '--woocommerce-loop-cards-bordercolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'cards_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-cards-bgcolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'cards_expand_thumbs',
				[
					'label' => esc_html__( 'Expand Thumbnails', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'cards_corner_radius',
				[
					'label' => esc_html__( 'Corner radius', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products.--skin-cards' => '--skin-cards-border-radius: {{VALUE}}px',
					],
				]
			);

		$element->end_controls_section();
	}

	public static function add_skin_controls__iconized( $element ){

		$element->start_controls_section(
			'section_iconized_skin_styles',
			[
				'label' => __( 'Iconized Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'iconized',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'iconized_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'iconized_content_inner_padding',
				[
					'label' => esc_html__( 'Content Inner Padding', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-padding: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'iconized_border_size',
				[
					'label' => esc_html__( 'Border Size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-size: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'iconized_border_color',
				[
					'label' => esc_html__( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-bordercolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'iconized_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-bgcolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'iconized_corner_radius',
				[
					'label' => esc_html__( 'Corner radius', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-radius: {{VALUE}}px',
					],
				]
			);

		$element->end_controls_section();
	}

	public static function add_skin_controls__proto( $element ){

		$element->start_controls_section(
			'section_proto_skin_styles',
			[
				'label' => __( 'Proto Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'proto',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'proto_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'proto_text_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-proto' => '--woocommerce-loop-proto-color: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'proto_link_color',
				[
					'label' => esc_html__( 'Link Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-proto' => '--woocommerce-loop-proto-color-link: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'proto_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-proto' => '--woocommerce-loop-proto-bgcolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'proto_loop_padded',
				[
					'label' => esc_html__( 'Inner Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'proto_loop_shadow',
				[
					'label' => esc_html__( 'Box Shadow', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'no' => esc_html__( 'Disabled', 'rey-core' ),
						'1' => esc_html__( 'Level 1', 'rey-core' ),
						'2' => esc_html__( 'Level 2', 'rey-core' ),
						'3' => esc_html__( 'Level 3', 'rey-core' ),
						'4' => esc_html__( 'Level 4', 'rey-core' ),
						'5' => esc_html__( 'Level 5', 'rey-core' ),
					],
					'condition' => [
						'proto_loop_padded' => 'yes',
					],
				]
			);
			$element->add_control(
				'proto_loop_shadow_hover',
				[
					'label' => esc_html__( 'Hover Box Shadow', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'no' => esc_html__( 'Disabled', 'rey-core' ),
						'1' => esc_html__( 'Level 1', 'rey-core' ),
						'2' => esc_html__( 'Level 2', 'rey-core' ),
						'3' => esc_html__( 'Level 3', 'rey-core' ),
						'4' => esc_html__( 'Level 4', 'rey-core' ),
						'5' => esc_html__( 'Level 5', 'rey-core' ),
					],
					'condition' => [
						'proto_loop_padded' => 'yes',
					],
				]
			);


		$element->end_controls_section();
	}

	function lazy_start(){

		$is_ajax_request = (isset($_REQUEST['action']) && 'reycore_element_lazy' === reycore__clean($_REQUEST['action']));

		if( $is_ajax_request ){
			reyCoreAssets()->collect_start();
		}

		// Initial Load (not Ajax)
		if( '' !== $this->_settings['lazy_load'] &&
			'yes' !== $this->_settings['paginate'] &&
			! wp_doing_ajax() &&
			! ( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) ){

			$qid = isset($GLOBALS['global_section_id']) ? $GLOBALS['global_section_id'] : get_queried_object_id();

			$config = [
				'element_id' => $this->_args['el_instance']->get_id(),
				'skin' => $this->_settings['_skin'],
				'trigger' => $this->_settings['lazy_load_trigger'] ? $this->_settings['lazy_load_trigger'] : 'scroll',
				'qid' => apply_filters('reycore/elementor/product_grid/lazy_load_qid', $qid),
				'options' => apply_filters('reycore/elementor/product_grid/lazy_load_options', [
					'prevent_ba_content' => 'yes',
					'prevent_stretched' => 'yes'
				])
			];

			if( 'click' === $this->_settings['lazy_load_trigger'] ){
				$config['trigger__click'] = $this->_settings['lazy_load_click_trigger'];
			}

			$this->_args['el_instance']->add_render_attribute( '_wrapper', 'data-lazy-load', wp_json_encode( $config ) );

			if( $this->_settings['_skin'] === 'carousel' ){
				$per_row = $this->_settings['slides_to_show'];
				$per_row_tablet = $this->_settings['slides_to_show_tablet'];
				$per_row_mobile = $this->_settings['slides_to_show_mobile'];
			}
			else {
				$per_row = $this->_settings['per_row'];
				$per_row_tablet = $this->_settings['per_row_tablet'];
				$per_row_mobile = $this->_settings['per_row_mobile'];
			}

			printf('<div class="__placeholders %4$s" style="--cols: %1$d; --cols-tablet: %2$d; --cols-mobile: %3$d;">',
				absint($per_row),
				($per_row_tablet ? absint($per_row_tablet) : reycore_wc_get_columns('tablet')),
				($per_row_mobile ? absint($per_row_mobile) : reycore_wc_get_columns('mobile')),
				( isset($this->_args['placeholder_class']) ? $this->_args['placeholder_class'] : '' ) );

				$count = $this->_settings['_skin'] !== 'carousel' ? $this->_settings['limit'] : $per_row;

				for( $i = 0; $i < absint($count); $i++ ){
					echo '<div class="__placeholder-item"><div class="__placeholder-thumb"></div><div class="__placeholder-title"></div><div class="__placeholder-subtitle"></div></div>';
				}

			echo '</div>';

			$scripts = ['reycore-elementor-elem-lazy-load'];

			if( wc_get_loop_prop( 'entry_animation' ) !== false || 'scroll' === $this->_settings['lazy_load_trigger'] ){
				$scripts[] = 'scroll-out';
			}

			if( ! empty($scripts) ){
				reyCoreAssets()->add_scripts($scripts);
			}

			do_action('reycore/elementor/product_grid/lazy_load_assets', $this->_settings);

			return true;
		}

		return false;
	}

	function lazy_end(){

		if( ! (isset($_REQUEST['action']) && 'reycore_element_lazy' === reycore__clean($_REQUEST['action'])) ){
			return;
		}

		$collected_assets = reyCoreAssets()->collect_end(true);

		if( !empty($collected_assets) ){
			printf( "<div data-assets='%s'></div>", wp_json_encode($collected_assets) );
		}

	}


	function ajax_load_more(){

		if( (isset($this->_settings['ajax_load_more']) && $this->_settings['ajax_load_more'] !== 'yes') ){
			return;
		}

		if( $this->_settings['paginate'] === 'yes' ){
			return;
		}

		if( ! ($this->_settings['_skin'] === '' || $this->_settings['_skin'] === 'mini') ){
			return;
		}

		$button_text = esc_html__('LOAD MORE', 'rey-core');

		$classes = [
			'style' => 'btn-line-active'
		];

		if( $custom_text = $this->_settings['ajax_load_more_text'] ){
			$button_text = $custom_text;
		}

		if( $btn_style = $this->_settings['ajax_load_more_btn_style'] ){
			$classes['style'] = $btn_style;
		}

		$limit = $this->_settings['ajax_load_more_limit'] ? $this->_settings['ajax_load_more_limit'] : $this->_settings['per_row'];

		if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) {
			$classes[] = '--disabled';
		}

		$qid = isset($GLOBALS['global_section_id']) ? $GLOBALS['global_section_id'] : get_queried_object_id();

		if( isset($_REQUEST['action']) && 'reycore_element_lazy' === reycore__clean($_REQUEST['action']) ){
			$qid = absint($_REQUEST['qid']);
		}

		$config = [
			'element_id' => $this->_args['el_instance']->get_id(),
			'skin' => $this->_settings['_skin'],
			'qid' => apply_filters('reycore/elementor/product_grid/load_more_qid', $qid),
			'options' => apply_filters('reycore/elementor/product_grid/load_more_options', [
				'prevent_ba_content' => 'yes',
				'prevent_stretched' => 'yes'
			]),
			'limit' => absint($limit),
			'max' => $this->_settings['ajax_load_more_max'] ? absint($this->_settings['ajax_load_more_max']) : 1
		];

		printf('<div class="rey-pg-loadmoreWrapper"><button class=\'btn rey-pg-loadmore %3$s\' data-config=\'%2$s\'><span class="rey-pg-loadmoreText">%1$s</span><div class="rey-lineLoader"></div></button></div>',
			$button_text,
			wp_json_encode( $config ),
			implode(' ', $classes)
		);

	}
}

endif;
