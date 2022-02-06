<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WooCommerce') && defined( 'YITH_WCPB_VERSION' ) && !class_exists('ReyCore_Compatibility__YithProductBundles') ):

	class ReyCore_Compatibility__YithProductBundles
	{
		private $settings = [];

		const ASSET_HANDLE = 'reycore-yith-pb';

		public function __construct()
		{
			add_filter('reycore/module/extra-variation-images/maybe_surpress_filter', [$this, 'surpress_filters_extra_variation_images'], 20);
		}

		function surpress_filters_extra_variation_images( $stat ){

			if( ! get_theme_mod('enable_extra_variation_images', false) ){
				return $stat;
			}

			$product = wc_get_product();

			if( ! $product ){
				global $product;
			}

			if( ! $product ){
				return $stat;
			}

			if( $product->get_type() === 'yith_bundle' ){
				return true;
			}

			return $stat;
		}

	}

	new ReyCore_Compatibility__YithProductBundles();
endif;
