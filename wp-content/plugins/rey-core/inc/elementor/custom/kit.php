<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Kit') ):
    /**
	 * Kit Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Kit {

		function __construct(){
			add_action( 'elementor/element/kit/section_settings-layout/before_section_end', [$this, 'kit_layout_settings'], 10);
			add_action( 'elementor/element/kit/section_layout-settings/before_section_end', [$this, 'kit_layout_settings'], 10);
			// add_action( 'elementor/element/kit/section_typography/before_section_end', [$this, 'kit_typography_settings'], 10);
			// add_action('elementor/db/before_save', [$this , 'force_theme_mods'], 10, 2);
		}

		/**
		 * Remove Container width as it directly conflicts with Rey's container settings
		 *
		 * @since 1.6.12
		 */
		function kit_layout_settings( $element ){
			$element->remove_control( 'container_width' );
			$element->remove_control( 'container_width_tablet' );
			$element->remove_control( 'container_width_mobile' );
		}

		/**
		 * Add options into Typography
		 *
		 * @since 1.9.6
		 */
		function kit_typography_settings( $element ){

			$element->start_injection( [
				'of' => 'paragraph_spacing_mobile',
			] );

			/**
			 * Add option to disable Rey's Customizer typography controls
			 */
			$element->add_control(
				'disable_rey_typography',
				[
					'label' => esc_html__( 'Disable Typography in Customizer', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => get_theme_mod('typography_inherit_elementor', false),
				]
			);

			$element->end_injection();
		}

		/**
		 * Disable Rey's Customizer typography controls
		 *
		 * @since 1.9.6
		 */
		public function force_theme_mods( $post_id , $is_meta ) {

			global $post;

			if( ! ($elementor_meta = get_post_meta( $post->ID, \Elementor\Core\Base\Document::PAGE_META_KEY, true )) ){
				return;
			}

			if( isset($elementor_meta['disable_rey_typography']) && $elementor_meta['disable_rey_typography'] === 'yes' ){
				set_theme_mod('typography_inherit_elementor', true);
			}
        }

	}
endif;
