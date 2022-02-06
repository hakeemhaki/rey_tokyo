<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Sidebar') ):
    /**
	 * Global Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Sidebar {

		function __construct(){
			add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
		}

		/**
		 * Add custom settings into Elementor's Global Settings
		 *
		 * @since 1.0.0
		 */
		function before_render( $element )
		{
			if( $element->get_unique_name() !== 'sidebar' ){
				return;
			}

			$settings = $element->get_settings();

			if( isset($settings['sidebar']) && $sidebar = $settings['sidebar'] ){

				$sidebar_class[] = $sidebar;
				$sidebar_class[] = 'widget-area';

				$is_toggable = apply_filters('reycore/sidebar/toggable_support', get_theme_mod('sidebar_shop__toggle__enable', false) && $sidebar !== 'filters-top-sidebar' && strpos($sidebar, 'filters-top-sidebar') === false );

				if(  $is_toggable ){
					reyCoreAssets()->add_scripts('reycore-wc-loop-toggable-widgets');
					$sidebar_class[] = '--supports-toggable';
					// $sidebar_class[] = 'rey-ecommSidebar';
				}

				$element->add_render_attribute( '_wrapper', 'class', implode( ' ', array_map( 'sanitize_html_class', apply_filters('rey/content/sidebar_class', $sidebar_class, $sidebar) ) ) );

				reyCoreAssets()->add_styles('rey-wc-tag-widgets');

			}

		}
	}
endif;
