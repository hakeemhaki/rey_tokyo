<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WooCommerce_Germanized') && class_exists('WooCommerce') && !class_exists('ReyCore_Compatibility__WooCommerceGermanized') ):

	class ReyCore_Compatibility__WooCommerceGermanized
	{
		public function __construct()
		{
			add_action('init', [$this, 'init']);
		}

		function init(){

			add_filter('woocommerce_get_script_data', [$this, 'checkout_params'], 10, 2);

		}

		function checkout_params($params, $handle){

			if( $handle === 'wc-checkout' ){
				$params['exclude_cloning_fields'] = '#shipping_address_type';
			}

			return $params;
		}

	}

	new ReyCore_Compatibility__WooCommerceGermanized;
endif;
