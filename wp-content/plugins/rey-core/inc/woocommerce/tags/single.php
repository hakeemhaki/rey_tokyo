<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Single') ):
/**
 * Loop fucntionality
 *
 */
class ReyCore_WooCommerce_Single
{
	private static $_instance = null;

	private $_skins = [];

	private function __construct()
	{
		add_action( 'init', [$this, 'init']);

		$this->set_single_skins();
		$this->load_includes();
	}

	function init(){

		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init', [$this, 'load_hooks'] );
			return;
		}

		$this->load_hooks();
	}

	public function load_hooks()
	{
		add_action( 'wp', [ $this, 'rearrangements' ]);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ]);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_inner_summary' ], 2);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_inner_summary_end' ], 500);
		add_action( 'woocommerce_before_single_product_summary', [ $this, 'wrap_product_summary' ], 0);
		add_action( 'woocommerce_after_single_product_summary', 'reycore_wc__generic_wrapper_end', 2);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_title' ], 4);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 6);
		add_filter( 'woocommerce_get_stock_html', [ $this, 'adjust_stock_html' ], 9, 2);
		add_filter( 'woocommerce_post_class', [$this, 'product_page_classes'], 20, 2 );
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_text_before_atc' ], 10);
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'add_text_after_atc' ], 10);
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'wrap_cart_qty' ], 10);
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'wrap_cart_qty_end' ], 5);
		add_action( 'woocommerce_before_quantity_input_field', [$this, 'wrap_quantity_input']);
		add_action( 'woocommerce_after_quantity_input_field', 'reycore_wc__generic_wrapper_end');
		add_action( 'woocommerce_after_add_to_cart_form', [$this, 'after_add_to_cart_form']);
		add_filter( 'woocommerce_product_thumbnails_columns', [$this,'product_thumbnails_columns'], 10);
		add_action( 'woocommerce_share', [$this,'add_share_buttons']);
		add_filter( 'wc_product_sku_enabled', [$this, 'product_sku']);
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_filter( 'body_class', [ $this, 'body_classes'], 20 );
		add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false', 100);
		add_filter( 'woocommerce_breadcrumb_defaults', [$this, 'remove_home_in_breadcrumbs']);
		add_filter( 'get_previous_post_where', [$this, 'get_adjacent_product_where']);
		add_filter( 'get_next_post_where', [$this, 'get_adjacent_product_where']);
		add_filter( 'get_previous_post_join', [$this, 'get_adjacent_product_join']);
		add_filter( 'get_next_post_join', [$this, 'get_adjacent_product_join']);
		add_filter( 'reycore/header_helper/overlap_classes', [$this, 'header_overlapping_helper'], 100);
		add_filter( 'woocommerce_get_asset_url', [$this, 'filter_assets_url'], 20, 2);
		add_filter( 'woocommerce_product_single_add_to_cart_text', [$this, 'single_add_to_cart_text'], 10, 2);

		$this->remove_product_meta();
		$this->remove_product_price();
	}


	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['single_ajax_add_to_cart'] = self::product_page_ajax_add_to_cart();
		$params['tabs_mobile_closed'] = false;
		return $params;
	}

	function enqueue_scripts( $force = false ){

		if( ! $force && ! is_product() ){
			return;
		}

		$styles = [
			'rey-wc-product'
		];

		$styles[] = 'rey-wc-product-mobile-gallery';
		$styles[] = 'rey-wc-product-gallery';

		$styles[] = 'rey-wc-product-skin-' . $this->get_single_active_skin();

		reyCoreAssets()->add_styles($styles);

		if( self::product_page_ajax_add_to_cart() ){
			reyCoreAssets()->add_scripts('reycore-wc-product-page-ajax-add-to-cart');
		}

	}

	/**
	 * Move stuff in template
	 * @since 1.0.0
	 */
	function rearrangements()
	{
		// Move breadcrumbs
		if( is_product() ){
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		}

		// Move ratings at the end
		if( get_theme_mod('single_product_reviews_after_meta', true) ){
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 45 );
		}

		// Adds discount badge
		add_action( 'woocommerce_single_product_summary', function(){
			add_filter( 'woocommerce_get_price_html', [ $this, 'discount_percentage' ], 100, 2);
		}, 0);

		add_action( 'woocommerce_single_product_summary', function(){
			remove_filter( 'woocommerce_get_price_html', [ $this, 'discount_percentage' ], 100);
		}, 9999);

	}

	/**
	 * Wrap inner summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_inner_summary()
	{ ?>
		<div class="rey-innerSummary">
		<?php
	}

	function wrap_inner_summary_end()
	{
		?>
		</div>
		<!-- .rey-innerSummary -->
		<?php
	}

	/**
	 * Wrap single summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_product_summary()
	{
		// force scripts loading
		$this->enqueue_scripts(true);

		// add product QTY for grouped products
		if ( ($product = wc_get_product()) && $product->get_type() === 'grouped' ) {
			add_action( 'woocommerce_before_add_to_cart_quantity', [$this, 'wrap_cart_qty']);
			add_action( 'woocommerce_after_add_to_cart_quantity', 'reycore_wc__generic_wrapper_end');
		}

		do_action('reycore/woocommerce/before_single_product_summary'); ?>

		<div class="rey-productSummary"><?php
	}

	/**
	 * Wrap single summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_title()
	{ ?>
		<div class="rey-productTitle-wrapper"><?php
	}

	/**
	 * Wrap Add to cart & Quantity
	 *
	 * @since 1.0.0
	 **/
	function wrap_cart_qty()
	{

		add_filter( 'woocommerce_quantity_input_args',[$this,'disable_quantity_input'], 200);

		$classes = [ 'rey-cartBtnQty', '--atc-normal-hover' ];

		$product = wc_get_product();

		if ( $product && ! $product->is_sold_individually() ) {
			if( $style = get_theme_mod('single_atc_qty_controls_styles', 'default') ){
				$classes[] = '--style-' . $style;
			}
		}

		if( get_theme_mod('single_atc__stretch', false) ){
			$classes[] = '--stretch';
		}

		$classes = apply_filters('reycore/woocommerce/cart_wrapper/classes', $classes, $product);

		printf('<div class="%s">', implode(' ', array_map('esc_attr', $classes)));
	}

	function wrap_cart_qty_end()
	{
		?>
		</div>
		<!-- .rey-cartBtnQty -->
		<?php

		remove_filter( 'woocommerce_quantity_input_args',[$this,'disable_quantity_input'], 200);
	}


	function disable_quantity_input($args){

		if( get_theme_mod('single_atc_qty_controls_styles', 'default') === 'disabled' ){
			$args['max_value'] = 1;
			$args['min_value'] = 1;
		}

		return $args;
	}

	/**
	 * Wrap Quantity
	 *
	 * @since 1.0.0
	 **/
	function wrap_quantity_input()
	{

		$classes = [ 'rey-qtyField' ];
		$style = get_theme_mod('single_atc_qty_controls_styles', 'default');
		$controls = get_theme_mod('single_atc_qty_controls', false);
		$can_add_select_box = $style === 'select' && apply_filters('reycore/woocommerce/quantity_field/can_add_select', true);
		$can_add_controls = ($controls && !$can_add_select_box);

		// will also be added in the cart
		if( $can_add_controls ){
			$classes[] = 'cartBtnQty-controls';
			reyCoreAssets()->add_scripts( 'reycore-wc-product-page-qty-controls' );
		}

		// start
		$content = sprintf('<div class="%s">', implode(' ', $classes));

		// return if product is sold individually
		if ( ($product = wc_get_product()) && $product && $product->is_sold_individually() ) {
			return;
		}

		// show QTY controls
		// - when enabled in product page
		// - in cart
		if( $can_add_controls ){
			$content .= sprintf('<span class="cartBtnQty-control --minus">%s</span>', reycore__get_svg_icon__core(['id'=>'reycore-icon-minus']));
			$content .= sprintf('<span class="cartBtnQty-control --plus">%s</span>', reycore__get_svg_icon__core(['id'=>'reycore-icon-plus']));
		}

		// Select box
		if( $can_add_select_box ) :

			$product = wc_get_product();

			$defaults = array(
				'input_name'  	=> 'quantity',
				'input_value'  	=> '1',
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product ? $product->get_min_purchase_quantity() : '', $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product ? $product->get_max_purchase_quantity() : '', $product ),
				'step' 		=> apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
				'style'		=> apply_filters( 'woocommerce_quantity_style', '', $product )
			);

			$min = ! empty( $defaults['min_value'] ) ? $defaults['min_value'] : 1;
			$max = ! empty( $defaults['max_value'] ) && $defaults['max_value'] != '-1' ? $defaults['max_value'] : 20;
			$step = ! empty( $defaults['step'] ) ? $defaults['step'] : 1;

			$options = '';
			for ( $count = $min; $count <= $max; $count = $count+$step ) {
				$options .= '<option value="' . $count . '">' . $count . '</option>';
			}

			$content .= '<div class="rey-qtySelect" style="' . $defaults['style'] . '">';
			$content .= '<span class="rey-qtySelect-title">'. reycore__texts('qty') .'</span>';
			$content .= reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']);
			$content .= sprintf('<select name="%1$s" title="%2$s" class="qty" data-min="%4$s" data-max="%5$s" >%3$s</select>',
				esc_attr( $defaults['input_name'] ),
				reycore__texts('qty'),
				$options,
				$min,
				$max
			);
			$content .= '</div>';

		endif;

		echo $content;
	}

	/**
	 * Add text before add to cart button
	 *
	 * @since 1.4.0
	 **/
	function add_text_before_atc()
	{
		$en = reycore__get_option( 'enable_text_before_add_to_cart', false );

		if( $en === false || $en === 'false' ){
			return;
		}

		$content = reycore__get_option( 'text_before_add_to_cart', false, ($en !== 'custom') );

		printf('<div class="rey-cartBtn-beforeText">%s</div>', reycore__parse_text_editor( $content )  );
	}

	/**
	 * Add text after add to cart button
	 *
	 * @since 1.4.0
	 **/
	function add_text_after_atc()
	{
		$en = reycore__get_option( 'enable_text_after_add_to_cart', false );

		if( $en === false || $en === 'false' ){
			return;
		}

		$content = reycore__get_option( 'text_after_add_to_cart', false, ($en !== 'custom') );

		printf('<div class="rey-cartBtn-afterText">%s</div>', reycore__parse_text_editor( $content ) );
	}

	function after_add_to_cart_form(){

		ob_start();
		do_action('reycore/woocommerce/single/after_add_to_cart_form');
		$content = ob_get_clean();

		if( !empty($content) ){
			printf('<div class="rey-after-atcForm">%s</div>', $content);
		}
	}

	/**
	 * Filter Adjacent Post JOIN query
	 * to exclude out of stock items
	 *
	 * @since 1.3.7
	 */
	function get_adjacent_product_join($join){
		global $wpdb;

		if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$join = $join . " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
		}

		return $join;
	}

	/**
	 * Filter Adjacent Post WHERE query
	 * to exclude out of stock items
	 *
	 * @since 1.3.7
	 */
	function get_adjacent_product_where($where){

		global $wpdb;

		if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$where = $wpdb->prepare("$where AND ($wpdb->postmeta.meta_key = %s AND $wpdb->postmeta.meta_value NOT LIKE %s)", '_stock_status', 'outofstock');
		}

		return $where;
	}

	/**
	 * Show Product Navigation
	 *
	 * @since 1.0.0
	 **/
	function product_navigation() {}

	/**
	 * Override thumbnails columns
	 *
	 * @since 1.0.0
	 **/
	function product_thumbnails_columns()
	{
		return 5;
	}


	function is_single_true_product(){
		return !in_array( wc_get_loop_prop('name'), ['upsells', 'up-sells', 'crosssells', 'cross-sells', 'related'] );
	}

	/**
	 * Add discount percentage to product
	 *
	 * @since 1.0.0
	 */
	function discount_percentage($html, $product = null){

		if( ! is_product() ){
			return $html;
		}

		if( ! $product ){
			$product = wc_get_product();
		}

		if( ! $product ){
			return $html;
		}

		$content = '';

		/* ------------------------------------ DISCOUNT ------------------------------------ */

		$has_badge = reycore__get_fallback_mod('single_discount_badge_v2', true, [
			'mod' => 'single_discount_badge',
			'value' => '1',
			'compare' => '==='
		] );

		if (
			$has_badge && $this->is_single_true_product() &&
			apply_filters('reycore/woocommerce/discounts/check', ($product->is_on_sale() || $product->is_type( 'grouped' )), $product)
			) {

			/**
			 * Hook: reycore/woocommerce/discounts/check
			 * How to check if it has a discount. In case you want to hide the badge for main variable product, in case
			 * there are *some* variations with discoutns, not all.
			 *
			 * add_filter('reycore/woocommerce/discounts/check', function($status, $product){
			 * 		return $product->get_sale_price();
			 * });
			 */

			if ( get_theme_mod('loop_show_sale_label', 'percentage') === 'save' ){
				$content .= reycore_wc__get_discount_save_html();
			}
			else {
				$percentage_html_text = apply_filters('reycore/woocommerce/discounts/percentage_html_text', esc_html_x('OFF', 'WooCommerce single item discount percentage', 'rey-core'));
				$content .= reycore_wc__get_discount_percentage_html($percentage_html_text);
			}
		}

		/* ------------------------------------ CUSTOM TEXT ------------------------------------ */

		if( $this->is_single_true_product() && ($text_type = get_theme_mod('single_product_price_text_type', 'no')) && $text_type !== 'no' ){

			$text = sprintf( '<span class="rey-priceText %2$s">%1$s</span>',
				get_theme_mod('single_product_price_text_custom', esc_html__('Free Shipping!', 'rey-core')),
				get_theme_mod('single_product_price_text_inline', false) ? '--block' : ''
			);

			// simple custom text
			if( $text_type === 'custom_text' ){
				$content .= $text;
			}

			// based on current product price & cart totals
			elseif( $text_type === 'free_shipping' && null !== WC()->cart && wc_shipping_enabled() && is_numeric( $product->get_price() ) ){
				if( ($product->get_price() + absint( WC()->cart->get_displayed_subtotal() )) > absint( get_theme_mod('single_product_price_text_shipping_cost', 0) ) ){
					$content .= $text;
				}
				// keep an eye on https://gist.githubusercontent.com/rashmimalpande/5de0d929b0cc27130096f6da5be8f9e9/raw/9282cc4d36203dd76343afd481f8baffab206f9b/free-shipping-notice-on-checkout.php
			}

		}

		return $html . $content;
	}


	/**
	 * Filter Stock's text markup
	 *
	 * @since 1.0.0
	 **/
	function adjust_stock_html($html, $product)
	{
		$stock_status = $product->get_stock_status();

		if( get_theme_mod('product_page__hide_stock', false) && $stock_status !== 'onbackorder' ){
			return '';
		}

        if ( ! apply_filters( 'reycore/woocommerce/stock_display', true ) ) {
            return $html;
        }

		$availability = $product->get_availability();

		switch( $stock_status ):
			// onbackorder
			case "instock":
				return sprintf('<p class="stock %s">%s <span>%s</span></p>',
					esc_attr( $availability['class'] ),
					reycore__get_svg_icon(['id' => 'rey-icon-check']),
					$availability['availability'] ? $availability['availability'] : esc_html__( 'In stock', 'rey-core' )
				);
				break;
			case "outofstock":
				return sprintf('<p class="stock %s">%s <span>%s</span></p>',
					esc_attr( $availability['class'] ),
					reycore__get_svg_icon(['id' => 'rey-icon-close']),
					$availability['availability'] ? $availability['availability'] : esc_html__( 'Out of stock', 'rey-core' )
				);
				break;
		endswitch;

		return $html;
	}


	/**
	 * Add share buttons
	 *
	 * @since 1.0.0
	 **/
	function add_share_buttons( $args = [] )
	{
		$args = wp_parse_args($args, [
			'title' => esc_html__('SHARE', 'rey-core'),
			'custom_classes' => []
		]);

		$classes = apply_filters('reycore/woocommerce/product_page/share/classes', $args['custom_classes']);

		// Sharing
		if( get_theme_mod('product_share', '1') == '1' ){

			printf('<div class="rey-productShare %s">', esc_attr(implode(' ', $classes)));

				echo '<div class="rey-productShare-inner">';

				if( $title = $args['title'] ){
					echo '<h5>'. $title .'</h5>';
				}

				if( function_exists('reycore__socialShare') ){

					$share_icons = get_theme_mod('product_share_icons', [
						[
							'social_icon' => 'twitter',
						],
						[
							'social_icon' => 'facebook-f',
						],
						[
							'social_icon' => 'linkedin',
						],
						[
							'social_icon' => 'pinterest-p',
						],
						[
							'social_icon' => 'mail',
						],
						[
							'social_icon' => 'copy',
						],
					]);

					reycore__socialShare([
						'share_items' => wp_list_pluck($share_icons, 'social_icon'),
						'colored' => get_theme_mod('product_share_icons_colored', false)
					]);

				}
				echo '</div>';
			echo '</div>';

			if( in_array('--sticky', $classes, true) ){
				reyCoreAssets()->add_scripts(['reycore-sticky', 'reycore-wc-product-page-sticky']);
			}
		}
	}

	/**
	 * Toggle product sku visibility
	 *
	 * @since 1.0.0
	 */
	function product_sku( $status ){

		if( reycore_wc__is_product() ){
			return reycore__get_fallback_mod('product_sku_v2', true, [
				'mod' => 'product_sku',
				'value' => '1',
				'compare' => '==='
			] );
		}

		return $status;
	}

	function product_meta_is_enabled(){
		return reycore__get_fallback_mod('single_product_meta_v2', true, [
			'mod' => 'single_product_meta',
			'value' => 'show',
			'compare' => '==='
		] );
	}

	/**
	 * Product meta
	 *
	 * @since 1.3.5
	 */
	function remove_product_meta(){
		if( !$this->product_meta_is_enabled() ){
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
		}
	}

	/**
	 * Product Price
	 *
	 * @since 1.6.4
	 */
	function remove_product_price(){
		if( ! get_theme_mod('single_product_price', true) ){
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
		}
	}

	/**
	 * Check if breadcrums are enabled
	 *
	 * @since 1.3.4
	 */
	function breadcrumb_enabled(){
		return get_theme_mod('single_breadcrumbs', 'yes_hide_home') !== 'no';
	}

	/**
	 * Remove Home button in breadcrumbs
	 *
	 * @since 1.3.4
	 */
	function remove_home_in_breadcrumbs( $args ){

		if( is_product() && get_theme_mod('single_breadcrumbs', 'yes_hide_home') === 'yes_hide_home' ){
			$args['home']  = false;
		}

		return $args;
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function product_page_classes($classes, $product)
	{
		if( is_product() ) {
			if( $product->get_id() === get_queried_object_id() ){
				$classes['product_page_class'] = 'rey-product';
			}

			if ( $product->get_type() === 'grouped' && get_theme_mod('single_atc_qty_controls', false) ) {
				$classes['grouped_controls'] = '--grouped-qty-controls';
			}
		}

		return $classes;
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{
		if( in_array('single-product', $classes) )
		{
			// Skin Class
			$classes['pdp_skin'] = 'single-skin--' . $this->get_single_active_skin();
		}

		return $classes;
	}


	function header_overlapping_helper ( $classes ){

		if( ! is_product() ){
			return $classes;
		}

		$classes['desktop'] = reycore__acf_get_field('header_fixed_overlap') !== true ? $classes['desktop'] : '';
		$classes['tablet'] = reycore__acf_get_field('header_fixed_overlap_tablet') !== true ? $classes['tablet'] : '';
		$classes['mobile'] = reycore__acf_get_field('header_fixed_overlap_mobile') !== true ? $classes['mobile'] : '';

		return $classes;
	}


	private function set_single_skins(){
		$this->_skins = [
			'default' => __('Default', 'rey-core'),
			'fullscreen' => __('Full-screen Summary', 'rey-core'),
			'compact' => __('Compact Layout', 'rey-core'),
		];
	}

	public function get_single_skins(){
		return apply_filters('reycore/woocommerce/single_skins', $this->_skins);
	}

	public function get_single_active_skin(){
		return get_theme_mod('single_skin', 'default');
	}

	function filter_assets_url( $full_url, $path ){

		// PhotoSwipe skin
		if( strpos($path, 'assets/css/photoswipe/default-skin/default-skin') !== false ){
			$full_url = REY_CORE_URI . 'assets/css/woocommerce-components/photoswipe-skin.css';
		}
		else if( strpos($path, 'assets/js/frontend/single-product') !== false ){
			$full_url = REY_CORE_URI . 'assets/js/woocommerce/wc-single-product.js';
		}

		return $full_url;
	}

	function single_add_to_cart_text( $text, $product ){

		if ( $custom_backorder_text = get_theme_mod('single_atc__text_backorders', '') && $product->is_on_backorder( 1 ) ) {
			return $custom_backorder_text;
		}

		$custom_text = get_theme_mod('single_atc__text', '');

		if( $custom_text !== '' ){

			if( $custom_text === '0' ){
				return '';
			}

			return $custom_text;
		}

		return $text;
	}

	public static function product_page_ajax_add_to_cart(){
		return 'yes' === get_theme_mod('product_page_ajax_add_to_cart', 'yes') && 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' );
	}


	/**
	 * Load files
	 *
	 * @since 1.0.0
	 **/
	function load_includes(){

		foreach( $this->_skins as $skin => $name ){
			if( ($skin_path = sprintf("%sinc/woocommerce/tags/single-%s.php", REY_CORE_DIR, $skin)) && is_readable($skin_path) ){
				require $skin_path;
			}
		}

		// load product gallery
		require REY_CORE_DIR . "inc/woocommerce/tags/product-gallery/gallery-base.php";
	}


	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Single
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}
endif;

ReyCore_WooCommerce_Single::getInstance();
