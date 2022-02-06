<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Menu')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Menu extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-menu';
	}

	public function get_title() {
		return __( 'Menu Navigation', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-menu-toggle';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-menu-scripts' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#menu-navigation';
	}

	public function on_export($element)
    {
        unset(
			$element['settings']['menu_id'],
			$element['settings']['pcat_categories']
        );

        return $element;
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Menu__Product_Categories( $this ) );
		$this->add_skin( new ReyCore_Widget_Menu__Custom_Items( $this ) );
		$this->add_skin( new ReyCore_Widget_Menu__Account_Menu( $this ) );
		$this->add_skin( new ReyCore_Widget_Menu__Product_tags( $this ) );
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

		$get_all_menus = reyCoreHelper()->get_all_menus();

		$this->add_control(
			'menu_id',
			[
				'label' => __( 'Select Menu', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $get_all_menus,
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'menu_depth',
			[
				'label' => __( 'Menu Depth', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'step' => 1,
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_settings',
			[
				'label' => __( 'Title', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'Enter your title', 'rey-core' ),
				'default' => '',
			]
		);

		$this->add_control(
			'title_size',
			[
				'label' => __( 'Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'rey-core' ),
					'small' => __( 'Small', 'rey-core' ),
					'medium' => __( 'Medium', 'rey-core' ),
					'large' => __( 'Large', 'rey-core' ),
					'xl' => __( 'XL', 'rey-core' ),
					'xxl' => __( 'XXL', 'rey-core' ),
				],
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => __( 'HTML Tag', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
				],
				'default' => 'h2',
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'hide_title',
			[
				'label' => esc_html__( 'Hide Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => '',
				'tablet_default' => '',
				'mobile_default' => '',
				'condition' => [
					'title!' => '',
				],
				'return_value' => 'hide',
				'prefix_class' => '--title%s-'
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_dd_settings',
			[
				'label' => __( 'Drop-down', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'title!' => '',
					// 'hide_title' => '',
				],
			]
		);

		$this->add_control(
			'dd_menu',
			[
				'label' => esc_html__( 'Drop-down Menu', 'rey-core' ),
				'description' => esc_html__( 'This option will force the menu to act as drop-down.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'dd_menu_mobile_only',
			[
				'label' => esc_html__( 'Drop-down Menu - Mobiles only', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'title!' => '',
					'dd_menu!' => '',
				],
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Menu Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'direction',
			[
				'label' => __( 'Menu Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => [
					'vertical'  => __( 'Vertical', 'rey-core' ),
					'horizontal'  => __( 'Horizontal', 'rey-core' ),
				],
				'prefix_class' => 'reyEl-menu--'
			]
		);

		$this->add_control(
			'vertical_mobile',
			[
				'label' => __( 'Force vertical on mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => '--vertical-xs',
				'default' => '',
				'prefix_class' => '',
				'condition' => [
					'direction' => ['horizontal'],
				],
			]
		);

		$this->add_responsive_control(
			'halign',
			[
				'label' => __( 'Horizontal Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'condition' => [
					'direction' => ['horizontal'],
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-menu-nav' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __( 'Columns', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 4,
				'step' => 1,
				'condition' => [
					'direction' => 'vertical',
				],
				'prefix_class' => 'reyEl-menu--cols-%s',
				'selectors' => [
					'{{WRAPPER}}.reyEl-menu--vertical .reyEl-menu-nav .menu-item' => '-ms-flex-preferred-size: calc(100% / {{VALUE}}); flex-basis: calc(100% / {{VALUE}});',
					'{{WRAPPER}}.reyEl-menu--vertical .reyEl-menu-nav' => '--menu-cols:{{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .reyEl-menu-nav .menu-item > a',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Text Alignment', 'rey-core' ),
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

		$this->start_controls_tabs( 'tabs_styles');

			$this->start_controls_tab(
				'tab_default',
				[
					'label' => __( 'Default', 'rey-core' ),
				]
			);

				$this->add_responsive_control(
					'text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-menu-nav .menu-item > a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-menu-nav .menu-item > a' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'border',
						'selector' => '{{WRAPPER}} .reyEl-menu-nav .menu-item > a',
						'responsive' => true,
						// 'separator' => 'before',
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

				$this->add_responsive_control(
					'text_color_hover',
					[
						'label' => __( 'Hover Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-menu-nav .menu-item:hover > a, {{WRAPPER}} .reyEl-menu-nav .menu-item:hover > a, {{WRAPPER}} .reyEl-menu-nav .menu-item.current-menu-item > a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'hover_bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-menu-nav .menu-item:hover > a, {{WRAPPER}} .reyEl-menu-nav .menu-item:hover > a, {{WRAPPER}} .reyEl-menu-nav .menu-item.current-menu-item > a' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'hover_border_color',
					[
						'label' => __( 'Border Color', 'elementor' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'condition' => [
							'border_border!' => '',
						],
						'selectors' => [
							'{{WRAPPER}} .reyEl-menu-nav .menu-item:hover > a, {{WRAPPER}} .reyEl-menu-nav .menu-item > a:hover, {{WRAPPER}} .reyEl-menu-nav .menu-item.current-menu-item > a' => 'border-color: {{VALUE}};',
							'{{WRAPPER}} .reyEl-menu-nav .menu-item:focus > a, {{WRAPPER}} .reyEl-menu-nav .menu-item > a:focus' => 'border-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'distance',
			[
			   'label' => __( 'Distance', 'rey-core' ),
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
						'max' => 5.0,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => .2,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--distance: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'items_margin',
			[
				'label' => __( 'Items Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .reyEl-menu-nav > .menu-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'items_padding',
			[
				'label' => __( 'Items Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .reyEl-menu-nav .menu-item > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'hover_style',
			[
				'label' => __( 'Hover Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'  => __( 'None', 'rey-core' ),
					'ulr'  => __( 'Left to Right Underline', 'rey-core' ),
					'ulr --thinner'  => __( 'Left to Right Underline (thinner)', 'rey-core' ),
					'ut'  => __( 'Left to Right Thick Underline', 'rey-core' ),
					'ut2'  => __( 'Left to Right Background', 'rey-core' ),
					'ub'  => __( 'Bottom Underline', 'rey-core' ),
					'sc'  => __( 'Scale on hover', 'rey-core' ),
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'deco_color',
			[
				'label' => esc_html__( 'Menu deco. color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-menu-nav .menu-item > a:after' => 'color: {{VALUE}}',
				],
				'condition' => [
					'hover_style!' => 'none',
				],
			]
		);

		$this->end_controls_section();

		/* ------------------------------------ SUBMENU ------------------------------------ */

		$this->start_controls_section(
			'section_vertical_submenu',
			[
				'label' => __( 'Vertical Sub-menu Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin' => '',
					'menu_depth!' => 1,
					'direction' => 'vertical',
					'columns' => 1,
					'use_css_columns' => '',
				],
			]
		);

			$this->add_control(
				'submenus_layout',
				[
					'label' => esc_html__( 'Submenus Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default', 'rey-core' ),
						'h'  => esc_html__( 'Slide Horizontally', 'rey-core' ),
						'v'  => esc_html__( 'Slide Vertically', 'rey-core' ),
					],
				]
			);

			$this->add_responsive_control(
				'submenus_start_layout',
				[
					'label' => esc_html__( 'Submenus side distance', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-navEl' => '--submenus-start-distance: {{VALUE}}px',
					],
					'condition' => [
						'submenus_layout!' => '',
					],
				]
			);

			$this->add_control(
				'click_behaviour',
				[
					'label' => esc_html__( 'Click Behaviour', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					// 'label_block' => true,
					'default' => 'open',
					'options' => [
						'open'  => esc_html__( 'Open Sub-menus', 'rey-core' ),
						'link'  => esc_html__( 'Go to Link', 'rey-core' ),
					],
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);

			$this->add_control(
				'nav_indicator',
				[
					'label' => esc_html__( 'Submenus Indicators', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'None', 'rey-core' ),
						'chevron'  => esc_html__( 'Chevron', 'rey-core' ),
						'arrow'  => esc_html__( 'Arrow', 'rey-core' ),
						'plus'  => esc_html__( 'Plus', 'rey-core' ),
					],
					'separator' => 'before',
					'condition' => [
						'submenus_layout!' => '',
					],
				]
			);

			$this->add_responsive_control(
				'nav_indicator_size',
				[
					'label' => esc_html__( 'Indicator Size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-navEl' => '--indicator-size: {{VALUE}}px',
					],
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);


			$this->add_responsive_control(
				'nav_indicator_width',
				[
					'label' => esc_html__( 'Indicator Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 0,
					'max' => 10,
					'step' => 0.1,
					'selectors' => [
						'{{WRAPPER}} .rey-navEl' => '--indicator-width: {{VALUE}}',
					],
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);

			$this->add_control(
				'nav_indicator_color',
				[
					'label' => esc_html__( 'Indicator Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-navEl .--submenu-indicator' => 'color: {{VALUE}}',
					],
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);

			$this->add_control(
				'nav_indicator_bg_color',
				[
					'label' => esc_html__( 'Indicator Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-navEl .--submenu-indicator' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);

			$this->add_control(
				'nav_indicator_color_active',
				[
					'label' => esc_html__( 'Indicator Color (Active)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-navEl .current-menu-item > a .--submenu-indicator' => 'color: {{VALUE}}',
					],
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);

			$this->add_control(
				'nav_indicator_bg_color_active',
				[
					'label' => esc_html__( 'Indicator Background Color (Active)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-navEl .current-menu-item > a .--submenu-indicator' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'nav_indicator_border',
					'selector' => '{{WRAPPER}} .rey-navEl .--submenu-indicator',
					'condition' => [
						'submenus_layout!' => '',
						'nav_indicator!' => '',
					],
				]
			);


		$this->end_controls_section();

		/* ------------------------------------ TITLE STYLES ------------------------------------ */

		$this->start_controls_section(
			'section_title_styles',
			[
				'label' => __( 'Title Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Color', 'elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-menuTitle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .reyEl-menuTitle',
			]
		);

		$this->add_responsive_control(
			'title_margin_bottom',
			[
				'label' => esc_html__( 'Margin Bottom', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-menuTitle' => 'margin-bottom: {{VALUE}}px;',
					// '{{WRAPPER}} .reyEl-menu:not(.--dd-menu) .reyEl-menuTitle' => 'margin-bottom: {{VALUE}}px;',
					// '{{WRAPPER}} .reyEl-menuTitle.--toggled' => 'margin-bottom: {{VALUE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'title_border',
			[
				'label' => esc_html__( 'Add title bottom border', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => '',
				'tablet_default' => '',
				'mobile_default' => 'block',
				'selectors'   => [
					'{{WRAPPER}} .reyEl-menuTitle:after' => 'display:{{VALUE}};',
				],
				'label_on' => esc_html__( 'Show', 'rey-core' ),
				'label_off' => esc_html__( 'Hide', 'rey-core' ),
				'return_value' => 'block',
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label' => __( 'Ttile Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .reyEl-menuTitle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* ------------------------------------ TITLE / DROPDOWN STYLES ------------------------------------ */

		$this->start_controls_section(
			'section_dropdown_styles',
			[
				'label' => __( 'Drop Down Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'title!' => '',
					'dd_menu!' => '',
				],
			]
		);

		$this->add_control(
			'floating_drop_down',
			[
				'label' => esc_html__( 'Floating menu', 'rey-core' ),
				'description' => esc_html__( 'Will float over the content below.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'floating_drop_down_bg',
			[
				'label' => esc_html__( 'Background color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .--dd-menu.--floating .reyEl-menu-navWrapper' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .--dd-menu.--floating-mobile .reyEl-menu-navWrapper' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		/* ------------------------------------ ADVANCED STYLES ------------------------------------ */

		$this->start_controls_section(
			'section_advanced_styles',
			[
				'label' => __( 'Advanced Style Settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'use_css_columns',
				[
					'label' => esc_html__( 'Use CSS Columns', 'rey-core' ),
					'description' => esc_html__( 'When having a hierarchical menu on a multi-columns layout, this option can help fixing the gaps.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'columns!' => '1',
						'columns!' => '',
					],
				]
			);


			$this->add_control(
				'icons_visibility',
				[
					'label' => esc_html__( 'Show Icon/Image', 'rey-core' ),
					'description' => sprintf( _x( 'First make sure to add an icon or image to the menu item in Appearance > Menus (<a href="%s" target="_blank">example</a>).', 'Elementor control description', 'rey-core' ), 'https://d.pr/i/nVDcLX' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'separator' => 'before'
				]
			);

			$this->add_responsive_control(
				'icons_size',
				[
					'label' => esc_html__( 'Icon/Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 5,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} a[data-has-icon]' => '--icon-size: {{VALUE}}px',
					],
					'condition' => [
						'icons_visibility' => 'yes',
					],
				]
			);

			$this->add_control(
				'icons_distance',
				[
					'label' => esc_html__( 'Icon/Image Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} a[data-has-icon]' => '--icon-distance: {{VALUE}}px',
					],
					'condition' => [
						'icons_visibility' => 'yes',
					],
				]
			);

			$this->add_control(
				'icons_position',
				[
					'label' => esc_html__( 'Icon/Image Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'start',
					'options' => [
						'start' => __( 'Start', 'rey-core' ),
						'end' => __( 'End', 'rey-core' ),
						'above' => __( 'Above', 'rey-core' ),
					],
					'prefix_class' => '--icons-',
					'condition' => [
						'icons_visibility' => 'yes',
					],
				]
			);


		$this->end_controls_section();


	}

	public function render_start($settings)
	{
		$classes = [
			'rey-element',
			'reyEl-menu',
		];

		if( $settings['dd_menu'] === 'yes') {

			$classes[] = '--dd-menu';

			if( $settings['floating_drop_down'] === 'yes') {
				$classes['floating'] = '--floating';
			}

			if( $settings['dd_menu_mobile_only'] === 'yes') {
				$classes[] = '--dd-menu--mobiles';

				if( $settings['floating_drop_down'] === 'yes') {
					$classes['floating'] = '--floating-mobile';
				}
			}
		}

		if( $settings['use_css_columns'] === 'yes') {
			$classes[] = '--css-cols';
		}

		if(
			absint($settings['menu_depth']) !== 1 &&
			($submenus_layout = $settings['submenus_layout']) &&
			$settings['direction'] === 'vertical' &&
			absint($settings['columns']) === 1 &&
			$settings['use_css_columns'] === ''
			) {

			$classes[] = '--submenus-' . $submenus_layout;

			$this->add_render_attribute( 'wrapper', 'data-vsubmenus', $submenus_layout );
			$this->add_render_attribute( 'wrapper', 'data-click', $settings['click_behaviour'] );

			if( $indicator_type = $settings['nav_indicator'] ){
				$this->add_render_attribute( 'wrapper', 'data-indicator', $indicator_type );
			}
		}

		if( $settings['icons_visibility'] !== 'yes') {
			add_filter('reycore/menu_nav/support_icons', '__return_false');
		}


		$this->add_render_attribute( 'wrapper', 'class', $classes );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php
	}

	public function render_end()
	{
		?>
		</div>
		<?php

		remove_filter('reycore/menu_nav/support_icons', '__return_false');
	}

	public function render_title($settings){

		if( !($title = $settings['title']) ){
			return;
		}

		printf('<%2$s class="reyEl-menuTitle reyEl-menuTitle--%3$s"><span>%1$s</span>%4$s</%2$s>',
			$title,
			$settings['title_tag'],
			$settings['title_size'],
			reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']),
			$this->get_id()
		);
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

		reyCoreAssets()->add_styles(['reycore-widget-menu-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$settings = $this->get_settings_for_display();
		$this->render_start($settings);
		$this->render_title($settings);

		if( is_nav_menu($settings['menu_id']) ):
			echo '<div class="reyEl-menu-navWrapper">';
			wp_nav_menu([
				'menu'        => $settings['menu_id'],
				'container'   => '',
				'menu_class'   => 'reyEl-menu-nav rey-navEl --menuHover-' . $settings['hover_style'],
				'items_wrap'  => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'depth' => $settings['menu_depth'],
				'walker' => new ReyCore_Walker_Nav_Menu
			]);
			echo '</div>';
		endif;

		$this->render_end();
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
