<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Scroll_Decorations__Skewed') ):

	class ReyCore_Widget_Scroll_Decorations__Skewed extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'skewed';
		}

		public function get_title() {
			return __( 'Skewed style', 'rey-core' );
		}

		public function render() {

			$settings = $this->parent->get_settings_for_display();

			$this->parent->render_start($settings);
			?>
			<span class="rey-scrollDeco-line"></span>
			<?php
			$this->parent->render_end();

			reyCoreAssets()->add_styles('reycore-elementor-scroll-deco');
			reyCoreAssets()->add_scripts('reycore-elementor-scroll-deco');
		}

	}
endif;
