<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('FP_Free_Gift') && !class_exists('ReyCore_Compatibility__FreeGiftsForWooCommerce') ):

	class ReyCore_Compatibility__FreeGiftsForWooCommerce
	{
		public function __construct() {
			add_action('init', [$this, 'init']);
		}

		function init(){
			add_filter('reycore/woocommerce/cartpanel/show_qty', [$this, 'hide_qty'], 10, 2);
			add_action( 'woocommerce_before_mini_cart' , [$this , 'add_to_cart_automatic_gift_product_ajax'] ) ;
			add_action( 'woocommerce_before_mini_cart' , [$this , 'remove_gift_product_from_cart_ajax'] ) ;
		}

		function hide_qty( $status, $cart_item ){

			if( isset($cart_item['fgf_gift_product']) ){
				return false;
			}

			return $status;
		}

		function maybe_check_minicart(){

			$can = false;

			if( isset($_REQUEST['action']) && 'rey_update_minicart' === reycore__clean($_REQUEST['action']) ){
				$can = true;
			}

			if( isset($_REQUEST['action']) && 'reycore_ajax_add_to_cart' === reycore__clean($_REQUEST['action']) ){
				$can = true;
			}

			if( isset($_REQUEST['wc-ajax']) && 'remove_from_cart' === reycore__clean($_REQUEST['wc-ajax']) ){
				$can = true;
			}

			if( isset($_REQUEST['wc-ajax']) && 'get_refreshed_fragments' === reycore__clean($_REQUEST['wc-ajax']) ){
				$can = true;
			}

			if( isset($_REQUEST['wc-ajax']) && 'add_to_cart' === reycore__clean($_REQUEST['wc-ajax']) ){
				$can = true;
			}

			return $can;
		}

		function add_to_cart_automatic_gift_product_ajax(){

			if( ! $this->maybe_check_minicart() ){
				return;
			}

			FGF_Gift_Products_Handler::automatic_gift_product( false ) ;
			FGF_Gift_Products_Handler::bogo_gift_product( false ) ;
			FGF_Gift_Products_Handler::coupon_gift_product( false ) ;
		}

		function remove_gift_product_from_cart_ajax() {

			if( ! $this->maybe_check_minicart() ){
				return;
			}

			FGF_Gift_Products_Handler::remove_gift_products() ;
		}
	}

	new ReyCore_Compatibility__FreeGiftsForWooCommerce;
endif;
