<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Woo_Base')):

abstract class ReyCore_Widget_Woo_Base extends \Elementor\Widget_Base {

	public function render_template(){}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		reyTemplates()->elementor->__before_render($this);

		if( ! reyTemplates()->elementor->__should_render($this) ){
			return;
		}

		// $this->_settings = $this->get_settings_for_display();

		$this->render_template();

		reyTemplates()->elementor->__after_render($this);

	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}

	public static function get_tabs(){

		$blocks = [
			'description' => esc_html__('Description', 'rey-core'),
			'additional_information' => esc_html__('Specifications (Additional Information)', 'rey-core'),
			'information' => esc_html__('Custom Information', 'rey-core'),
			'reviews' => esc_html__('Reviews', 'rey-core'),
		];

		if( ($custom_tabs = get_theme_mod('single__custom_tabs', '')) && is_array($custom_tabs) && !empty($custom_tabs) ){
			foreach ($custom_tabs as $key => $c_tab) {
				$blocks['custom_tab_' . $key] = sprintf('%s (%s)', $c_tab['text'] , esc_html__('Custom', 'rey-core') );
			}
		}

		return $blocks;
	}


	public function get_icon_class(){

		$class = 'general';
		$name = $this->get_name();

		if( strpos($name, 'reycore-woo-') === 0 ){
			$class = str_replace('reycore-woo-', '', $name);
		}

		return sprintf('rey-editor-icons --%s', $class);
	}

	public function maybe_show_in_panel(){
		return true;
	}

}
endif;
