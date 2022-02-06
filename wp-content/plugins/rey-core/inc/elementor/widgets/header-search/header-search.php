<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Header_Search')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Search extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-header-search';
	}

	public function get_title() {
		return __( 'Search Box - Header', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-header/#search-box';
	}

	public function get_style_depends() {
		return $this->get_skin_assets('style');
	}

	public function rey_get_script_depends() {
		return $this->get_skin_assets('script');
	}

	function get_skin_assets($type = ''){

		$assets = [];

		if (
			! \Elementor\Plugin::$instance->editor->is_edit_mode() &&
			! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {

			if( $settings = $this->get_settings_for_display() ){

				$search_style = isset($settings['search_style']) && !empty($settings['search_style']) ?
					$settings['search_style'] :
					$this->get_default_search_style();

				$assets = apply_filters('reycore/elementor/header-search/assets', $assets, $search_style, $type);
			}
		}

		return $assets;
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
			'notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'If you don\'t want to show this element, simply remove it from its section.', 'rey-core' ),
				'content_classes' => 'rey-raw-html',
			]
		);

		$cst_link_query['autofocus[section]'] = 'header_search_options';
		$cst_link = add_query_arg( $cst_link_query, admin_url( 'customize.php' ) );

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'Search options can be edited into the <a href="%1$s" target="_blank">Customizer Panel > Header > Search</a>, but you can also override those settings below.', 'rey-core' ), $cst_link ),
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
			'search_style',
			[
				'label' => __( 'Search Panel Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => $this->get_default_search_style(),
				'options' => [
					'wide' => esc_html__( 'Wide Panel', 'rey-core' ),
					'side' => esc_html__( 'Side Panel', 'rey-core' ),
				],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'search_complementary',
			[
				'label' => __( 'Suggestions content type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( '- Select -', 'rey-core' ),
					'menu' => esc_html__( 'Menu', 'rey-core' ),
					'keywords' => esc_html__( 'Keyword suggestions', 'rey-core' ),
				],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$get_all_menus = reyCoreHelper()->get_all_menus();

		$this->add_control(
			'search_menu_source',
			[
				'label' => __( 'Menu Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $get_all_menus,
				'default' => '',
				'condition' => [
					'custom!' => '',
					'search_complementary' => 'menu',
				],
			]
		);

		$this->add_control(
			'keywords',
			[
				'label' => __( 'Keywords', 'rey-core' ),
				'description' => __( 'Add keyword suggestions, separated by comma ",".', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => __( 'eg: t-shirt, pants, trousers', 'rey-core' ),
				'condition' => [
					'custom!' => '',
					'search_complementary' => 'keywords',
				],
			]
		);

		$this->add_control(
			'search_icon_text',
			[
				'label' => esc_html__( 'Custom Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: Search', 'rey-core' ),
				'condition' => [
					'custom!' => '',
					'search_style' => ['wide', 'side'],
				],
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch-toggle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => esc_html__( 'Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch-toggle:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 5,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch .__icon' => '--icon-size: {{VALUE}}px;',
				],
			]
		);

		$this->add_control(
			'search_icon',
			[
				'label' => esc_html__( 'Icon', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'Default', 'rey-core' ),
					'custom' => esc_html__( '- Custom Icon -', 'rey-core' ),
					'disabled' => esc_html__( '- No Icon -', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'custom_icon',
			[
				'label' => __( 'Custom Icon', 'elementor' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'search_icon' => 'custom',
				],

			]
		);


		$this->add_control(
			'text_position',
			[
				'label' => __( 'Text Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Default', 'rey-core' ),
					'before' => esc_html__( 'Before', 'rey-core' ),
					'after' => esc_html__( 'After', 'rey-core' ),
					'under' => esc_html__( 'Under', 'rey-core' ),
				],
				'condition' => [
					'search_icon_text!' => '',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'text_distance',
			[
				'label' => esc_html__( 'Text Distance', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--text-distance: {{VALUE}}px',
				],
				'condition' => [
					'search_icon_text!' => '',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'custom_text_typo',
				'label' => esc_html__( 'Text typo', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-headerSearch-text',
				'condition' => [
					'search_icon_text!' => '',
				]
			]
		);

		$this->end_controls_section();

		/* ------------------------------------ PANEL ------------------------------------ */

		$this->start_controls_section(
			'section_panel_styles',
			[
				'label' => __( 'Panel Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'text_color',
				[
					'label' => __( 'Panel Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						':root' => '--search-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => __( 'Panel Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						':root' => '--search-bg-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();
	}

	public function get_default_search_style(){

		if( function_exists('reycore_wc__get_header_search_args') ){
			return reycore_wc__get_header_search_args('search_style');
		}

		return get_theme_mod('header_search_style', 'wide');
	}

	function set_options( $vars ){

		$settings = $this->get_settings_for_display();

		if( $settings['custom'] ){
			$vars['search_complementary'] = $settings['search_complementary'];
			$vars['search_menu_source'] = $settings['search_menu_source'];
			$vars['keywords'] = $settings['keywords'];
			$vars['search_style'] = !is_null($settings['search_style']) ? $settings['search_style'] : $this->get_default_search_style();
			$vars['search__before_content'] = $settings['search_icon_text'];
		}

		// deprecated
		$reverse__legacy = false;
		if( isset($settings['custom_text_reverse']) ){
			$reverse__legacy = $settings['custom_text_reverse'] !== '';
		}

		if( isset($vars['classes']) ){

			$text_position = $reverse__legacy ? 'after' : 'before';

			if( isset($settings['text_position']) && $settings['text_position']  ){
				$text_position = $settings['text_position'];
			}

			$vars['classes'] .= ' --tp-' . $text_position;
		}

		return $vars;
	}

	function set_icon( $icon_html ){

		$settings = $this->get_settings_for_display();

		if( $settings['search_icon'] === '' ){
			return $icon_html;
		}

		if( $settings['search_icon'] === 'disabled' ){
			return '';
		}

		else if( $settings['search_icon'] === 'custom' ) {

			if( ($custom_icon = $settings['custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
				ob_start();
				\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => 'icon-search' ] );
				return ob_get_clean();
			}
		}

		return $icon_html;
	}

	protected function render() {

		reyCoreAssets()->add_styles(['reycore-header-search-top', 'reycore-header-search']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );
		reyCoreAssets()->add_scripts(['reycore-header-search']);

		// force enable
		add_filter('theme_mod_header_enable_search', '__return_true', 10);
		add_filter('reycore/woocommerce/header/search_icon', [$this, 'set_icon']);

		$settings = $this->get_settings_for_display();

		$search_style = !is_null($settings['search_style']) ? $settings['search_style'] : $this->get_default_search_style();

		add_filter('reycore/header/search_params', [$this, 'set_options'], 10);

		// Wide & Side panels
		if( in_array($search_style, ['wide', 'side']) ){
			reycore__get_template_part('template-parts/header/search-toggle');
			// load panel
			add_action('rey/after_site_wrapper', 'reycore__header__add_search_panel');
		}
		// Default simple form
		elseif($search_style === 'button') {
			get_template_part('template-parts/header/search-button');
			reyCoreAssets()->add_styles('rey-header-search');
			reyCoreAssets()->add_scripts('rey-searchform');
		}

		do_action('reycore/elementor/header-search/template', $settings, $search_style);
		// settings not applying on the panel
		remove_filter('reycore/woocommerce/header/search_icon', [$this, 'set_icon']);
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
