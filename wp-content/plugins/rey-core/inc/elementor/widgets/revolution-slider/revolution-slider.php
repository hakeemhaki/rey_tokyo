<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('RevSliderSlider') && !class_exists('ReyCore_Widget_Revolution_Slider') ):
/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Revolution_Slider extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-revolution-slider';
	}

	public function get_title() {
		return __( 'Revolution Slider', 'rey-core' );
	}

	public function get_icon() {
		return '';
	}

	public function get_categories() {
		return [ 'rey-theme-covers' ];
	}

	public function get_keywords() {
		return [ 'revolution', 'slider' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-revolution-slider-scripts' ];
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['slider_id']
        );

        return $element;
    }

	public function get_sliders() {

		$list = [];

		try {
			$_slider = new RevSliderSlider();

			if( method_exists('RevSliderSlider', 'get_sliders') ){

				$sliders	= $_slider->get_sliders();

				if(!empty($sliders)){
					foreach($sliders as $slider){
						$id			= $slider->get_alias();
						$list[$id] = $slider->get_title();
					}
				}
			}
        }catch(Exception $e){}

		return $list;
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);
		$sliders = $this->get_sliders();

		$this->add_control(
			'important_note',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => empty($sliders) ? __( 'To use this element you need to install Revolution Slider plugin.', 'rey-core' ) : '',
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->add_control(
			'slider_id',
			[
				'label' => __( 'Slider ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $sliders,
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'special_bg',
			[
				'label' => __( 'Special Background Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'None', 'rey-core' ),
					'stripes'  => __( 'Stripes', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'stripes_loading_bg',
			[
				'label' => __( 'Stripes - "Loading" Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-loading' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->add_control(
			'stripes_stripe_1',
			[
				'label' => __( 'Stripes - Stripe 1 Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-stripe1' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->add_control(
			'stripes_stripe_2',
			[
				'label' => __( 'Stripes - Stripe 2 Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-stripe2' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->add_control(
			'stripes_stripe_3',
			[
				'label' => __( 'Stripes - Stripe 3 Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-stripe3' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->end_controls_section();

	}

	function render_bg($settings){
		if($settings['special_bg'] === 'stripes'): ?>
			<div class="revStripes-bg">
				<div class="revStripes-loading"></div>
				<div class="revStripes-loading"></div>
				<div class="revStripes-loading"></div>
				<div class="revStripes-stripe1"></div>
				<div class="revStripes-stripe2"></div>
				<div class="revStripes-stripe3"></div>
			</div>
		<?php endif;
	}


	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render()
	{
		reyCoreAssets()->add_styles(['reycore-widget-revolution-slider-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$this->add_render_attribute( 'wrapper', 'class', 'rey-revSlider' );

		$content = '';

		$settings = $this->get_settings_for_display();
		$slider_alias = $settings['slider_id'];

		// Rev Output
		$output = new RevSliderOutput();
		$_slider = new RevSliderSlider();

		if( empty($slider_alias) || ! $_slider->alias_exists( $slider_alias ) ){
			return;
		}

		$_slider->init_by_alias( $slider_alias );
		$params = $_slider->get_params();

		$params['general']['slideshow']['waitForInit'] = apply_filters('reycore/elementor/revslider/waitforinit', (reycore__preloader_is_active() || $settings['special_bg'] === 'stripes') );

		if( ! \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ){

			$output->set_custom_settings($params);

			ob_start();
			$slider = $output->add_slider_to_stage($slider_alias);
			$content = ob_get_contents();
			ob_clean();
			ob_end_clean();
		}

		else {
			$height = $params['size']['height']['d'];

			$content = '<div class="revSlider-editMode" style="height:'.$height.'px;"><img src="' . RS_PLUGIN_URL_CLEAN . 'admin/assets/images/rs6_logo_2x.png"><div>'. __('Please preview in frontend.', 'rey-core') .'</div></div>';
		}

		$slider_id = $output->get_slider_id();

		$this->add_render_attribute( 'wrapper', 'data-rev-settings', wp_json_encode([
			'slider_id' => $slider_id
		]) ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php $this->render_bg( $settings ); ?>
			<?php echo $content; ?>
		</div>
		<?php
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
}
endif;
