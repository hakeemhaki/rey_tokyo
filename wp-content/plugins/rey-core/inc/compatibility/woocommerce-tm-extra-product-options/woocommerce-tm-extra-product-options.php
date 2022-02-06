<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( class_exists('WooCommerce') && !class_exists('ReyCore_Compatibility__TMExtraProductOptions') ):

	class ReyCore_Compatibility__TMExtraProductOptions
	{
		public function __construct()
		{

			if ( ! class_exists( 'Themecomplete_Extra_Product_Options_Setup' ) ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
		}


		public function load_scripts(){
            // wp_enqueue_style( 'reycore-tm-epo-styles', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
            wp_enqueue_script( 'reycore-tm-epo-scripts', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/script.js', ['jquery', 'rey-script', 'reycore-woocommerce'], REY_CORE_VERSION, true );
		}
	}

	new ReyCore_Compatibility__TMExtraProductOptions();
endif;
