<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'WC_Bundles' ) && !class_exists('ReyCore_Compatibility__WooCommerceProductBundles') ):

	class ReyCore_Compatibility__WooCommerceProductBundles
	{
		private $settings = [];

		const ASSET_HANDLE = 'reycore-wc-bundles-styles';

		public function __construct()
		{
			add_action( 'init', [ $this, 'init' ] );
			add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}

		public function init(){
			$this->settings = apply_filters('reycore/compat/wc_bundles/params', []);
		}

		public function enqueue_scripts(){
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
		}

		public function register_scripts(){
            wp_register_style( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}
	}

	new ReyCore_Compatibility__WooCommerceProductBundles();
endif;
