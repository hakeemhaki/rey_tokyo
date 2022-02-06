<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Image') ):
    /**
	 * Image Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Image {

		function __construct(){
			add_action( 'elementor/widget/image/skins_init', [$this, 'image_skins'] );
			add_action( 'elementor/element/image/section_image/before_section_end', [$this, 'image_image_controls'], 10);
			add_action( 'elementor/element/image/section_style_image/before_section_end', [$this, 'image_style_controls'], 10);
		}

		/**
		 * Add custom skins into Elementor's Image widget
		 *
		 * @since 1.0.0
		 */
		function image_skins( $element )
		{
			if( class_exists('ReyCore_Image_Dynamic_Skin') ){
				$element->add_skin( new ReyCore_Image_Dynamic_Skin( $element ) );
			}
		}

		/**
		 * Add custom settings into Elementor's image section
		 *
		 * @since 1.0.0
		 */
		function image_image_controls( $element )
		{
			$image_control = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'image' );
			$image_control['condition']['_skin'] = [''];
			$element->update_control( 'image', $image_control );

			$element->start_injection( [
				'of' => 'image',
			] );

			$element->add_control(
				'source',
				[
					'label' => __( 'Image Source', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'featured_image',
					'options' => [
						'featured_image'  => __( 'Featured Image', 'rey-core' ),
					],
					'condition' => [
						'_skin' => ['dynamic_image'],
					],
				]
			);

			$element->end_injection();
		}


		/**
		 * Add custom settings into Elementor's style section
		 *
		 * @since 1.0.0
		 */
		function image_style_controls( $element )
		{

			$element->start_injection( [
				'of' => 'image_box_shadow',
			] );

			$element->add_control(
				'rey_custom_height__notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => '<em>'. __( 'These options are deprecated because Elementor added Custom height options natively. Please use those options instead.', 'rey-core' ) .'</em>',
					'content_classes' => 'rey-raw-html',
					'separator' => 'before',
				]
			);


			$element->add_control(
				'rey_custom_height',
				[
					'label' => __( 'Custom Height', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'ch',
					'prefix_class' => 'elementor-image--',
					'default' => '',
				]
			);


			$element->add_responsive_control(
				'rey_height',
				[
				'label' => __( 'Height', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 10,
							'max' => 1000,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'px',
						// 'size' => ,
					],
					'tablet_default' => [
						'unit' => 'px',
					],
					'mobile_default' => [
						'unit' => 'px',
					],
					'selectors' => [
						// TODO remove in 2.1+
						'{{WRAPPER}}.elementor-image--ch .elementor-image' => 'height: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}}.elementor-image--ch .elementor-widget-container' => 'height: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'rey_custom_height' => ['ch'],
					],
				]
			);

			$element->add_responsive_control(
				'rey_custom_height_contain',
				[
					'label' => __( 'Contain image?', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'contain',
					'prefix_class' => 'elementor-image-of%s-',
					'default' => '',
				]
			);

			$element->end_injection();
		}

	}
endif;
