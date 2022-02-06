<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Slider_Nav__Bullets') ):

	class ReyCore_Widget_Slider_Nav__Bullets extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'bullets';
		}

		public function get_title() {
			return __( 'Bullets Nav', 'rey-core' );
		}

		public function render() {

			reyCoreAssets()->add_styles(['reycore-widget-slider-nav-styles']);

			$this->parent->_settings = $this->parent->get_settings_for_display();

			$this->parent->render_start();
			$this->parent->render_end();

			reyCoreAssets()->add_scripts( $this->parent->rey_get_script_depends() );

		}

	}
endif;
