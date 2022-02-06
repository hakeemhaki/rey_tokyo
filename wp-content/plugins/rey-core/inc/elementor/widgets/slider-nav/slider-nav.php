<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Slider_Nav')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Slider_Nav extends \Elementor\Widget_Base {

	public $_settings = [];

	public function get_name() {
		return 'reycore-slider-nav';
	}

	public function get_title() {
		return __( 'Slider Navigation', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-post-navigation';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-slider-nav-scripts' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#slider-navigation';
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Slider_Nav__Bullets( $this ) );
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

		$this->add_control(
			'slider_source',
			[
				'label' => esc_html__( 'Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'pg',
				'options' => [
					'pg'  => esc_html__( 'Product Grid - Carousel', 'rey-core' ),
					'bp'  => esc_html__( 'Blog Posts - Carousel', 'rey-core' ),
					'tabs'  => esc_html__( 'Tabs', 'rey-core' ),
					'parent'  => esc_html__( 'Parent Section Slideshow', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'slider_id',
			[
				'label' => __( 'Slider Unique ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'description' => __( 'Supported widgets: Product Grid - Carousel .', 'rey-core' ),
				'placeholder' => __( 'eg: .rey-gridCarousel-a6596db', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'slider_source' => 'pg',
				],
			]
		);

		$this->add_control(
			'slider_id__bp',
			[
				'label' => __( 'Slider Unique ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'description' => __( 'Supported widgets: Posts.', 'rey-core' ),
				'placeholder' => __( 'eg: .carousel-5e8448d138e77', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'slider_source' => 'bp',
				],
			]
		);

		$this->add_control(
			'tabs_id',
			[
				'label' => __( 'Tabs Unique ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'description' => __( 'Supported widgets: Section as Tab wrapper.', 'rey-core' ),
				'placeholder' => __( 'eg: .tabs-5e8448d138e77', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'slider_source' => 'tabs',
				],
			]
		);

		$this->add_control(
			'show_counter',
			[
				'label' => __( 'Show Counter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => '',
				],
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

		$this->add_control(
			'color',
			[
				'label' => __( 'Primary Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'align',
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
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bullets_style',
			[
				'label' => __( 'Bullets Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'lines',
				'options' => [
					'lines'  => __( 'Navigation Lines', 'rey-core' ),
					'dots'  => __( 'Navigation Dots', 'rey-core' ),
				],
				'condition' => [
					'_skin' => 'bullets',
				],
			]
		);

		$this->add_control(
			'bullets_width',
			[
				'label' => __( 'Bullets Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 2,
				'max' => 200,
				'step' => 1,
				'condition' => [
					'_skin' => 'bullets',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav--bullets-lines button' => 'width: calc({{VALUE}}px + 16px)',
					'{{WRAPPER}} .rey-sliderNav--bullets-dots button' => 'width: calc({{VALUE}}px + 16px); height: calc({{VALUE}}px + 16px);',
				],

			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_arrows_styles',
			[
				'label' => __( 'Arrows Styles', 'rey-core' ),
				'condition' => [
					'_skin' => '',
				],
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'arrows_type',
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

		$this->add_control(
			'arrows_custom_icon',
			[
				'label' => __( 'Custom Arrow Icon (Right)', 'elementor' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'arrows_type' => 'custom',
				],
			]
		);

		$this->add_control(
			'arrows_size',
			[
				'label' => esc_html__( 'Arrows size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 5,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--arrow-size: {{VALUE}}px',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start(){

		$classes = [
			'rey-sliderNav',
			'rey-sliderNav' . ( $this->_settings['_skin'] == 'bullets' ? '--bullets' : '--arrows' )
		];

		if( $this->_settings['_skin'] == 'bullets' ) {
			$classes[] = 'rey-sliderNav--bullets-' . $this->_settings['bullets_style'];
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		$this->add_render_attribute( 'wrapper', 'data-slider-source', esc_attr($this->_settings['slider_source']) );

		if( $this->_settings['slider_source'] !== 'parent' ){
			$source_id = $this->_settings['slider_id'];
			if( $this->_settings['slider_source'] === 'bp' ){
				$source_id = $this->_settings['slider_id__bp'];
			}
			if( $this->_settings['slider_source'] === 'tabs' ){
				$source_id = $this->_settings['tabs_id'];
			}
			$this->add_render_attribute( 'wrapper', 'data-slider-id', esc_attr($source_id) );
		}

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php

		add_filter('rey/svg_arrow_markup', [$this, 'custom_icon']);

	}

	function custom_icon( $html ){

		$custom_svg_icon = '';

		if( 'custom' === $this->_settings['arrows_type'] &&
			($custom_icon = $this->_settings['arrows_custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
			ob_start();
			\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
			return ob_get_clean();
		}
		else if( 'chevron' === $this->_settings['arrows_type'] ){
			return '<svg viewBox="0 0 40 64" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="39.5 32 6.83 64 0.5 57.38 26.76 32 0.5 6.62 6.83 0"></polygon></svg>';
		}

		return $html;
	}

	public function render_end(){
		?></div><?php
		remove_filter('rey/svg_arrow_markup', [$this, 'custom_icon']);
	}

	public function render_counter(){
		if( $this->_settings['show_counter'] === 'yes' ): ?>
			<div class="rey-sliderNav-counter">
				<span class="rey-sliderNav-counterCurrent"></span>
				<span class="rey-sliderNav-counterSeparator">&mdash;</span>
				<span class="rey-sliderNav-counterTotal"></span>
			</div>
		<?php endif;
	}

	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-slider-nav-styles']);

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();

		echo reycore__arrowSvg(false);

		$this->render_counter();

		echo reycore__arrowSvg();

		$this->render_end();

		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

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
