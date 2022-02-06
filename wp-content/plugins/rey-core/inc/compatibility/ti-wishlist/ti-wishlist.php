<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wishlist plugin integration
 * https://wordpress.org/plugins/ti-woocommerce-wishlist/
 */
if( class_exists('TInvWL_Public_AddToWishlist') && class_exists('WooCommerce') && !class_exists('ReyCore_Compatibility__TIWishlist') ):

	class ReyCore_Compatibility__TIWishlist
	{
		const ASSET_HANDLE = 'ti-wishlist';

		public function __construct()
		{
			add_action('init', [$this, 'init']);
		}

		function init(){

			add_filter( 'tinvwl_enable_wizard', '__return_false', 10);
			add_filter( 'tinvwl_prevent_automatic_wizard_redirect', '__return_true', 10);
			add_filter( 'tinvwl_wishlist_item_thumbnail', [ $this, 'prevent_product_slideshows'], 10, 3 );

			remove_action( 'woocommerce_before_shop_loop_item', 'tinvwl_view_addto_htmlloop', 9 );
			remove_action( 'woocommerce_after_shop_loop_item', 'tinvwl_view_addto_htmlloop', 9 );
			remove_action( 'woocommerce_after_shop_loop_item', 'tinvwl_view_addto_htmlloop', 10 );

			add_filter('reycore/woocommerce/wishlist/default_catalog_position', [$this, 'add_default_catalog_position']);
			add_filter('reycore/woocommerce/wishlist/ids', [$this, 'get_wishlist_ids']);
			add_filter('reycore/woocommerce/wishlist/button_html', [$this, 'button_html']);
			add_filter('reycore/woocommerce/wishlist/url', [$this, 'wishlist_url']);
			add_filter('reycore/woocommerce/wishlist/counter_html', [$this, 'wishlist_counter_html']);

			add_action( 'woocommerce_single_product_summary', [ $this, 'show_add_to_wishlist_in_product_page_catalog_mode'], 20);

			add_action( 'wp_footer', [$this, 'force_scripts']);
			add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			add_filter( 'rey/main_script_params', [ $this, 'script_params'], 20 );

		}


		function add_default_catalog_position(){
			return tinv_get_option( 'add_to_wishlist_catalog', 'position' ) === 'above_thumb' ? 'topright' :  'bottom';
		}

		function get_wishlist_ids( $ids ){

			$products = TInvWL_Public_Wishlist_View::instance()->get_current_products();

			if( empty($products) ){
				return $ids;
			}

			return wp_list_pluck($products, 'product_id');
		}

		function button_html(){
			return do_shortcode('[ti_wishlists_addtowishlist loop=yes]');
		}

		function wishlist_url(){
			return tinv_url_wishlist_default();
		}

		function wishlist_counter_html(){
			return '<span class="wishlist_products_counter"><span class="wishlist_products_counter_number"></span></span>';
		}

		function prevent_product_slideshows($html, $wl_product, $product){

			if( get_theme_mod('loop_extra_media', 'second') === 'slideshow' ) {
				$product->set_catalog_visibility('hidden');
				$html = str_replace('rey-productSlideshow', 'rey-productSlideshow --prevent-thumbnail-sliders --show-first-only', $html);
			}

			return $html;
		}

		function show_add_to_wishlist_in_product_page_catalog_mode(){
			if( reycore_wc__is_product() ) {
				$product = wc_get_product();
				if(
					! $product->is_purchasable() &&
					( $product->get_regular_price() || $product->get_sale_price() ||
						( $product->is_type( 'variable' ) && $product->get_price() !== '' )
					) ) {

					remove_action( 'woocommerce_single_product_summary', 'tinvwl_view_addto_htmlout', 29 );
					remove_action( 'woocommerce_single_product_summary', 'tinvwl_view_addto_htmlout', 31 );
					remove_action( 'woocommerce_before_add_to_cart_button', 'tinvwl_view_addto_html', 20 );
					remove_action( 'woocommerce_after_add_to_cart_button', 'tinvwl_view_addto_html', 0 );

					echo do_shortcode("[ti_wishlists_addtowishlist]");
				}
			}
		}

		function force_scripts(){

			if( ! function_exists('reycore_wc__get_account_panel_args') ){
				return;
			}

			$args = reycore_wc__get_account_panel_args();

			if( !($args['wishlist'] && $args['counter']) ){
				return;
			}

			wp_enqueue_script( 'tinvwl' );
		}

		public function script_params($params)
		{
			$params['wishlist_type'] = 'tinvwl';
			return $params;
		}

		public function enqueue_scripts(){
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
		}

		public function register_scripts(){
            wp_register_style( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}
	}

	new ReyCore_Compatibility__TIWishlist;
endif;
