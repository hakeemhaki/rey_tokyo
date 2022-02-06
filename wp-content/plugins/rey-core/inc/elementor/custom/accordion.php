<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Accordion') ):
    /**
	 * Accordion Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Accordion {

		function __construct(){
			add_action( 'elementor/element/accordion/section_title/before_section_end', [$this, 'settings'], 10);
			add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function settings( $element )
		{

			$element->start_injection( [
				'of' => 'selected_active_icon',
			] );

				$element->add_control(
					'rey_start_closed',
					[
						'label' => esc_html__( 'Start Closed?', 'rey-core' ) . reyCoreElementor::getReyBadge(),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
						'prefix_class' => 'rey-accClosed-',
						'render_type' => 'template',
					]
				);

			$element->end_injection();
		}

		function before_render($element){

			if( $element->get_unique_name() !== 'accordion' ){
				return;
			}

			$settings = $element->get_settings();

			if( $settings['rey_start_closed'] !== '' ){
				reyCoreAssets()->add_scripts('reycore-elementor-elem-accordion');
			}

		}
	}
endif;
