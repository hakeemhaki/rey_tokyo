<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if(!class_exists('ReyCore_Widget_Basic_Post_Grid')):
	/**
	 *
	 * Elementor widget.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Widget_Basic_Post_Grid extends \Elementor\Widget_Base {

		public $_query = null;

		public $_settings = [];

		private $posts_archive = null;

		public function get_name() {
			return 'reycore-basic-post-grid';
		}

		public function get_title() {
			return __( 'Posts', 'rey-core' );
		}

		public function get_icon() {
			return 'eicon-posts-grid';
		}

		public function get_categories() {
			return [ 'rey-theme' ];
		}

		public function get_keywords() {
			return [ 'post', 'grid', 'blog', 'recent', 'news' ];
		}

		protected function _register_skins() {
			$this->add_skin( new ReyCore_Widget_Basic_Post_Grid__Basic2( $this ) );
			$this->add_skin( new ReyCore_Widget_Basic_Post_Grid__Inner( $this ) );
		}

		public function get_custom_help_url() {
			return 'https://support.reytheme.com/kb/rey-elements/#post-grid';
		}

		/**
		 * Register widget controls.
		 *
		 * Adds different input fields to allow the user to change and customize the widget settings.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_layout',
				[
					'label' => __( 'Layout', 'rey-core' ),
				]
			);

			$this->add_responsive_control(
				'per_row',
				[
					'label' => __( 'Posts per row', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'prefix_class' => 'reyEl-bPostGrid%s--',
					'min' => 1,
					'max' => 6,
					'devices' => [ 'desktop', 'tablet', 'mobile' ],
					'desktop_default' => 2,
					'tablet_default' => 2,
					'mobile_default' => 1,
					'condition' => [
						'carousel!' => 'yes',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--per-row: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'posts_per_page',
				[
					'label' => __( 'Limit', 'rey-core' ),
					'description' => __( 'Select the number of items to load from query.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 2,
					'min' => 1,
					'max' => 100,
				]
			);

			$this->add_responsive_control(
				'gap',
				[
					'label' => __( 'Gap', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 30,
					'min' => 0,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .reyEl-bPostGrid' => '--bpostgrid-spacing: {{VALUE}}px;',
					],
				]
			);

			$this->add_responsive_control(
				'align',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left' => [
							'title' => __( 'Left', 'rey-core' ),
							'icon' => 'eicon-text-align-left',

						],
						'center' => [
							'title' => __( 'Center', 'rey-core' ),
							'icon' => 'eicon-text-align-center',
						],
						'right' => [
							'title' => __( 'Right', 'rey-core' ),
							'icon' => 'eicon-text-align-right',
						],
					],
					'prefix_class' => 'elementor%s-align-',
				]
			);

			$this->add_control(
				'lazy_load',
				[
					'label' => esc_html__( 'Lazy Load', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'separator' => 'before',
					'condition' => [
						// '_skin' => [],
						'add_pagination' => '',
					],
				]
			);

			$this->add_responsive_control(
				'lazy_load_placeholders_height',
				[
					'label' => esc_html__( 'Placeholder height', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--lazy-placeholder-height: {{VALUE}}px',
					],
					'condition' => [
						// '_skin' => [],
						'add_pagination' => '',
						'lazy_load!' => '',
					],
				]
			);

			$this->add_control(
				'lazy_load_placeholders_bg',
				[
					'label' => esc_html__( 'Placeholder color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--lazy-placeholder-bg: {{VALUE}}',
					],
					'condition' => [
						// '_skin' => [],
						'add_pagination' => '',
						'lazy_load!' => '',
					],
				]
			);

			$this->add_control(
				'lazy_load_trigger',
				[
					'label' => esc_html__( 'Trigger', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'scroll',
					'options' => [
						'scroll'  => esc_html__( 'On Scroll', 'rey-core' ),
						'click'  => esc_html__( 'On Click', 'rey-core' ),
						'mega-menu'  => esc_html__( 'On Mega Menu display', 'rey-core' ),
					],
					'condition' => [
						// '_skin' => [],
						'add_pagination' => '',
						'lazy_load!' => '',
					],
				]
			);

			$this->add_control(
				'lazy_load_click_trigger',
				[
					'label' => esc_html__( 'Click Selector', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: .custom-unique-selector', 'rey-core' ),
					'condition' => [
						// '_skin' => [],
						'add_pagination' => '',
						'lazy_load!' => '',
						'lazy_load_trigger' => 'click',
					],
				]
			);

			$this->end_controls_section();

			/**
			 * Query Settings
			 */

			$this->start_controls_section(
				'section_query',
				[
					'label' => __( 'Posts Query', 'rey-core' ),
				]
			);

			$this->add_control(
				'post_type',
				[
					'label' => esc_html__( 'Post Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'post',
					'options' => reycore__get_post_types_list([
						'exclude' => [
							'product', 'page'
						]
					]),
				]
			);

			$this->add_control(
				'query_type',
				[
					'label' => esc_html__('Query Type', 'rey-core'),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'recent',
					'options' => [
						'recent'           => esc_html__('Recent', 'rey-core'),
						'manual-selection' => esc_html__('Manual Selection', 'rey-core'),
						'current-query' => esc_html__('Current Query', 'rey-core'),
					],
				]
			);

			$this->add_control(
				'categories',
				[
					'label' => esc_html__('Categories', 'rey-core'),
					'placeholder' => esc_html__('- Select category -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'category',
					],
					'label_block' => true,
					'multiple' => true,
					'default'     => [],
					'condition' => [
						'query_type' => ['recent'],
						'post_type' => 'post'
					],
				]
			);

			$this->add_control(
				'all_taxonomies',
				[
					'label' => esc_html__('Taxonomy Term', 'rey-core'),
					'placeholder' => esc_html__('- Select term -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'all_taxonomies',
					],
					'label_block' => true,
					'multiple' => true,
					'default'     => [],
					'condition' => [
						'query_type' => 'recent',
						'post_type!' => ''
					],
				]
			);

			$this->add_control(
				'tags',
				[
					'label' => esc_html__('Tags', 'rey-core'),
					'placeholder' => esc_html__('- Select tags -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'post_tag',
					],
					'label_block' => true,
					'multiple' => true,
					'default'     => [],
					'condition' => [
						'query_type' => ['recent'],
						'post_type' => 'post'
					],
				]
			);

			// Advanced settings
			$this->add_control(
				'include',
				[
					'label'       => esc_html__( 'Posts', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'placeholder' => 'eg: 21, 22',
					'label_block' => true,
					'description' => __( 'Add posts IDs separated by comma.', 'rey-core' ),
					'condition' => [
						'query_type' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'exclude',
				[
					'label'       => esc_html__( 'Exclude Posts', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'placeholder' => 'eg: 21, 22',
					'label_block' => true,
					'description' => __( 'Add posts IDs separated by comma.', 'rey-core' ),
					'condition' => [
						'query_type!' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'orderby',
				[
					'label' => __( 'Order By', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'post_date',
					'options' => [
						'post_date' => __( 'Date', 'rey-core' ),
						'post_title' => __( 'Title', 'rey-core' ),
						'menu_order' => __( 'Menu Order', 'rey-core' ),
						'rand' => __( 'Random', 'rey-core' ),
					],
					'condition' => [
						'query_type!' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'order',
					[
					'label' => __( 'Order', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'desc',
					'options' => [
						'asc' => __( 'ASC', 'rey-core' ),
						'desc' => __( 'DESC', 'rey-core' ),
					],
					'condition' => [
						'query_type!' => 'manual-selection',
					],
				]
			);

			$this->add_control(
				'exclude_duplicates',
				[
					'label' => __( 'Exclude Duplicates', 'rey-core' ),
					'description' => __( 'Exclude duplicates that were already loaded in this page', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'add_pagination',
				[
					'label' => __( 'Enable Pagination', 'rey-core' ),
					'description' => __( 'Use element as a posts archive.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'query_id',
				[
					'label' => esc_html__( 'Custom Query ID', 'rey-core' ),
					'description' => esc_html__( 'Give your Query a custom unique id to allow server side filtering.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: my_custom_filter', 'rey-core' ),
					'separator' => 'before',
				]
			);

			$this->end_controls_section();

			/* ------------------------------------ CAROUSEL ------------------------------------ */

			$this->start_controls_section(
				'section_carousel_settings',
				[
					'label' => __( 'Carousel Settings', 'rey-core' ),
				]
			);

			$this->add_control(
				'carousel',
				[
					'label' => esc_html__( 'Activate Carousel', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);


			$slides_to_show = range( 1, 10 );
			$slides_to_show = array_combine( $slides_to_show, $slides_to_show );

			$this->add_responsive_control(
				'slides_to_show',
				[
					'label' => __( 'Slides to Show', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' => __( 'Default', 'rey-core' ),
					] + $slides_to_show,
					'condition' => [
						'carousel' => 'yes',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--slides-to-show: {{VALUE}}; --per-row: {{VALUE}}',
					],
					'devices' => [ 'desktop', 'tablet', 'mobile' ],
					'desktop_default' => 4,
					'tablet_default' => 3,
					'mobile_default' => 2,
				]
			);

			$this->add_control(
				'pause_on_hover',
				[
					'label' => __( 'Pause on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'autoplay_speed',
				[
					'label' => __( 'Autoplay Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'infinite',
				[
					'label' => __( 'Infinite Loop', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'effect',
				[
					'label' => __( 'Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'slide',
					'options' => [
						'slide' => __( 'Slide', 'rey-core' ),
						'fade' => __( 'Fade', 'rey-core' ),
					],
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'speed',
				[
					'label' => __( 'Animation Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'direction',
				[
					'label' => __( 'Direction', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'ltr',
					'options' => [
						'ltr' => __( 'Left', 'rey-core' ),
						'rtl' => __( 'Right', 'rey-core' ),
					],
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'carousel_arrows',
				[
					'label' => esc_html__( 'Add arrows', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'carousel!' => '',
					],
				]
			);

			$this->add_control(
				'carousel_pagination',
				[
					'label' => esc_html__( 'Add pagination dots', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'carousel!' => '',
					],
				]
			);

			$this->add_control(
				'carousel_viewport_offset',
				[
					'label' => esc_html__( 'Container Offset', 'rey-core' ),
					'description' => esc_html__( 'This option will pull the carousel horizontal sides toward the viewport edges. Applies only on desktop and overrides all settings.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'on',
					'separator' => 'before',
					'prefix_class' => '--offset-',
					'condition' => [
						'carousel!' => '',
					],
				]
			);

			$this->add_control(
				'carousel_viewport_offset_side',
				[
					'label' => esc_html__( 'Container Offset Side', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'both',
					'options' => [
						'both'  => esc_html__( 'Both', 'rey-core' ),
						'left'  => esc_html__( 'Left', 'rey-core' ),
						'right'  => esc_html__( 'Right', 'rey-core' ),
					],
					'prefix_class' => '--offset-on-',
					'condition' => [
						'carousel!' => '',
						'carousel_viewport_offset!' => '',
					],
				]
			);


			$this->add_responsive_control(
				'carousel_padding',
				[
					'label' => __( 'Horizontal Padding (Offset)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', 'vw' ],
					'condition' => [
						'carousel!' => '',
					],
					'allowed_dimensions' => 'horizontal',
					'selectors' => [
						'{{WRAPPER}} .splide__track' => 'padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; ',
					],
				]
			);

			$this->add_control(
				'carousel_id',
				[
					'label' => __( 'Carousel Unique ID', 'rey-core' ),
					'separator' => 'before',
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('carousel-'),
					'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="https://support.reytheme.com/kb/products-grid-element/#adding-custom-navigation" target="_blank">how to connect them</a>.', 'rey-core' ),
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);


			$this->add_control(
				'disable_desktop',
				[
					'label' => esc_html__( 'Disable on desktop', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'desktop',
					'prefix_class' => '--disable-',
					'separator' => 'before',
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'disable_tablet',
				[
					'label' => esc_html__( 'Disable on tablet', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'tablet',
					'prefix_class' => '--disable-',
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->add_control(
				'disable_mobile',
				[
					'label' => esc_html__( 'Disable on mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'mobile',
					'prefix_class' => '--disable-',
					'condition' => [
						'carousel' => 'yes',
					],
				]
			);

			$this->end_controls_section();


			/* ------------------------------------ Inner Skin Options ------------------------------------ */


			$this->start_controls_section(
				'section_innerskin_styles',
				[
					'label' => __( 'Inner Skin Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'_skin' => 'inner',
					],
				]
			);

			$this->add_control(
				'inner_align',
				[
					'label' => esc_html__( 'Vertical Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'flex-end',
					'options' => [
						'flex-start'  => esc_html__( 'Top', 'rey-core' ),
						'center'  => esc_html__( 'Middle', 'rey-core' ),
						'flex-end'  => esc_html__( 'Bottom', 'rey-core' ),
					],
					'selectors' => [
						'{{WRAPPER}} .reyEl-bPostGrid-inner' => 'justify-content: {{VALUE}}',
					],
					'condition' => [
						'_skin' => 'inner',
					],
				]
			);

			$this->add_responsive_control(
				'inner_spacing',
				[
				   'label' => esc_html__( 'Content Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 180,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 10.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 40,
					],
					'selectors' => [
						'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-spacing: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'_skin' => 'inner',
					],
				]
			);

			$this->add_control(
				'inner_image_stretch',
				[
					'label' => esc_html__( 'Image Stretch', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'inner_hover_effect',
				[
					'label' => esc_html__( 'Hover Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'none',
					'options' => [
						'none'  => esc_html__( 'Default', 'rey-core' ),
						'scale'  => esc_html__( 'Scale', 'rey-core' ),
						'clip'  => esc_html__( 'Clip & Scale', 'rey-core' ),
						'shift'  => esc_html__( 'Subtle Shift', 'rey-core' ),
					],
				]
			);

			$this->start_controls_tabs( 'tabs_inner_styles' );

				$this->start_controls_tab(
					'tab_inner_normal',
					[
						'label' => __( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'inner_text_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-text-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'inner_link_color',
						[
							'label' => __( 'Link Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-link-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'inner_bg_color',
						[
							'label' => __( 'Overlay Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-inner-bg-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_inner_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'inner_link_color_hover',
						[
							'label' => __( 'Link Hover Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-link-hover-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'inner_bg_color_hover',
						[
							'label' => __( 'Overlay Hover Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-inner-bg-hover-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();


			$this->end_controls_section();

			/* ------------------------------------ Start section - Thumbnail ------------------------------------ */

			$this->start_controls_section(
				'section_thumb_styles',
				[
					'label' => __( 'Thumbnail Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'thumb',
				[
					'label' => __( 'Display thumbnail', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'thumb_size',
				[
					'label' => __( 'Thumbnail layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'natural',
					'options' => [
						'natural'  => __( 'Natural', 'rey-core' ),
						'custom'  => __( 'Custom Height', 'rey-core' ),
					],
					'prefix_class' => 'reyEl-bpost-thumb--',
					'condition' => [
						'thumb' => 'yes',
					],
				]
			);

			$this->add_responsive_control(
				'custom_height',
				[
					'label' => __( 'Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'default' => [
						'size' => 300,
					],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 800,
						],
					],
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}}.reyEl-bpost-thumb--custom .reyEl-bpost-thumb' => 'height: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'thumb' => 'yes',
						'thumb_size' => 'custom',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'medium_large',
					'separator' => 'before',
					'exclude' => ['custom'],
					// 'condition' => [
					// 	'_skin!' => 'carousel-section',
					// ],
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_title_styles',
				[
					'label' => __( 'Title Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'title',
					[
						'label' => __( 'Display title', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'return_value' => 'yes',
						'default' => 'yes',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'title_typo',
						'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
						'selector' => '{{WRAPPER}} .reyEl-bpost-title',
						'condition' => [
							'title' => ['yes'],
						],
					]
				);

				$this->add_control(
					'title_underline_effect',
					[
						'label' => esc_html__( 'Title underline effect', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
						'prefix_class' => '--title-hover-',
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' => esc_html__( 'Title Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-bpost-title a' => 'color: {{VALUE}}',
						],
						'condition' => [
							'_skin!' => 'inner',
						],
					]
				);

			$this->end_controls_section();



			$this->start_controls_section(
				'section_meta_styles',
				[
					'label' => __( 'Meta Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'meta_author',
				[
					'label' => __( 'Display Author', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'meta_date',
				[
					'label' => __( 'Display Date', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'meta_comments',
				[
					'label' => __( 'Display Comments', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'meta_color',
				[
					'label' => esc_html__( 'Meta Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-postInfo, {{WRAPPER}} .rey-postInfo a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			$this->add_control(
				'date_color',
				[
					'label' => esc_html__( 'Date Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-entryDate' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_content_styles',
				[
					'label' => __( 'Excerpt Settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'excerpt',
				[
					'label' => __( 'Display excerpt', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'excerpt_length',
				[
					'label' => __( 'Excerpt Length', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 20,
					'min' => 0,
					'max' => 200,
					'step' => 0,
				]
			);

			$this->add_control(
				'excerpt_color',
				[
					'label' => esc_html__( 'Excerpt Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .reyEl-bpost-content, {{WRAPPER}} .reyEl-bpost-content a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			// settings pt image size

			$this->end_controls_section();


			$this->start_controls_section(
				'section_footer_styles',
				[
					'label' => __( 'Post footer settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'read_more',
				[
					'label' => __( 'Display Read more button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'read_more_text',
				[
					'label' => esc_html__( 'Button text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Continue Reading', 'rey-core' ),
					'condition' => [
						'read_more!' => '',
					],
				]
			);

			$this->add_control(
				'read_duration',
				[
					'label' => __( 'Display Read Duration', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
					'condition' => [
						'read_more' => ['yes'],
					],
				]
			);

			$this->add_control(
				'footer_color',
				[
					'label' => esc_html__( 'Footer Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .reyEl-bpost-footer, {{WRAPPER}} .reyEl-bpost-footer a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_posts_misc',
				[
					'label' => __( 'Misc. settings', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'vertical_spacing',
				[
				   'label' => esc_html__( 'Vertical Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 9,
							'max' => 180,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 30,
					],
					'selectors' => [
						'{{WRAPPER}} .reyEl-bPostGrid' => '--bpostgrid-vspacing: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'entry_animation',
				[
					'label' => __( 'Animate on scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_carousel_styles',
				[
					'label' => __( 'Carousel Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'carousel!' => '',
					],
				]
			);

				$this->add_control(
					'pagination_color',
					[
						'label' => esc_html__( 'Pagination Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .__pagination' => '--color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

		}

		public function get_query() {
			return $this->_query;
		}

		public function pre_get_posts_query_filter( $query ){
			$query_id = $this->_settings[ 'query_id' ];
			do_action( "reycore/elementor/query/{$query_id}", $query, $this );
		}

		public function query_posts() {

			$query_args = [
				'posts_per_page' => $this->_settings['posts_per_page'] ? $this->_settings['posts_per_page'] : get_option('posts_per_page'),
				'post_type' => $this->_settings['post_type'],
				'post_status' => 'publish',
				'ignore_sticky_posts' => true,
				// 'no_found_rows' => true,
				// 'update_post_meta_cache' => false, //useful when post meta will not be utilized
				'update_post_term_cache' => false, //useful when taxonomy terms will not be utilized
				'orderby' => isset($this->_settings['orderby']) ? $this->_settings['orderby'] : 'date',
				'order' => isset($this->_settings['order']) ? $this->_settings['order'] : 'DESC',
			];

			if( $this->_settings['query_type'] == 'current-query' ){
				$current_query_args = array_filter($GLOBALS['wp_query']->query_vars);
				$query_args = array_merge($current_query_args, $query_args);
			}
			else if( $this->_settings['query_type'] == 'manual-selection' && !empty($this->_settings['include']) ) {
				$query_args['post__in'] = array_map( 'trim', explode( ',', $this->_settings['include'] ) );
			}
			else {

				if( 'post' === $this->_settings['post_type'] && isset($this->_settings['categories']) && $categories = $this->_settings['categories'] ){
					unset($query_args['update_post_term_cache']);
					$query_args['category__in'] = array_map( 'absint', $categories );
				}

				if( 'post' === $this->_settings['post_type'] && isset($this->_settings['tags']) && $tags = $this->_settings['tags'] ){
					unset($query_args['update_post_term_cache']);
					$query_args['tag__in'] = array_map( 'absint', $tags );
				}

				if( 'post' !== $this->_settings['post_type'] && isset($this->_settings['all_taxonomies']) && $all_taxonomies = $this->_settings['all_taxonomies'] ){

					unset($query_args['update_post_term_cache']);

					foreach ( $all_taxonomies as $term_id ) {

						$term = get_term( $term_id );

						if( isset($term->taxonomy) ){
							$query_args['tax_query'][] = [
								'taxonomy' => $term->taxonomy,
								'field' => 'term_id',
								'terms' => absint($term_id),
							];
						}
					}

				}

				if( isset($this->_settings['tags']) && $tags = $this->_settings['tags'] ){
					unset($query_args['update_post_term_cache']);
					$query_args['tag__in'] = array_map( 'absint', $tags );
				}

				if( !empty($this->_settings['exclude']) ) {
					$query_args['post__not_in'] = array_map( 'trim', explode( ',', $this->_settings['exclude'] ) );
				}
			}

			// Exclude duplicates
			if( $this->_settings['exclude_duplicates'] !== '' &&
				isset($GLOBALS["rey_exclude_posts"]) &&
				($to_exclude = $GLOBALS["rey_exclude_posts"]) ){
				$query_args['post__not_in'] = isset($query_args['post__not_in']) ? array_merge( $query_args['post__not_in'], $to_exclude ) : $to_exclude;
			}

			if( $this->_settings['add_pagination'] !== '' ){
				$query_args['paged'] = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			}

			$query_args = apply_filters( 'reycore/elementor/posts/query_args', $query_args, $this );

			if ( isset($this->_settings['query_id']) && !empty($this->_settings['query_id']) ) {
				add_action( 'pre_get_posts', [ $this, 'pre_get_posts_query_filter' ] );
			}

			// $this->_query = new WP_Query( $query_args );
			$this->_query = reyCoreHelper()->get_query( $query_args );

			remove_action( 'pre_get_posts', [ $this, 'pre_get_posts_query_filter' ] );

			do_action( 'reycore/elementor/query/query_results', $this->_query, $this );

		}

		/**
		 * Render thumbnail
		 *
		 * @since 1.0.0
		 **/
		public function render_thumbnail()
		{

			if ( 'yes' === $this->_settings['thumb'] && has_post_thumbnail() ): ?>
			<div class="reyEl-bpost-thumb">
				<a class="reyEl-bpost-thumbLink" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<?php
					$image_size = $this->_settings['image_size'] !== 'custom' ? $this->_settings['image_size'] : 'medium_large';
					the_post_thumbnail(  $image_size); ?>
				</a>
			</div>
			<?php endif;
		}

		/**
		 * Render meta
		 *
		 * @since 1.0.0
		 **/
		public function render_meta()
		{

			if( 'yes' === $this->_settings['meta_author'] || 'yes' === $this->_settings['meta_date'] || 'yes' === $this->_settings['meta_comments'] ): ?>
				<div class="rey-postInfo">
				<?php
					if( 'yes' === $this->_settings['meta_author'] ){
						if( function_exists('rey__posted_by') ){
							rey__posted_by();
						}
					}
					if( 'yes' === $this->_settings['meta_date'] ){
						if( function_exists('rey__posted_date') ){
							rey__posted_date();
						}
					}
					if( 'yes' === $this->_settings['meta_comments'] ){
						if( function_exists('rey__comment_count') ){
							rey__comment_count();
						}
					}
					if( function_exists('rey__edit_link') ){
						rey__edit_link();
					}
				?>
				</div>
			<?php endif;
		}

		/**
		 * Render title
		 *
		 * @since 1.0.0
		 **/
		public function render_title()
		{
			if( 'yes' === $this->_settings['title'] ){
				the_title( sprintf( '<h3 class="reyEl-bpost-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' );
			}
		}

		/**
		 * Render excerpt
		 *
		 * @since 1.0.0
		 **/
		public function render_excerpt()
		{
			$settings = $this->_settings;

			if( 'yes' !== $settings['excerpt'] ) {
				return;
			}

			add_filter( 'excerpt_length', function() use ($settings) {
				return $settings['excerpt_length'];
			}, 999 );
			?>

			<div class="reyEl-bpost-content">
				<?php the_excerpt(); ?>
			</div>

			<?php
		}

		/**
		 * Render footer
		 *
		 * @since 1.0.0
		 **/
		public function render_footer()
		{

			if( 'yes' !== $this->_settings['read_more'] ) {
				return;
			}

			?>
			<div class="reyEl-bpost-footer">

				<a class="btn btn-line-active" href="<?php echo esc_url( get_permalink() ) ?>">

					<?php if( $custom_text = $this->_settings['read_more_text'] ){
						echo $custom_text;
					}
					else {
						esc_html_e('CONTINUE READING', 'rey-core');
					} ?>

					<span class="screen-reader-text"> <?php echo get_the_title(); ?></span>
				</a>
				<?php
				if( 'yes' === $this->_settings['read_duration'] ){
					if( function_exists('rey__postDuration') ){
						echo rey__postDuration();
					}
				} ?>
			</div>

			<?php
		}


		public function render_start(){

			reyCoreAssets()->add_styles('rey-blog');
			reyCoreAssets()->add_scripts(['imagesloaded']);

			$skin = $this->_settings['_skin'] ? $this->_settings['_skin'] : 'default';

			$classes = [
				'rey-element',
				'reyEl-bPostGrid',
				'reyEl-bPostGrid--' . $skin,
			];

			if( 'inner' === $skin) {

				if( 'yes' === $this->_settings['inner_image_stretch'] ) {
					$classes['inner_stretch_img'] = '--stretch-image';
				}

				$classes['inner_effect'] = '--inner-effect-' . $this->_settings['inner_hover_effect'];
			}

			if( $this->_settings['per_row'] && $this->_settings['per_row'] < $this->_settings['posts_per_page'] ){
				$classes['masonry'] = '--masonry';
				reyCoreAssets()->add_scripts(['reycore-widget-basic-post-grid-scripts']);
				wp_enqueue_script('masonry');
			}

			if( $this->_settings['carousel'] !== '' ){

				unset($classes['masonry']);

				$classes[] = 'splide';

				if($carousel_id = $this->_settings['carousel_id'] ){
					$classes['carousel_id'] = esc_attr($carousel_id);
				}
			}

			$this->add_render_attribute( 'wrapper', 'class', $classes );

			if( $this->_settings['carousel'] !== '' ):

				reyCoreAssets()->add_scripts(['reycore-widget-basic-post-grid-scripts', 'rey-splide']);
				reyCoreAssets()->add_styles('rey-splide');

				$carousel_config = [
					'slides_to_show' => $this->_settings['slides_to_show'],
					'autoplay' => $this->_settings['autoplay'] !== '',
					'autoplay_speed' => $this->_settings['autoplay_speed'],
					'pause_on_hover' => $this->_settings['pause_on_hover'] !== '',
					'infinite' => $this->_settings['infinite'] !== '',
					'effect' => $this->_settings['effect'],
					'speed' => $this->_settings['speed'],
					'direction' => $this->_settings['direction'],
					'gap' => $this->_settings['gap'],
					'gap_tablet' => $this->_settings['gap_tablet'],
					'gap_mobile' => $this->_settings['gap_mobile'],
					'carousel_padding' => $this->_settings['carousel_padding'],
					'carousel_padding_tablet' => $this->_settings['carousel_padding_tablet'],
					'carousel_padding_mobile' => $this->_settings['carousel_padding_mobile'],
				];

				if( $this->_settings['carousel_pagination'] !== '' ){
					$carousel_config['createPagination'] = [
						'class' => '--circles'
					];
				}

				if( $this->_settings['carousel_arrows'] !== '' ){
					$carousel_config['createArrows'] = [
						'navSelector' => '--basic'
					];
				}

				if( $this->_settings['slides_to_show_tablet'] ){
					$carousel_config['slides_to_show_tablet'] = $this->_settings['slides_to_show_tablet'];
				}

				if( $this->_settings['slides_to_show_mobile'] ){
					$carousel_config['slides_to_show_mobile'] = $this->_settings['slides_to_show_mobile'];
				}

				$this->add_render_attribute( 'wrapper', 'data-carousel-settings', wp_json_encode($carousel_config) );


			endif;

			$GLOBALS["rey_exclude_posts"] = wp_list_pluck( $this->_query->posts, 'ID' );

			?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php

			if( $this->_settings['carousel'] !== '' ){
				echo '<div class="splide__track"><div class="splide__list">';
			}
		}

		public function render_end(){

			if( $this->_settings['carousel'] !== '' ){
				echo '</div></div>';
			}

			echo '</div>';

			$this->render_pagination();


		}

		public function get_classes(){

			$classes = [];

			if( $this->_settings['carousel'] !== '' ){
				$classes[] = 'splide__slide';
			}

			if(
				reycore__config('animate_blog_items') && !\Elementor\Plugin::$instance->editor->is_edit_mode() &&
				$this->_settings['entry_animation'] === 'yes' && $this->_settings['carousel'] !== 'yes') {
					reyCoreAssets()->add_scripts(['scroll-out']);
				return '';
				$classes[] = 'is-animated-entry';
			}

			return implode(' ', $classes);
		}

		/**
		 * Render widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function render() {

			$this->_settings = $this->get_settings_for_display();

			if( class_exists('ReyCore_Elementor_Posts') ){
				$this->posts_archive = new ReyCore_Elementor_Posts( [
					'el_instance' => $this
				], $this->_settings );
			}

			if( $this->posts_archive && $this->posts_archive->lazy_start() ){
				return;
			}

			reyCoreAssets()->add_styles('reycore-widget-basic-post-grid-styles');

			$this->query_posts();

			if ( ! $this->_query->found_posts ) {
				return;
			}

			$this->render_start();

			while ( $this->_query->have_posts() ) : $this->_query->the_post();

				 ?>

				<div class="reyEl-bPostGrid-item <?php echo $this->get_classes(); ?>">
					<?php
						$this->render_thumbnail();
						$this->render_meta();
						$this->render_title();
						$this->render_excerpt();
						$this->render_footer();
					?>
				</div>
			<?php endwhile;
			wp_reset_postdata();

			$this->render_end();

			if( $this->posts_archive ){
				$this->posts_archive->lazy_end();
			}

		}

		function render_pagination(){

			if( $this->_settings['add_pagination'] !== '' && function_exists('rey__pagination') ){

				add_filter('rey/blog_pagination_args', function($args){

					$args['base']    = str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) );
					$args['format']  = '?paged=%#%';
					$args['total']  = $this->_query->max_num_pages;
					$args['current']  = max( 1, get_query_var( 'paged' ) );
					$args['prev_text']  = reycore__arrowSvg(false);
					$args['next_text']  = reycore__arrowSvg();

					return $args;
				});

				rey__pagination();
			}

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
