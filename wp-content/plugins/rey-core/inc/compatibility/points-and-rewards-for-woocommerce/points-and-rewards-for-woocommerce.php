<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WooCommerce') && defined('REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION') && !class_exists('ReyCore_Compatibility__PointsAndRewardsWc') ):

	class ReyCore_Compatibility__PointsAndRewardsWc
	{
		public $settings = [];

		public function __construct()
		{
			add_action( 'init', [ $this, 'init' ] );
		}

		public function init(){

			$this->set_settings();

			add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );
		}

		public function set_settings(){
			$this->settings = apply_filters('reycore/compatibility/points_and_rewards_wc/settings', [
			]);
		}


		public function load_styles(){

			if( ! (is_cart() || is_checkout()) ){
				return;
			}

            wp_enqueue_style( 'reycore-parw-styles', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}

	}

	new ReyCore_Compatibility__PointsAndRewardsWc();
endif;
