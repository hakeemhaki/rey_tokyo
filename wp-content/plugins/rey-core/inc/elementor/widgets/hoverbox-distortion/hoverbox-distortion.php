<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Hoverbox_Distortion')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Hoverbox_Distortion extends \Elementor\Widget_Base {

	private $_settings = [];
	private $_images = [];

	public function get_name() {
		return 'reycore-hoverbox-distortion';
	}

	public function get_title() {
		return __( 'HoverBox - Distortion', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-image-rollover';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return [ 'threejs', 'distortion-app', 'animejs', 'imagesloaded', 'reycore-widget-hoverbox-distortion-scripts' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-covers/#skew-cover';
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

		$this->start_controls_tabs( 'hb_content' );

		$this->start_controls_tab(
			'content_normal',
			[
				'label' => __( 'Normal', 'rey-core' ),
			]
		);

		$this->add_control(
			'active_image',
			[
			   'label' => esc_html__( 'Active Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'top_text',
			[
				'label' => esc_html__( 'Top Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => esc_html__( 'Some text', 'rey-core' ),
			]
		);

		$this->add_control(
			'top_text_hide_hover',
			[
				'label' => esc_html__( 'Hide Top Text on hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'content_hover',
			[
				'label' => __( 'Hover', 'rey-core' ),
			]
		);

		$this->add_control(
			'hover_image',
			[
			   'label' => esc_html__( 'Hover Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'text_1',
			[
				'label' => esc_html__( 'Hover Text #1', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => esc_html__( 'Some text', 'rey-core' ),
			]
		);

		$this->add_control(
			'text_2',
			[
				'label' => esc_html__( 'Hover Text #2', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => esc_html__( 'Some text', 'rey-core' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => esc_html__( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'CLICK HERE', 'rey-core' ),
				'placeholder' => esc_html__( 'some placeolder', 'rey-core' ),
			]
		);

		$this->add_control(
			'button_url',
			[
				'label' => __( 'Hover Button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'large',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_transition_settings',
			[
				'label' => __( 'Transition Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'transition_duration',
			[
				'label' => __( 'Transition Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1600,
				'min' => 500,
				'max' => 10000,
				'step' => 50,
				'selectors' => [
					'{{WRAPPER}} .hbDist-imgsBg' => 'transition-duration: {{VALUE}}ms',
				],
			]
		);

		$this->add_control(
			'transition_type',
			[
				'label' => esc_html__( 'Transition Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Morph into next slide', 'rey-core' ),
					'random'  => esc_html__( 'Random', 'rey-core' ),
					'0'  => esc_html__( 'Effect #1', 'rey-core' ),
					'1'  => esc_html__( 'Effect #2', 'rey-core' ),
					'2'  => esc_html__( 'Effect #3', 'rey-core' ),
					'3'  => esc_html__( 'Effect #4', 'rey-core' ),
					'4'  => esc_html__( 'Effect #5', 'rey-core' ),
					'5'  => esc_html__( 'Effect #6', 'rey-core' ),
					'6'  => esc_html__( 'Effect #7', 'rey-core' ),
					'7'  => esc_html__( 'Effect #8', 'rey-core' ),
					'8'  => esc_html__( 'Effect #9', 'rey-core' ),
					'9'  => esc_html__( 'Effect #10', 'rey-core' ),
					'10'  => esc_html__( 'Effect #11', 'rey-core' ),
					'11'  => esc_html__( 'Effect #12', 'rey-core' ),
					'12'  => esc_html__( 'Effect #13', 'rey-core' ),
					'13'  => esc_html__( 'Effect #14', 'rey-core' ),
					'14'  => esc_html__( 'Effect #15', 'rey-core' ),
					'15'  => esc_html__( 'Effect #16', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'transition_intensity',
			[
				'label' => esc_html__( 'Transition Intensity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0.2,
				'min' => -1,
				'max' => 1,
				'step' => 0.05,
			]
		);

		$this->add_control(
			'transition_out_intensity',
			[
				'label' => esc_html__( 'OUT Transition Intensity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => -1,
				'max' => 1,
				'step' => 0.05,
			]
		);

		$this->add_control(
			'transition_direction',
			[
				'label' => esc_html__( 'Vertical direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'transition_easing',
			[
				'label' => esc_html__( 'Transition Easing', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'easeInOutCubic',
				'options' => [
					'easeInQuad' => 'easeInQuad',
					'easeInCubic' => 'easeInCubic',
					'easeInQuart' => 'easeInQuart',
					'easeInQuint' => 'easeInQuint',
					'easeInSine' => 'easeInSine',
					'easeInExpo' => 'easeInExpo',
					'easeInCirc' => 'easeInCirc',
					'easeInOutQuad' => 'easeInOutQuad',
					'easeInOutCubic' => 'easeInOutCubic',
					'easeInOutQuart' => 'easeInOutQuart',
					'easeInOutQuint' => 'easeInOutQuint',
					'easeInOutSine' => 'easeInOutSine',
					'easeInOutExpo' => 'easeInOutExpo',
					'easeInOutCirc' => 'easeInOutCirc',
					'easeOutQuad' => 'easeOutQuad',
					'easeOutCubic' => 'easeOutCubic',
					'easeOutQuart' => 'easeOutQuart',
					'easeOutQuint' => 'easeOutQuint',
					'easeOutSine' => 'easeOutSine',
					'easeOutExpo' => 'easeOutExpo',
					'easeOutCirc' => 'easeOutCirc',
				]
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_responsive_control(
			'custom_height',
			[
				'label' => __( 'Element Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1440,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', 'vh', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .rey-hoverBox-distortion' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_control(
			'active_overlay_color',
			[
				'label' => esc_html__( 'Active - Overlay background color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.3)',
				'selectors' => [
					'{{WRAPPER}} .hbDist-imgsBg' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_overlay_color',
			[
				'label' => esc_html__( 'Hover - Overlay background color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}:hover .hbDist-imgsBg' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'top_text_typo',
				'label' => esc_html__('Top Text Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .hbDist-textTop',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text1_typo',
				'label' => esc_html__('Text #2 Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .hbDist-text1',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text2_typo',
				'label' => esc_html__('Text #2 Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .hbDist-text2',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .rey-coverDistortion .hbDist-captions .rey-buttonSkew',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-primary-outline btn-dash',
				'options' => [
					'btn-simple'  => __( 'Link', 'rey-core' ),
					'btn-primary'  => __( 'Primary', 'rey-core' ),
					'btn-secondary'  => __( 'Secondary', 'rey-core' ),
					'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
					'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
					'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
					'btn-primary-outline btn-dash btn-rounded'  => __( 'Primary Outlined & Dash & Rounded', 'rey-core' ),
				],
			]
		);

		$this->add_responsive_control(
			'inner_padding',
			[
				'label' => __( 'Inner Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .hbDist-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start(){

		$this->add_render_attribute( 'wrapper', 'class', 'rey-hoverBox-distortion');

		$this->add_render_attribute( 'wrapper', 'data-box-settings', wp_json_encode([
			'transitionDuration' => $this->_settings['transition_duration'],
			'effect' => $this->_settings['transition_type'],
			'intensity1' => $this->_settings['transition_intensity'],
			'intensity2' => $this->_settings['transition_out_intensity'],
			'vertical' => $this->_settings['transition_direction'] === 'yes',
			'easing' => $this->_settings['transition_easing'],
		]) );

		$this->_images[] = [
			'url' => reycore__get_attachment_image( [
				'image' => $this->_settings['active_image'],
				'size' => $this->_settings['image_size'],
				'key' => 'image',
				'settings' => $this->_settings,
				'return_url' => true
			] ),
		];

		$this->_images[] = [
			'url' => reycore__get_attachment_image( [
				'image' => $this->_settings['hover_image'],
				'size' => $this->_settings['image_size'],
				'key' => 'image',
				'settings' => $this->_settings,
				'return_url' => true
			] ),
		];

		$this->add_render_attribute( 'wrapper', 'data-images', wp_json_encode( $this->_images ) );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?></div><?php
	}

	public function render_images(){

		if( ! isset($this->_images[0]) ){
			return;
		}
		?>

		<div class="hbDist-imgs">
			<?php
				printf('<img src="%s" width="%s" height="%s" class="hbDist-img">', $this->_images[0]['url'][0], $this->_images[0]['url'][1], $this->_images[0]['url'][2]);
			?>
		</div>

		<div class="hbDist-imgsBg"></div>
		<?php
	}

	function render_text(){
		?>
		<div class="hbDist-text">

			<?php if( $top_text = $this->_settings['top_text'] ): ?>
				<h2 class="hbDist-textTop <?php echo $this->_settings['top_text_hide_hover'] === 'yes' ? '--hide-hover' : ''; ?>"><?php echo $top_text ?></h2>
			<?php endif; ?>

			<?php if( $text_1 = $this->_settings['text_1'] ): ?>
				<p class="hbDist-text1 hbDist-hoverItem"><?php echo $text_1 ?></p>
			<?php endif; ?>

			<?php if( $text_2 = $this->_settings['text_2'] ): ?>
				<p class="hbDist-text2 hbDist-hoverItem"><?php echo $text_2 ?></p>
			<?php endif; ?>

			<?php
			if( $button_text = $this->_settings['button_text'] ): ?>
				<div class="hbDist-btn hbDist-hoverItem">
					<?php
					$this->add_render_attribute( 'button' , 'class', ['btn', $this->_settings['button_style']] );

					if( isset($this->_settings['button_url']['url']) && ($url = $this->_settings['button_url']['url']) ){
						$this->add_render_attribute( 'button' , 'href', $url );

						if( $this->_settings['button_url']['is_external'] ){
							$this->add_render_attribute( 'button' , 'target', '_blank' );
						}

						if( $this->_settings['button_url']['nofollow'] ){
							$this->add_render_attribute( 'button' , 'rel', 'nofollow' );
						}
					} ?>

					<a <?php echo  $this->get_render_attribute_string('button'); ?>>
						<?php echo $button_text; ?>
					</a>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}


	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-hoverbox-distortion-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();
		$this->render_start();
		$this->render_images();
		$this->render_text();
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
