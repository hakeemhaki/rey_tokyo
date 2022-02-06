<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( function_exists('yith_name_your_price_init') && !class_exists('ReyCore_Compatibility__YITH_NameYourPrice') ):

	class ReyCore_Compatibility__YITH_NameYourPrice
	{
		public function __construct()
		{
			add_filter('theme_mod_product_page_ajax_add_to_cart', [$this, 'maybe_disable_ajax_atc']);
		}

		function maybe_disable_ajax_atc( $value ){

			if( $this->is_name_your_price_product() ){
				return 'no';
			}

			return $value;
		}

		function is_name_your_price_product(){

			if ( is_product() ) {
				$product = wc_get_product();
				if ( $product && ywcnp_product_is_name_your_price( $product ) ){
					return true;
				}
			}

			return false;
		}
	}

	new ReyCore_Compatibility__YITH_NameYourPrice;
endif;
