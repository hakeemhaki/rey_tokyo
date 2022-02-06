<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Text_Scroller')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Text_Scroller extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-text-scroller';
	}

	public function get_title() {
		return __( 'Text Scroller', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-post-slider';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-text-scroller-scripts', 'rey-splide' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#text-scroller';
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
			'section_content',
			[
				'label' => __( 'Content', 'rey-core' ),
			]
		);

		$items = new \Elementor\Repeater();

		$items->add_control(
			'content',
			[
				'label' => __( 'Content', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( 'Default content.', 'rey-core' ),
				'placeholder' => __( 'Type your content here', 'rey-core' ),
			]
		);

		$this->add_control(
			'items',
			[
				'label' => __( 'Items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $items->get_controls(),
				'default' => [
					[
						'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'rey-core' ),
					],
					[
						'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'rey-core' ),
					],
				],
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'autoplay_duration',
			[
				'label' => __( 'Autoplay Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'min' => 2000,
				'max' => 20000,
				'step' => 50,
				'condition' => [
					'autoplay!' => '',
				],
			]
		);

		$this->add_control(
			'arrows',
			[
				'label' => __( 'Arrows Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'effect',
			[
				'label' => esc_html__( 'Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide'  => esc_html__( 'Slide', 'rey-core' ),
					'fade'  => esc_html__( 'Fade', 'rey-core' ),
					'vertical'  => esc_html__( 'Vertical Slide', 'rey-core' ),
				],
			]
		);

		$this->add_control(
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

		// $this->add_control(
		// 	'dots',
		// 	[
		// 		'label' => __( 'Dots Navigation', 'rey-core' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'default' => '',
		// 	]
		// );


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
					'{{WRAPPER}} .rey-textScroller-item, {{WRAPPER}} .rey-textScroller-item a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typo',
				'selector' => '{{WRAPPER}} .rey-textScroller-item',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'center',
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

		$this->add_responsive_control(
			'arrows_distance',
			[
				'label' => esc_html__( 'Arrows distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => -200,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-textScroller' => '--arrow-distance: {{VALUE}}px;',
				],
				'condition' => [
					'arrows!' => '',
				],
			]
		);

		$this->end_controls_section();


	}

	public function render_start( $settings ){

		$this->add_render_attribute( 'wrapper', 'class', 'rey-textScroller clearfix' );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?>
		</div>
		<?php
	}

	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-text-scroller-styles', 'rey-splide']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$settings = $this->get_settings_for_display();

		$this->render_start($settings);

		$id_class = sprintf('rey-textScroller-%s', $this->get_id() );

		if( !empty($settings['items']) ){

			$classes[] = $id_class;

			$slider_settings = [
				'type' => 'slide',
				'autoplay' => $settings['autoplay'] !== '',
				'interval' => absint($settings['autoplay_duration']),
				'delayInit' => absint($settings['delay_init']),
				'customArrows' => $settings['arrows'] !== '' ? '.rey-textScroller-arrows--' . $this->get_id() : false,
			];

			if( $settings['effect'] === 'vertical' ){
				$slider_settings['type'] = 'fade';
				$slider_settings['speed'] = 1;
				$classes[] = ' --vertical';
			}
			elseif( $settings['effect'] === 'fade' ){
				$slider_settings['type'] = 'fade';
			} ?>

			<div class="rey-textScroller-items splide <?php echo implode(' ', $classes); ?>" data-slider-config='<?php echo wp_json_encode($slider_settings); ?>' >

				<div class="splide__track">
					<div class="splide__list">
					<?php
					foreach ($settings['items'] as $key => $item) {
						printf('<div class="splide__slide rey-textScroller-item"><span>%s</span></div>', $item['content']);
					} ?>
					</div>
				</div>

				<?php if( $settings['arrows'] !== '' ): ?>
				<div class="rey-textScroller-arrows rey-textScroller-arrows--<?php echo $this->get_id() ?>">
					<?php echo reycore__arrowSvg(false, 'rey-textScroller-arrow', 'data-dir="-1"'); ?>
					<?php echo reycore__arrowSvg(true, 'rey-textScroller-arrow', 'data-dir="+1"'); ?>
				</div>
				<?php endif; ?>

			</div>

		<?php
		}
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
