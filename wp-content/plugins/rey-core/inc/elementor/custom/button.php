<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Button') ):
    /**
	 * Button Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Button {

		function __construct(){
			add_action( 'elementor/element/button/section_button/before_section_end', [$this, 'button_settings'], 10);
			add_action( 'elementor/element/button/section_button/after_section_end', [$this, 'modal_settings'], 10);
			add_action( 'elementor/element/button/section_button/after_section_end', [$this, 'add_to_cart_settings'], 10);
			add_action( 'elementor/element/button/section_style/before_section_end', [$this, 'add_block_option'], 10);
			add_action( 'elementor/element/reycore-acf-button/section_button/before_section_end', [$this, 'button_settings'], 10);
			add_action( 'elementor/element/reycore-acf-button/section_button/after_section_end', [$this, 'modal_settings'], 10);
			add_action( 'elementor/element/reycore-acf-button/section_button/after_section_end', [$this, 'add_to_cart_settings'], 10);
			add_action( 'elementor/element/reycore-acf-button/section_style/before_section_end', [$this, 'add_block_option'], 10);
			add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function button_settings( $element )
		{
			// Get existing button type control
			$button_type = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'button_type' );

			// Add new styles
			$button_type['options'] = $button_type['options'] + ReyCoreElementor::button_styles();

			// Update the control
			$element->update_control( 'button_type', $button_type );


			$element->start_injection( [
				'of' => 'icon_indent',
			] );

			$element->add_responsive_control(
				'rey_icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .elementor-button .elementor-button-icon' => 'font-size: {{SIZE}}px;',
					],
					'condition' => [
						'selected_icon[value]!' => '',
					],
				]
			);

			$element->add_control(
				'rey_icon_style',
				[
					'label' => esc_html__( 'Icon Effect', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'None', 'rey-core' ),
						'aoh'  => esc_html__( 'Animate on hover', 'rey-core' ),
						'soh'  => esc_html__( 'Show on hover', 'rey-core' ),
						// 'soh'  => esc_html__( 'Show on parent column hover', 'rey-core' ),
					],
					'prefix_class' => '--icon-style-',
					'condition' => [
						'selected_icon[value]!' => '',
					],
				]
			);

			$element->end_injection();
		}

		/**
		 * Add option to enable modal link
		 *
		 * @since 1.0.0
		 */
		function modal_settings( $element )
		{

			$element->start_controls_section(
				'section_tabs',
				[
					'label' => __( 'Modal Settings', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT
				]
			);

			$element->add_control(
				'rey_enable_modal',
				[
					'label' => __( 'Enable Modal Link', 'rey-core' ),
					'description' => __( 'Enable to be able to open modal window. Make sure to add Modal section unique ID in the link field. Learn <a href="https://support.reytheme.com/kb/create-modal-sections/" target="_blank">how to create modals</a>.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => '',
				]
			);

			$element->add_control(
				'rey_modal_replace',
				[
					'label' => __( 'Text Replace in modal', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'key|value', 'rey-core' ),
					'description' => sprintf( __( 'Replace text in modal. Each replacement in a separate line. Separate replacement key from the value using %s character.', 'rey-core' ), '<code>|</code>' ),
					'classes' => 'elementor-control-direction-ltr',
					'condition' => [
						'rey_enable_modal!' => '',
					],
				]
			);

			$element->end_controls_section();
		}

		/**
		 * Add option
		 *
		 * @since 1.0.0
		 */
		function add_block_option( $element )
		{

			$element->add_responsive_control(
				'rey_btn_block',
				[
					'label' => esc_html__( 'Stretch Button', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--btn-block-%s-',
					'separator' => 'before'
				]
			);

		}

		/**
		 * Add option to enable add to cart link
		 *
		 * @since 1.0.0
		 */
		function add_to_cart_settings( $element )
		{

			$element->start_controls_section(
				'section_atc',
				[
					'label' => __( 'Add To Cart Settings', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT
				]
			);

			$element->add_control(
				'rey_atc_enable',
				[
					'label' => __( 'Enable Add To Cart Link', 'rey-core' ),
					'description' => __( 'Enable this option to force this button to link to adding a product to cart.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$element->add_control(
				'rey_atc_product',
				[
					'label' => esc_html__( 'Select Product', 'rey-core' ),
					'description' => esc_html__( 'Leave empty to automatically detect the product, if this button is placed inside a product page.', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'posts',
						'post_type' => 'product',
					],
					'condition' => [
						'rey_atc_enable!' => '',
					],
				]
			);

			$element->add_control(
				'rey_atc_checkout',
				[
					'label' => esc_html__( 'Redirect to checkout?', 'rey-core' ),
					'description' => __( 'You can basically transform this button into a "Buy Now" button. <strong>Please make sure the "Link" is empty!</strong>.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'rey_atc_enable!' => '',
					],
				]
			);

			$element->add_control(
				'rey_atc_text',
				[
					'label' => esc_html__( 'Custom "Added to cart" text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => [
						'rey_atc_enable!' => '',
					],
				]
			);

			$element->end_controls_section();
		}

		/**
		 * Render some attributes before rendering
		 *
		 * @since 1.0.0
		 **/
		function before_render( $element )
		{

			if( ! in_array($element->get_unique_name(), ['button', 'reycore-acf-button'], true) ){
				return;
			}

			$settings = $element->get_data('settings');

			if( isset($settings['rey_enable_modal']) && $settings['rey_enable_modal'] !== '' ){
				$this->do_modal( $element );
			}

			if( isset($settings['rey_atc_enable']) && $settings['rey_atc_enable'] !== '' ){
				$this->do_atc( $element );
			}

		}

		function do_modal($element){

			$settings = $element->get_settings();

			$element->add_render_attribute( 'button', 'data-rey-inline-modal', '' );

			// Replacements
			if ( isset($settings['rey_modal_replace']) && ! empty( $settings['rey_modal_replace'] ) ) {
				$replacements = explode( "\n", $settings['rey_modal_replace'] );

				foreach ( $replacements as $replacement ) {
					if ( ! empty( $replacement ) ) {

						$attr = explode( '|', $replacement, 2 );

						if ( ! isset( $attr[1] ) ) {
							$attr[1] = '';
						}

						$element->add_render_attribute( 'button', 'data-modal-replacements', wp_json_encode([
							esc_attr( $attr[0] ) => esc_attr( $attr[1] )
						]) );
					}
				}
			}

			// load modal scripts
			add_filter('reycore/modals/always_load', '__return_true');

		}

		function do_atc($element){

			if( ! class_exists('WooCommerce') ){
				return;
			}

			$settings = $element->get_settings();

			if( !( $product_id = $settings['rey_atc_product'] ) ){
				if( !(is_product() && ($product = wc_get_product()) && $product_id = $product->get_id()) ){
					return;
				}
			}

			$setting_url = $settings['link'];

			// override URL, but only works when link is empty
			if( empty($setting_url['url']) && isset($settings['rey_atc_checkout']) && ($settings['rey_atc_checkout'] !== '') ){

				$setting_url['url'] = add_query_arg([
					'add-to-cart' => $product_id,
					], wc_get_checkout_url()
				);

				$element->add_link_attributes( 'button', $setting_url, true );
				return;
			}

			$element->add_render_attribute( 'button', 'data-product_id', esc_attr($product_id) );
			$element->add_render_attribute( 'button', 'data-quantity', 1 );
			$element->add_render_attribute( 'button', 'class', 'add_to_cart_button ajax_add_to_cart' );

			if( isset($settings['rey_atc_checkout']) && ($settings['rey_atc_checkout'] !== '') ){
				$element->add_render_attribute( 'button', 'data-checkout', 1 );
				$element->add_render_attribute( 'button', 'class', '--prevent-aatc --prevent-open-cart' );
			}

			if( isset($settings['rey_atc_text']) ){
				$element->add_render_attribute( 'button', 'data-atc-text', esc_attr($settings['rey_atc_text']) );
			}

			reyCoreAssets()->add_scripts('reycore-elementor-elem-button-add-to-cart');

		}

	}
endif;
