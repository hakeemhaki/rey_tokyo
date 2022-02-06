<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Header_Account') ):
/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Account extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-account';
	}

	public function get_title() {
		return __( 'Account - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-select';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce', 'reycore-wc-header-account-panel', 'reycore-wc-header-wishlist', 'reycore-wishlist', 'wp-util' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#account';
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

		$cst_link_query['autofocus[section]'] = 'header_account_options';
		$cst_link = add_query_arg( $cst_link_query, admin_url( 'customize.php' ) );

		$this->add_control(
			'notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'If you don\'t want to show this element, simply remove it from its section.', 'rey-core' ),
				'content_classes' => 'rey-raw-html',
			]
		);

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'Account panel options can be edited into the <a href="%1$s" target="_blank">Customizer Panel > Header > Account</a>, but you can also override those settings below.', 'rey-core' ), $cst_link ),
				'content_classes' => 'rey-raw-html',
				'condition' => [
					'custom' => [''],
				],
			]
		);


		$this->add_control(
			'custom',
			[
				'label' => __( 'Override settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'button_type',
			[
				'label' => __( 'Button Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( '- Inherit -', 'rey-core' ),
					'text'  => __( 'Text', 'rey-core' ),
					'icon'  => __( 'Icon', 'rey-core' ),
					'both_before' => esc_html__( 'Text & Icon Before', 'rey-core' ),
					'both_after' => esc_html__( 'Text & Icon After', 'rey-core' ),
					'both_above' => esc_html__( 'Text & Icon Above', 'rey-core' ),
				],
				'condition' => [
					'custom!' => '',
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => __( 'Button text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'ACCOUNT', 'rey-core' ),
				'placeholder' => __( 'eg: ACCOUNT', 'rey-core' ),
				'condition' => [
					'custom!' => '',
					'button_type!' => 'icon',
				],
			]
		);

		$this->add_control(
			'button_text_logged_in',
			[
				'label' => __( 'Button text (Logged in)', 'rey-core' ),
				'description' => esc_html__( 'Optional. Text to display when user is logged in.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'eg: SIGN OUT', 'rey-core' ),
				'condition' => [
					'custom!' => '',
					'button_type!' => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_type',
			[
				'label' => esc_html__( 'Icon Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'rey-icon-user',
				'options' => [
					'rey-icon-user' => esc_html__( 'User Icon', 'rey-core' ),
					'reycore-icon-heart' => esc_html__( 'Heart Icon', 'rey-core' ),
					'custom' => esc_html__( '- Custom Icon -', 'rey-core' ),
				],
				'condition' => [
					'custom!' => '',
					'button_type!' => 'text',
				],
			]
		);

		$this->add_control(
			'custom_icon',
			[
				'label' => __( 'Custom Icon', 'elementor' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'custom!' => '',
					'icon_type' => 'custom',
				],

			]
		);

		$this->add_control(
			'login_title',
			[
			   'label' => esc_html__( 'Login/Register settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'custom!' => '',
				],
			]
		);

		$this->add_control(
			'forms_enable',
			[
				'label' => esc_html__( 'Enable Forms/Account Menu', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'custom!' => '',
				],
			]
		);

		$this->add_control(
			'redirect_type',
			[
				'label' => esc_html__( 'Action on success', 'rey-core' ),
				'description' => esc_html__( 'Select the action to make after successfull registration or login.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'load_menu',
				'options' => [
					'load_menu' => esc_html__( 'Show "My Account Menu"', 'rey-core' ),
					'refresh' => esc_html__( 'Refresh same page', 'rey-core' ),
					'myaccount' => esc_html__( 'Go to My Account', 'rey-core' ),
					'url' => esc_html__( 'Go to custom URL', 'rey-core' ),
				],
				'condition' => [
					'custom!' => '',
					'forms_enable!' => '',
				],
			]
		);

		$this->add_control(
			'redirect_url',
			[
				'label' => esc_html__( 'Redirect URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: ', 'rey-core' ) . get_site_url(),
				'condition' => [
					'custom!' => '',
					'redirect_type' => 'url',
					'forms_enable!' => '',
				],
			]
		);


		$this->add_control(
			'wishlist_title',
			[
			   'label' => esc_html__( 'Wishlist settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'custom!' => '',
				],
			]
		);

		$this->add_control(
			'wishlist',
			[
				'label' => __( 'Enable Wishlist', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'custom!' => '',
				],
			]
		);

		$this->add_control(
			'counter',
			[
				'label' => __( 'Wishlist Counter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'custom!' => '',
					'wishlist!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--icon-size: {{VALUE}}px',
				],
			]
		);

		$this->add_control(
			'icon_distance',
			[
				'label' => esc_html__( 'Icon Distance', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--icon-distance: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerAccount-btnIcon' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerAccount-btnText' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'hover_text_color',
			[
				'label' => __( 'Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerIcon-btn:hover, {{WRAPPER}} .rey-headerIcon-btn:hover .rey-headerAccount-btnText, {{WRAPPER}} .rey-headerIcon-btn:hover .rey-headerAccount-btnIcon' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .rey-headerAccount-btnText',
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_panel_styles',
			[
				'label' => __( 'Panel Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'products_layout',
				[
					'label' => esc_html__( 'Products layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'grid',
					'options' => [
						'grid'  => esc_html__( 'Grid', 'rey-core' ),
						'list'  => esc_html__( 'List', 'rey-core' ),
					],
				]
			);

		$this->end_controls_section();
	}

	function set_options( $vars ){

		$settings = $this->get_settings_for_display();

		$vars['enabled'] = true;

		if( $settings['custom'] ){

			if( $button_type = $settings['button_type'] ){
				$vars['button_type'] = $button_type;
			}

			$vars['button_text'] = $settings['button_text'];
			$vars['button_text_logged_in'] = $settings['button_text_logged_in'];
			$vars['icon_type'] = $settings['icon_type'];
			$vars['wishlist'] = $settings['wishlist'];
			$vars['counter'] = $settings['counter'];
			$vars['forms'] = $settings['forms_enable'];
			$vars['login_register_redirect'] = $settings['redirect_type'];
			$vars['login_register_redirect_url'] = $settings['redirect_url'];
			$vars['wishlist_prod_layout'] = $settings['products_layout'];
		}

		return $vars;
	}

	function set_icon( $icon_html ){

		$settings = $this->get_settings_for_display();

		if( $settings['icon_type'] === 'custom' ) {
			if( ($custom_icon = $settings['custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
				ob_start();
				\Elementor\Icons_Manager::render_icon( $custom_icon );
				return ob_get_clean();
			}
		}

		return $icon_html;
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

		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		add_filter('rey/header/account_params', [$this, 'set_options'], 10);
		add_filter('reycore/woocommerce/header/account_icon', [$this, 'set_icon']);
		reycore__get_template_part('template-parts/woocommerce/header-account');
		// load panel markup
		add_action('rey/after_site_wrapper', 'reycore_wc__add_account_panel');
		remove_filter('reycore/woocommerce/header/account_icon', [$this, 'set_icon']);

		reyCoreAssets()->add_styles(['rey-wc-header-account-panel-top', 'rey-wc-header-account-panel', 'rey-wc-header-wishlist']);
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
