<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Global') ):
    /**
	 * Global Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Global {

		function __construct(){
			add_action( 'elementor/element/global-settings/style/before_section_end', [$this, 'global_settings'], 10);
		}

		/**
		 * Add custom settings into Elementor's Global Settings
		 *
		 * @since 1.0.0
		 */
		function global_settings( $element )
		{
			$element->remove_control( 'elementor_container_width' );
		}
	}
endif;
