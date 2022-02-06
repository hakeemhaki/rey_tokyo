<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Menu_Fancy')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Menu_Fancy extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-menu-fancy';
	}

	public function get_title() {
		return __( 'Fancy Menu', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-text-align-left';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-menu-fancy-scripts' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#menu-fancy';
	}

	public function on_export($element)
    {
        unset(
			$element['settings']['menu_id']
        );

        return $element;
	}

	// protected function _register_skins() {
	// 	$this->add_skin( new ReyCore_Widget_Menu_Fancy__Stacks( $this ) );
	// }

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

		$get_all_menus = reyCoreHelper()->get_all_menus();

		$this->add_control(
			'menu_id',
			[
				'label' => __( 'Select Menu', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $get_all_menus,
				// 'condition' => [
				// 	'_skin' => '',
				// ],
			]
		);

		$this->add_control(
			'menu_depth',
			[
				'label' => __( 'Menu Depth', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
				'min' => 1,
				'step' => 1,
				// 'condition' => [
				// 	'_skin' => '',
				// ],
			]
		);

		$this->add_control(
			'size',
			[
				'label' => esc_html__( 'Size Presets', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'sm'  => esc_html__( 'Smaller', 'rey-core' ),
					''  => esc_html__( 'Default (inherits)', 'rey-core' ),
					'lg'  => esc_html__( 'Large', 'rey-core' ),
					'xl'  => esc_html__( 'Extra Large', 'rey-core' ),
					'xxl'  => esc_html__( 'Extra Extra Large', 'rey-core' ),
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
				'name' => 'typo',
				'selector' => '{{WRAPPER}} .menu-item > a',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .menu-item > a, {{WRAPPER}} .reyEl-fancyMenu-back' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => esc_html__( 'Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .menu-item > a:hover, {{WRAPPER}} .menu-item.current-menu-item > a, {{WRAPPER}} .reyEl-fancyMenu-back:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'arrow_size',
			[
				'label' => esc_html__( 'Back arrow size (px)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 16,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-fancyMenu-back' => 'font-size: {{VALUE}}px',
				],
			]
		);

		$this->add_control(
			'enable_indicators',
			[
				'label' => esc_html__( 'Submenu indicators', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Disabled', 'rey-core' ),
					'yes'  => esc_html__( 'Arrow', 'rey-core' ),
					'chevron'  => esc_html__( 'Chevron', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'submenus_arrow_size',
			[
				'label' => esc_html__( 'Submenus indicators size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} [data-indicator] .--submenu-indicator' => '--size: {{VALUE}}px',
				],
				'condition' => [
					'enable_indicators!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	public function __add_depth_attr( $atts, $item, $args, $depth ){
		$atts['data-depth'] = $depth;
		return $atts;
	}

	public function render_start($settings)
	{
		// add_filter('nav_menu_link_attributes', [$this, '__add_depth_attr'], 10, 4);

		$classes = [
			'rey-element',
			'reyEl-fancyMenu',
			'--size-' . $settings['size'],
		];

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		$this->add_render_attribute( 'wrapper', 'data-depth', $settings['menu_depth'] );

		if( $indicator = $settings['enable_indicators'] ){
			$this->add_render_attribute( 'wrapper', 'data-indicator', $indicator );
		} ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php if($settings['menu_depth'] > 1): ?>
			<button class="reyEl-fancyMenu-back">
				<?php echo reycore__arrowSvg(false); ?>
			</button>
			<?php endif; ?>
		<?php
	}

	public function render_end()
	{
		?></div><?php

		// remove_filter('nav_menu_link_attributes', [$this, '__add_depth_attr'], 10, 4);
	}

	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-menu-fancy-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$settings = $this->get_settings_for_display();

		$this->render_start( $settings );

		if( is_nav_menu($settings['menu_id']) ):
			wp_nav_menu([
				'menu'        => $settings['menu_id'],
				'container'   => '',
				'menu_class'   => 'reyEl-fancyMenu-nav --start',
				'items_wrap'  => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'depth' => $settings['menu_depth']
			]);
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
