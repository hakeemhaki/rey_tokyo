<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('Woocommerce_German_Market') && class_exists('WooCommerce') && !class_exists('ReyCore_Compatibility__GermanMarket') ):

	class ReyCore_Compatibility__GermanMarket
	{
		public function __construct()
		{
			add_action('init', [$this, 'init']);
		}

		function init(){
			add_filter( 'theme_mod_loop_show_prices', [$this,'disable_loop_prices'], 10 );

			if ( get_option( 'gm_deactivate_checkout_hooks', 'off' ) == 'off' ) {
				update_option( 'gm_deactivate_checkout_hooks', 'on' );
			}

		}

		function disable_loop_prices($status){
			return '2';
		}

		function load_styles(){
            wp_enqueue_style( 'rey-compat-german-market', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}

	}

	new ReyCore_Compatibility__GermanMarket;
endif;
