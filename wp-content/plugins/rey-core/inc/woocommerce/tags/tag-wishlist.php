<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Wishlist') ):

/**
 * ReyCore_WooCommerce_Wishlist class.
 *
 * @since 1.0.0
 */
class ReyCore_WooCommerce_Wishlist {

	/**
	 * Holds the reference to the instance of this class
	 * @var ReyCore_WooCommerce_Wishlist
	 */
	private static $_instance = null;

	private function __construct() {
		add_action('init', [$this, 'init']);
		add_action( 'wp_ajax_get_wishlist_data', [ $this, 'get_wishlist_data'] );
		add_action( 'wp_ajax_nopriv_get_wishlist_data', [ $this, 'get_wishlist_data'] );
	}

	function init(){

		if( ! function_exists('reycore_wc__check_wishlist') ){
			return;
		}

		if( ! reycore_wc__check_wishlist() ){
			return;
		}

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'reycore/woocommerce/account_panel', [ $this, 'display_wishlist_in_account_panel']);
	}

	public function display_wishlist_in_account_panel(){
		reycore__get_template_part('template-parts/woocommerce/header-account-wishlist');
		$this->load_dependencies();
	}

	public function load_dependencies(){
		reyCoreAssets()->add_scripts('simple-scrollbar');
		reyCoreAssets()->add_styles('simple-scrollbar');
		add_action( 'wp_footer', [ $this, 'item_template']);
	}

	public static function catalog_default_position(){

		if( get_theme_mod('loop_wishlist_position', '') !== '' ){
			return get_theme_mod('loop_wishlist_position', 'bottom');
		}

		return apply_filters('reycore/woocommerce/wishlist/default_catalog_position', 'bottom');
	}

	public static function get_wishlist_url(){
		return apply_filters('reycore/woocommerce/wishlist/url', false);
	}

	public static function get_wishlist_counter_html(){
		$html = '<span class="rey-wishlist-counter"><span class="rey-wishlist-counterNb"></span></span>';
		return apply_filters('reycore/woocommerce/wishlist/counter_html', $html);
	}

	/**
	* Add the icon, wrapped in custom div
	*
	* @since 1.0.0
	*/
	public static function get_button_html(){
		if( ! get_theme_mod('loop_wishlist_enable', true) ){
			return;
		}
		echo apply_filters('reycore/woocommerce/wishlist/button_html', '');
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['wishlist_type'] = 'native';
		$params['wishlist_empty_text'] = esc_html__('Your Wishlist is currently empty.', 'rey-core');
		return $params;
	}

	public function get_wishlist_products_query(){

		$items = apply_filters('reycore/woocommerce/wishlist/ids', []);

		if( empty($items) ){
			return [];
		}

		$args = [
			'limit'      => 16,
			'visibility' => 'catalog',
			'include'    => array_map('absint', $items),
			'orderby'    => 'post__in',
			'rey_wishlist' => true
		];

		return wc_get_products( $args );
	}

	/**
	 * Get wihslist
	 *
	 * @since   1.1.1
	 */
	public function get_wishlist_data()
	{
		$products = $this->get_wishlist_products_query();

		if( empty($products) ){
			wp_send_json_success( [] );
		}

		$data = [];

		foreach ($products as $key => $product) {

			if( isset($_REQUEST['ids']) && absint($_REQUEST['ids']) === 1 ){
				$data[] = $product->get_id();
				continue;
			}

			$product_data = [
				'id' => $product->get_id(),
				'type' => $product->get_type(),
				'title' => $product->get_title(),
				'image' => $product->get_image(),
				'stock_status' => $product->get_stock_status(),
				'url' => esc_url( get_the_permalink( $product->get_id() ) ),
				'price' => $product->get_price_html(),
				'is_purchasable' => $product->is_purchasable(),
				'is_in_stock' => $product->is_in_stock(),
				'supports_ajax_add_to_cart' => $product->supports('ajax_add_to_cart'),
				'sku' => $product->get_sku(),
				'add_to_cart_description' => strip_tags( $product->add_to_cart_description() ),
			];

			// ATC Button

			$atc_args = [
				'quantity' => 1,
				'class' => implode(' ', array_filter([
					'button',
					'product_type_' . $product_data['type'],
					$product_data['is_purchasable'] && $product_data['is_in_stock'] ? 'add_to_cart_button' : '',
					$product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
				])),
				'attributes' => [
					'data-product_id' => $product_data['id'],
					'data-product_sku' => $product_data['sku'],
					'aria-label' => $product_data['add_to_cart_description'],
					'rel' => 'nofollow',
				]
			];

			$cart_layout = get_theme_mod('header_cart_layout', 'bag');
			$cart_icon = !($cart_layout === 'disabled' || $cart_layout === 'text') ? reycore__get_svg_icon__core([ 'id'=> 'reycore-icon-' . $cart_layout ]) : '';
			$add_to_cart_contents = sprintf('<span>%s</span> %s', $product->add_to_cart_text(), $cart_icon);

			$product_data['add_to_cart'] = sprintf(
				'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
				esc_url( $product->add_to_cart_url() ),
				esc_attr( isset( $atc_args['quantity'] ) ? $atc_args['quantity'] : 1 ),
				esc_attr( isset( $atc_args['class'] ) ? $atc_args['class'] : 'button' ),
				isset( $atc_args['attributes'] ) ? reycore__implode_html_attributes( $atc_args['attributes'] ) : '',
				$add_to_cart_contents
			);


			$data[] = $product_data;
		}

		wp_send_json_success(apply_filters('reycore/woocommerce/wishlist/get_data', $data));
	}

	/**
	 * template used for wishlist items.
	 * @since 1.0.0
	 */
	public function item_template(){

		$thumb_classes = '';

		if( class_exists('ReyCore_WooCommerce_Loop') && ReyCore_WooCommerce_Loop::getInstance()->is_custom_image_height() ){
			$thumb_classes .= ' --customImageContainerHeight';
		} ?>

		<script type="text/html" id="tmpl-reyWishlistItem">
			<# for (var i = 0; i < data.num; i++) { #>
				<div class="rey-wishlistItem" style="transition-delay: {{i * 0.07}}s ">
					<div class="rey-wishlistItem-thumbnail <?php echo esc_attr($thumb_classes) ?>">
						<a href="{{{data.ob[i].url}}}" class="rey-wishlistItem-thumbnailLink">{{{data.ob[i].image}}}</a>
						<a class="rey-wishlistItem-remove" href="#" data-id="{{{data.ob[i].id}}}" aria-label="<?php echo get_theme_mod('wishlist__texts_rm', esc_html__('Remove from wishlist', 'rey-core')) ?>"><?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?></a>
					</div>
					<div class="rey-wishlistItem-name">
						<a href="{{{data.ob[i].url}}}">{{data.ob[i].title}}</a>
						<# if(!data.grid){ #>
							<div class="rey-wishlistItem-price">{{{data.ob[i].price}}}</div>
						<# } #>
					</div>
					<# if(data.grid){ #>
						<div class="rey-wishlistItem-price">{{{data.ob[i].price}}}</div>
					<# } #>
					<?php if( ! reycore_wc__is_catalog()): ?>
					<# if( typeof data.ob[i].add_to_cart ){ #>
						<div class="rey-wishlistItem-atc">{{{data.ob[i].add_to_cart}}}</div>
					<# } #>
					<?php endif; ?>
				</div>
			<# } #>
		</script>

		<?php
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Wishlist
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

ReyCore_WooCommerce_Wishlist::getInstance();
