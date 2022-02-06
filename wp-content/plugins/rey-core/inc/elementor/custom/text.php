<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Text') ):
    /**
	 * Text Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Text {

		function __construct(){

			add_action( 'elementor/element/text-editor/section_style/before_section_end', [$this, 'layout_settings'], 10);
			add_action( 'elementor/element/text-editor/section_editor/before_section_end', [$this, 'editor_controls'], 10);
			add_action( 'elementor/element/reycore-acf-text/section_style/before_section_end', [$this, 'layout_settings'], 10);
			add_action( 'elementor/widget/text-editor/skins_init', [$this, 'load_skins'] );
			add_filter( 'widget_text', [$this, 'widget_text'], 10, 2 );

		}

		function load_skins( $element )
		{
			if( class_exists('ReyCore_Text_Dynamic_Skin') ){
				$element->add_skin( new ReyCore_Text_Dynamic_Skin( $element ) );
			}
		}

		function editor_controls( $element )
		{
			$editor = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'editor' );

			$editor['condition']['_skin'] = [''];
			$element->update_control( 'editor', $editor );

			$element->start_injection( [
				'of' => 'editor',
			] );

			$element->add_control(
				'rey_dynamic_source',
				[
					'label' => __( 'Text Source', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'title',
					'options' => [
						'title'  => __( 'Post Title', 'rey-core' ),
						'excerpt'  => __( 'Post excerpt', 'rey-core' ),
						'archive_title'  => __( 'Archive Title', 'rey-core' ),
						'desc'  => __( 'Archive Description', 'rey-core' ),
					],
					'condition' => [
						'_skin' => ['dynamic_text'],
					],
				]
			);

			$element->end_injection();
		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function layout_settings( $element )
		{

			$element->add_control(
				'rey_links_styles',
				[
					'label' => esc_html__( 'Links Style', 'rey-core' ). reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'anim-ul',
					'separator' => 'before',
					'options' => [
						''  => esc_html__( '- None -', 'rey-core' ),
						'anim-ul'  => esc_html__( 'Thickness Animated Underline', 'rey-core' ),
						'ltr-ul'  => esc_html__( 'Left to right underline', 'rey-core' ),
						'altr-ul'  => esc_html__( 'Active left to right underline', 'rey-core' ),
						'exp-ul'  => esc_html__( 'Expanding underline', 'rey-core' ),
						'simple-ul'  => esc_html__( 'Simple underline', 'rey-core' ),
					],
					'prefix_class' => 'u-links-'
				]
			);

			$element->add_control(
				'rey_links_deco_color',
				[
					'label' => esc_html__( 'Decoration Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--deco-color: {{VALUE}}',
					],
					'condition' => [
						'rey_links_styles!' => '',
					],
				]
			);

			$element->add_control(
				'remove_last_p',
				[
					'label' => __( 'Remove last bottom-margin', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'description' => __( 'Remove the last paragraph bottom margin.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'u-last-p-margin',
					'default' => '',
					'prefix_class' => '',
					'separator' => 'before'
				]
			);

			$element->add_control(
				'rey_toggle_text',
				[
					'label' => __( 'Toggle Text', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'description' => __( 'Make the text toggable. Needs live preview.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'separator' => 'before'
				]
			);

			$element->add_control(
				'rey_toggle_text_tags',
				[
					'label' => __( 'Strip tags', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'rey_toggle_text!' => '',
					],
				]
			);

			$element->add_control(
				'rey_toggle_text_more',
				[
					'label' => esc_html__( 'More text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: More', 'rey-core' ),
					'condition' => [
						'rey_toggle_text!' => '',
					],
				]
			);

			$element->add_control(
				'rey_toggle_text_less',
				[
					'label' => esc_html__( 'Less text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Less', 'rey-core' ),
					'condition' => [
						'rey_toggle_text!' => '',
					],
				]
			);

		}

		function widget_text($content, $all_settings){

			if( ! isset($all_settings['rey_toggle_text']) ){
				return $content;
			}

			if( $all_settings['rey_toggle_text'] === '' ){
				return $content;
			}

			$strip_tags = isset($all_settings['rey_toggle_text_tags']) && $all_settings['rey_toggle_text_tags'] !== '';

			$more_text = esc_html_x('Read more', 'Toggling the product excerpt.', 'rey-core');
			$less_text = esc_html_x('Less', 'Toggling the product excerpt.', 'rey-core');

			if( isset($all_settings['rey_toggle_text_more']) && $custom_more_text = $all_settings['rey_toggle_text_more'] ){
				$more_text = $custom_more_text;
			}

			if( isset($all_settings['rey_toggle_text_less']) && $custom_less_text = $all_settings['rey_toggle_text_less'] ){
				$less_text = $custom_less_text;
			}

			if( $strip_tags ){

				$intro = wp_strip_all_tags($content);
				$limit = 50;

				if ( strlen($intro) > $limit) {

					$content = '<div class="u-toggle-text --collapsed">';
						$content .= '<div class="u-toggle-content">';
						$content .= $intro;
						$content .= '</div>';
						$content .= '<button class="btn u-toggle-btn" data-read-more="'. $more_text .'" data-read-less="'. $less_text .'"></button>';
					$content .= '</div>';

					return $content;
				}
			}
			// keep tags
			else{
				$full_content = $content;
				if( $full_content ):
					$content = '<div class="u-toggle-text-next-btn --short">';
					$content .= $full_content;
					$content .= '</div>';
					$content .= '<button class="btn btn-line-active"><span data-read-more="'. $more_text .'" data-read-less="'. $less_text .'"></span></button>';
				endif;
			}

			return $content;
		}

		function toggle_text(){

		}

	}
endif;
