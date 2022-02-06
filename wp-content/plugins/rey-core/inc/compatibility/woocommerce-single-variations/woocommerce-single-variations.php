<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WooCommerce_Single_Variations') && !class_exists('ReyCore_Compatibility__WooCommerceSingleVariations') ):

	class ReyCore_Compatibility__WooCommerceSingleVariations
	{
		public function __construct() {
			add_action('init', [$this, 'init']);
		}

		function init(){
			global $woocommerce_single_variations_options;

			if( ! (isset($woocommerce_single_variations_options['enable']) && $woocommerce_single_variations_options['enable']) ){
				return;
			}

			add_filter('reycore/ajaxfilters/post_types_count', [$this, 'filter_product_count_post_types']);
		}

		function filter_product_count_post_types( $types ){

			$types[] = 'product_variation';

			return $types;
		}
	}

	new ReyCore_Compatibility__WooCommerceSingleVariations;
endif;
