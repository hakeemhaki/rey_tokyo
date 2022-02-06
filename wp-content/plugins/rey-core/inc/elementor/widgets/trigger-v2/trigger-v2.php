<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if(!class_exists('ReyCore_Widget_Trigger_V2')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Trigger_V2 extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-trigger-v2';
	}

	public function get_title() {
		return __( 'Trigger', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-menu-bar';
	}

	public function get_categories() {
		return [ 'rey-header', 'rey-theme' ];
	}

	// public function get_custom_help_url() {
	// 	return 'https://support.reytheme.com/kb/rey-elements-header/#fullscreen-navigation';
	// }

	function controls__settings(){

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'trigger',
			[
				'label' => esc_html__( 'Trigger type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'click',
				'options' => [
					'click'  => esc_html__( 'On Click', 'rey-core' ),
					'hover'  => esc_html__( 'On Hover', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'action',
			[
				'label' => esc_html__( 'Action', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Select', 'rey-core' ),
					'offcanvas'  => esc_html__( 'Open Off-Canvas Panel (Global Section)', 'rey-core' ),
					// open modal?
					// dropdpwn
				],
			]
		);

		$this->add_control(
			'offcanvas_panel',
			[
				'label_block' => true,
				'label' => __( 'Off-Canvas Panel Sections', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ReyCore_GlobalSections::get_global_sections('offcanvas', [
					'' => __('- Select -', 'rey-core')
				]),
				'condition' => [
					'action' => 'offcanvas',
				],
			]
		);

		$this->end_controls_section();
	}

	function controls__styles() {

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'hamburger',
					'options' => [
						'hamburger'  => esc_html__( 'Hamburger Icon', 'rey-core' ),
						'button'  => esc_html__( 'Button', 'rey-core' ),
						'image'  => esc_html__( 'Image', 'rey-core' ),
						// 'lottie'  => esc_html__( 'Lottie animation', 'rey-core' ),
					],
				]
			);


		$this->end_controls_section();
	}

	function controls__hamburger_styles() {

		$this->start_controls_section(
			'section_styles_hamburger',
			[
				'label' => __( 'Hamburger Icon', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'hamburger',
				],
			]
		);

			$this->add_control(
				'hamburger_style',
				[
					'label' => esc_html__( 'Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default - 3 bars', 'rey-core' ),
						'--2b'  => esc_html__( '2 bars', 'rey-core' ),
						'--2bh'  => esc_html__( '2 bars + hover', 'rey-core' ),
						'--2b2'  => esc_html__( '2 bars v2', 'rey-core' ),
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_width',
				[
					'label' => esc_html__( 'Bars Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-width: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_bars_thick',
				[
					'label' => esc_html__( 'Bars Thickness', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 15,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-thick: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_bars_distance',
				[
					'label' => esc_html__( 'Bars Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 15,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_bars_round',
				[
					'label' => esc_html__( 'Bars Roundness', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 2,
					'min' => 0,
					'max' => 15,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-roundness: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hamburger_text',
				[
					'label' => esc_html__( 'Custom Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'separator' => 'before'
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'hamburger_text_styles',
					'selector' => '{{WRAPPER}} .__custom-text',
				]
			);

			$this->add_control(
				'hamburger_text_reverse',
				[
					'label' => esc_html__( 'Flip Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'hamburger_text_distance',
				[
					'label' => esc_html__( 'Text distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .__custom-text' => '--text-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'hamburger_text_color',
				[
					'label' => esc_html__( 'Text color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__custom-text' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hamburger_text_mobile',
				[
					'label' => esc_html__( 'Hide text on mobiles/tablet', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

		$this->end_controls_section();
	}

	function controls__button_styles() {

		$this->start_controls_section(
			'section_btn_style',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'button',
				],
			]
		);

			$this->add_control(
				'btn_text',
				[
					'label' => esc_html__( 'Button text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Click here', 'rey-core' ),
					'placeholder' => esc_html__( 'eg: click here', 'rey-core' ),
				]
			);

			$this->add_control(
				'btn_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'btn-line-active',
					'options' => [
						'btn-simple'  => __( 'Link', 'rey-core' ),
						'btn-primary'  => __( 'Primary', 'rey-core' ),
						'btn-secondary'  => __( 'Secondary', 'rey-core' ),
						'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
						'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
						'btn-line-active'  => __( 'Underlined', 'rey-core' ),
						'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
						'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
					],
				]
			);

			$this->start_controls_tabs( 'tabs_items_styles' );

				$this->start_controls_tab(
					'tabs_btn_normal',
					[
						'label' => esc_html__( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_hover',
					[
						'label' => esc_html__( 'Active', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color_active',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_active',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width_active',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_active',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();
			$this->end_controls_tabs();

			$this->add_responsive_control(
				'btn_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
					'selector' => '{{WRAPPER}} .rey-triggerBtn.--button2',
				]
			);

			$this->add_control(
				'icon',
				[
					'label' => __( 'Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'default' => [
						'value' => 'fas fa-plus',
						'library' => 'fa-solid',
					],
					'separator' => 'before'
				]
			);

			$this->add_responsive_control(
				'icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => '--icon-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_distance',
				[
					'label' => esc_html__( 'Icon Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 0,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => '--icon-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => '--icon-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'icon_reverse',
				[
					'label' => esc_html__( 'Move icon to left', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		$this->end_controls_section();
	}

	function controls__image_styles(){

		$this->start_controls_section(
			'section_image_style',
			[
				'label' => __( 'Image Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'image',
				],
			]
		);

			$this->add_control(
				'the_image',
				[
				   'label' => esc_html__( 'Select Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);

			$this->add_control(
				'image_size_css',
				[
				   'label' => esc_html__( 'Custom Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em', '%' ],
					'range' => [
						'px' => [
							'min' => 8,
							'max' => 1280,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 90,
					],
					'selectors' => [
						'{{WRAPPER}} .rey-triggerImg' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'medium',
					'exclude' => ['custom'],
					'label' => esc_html__('Physical image size', 'rey-core')
				]
			);

		$this->end_controls_section();

	}

	function controls__lottie_styles(){

		$this->start_controls_section(
			'section_lottie_style',
			[
				'label' => __( 'Lottie Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'lottie',
				],
			]
		);

			$this->add_control(
				'lottie_source',
				[
					'label' => __( 'Source', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'media_file',
					'options' => [
						'media_file' => __( 'Media File', 'rey-core' ),
						'external_url' => __( 'External URL', 'rey-core' ),
					],
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'lottie_source_external_url',
				[
					'label' => __( 'External URL', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'condition' => [
						'lottie_source' => 'external_url',
					],
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'Enter your URL', 'rey-core' ),
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'lottie_source_json',
				[
					'label' => __( 'Upload JSON File', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'media_type' => 'application/json',
					'frontend_available' => true,
					'condition' => [
						'lottie_source' => 'media_file',
					],
				]
			);

			$this->add_control(
				'lottie_size_css',
				[
				   'label' => esc_html__( 'Custom Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em', '%' ],
					'range' => [
						'px' => [
							'min' => 8,
							'max' => 1280,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 90,
					],
					'selectors' => [
						'{{WRAPPER}} .rey-triggerLottie' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);


		$this->end_controls_section();

	}

	protected function register_controls() {

		$this->controls__settings();
		$this->controls__styles();
		$this->controls__hamburger_styles();
		$this->controls__button_styles();
		$this->controls__image_styles();
		// $this->controls__lottie_styles();

	}


	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render()
	{
		reyCoreAssets()->add_styles(['reycore-widget-trigger-v2-styles']);

		$settings = $this->get_settings_for_display();

		// bail if no action is set
		if( $settings['action'] === '' ){
			return;
		}

		$attributes = [];

		// offcanvas
		if ($settings['action'] === 'offcanvas' && ($gs_offcanvas = $settings['offcanvas_panel'])){
			$attributes[] = sprintf('data-offcanvas-id="%s"', esc_attr($gs_offcanvas));
			$attributes[] = sprintf('data-action="%s"', esc_attr($settings['action']));
			add_filter("reycore/module/offcanvas_panels/load_panel={$gs_offcanvas}", '__return_true');
		}

		$attributes[] = sprintf('data-trigger="%s"', esc_attr($settings['trigger']));

		$classes = [
			'btn',
			'rey-triggerBtn',
			'js-triggerBtn',
			'--' . $settings['layout'] . '2',
		];

		if( $settings['layout'] === 'button') {
			if( $btn_text = $settings['btn_text'] ){
				$classes[] = $settings['btn_style'];
				$classes[] = $settings['icon_reverse'] === 'yes' ? '--reverse-icon' : '';
			}
		}
		elseif( $settings['layout'] === 'image') {

		}
		else {
			$classes[] = 'rey-headerIcon';

			if( $settings['hamburger_style'] !== '' ){
				$classes[] = '--hamburger2' . $settings['hamburger_style'];
			}
		}

		$attributes[] = sprintf('aria-label="%s"', esc_html__('Open', 'rey-core'));

		printf('<button class="%s" %s>', esc_attr(implode(' ', $classes)), implode(' ', $attributes));

			if( $settings['layout'] === 'button'  ){

				if( $btn_text = $settings['btn_text'] ){
					printf( '<span>%s</span>', do_shortcode($settings['btn_text']) );
				}

				if( ($icon = $settings['icon']) ){
					\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
				}

			}

			elseif( $settings['layout'] === 'image' ){
				if( $image = $settings['the_image'] ) {
					echo reycore__get_attachment_image( [
						'image' => $image,
						'size' => $settings['image_size'],
						'attributes' => ['class'=>'rey-triggerImg']
					] );
				}
			}

			elseif( $settings['layout'] === 'hamburger' ){

				echo '<span class="__bars"><span class="__bar"></span><span class="__bar"></span><span class="__bar"></span></span>';

				if( $custom_text = $settings['hamburger_text'] ){
					$custom_text_class = $settings['hamburger_text_mobile'] === 'yes' ? '--dnone-tablet --dnone-mobile' : '';
					$custom_text_class .= $settings['hamburger_text_reverse'] ? ' --flip' : '';
					printf('<span class="__custom-text %s">%s</span>', $custom_text_class, $custom_text);
				}
			}

		echo '</button>';

		do_action('reycore/elementor/btn_trigger');

	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}

}
endif;
