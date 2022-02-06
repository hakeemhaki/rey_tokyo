<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_MiniCart') ):

class ReyCore_WooCommerce_MiniCart
{
	private static $_instance = null;

	private function __construct()
	{
		add_action('init', [$this, 'init']);
	}

	function init(){
		add_action( 'rey/header/row', [$this, 'add_cart_into_header'], 40);
		add_filter( 'woocommerce_add_to_cart_fragments', [$this, 'cart_fragment'] );
		add_action( 'woocommerce_before_mini_cart', [$this,'ajax_enqueue_scripts']);
		add_action( 'woocommerce_before_mini_cart_contents', [$this,'minicart_before_cart_items'], 0);
		add_action( 'woocommerce_mini_cart_contents', [$this,'minicart_after_cart_items'], 999);
		add_action( 'woocommerce_after_mini_cart', [$this,'minicart_custom_content_when_empty']);
		add_action( 'woocommerce_widget_shopping_cart_total', [$this,'show_shipping_minicart'], 15);
		add_filter( 'woocommerce_cart_item_name', [$this,'cart_wrap_name']);
		add_filter( 'woocommerce_widget_cart_item_quantity', [$this,'mini_cart_quantity'], 10, 3 );
		add_action( 'wp_ajax_rey_update_minicart', [$this, 'update_minicart_qty']);
		add_action( 'wp_ajax_nopriv_rey_update_minicart', [$this, 'update_minicart_qty']);
		add_action( 'woocommerce_before_mini_cart_contents', [$this, 'enable_qty_controls_minicart']);
		add_action( 'woocommerce_mini_cart_contents', [$this, 'enable_qty_controls_minicart__remove']);
		add_action( 'reycore/woocommerce/minicart/before_totals', [$this, 'cross_sells_carousel'], 10);
		add_action( 'reycore/woocommerce/minicart/before_totals', [$this, 'shipping_bar'], 10);
		add_action( 'woocommerce_before_cart_table', [$this, 'shipping_bar_cart_page'], 20);
		remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
		remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
		add_action( 'woocommerce_widget_shopping_cart_buttons', [$this, 'shopping_cart_button_view_cart'], 10 );
		add_action( 'woocommerce_widget_shopping_cart_buttons', [$this, 'shopping_cart_proceed_to_checkout'], 20 );
		add_filter( 'theme_mod_header_cart__cross_sells_bubble', [ $this, 'disable_header_cart__cross_sells_bubble' ] );
		add_filter( 'theme_mod_header_cart__cross_sells_carousel', [ $this, 'header_cart__cross_sells_carousel' ] );
	}

	/**
	 * Add Mini Cart to Header
	 */
	function add_cart_into_header(){
		if( get_theme_mod('shop_catalog', false) != true && get_theme_mod('header_enable_cart', true) ){

			reycore__get_template_part('template-parts/woocommerce/header-shopping-cart');

			// load panel
			add_action( 'rey/after_site_wrapper', [$this, 'add_cart_panel']);
		}
	}

	/**
	 * Add Cart Panel (triggered by header)
	 * @since 1.0.8
	 */
	function add_cart_panel(){

		if( get_theme_mod('shop_catalog', false) != true && get_theme_mod('header_enable_cart', true) && ! is_cart() && ! is_checkout() ){

			$cart_title = esc_html_x('SHOPPING BAG', 'Shopping bag title in cart panel', 'rey-core');

			if( $custom_text = get_theme_mod( 'header_cart__title', '' ) ){
				$cart_title = $custom_text;
			}

			if( ! get_theme_mod('header_cart__btn_cart__enable', true) ){
				$cart_title = '<a href="' . esc_url( wc_get_cart_url() ) . '">'. $cart_title .'</a>';
			}

			reycore__get_template_part('template-parts/woocommerce/header-shopping-cart-panel', false, false, [
				'inline_buttons' => get_theme_mod('header_cart__btns_inline', false),
				'cart_title' => $cart_title,
				'recent' => [
					'enabled' => get_theme_mod('header_cart__recent', false),
					'title' => esc_html__('RECENTLY VIEWED', 'rey-core'),
					'ids' => $this->get_recent_product_ids(),
					'quickview' => true,
				]
			]);

			$styles = ['rey-wc-general', 'simple-scrollbar', 'rey-wc-header-mini-cart-top', 'rey-wc-header-mini-cart', 'reycore-side-panel'];
			$scripts = ['reycore-wc-header-minicart', 'simple-scrollbar', 'reycore-wc-product-page-qty-controls'];

			if( get_theme_mod('header_cart__cross_sells_bubble', true) ){
				$scripts[] = 'reycore-wc-header-cart-crosssells-bubble';
			}

			// Handle ajax loaded component's scripts
			if( get_theme_mod('header_cart__cross_sells_bubble', true) ||
				get_theme_mod('header_cart__cross_sells_carousel', true) ){
				do_action('reycore/woocommerce/ajax/scripts');
			}

			reyCoreAssets()->add_styles($styles);
			reyCoreAssets()->add_scripts( $scripts );
		}
	}

	function get_recent_product_ids(){

		$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : [];

		if( empty($viewed_products) ){
			return [];
		}

		return array_slice( array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) ), 0, 10 );
	}


	/**
	 * Cart Counter
	 * Displayed a link to the cart including the number of items present
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public static function cart_link( $class = 'rey-headerCart-nb' ) {
		$cart_contents = is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : '';
		?>
			<span class="<?php echo esc_attr($class); ?>">
				<?php echo sprintf( '%d', $cart_contents ); ?>
			</span>
		<?php
	}

	/**
	 * Cart total
	 *
	 * @since  1.4.5
	 */
	public static function cart_total() {
		return sprintf('<span class="rey-headerCart-textTotal">%s</span>', is_object( WC()->cart ) ? WC()->cart->get_cart_total() : '');
	}

	/**
	 * Cart Fragments
	 * Ensure cart contents update when products are added to the cart via AJAX
	 *
	 * @param  array $fragments Fragments to refresh via AJAX.
	 * @return array            Fragments to refresh via AJAX
	 */
	function cart_fragment( $fragments ) {

		ob_start();
		self::cart_link();
		$fragments['.rey-headerCart-nb'] = ob_get_clean();

		ob_start();
		echo self::cart_total();
		$fragments['.rey-headerCart-textTotal'] = ob_get_clean();

		ob_start();
		self::cart_link('rey-cartPanel-counter __nb');
		$fragments['.rey-cartPanel-counter.__nb'] = ob_get_clean();

		$fragments['rey_cart_cross_sells'] = $this->cross_sells_bubble();

		return $fragments;
	}

	function ajax_enqueue_scripts(){
		if( wp_doing_ajax() ){
			do_action('wp_enqueue_scripts');
		}
	}
	/**
	 * Wrap mini cart items
	 *
	 * @since 1.3.7
	 **/
	function minicart_before_cart_items()
	{
		echo '<div class="woocommerce-mini-cart-inner">';
	}

	/**
	 * Wrap mini cart items
	 *
	 * @since 1.3.7
	 **/
	function minicart_after_cart_items()
	{
		echo '</div>';

		$this->add_continue_shopping_button();
	}

	/**
	 * Adds content into Cart Panel when empty
	 *
	 * @since 1.4.3
	 **/
	function minicart_custom_content_when_empty()
	{

		if( WC()->cart->is_empty() && get_theme_mod('header_cart_hide_empty', 'no') === 'no' &&
			($header_cart_gs = get_theme_mod('header_cart_gs', 'none')) && $header_cart_gs !== 'none' ){
			if( class_exists('ReyCore_GlobalSections') ){
				echo ReyCore_GlobalSections::do_section( $header_cart_gs, true );
			}
		}
	}

	function get_min_free_shipping_amount() {

		$is_available = false;

		if ( $manual_min_amount_data = get_theme_mod('header_cart_shipping_bar__min', '') ) {
			return [
				'amount' => floatval($manual_min_amount_data),
				'is_available' => false
			];
		}

		$min_free_shipping_amount = 0;

		$legacy_free_shipping = new WC_Shipping_Legacy_Free_Shipping();

		if ( 'yes' === $legacy_free_shipping->enabled ) {
			if ( in_array( $legacy_free_shipping->requires, array( 'min_amount', 'either', 'both' ) ) ) {
				$min_free_shipping_amount = $legacy_free_shipping->min_amount;
			}
		}

		$do_check_for_available_free_shipping = true;

		if (
			0 == $min_free_shipping_amount &&
			function_exists( 'WC' ) &&
			( $wc_shipping = WC()->shipping ) &&
			( $wc_cart = WC()->cart ) &&
			$wc_shipping->enabled &&
			( $packages = $wc_cart->get_shipping_packages() )
		) {

			$shipping_methods = $wc_shipping->load_shipping_methods( $packages[0] );

			foreach ( $shipping_methods as $shipping_method ) {
				if (
					'yes' === $shipping_method->enabled && 0 != $shipping_method->instance_id &&
					$shipping_method instanceof WC_Shipping_Free_Shipping
				) {

					if ( in_array( $shipping_method->requires, array( 'min_amount', 'either', 'both' ) ) ) {

						if ( $shipping_method->is_available( $packages[0] ) ) {
							$is_available = true;
						}

						$min_free_shipping_amount = $shipping_method->min_amount;

						if ( ! $do_check_for_available_free_shipping ) {
							continue;
						}

					}
					elseif ( $do_check_for_available_free_shipping ) {

						$is_available = true;
						$min_free_shipping_amount = 0;

						continue;

					}
				}
			}
		}

		return [
			'amount' => floatval($min_free_shipping_amount),
			'is_available' => $is_available
		];
	}

	function shipping_bar_cart_page(){

		if( ! get_theme_mod('header_cart_shipping_bar__cart_page', false) ){
			return;
		}

		$this->shipping_bar();
	}


	/**
	 * Show Shipping Bar
	 *
	 * @since 2.0.4
	 **/
	function shipping_bar()
	{
		if( ! WC()->shipping || ! WC()->cart || ! WC()->countries ){
			return;
		}

		if( ! WC()->cart->show_shipping() ){
			return;
		}

		if( ! get_theme_mod('header_cart_shipping_bar__enable', false) ){
			return;
		}

		$show_over = get_theme_mod('header_cart_shipping_bar__show_over', false);

		if( $free_shipping_min = $this->get_min_free_shipping_amount() ){
			if( $free_shipping_min['is_available'] && ! $show_over ) {
				return;
			}
		}

		$min = $free_shipping_min['amount'];
		$total = floatval( wc_prices_include_tax() ? WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax() : WC()->cart->get_cart_contents_total() );

		$is_over = $min < $total;

		$over_text = '';
		$over_class = '';

		if( $is_over ){
			if( $show_over ){
				$over_text = esc_html__('Free shipping!', 'rey-core');
				if( $custom_over_text = get_theme_mod('header_cart_shipping_bar__show_over_text', '') ){
					$over_text = $custom_over_text;
				}
				$over_class = '--over';
			}
			else {
				return;
			}
		}

		$diff = $min - $total;
		$percentage = 100 - (($diff / $min) * 100);

		echo sprintf('<div class="rey-cartShippingBar %2$s" style="--bar-perc:%1$d%%;">', $percentage > 100 ? 100 : $percentage, $over_class);

			$text = esc_html__('You\'re only {{diff}} away from free shipping.', 'rey-core');

			if( $custom_text = get_theme_mod('header_cart_shipping_bar__text', '') ){
				$text = $custom_text;
			}

			if( $over_text ){
				$text = $over_text;
			}

			echo sprintf('<div class="__text">%s</div>', str_replace('{{diff}}', wc_price( $diff ), $text));

			echo '<div class="__bar"></div>';

		echo '</div>';

	}

	/**
	 * Show Shipping in minicart
	 *
	 * @since 1.6.3
	 **/
	function show_shipping_minicart()
	{
		if( ! get_theme_mod('header_cart_show_shipping', false) ){
			return;
		}

		if( $cost = reyCoreCart()->get_shipping_cost(true) ){
			printf(
				'<span class="minicart-shipping"><strong>%1$s</strong><span>%2$s</span></span>',
				esc_html__( 'Shipping', 'rey-core' ),
				$cost
			);
		}

	}

	/**
	 * Change cart remove text
	 *
	 * @since 1.0.0
	 */
	function cart_wrap_name($html) {
		return sprintf('<div class="woocommerce-mini-cart-item-title">%s</div>', $html);
	}

	/**
	 * Check if Elementor PRO mini cart template is loaded
	 *
	 * @since 1.6.7
	 **/
	public static function mini_cart_elementorpro_template_active()
	{
		return 'yes' === get_option( 'elementor_use_mini_cart_template', 'no' );
	}

	/**
	 * Add quantity in Mini-Cart
	 *
	 * @since 1.6.6
	 **/
	function mini_cart_quantity($html, $cart_item, $cart_item_key)
	{
		if ( ! get_theme_mod('header_cart_show_qty', true) ) {
			return $html;
		}

		if ( self::mini_cart_elementorpro_template_active() ) {
			return $html;
		}

		$product = reyCoreCart()->cart_get_product( $cart_item );

		if ( ! $product ) {
			return $html;
		}

		// prevent showing quantity controls
		if ( ! apply_filters('reycore/woocommerce/cartpanel/show_qty', ! $product->is_sold_individually(), $cart_item ) ) {
			return $html;
		}

		$defaults = [
			'input_value'  	=> $cart_item['quantity'],
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'step' 		=> apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
		];

		$quantity = woocommerce_quantity_input( $defaults, $cart_item['data'], false );

		$quantity = str_replace('cartBtnQty-control --minus --disabled', 'cartBtnQty-control --minus', $quantity);
		$quantity = str_replace('cartBtnQty-control --plus --disabled', 'cartBtnQty-control --plus', $quantity);

		if( $defaults['input_value'] === $defaults['min_value'] ){
			$quantity = str_replace('cartBtnQty-control --minus', 'cartBtnQty-control --minus --disabled', $quantity);
		}
		else if( $defaults['max_value'] > $defaults['min_value'] && $defaults['input_value'] === $defaults['max_value'] ) {
			$quantity = str_replace('cartBtnQty-control --plus', 'cartBtnQty-control --plus --disabled', $quantity);
		}

		/**
		 * Prices
		 */
		if( apply_filters('reycore/woocommerce/cartpanel/show_discount', $cart_item['data']->is_on_sale(), $cart_item ) ){
			$price_html = wc_format_sale_price(
				wc_get_price_to_display( $cart_item['data'], ['price' => $cart_item['data']->get_regular_price()] ),
				wc_get_price_to_display( $cart_item['data'] ) ) .
				$cart_item['data']->get_price_suffix();
		}
		else {
			$price_html = WC()->cart->get_product_price( $cart_item['data'] );
		}

		$should_show_subtotal = $cart_item['quantity'] > 1 && isset($cart_item['line_total']) && get_theme_mod('header_cart_show_subtotal', true);

		$product_price = sprintf(
			'<span class="woocommerce-mini-cart-price">%1$s %2$s</span>',
			apply_filters( 'woocommerce_cart_item_price', $price_html, $cart_item, $cart_item_key, 'mini-cart' ),
			$should_show_subtotal ? '<span class="__item-total">' . apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) . '</span>' : ''
		);

		return sprintf('<div class="quantity-wrapper">%s %s</div><div class="__loader"></div>',
			$quantity,
			$product_price
		);
	}

	/**
	 * update cart
	 *
	 * @since 1.6.6
	 **/
	function update_minicart_qty()
	{
		if( self::mini_cart_elementorpro_template_active() ){
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$cart_item_key = wc_clean( isset( $_POST['cart_item_key'] ) ? wp_unslash( $_POST['cart_item_key'] ) : '' );
		$cart_item_qty = wc_clean( isset( $_POST['cart_item_qty'] ) ? absint( $_POST['cart_item_qty'] ) : '' );

		if ( $cart_item_key && $cart_item_qty ) {

			WC()->cart->set_quantity($cart_item_key, $cart_item_qty, $refresh_totals = true);

			ob_start();

			woocommerce_mini_cart();

			$mini_cart = ob_get_clean();

			$data = array(
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					array(
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
					)
				),
				'cart_hash' => WC()->cart->get_cart_hash(),
			);

			wp_send_json( $data );
		}

		wp_send_json_error();
	}

	/**
	 * Enable Qty controls on mini-cart & disable select (if enabled)
	 * @since 1.6.6
	 */
	function enable_qty_controls_minicart(){
		add_filter('theme_mod_single_atc_qty_controls', '__return_true');
		add_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
	}

	function enable_qty_controls_minicart__remove(){
		remove_filter('theme_mod_single_atc_qty_controls', '__return_true');
		remove_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
	}

	public static function cs_cart_btn_class( $html ){
		$html = str_replace('"button ', '"btn btn-line-active ', $html);
		$html = str_replace(' button ', ' btn btn-line-active ', $html);
		return $html;
	}

	public static function cs_cart_loop_skin(){
		return 'default';
	}

	public function cross_sells_bubble(){

		$output = '';

		if( isset($_REQUEST['wc-ajax']) && 'add_to_cart' === reycore__clean($_REQUEST['wc-ajax']) ){
			if( ! (isset($_REQUEST['product_id']) && $product_id = absint($_REQUEST['product_id'])) ) {
				return $output;
			}
		}

		else if( ! (isset($_REQUEST['add-to-cart']) && $product_id = absint($_REQUEST['add-to-cart'])) ){
			return $output;
		}

		$settings = apply_filters('reycore/woocommerce/cartpanel/cross_sells_bubble', [
			'enable' => get_theme_mod('header_cart__cross_sells_bubble', true),
			'limit' => get_theme_mod('header_cart__cross_sells_bubble_limit', 3),
			'rating' => false,
			'title' => get_theme_mod('header_cart__cross_sells_bubble_title', __( 'You may also like&hellip;', 'woocommerce' )),
			'add_to_cart' => esc_html__( 'Continue shopping', 'woocommerce' ),
		]);

		if( ! $settings['enable'] ){
			return $output;
		}

		if( ! ($product = wc_get_product($product_id)) ){
			return $output;
		}

		if( ! ($cs_ids = $product->get_cross_sell_ids()) ){
			return $output;
		}

		if( ! is_array($cs_ids) ){
			return $output;
		}

		$cs_ids = array_unique( $settings['limit'] > 0 ? array_slice( $cs_ids, 0, $settings['limit'] ) : $cs_ids );

		$exclude_items = [];

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['data'] && $pid = $item['data']->get_id() ) {
				$exclude_items[] = $pid;
			}
		}

		if( ! ($cross_sells_data = $this->render_cross_sells( $cs_ids, $exclude_items )) ){
			return $output;
		}

		$output .= '<div class="rey-crossSells-bubble" >';
			$output .= sprintf('<h3 class="rey-crossSells-bubble-title">%s</h3>', $settings['title']);
			$output .= $cross_sells_data;
			$output .= sprintf('<div><a class="rey-crossSells-bubble-close btn btn-primary-outline btn--block" href="#">%s</a></div>', $settings['add_to_cart'] );
		$output .= '</div>';

		return $output;
	}

	function cross_sells_carousel(){

		$settings = apply_filters('reycore/woocommerce/cartpanel/cross_sells_carousel', [
			'enable' => get_theme_mod('header_cart__cross_sells_carousel', true),
			'limit' => get_theme_mod('header_cart__cross_sells_carousel_limit', 10),
			'title' => get_theme_mod('header_cart__cross_sells_carousel_title', __( 'You may also like&hellip;', 'woocommerce' )),
			'mobile' => get_theme_mod('header_cart__cross_sells_carousel_mobile', true),
			'autoplay' => false,
			'autoplay_duration' => 3000,
		]);

		if( ! $settings['enable'] ){
			return;
		}

		$cross_sells = WC()->cart->get_cross_sells();
		$cross_sells = array_unique( $settings['limit'] > 0 ? array_slice( $cross_sells, 0, $settings['limit'] ) : $cross_sells );

		if( ! ($cross_sells_data = $this->render_cross_sells( $cross_sells )) ){
			return;
		}

		$class = 'splide rey-crossSells-carousel';

		if( $settings['mobile'] ){
			$class .= ' --dnone-desktop --dnone-tablet';
		}

		$slider_config = wp_json_encode([
			'autoplay' => $settings['autoplay'],
			'autoplaySpeed' => $settings['autoplay_duration'],
		]);

		$assets_config = reyCoreAssets()->get_assets_paths([
			'styles' => ['rey-wc-general', 'rey-splide'],
			'scripts' => ['reycore-wc-header-cart-crosssells-panel', 'splidejs', 'rey-splide'],
		]);

		printf('<div class="%1$s" data-slider-config=\'%4$s\' data-assets-config=\'%5$s\'><h3 class="rey-crossSells-carousel-title">%2$s</h3>%3$s</div>',
			$class,
			$settings['title'],
			$cross_sells_data,
			$slider_config,
			!empty($assets_config) ? wp_json_encode($assets_config) : ''
		);
	}

	function render_cross_sells( $ids = [], $exclude = [] ){

		add_filter('woocommerce_loop_add_to_cart_link', [__CLASS__, 'cs_cart_btn_class'], 20);
		add_filter('theme_mod_look_skin', [__CLASS__, 'cs_cart_loop_skin'], 100);

		$cs_data = '';

		foreach ($ids as $csid) {

			if( !empty($exclude) && in_array($csid, $exclude, true) ){
				continue;
			}

			if( isset($GLOBALS['post']) ) {
				$original_post = $GLOBALS['post'];
			}

			$GLOBALS['post'] = get_post( $csid ); // WPCS: override ok.
			setup_postdata( $GLOBALS['post'] );

			if( ! ($product = wc_get_product($GLOBALS['post'])) ){
				continue;
			}

			if ( ! $product->is_purchasable() ) {
				continue;
			}

			if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $product->is_in_stock() ) {
				continue;
			}

			ob_start(); ?>

			<div class="rey-crossSells-item splide__slide" data-id="<?php echo esc_attr($csid) ?>">

				<div class="rey-crossSells-itemThumb">
					<?php
						woocommerce_template_loop_product_link_open();
						woocommerce_template_loop_product_thumbnail();
						woocommerce_template_loop_product_link_close();
					?>
				</div>

				<div class="rey-crossSells-itemContent">
					<?php

						if( class_exists('ReyCore_WooCommerce_Loop') ):
							ReyCore_WooCommerce_Loop::getInstance()->component_brands();
						endif;

						echo sprintf(
							'<h4 class="%s"><a href="%s">%s</a></h4>',
							esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'rey-crossSells-itemTitle' ) ),
							esc_url(get_the_permalink()),
							get_the_title()
						);

						woocommerce_template_loop_price();

						add_filter('woocommerce_product_add_to_cart_text', [ReyCore_WooCommerce__Cart::getInstance(), 'cross_sells_buttons_text'], 20, 2);
							woocommerce_template_loop_add_to_cart([
								'wrap_button' => false,
								'supports_qty' => false,
							]);
						remove_filter('woocommerce_product_add_to_cart_text', [ReyCore_WooCommerce__Cart::getInstance(), 'cross_sells_buttons_text'], 20);

						do_action('reycore/woocommerce/cart/crosssells', $csid);

						if( apply_filters('reycore/woocommerce/cart/crosssells/quickview', true) &&
							class_exists('ReyCore_WooCommerce_QuickView') &&
							in_array(true, reycore_wc_get_loop_components('quickview'), true) ){
							echo ReyCore_WooCommerce_QuickView::getInstance()->get_button_html( $csid, 'btn btn-line-active' );
						}
					?>
				</div>
			</div><?php

			$cs_data .= ob_get_clean();

			if( isset($original_post) ) {
				$GLOBALS['post'] = $original_post; // WPCS: override ok.
			}
		}

		remove_filter('woocommerce_loop_add_to_cart_link', [__CLASS__, 'cs_cart_btn_class'], 20);
		remove_filter('theme_mod_look_skin', [__CLASS__, 'cs_cart_loop_skin'], 100);

		if( ! $cs_data ){
			return;
		}

		return '<div class="splide__track"><div class="rey-crossSells-itemsWrapper splide__list">' . $cs_data . '</div></div>';
	}

	/**
	 * This feature is exclusive to desktops.
	 * No reason to load it on mobiles.
	 */
	function disable_header_cart__cross_sells_bubble($mod){

		if( reycore__is_mobile() && reycore__supports_mobile_caching() ){
			return false;
		}

		return $mod;
	}

	/**
	 * This feature is exclusive to mobile if specified.
	 * No reason to load it on desktop.
	 */
	function header_cart__cross_sells_carousel($mod){

		if( get_theme_mod('header_cart__cross_sells_carousel_mobile', true) ){
			if( ! reycore__is_mobile() && reycore__supports_mobile_caching() ){
				return false;
			}
		}

		return $mod;
	}

	/**
	 * Output the view cart button.
	 */
	function shopping_cart_button_view_cart() {

		if( ! get_theme_mod('header_cart__btn_cart__enable', true) ){
			return;
		}

		$text = esc_html__( 'View cart', 'woocommerce' );

		if( $custom_text = get_theme_mod('header_cart__btn_cart__text', '' ) ){
			$text = $custom_text;
		}

		echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="btn btn-secondary wc-forward button--cart">' . $text . '</a>';
	}

	function shopping_cart_proceed_to_checkout() {

		if( site_url() === wc_get_checkout_url() ){
			return;
		}

		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="btn btn-primary checkout wc-forward">' . esc_html__( 'Checkout', 'woocommerce' ) . '</a>';
	}

	function add_continue_shopping_button(){

		if( ! get_theme_mod('header_cart__close_extend', false) ){
			return;
		}

		if( ! get_theme_mod('header_cart__continue_shop', false) ){
			return;
		}

		printf(
			'<div class="rey-cartPanel-continue"><button class="btn btn-line-active">%s</button></div>',
			esc_html__( 'Continue shopping', 'woocommerce' )
		);
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_MiniCart
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

ReyCore_WooCommerce_MiniCart::getInstance();
