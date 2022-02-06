<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Heading') ):
    /**
	 * Heading Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Heading {

		function __construct(){
			add_action( 'elementor/widget/heading/skins_init', [$this,'heading_skins'] );
			add_action( 'elementor/element/heading/section_title/before_section_end', [$this,'heading_controls'], 10);
			add_action( 'elementor/element/heading/section_title_style/after_section_end', [$this,'heading_controls_styles'], 10);
			add_action( 'elementor/element/reycore-acf-heading/section_title_style/after_section_end', [$this,'heading_controls_styles'], 10);
			add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
		}

		/**
		 * Add custom skins into Elementor's Heading widget
		 *
		 * @since 1.0.0
		 */
		function heading_skins( $element )
		{
			if( class_exists('ReyCore_Heading_Dynamic_Skin') ){
				$element->add_skin( new ReyCore_Heading_Dynamic_Skin( $element ) );
			}
		}


		/**
		 * Add custom settings into Elementor's title section
		 *
		 * @since 1.0.0
		 */
		function heading_controls( $element )
		{
			$heading_title = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'title' );
			$heading_title['condition']['_skin'] = [''];
			$element->update_control( 'title', $heading_title );

			$element->start_injection( [
				'of' => 'title',
			] );

			$element->add_control(
				'source',
				[
					'label' => __( 'Title Source', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'title',
					'options' => [
						'title'  => __( 'Post Title', 'rey-core' ),
						'excerpt'  => __( 'Post excerpt', 'rey-core' ),
						'archive_title'  => __( 'Archive Title', 'rey-core' ),
						'desc'  => __( 'Archive Description', 'rey-core' ),
					],
					'condition' => [
						'_skin' => ['dynamic_title'],
					],
				]
			);

			$element->end_injection();
		}


		/**
		 * Add custom settings into Elementor's title style section
		 *
		 * @since 1.0.0
		 */
		function heading_controls_styles( $element )
		{

			$element->start_controls_section(
				'section_special_styles',
				[
					'label' => __( 'Special Styles', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$element->add_control(
				'rey_text_stroke',
				[
					'label' => __( 'Text Outline', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'stroke',
					'prefix_class' => 'elementor-heading--'
				]
			);

			$element->add_control(
				'rey_text_stroke_size',
				[
					'label' => __( 'Stroke size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}}' => '--heading-stroke-size: {{VALUE}}px',
					],
					'condition' => [
						'rey_text_stroke!' => '',
					],
				]
			);

			$element->add_responsive_control(
				'rey_vertical_text',
				[
					'label' => __( 'Vertical Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'vertical',
					'prefix_class' => 'elementor-heading-%s-'
				]
			);

			$element->add_control(
				'rey_vertical_text_reversed',
				[
					'label' => __( 'Vertical Text - Reversed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'yes',
					'prefix_class' => '--reversed-',
					'condition' => [
						'rey_vertical_text!' => '',
					],
				]
			);

			$element->add_control(
				'rey_parent_hover',
				[
					'label' => __( 'Animate on Parent Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'None', 'rey-core' ),
						'underline'  => __( 'Underline animation', 'rey-core' ),
						'show'  => __( 'Visible In', 'rey-core' ),
						'hide'  => __( 'Visible Out', 'rey-core' ),
						'slide_in'  => __( 'Slide In', 'rey-core' ),
						'slide_out'  => __( 'Slide Out', 'rey-core' ),
					],
					'prefix_class' => 'el-parent-animation--'
				]
			);

			$element->add_control(
				'rey_parent_hover_slide_direction',
				[
					'label' => __( 'Slide direction', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'bottom',
					'options' => [
						'top'  => __( 'Top', 'rey-core' ),
						'right'  => __( 'Right', 'rey-core' ),
						'bottom'  => __( 'Bottom', 'rey-core' ),
						'left'  => __( 'Left', 'rey-core' ),
					],
					'prefix_class' => '--slide-',
					'condition' => [
						'rey_parent_hover' => ['slide_in', 'slide_out'],
					],
				]
			);

			$element->add_control(
				'rey_parent_hover_slide_blur',
				[
					'label' => esc_html__( 'Slide with blur', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'ry',
					'prefix_class' => '--blur',
					'condition' => [
						'rey_parent_hover' => ['show', 'hide', 'slide_in', 'slide_out'],
					],
				]
			);

			$element->add_control(
				'rey_parent_hover_delay',
				[
					'label' => esc_html__( 'Transition delay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 5000,
					'step' => 50,
					'selectors' => [
						'{{WRAPPER}} .elementor-heading-title' => 'transition-delay: {{VALUE}}ms',
					],
					'condition' => [
						'rey_parent_hover' => ['show', 'hide', 'slide_in', 'slide_out'],
					],
				]
			);

			$element->add_control(
				'rey_parent_hover_trigger',
				[
					'label' => __( 'Parent Hover Trigger', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'column',
					'options' => [
						'column'  => __( 'Parent Column', 'rey-core' ),
						'section'  => __( 'Parent Section', 'rey-core' ),
					],
					'condition' => [
						'rey_parent_hover!' => '',
					],
					'prefix_class' => 'el-parent-trigger--'
				]
			);

			// parent hover
			// hover trigger - parent section / parent column
			// hover effect - underline / animate in

			$element->add_control(
				'rey_special_text_heading',
				[
				   'label' => esc_html__( 'WORD STYLES', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$element->add_control(
				'rey_special_text_styles',
				[
					'label' => esc_html__( 'Styles', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'None', 'rey-core' ),
						'grd'  => esc_html__( 'Gradient word', 'rey-core' ),
						'out'  => esc_html__( 'Outline word', 'rey-core' ),
						'hf'  => esc_html__( 'Highlight full', 'rey-core' ),
						'hp'  => esc_html__( 'Highlight partial', 'rey-core' ),
						'cv'  => esc_html__( 'Curvy underline', 'rey-core' ),
					],
					'prefix_class' => 'el-mark--',
				]
			);

			$element->add_control(
				'rey_special_text_styles__notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __('Wrap the word into a <strong>&lt;mark&gt;...&lt;/mark&gt;</strong> tag.', 'rey-core'),
					'content_classes' => 'elementor-descriptor',
					'condition' => [
						'rey_special_text_styles!' => '',
					],
				]
			);

			$element->add_control(
				'rey_special_text_styles__stroke_size',
				[
					'label' => __( 'Stroke size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 1,
					'selectors' => [
						'{{WRAPPER}} mark' => '--mark-stroke-size: {{VALUE}}px',
					],
					'condition' => [
						'rey_special_text_styles' => 'out',
					],
				]
			);

			$element->add_control(
				'rey_special_text_styles__height',
				[
					'label' => __( 'Height', 'rey-core' ) . ' (%)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 33,
					'selectors' => [
						'{{WRAPPER}} mark:before' => 'height: {{VALUE}}%',
					],
					'condition' => [
						'rey_special_text_styles' => 'hp',
					],
				]
			);

			$element->add_control(
				'rey_special_text_styles__color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} mark' => '--mark-color: {{VALUE}}',
					],
					'condition' => [
						'rey_special_text_styles!' => '',
					],
				]
			);

			$element->add_control(
				'rey_special_text_styles__grcolor',
				[
					'label' => esc_html__( '2nd Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} mark' => '--mark-gradient-color: {{VALUE}}',
					],
					'condition' => [
						'rey_special_text_styles' => 'grd',
					],
				]
			);

			$element->add_control(
				'rey_special_text_styles__grangle',
				[
				   'label' => esc_html__( 'Gradient Angle', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'deg' ],
					'default' => [
						'unit' => 'deg',
						'size' => 180,
					],
					'range' => [
						'deg' => [
							'step' => 10,
						],
					],
					'selectors' => [
						'{{WRAPPER}} mark' => '--mark-gradient-angle: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'rey_special_text_styles' => 'grd',
					],
				]
			);

			$element->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'rey_special_text_styles__bg',
					'types' => [ 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} mark, {{WRAPPER}} mark:before',
					'condition' => [
						'rey_special_text_styles!' => ['', 'grd'],
					],
				]
			);

			$element->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'rey_special_text_styles__typo',
					'selector' => '{{WRAPPER}} mark',
					'condition' => [
						'rey_special_text_styles!' => '',
					],
				]
			);

			$element->add_responsive_control(
				'rey_special_text_styles__padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} mark, {{WRAPPER}} mark:before' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'rey_special_text_styles!' => '',
					],
				]
			);

			$element->add_control(
				'rey_special_text_styles__radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} mark, {{WRAPPER}} mark:before' => 'border-radius: {{VALUE}}px',
					],
					'condition' => [
						'rey_special_text_styles!' => ['', 'grd'],
					],
				]
			);

			$element->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'rey_special_text_styles__shadow',
					'selector' => '{{WRAPPER}} mark, {{WRAPPER}} mark:before',
					'condition' => [
						'rey_special_text_styles!' => ['', 'grd'],
					],
				]
			);

			$element->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				[
					'name' => 'rey_special_text_styles__tshadow',
					'selector' => '{{WRAPPER}} mark',
					'condition' => [
						'rey_special_text_styles!' => '',
					],
				]
			);

			$element->add_control(
				'rey_special_text_styles__blend_mode',
				[
					'label' => __( 'Blend Mode', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' => __( 'Normal', 'rey-core' ),
						'multiply' => 'Multiply',
						'screen' => 'Screen',
						'overlay' => 'Overlay',
						'darken' => 'Darken',
						'lighten' => 'Lighten',
						'color-dodge' => 'Color Dodge',
						'saturation' => 'Saturation',
						'color' => 'Color',
						'difference' => 'Difference',
						'exclusion' => 'Exclusion',
						'hue' => 'Hue',
						'luminosity' => 'Luminosity',
					],
					'selectors' => [
						'{{WRAPPER}} mark' => 'mix-blend-mode: {{VALUE}}',
					],
					'condition' => [
						'rey_special_text_styles!' => '',
					],
				]
			);

			$element->end_controls_section();
		}

		function before_render( $element )
		{

			if( ! in_array($element->get_unique_name(), ['heading', 'reycore-acf-heading'], true) ){
				return;
			}

			$settings = $element->get_data('settings');

			if( isset($settings['rey_parent_hover']) && $settings['rey_parent_hover'] !== '' ){
				reyCoreAssets()->add_styles('reycore-elementor-heading-animation');
				reyCoreAssets()->add_scripts('reycore-elementor-elem-heading');
			}

			if( isset($settings['rey_special_text_styles']) && $settings['rey_special_text_styles'] !== '' ){
				reyCoreAssets()->add_styles('reycore-elementor-heading-highlight');
			}

			if( (isset($settings['rey_text_stroke']) && $settings['rey_text_stroke'] !== '') ||
			(isset($settings['rey_vertical_text']) && $settings['rey_vertical_text'] !== '') ){
				reyCoreAssets()->add_styles('reycore-elementor-heading-special');
			}

		}

	}
endif;
