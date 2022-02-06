<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if(!function_exists('reycore_wc__is_catalog')):
/**
 * Check if store is catalog
 *
 * @since 1.2.0
 **/
function reycore_wc__is_catalog()
{
	return get_theme_mod('shop_catalog', false) === true;
}
endif;

if(!function_exists('reycore_wc__get_discount')):
	/**
	 * Get product discount percentage
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__get_discount( $product = false, $percentage = true ){

		if( ! $product ){
			global $product;
		}

		if( ! $product ){
			$product = wc_get_product();
		}

		if ( ! ( $product && ($product->is_on_sale() || $product->is_type( 'grouped' )) ) ) {
			return;
		}

		$get_discount = false;

		if( $cache_discounts = apply_filters('reycore/woocommerce/cache_discounts', true) ){
			$transient_name = ($percentage ? '_rey__discount_percentage_' : '_rey__discount_save_') . $product->get_id();
			$get_discount = get_transient($transient_name);
		}

		if ( false === $get_discount ) {

			$discount = 0;

			if ( in_array($product->get_type(), ['simple', 'external', 'variation'], true) ) {

				if( $sale_price = apply_filters('reycore/woocommerce/discount_labels/sale_price', $product->get_sale_price(), $product) ){
					if( $percentage ){
						$discount = ( ( $product->get_regular_price() - $sale_price ) / $product->get_regular_price() ) * 100;
					}
					else {
						$discount = $product->get_regular_price() - $sale_price;
					}
				}

			}

			elseif ( $product->is_type( 'grouped' ) ) {

				$perc_discount = 0;

				foreach ( $product->get_children() as $_product_id ) {

					$_product = wc_get_product( $_product_id );

					if( ! $_product ){
						continue;
					}

					if( ! $percentage ){
						$discount += reycore_wc__get_discount($_product, false);
					}
					else {

						$perc_discount = reycore_wc__get_discount($_product, true);

						if ( $perc_discount > $discount ) {
							$discount = $perc_discount;
						}

					}

				}

			}

			elseif ( $product->is_type( 'variable' ) ) {

				foreach ( $product->get_children() as $_product_id ) {

					$_product = wc_get_product( $_product_id );

					if( ! $_product ){
						continue;
					}

					if( ! $_product->is_on_sale() ) {
						continue;
					}

					$price = $_product->get_regular_price();
					$sale = apply_filters('reycore/woocommerce/discount_labels/sale_price', $_product->get_sale_price(), $_product);

					if ( $price != 0 && ! empty( $sale ) ) {

						if( $percentage ){
							// show the biggest
							$perc = ( $price - $sale ) / $price * 100;
							if ( $perc > $discount ) {
								$discount = $perc;
							}
						}
						else {
							// show the biggest
							$save = $price - $sale;
							if ( $save > $discount ) {
								$discount = $save;
							}
						}
					}
				}
			}

			// Format price for "Sale $$"
			if( ! $percentage ){

				$sale_discount_args = [
					'decimals' => wc_get_price_decimals(),
					'decimals_separator' => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
				];

				$get_discount = apply_filters('reycore/woocommerce/discounts/sale_price_format', number_format( absint( $discount ), $sale_discount_args['decimals'], $sale_discount_args['decimals_separator'], $sale_discount_args['thousand_separator'] ), $discount, $sale_discount_args);

			}
			else {
				$get_discount = absint( round($discount) );
			}

			if( $cache_discounts ){
				set_transient($transient_name, $get_discount, MONTH_IN_SECONDS);
			}
		}

		return $get_discount;
	}
endif;



if(!function_exists('reycore_wc__get_discount_percentage_html')):
	/**
	 * Get the Discount percentage HTML markup
	 *
	 * @since 1.9.0
	 */
	function reycore_wc__get_discount_percentage_html($text = ''){
		if( $discount = reycore_wc__get_discount() ){
			return sprintf( __('<span class="rey-discount">-%d%% %s</span>', 'rey-core'), $discount, $text );
		}
	}
endif;

if(!function_exists('reycore_wc__get_discount_save_html')):
	/**
	 * Get the Discount "Save difference" HTML markup
	 *
	 * @since 1.9.0
	 */
	function reycore_wc__get_discount_save_html(){
		if( $discount = reycore_wc__get_discount(false, false) ){

			$currency_symbol = get_woocommerce_currency_symbol();
			$currency_position = get_option('woocommerce_currency_pos');
			$before = $after = '';

			if ($currency_position === 'left') {
				$before = $currency_symbol;
			} elseif ($currency_position === 'left_space') {
				$before = $currency_symbol . ' ';
			} elseif ($currency_position === 'right') {
				$after = $currency_symbol;
			} elseif ($currency_position === 'right_space') {
				$after = ' ' . $currency_symbol;
			}

			return sprintf( '<span class="rey-discount">%s %s</span>',
				get_theme_mod('loop_sale__save_text', esc_html_x('Save', 'rey-core')),
				$before . $discount . $after
			);
		}
	}
endif;


if(!function_exists('reycore_wc__reset_filters_link')):
	/**
	 * Get link for resetting filters
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__reset_filters_link()
	{
		$link = '';

		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_shop() ) {
			$link = get_permalink( wc_get_page_id( 'shop' ) );
		} elseif ( is_product_category() && $cat = get_query_var( 'product_cat' ) ) {
			$link = get_term_link( $cat, 'product_cat' );
		} elseif ( is_product_tag() && $tag = get_query_var( 'product_tag' ) ) {
			$link = get_term_link( $tag, 'product_tag' );
		} else {
			$queried_object = get_queried_object();
			if( is_object($queried_object) && isset($queried_object->slug) && !empty($queried_object->slug) ){
				$link = get_term_link( $queried_object->slug, $queried_object->taxonomy );
			}
		}

		/**
		 * Search Arg.
		 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
		 */
		if ( get_search_query() ) {
			$link = add_query_arg( 's', rawurlencode( wp_specialchars_decode( get_search_query() ) ), $link );
		}

		// Post Type Arg
		if ( isset( $_GET['post_type'] ) ) {
			$link = add_query_arg( 'post_type', wc_clean( wp_unslash( $_GET['post_type'] ) ), $link );
		}

		return esc_url( apply_filters('reycore/woocommerce/reset_filters_link', $link) );
	}
endif;


if(!function_exists('reycore_wc__check_filter_panel')):
	/**
	 * Check if panel filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__check_filter_panel()
	{
		return is_active_sidebar( 'filters-sidebar' );
	}
endif;


if(!function_exists('reycore_wc__check_filter_sidebar_top')):
	/**
	 * Check if top sidebar filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__check_filter_sidebar_top()
	{
		return is_active_sidebar( 'filters-top-sidebar' );
	}
endif;


if(!function_exists('reycore_wc__check_shop_sidebar')):
	/**
	 * Check if shop sidebar filter is enabled
	 *
	 * @since 1.5.0
	 **/
	function reycore_wc__check_shop_sidebar()
	{
		return apply_filters('reycore/woocommerce/check_shop_sidebar', is_active_sidebar( 'shop-sidebar' ) );
	}
endif;


if(!function_exists('reycore_wc__get_active_filters')):
	/**
	 * Check if panel filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__get_active_filters()
	{
		return apply_filters('reycore/woocommerce/get_active_filters', 0);
	}
endif;


/**
 * Load custom WooCommerce templates
 *
 * @since 1.0.0
 */
add_filter('wc_get_template', function( $template, $template_name ){

	$overrides = apply_filters('reycore/woocommerce/wc_get_template', [
		[
			// Loop - Pagination
			'template_name' => 'loop/pagination.php',
			'template' => sprintf( 'template-parts/woocommerce/loop-pagination-%s.php', get_theme_mod('loop_pagination', 'paged') )
		],
		[
			// Product page - Blocks
			'template_name' => 'single-product/tabs/tabs.php',
			'template' => sprintf('template-parts/woocommerce/single-%s.php', get_theme_mod('product_content_layout', 'blocks'))
		],
		[
			// Order by select list
			'template_name' => 'loop/orderby.php',
			'template' => 'template-parts/woocommerce/loop-orderby.php'
		],
		[
			// Results counts
			'template_name' => 'loop/result-count.php',
			'template' => 'template-parts/woocommerce/loop-result-count.php'
		],
		[
			// Price lopp
			'template_name' => 'loop/price.php',
			'template' => 'template-parts/woocommerce/loop-price.php'
		],
		[
			// Mini Cart
			'template_name' => 'cart/mini-cart.php',
			'template' => 'template-parts/woocommerce/cart/mini-cart.php'
		],
		[
			// Single product - Meta
			'template_name' => 'single-product/meta.php',
			'template' => 'template-parts/woocommerce/single-meta.php'
		],
		[
			// Single product - Variation Data
			'template_name' => 'single-product/add-to-cart/variation.php',
			'template' => 'template-parts/woocommerce/single-variation-data.php'
		],
		[
			// Single product image
			'template_name' => 'single-product/product-image.php',
			'template' => 'template-parts/woocommerce/single-product-image.php'
		],
		[
			// Simple ATC Button
			'template_name' => 'single-product/add-to-cart/simple.php',
			'template' => 'template-parts/woocommerce/single-simple-add-to-cart-button.php'
		],
		[
			// Variable ATC Button
			'template_name' => 'single-product/add-to-cart/variation-add-to-cart-button.php',
			'template' => 'template-parts/woocommerce/single-variation-add-to-cart-button.php'
		],

	], $template_name);

	foreach ($overrides as $key => $override ) {

		if( $template_name === $override['template_name'] ){
			if ( file_exists( STYLESHEETPATH . '/' . $override['template'] ) ) {
				$template = STYLESHEETPATH . '/' . $override['template'];
			}
			elseif ( file_exists( TEMPLATEPATH . '/' . $override['template'] ) ) {
				$template = TEMPLATEPATH . '/' . $override['template'];
			}
			elseif ( file_exists( REY_CORE_DIR . $override['template'] ) ) {
				$template = REY_CORE_DIR . $override['template'];
			}
		}
	}

	return $template;

}, 10, 2);

/**
 * Load custom WooCommerce template part
 *
 * @since 1.0.0
 */
add_filter('wc_get_template_part', function( $template, $slug, $name ){

	$templates = [];

	// Loop - Content Product
	if( $slug === 'content' && $name === 'product' ){
		$templates[] = 'template-parts/woocommerce/content-product.php';
	}

	$templates = apply_filters('reycore/woocommerce/wc_get_template_part', $templates, $template, $slug, $name);

	foreach ($templates as $tpl) {
		if ( file_exists( STYLESHEETPATH . '/' . $tpl ) ) {
			$template = STYLESHEETPATH . '/' . $tpl;
		}
		elseif ( file_exists( TEMPLATEPATH . '/' . $tpl ) ) {
			$template = TEMPLATEPATH . '/' . $tpl;
		}
		elseif ( file_exists( REY_CORE_DIR . $tpl ) ) {
			$template = REY_CORE_DIR . $tpl;
		}
	}

	return $template;

}, 10, 3);


if(!function_exists('reycore_wc_get_columns')):
	/**
	 * Get Enabled WooCommerce Components
	 */
	function reycore_wc_get_columns( $device = '' ){

		$devices = apply_filters('reycore/woocommerce/columns', [
			'desktop' => wc_get_default_products_per_row(),
			'tablet' => get_theme_mod('woocommerce_catalog_columns_tablet', 2),
			'mobile' => get_theme_mod('woocommerce_catalog_columns_mobile', 2)
		]);

		if( isset($devices[$device]) ){
			return absint($devices[$device]);
		}

		return $devices;
	}
endif;


if(!function_exists('reycore_wc_get_loop_components')):
	/**
	 * Get Enabled WooCommerce Components
	 */
	function reycore_wc_get_loop_components( $component = '' ){

		$components = apply_filters('reycore/loop_components', [
			// items components
			'result_count' => get_theme_mod('loop_product_count', true),
			'catalog_ordering' => true,
			'title' => true,
			'ratings' => get_theme_mod('loop_ratings', '2') == '1',
			'quickview' => [
				'bottom' => get_theme_mod('loop_quickview', '1') != '2' && get_theme_mod('loop_quickview_position', 'bottom') === 'bottom',
				'topright' => get_theme_mod('loop_quickview', '1') != '2' && get_theme_mod('loop_quickview_position', 'bottom') === 'topright',
				'bottomright' => get_theme_mod('loop_quickview', '1') != '2' && get_theme_mod('loop_quickview_position', 'bottom') === 'bottomright',
			],
			'brands' => get_theme_mod('loop_show_brads', '1'),
			'category' => get_theme_mod('loop_show_categories', '2') == '1',
			'excerpt' => get_theme_mod('loop_short_desc', '2') == '1',
			'wishlist' => reycore_wc__check_wishlist(),
			'prices' => get_theme_mod('loop_show_prices', '1') == '1',
			'thumbnails' => true,
			'thumbnails_second' => true,
			'add_to_cart' => get_theme_mod('loop_add_to_cart', true) === true,
			'variations' => get_theme_mod('woocommerce_loop_variation', 'disabled') !== 'disabled',
			'new_badge' => get_theme_mod('loop_new_badge', '1') === '1',
			'sold_out_badge' => get_theme_mod('loop_sold_out_badge', '1') !== '2',
			'featured_badge' => get_theme_mod('loop_featured_badge', 'hide') !== 'hide',
			'discount' => [
				'top' => get_theme_mod('loop_show_sale_label', 'percentage') !== '',
				'price' => get_theme_mod('loop_show_sale_label', 'percentage') !== '' && get_theme_mod('loop_discount_label', 'price') === 'price',
			]
		]);

		if( isset($components[$component]) ){
			return $components[$component];
		}

		return $components;
	}
endif;


if(!function_exists('reycore_wc__check_wishlist')):
	/**
	 * Determine wishlist
	 *
	 * @since 1.7.0
	 **/
	function reycore_wc__check_wishlist()
	{
		if( class_exists('TInvWL_Public_AddToWishlist') ){
			return true;
		}

		return get_theme_mod('wishlist__enable', true);
	}
endif;


if(!function_exists('reycore_wc__check_filter_btn')):
	/**
	 * Check if filter button should be added
	 *
	 * @since 1.9.0
	 **/
	function reycore_wc__check_filter_btn()
	{
		if( $custom_sidebar = apply_filters('reycore/woocommerce/filter_button/custom_sidebar', '') ){
			return $custom_sidebar;
		}

		if( $mobile_btn = get_theme_mod('ajaxfilter_mobile_button_opens', '') ){
			return $mobile_btn;
		}

		if( reycore_wc__check_filter_panel() ){
			return 'filters-sidebar';
		}

		else if( reycore_wc__check_shop_sidebar() && get_theme_mod('ajaxfilter_shop_sidebar_mobile_offcanvas', true) ){
			return 'shop-sidebar';
		}

		else if( reycore_wc__check_filter_sidebar_top() ){
			return 'filters-top-sidebar';
		}

		return false;
	}
endif;


if(!function_exists('reycore_wc__append_loop_components_status')):
/**
 * Append loop components status in INIT.
 * If added dirrectly in `reycore_wc_get_loop_components`, will throw an error
 * because the sidebar needs to check after init.
 *
 * @since 1.3.0
 **/
function reycore_wc__append_loop_components_status()
{
	add_filter('reycore/loop_components', function($components){

		// loop components
		$components['view_selector'] = get_theme_mod('loop_view_switcher', '1') == '1';
		$components['filter_button'] = reycore_wc__check_filter_btn() !== false;
		$components['filter_top_sidebar'] = reycore_wc__check_filter_sidebar_top();

		if( class_exists('ReyCore_WooCommerce_Wishlist') && reycore_wc__check_wishlist() ):
			$wishlist_pos = ReyCore_WooCommerce_Wishlist::catalog_default_position();
			$components['wishlist'] = [
				'bottom' => $wishlist_pos === 'bottom',
				'topright' => $wishlist_pos === 'topright',
				'bottomright' => $wishlist_pos === 'bottomright'
			];
		endif;

		return $components;
	});
}
add_action('init', 'reycore_wc__append_loop_components_status');
endif;


if(!function_exists('reycore_wc__check_downloads_endpoint')):
	/**
	 * Check if downloads endpoint is disabled
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__check_downloads_endpoint() {
		return get_option('woocommerce_myaccount_downloads_endpoint') != '';
	}
endif;


if(!function_exists('reycore_wc__count_downloads')):
	/**
	 * Get downloads count and store in transient
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__count_downloads( $user_id ) {

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		$transient_name = "rey-wc-user-dld-{$user_id}";

		if ( false === ( $downloads_count = get_transient( $transient_name ) ) ) {

			if( isset(WC()->customer) ){
				$customer = WC()->customer;
			}
			else {
				$customer = new WC_Customer( $user_id );
			}

			$downloads_count = $customer->get_downloadable_products();
			set_transient( $transient_name, $downloads_count, HOUR_IN_SECONDS );
		}

		return $downloads_count;
	}
endif;


if(!function_exists('reycore_wc__count_orders')):
	/**
	 * Get orders count and store in transient
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__count_orders( $user_id ) {

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		$transient_name = "rey-wc-user-order-{$user_id}";

		if ( false === ( $orders_count = get_transient( $transient_name ) ) ) {
			$orders_count = wc_get_customer_order_count($user_id);
			set_transient( $transient_name, $orders_count, HOUR_IN_SECONDS );
		}

		return $orders_count;
	}
endif;

if(!function_exists('reycore_wc__account_counters_reset')):
	/**
	 * Reset orders count transients
	 *
	 * @since 1.6.3
	 */
	function reycore_wc__account_counters_reset( $order_id ) {
		if( ($order = wc_get_order( $order_id )) && ($user_id = $order->get_user_id()) ){
			delete_transient("rey-wc-user-order-{$user_id}");
			delete_transient("rey-wc-user-dld-{$user_id}");
		}
	}
	add_action( 'woocommerce_delete_shop_order_transients', 'reycore_wc__account_counters_reset' );
endif;


if(!function_exists('reycore_wc__cache_discounts_refresh')):
/**
 * Refresh discounted products meta
 *
 * @since 1.5.0
 **/
function reycore_wc__cache_discounts_refresh( $post_id )
{

	if( $post_id > 0 ){
		delete_transient( '_rey__discount_percentage_' . $post_id );
		delete_transient( '_rey__discount_save_' . $post_id );
		return;
	}

	$products_on_sale = wc_get_product_ids_on_sale();

	foreach($products_on_sale as $product_id){
		delete_transient( '_rey__discount_percentage_' . $product_id );
		delete_transient( '_rey__discount_save_' . $product_id );
	}
}
add_action('woocommerce_delete_product_transients', 'reycore_wc__cache_discounts_refresh');
add_action('woocommerce_update_product', 'reycore_wc__cache_discounts_refresh');

endif;


if(!function_exists('reycore_wc__product_categories')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__product_categories( $args = [] ){

		$args = wp_parse_args($args, [
			'hide_empty' => true,
			'parent' => false,
			'labels' => false,
			'hierarchical' => false,
			'orderby' => 'term_id',
			'extra_item' => [],
			'field' => 'slug'
		]);

		$terms_args = [
			'taxonomy'   => 'product_cat',
			'hide_empty' => $args['hide_empty'],
			'orderby' => $args['orderby'], // 'name', 'term_id', 'term_group', 'parent', 'menu_order', 'count'
			'update_term_meta_cache'	=> false,
		];

		// if parent only
		if( $args['parent'] === 0 ){
			$terms_args['parent'] = 0;
		}
		// if subcategories
		elseif( $args['parent'] !== 0 && $args['parent'] !== false ){

			// if automatic
			if( $args['parent'] === '' ) {
				$parent_term = get_queried_object();
			}
			// if pre-defined parent category
			else {
				$parent_term = get_term_by('slug', $args['parent'], 'product_cat');
			}

			if(is_object($parent_term) && isset($parent_term->term_id) ) {
				$terms_args['parent'] = $parent_term->term_id;
			}
		}

		$terms = reyCoreHelper()->get_terms( $terms_args );
		// $terms = get_terms( $terms_args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			$options = $args['extra_item'];
			foreach ( $terms as $term ) {

				$term_name = wp_specialchars_decode($term->name);

				if( $args['labels'] === true && isset($term->parent) && $term->parent === 0 ){
					$term_name = sprintf('%s (%s)', $term_name, esc_html__('Parent Category', 'rey-core'));
				}

				$parents_symbol = '';

				if( $args['hierarchical'] ){

					$ancestors = get_ancestors($term->term_id, $term->taxonomy);
					$ancestors = array_reverse($ancestors);

					foreach ($ancestors as $key => $anc) {
						$parents_symbol .= get_term( $anc )->name . ' > ';
					}

					$term_name = $parents_symbol . $term_name;
				}

				$field = $args['field'];
				if( isset($term->$field) ){
					$options[ $term->$field ] = $term_name;
				}

			}

			if( $args['hierarchical'] ){
				asort($options);
			}

			return $options;
		}

		return [];
	}
endif;


if(!function_exists('reycore_wc__product_tags')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__product_tags( $args = [] ){

		$args = wp_parse_args($args, [
			'taxonomy'   => 'product_tag',
			'hide_empty' => true,
		]);

		$terms = get_terms( $args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
			return $options;
		}
	}
endif;


if(!function_exists('reycore_wc__products')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__products(){

		$product_ids = get_posts( [
				'post_type' => 'product',
				'numberposts' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
		 ]);

		if ( ! empty( $product_ids ) ){
			foreach ( $product_ids as $product_id ) {
				$options[ $product_id ] = get_the_title($product_id);
			}
			return $options;
		}
		return [];
	}
endif;


if(!function_exists('reycore_wc__attributes_list')):
	/**
	 * WooCommerce Attribites
	 * @return array
	 */
	function reycore_wc__attributes_list( $attributes ){

		$attribute_taxonomies = wc_get_attribute_taxonomy_labels();

		if ( ! empty( $attribute_taxonomies ) ){
			foreach ( $attribute_taxonomies as $key => $value ) {
				$new_key = wc_attribute_taxonomy_name( $key );
				$attributes[ $new_key ] = $value;
			}
		}

		return $attributes;
	}
	add_filter('reycore/elementor/product_grid/attributes', 'reycore_wc__attributes_list', 10);
endif;


function reycore_wc__get_all_attributes_terms(){

	$attrs = [];

	foreach( wc_get_attribute_taxonomies() as $attribute ) {

		$taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);

		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => true
		]);

		if( is_wp_error($terms) ){
			continue;
		}

		foreach ($terms as $key => $term) {
			if( isset($term->term_id) ){
				$attrs[ $term->term_id ] = sprintf('%s (%s)', $term->name, $attribute->attribute_label);
			}
		}

	}

	return $attrs;

}

if(!function_exists('reycore_wc__get_product')):
	function reycore_wc__get_product( $product_id = false ){

		$product = wc_get_product( $product_id ? $product_id : false );

		if( ! $product ){
			return false;
		}

		return $product;

	}
endif;

if(!function_exists('reycore_wc__is_product')):
	/**
	 * Is product
	 *
	 * @since 2.1.0
	 **/
	function reycore_wc__is_product()
	{
		return apply_filters('reycore/woocommerce/is_product', is_product() || get_query_var('rey__is_quickview', false) );
	}
endif;
