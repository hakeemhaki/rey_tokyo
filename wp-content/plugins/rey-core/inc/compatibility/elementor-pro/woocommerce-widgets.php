<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Compatibility__ElementorPro_WooCommerce') ):
	/**
	 * Elementor PRO
	 *
	 * @since 1.3.0
	 */
	class ReyCore_Compatibility__ElementorPro_WooCommerce
	{

		public function __construct()
		{

			add_action( 'elementor/frontend/widget/before_render', [$this, '_before_render'], 10);
			add_action( 'elementor/frontend/widget/after_render', [$this, '_after_render'], 10);

		}

		public function _before_render( $element ){

			$element_type = $element->get_unique_name();

			switch($element_type){
				case"woocommerce-product-price":
					$this->product_price__before();
					break;
			}
		}

		public function _after_render( $element ){

			$element_type = $element->get_unique_name();

			switch($element_type){
				case"woocommerce-product-price":
					$this->product_price__after();
					break;
			}
		}

		function product_price__before(){
			add_filter( 'woocommerce_get_price_html', [ ReyCore_WooCommerce_Single::getInstance(), 'discount_percentage' ], 10, 2);
		}

		function product_price__after(){
			remove_filter( 'woocommerce_get_price_html', [ ReyCore_WooCommerce_Single::getInstance(), 'discount_percentage' ], 10, 2);
		}

	}

	new ReyCore_Compatibility__ElementorPro_WooCommerce;
endif;
