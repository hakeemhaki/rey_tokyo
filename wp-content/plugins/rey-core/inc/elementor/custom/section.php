<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Section') ):
	/**
	 * Section Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Section {

		function __construct(){
			add_action( 'elementor/element/section/section_background/before_section_end', [$this, 'bg_settings'], 10);
			add_action( 'elementor/element/section/section_background/after_section_end', [$this, 'slideshow_settings'], 10);
			add_action( 'elementor/element/section/section_background_overlay/before_section_end', [$this,'slideshow_bg_overlay_settings'], 10);
			add_action( 'elementor/element/section/section_layout/before_section_end', [$this, 'layout_settings'], 10);
			add_action( 'elementor/element/section/section_layout/after_section_end', [$this, 'modal_settings'], 10);
			add_action( 'elementor/element/section/section_layout/after_section_end', [$this, 'tabs_settings'], 10);
			add_action( 'elementor/element/section/section_effects/before_section_end', [$this, 'effects_settings'], 10);
			add_action( 'elementor/element/section/section_advanced/before_section_end', [$this, 'section_advanced'], 10);
			add_action( 'elementor/element/section/_section_responsive/after_section_end', [$this, 'custom_css_settings'], 10);
			add_action( 'elementor/element/section/section_custom_css_pro/after_section_end', [$this, 'hide_on_demand'], 10);
			add_action( 'elementor/frontend/section/before_render', [$this, 'before_render'], 10);
			add_action( 'elementor/frontend/section/after_render', [$this, 'after_render'], 10);
			add_action('elementor/element/after_add_attributes', [$this, 'after_add_attributes'], 10);
			add_filter( 'elementor/section/print_template', [$this, 'print_template'], 10 );
			add_filter( 'elementor/frontend/section/should_render', [$this, 'should_render_section'], 10, 2 );
		}

		/**
		 * Add slideshow background option
		 *
		 * @since 1.0.0
		 */
		function bg_settings( $element )
		{
			$control_manager = \Elementor\Plugin::instance()->controls_manager;

			// extract background args
			// group control is not available, so only get main bg control
			$bg = $control_manager->get_control_from_stack( $element->get_unique_name(), 'background_background' );
			// add new condition, for REY slideshow background
			$bg['options']['rey_slideshow'] = [
				'title' => _x( 'Background Slideshow (Rey Theme)', 'Background Control', 'rey-core' ),
				'icon' => 'fa fa-arrows-h',
			];
			$bg['prefix_class'] = 'rey-section-bg--';
			$element->update_control( 'background_background', $bg );

			// Add Dynamic switcher
			$element->add_control(
				'rey_dynamic_bg',
				[
					'label' => esc_html__( 'Use Featured Image', 'rey-core' )  . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'background_background' => 'classic',
					],
				]
			);

			// adds desktop-only gradient
			// Add Dynamic switcher
			$element->add_control(
				'rey_desktop_gradient',
				[
					'label' => esc_html__( 'Desktop-Only Gradient', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'background_background' => 'gradient',
					],
					'prefix_class' => 'rey-gradientDesktop-',
				]
			);

			$element->add_control(
				'rey_bg_image_lazy',
				[
					'label' => esc_html__( 'Use Image Tag', 'rey-core' ) .  reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'background_background' => 'classic',
					],
					'selectors' => [
						'{{WRAPPER}}:not(.elementor-element-edit-mode)' => 'background-image:none !important;',
					],
				]
			);

			$element->add_control(
				'rey_bg_disable_mobile',
				[
					'label' => esc_html__( 'Disable image on mobiles', 'rey-core' ) .  reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'background_background' => 'classic',
					],
					'selectors' => [
						'(mobile){{WRAPPER}}' => 'background-image:none !important;',
						'(mobile){{WRAPPER}} .rey-section-wrap-bg-image' => 'display:none !important;',
					],
				]
			);

			foreach (['', '_tablet', '_mobile'] as $key => $value) {

				// Position
				$bg_position = [];
				$bg_position[$key] = $control_manager->get_control_from_stack( $element->get_unique_name(), 'background_position' . $value );
				if( ! is_wp_error($bg_position[$key]) && is_array($bg_position[$key]) ){
					$bg_position[$key]['selectors']['{{WRAPPER}} .rey-section-wrap-bg-image'] = 'object-position:{{VALUE}}';
					$element->update_control( 'background_position' . $value, $bg_position[$key] );
				}

				// Size
				$bg_size = [];
				$bg_size[$key] = $control_manager->get_control_from_stack( $element->get_unique_name(), 'background_size' . $value );
				if( ! is_wp_error($bg_size[$key]) && is_array($bg_size[$key]) ){
					$bg_size[$key]['selectors']['{{WRAPPER}} .rey-section-wrap-bg-image'] = 'object-fit:{{VALUE}}';
					$element->update_control( 'background_size' . $value, $bg_size[$key] );
				}
			}
		}

		/**
		 * Add custom slideshow settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function slideshow_settings( $element )
		{

			/**
			 * Used for transitioning to Elementor 3.0.x
			 * https://github.com/elementor/elementor/issues/12242
			 * TODO: To be removed as soon as Elementor fixes this.
			 */
			if( apply_filters('reycore/elementor/section/slideshow_options/disable', false) ){
				return;
			}

			$element->start_controls_section(
				'rey_section_slideshow',
				[
					'label' => __( 'Background Slideshow', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'hide_in_inner' => true,
					'condition' => [
						'background_background' => 'rey_slideshow'
					]
				]
			);

			$element->add_control(
				'rey_slideshow_autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$element->add_control(
				'rey_slideshow_autoplay_time',
				[
					'label' => __( 'Autoplay Timeout', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
					'min' => 100,
					'max' => 30000,
					'step' => 10,
					'placeholder' => 5000,
					'condition' => [
						'rey_slideshow_autoplay' => 'yes',
					],
				]
			);

			$element->add_control(
				'rey_slideshow_speed',
				[
					'label' => __( 'Transition speed', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
					'min' => 100,
					'max' => 5000,
					'step' => 10,
					'placeholder' => 500,
				]
			);

			$element->add_control(
				'rey_slideshow_effect',
				[
					'label' => __( 'Slideshow Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'slide',
					'options' => [
						'slide'  => __( 'Slide', 'rey-core' ),
						'fade'  => __( 'Fade In/Out', 'rey-core' ),
						'scaler'  => __( 'Scale & Fade', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'rey_slideshow_nav',
				[
					'label' => __( 'Connect the dots', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'label_block' => true,
					'placeholder' => __( 'eg: #toggle-boxes-gd4fg6', 'rey-core' ),
					'description' => __( 'Use the Toggle Boxes widget and paste its unique id here. If empty, the first Toggler widget found in this section will be used, if any.', 'rey-core' ),
				]
			);

			$slides = new \Elementor\Repeater();

			$slides->add_control(
				'img',
				[
				   'label' => __( 'Choose Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
					'dynamic' => [
						'active' => true,
					],
					// 'selectors' => [
					// 	'{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-image: url("{{URL}}");',
					// ],
				]
			);

			$slides->add_responsive_control(
				'img_position',
				[
				   'label' => __( 'Position (X & Y)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '50% 50%',
					'selectors' => [
						// '{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-position: {{VALUE}};',
						'{{WRAPPER}} {{CURRENT_ITEM}} .rey-section-slideshowItem-img' => 'object-position: {{VALUE}};',
					],
				]
			);

			$element->add_control(
				'rey_slideshow_slides',
				[
					'label' => __( 'Slides', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $slides->get_controls(),
				]
			);

			$element->add_control(
				'rey_slideshow_mobile__title',
				[
				   'label' => esc_html__( 'MOBILE', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$element->add_control(
				'rey_slideshow_mobile',
				[
					'label' => __( 'Show on mobiles?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$element->add_control(
				'rey_slideshow_mobile__color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'(mobile){{WRAPPER}} .rey-section-slideshowItem.rey-section-slideshowItem--0' => 'background-color: {{VALUE}}; background-image: none;',
					],
					'condition' => [
						'rey_slideshow_mobile' => '',
					],
				]
			);

			$element->add_control(
				'rey_slideshow_mobile__image',
				[
				   'label' => esc_html__( 'Background Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'selectors' => [
						'(mobile){{WRAPPER}} .rey-section-slideshowItem.rey-section-slideshowItem--0' => 'background-image: url("{{URL}}");',
					],
					'condition' => [
						'rey_slideshow_mobile' => '',
					],
				]
			);

			$element->add_control(
				'rey_slideshow_misc__title',
				[
				   'label' => esc_html__( 'MISC.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$element->add_control(
				'rey_slideshow_container',
				[
					'label' => __( 'Fit Container', 'rey-core' ),
					'description' => __( 'Useful when using this feature in a Page cover global section.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$element->end_controls_section();
		}

		/**
		 * Update the conditions of the background overlay section,
		 * to apply for rey_slideshow as well.
		 */
		function slideshow_bg_overlay_settings( $stack )
		{
		 	// Disabled in 2.7.0+ because Elementor has been updated without any background type condition
			if( version_compare(ELEMENTOR_VERSION, '2.7.0', '>=' ) ) {
				return;
			}

			// get section args
			$section_bg_overlay = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), 'section_background_overlay' );
			// pass custom condition
			$section_bg_overlay['condition']['background_background'][] = 'rey_slideshow';
			// update section
			$stack->update_control( 'section_background_overlay', $section_bg_overlay, ['recursive'=> true] );
		}


		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function layout_settings( $element )
		{
			$element->remove_control( 'stretch_section' );

			$element->start_injection( [
				'of' => '_title',
			] );

			$element->add_control(
				'rey_stretch_section',
				[
					'label' => __( 'Stretch Section', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'section-stretched',
					'prefix_class' => 'rey-',
					'hide_in_inner' => true,
					'description' => __( 'Stretch the section to the full width of the page using plain CSS.', 'rey-core' ) . sprintf( ' <a href="%1$s" target="_blank">%2$s</a>', 'https://go.elementor.com/stretch-section/', __( 'Learn more.', 'rey-core' ) ),
				]
			);

			$element->end_injection();


			$element->start_injection( [
				'of' => 'html_tag',
			] );

			$element->add_control(
				'rey_inner_section_width',
				[
					'label' => __( 'Inner-Section Width', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'separator' => 'before',
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 2500,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
						'vw' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'size_units' => [ 'px', '%', 'vw' ],
					'selectors' => [
						'{{WRAPPER}}' => 'max-width: {{SIZE}}{{UNIT}};',
					],
					'hide_in_top' => true
				]
			);

			$element->add_control(
				'rey_flex_wrap',
				[
					'label' => __( 'Multi-rows (Flex Wrap)', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'description' => __( 'Enabling this option will allow columns on separate rows. Note that manual resizing handles are disabled.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'rey-core' ),
					'label_off' => __( 'No', 'rey-core' ),
					'return_value' => 'rey-flexWrap',
					'default' => '',
					'prefix_class' => '',
					'separator' => 'before'
				]
			);


			$element->add_control(
				'rey_mobile_offset',
				[
					'label' => __( 'Mobile Offset (Overflow X)', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'description' => __( 'You can force this section\'s container to stretch on mobiles and display a horizontal scrollbar.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'rey-mobiOffset',
					'default' => '',
					'prefix_class' => '',
					'separator' => 'before'
				]
			);

				$element->add_control(
					'rey_mobile_offset_width',
					[
						'label' => esc_html__( 'Stretch width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'max' => 3000,
						'step' => 1,
						'selectors' => [
							'{{WRAPPER}}' => '--mobi-offset: {{SIZE}}px;',
						],
						'condition' => [
							'rey_mobile_offset!' => '',
						],
					]
				);

				$element->add_control(
					'rey_mobile_offset_gutter',
					[
						'label' => esc_html__( 'Include Side Gap', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'rey-mobiOffset--gap',
						'default' => '',
						'prefix_class' => '',
						'condition' => [
							'rey_mobile_offset!' => '',
						],
					]
				);



			$element->end_injection();

			if( reyCoreElementor()->is_optimized_dom() ){
				// Gap Class
				$gap = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'gap' );
				$gap['prefix_class'] = 'elementor-section-gap-';
				$element->update_control( 'gap', $gap );
			}
		}


		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function modal_settings( $element )
		{

			$element->start_controls_section(
				'section_modal',
				[
					'label' => __( 'Modal Settings', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_LAYOUT
				]
			);

			$element->add_control(
				'rey_modal',
				[
					'label' => __( 'Enable Modal', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'modal-section',
					'default' => '',
					'prefix_class' => 'rey-',
					'hide_in_inner' => true,
				]
			);

			$element->add_responsive_control(
				'rey_modal_width',
				[
				'label' => __( 'Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw' ],
					'range' => [
						'px' => [
							'min' => 320,
							'max' => 2560,
							'step' => 1,
						],
						'vw' => [
							'min' => 10,
							'max' => 100,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'vw',
						'size' => 80,
					],
					// 'selectors' => [
					// 	'{{WRAPPER}}' => 'max-width: {{SIZE}}{{UNIT}};',
					// ],
					'condition' => [
						'rey_modal!' => '',
					],
				]
			);

			$element->add_responsive_control(
				'rey_modal_height',
				[
				'label' => __( 'Max-Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw' ],
					'range' => [
						'px' => [
							'min' => 150,
							'max' => 1000,
							'step' => 1,
						],
						'vw' => [
							'min' => 10,
							'max' => 100,
							'step' => 1,
						],
					],
					'default' => [],
					'selectors' => [
						'{{WRAPPER}}.elementor-element-edit-mode' => 'max-height: {{SIZE}}{{UNIT}}; overflow-y: auto !important; overflow-x: hidden !important;',
						'{{WRAPPER}}' => 'max-height: {{SIZE}}{{UNIT}}; overflow: auto;',
					],
					'condition' => [
						'rey_modal!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_close_pos',
				[
					'label' => __( 'Close Position', 'rey-core' ),
					'description' => __( 'Button can be previewed in normal frontend (not edit mode).', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Inside', 'rey-core' ),
						'--outside'  => __( 'Outside', 'rey-core' ),
					],
					'render_type' => 'none',
					'condition' => [
						'rey_modal!' => '',
					],
					'separator' => 'before',
				]
			);

			$element->add_control(
				'rey_modal_close_color',
				[
					'label' => esc_html__( 'Close Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'condition' => [
						'rey_modal!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_splash',
				[
					'label' => __( 'Auto pop-up?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Disabled', 'rey-core' ),
						'scroll'  => __( 'On Page Scroll', 'rey-core' ),
						'time'  => __( 'On Page Load', 'rey-core' ),
						'exit'  => __( 'On Exit Intent', 'rey-core' ),
					],
					'render_type' => 'none',
					'separator' => 'before',
					'condition' => [
						'rey_modal!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_splash_scroll_distance',
				[
					'label' => esc_html__( 'Scroll Distance (%)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'condition' => [
						'rey_modal!' => '',
						'rey_modal_splash' => 'scroll',
					],
				]
			);

			$element->add_control(
				'rey_modal_splash_timeframe',
				[
					'label' => esc_html__( 'Timeframe (Seconds)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 4,
					'min' => 0,
					'max' => 500,
					'step' => 1,
					'condition' => [
						'rey_modal!' => '',
						'rey_modal_splash' => 'time',
					],
				]
			);

			// $element->add_control(
			// 	'rey_modal_splash_nag',
			// 	[
			// 		'label' => esc_html__( 'Disable Nagging?', 'rey-core' ),
			// 		'description' => esc_html__( 'When the visitor closes the splash popup, he won\'t be nagged with this splash for one day.', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'default' => 'yes',
			// 		'condition' => [
			// 			'rey_modal!' => '',
			// 			'rey_modal_splash!' => '',
			// 		],
			// 	]
			// );

			$element->add_control(
				'rey_modal_splash_nag',
				[
					'label' => esc_html__( 'Prevent re-opening?', 'rey-core' ),
					'description' => esc_html__( 'When the visitor closes the splash popup, he won\'t be nagged again.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						''  => esc_html__( 'No', 'rey-core' ),
						'yes'  => esc_html__( 'Yes - for 1 day', 'rey-core' ),
						'week'  => esc_html__( 'Yes - for 1 week', 'rey-core' ),
						'month'  => esc_html__( 'Yes - for 1 month', 'rey-core' ),
						'forever'  => esc_html__( 'Yes - Forever', 'rey-core' ),
					],
					'condition' => [
						'rey_modal!' => '',
						'rey_modal_splash!' => '',
					],
				]
			);

			$element->add_control(
				'rey_modal_id',
				[
					'label' => __( 'Modal ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('#modal-'),
					'placeholder' => __( 'eg: #some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the link text-fields, where specified.', 'rey-core' ),
					'separator' => 'before',
					'condition' => [
						'rey_modal!' => '',
					],
					'render_type' => 'none',
				]
			);

			$element->end_controls_section();
		}

		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function tabs_settings( $element )
		{

			$element->start_controls_section(
				'section_tabs',
				[
					'label' => __( 'Tabs Settings', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_LAYOUT
				]
			);

			$element->add_control(
				'rey_tabs',
				[
					'label' => __( 'Enable Tabs', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'tabs-section',
					'default' => '',
					'prefix_class' => 'rey-',
					// 'hide_in_inner' => true,
				]
			);


			$element->add_control(
				'rey_tabs_id',
				[
					'label' => __( 'Tabs ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('tabs-'),
					'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the "Toggle Boxes" Widget where specified.', 'rey-core' ),
					'condition' => [
						'rey_tabs!' => '',
					],
					'render_type' => 'none',
				]
			);

			$element->add_control(
				'rey_tabs_effect',
				[
					'label' => esc_html__( 'Tabs Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default'  => esc_html__( 'Fade', 'rey-core' ),
						'slide'  => esc_html__( 'Fade & Slide', 'rey-core' ),
					],
					'condition' => [
						'rey_tabs!' => '',
					],
				]
			);

			$element->add_control(
				'rey_tabs_transition_speed',
				[
					'label' => esc_html__( 'Transition Speed', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'condition' => [
						'rey_tabs!' => '',
					],
					'selectors' => [
						// v2
						'{{WRAPPER}}.rey-tabs-section > .elementor-container > .elementor-row > .elementor-column' => 'transition-duration: {{VALUE}}ms',
						// v3
						'{{WRAPPER}}.rey-tabs-section > .elementor-container > .elementor-column' => 'transition-duration: {{VALUE}}ms',
					],
				]
			);

			$element->end_controls_section();
		}


		/**
		 * Add custom settings into Elementor's Section
		 *
		 * @since 1.0.0
		 */
		function effects_settings( $element )
		{

			if( reyCoreElementor()->animations_enabled() ):

				$element->add_control(
					'rey_animation_type',
					[
						'label' => __( 'Entrace Effect', 'rey-core' ) . reyCoreElementor::getReyBadge(),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							''  => __( '- Select -', 'rey-core' ),
							'reveal'  => __( 'Reveal', 'rey-core' ),
							'fade-in'  => __( 'Fade In', 'rey-core' ),
							'fade-slide'  => __( 'Fade In From Bottom', 'rey-core' ),
						],
						'render_type' => 'none',
						// 'condition' => [
						// 	'sticky!' => ['top', 'bottom'],
						// ],
					]
				);

				$element->add_control(
					'rey_entrance_title',
					[
					'label' => __( 'ENTRANCE SETTINGS', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							'rey_animation_type!' => [''],
						],
					]
				);

				$element->add_control(
					'rey_animation_duration',
					[
						'label' => __( 'Animation Duration', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							'slow' => __( 'Slow', 'rey-core' ),
							'' => __( 'Normal', 'rey-core' ),
							'fast' => __( 'Fast', 'rey-core' ),
						],
						'condition' => [
							'rey_animation_type!' => [''],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_animation_delay',
					[
						'label' => __( 'Animation Delay', 'rey-core' ) . ' (ms)',
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'step' => 100,
						'condition' => [
							'rey_animation_type!' => [''],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_reveal_title',
					[
					'label' => __( 'REVEAL SETTINGS', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							'rey_animation_type' => ['reveal'],
						],
					]
				);

				$element->add_control(
					'rey_animation_type_reveal_direction',
					[
						'label' => __( 'Reveal Direction', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'left',
						'options' => [
							'left'  => __( 'Left', 'rey-core' ),
							'top'  => __( 'Top', 'rey-core' ),
						],
						'condition' => [
							'rey_animation_type' => ['reveal'],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_animation_type__reveal_bg_color',
					[
						'label' => __( 'Reveal Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						// 'selectors' => [
						// 	'{{WRAPPER}}.rey-anim--reveal-bg .rey-anim--reveal-bgHolder' => 'background-color: {{VALUE}}',
						// ],
						'condition' => [
							'rey_animation_type' => ['reveal'],
						],
						'render_type' => 'none',
					]
				);
			endif;

			/**
			 * Scroll effects
			 */

			$element->add_control(
				'rey_scroll_effects',
				[
					'label' => __( 'Scroll Effects', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'None', 'rey-core' ),
						'clip-in'  => __( 'Clip In', 'rey-core' ),
						'clip-out'  => __( 'Clip Out', 'rey-core' ),
						'sticky'  => __( 'Sticky', 'rey-core' ),
						'colorize'  => __( 'Colorize Site', 'rey-core' ),
					],
					// 'hide_in_inner' => true,
					'prefix_class' => 'rey-sectionScroll rey-sectionScroll--',
					'separator' => 'before',
				]
			);

			$element->add_control(
				'rey_clip_mobile',
				[
					'label' => esc_html__( 'Add effect on mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'rey_scroll_effects' => ['clip-in', 'clip-out'],
					],
					'prefix_class' => '--clip-mobile-',
				]
			);

			$element->add_responsive_control(
				'rey_clip_offset',
				[
					'label' => __( 'Scroll Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 5,
					'max' => 300,
					'step' => 1,
					'condition' => [
						'rey_scroll_effects' => ['clip-in', 'clip-out'],
					],
					'selectors' => [
						'{{WRAPPER}}.rey-sectionScroll' => '--clip-offset: {{VALUE}}px; ',
					]
				]
			);

			$element->add_control(
				'rey_clip_threshold',
				[
					'label' => __( 'Scroll Threshold', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 0.5,
					'min' => 0,
					'max' => 1,
					'step' => 0.1,
					'condition' => [
						'rey_scroll_effects' => ['clip-in', 'clip-out', 'colorize'],
					],
					'selectors' => [
						'{{WRAPPER}}.rey-sectionScroll' => '--clip-threshold: {{VALUE}} ',
					]
				]
			);

			$element->add_control(
				'rey_sticky_offset',
				[
					'label' => __( 'Sticky Offset', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 300,
					'step' => 1,
					'condition' => [
						'rey_scroll_effects' => 'sticky',
					],
					'selectors' => [
						'{{WRAPPER}}.rey-sectionScroll' => '--sticky-offset: {{VALUE}}px; ',
					]
				]
			);


			$element->add_control(
				'rey_sticky_breakpoints',
				[
					'label' => __( 'Sticky Breakpoints', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'multiple' => true,
					'label_block' => true,
					'default' => ['desktop'],
					'options' => [
						'desktop'  => __( 'Desktop', 'rey-core' ),
						'tablet'  => __( 'Tablet', 'rey-core' ),
						'mobile'  => __( 'Mobile', 'rey-core' ),
					],
					'condition' => [
						'rey_scroll_effects' => 'sticky',
					],
				]
			);

			$element->add_control(
				'rey_colorize_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'condition' => [
						'rey_scroll_effects' => 'colorize',
					],
				]
			);

			$element->add_control(
				'rey_colorize_text_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'condition' => [
						'rey_scroll_effects' => 'colorize',
					],
				]
			);

			$element->add_control(
				'rey_colorize_link_color',
				[
					'label' => esc_html__( 'Links Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'condition' => [
						'rey_scroll_effects' => 'colorize',
					],
				]
			);

			$element->add_control(
				'rey_colorize_link_hover_color',
				[
					'label' => esc_html__( 'Links Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'condition' => [
						'rey_scroll_effects' => 'colorize',
					],
				]
			);

			$element->add_control(
				'rey_colorize_force',
				[
					'label' => esc_html__( 'Force already colored elements', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'rey_scroll_effects' => 'colorize',
					],
				]
			);

			$element->add_control(
				'rey_scroll_effects_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __( 'Please preview in public mode.', 'rey-core' ),
					'content_classes' => 'rey-raw-html',
					'condition' => [
						'rey_scroll_effects!' => '',
					],
				]
			);
		}

		function custom_css_settings(  $element ){

			$element->start_controls_section(
				'section_rey_custom_CSS',
				[
					'label' => sprintf( '<span>%s</span><span class="rey-hasStylesNotice">%s</span>', __( 'Custom CSS', 'rey-core' ) , __( 'Has Styles!', 'rey-core' ) ) . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
					'hide_in_inner' => true,
				]
			);

			$css_desc = sprintf(__('<p class="rey-addMargin">Click to insert selector: <span class="rey-selectorCss js-insertToEditor" title="Click to insert">%s</span></p>', 'rey-core') , 'SECTION-ID {}' );

			$css_desc .= sprintf('<p class="rey-addMargin"><span></span><select class="js-insertSnippetToEditor">
				<option value="">%s</option>
				<option value="@media (max-width:767px) {}">< 767px (Mobile only)</option>
				<option value="@media (max-width:1024px) {}">< 1024px (Mobiles & Tablet)</option>
				<option value="@media (min-width:768px) and (max-width:1024px) {}">768px to 1024px (Tablet only)</option>
				<option value="@media (min-width:768px) {}">> 768px (Tablet & Desktop)</option>
				<option value="@media (min-width:1025px) {}">> 1025px (Desktop only)</option>
				<option value="@media (min-width:1025px) and (max-width:1440px) {}">1025px to 1440px (Desktop, until 1440px)</option>
				<option value="@media (min-width:1441px) {}">> 1441px (Desktop, from 1441px)</option>
			</select></p>', esc_html__('Insert media query snippet:', 'rey-core'));

			$css_desc .= __( '<p>For more advanced control over the section\'s CSS, or any other element, i suggest trying <a href="https://elementor.com/pro/" target="_blank">Elementor PRO</a>.</p>', 'rey-core' );

			$element->add_control(
				'rey_custom_css',
				[
					'type' => \Elementor\Controls_Manager::CODE,
					'label' => esc_html__('Custom CSS', 'rey-core'),
					'language' => 'css',
					'render_type' => 'ui',
					'show_label' => false,
					'separator' => 'none',
					'description' =>  $css_desc,
				]
			);

			$element->end_controls_section();

		}

		function hide_on_demand( $section ){

			$section->start_controls_section(
				'section_rey_hide_on_demand',
				[
					'label' => esc_html__('Hide on demand', 'rey-core') . reyCoreElementor::getReyBadge(),
					'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
					'hide_in_inner' => true,
				]
			);

			$section->add_control(
				'rey_hod__notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __('This feature should mostly be used for promotion banners or content that is dismissable.', 'rey-core'),
					'content_classes' => 'elementor-descriptor',
				]
			);

			$section->add_control(
				'rey_hod__enable',
				[
					'label' => esc_html__( 'Enable hiding on demand', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$section->add_control(
				'rey_hod__hide_type',
				[
					'label' => esc_html__( 'Hiding Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'icon',
					'options' => [
						'icon'  => esc_html__( 'Close Icon', 'rey-core' ),
						'custom'  => esc_html__( 'Custom link', 'rey-core' ),
					],
					'condition' => [
						'rey_hod__enable!' => '',
					],
				]
			);

			$section->add_control(
				'rey_hod__notice_custom',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __('Please use the URL <strong>#close-section</strong> inside.', 'rey-core'),
					'content_classes' => 'elementor-descriptor',
					'condition' => [
						'rey_hod__enable!' => '',
						'rey_hod__hide_type' => 'custom',
					],
				]
			);

			$section->add_control(
				'rey_hod__close_color',
				[
					'label' => esc_html__( 'Close Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'condition' => [
						'rey_hod__enable!' => '',
						'rey_hod__hide_type' => 'icon',
					],
					'selectors' => [
						'{{WRAPPER}} .rey-hod-close' => 'color: {{VALUE}}',
					]
				]
			);

			$section->add_control(
				'rey_hod__close_position',
				[
					'label' => esc_html__( 'Close Icon Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'right',
					'options' => [
						'left'  => esc_html__( 'Left', 'rey-core' ),
						'right'  => esc_html__( 'Right', 'rey-core' ),
					],
					'condition' => [
						'rey_hod__enable!' => '',
						'rey_hod__hide_type' => 'icon',
					],
				]
			);


			$section->add_control(
				'rey_hod__close_size',
				[
					'label' => esc_html__( 'Close Icon Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'condition' => [
						'rey_hod__enable!' => '',
						'rey_hod__hide_type' => 'icon',
					],
					'selectors' => [
						'{{WRAPPER}} .rey-hod-close' => 'font-size: {{VALUE}}px',
					]
				]
			);

			$section->add_control(
				'rey_hod__close_thickness',
				[
					'label' => esc_html__( 'Close Icon Thickness', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 12,
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'condition' => [
						'rey_hod__enable!' => '',
						'rey_hod__hide_type' => 'icon',
					],
					'selectors' => [
						'{{WRAPPER}} .rey-hod-close svg' => '--stroke-width: {{VALUE}}px',
					]
				]
			);

			$section->add_control(
				'rey_hod__close_distance',
				[
					'label' => esc_html__( 'Close Icon - Side distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'condition' => [
						'rey_hod__enable!' => '',
						'rey_hod__hide_type' => 'icon',
					],
					'selectors' => [
						'{{WRAPPER}} .rey-hod-close' => '--hod-distance: {{VALUE}}px',
					]
				]
			);

			$section->add_control(
				'rey_hod__store_state',
				[
					'label' => esc_html__( 'Hidden state duration', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'day',
					'options' => [
						'none'  => esc_html__( 'None (show on refresh)', 'rey-core' ),
						'day'  => esc_html__( '1 Day', 'rey-core' ),
						'week'  => esc_html__( '1 Week', 'rey-core' ),
						'month'  => esc_html__( '1 Month', 'rey-core' ),
					],
					'condition' => [
						'rey_hod__enable!' => '',
					],
				]
			);

			$section->end_controls_section();

		}


		function after_add_attributes($element){

			if( 'section' !== $element->get_unique_name() ){
				return;
			}

			$settings = $element->get_settings_for_display();

			if( reyCoreElementor()->animations_enabled() && $settings['rey_animation_type'] != '' ):

				/**
				 * Hack to delay loading (lazy load) the video background if section is animated
				 */

				// Check if background enabled
				if( $settings['background_background'] === 'video' && $settings['background_video_link'] ){

					// add a temporary custom attribute
					$element->add_render_attribute( '_wrapper', 'data-rey-video-link', esc_attr($settings['background_video_link']) );

					// unset video link to remove it from data-settings attribute
					$frontend_settings = $element->get_render_attributes('_wrapper', 'data-settings');
					if( $frontend_settings && isset($frontend_settings[0]) && $frontend_settings_dec = json_decode($frontend_settings[0], true) ){
						unset($frontend_settings_dec['background_video_link']);
						$element->add_render_attribute( '_wrapper', 'data-settings', wp_json_encode( $frontend_settings_dec ), true );
					}

					reyCoreAssets()->add_scripts('reycore-elementor-elem-section-video');

				}
			endif;

		}

		/**
		 * Tweak the CSS classes field.
		 */
		function section_advanced( $stack )
		{
			$controls_manager = \Elementor\Plugin::instance()->controls_manager;
			$unique_name = $stack->get_unique_name();

			// Margin
			$margin = $controls_manager->get_control_from_stack( $unique_name, 'margin' );
			$margin['condition'] = 'all';
			$margin['condition'] = [
				'rey_allow_horizontal_margin' => ''
			];
			$stack->update_control( 'margin', $margin );

			$stack->start_injection( [
				'at' => 'after',
				'of' => 'margin',
			] );

			$stack->add_control(
				'rey_allow_horizontal_margin',
				[
					'label' => __( 'Allow Horizontal Margins', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'all',
				]
			);

			$stack->add_responsive_control(
				'rey_margin_all',
				[
					'label' => __( 'Margin', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'rey_allow_horizontal_margin' => 'all'
					],
				]
			);

			$stack->end_injection();


			// CSS CLASS - stretch field
			$css_classes = $controls_manager->get_control_from_stack( $unique_name, 'css_classes' );
			$css_classes['label_block'] = true;
			$stack->update_control( 'css_classes', $css_classes );

			// PADDING
			foreach (['', '_tablet', '_mobile'] as $key => $value) {
				$item = [];
				$item[$key] = $controls_manager->get_control_from_stack( $unique_name, 'padding' . $value );
				if( ! is_wp_error($item[$key]) && is_array($item[$key]) ){
					$item[$key]['selectors'] = [
						'{{WRAPPER}} > .elementor-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					];
					$stack->update_control( 'padding' . $value, $item[$key] );
				}
			}

			$stack->add_control(
				'rey_hide_on',
				[
					'label' => esc_html__( 'Hide section for', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Don\'t hide', 'rey-core' ),
						'logged-in'  => esc_html__( 'Logged IN users', 'rey-core' ),
						'logged-out'  => esc_html__( 'Logged OUT users', 'rey-core' ),
					],
				]
			);
		}

		function should_render_section( $should_render, $element ){

			if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) {
				return $should_render;
			}

			if( $hide_on = $element->get_settings('rey_hide_on') ){

				$is_logged_in = is_user_logged_in();

				if( $hide_on === 'logged-in' && $is_logged_in ){
					return false;
				}
				else if( $hide_on === 'logged-out' && ! $is_logged_in ){
					return false;
				}

			}

			return $should_render;
		}

		/**
		 * Render some attributes before rendering
		 *
		 * @since 1.0.0
		 **/
		function before_render( $element )
		{

			if( ! apply_filters( "elementor/frontend/section/should_render", true, $element ) ){
				return;
			}

			$settings = $element->get_settings();

			$classes = [];

			if( reyCoreElementor()->animations_enabled() && $settings['rey_animation_type'] != '' ):

				$classes[] = 'rey-animate-el';
				$classes[] = 'rey-anim--' . esc_attr( $settings['rey_animation_type'] );
				$classes[] = 'rey-anim--viewport';

				$config = [
					'id'               => $element->get_id(),
					'element_type'     => 'section',
					'animation_type'   => esc_attr( $settings['rey_animation_type'] ),
					'reveal_direction' => esc_attr( $settings['rey_animation_type_reveal_direction']),
					'reveal_bg'        => esc_attr( $settings['rey_animation_type__reveal_bg_color']),
				];

				if( $settings['rey_animation_delay'] ) {
					$config['delay']= esc_attr( $settings['rey_animation_delay'] );
				}

				if( $settings['rey_animation_duration'] ) {
					$config['duration']= esc_attr( $settings['rey_animation_duration'] );
				}

				$element->add_render_attribute( '_wrapper', 'data-rey-anim-config', wp_json_encode($config) );

				reyCoreAssets()->add_scripts('reycore-elementor-entrance-animations');
				reyCoreAssets()->add_styles('reycore-elementor-entrance-animations');

			endif;

			// Modal
			if( $settings['rey_modal'] == 'modal-section' ){

				$modal_attributes[] = 'data-rey-modal-id="'. $settings['rey_modal_id'] .'"';

				if( isset($settings['rey_modal_splash']) && $splash = $settings['rey_modal_splash'] ){
					$modal_attributes[] = sprintf("data-rey-modal-splash='%s'", wp_json_encode([
						'type' => esc_attr($splash),
						'time' => esc_attr($settings['rey_modal_splash_timeframe']),
						'distance' => esc_attr($settings['rey_modal_splash_scroll_distance']),
						'nag' => $settings['rey_modal_splash_nag'] === 'yes' ? 'day' : $settings['rey_modal_splash_nag'],
					]));
				}

				if( $modal_close_btn_color = $settings['rey_modal_close_color'] ){
					$modal_attributes[] = 'data-rey-modal-close-color="'. esc_attr($modal_close_btn_color) .'"';
				}

				$modal_size = '';

				foreach( ['', 'tablet', 'mobile'] as $breakpoint ){

					$modal_width_key = 'rey_modal_width' . ( !empty($breakpoint) ? ('_' . $breakpoint) : '' );

					if( isset($settings[$modal_width_key]) && isset($settings[$modal_width_key]['size']) && !empty($settings[$modal_width_key]['size']) ) {
						$modal_size .= '--modal-size'. ( !empty($breakpoint) ? ('-' . $breakpoint) : '' ) .':';
						$modal_size .= esc_attr( $settings[$modal_width_key]['size'] );
						$modal_size .= isset($settings[$modal_width_key]['unit']) && !empty($settings[$modal_width_key]['unit']) ? esc_attr( $settings[$modal_width_key]['unit'] ) : 'px';
						$modal_size .= ';';
					}
				}

				// Wrap section & add overlay
				printf( '<div class="rey-modalSection" %1$s><div class="rey-modalSection-overlay"></div><div class="rey-modalSection-inner" %4$s><button class="rey-modalSection-close %3$s" aria-label="%5$s">%2$s</button>',
					implode(' ', $modal_attributes),
					reycore__get_svg_icon(['id' => 'rey-icon-close']),
					esc_attr( $settings['rey_modal_close_pos'] ),
					sprintf('style="%s"', $modal_size),
					esc_html__('Close', 'rey-core')
				);

				add_filter('reycore/modals/always_load', '__return_true');

				reyCoreAssets()->add_styles('reycore-elementor-modal-section');
				reyCoreAssets()->add_scripts('reycore-elementor-modal');

			}

			// Tabs
			if( $settings['rey_tabs'] == 'tabs-section' ){
				$element->add_render_attribute( '_wrapper', 'data-tabs-id', esc_attr($settings['rey_tabs_id']) );
				$classes[] = '--tabs-effect-' . esc_attr($settings['rey_tabs_effect']);
			}

			// Dynamic image background
			if( 'classic' === $settings['background_background'] && isset($settings['rey_dynamic_bg']) && $settings['rey_dynamic_bg'] === 'yes' ):
				$thumbnail_data = reycore__get_post_term_thumbnail();

				if( isset($thumbnail_data['url']) && ($thumbnail_url = $thumbnail_data['url']) ){
					$element->add_render_attribute( '_wrapper', 'style', sprintf('background-image:url(%s);', $thumbnail_url) );
				}
			endif;

			if( '' !== $settings['rey_scroll_effects'] ){
				reyCoreAssets()->add_styles('reycore-elementor-scroll-effects');
			}

			// Sticky
			if( 'sticky' === $settings['rey_scroll_effects'] ){

				$sticky_config = [];

				if( $sticky_offset = $settings['rey_sticky_offset'] ){
					$sticky_config['offset'] = esc_attr($sticky_offset);
				}

				if( $sticky_breakpoints = $settings['rey_sticky_breakpoints'] ){
					$sticky_config['breakpoints'] = array_map('esc_attr', $sticky_breakpoints);
				}

				if( !empty($sticky_config) ){
					$element->add_render_attribute( '_wrapper', 'data-sticky-config', wp_json_encode($sticky_config) );
				}

				reyCoreAssets()->add_scripts(['reycore-sticky', 'reycore-elementor-elem-section-sticky', 'imagesloaded']);

			}

			// Colorize
			else if( 'colorize' === $settings['rey_scroll_effects'] ){

				$colorize_config = [];

				if( $colorize__bg = $settings['rey_colorize_bg_color'] ){
					$colorize_config['bg'] = esc_attr($colorize__bg);
				}

				if( $colorize__text = $settings['rey_colorize_text_color'] ){
					$colorize_config['text'] = esc_attr($colorize__text);
				}

				if( $colorize__link = $settings['rey_colorize_link_color'] ){
					$colorize_config['link'] = esc_attr($colorize__link);
				}

				if( $colorize__link_hover = $settings['rey_colorize_link_hover_color'] ){
					$colorize_config['link_hover'] = esc_attr($colorize__link_hover);
				}

				if( 'yes' === $settings['rey_colorize_force'] ){
					$colorize_config['force'] = true;
				}

				if( !empty($colorize_config) ){
					$element->add_render_attribute( '_wrapper', 'data-colorize-config', wp_json_encode($colorize_config) );
				}
			}

			// Hide on demand
			if( $settings['rey_hod__enable'] !== '' ){

				$hod_config['hide_type'] = $settings['rey_hod__hide_type'];
				$hod_config['close_position'] = $settings['rey_hod__close_position'];
				$hod_config['store_state'] = $settings['rey_hod__store_state'];

				$element->add_render_attribute( '_wrapper', 'data-rey-hod-settings', wp_json_encode($hod_config) );

				reyCoreAssets()->add_styles('reycore-elementor-hide-on-demand');
				reyCoreAssets()->add_scripts('reycore-elementor-elem-section-hod');

			}

			if( 'classic' === $settings['background_background'] &&
				($bg_image = $settings['background_image']) && $settings['rey_bg_image_lazy'] !== '' ){
				// Catch output
				ob_start();
			}
			// Rey slidesjow
			elseif( 'rey_slideshow' === $settings['background_background'] && isset($settings['rey_slideshow_slides']) && !empty($settings['rey_slideshow_slides']) ){

				reyCoreAssets()->add_scripts(['rey-splide', 'reycore-elementor-elem-section-slideshow']);
				reyCoreAssets()->add_styles(['rey-splide', 'reycore-elementor-section-slideshow']);

				$slideshow_config = [
					'type' => 'slide'
				];

				if( $settings['rey_slideshow_autoplay'] ){
					$slideshow_config['autoplay'] = $settings['rey_slideshow_autoplay'] !== '';
					$slideshow_config['interval'] = absint( $settings['rey_slideshow_autoplay_time'] );
				}

				$slideshow_config['speed'] = absint( $settings['rey_slideshow_speed'] );

				$classes['slideshow-mobile'] = '--no-mobile-slideshow';

				if( $settings['rey_slideshow_mobile'] === 'yes' ){
					$slideshow_config['mobile'] = true;
					$classes['slideshow-mobile'] = '';
				}

				if( $settings['rey_slideshow_container'] !== '' ){
					$slideshow_config['class'] = '--slideshow-container --slideshow-container-gap-' . esc_attr( $settings['gap'] );
				}

				if( $settings['rey_slideshow_effect'] === 'scaler' ){
					$slideshow_config['type'] = 'fade';
				}
				elseif( $settings['rey_slideshow_effect'] === 'fade' ){
					$slideshow_config['type'] = 'fade';
				}

				$element->add_render_attribute( 'slideshow_wrapper', 'data-rey-slideshow-settings', wp_json_encode($slideshow_config) );
				$element->add_render_attribute( 'slideshow_wrapper', 'data-rey-slideshow-nav', esc_attr( $settings['rey_slideshow_nav'] ) );

				// Catch output
				ob_start();

			}


			if( !empty($classes) ){
				$element->add_render_attribute( '_wrapper', 'class', $classes );
			}

			// scripts
			if( in_array($settings['rey_scroll_effects'], ['clip-in', 'clip-out', 'colorize'], true) ){

				reyCoreAssets()->add_scripts('scroll-out');

				if( $settings['rey_scroll_effects'] === 'colorize' ){
					reyCoreAssets()->add_scripts('reycore-elementor-scroll-colorize');
				}
				else {
					reyCoreAssets()->add_scripts('reycore-elementor-scroll-clip');
				}
			}

		}


		/**
		 * Add HTML after section rendering
		 *
		 * @since 1.0.0
		 **/
		function after_render( $element )
		{
			if( ! apply_filters( "elementor/frontend/section/should_render", true, $element ) ){
				return;
			}

			$settings = $element->get_settings_for_display();

			// Image Tag
			if( 'classic' === $settings['background_background'] && ($bg_image = $settings['background_image']) && $settings['rey_bg_image_lazy'] !== '' ){

				$bg_image_tablet = isset($settings['background_image_tablet']['id']) ? $settings['background_image_tablet']['id'] : false;
				$bg_image_mobile = isset($settings['background_image_mobile']['id']) ? $settings['background_image_mobile']['id'] : false;

				$desktop_class = 'rey-section-wrap-bg-image';

				if( $bg_image_tablet ){
					$desktop_class .= ' --dnone-tablet';
				}

				if( $bg_image_mobile ){
					$desktop_class .= ' --dnone-mobile';
				}

				$image_html = wp_get_attachment_image( $bg_image['id'], apply_filters('reycore/elementor/bg_image_lazy', 'large'), false, [
					'class' => $desktop_class
				]);

				$mobile_size = apply_filters('reycore/elementor/bg_image_lazy/mobile', 'medium');

				if( $bg_image_tablet ){
					$image_html .= wp_get_attachment_image( $bg_image_tablet, $mobile_size, false, [
						'class' => 'rey-section-wrap-bg-image --visible-tablet ' . ( ! $bg_image_mobile ? '--visible-mobile' : '' )
					]);
				}

				if( $bg_image_mobile ){
					$image_html .= wp_get_attachment_image( $bg_image_mobile, $mobile_size, false, [
						'class' => 'rey-section-wrap-bg-image --visible-mobile'
					]);
				}

				// Collect output
				$content = ob_get_clean();

				$html_tag = $settings['html_tag'] ?: 'section';
				$query = sprintf('//%s[contains( @class, "elementor-element-%s")]', $html_tag, $element->get_id());

				if( $new_html = ReyCoreElementor::el_inject_html( $content, $image_html, $query) ){
					$content = $new_html;
				}

				echo $content;
			}
			elseif( 'rey_slideshow' === $settings['background_background'] && isset($settings['rey_slideshow_slides']) && !empty($settings['rey_slideshow_slides']) ){

				$slideshow_html = sprintf(
					'<div class="rey-section-slideshow splide--%s splide" %s>',
					esc_attr( $settings['rey_slideshow_effect'] ),
					$element->get_render_attribute_string( 'slideshow_wrapper' )
				);

					$slideshow_html .= '<div class="splide__track">';
					$slideshow_html .= '<div class="splide__list">';

					foreach ($settings['rey_slideshow_slides'] as $index => $item) {
						if( isset($item['img']['id']) && $image_id = $item['img']['id'] ){

							$img = wp_get_attachment_image( $image_id, 'full', false, [
								'class' => 'rey-section-slideshowItem-img'
							]);

							$slideshow_html .= sprintf(
								'<div class="splide__slide rey-section-slideshowItem rey-section-slideshowItem--%1$d elementor-repeater-item-%3$s %4$s">%2$s</div>',
								$index,
								$img,
								$item['_id'],
								($index === 0 ? 'is-active' : '')
							);
						}
					}

					$slideshow_html .= '</div>';
					$slideshow_html .= '</div>';
				$slideshow_html .= '</div>';

				// Collect output
				$content = ob_get_clean();

				$html_tag = $settings['html_tag'] ?: 'section';
				$query = sprintf('//%s[contains( @class, "elementor-element-%s")]', $html_tag, $element->get_id());

				if( $new_html = ReyCoreElementor::el_inject_html( $content, $slideshow_html, $query) ){
					$content = $new_html;
				}

				echo $content;
			}
			elseif( $element->get_settings_for_display('rey_modal') == 'modal-section' ){
				echo '</div></div>';
			}

		}


		/**
		 * Filter Section Print Content
		 *
		 * @since 1.0.0
		 **/
		function print_template( $template )
		{

			$template_new = "

			<# if ( settings.background_background && settings.background_background == 'rey_slideshow' && settings.rey_slideshow_slides ) { #>

				<# var slide_config = JSON.stringify({
					'autoplay': settings.rey_slideshow_autoplay,
					'interval': settings.rey_slideshow_autoplay_time,
					'animationDuration': settings.rey_slideshow_speed,
					'mobile': settings.rey_slideshow_mobile !== ''
				}); #>

				<div class='splide rey-section-slideshow' data-rey-slideshow-effect='{{settings.rey_slideshow_effect}}' data-rey-slideshow-nav='{{settings.rey_slideshow_nav}}' data-rey-slideshow-settings='{{slide_config}}'>
					<div class='splide__track'>
						<div class='splide__list'>
							<# _.each( settings.rey_slideshow_slides, function( item, index ) { #>
								<div class='splide__slide rey-section-slideshowItem rey-section-slideshowItem--{{index}} elementor-repeater-item-{{item._id}}'>
									<img src='{{item.img.url}}' class='rey-section-slideshowItem-img' />
								</div>
							<# } ); #>
						</div>
					</div>
				</div>
			<#	} #>";


			// $template_new .= "
			// <# if ( settings.rey_hod__enable !== '' ) {
			// 	var hod_config = JSON.stringify({
			// 		'hide_type': settings.rey_hod__hide_type,
			// 		'close_position': settings.rey_hod__close_position,
			// 		'store_state': settings.rey_hod__store_state,
			// 	});
			// 	view.addRenderAttribute( '_wrapper', 'data-rey-hod-settings', hod_config );
			// } #>";

			return $template_new . $template;
		}
	}

endif;
