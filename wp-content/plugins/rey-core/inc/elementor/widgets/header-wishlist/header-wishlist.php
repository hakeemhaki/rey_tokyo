<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Header_Wishlist') ):
/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Header_Wishlist extends \Elementor\Widget_Base {

	public $_settings = [];

	public function get_name() {
		return 'reycore-header-wishlist';
	}

	public function get_title() {
		return __( 'Account - Wishlist', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-heart-o';
	}

	public function get_categories() {
		return [ 'rey-header' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce', 'reycore-wc-header-wishlist', 'reycore-wishlist', 'reycore-elementor-elem-header-wishlist', 'wp-util' ];
	}

	// public function get_custom_help_url() {
	// 	return 'https://support.reytheme.com/kb/rey-elements-header/#wishlist';
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
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'text',
				[
					'label' => esc_html__( 'Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: WISHLIST', 'rey-core' ),
				]
			);

			$this->add_control(
				'icon',
				[
					'label' => esc_html__( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => get_theme_mod('wishlist__icon_type', 'heart'),
					'options' => [
						''  => esc_html__( 'None', 'rey-core' ),
						'heart'  => esc_html__( 'Heart', 'rey-core' ),
						'favorites'  => esc_html__( 'Ribbon', 'rey-core' ),
						'custom'  => esc_html__( '- Custom Icon -', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'custom_icon',
				[
					'label' => __( 'Custom Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [
						'icon' => 'custom',
					],

				]
			);

			$this->add_control(
				'counter_layout',
				[
					'label' => esc_html__( 'Counter layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'minimal',
					'options' => [
						''  => esc_html__( 'Hide', 'rey-core' ),
						'minimal'  => esc_html__( 'Minimal', 'rey-core' ),
						'bubble'  => esc_html__( 'Bubble', 'rey-core' ),
						// 'icon'  => esc_html__( 'In Icon', 'rey-core' ),
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

			$this->add_control(
				'icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 200,
					'step' => 1,
					'condition' => [
						'icon!' => '',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--icon-size: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hover_color',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn:hover' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'counter_color',
				[
					'label' => esc_html__( 'Counter Bg. Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .--counter-bubble .rey-wishlistCounter-number' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'counter_layout' => 'bubble',
					],
				]
			);

			$this->add_control(
				'text_position',
				[
					'label' => __( 'Text Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'after',
					'options' => [
						'before' => esc_html__( 'Before', 'rey-core' ),
						'after' => esc_html__( 'After', 'rey-core' ),
						'under' => esc_html__( 'Under', 'rey-core' ),
					],
					'condition' => [
						'text!' => '',
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
						'text!' => '',
					],
				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'label' => esc_html__('Text Typography', 'rey-core'),
					'name' => 'text_typo',
					'selector' => '{{WRAPPER}} .rey-elWishlist-btnText',
					'condition' => [
						'text!' => '',
					],
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

			$this->add_control(
				'panel_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-elWishlist-content' => '--color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'panel_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-elWishlist-content' => '--background-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_start()
	{
		reyCoreAssets()->add_styles('rey-wc-header-wishlist');

		$classes = [
			'rey-elWishlist',
			'rey-headerIcon',
			'rey-header-dropPanel',
		];

		reyCoreAssets()->add_styles('rey-header-drop-panel');
		reyCoreAssets()->add_scripts('rey-drop-panel');

		$this->add_render_attribute( 'wrapper', 'class', $classes ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	function counter_html(){
		return class_exists('ReyCore_WooCommerce_Wishlist') ? ReyCore_WooCommerce_Wishlist::get_wishlist_counter_html() : '';
	}

	function get_url(){
		return class_exists('ReyCore_WooCommerce_Wishlist') ? ReyCore_WooCommerce_Wishlist::get_wishlist_url() : '';
	}

	public function render_button(){

		$text_html = $icon_html = $counter_layout_html = "";

		if( $text = $this->_settings['text'] ){
			$text_html = "<span class=\"rey-elWishlist-btnText\">{$text}</span>";
		}

		if( $icon = $this->_settings['icon'] ){

			if( $icon === 'custom' ){
				if( ($custom_icon = $this->_settings['custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
					ob_start();
					\Elementor\Icons_Manager::render_icon( $custom_icon );
					$icon_html = ob_get_clean();
				}
			}
			else {
				$icon_html = reycore__get_svg_icon__core([
					'id' => 'reycore-icon-' . $icon,
					'class' => 'rey-elWishlist-btnIcon'
				]);
			}
		}

		if( $this->_settings['counter_layout'] !== '' ){
			if( $counter_html = $this->counter_html() ){
				$counter_html .= reycore__get_svg_icon(['id' => 'rey-icon-close', 'class' => 'rey-elWishlist-btnCounter-close']);
				$counter_layout_html = sprintf('<span class="rey-elWishlist-btnCounter">%s</span>', $counter_html);
			}
		}

		$classes = [
			'--itype-' . $this->_settings['icon'],
			'--counter-' . $this->_settings['counter_layout'],
			'--tp-' . $this->_settings['text_position']
		];

		printf('<button class="btn rey-headerIcon-btn rey-header-dropPanel-btn %s" aria-label="%s">', esc_attr( implode(' ', $classes)), esc_html__('Open', 'rey-core'));
			echo $text_html;
			echo sprintf('<span class="__icon">%s</span>', $icon_html);
			echo $counter_layout_html;
		echo '</button>';
	}

	public function render_panel() {

		echo '<div class="rey-header-dropPanel-content rey-elWishlist-content">';

			echo '<h4 class="rey-wishlistPanel-title">';

				$title = apply_filters('reycore/woocommerce/wishlist/title', esc_html_x('WISHLIST', 'Title in Header.', 'rey-core'));

				if( $wishlist_url = $this->get_url() ){
					printf( '<a href="%s">%s</a>', esc_url( $wishlist_url ), $title );
				}
				else {
					echo $title;
				}

				echo $this->counter_html();

			echo '</h4>';

			echo '<div class="rey-wishlistPanel-container --prod-' . $this->_settings['products_layout'] . '">';
				echo '<div class="rey-elWishlist-panel rey-wishlistPanel"></div>';
				echo '<div class="rey-lineLoader"></div>';
			echo '</div>';
		echo '</div>';

		if( class_exists('ReyCore_WooCommerce_Wishlist') ){
			ReyCore_WooCommerce_Wishlist::getInstance()->load_dependencies();
		}

	}

	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-header-wishlist-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();
		$this->render_start();
		$this->render_button();
		$this->render_panel();
		$this->render_end();

	}

	protected function content_template() {}
}

endif;
