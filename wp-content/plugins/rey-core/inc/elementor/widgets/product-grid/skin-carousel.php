<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Product_Grid__Carousel') ):

	class ReyCore_Widget_Product_Grid__Carousel extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'carousel';
		}

		public function get_title() {
			return __( 'Carousel', 'rey-core' );
		}

		protected function _register_controls_actions() {
			parent::_register_controls_actions();

			add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_end', [ $this, 'register_carousel_controls' ] );
			add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_end', [ $this, 'register_carousel_styles' ] );
		}

		public function register_carousel_controls( $element ){

			$element->start_injection( [
				'of' => 'per_row',
			] );

			$slides_to_show = range( 1, 10 );
			$slides_to_show = array_combine( $slides_to_show, $slides_to_show );

			$element->add_responsive_control(
				'slides_to_show',
				[
					'label' => __( 'Slides to Show', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' => __( 'Default', 'rey-core' ),
					] + $slides_to_show,
					'condition' => [
						'_skin' => 'carousel',
					],
					'selectors' => [
						'{{WRAPPER}} ul.products' => '--woocommerce-grid-columns: {{VALUE}}',
					],
					'render_type' => 'template',
					'devices' => [ 'desktop', 'tablet', 'mobile' ],
					'desktop_default' => 4,
					'tablet_default' => 3,
					'mobile_default' => 2,
				]
			);

			$element->add_control(
				'disable_desktop',
				[
					'label' => esc_html__( 'Disable on desktop', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'desktop',
					'prefix_class' => '--disable-',
					'condition' => [
						'_skin' => 'carousel',
					],
					'render_type' => 'template',
				]
			);

			$element->add_control(
				'disable_tablet',
				[
					'label' => esc_html__( 'Disable on tablet', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'tablet',
					'prefix_class' => '--disable-',
					'condition' => [
						'_skin' => 'carousel',
					],
					'render_type' => 'template',
				]
			);

			$element->add_control(
				'disable_mobile',
				[
					'label' => esc_html__( 'Disable on mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'mobile',
					'prefix_class' => '--disable-',
					'condition' => [
						'_skin' => 'carousel',
					],
					'render_type' => 'template',
				]
			);

			$element->end_injection();


			$element->start_controls_section(
				'section_carousel_settings',
				[
					'label' => __( 'Carousel Settings', 'rey-core' ),
					'condition' => [
						'_skin' => 'carousel',
					],
				]
			);

			$element->add_control(
				'pause_on_hover',
				[
					'label' => __( 'Pause on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'autoplay_speed',
				[
					'label' => __( 'Autoplay Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 5000,
					'condition' => [
						'autoplay' => 'yes',
					],
				]
			);

			$element->add_responsive_control(
				'infinite',
				[
					'label' => __( 'Infinite Loop', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'yes',
					'options' => [
						'yes' => __( 'Yes', 'rey-core' ),
						'no' => __( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'effect',
				[
					'label' => __( 'Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'slide',
					'options' => [
						'slide' => __( 'Slide', 'rey-core' ),
						'fade' => __( 'Fade', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'speed',
				[
					'label' => __( 'Animation Speed', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 500,
				]
			);

			$element->add_control(
				'direction',
				[
					'label' => __( 'Direction', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'ltr',
					'options' => [
						'ltr' => __( 'Left', 'rey-core' ),
						'rtl' => __( 'Right', 'rey-core' ),
					],
				]
			);

			$element->add_responsive_control(
				'gap',
				[
					'label' => __( 'Gap', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 30,
					'min' => 0,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .reyEl-productGrid ul.products' => '--woocommerce-products-gutter: {{VALUE}}px',
					],
					'render_type' => 'template',
				]
			);

			$element->add_control(
				'delay_init',
				[
					'label' => __( 'Delay Initialization', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 20000,
					'step' => 50,
				]
			);

			$element->add_control(
				'carousel_viewport_offset',
				[
					'label' => esc_html__( 'Container Offset', 'rey-core' ),
					'description' => esc_html__( 'This option will pull the carousel horizontal sides toward the viewport edges. Applies only on desktop and overrides all settings.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'on',
					'separator' => 'before',
					'prefix_class' => '--offset-',
					'render_type' => 'template',
				]
			);

			$element->add_control(
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
						'carousel_viewport_offset!' => '',
					],
					'render_type' => 'template',
				]
			);

			$element->add_responsive_control(
				'carousel_padding',
				[
					'label' => __( 'Horizontal Padding (Offset)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', 'vw' ],
					'allowed_dimensions' => 'horizontal',
					'selectors' => [
						'{{WRAPPER}} .splide__track' => 'padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; ',
					],
					'render_type' => 'template',
				]
			);

			$element->add_responsive_control(
				'carousel_vertical_padding',
				[
					'label' => __( 'Vertical Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'allowed_dimensions' => 'vertical',
					'selectors' => [
						'{{WRAPPER}} .splide__track' => 'padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}}; ',
					],
					'render_type' => 'template',
				]
			);

			// Navigation
			$element->add_control(
				'carousel_arrows',
				[
					'label' => esc_html__( 'Enable Arrows', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'separator' => 'before'
				]
			);

			$element->add_control(
				'carousel_nav_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => esc_html__( 'Did you know you can use "Slider Navigation" element to control this carousel, and place it everywhere? Read below on the Carousel Unique ID option.', 'rey-core' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
					'condition' => [
						'carousel_arrows!' => '',
					],
				]
			);


			$element->add_control(
				'carousel_id',
				[
					'label' => __( 'Carousel Unique ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('carousel-'),
					'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
					'description' => __( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="https://support.reytheme.com/kb/products-grid-element/#adding-custom-navigation" target="_blank">how to connect them</a>.', 'rey-core' ),
					'separator' => 'before'
				]
			);

			$element->add_control(
				'disable_acc_outlines',
				[
					'label' => esc_html__( 'Disable accesibility outlines', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--disable-acc-outlines-',
				]
			);

			$element->end_controls_section();

		}

		function register_carousel_styles( $element ){

			$element->start_controls_section(
				'section_carousel_arrows_styles',
				[
					'label' => __( 'Arrows styles', 'rey-core' ),
					'condition' => [
						'_skin' => 'carousel',
						'carousel_arrows!' => '',
					],
				]
			);

			$element->add_control(
				'carousel_arrows_position',
				[
					'label' => esc_html__( 'Arrows position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'inside',
					'options' => [
						'inside'  => esc_html__( 'Inside (over first/last products)', 'rey-core' ),
						'outside'  => esc_html__( 'Outside', 'rey-core' ),
					],
					'prefix_class' => '--carousel-navPos-'
				]
			);

			$element->add_control(
				'carousel_arrows_show_on_hover',
				[
					'label' => esc_html__( 'Show on hover only', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'prefix_class' => '--show-on-hover-'
				]
			);

			$element->add_control(
				'carousel_arrows_type',
				[
					'label' => esc_html__( 'Arrows Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default', 'rey-core' ),
						'chevron'  => esc_html__( 'Chevron', 'rey-core' ),
						'custom'  => esc_html__( 'Custom Icon', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'carousel_arrows_custom_icon',
				[
					'label' => __( 'Custom Arrow Icon (Right)', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [
						'carousel_arrows_type' => 'custom',
					],
				]
			);

			$element->add_control(
				'carousel_arrows_size',
				[
					'label' => esc_html__( 'Arrows size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 5,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'font-size: {{VALUE}}px',
					],
				]
			);

			$element->add_responsive_control(
				'carousel_arrows_padding',
				[
					'label' => __( 'Arrows Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$element->start_controls_tabs( 'tabs_styles');

				$element->start_controls_tab(
					'tab_default',
					[
						'label' => __( 'Default', 'rey-core' ),
					]
				);

					$element->add_control(
						'carousel_arrows_color',
						[
							'label' => esc_html__( 'Arrows Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'carousel_arrows_bg_color',
						[
							'label' => esc_html__( 'Arrows Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'background-color: {{VALUE}}',
							],
						]
					);

					$element->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'carousel_arrows_border',
							'selector' => '{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg',
							'responsive' => true,
						]
					);

				$element->end_controls_tab();

				$element->start_controls_tab(
					'tab_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$element->add_control(
						'carousel_arrows_color_hover',
						[
							'label' => esc_html__( 'Arrows Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'carousel_arrows_bg_color_hover',
						[
							'label' => esc_html__( 'Arrows Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'carousel_arrows_border_color_hover',
						[
							'label' => esc_html__( 'Arrows Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'border-color: {{VALUE}}',
							],
						]
					);

				$element->end_controls_tab();

			$element->end_controls_tabs();

			$element->add_control(
				'carousel_arrows_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$element->end_controls_section();
		}


		public function loop_start($product_archive)
		{

			wc_set_loop_prop( 'name', 'product_grid_element' );
			wc_set_loop_prop( 'loop', 0 );

			do_action('reycore/woocommerce/loop/before_grid');
			do_action('reycore/woocommerce/loop/before_grid/name=product_grid_element');

			$parent_classes = $product_archive->get_css_classes();

			if( $parent_classes['grid_layout'] === 'rey-wcGrid-metro' ){
				$parent_classes[] = '--prevent-metro';
			}

			$classes = [
				'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
				'--prevent-scattered', // make sure scattered is not applied
				'--prevent-masonry', // make sure masonry is not applied
				'splide__list'
			];

			$attributes = '';
			$carousel_classes = [];

			if( $carousel_id = esc_attr( $this->_settings['carousel_id'] ) ){
				$carousel_classes[] =  $carousel_id;
				$attributes .= sprintf(' data-slider-carousel-id="%s"', $carousel_id);
			}

			printf('<div class="splide %2$s" %3$s><div class="splide__track"><ul class="products %1$s">',
				implode(' ', apply_filters('reycore/woocommerce/product_loop_classes', array_merge( $classes, $parent_classes )) ),
				implode(' ', $carousel_classes ),
				apply_filters('reycore/woocommerce/product_loop_attributes', $attributes, $this->_settings)
			);

		}

		public function loop_end(){
			echo '</ul></div>';

			if( $this->_settings['carousel_arrows'] !== '' ){

				printf('<div class="reyEl-productGrid-carouselNav __arrows-%s">', $this->parent->get_id() );

					$custom_svg_icon = '';

					if( 'custom' === $this->_settings['carousel_arrows_type'] &&
						($custom_icon = $this->_settings['carousel_arrows_custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
						ob_start();
						\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
						$custom_svg_icon = ob_get_clean();
					}

					reycore__svg_arrows([
						'type' => $this->_settings['carousel_arrows_type'],
						'custom_icon' => $custom_svg_icon,
						'attributes' => [
							'left' => 'data-dir="-1"',
							'right' => 'data-dir="+1"',
						]
					]);

				echo '</div>';
			}

			echo '</div>';

			do_action('reycore/woocommerce/loop/after_grid');
			do_action('reycore/woocommerce/loop/after_grid/name=product_grid_element');

		}

		/**
		 * Render widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function render() {

			reyCoreAssets()->add_styles(['reycore-general']);
			reyCoreAssets()->add_scripts( ['reycore-woocommerce', 'reycore-widget-product-grid-scripts'] );

			$this->_settings = $this->parent->get_settings_for_display();

			if( ! class_exists('ReyCore_WooCommerce_ProductArchive') ){
				return;
			}

			$carousel_config = [
				'type' => $this->_settings['effect'] === 'fade' ? 'fade' : 'slide',
				'slides_to_show' => $this->_settings['slides_to_show'] ? $this->_settings['slides_to_show'] : reycore_wc_get_columns('desktop'),
				'slides_to_show_tablet' => $this->_settings['slides_to_show_tablet'] ? $this->_settings['slides_to_show_tablet'] : reycore_wc_get_columns('tablet'),
				'slides_to_show_mobile' => $this->_settings['slides_to_show_mobile'] ? $this->_settings['slides_to_show_mobile'] : reycore_wc_get_columns('mobile'),
				'autoplay' => $this->_settings['autoplay'] === 'yes',
				'autoplaySpeed' => $this->_settings['autoplay_speed'],
				'pause_on_hover' => $this->_settings['pause_on_hover'],
				'infinite' => $this->_settings['infinite'] === 'yes',
				'infinite_tablet' => $this->_settings['infinite_tablet'] === 'yes',
				'infinite_mobile' => $this->_settings['infinite_mobile'] === 'yes',
				'speed' => $this->_settings['speed'],
				'direction' => $this->_settings['direction'],
				'carousel_padding' => $this->_settings['carousel_padding'],
				'carousel_padding_tablet' => $this->_settings['carousel_padding_tablet'],
				'carousel_padding_mobile' => $this->_settings['carousel_padding_mobile'],
				'delayInit' => $this->_settings['delay_init'],
				'gap' => $this->_settings['gap'],
				'gap_tablet' => $this->_settings['gap_tablet'],
				'gap_mobile' => $this->_settings['gap_mobile'],
				'customArrows' => $this->_settings['carousel_arrows'] !== '' ? '.__arrows-' . $this->parent->get_id() : '',
			];

			$args = [
				'name'        => 'product_grid_element',
				'filter_name' => 'product_grid',
				'main_class'  => 'reyEl-productGrid',
				'el_instance' => $this->parent,
				'attributes'  => [
					'data-carousel-settings' => wp_json_encode( apply_filters('reycore/elementor/product_grid/carousel_settings', $carousel_config) )
				]
			];

			$product_archive = new ReyCore_WooCommerce_ProductArchive( $args, $this->_settings );

			if( $product_archive->lazy_start() ){
				return;
			}

			reyCoreAssets()->add_styles(['reycore-widget-product-grid-styles', 'rey-splide']);
			reyCoreAssets()->add_scripts( ['rey-splide'] );

			if ( $product_archive->get_query_results() ) {

				$product_archive->render_start();

					$this->loop_start($product_archive);

						$product_archive->render_products();

					$this->loop_end();

				$product_archive->render_end();
			}

			else {
				wc_get_template( 'loop/no-products-found.php' );
			}

			$product_archive->lazy_end();

		}

	}
endif;
