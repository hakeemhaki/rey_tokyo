<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Marquee')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Marquee extends \Elementor\Widget_Base {

	public $_settings = [];

	public function get_name() {
		return 'reycore-marquee';
	}

	public function get_title() {
		return __( 'Marquee', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-animation-text';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#marquee';
	}

	public function rey_get_script_depends() {
		return [ 'animejs', 'reycore-widget-marquee-scripts' ];
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
				'label' => esc_html__( 'Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'TEXT EXAMPLE', 'rey-core' ),
				'description' => esc_html__( 'You can also add multiple words per line.', 'rey-core' ),
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => esc_html__( 'Separator', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'None', 'rey-core' ),
					'dot'  => esc_html__( 'Dot', 'rey-core' ),
					'line'  => esc_html__( 'Line', 'rey-core' ),
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'direction',
			[
				'label' => esc_html__( 'Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'  => esc_html__( 'To Left', 'rey-core' ),
					'right'  => esc_html__( 'To Right', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => esc_html__( 'Speed (seconds)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
				'min' => 1,
				'max' => 500,
				'step' => 1,
				// 'selectors' => [
				// 	'{{WRAPPER}} .rey-marquee' => '--duration: {{VALUE}}s',
				// ],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				'default' => [],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Marquee Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typo',
				'selector' => '{{WRAPPER}}',
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => esc_html__( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'distance',
			[
			   'label' => esc_html__( 'Dinstance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'em' => [
						'min' => 0.1,
						'max' => 3.0,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => .2,
				],
				'selectors' => [
					'{{WRAPPER}} .rey-marquee' => '--distance: {{SIZE}}{{UNIT}};',
				],
				'render_type' => 'template',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_separator_style',
			[
				'label' => __( 'Separator Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'separator_size',
			[
			   'label' => esc_html__( 'Separator size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'em' => [
						'min' => 0.1,
						'max' => 3.0,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => .2,
				],
				'selectors' => [
					'{{WRAPPER}} .rey-marquee' => '--sep-size: {{SIZE}}{{UNIT}};',
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-marquee' => '--sep-color: {{VALUE}};',
				],
			]
		);


		$this->end_controls_section();
	}

	public function render_start()
	{
		$classes = [
			'rey-marquee',
			'rey-marquee--default',
			$this->_settings['separator'] ? '--sep-' . $this->_settings['separator'] : '',
			'--dir-' . $this->_settings['direction'],
		];

		$this->add_render_attribute( 'wrapper', [
			'class' => $classes,
			'data-duration' => $this->_settings['speed']
		] );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	public function render_the_content(){

		if( '' === $this->_settings['text'] ){
			return;
		}

		$content = '';

		$texts = preg_split('/\r\n|\r|\n/', $this->_settings['text']);

		if( count($texts) > 1 ){
			foreach ($texts as $text) {
				$content .= '<div class="rey-marqueeInnerChunk"><span>' . $text . '</span></div>';
			}
		}
		else {
			$content = '<span>' . $texts[0] . '</span>';
		}

		$link['start'] = $link['end'] = '';

		if ( ! empty( $this->_settings['link']['url'] ) ) {
			$this->add_link_attributes( 'href', $this->_settings['link'] );
			$link['start'] = sprintf('<a %s>', $this->get_render_attribute_string( 'href' ));
			$link['end'] = '</a>';
		} ?>

		<div class="rey-marqueeContent">
			<?php echo $link['start']; ?>
			<div class="rey-marqueeSlider">
				<div class="rey-marqueeChunk"><?php echo $content; ?></div>
			</div>
			<?php echo $link['end']; ?>
		</div>
		<?php
	}



	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-marquee-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();
		$this->render_the_content();
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
