<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Woo_Pdp_Gallery')):

class ReyCore_Widget_Woo_Pdp_Gallery extends ReyCore_Widget_Woo_Base {

	// public function __construct( $data = [], $args = null ) {

	// 	if ( $data && isset($data['settings']) && $settings = $data['settings'] ) {
	// 		error_log(var_export( 'elem_contstri',1));
	// 	}

	// 	parent::__construct( $data, $args );
	// }

	public function get_name() {
		return 'reycore-woo-pdp-gallery';
	}

	public function get_title() {
		return __( 'Gallery (PDP)', 'rey-core' );
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
	// 	return 'https://support.reytheme.com/kb/rey-elements-header/#logo';
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

			if( class_exists('ReyCore_WooCommerce_ProductGallery_Base') ):
				$this->add_control(
					'layout',
					[
						'label' => esc_html__( 'Gallery layout', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							''  => esc_html__( '- Inherit -', 'rey-core' ),
						] + ReyCore_WooCommerce_ProductGallery_Base::get_gallery_types(),
					]
				);
			endif;

			// -----

			$this->add_control(
				'thumbnails_heading',
				[
				   'label' => esc_html__( 'Thumbnails', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'layout' => ['vertical', 'horizontal'],
					],
				]
			);

			$this->add_control(
				'max_thumbs',
				[
					'label' => esc_html__( 'Max. thumbs', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 25,
					'step' => 1,
					'condition' => [
						'layout' => ['vertical', 'horizontal'],
					],
				]
			);

			$this->add_control(
				'flip_thumbs',
				[
					'label' => esc_html__( 'Flip thumbs position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'condition' => [
						'layout' => ['vertical'],
					],
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'disable_cropping',
				[
					'label' => esc_html__( 'Disable thumbs cropping', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'condition' => [
						'layout' => ['vertical', 'horizontal'],
					],
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			// -----

			$this->add_control(
				'cascade_img_distance',
				[
					'label' => esc_html__( 'Images Distance', 'rey-core' ). ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 0,
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--gallery-cascade-images-distance: {{VALUE}}px;',
					],
					'condition' => [
						'layout' => 'cascade',
					],
				]
			);

			$this->add_control(
				'cascade_bullets',
				[
					'label' => esc_html__( 'Bullets Nav.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => esc_html__( 'Show', 'rey-core' ),
					'label_off' => esc_html__( 'Hide', 'rey-core' ),
					'return_value' => 'none',
					'condition' => [
						'layout' => 'cascade',
					],
					'selectors' => [
						'{{WRAPPER}} .rey-cascadeNav-wrapper' => 'display: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'cascade_bullets_position',
				[
					'label' => esc_html__( 'Bullets Nav. position', 'rey-core' ). ' (%)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-cascadeNav-wrapper' => 'left: {{VALUE}}% !important;',
					],
					'condition' => [
						'cascade_bullets!' => 'none',
						'layout' => 'cascade',
					],
				]
			);


			// -----

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize more Gallery options<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Product page - Layout to customize more gallery options that apply site-wide.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'product_gallery_layout'], admin_url( 'customize.php' ) ) ),
					'separator' => 'before'
				]
			);

		$this->end_controls_section();

		// $this->start_controls_section(
		// 	'section_styles',
		// 	[
		// 		'label' => __( 'Styles', 'rey-core' ),
		// 		'tab' => \Elementor\Controls_Manager::TAB_STYLE
		// 	]
		// );

		// $this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_product_gallery_layout', [$this, 'layout']);
		add_filter('theme_mod_product_gallery_thumbs_max', [$this, 'max_thumbs']);
		add_filter('theme_mod_product_gallery_thumbs_flip', [$this, 'flip_thumbs']);
		add_filter('theme_mod_product_gallery_thumbs_disable_cropping', [$this, 'disable_cropping']);

		woocommerce_show_product_images();

		remove_filter('theme_mod_product_gallery_layout', [$this, 'layout']);
		remove_filter('theme_mod_product_gallery_thumbs_max', [$this, 'max_thumbs']);
		remove_filter('theme_mod_product_gallery_thumbs_flip', [$this, 'flip_thumbs']);
		remove_filter('theme_mod_product_gallery_thumbs_disable_cropping', [$this, 'disable_cropping']);

	}

	function layout($mod){

		if( $layout = $this->_settings['layout'] ){
			return $layout;
		}

		return $mod;
	}

	function max_thumbs($mod){

		if( $max_thumbs = $this->_settings['max_thumbs'] ){
			return $max_thumbs;
		}

		return $mod;
	}

	function flip_thumbs($mod){

		if( $flip_thumbs = $this->_settings['flip_thumbs'] ){
			return $flip_thumbs === 'yes';
		}

		return $mod;
	}

	function disable_cropping($mod){

		if( $disable_cropping = $this->_settings['disable_cropping'] ){
			return $disable_cropping === 'yes';
		}

		return $mod;
	}

}
endif;
