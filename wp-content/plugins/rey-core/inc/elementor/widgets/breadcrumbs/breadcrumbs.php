<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if(!class_exists('ReyCore_Widget_Breadcrumbs')):
	/**
	 *
	 * Elementor widget.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Widget_Breadcrumbs extends \Elementor\Widget_Base {

		public function get_name() {
			return 'reycore-breadcrumbs';
		}

		public function get_title() {
			return __( 'Breadcrumbs', 'rey-core' );
		}

		public function get_icon() {
			return 'eicon-product-breadcrumbs';
		}

		public function get_categories() {
			return [ 'rey-theme' ];
		}

		public function get_custom_help_url() {
			return 'https://support.reytheme.com/kb/rey-elements/#breadcrumbs';
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
				'section_settings',
				[
					'label' => __( 'Settings', 'rey-core' ),
				]
			);

			$this->add_control(
				'delimiter',
				[
					'label' => __( 'Custom Delimiter', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( '', 'rey-core' ),
					'placeholder' => __( 'eg: /', 'rey-core' ),
				]
			);

			$this->add_control(
				'add_home',
				[
					'label' => __( 'Add Home?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'home_url',
				[
					'label' => esc_html__( 'Home URL', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => [
						'add_home!' => '',
					],
				]
			);

			$this->add_control(
				'home',
				[
					'label' => __( 'Home Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => __( 'Home', 'rey-core' ),
					'condition' => [
						'add_home' => ['yes'],
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


			$this->end_controls_section();

			$this->start_controls_section(
				'section_style',
				[
					'label' => __( 'Style', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typography',
					'selector' => '{{WRAPPER}} .rey-breadcrumbs',
				]
			);

			$this->add_control(
				'text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}}' => 'color: {{VALUE}};',
					],
				]
			);

			$this->start_controls_tabs( 'tabs_breadcrumbs_style' );

			$this->start_controls_tab(
				'tab_color_normal',
				[
					'label' => __( 'Normal', 'rey-core' ),
				]
			);

			$this->add_control(
				'link_color',
				[
					'label' => __( 'Link Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} a' => 'color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_color_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

			$this->add_control(
				'link_hover_color',
				[
					'label' => __( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} a:hover' => 'color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->end_controls_section();
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

			$settings = $this->get_settings_for_display();

			$this->add_render_attribute( 'wrapper', 'class', 'rey-element' );
			$this->add_render_attribute( 'wrapper', 'class', 'reyEl-breadcrumbs' );

			?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

				<?php
				/**
				 * WooCommerce Breadcrumbs
				 * @from woocommerce/includes/class-wc-breadcrumb.php
				 */

				$delimiter = '&#8250;';

				if( $settings['delimiter'] != '' ){
					$delimiter = $settings['delimiter'];
				}

				$args = [
					'delimiter'   => '<span class="rey-breadcrumbs-del">'. $delimiter .'</span>',
					'wrap_before' => '<nav class="rey-breadcrumbs">',
					'wrap_after'  => '</nav>',
					'before'      => '<div class="rey-breadcrumbs-item">',
					'after'       => '</div>',
					'home'       => 'yes' == $settings['add_home'] ? $settings['home'] : '',
				];

				$home_url = home_url();
				$custom_home_url = '';

				if( 'yes' == $settings['add_home'] && $custom_home_url = $settings['home_url'] ){
					$home_url = $custom_home_url;
				}

				if( function_exists('woocommerce_breadcrumb') ) {

					if( $custom_home_url ){
						add_filter('woocommerce_breadcrumb_home_url', function() use ($custom_home_url) {
							return $custom_home_url;
						});
					}

					add_filter('woocommerce_breadcrumb_defaults', function() use ($args) {
						return $args;
					});

					woocommerce_breadcrumb();
				}

				else {
					$breadcrumbs = new ReyCore_WC_Breadcrumb();

					if ( $args['home'] ) {
						$breadcrumbs->add_crumb( $args['home'], apply_filters( 'woocommerce_breadcrumb_home_url', $home_url ) );
					}

					$args['breadcrumb'] = $breadcrumbs->generate();

					/**
					 * WooCommerce Breadcrumb hook
					 *
					 * @hooked WC_Structured_Data::generate_breadcrumblist_data() - 10
					 */
					do_action( 'woocommerce_breadcrumb', $breadcrumbs, $args );

					if ( ! empty( $args['breadcrumb'] ) ) {

						echo $args['wrap_before'];

						foreach ( $args['breadcrumb'] as $key => $crumb ) {

							echo $args['before'];

							if ( ! empty( $crumb[1] ) && sizeof( $args['breadcrumb'] ) !== $key + 1 ) {
								echo '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>';
							} else {
								echo esc_html( $crumb[0] );
							}

							echo $args['after'];

							if ( sizeof( $args['breadcrumb'] ) !== $key + 1 ) {
								echo $args['delimiter'];
							}
						}

						echo $args['wrap_after'];
					}
				}
				?>
			</div>
			<!-- .reyEl-breadcrumbs -->
			<?php
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
