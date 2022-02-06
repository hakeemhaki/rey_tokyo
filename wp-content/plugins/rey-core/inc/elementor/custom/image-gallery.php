<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Image_Gallery') ):
    /**
	 * Text Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Image_Gallery {

		function __construct(){

			add_action( 'elementor/element/image-gallery/section_gallery/before_section_end', [$this, 'columns_settings']);
			add_action( 'elementor/element/image-gallery/section_gallery_images/before_section_end', [$this, 'layout_settings']);

		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function layout_settings( $element )
		{

			$element->add_control(
				'rey_content_position',
				[
					'label' => __( 'Vertical Align', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => __( 'Default', 'rey-core' ),
						'top' => __( 'Top', 'rey-core' ),
						'center' => __( 'Middle', 'rey-core' ),
						'bottom' => __( 'Bottom', 'rey-core' ),
						'space-between' => __( 'Space Between', 'rey-core' ),
						'space-around' => __( 'Space Around', 'rey-core' ),
						'space-evenly' => __( 'Space Evenly', 'rey-core' ),
					],
					'selectors_dictionary' => [
						'top' => 'flex-start',
						'bottom' => 'flex-end',
					],
					'selectors' => [
						'{{WRAPPER}} .gallery' => 'align-items: {{VALUE}}',
						// '{{WRAPPER}} .gallery' => 'align-content: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'rey_opacity',
				[
					'label' => __( 'Opacity', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 1,
							'min' => 0.10,
							'step' => 0.01,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .gallery-item img' => 'opacity: {{SIZE}};',
					],
				]
			);

		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function columns_settings( $element )
		{

			$element->remove_control('gallery_columns');

			$gallery_columns = range( 1, 10 );
			$gallery_columns = array_combine( $gallery_columns, $gallery_columns );

			$element->add_responsive_control(
				'gallery_columns',
				[
					'label' => __( 'Columns', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 4,
					'options' => $gallery_columns,
					'prefix_class' => 'gallery-cols-%s-',
					'render_type' => 'template',
				]
			);

		}

	}
endif;
