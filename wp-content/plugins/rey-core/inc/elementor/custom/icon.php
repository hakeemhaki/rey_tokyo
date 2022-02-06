<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Icon') ):
    /**
	 * Icon Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Icon {

		function __construct(){
			add_action( 'elementor/element/icon/section_icon/before_section_end', [$this, 'add_controls'], 10);
		}

		/**
		 * Add custom settings into Elementor's image section
		 *
		 * @since 1.0.0
		 */
		function add_controls( $element )
		{
			$element->add_control(
				'icon_block',
				[
					'label' => esc_html__( 'Force as block', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--icon-block-'
				]
			);
		}


	}
endif;
