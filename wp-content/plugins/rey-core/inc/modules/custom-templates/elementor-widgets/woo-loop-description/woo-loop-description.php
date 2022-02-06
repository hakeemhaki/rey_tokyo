<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Woo_Loop_Description')):

class ReyCore_Widget_Woo_Loop_Description extends ReyCore_Widget_Woo_Base {

	public function get_name() {
		return 'reycore-woo-loop-description';
	}

	public function get_title() {
		return __( 'Product Archive Description', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function get_categories() {
		return [ 'rey-woocommerce-loop' ];
	}

	public function show_in_panel() {
		return $this->maybe_show_in_panel();
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

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

			$this->add_control(
				'display',
				[
					'label' => esc_html__( 'Description to display', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default (Top)', 'rey-core' ),
						'bottom'  => esc_html__( 'Bottom', 'rey-core' ),
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors['main'] = '{{WRAPPER}} .term-description, {{WRAPPER}} .rey-taxBottom';

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => $selectors['main'],
				]
			);

			$this->add_responsive_control(
				'alignment',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left'           => [
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-text-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-text-align-center',
						],
						'right'          => [
							'title'         => __( 'Right', 'rey-core' ),
							'icon'          => 'eicon-text-align-right',
						],
					],
					'default' => '',
					'selectors' => [
						$selectors['main'] => 'text-align: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		if( 'bottom' === $this->_settings['display'] ){
			ReyCore_WooCommerce_Taxonomies::getInstance()->output_bottom_content();
		}
		else {
			do_action( 'woocommerce_archive_description' );
		}

	}


}
endif;
