<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Woo_Pdp_Badge')):

class ReyCore_Widget_Woo_Pdp_Badge extends ReyCore_Widget_Woo_Base {

	public function get_name() {
		return 'reycore-woo-pdp-badge';
	}

	public function get_title() {
		return __( 'Badges (PDP)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function get_categories() {
		return [ 'rey-woocommerce-pdp' ];
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

			$selectors = [
				'wrapper' => '{{WRAPPER}}',
			];

			$this->add_control(
				'badge_position',
				[
					'label' => esc_html__( 'Badges Position to render', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'before_title',
					'options' => [
						'before_title'  => esc_html__( 'Before Title', 'rey-core' ),
						'before_meta'  => esc_html__( 'Before Meta', 'rey-core' ),
						'after_meta'  => esc_html__( 'After Meta', 'rey-core' ),
					],
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
						$selectors['wrapper'] => 'text-align: {{VALUE}};',
					],
				]
			);


		$this->end_controls_section();

	}

	function render_template() {

		if( ! class_exists('ReyCore_WooCommerce_ProductBadges') ){
			return;
		}

		$badgesInstance = ReyCore_WooCommerce_ProductBadges::getInstance();

		$this->_settings = $this->get_settings_for_display();

		$badges = $badgesInstance->get_badges();

		if( ! is_array($badges) ){
			return;
		}

		foreach ($badges as $key => $badge) {

			if( ! $badge['product_page'] ){
				continue;
			}

			if( ! (isset($badge['type']) && ($type = $badge['type'])) ){
				continue;
			}

			if( $badge['product_page_position'] !== $this->_settings['badge_position'] ){
				continue;
			}

			if( $type === 'text'){
				$badgesInstance->render_text($badge);
			}
			else if( $type === 'image'){
				$badgesInstance->render_image($badge);
			}
		}

	}

}
endif;
