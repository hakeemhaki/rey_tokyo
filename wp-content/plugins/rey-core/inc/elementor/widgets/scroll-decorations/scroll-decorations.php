<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Scroll_Decorations')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Scroll_Decorations extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-scroll-decorations';
	}

	public function get_title() {
		return __( 'Scroll Decorations', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-scroll';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#scroll-decorations';
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Scroll_Decorations__Skewed( $this ) );
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
			'text',
			[
				'label' => __( 'Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'SCROLL', 'rey-core' ),
				'placeholder' => __( 'eg: SCROLL', 'rey-core' ),
				'condition' => [
					'_skin!' => ['skewed'],
				],
			]
		);

		$this->add_control(
			'target',
			[
				'label'   => __( 'Click Target', 'rey-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''       => __( 'None', 'rey-core' ),
					'top'    => __( 'Scroll To Top', 'rey-core' ),
					'next'   => __( 'Scroll to Next Section', 'rey-core' ),
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
			'primary_color',
			[
				'label' => __( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-scrollDeco' => 'color: {{VALUE}}',
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
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();


	}

	public function render_start( $settings ){

		$this->add_render_attribute( 'wrapper', 'class', 'rey-scrollDeco' );

		if( ! $skin = $settings['_skin'] ) {
			$skin = 'default';
		}

		$this->add_render_attribute( 'wrapper', 'class', 'rey-scrollDeco--' . $skin );
		if( $target = $settings['target'] ){
			$this->add_render_attribute( 'wrapper', 'data-target', $target );
		}
		?>
		<a href="#" <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?></a><?php
		reyCoreAssets()->add_styles('reycore-elementor-scroll-deco');
		reyCoreAssets()->add_scripts('reycore-elementor-scroll-deco');
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->render_start($settings);
		?>

		<?php if($text = $settings['text']): ?>
			<span class="rey-scrollDeco-text"><?php echo $text; ?></span>
		<?php endif; ?>

		<span class="rey-scrollDeco-line"></span>

		<?php
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
