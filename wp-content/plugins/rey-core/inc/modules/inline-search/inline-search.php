<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_Module__InlineSearch') ):

	class ReyCore_Module__InlineSearch
	{

		public static $is_enabled = false;

		const ASSET_HANDLE = 'reycore-inlinesearch';

		public function __construct()
		{
			add_action( 'init', [$this, 'init']);
			add_action( 'wp', [$this, 'wp']);
			add_filter( 'reycore/kirki_fields/field=header_search_style', [ $this, 'add_customizer_option' ] );
			add_action( 'elementor/element/reycore-header-search/section_settings/before_section_end', [ $this, 'add_elementor_style_option' ] );
			add_action( 'elementor/element/reycore-header-search/section_styles/after_section_start', [ $this, 'elementor_hide_initial_styles_section' ] );
			add_action( 'elementor/element/reycore-header-search/section_styles/after_section_end', [ $this, 'add_elementor_style_options' ] );
		}

		function is_enabled(){
			return get_theme_mod('header_enable_search', true) && function_exists('reycore_wc__get_header_search_args') && 'inline' === reycore_wc__get_header_search_args('search_style');
		}

		function init(){

			self::$is_enabled = $this->is_enabled();

			add_action( 'reycore/elementor/header-search/template', [$this, 'elementor_inline_search_form'], 10, 2);
			add_action( 'wp', [ $this, 'remove_default_search' ]);
		}

		public function wp(){
			add_action( 'rey/header/row', [$this, 'inline_search_form'], 30);
			add_filter( 'rey/main_script_params', [ $this, 'script_params'], 11 );
			add_filter( 'reycore/elementor/header-search/assets', [$this, 'add_element_assets'], 20, 2);
			add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}

		public function add_customizer_option( $args ){
			$args['choices']['inline'] = esc_html__( 'Inline Form', 'rey-core' );
			return $args;
		}

		public function add_elementor_style_option( $element ){
			$search_styles = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'search_style' );
			$search_styles['options'] = $search_styles['options'] + [ 'inline' => esc_html__( 'Inline Form', 'rey-core' ) ];
			$element->update_control( 'search_style', $search_styles );
		}

		public function elementor_hide_initial_styles_section( $element ){
			$search_styles = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'section_styles' );
			$search_styles['condition']['search_style!'] = ['inline'];
			$element->update_control( 'section_styles', $search_styles );
		}


		/**
		 * Remove default search button
		 *
		 * @since 1.3.0
		 */
		function remove_default_search() {

			if( !self::$is_enabled ){
				return;
			}

			remove_action('rey/header/row', 'rey__header__search', 30);
		}

		/**
		 * Add markup
		 *
		 * @since 1.3.0
		 **/
		function inline_search_form(){

			if( !self::$is_enabled ){
				return;
			}

			$this->load_scripts();

			reycore__get_template_part('inc/modules/inline-search/tpl-search-form-inline');
		}

		/**
		 * Add markup in Elementor
		 *
		 * @since 1.3.0
		 **/
		function elementor_inline_search_form($settings, $search_style){

			// Inline Form
			if( $search_style === 'inline' ){

				$this->load_scripts();

				reycore__get_template_part('inc/modules/inline-search/tpl-search-form-inline');
			}
		}

		/**
		 * Filter main script's params
		 *
		 * @since 1.0.0
		 **/
		public function script_params($params)
		{
			$params['ajax_search_only_title'] = false;
			return $params;
		}

		function add_element_assets( $styles, $search_style ){

			if( $search_style === 'inline' ){
				$styles[] = self::ASSET_HANDLE;
			}

			return $styles;
		}

		public function register_assets(){

			reyCoreAssets()->register_asset('styles', [
				self::ASSET_HANDLE => [
					'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				]
			]);

			$script_deps = ['reycore-scripts'];

			if( class_exists('WooCommerce') ){
				$script_deps[] = 'reycore-woocommerce';
			}

			reyCoreAssets()->register_asset('scripts', [
				self::ASSET_HANDLE => [
					'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js',
					'deps'    => $script_deps,
					'version'   => REY_CORE_VERSION,
				]
			]);

		}

		function enqueue_scripts(){

			// if( ! self::$is_enabled ){
			// 	return;
			// }

			// option already enabled in CST.
			// get_theme_mod('header_search_style', 'wide') === 'inline'

			if( class_exists('\Elementor\Plugin') &&
				( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) ){
				self::load_scripts();
			}
		}

		public static function load_scripts(){
			reyCoreAssets()->add_styles(['reycore-header-search-top', 'reycore-header-search']);
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
			reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
			reyCoreAssets()->add_scripts(['reycore-header-search']);
		}

		function add_elementor_style_options( $element ){

			$element->start_controls_section(
				'section_styles_inline',
				[
					'label' => __( 'Inline Form Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'search_style' => 'inline',
					],
				]
			);

			$element->start_controls_tabs( 'inline_colors' );

				$element->start_controls_tab(
					'inline_colors_normal',
					[
						'label' => __( 'Normal', 'rey-core' ),
					]
				);

					$element->add_control(
						'inline_text_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.rey-headerSearch--inline input[type="search"]' => 'color: {{VALUE}}',
							],
							'condition' => [
								'search_style' => 'inline',
							],
						]
					);

					$element->add_control(
						'inline_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-headerSearch--inline form' => 'background-color: {{VALUE}}',
								'{{WRAPPER}} .rey-headerSearch--inline form:before' => 'display: none',
							],
							'condition' => [
								'search_style' => 'inline',
							],
						]
					);

					$element->add_control(
						'inline_icon_color',
						[
							'label' => esc_html__( 'Search Icon Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-headerSearch--inline .icon-search' => 'color: {{VALUE}}',
							],
							'condition' => [
								'search_style' => 'inline',
							],
						]
					);


				$element->end_controls_tab();

				$element->start_controls_tab(
					'inline_colors_focus',
					[
						'label' => __( 'Focused', 'rey-core' ),
					]
				);

					$element->add_control(
						'inline_text_color_active',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.search-inline--active {{WRAPPER}} .rey-headerSearch--inline input[type="search"]' => 'color: {{VALUE}}',
							],
							'condition' => [
								'search_style' => 'inline',
							],
						]
					);

					$element->add_control(
						'inline_bg_color_active',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.search-inline--active {{WRAPPER}} .rey-headerSearch--inline form:before' => 'display: block; background-color: {{VALUE}};',
							],
							'condition' => [
								'search_style' => 'inline',
							],
						]
					);


					$element->add_control(
						'inline_icon_color__active',
						[
							'label' => esc_html__( 'Search Icon Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'.search-inline--active {{WRAPPER}} .rey-headerSearch--inline .icon-search' => 'color: {{VALUE}};',
							],
							'condition' => [
								'search_style' => 'inline',
							],
						]
					);


				$element->end_controls_tab();

			$element->end_controls_tabs();

			$element->add_control(
				'inline_icon_color_mobile',
				[
					'label' => esc_html__( 'Search Icon Color [Mobile]', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-headerSearch--inline .rey-headerSearch-toggle .icon-search' => 'color: {{VALUE}}',
					],
					'condition' => [
						'search_style' => 'inline',
					],
				]
			);

			$element->add_responsive_control(
				'inline_icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-headerSearch--inline .icon-search' => 'font-size: {{VALUE}}px',
					],
					'condition' => [
						'search_style' => 'inline',
					],
				]
			);

			$element->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'inline_border',
					'selector' => '{{WRAPPER}} .rey-headerSearch--inline form',
					'condition' => [
						'search_style' => 'inline',
					],
					'separator' => 'before',
				]
			);

			$element->add_responsive_control(
				'inline_border_radius',
				[
					'label' => __( 'Border Radius', 'elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .rey-headerSearch--inline input[type="search"], {{WRAPPER}} .rey-headerSearch--inline .search-btn, {{WRAPPER}} .rey-headerSearch--inline form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'search_style' => 'inline',
					],
				]
			);

			$element->add_control(
				'use_button',
				[
					'label' => esc_html__( 'Icon as Button?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'no',
					'options' => [
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
					'separator' => 'before',
					'prefix_class' => '--has-button-'
				]
			);

			$element->add_control(
				'inline_button_color',
				[
					'label' => esc_html__( 'Button Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}.--has-button-yes .rey-headerSearch--inline .search-btn' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'search_style' => 'inline',
						'use_button' => 'yes'
					],
				]
			);

			$element->add_control(
				'inline_button_color_hover',
				[
					'label' => esc_html__( 'Button Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}.--has-button-yes .rey-headerSearch--inline .search-btn:hover' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'search_style' => 'inline',
						'use_button' => 'yes'
					],
				]
			);

			$element->add_control(
				'expand_click',
				[
					'label' => esc_html__( 'Expand on click', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--expand-',
					'separator' => 'before'
				]
			);

			$element->add_control(
				'inline_custom_width',
				[
					'label' => esc_html__( 'Desktop Custom Width', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 100,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-headerSearch--inline' => '--width:{{VALUE}}px',
					],
				]
			);

			$element->end_controls_section();
		}

	}

	new ReyCore_Module__InlineSearch();
endif;
