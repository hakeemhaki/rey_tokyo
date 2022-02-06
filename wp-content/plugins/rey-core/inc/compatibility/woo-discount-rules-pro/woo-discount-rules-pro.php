<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( (defined('WDR_PRO_VERSION') || defined('WDR_VERSION')) && !class_exists('ReyCore_Compatibility__WooDiscountRulesPro') ):

	class ReyCore_Compatibility__WooDiscountRulesPro
	{
		public function __construct() {
			add_action('init', [$this, 'init']);
		}

		function init(){
			add_filter('reycore/woocommerce/cartpanel/show_qty', [$this, 'hide_qty'], 10, 2);
			add_filter('reycore/woocommerce/discount_labels/sale_price', [$this, 'sale_price'], 10, 2);
			add_action('advanced_woo_discount_rules_after_save_rule', [$this, 'clear_labels_transients']);
		}

		function hide_qty( $status, $cart_item ){

			if( isset($cart_item['wdr_free_product']) ){
				return false;
			}

			return $status;
		}

		function sale_price( $sale_price, $product ){

			if( ! class_exists('\Wdr\App\Controllers\Configuration') ){
				return $sale_price;
			}

			$calculate_discount_from = Wdr\App\Controllers\Configuration::getInstance()->getConfig('calculate_discount_from', 'sale_price');

			if ($calculate_discount_from == 'regular_price') {
				$product_price = Wdr\App\Helpers\Woocommerce::getProductRegularPrice($product);
			} else {
				$product_price = Wdr\App\Helpers\Woocommerce::getProductPrice($product);
			}

			return apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', $product_price, $product, 1, $product_price, 'discounted_price', true, false);

		}

		function clear_labels_transients(){

			global $wpdb;

			$transient_name = '_rey__discount_';

			$like_main = '%transient_' . $wpdb->esc_like( $transient_name ) . '%';
			$like_timeout = '%transient_timeout_' . $wpdb->esc_like( $transient_name ) . '%';

			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s ", $like_main, $like_timeout ) );

		}

	}

	new ReyCore_Compatibility__WooDiscountRulesPro;
endif;
