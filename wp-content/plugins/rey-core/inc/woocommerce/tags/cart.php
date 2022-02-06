<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce__Cart') ):

	class ReyCore_WooCommerce__Cart
	{
		private static $_instance = null;

		const ELEMENT_DEFAULT_LAYOUT = 'custom';
		const SITE_DEFAULT_LAYOUT = 'classic';

		public $el_settings = [];

		protected $layout;

		private function __construct()
		{
			add_action( 'wp', [$this, 'wp']);
			add_action( 'woocommerce_before_calculate_totals', [$this, 'handle_early_ajax']);
			add_filter( 'reycore/woocommerce/wc_get_template', [$this, 'override_template'], 20);
			add_filter( 'woocommerce_package_rates', [$this, 'hide_flatrate_if_freeshipping'], 100 );
			add_action( 'woocommerce_before_cart_table', [$this, 'enable_cart_controls']);
			add_action( 'woocommerce_after_cart_table', [$this, 'enable_cart_controls__cleanup']);
			add_filter( 'woocommerce_cart_item_quantity', [$this, 'modify_qty_buttons'], 20, 3);
			add_action( 'woocommerce_before_template_part', [$this, 'cart_collaterals_carousel'], 10, 4);
		}

		function wp() {

			if( ! is_cart() ){
				return;
			}

			// ...
		}


		/**
		 * Get layout option
		 *
		 * @since 2.0.0
		 **/
		function get_cart_layout() {

			if( defined('REY_CART_SETTINGS') ){
				$this->el_settings = REY_CART_SETTINGS;
				return $this->layout = isset($this->el_settings['layout']) ? $this->el_settings['layout'] : self::ELEMENT_DEFAULT_LAYOUT;
			}

			if( $this->layout ){
				return $this->layout;
			}

			if( ! class_exists('\Elementor\Plugin') ){
				return $this->layout = self::SITE_DEFAULT_LAYOUT;
			}

			// for WC-AJAX
			$url = wp_get_referer();

			if( ! ( $post_id = absint( url_to_postid( $url ) ) ) ){
				return $this->layout = self::SITE_DEFAULT_LAYOUT;
			}

			$elementor =\Elementor\Plugin::$instance;

			if( ! ($elementor->db && $elementor->db->is_built_with_elementor( $post_id )) ){
				return $this->layout = self::SITE_DEFAULT_LAYOUT;
			}

			$document = $elementor->documents->get( $post_id );

			$data = $document ? $document->get_elements_data() : '';

			if ( empty( $data ) ) {
				return $this->layout = self::SITE_DEFAULT_LAYOUT;
			}

			$_settings = [];

			$elementor->db->iterate_data( $data, function( $element ) use (&$_settings) {
				if ( !empty( $element['widgetType'] ) && $element['widgetType'] === 'reycore-wc-cart' ) {
					$_settings[] = $element['settings'];
				}
			});

			// always get the first one
			if( empty( $_settings ) ) {
				return $this->layout = self::SITE_DEFAULT_LAYOUT;
			}

			$this->el_settings = $_settings[0];

			return $this->layout = isset( $this->el_settings['layout'] ) && !empty( $this->el_settings['layout'] ) ? $this->el_settings['layout'] : self::ELEMENT_DEFAULT_LAYOUT;

		}

		function is_custom_layout(){
			return $this->get_cart_layout() === 'custom';
		}

		/**
		 * Handle Cart's Ajax early calls
		 *
		 * @since 2.0.0
		 **/
		function handle_early_ajax() {
			$this->custom_text_markup();
		}

		function get_setting( $setting, $default ){

			if( isset($this->el_settings[$setting]) ) {
				return $this->el_settings[$setting];
			}

			return $default;
		}

		function custom_text_markup(){

			if( ! $this->is_custom_layout() ){
				return;
			}

			if( $text = $this->get_setting('custom_text_before_proceed', '') ){
				add_action('woocommerce_proceed_to_checkout', function() use ($text){
					printf('<div class="rey-cart-customText --before-proceed">%s</div>', reycore__parse_text_editor($text));
				}, absint( $this->get_setting('custom_text_before_proceed_pos', '1') ) );
			}

		}


		function override_template( $templates ){

			$templates[] = [
				'template_name' => 'cart/cart.php',
				'template' => 'template-parts/woocommerce/cart/cart.php'
			];

			return $templates;

		}

		function hide_flatrate_if_freeshipping( $rates ) {

			if( ! get_theme_mod('cart_checkout_hide_flat_rate_if_free_shipping', false) ){
				return $rates;
			}

			$flat_rate_id = $supports_free_shipping = false;

			foreach ( $rates as $rate_id => $rate ) {
				// there is free shipping
				if( ! $supports_free_shipping ){
					$supports_free_shipping = 'free_shipping' === $rate->get_method_id();
				}
				// there is flat rate, get key
				if( ! $flat_rate_id !== false ){
					$flat_rate_id = 'flat_rate' === $rate->get_method_id() ? $rate_id : false;
				}
			}

			if( $supports_free_shipping && $flat_rate_id !== false ){
				unset($rates[$flat_rate_id]);
			}

			return $rates;
		}

		/**
		 * Get shipping costs
		 */
		function get_shipping_cost( $cart_panel = false ){

			/**
			 * @filter
			 *
			 * Use code below if you want to hide the shipping costs from cart panel & checkout
			 *
			 * add_filter('reycore/woocommerce/cart_checkout/show_shipping', '__return_false', 20);
			 *
			 */
			if( ! apply_filters('reycore/woocommerce/cart_checkout/show_shipping', true) ){
				return;
			}

			if( !WC()->shipping || !WC()->cart || !WC()->countries ){
				return;
			}

			if( ! WC()->cart->show_shipping() ){
				return;
			}

			$packages = WC()->shipping()->get_packages();

			$free_text = esc_html_x('FREE', 'Shipping status in Checkout when 0.', 'rey-core');
			$shipping_price = '';
			$supports_free_shipping = false;

			foreach ( $packages as $i => $package ) :

				foreach ( $package['rates'] as $key => $method ) :

					$supports_free_shipping = in_array( $method->get_method_id(), ['free_shipping', 'local_pickup'], true );

					/**
					 * @filter
					 *
					 * Use code below if you want to show the shipping price, exactly as picked into the cart/checkout
					 *
					 * add_filter('reycore/woocommerce/cart_panel/show_free_regardless', '__return_false', 20);
					 *
					 */
					if( apply_filters('reycore/woocommerce/cart_panel/show_free_regardless', ($cart_panel && $supports_free_shipping), $method, $cart_panel, $supports_free_shipping) ){
						return $free_text;
					}

					if ( WC()->session->chosen_shipping_methods[ $i ] == $key ) {

						$shipping_price_text = '';

						$has_cost  = 0 < $method->cost;

						$hide_cost = ! $has_cost && $supports_free_shipping;

						if ( $has_cost && ! $hide_cost ) {
							if ( WC()->cart->display_prices_including_tax() ) {
								$shipping_price_text .= wc_price( $method->cost + $method->get_shipping_tax() );
								if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
									$shipping_price_text .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
								}
							} else {
								$shipping_price_text .= wc_price( $method->cost );
								if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
									$shipping_price_text .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
								}
							}
						}

						$shipping_price .= apply_filters('reycore/woocommerce/cart_panel/shipping_price_text', $shipping_price_text, $method, $has_cost, $hide_cost);
					}
				endforeach;
			endforeach;

			if( $supports_free_shipping && $shipping_price === '' ){
				return $free_text;
			}

			return wp_kses_post( $shipping_price );
		}

		/**
		 * Enable Qty controls on Cart & disable select (if enabled)
		 * @since 1.6.6
		 */
		function enable_cart_controls(){
			add_filter('theme_mod_single_atc_qty_controls', '__return_true');
			add_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
		}

		function enable_cart_controls__cleanup(){
			remove_filter('theme_mod_single_atc_qty_controls', '__return_true');
			remove_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
		}

		/**
		 * Get cart product
		 *
		 * @since 1.9.2
		 **/
		function cart_get_product($cart_item)
		{
			$product_id   = absint( $cart_item['product_id'] );
			$variation_id = absint( $cart_item['variation_id'] );

			// Ensure we don't add a variation to the cart directly by variation ID.
			if ( 'product_variation' === get_post_type( $product_id ) ) {
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $variation_id );
			}

			return wc_get_product( $variation_id ? $variation_id : $product_id );
		}

		/**
		 * Disable plus minus
		 *
		 * @since 1.9.2
		 **/
		function modify_qty_buttons($quantity, $cart_item_key, $cart_item)
		{

			$product = $this->cart_get_product($cart_item);

			if ( ! $product ) {
				return $quantity;
			}

			// prevent showing quantity controls
			if ($product->is_sold_individually() ) {
				return $quantity;
			}

			$defaults = array_map('intval', [
				'input_value'  	=> $cart_item['quantity'],
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'step' 		=> apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
			] );

			$quantity = str_replace('cartBtnQty-control --minus --disabled', 'cartBtnQty-control --minus', $quantity);
			$quantity = str_replace('cartBtnQty-control --plus --disabled', 'cartBtnQty-control --plus', $quantity);

			if( $defaults['input_value'] === $defaults['min_value'] ){
				$quantity = str_replace('cartBtnQty-control --minus', 'cartBtnQty-control --minus --disabled', $quantity);
			}
			else if( $defaults['max_value'] > $defaults['min_value'] && $defaults['input_value'] === $defaults['max_value'] ) {
				$quantity = str_replace('cartBtnQty-control --plus', 'cartBtnQty-control --plus --disabled', $quantity);
			}

			return $quantity;
		}


		function cross_sells_buttons_text( $text, $product ){

			if( $custom_text = get_theme_mod('header_cart__cross_sells_btn_text', '') ){
				if(
					($product->get_type() === 'simple' && $product->is_purchasable() && $product->is_in_stock()) ||
					($product->get_type() === 'variable' && $product->is_purchasable())
				){
					return $custom_text;
				}
			}

			return $text;
		}

		/**
		 * Add style
		 *
		 * @since 1.9.4
		 **/
		function cart_collaterals_carousel($template_name, $template_path, $located, $args)
		{
			if( $template_name === 'cart/cross-sells.php' ){
				add_filter('woocommerce_product_add_to_cart_text', [$this, 'cross_sells_buttons_text'], 20, 2);
				if( isset($args['cross_sells']) && count($args['cross_sells']) > 1 ){
					reyCoreAssets()->add_scripts(['rey-splide', 'reycore-wc-product-carousels']);
					reyCoreAssets()->add_styles('rey-splide');
				}
			}
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_WooCommerce__Cart
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	function reyCoreCart(){
		return ReyCore_WooCommerce__Cart::getInstance();
	}

	reyCoreCart();

endif;
