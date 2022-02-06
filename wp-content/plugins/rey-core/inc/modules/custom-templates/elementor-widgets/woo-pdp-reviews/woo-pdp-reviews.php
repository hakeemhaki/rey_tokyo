<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Woo_Pdp_Reviews')):

class ReyCore_Widget_Woo_Pdp_Reviews extends ReyCore_Widget_Woo_Base {

	public function get_name() {
		return 'reycore-woo-pdp-reviews';
	}

	public function get_title() {
		return __( 'Reviews (PDP)', 'rey-core' );
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

			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'default'  => esc_html__( 'Button pill', 'rey-core' ),
						'classic'  => esc_html__( 'Classic', 'rey-core' ),
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

			$selectors = [
				'main' => '{{WRAPPER}} .product_title'
			];


		$this->end_controls_section();

	}

	function render_template() {

		$maybe_show[] = wc_review_ratings_enabled();

		if( $product = wc_get_product() ){
			$maybe_show[] = $product->get_reviews_allowed();
		}

		if( in_array(false, $maybe_show, true) ){

			if( current_user_can('administrator') && apply_filters('reycore/woocommerce/tabs_blocks/show_info_help', true) ){
				echo '<p class="__notice">';
					echo reycore__get_svg_icon(['id'=>'rey-icon-help']);
					printf( __('Seems like Reviews are disabled. Please check <a href="%s" target="_blank">this article</a> to learn how to enable them. <br>This text is only shown to administrators.', 'rey-core'), 'https://themeisle.com/blog/customer-reviews-for-woocommerce/');
				echo '</p>';
			}

			return;
		}

		$this->_settings = $this->get_settings_for_display();

		if( 'classic' === $this->_settings['layout'] ){
			comments_template();
			return;
		}

		reycore__get_template_part('template-parts/woocommerce/single-block-reviews');
	}

}
endif;
