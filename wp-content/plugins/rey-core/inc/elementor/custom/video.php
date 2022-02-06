<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Video') ):
    /**
	 * Text Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Video {

		function __construct(){

			add_action( 'elementor/element/video/section_video_style/before_section_end', [$this, 'icon_settings'], 10);

		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function icon_settings( $element )
		{
			$element->start_injection( [
				'of' => 'play_icon_color',
			] );

			$element->add_control(
				'play_icon_icon',
				[
					'label' => __( 'Play Icon Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Default (play circle)', 'rey-core' ),
						'caret'  => __( 'Caret right', 'rey-core' ),
						'chevron'  => __( 'Chevron right', 'rey-core' ),
						'play'  => __( 'Play icon', 'rey-core' ),
						'yt'  => __( 'Youtube Play icon', 'rey-core' ),
					],
					'prefix_class' => 'rey-video-iconType-'
				]
			);

			$element->end_injection();


		}

	}
endif;
