<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Section_Skew')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Section_Skew extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-section-skew';
	}

	public function get_title() {
		return __( 'Section - Skew', 'rey-core' );
	}

	public function get_icon() {
		return 'rey-font-icon-skew-section';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#section-skew';
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
			'section_content_left',
			[
				'label' => __( 'Content', 'rey-core' ),
			]
		);

		$this->add_control(
			'outline_text',
			[
				'label' => __( 'Outlined Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'BRAND FOCUS', 'rey-core' ),
				'placeholder' => __( 'eg: SOME TEXT', 'rey-core' ),
				'label_block' => true
			]
		);

		$this->add_control(
			'main_title',
			[
				'label' => __( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Some text', 'rey-core' ),
				'placeholder' => __( 'eg: SOME TEXT', 'rey-core' ),
				'label_block' => true
			]
		);

		$this->add_control(
			'main_image',
			[
			   'label' => __( 'Image (above title)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
			]
		);


		$this->add_control(
			'btn_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOW NOW', 'rey-core' ),
			]
		);

		$this->add_control(
			'btn_link',
			[
				'label' => __( 'Button Link', 'rey-core' ),
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

		$this->end_controls_section();


		$this->start_controls_section(
			'section_content_right',
			[
				'label' => __( 'Media Content', 'rey-core' ),
			]
		);

		$this->add_control(
			'media_type',
			[
				'label' => __( 'Media Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image'  => __( 'Image', 'rey-core' ),
					'video'  => __( 'Video', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'media_image',
			[
			   'label' => __( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'media_type' => 'image',
				],
			]
		);

		$this->add_control(
			'video_type',
			[
				'label' => __( 'Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => [
					'youtube' => __( 'YouTube', 'rey-core' ),
					'vimeo' => __( 'Vimeo', 'rey-core' ),
					'dailymotion' => __( 'Dailymotion', 'rey-core' ),
					'hosted' => __( 'Self Hosted', 'rey-core' ),
				],
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'youtube_url',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your URL', 'rey-core' ) . ' (YouTube)',
				'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'label_block' => true,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'youtube',
				],
			]
		);

		$this->add_control(
			'vimeo_url',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your URL', 'rey-core' ) . ' (Vimeo)',
				'default' => 'https://vimeo.com/235215203',
				'label_block' => true,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'dailymotion_url',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your URL', 'rey-core' ) . ' (Dailymotion)',
				'default' => 'https://www.dailymotion.com/video/x6tqhqb',
				'label_block' => true,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'dailymotion',
				],
			]
		);

		$this->add_control(
			'insert_url',
			[
				'label' => __( 'External URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
				],
			]
		);

		$this->add_control(
			'hosted_url',
			[
				'label' => __( 'Choose File', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'media_type' => 'video',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
					'insert_url' => '',
				],
			]
		);

		$this->add_control(
			'external_url',
			[
				'label' => __( 'URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'autocomplete' => false,
				'show_external' => false,
				'label_block' => true,
				'show_label' => false,
				'media_type' => 'video',
				'placeholder' => __( 'Enter your URL', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
					'insert_url' => 'yes',
				],
			]
		);

		$this->add_control(
			'start',
			[
				'label' => __( 'Start Time', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'description' => __( 'Specify a start time (in seconds)', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'loop' => '',
				],
			]
		);

		$this->add_control(
			'end',
			[
				'label' => __( 'End Time', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'description' => __( 'Specify an end time (in seconds)', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'loop' => '',
					'video_type' => [ 'youtube', 'hosted' ],
				],
			]
		);

		$this->add_control(
			'video_options',
			[
				'label' => __( 'Video Options', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'mute',
			[
				'label' => __( 'Mute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label' => __( 'Loop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
					'video_type!' => 'dailymotion',
				],
			]
		);

		$this->add_control(
			'controls',
			[
				'label' => __( 'Player Controls', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type!' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'showinfo',
			[
				'label' => __( 'Video Info', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => [ 'dailymotion' ],
				],
			]
		);

		$this->add_control(
			'modestbranding',
			[
				'label' => __( 'Modest Branding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'media_type' => 'video',
					'video_type' => [ 'youtube' ],
					'controls' => 'yes',
				],
			]
		);

		$this->add_control(
			'logo',
			[
				'label' => __( 'Logo', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => [ 'dailymotion' ],
				],
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Controls Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'media_type' => 'video',
					'video_type' => [ 'vimeo', 'dailymotion' ],
				],
			]
		);

		// YouTube.
		$this->add_control(
			'yt_privacy',
			[
				'label' => __( 'Privacy Mode', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'description' => __( 'When you turn on privacy mode, YouTube won\'t store information about visitors on your website unless they play the video.', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'youtube',
				],
			]
		);

		$this->add_control(
			'rel',
			[
				'label' => __( 'Suggested Videos', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Current Video Channel', 'rey-core' ),
					'yes' => __( 'Any Video', 'rey-core' ),
				],
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'youtube',
				],
			]
		);

		// Vimeo.
		$this->add_control(
			'vimeo_title',
			[
				'label' => __( 'Intro Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'vimeo_portrait',
			[
				'label' => __( 'Intro Portrait', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'vimeo_byline',
			[
				'label' => __( 'Intro Byline', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'default' => 'yes',
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'download_button',
			[
				'label' => __( 'Download Button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_off' => __( 'Hide', 'rey-core' ),
				'label_on' => __( 'Show', 'rey-core' ),
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
				],
			]
		);

		$this->add_control(
			'poster',
			[
				'label' => __( 'Poster', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'condition' => [
					'media_type' => 'video',
					'video_type' => 'hosted',
				],
			]
		);

		// $this->add_control(
		// 	'view',
		// 	[
		// 		'label' => __( 'View', 'rey-core' ),
		// 		'type' => \Elementor\Controls_Manager::HIDDEN,
		// 		'default' => 'youtube',
		// 		'condition' => [
		// 			'media_type' => 'video',
		// 		],
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
			'bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-left' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'media_top',
			[
				'label' => __( 'Media Top Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 400,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-right' => 'top: {{VALUE}}px',
					'{{WRAPPER}} .rey-sectionSkew' => 'padding-bottom: {{VALUE}}px',
				],
			]
		);

		$this->add_control(
			'outline_text_title',
			[
			   'label' => __( 'Outlined Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'outline_text_color',
			[
				'label' => __( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'outline_text_typo',
				'selector' => '{{WRAPPER}} .sectionSkew-text',
			]
		);

		$this->add_control(
			'main_text_title',
			[
			   'label' => __( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'main_text_color',
			[
				'label' => __( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-mainTitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'main_text_typo',
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .sectionSkew-mainTitle',
			]
		);

		$this->add_control(
			'btn_title',
			[
			   'label' => __( 'Button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label' => __( 'Button Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sectionSkew-mainBtn .btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-line-active',
				'options' => [
					'btn-simple'  => __( 'Link', 'rey-core' ),
					'btn-primary'  => __( 'Primary', 'rey-core' ),
					'btn-secondary'  => __( 'Secondary', 'rey-core' ),
					'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
					'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
					'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();
	}


	public function render_start($settings)
	{
		$this->add_render_attribute( 'wrapper', 'class', 'rey-sectionSkew' ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	public function render_left($settings)
	{
		?>
		<div class="sectionSkew-left">
			<div class="sectionSkew-leftInner">
				<?php

				if( $title = $settings['outline_text'] ):
					printf('<div class="sectionSkew-textWrapper"><h2 class="sectionSkew-text">%s</h2></div>', $title);
				endif;

				echo reycore__get_attachment_image( [
					'image' => $settings['main_image'],
					'size' => 'full',
					'attributes' => ['class'=>'sectionSkew-mainImg']
				] );

				if( $main_title = $settings['main_title'] ):
					printf('<h3 class="sectionSkew-mainTitle">%s</h3>', $main_title);
				endif;

				if( $button_text = $settings['btn_text'] ): ?>

					<div class="sectionSkew-mainBtn">

						<?php
						$this->add_render_attribute( 'link' , 'class', 'btn ' . $settings['btn_style'] );

						if( isset($settings['btn_link']['url']) && $url = $settings['btn_link']['url'] ){
							$this->add_render_attribute( 'link' , 'href', $url );

							if( $settings['btn_link']['is_external'] ){
								$this->add_render_attribute( 'link' , 'target', '_blank' );
							}

							if( $settings['btn_link']['nofollow'] ){
								$this->add_render_attribute( 'link' , 'rel', 'nofollow' );
							}
						} ?>

						<a <?php echo  $this->get_render_attribute_string('link'); ?>>
							<?php echo $button_text; ?>
						</a>
					</div>
					<!-- .sectionSkew-mainBtn -->
				<?php endif; ?>

			</div>
		</div><?php
	}

	public function render_right($settings)
	{
		?>
		<div class="sectionSkew-right">
			<div class="sectionSkew-rightInner">
				<?php

				if( 'image' === $settings['media_type'] ):

					echo reycore__get_attachment_image( [
						'image' => $settings['media_image'],
						'size' => 'full',
						'attributes' => ['class'=>'sectionSkew-mediaImg']
					] );

				elseif( 'video' === $settings['media_type'] ):

					$video = \Elementor\Plugin::instance()->elements_manager->create_element_instance(
						[
							'elType' => 'widget',
							'widgetType' => 'video',
							'id' => 'sectionSkew-video-' . $this->get_id(),
							'settings' => [
								'video_type' => $settings['video_type'],
								'youtube_url' => $settings['youtube_url'],
								'vimeo_url' => $settings['vimeo_url'],
								'dailymotion_url' => $settings['dailymotion_url'],
								'insert_url' => $settings['insert_url'],
								'hosted_url' => $settings['hosted_url'],
								'external_url' => $settings['external_url'],
								'start' => $settings['start'],
								'end' => $settings['end'],
								'autoplay' => $settings['autoplay'],
								'mute' => $settings['mute'],
								'loop' => $settings['loop'],
								'controls' => $settings['controls'],
								'showinfo' => $settings['showinfo'],
								'modestbranding' => $settings['modestbranding'],
								'logo' => $settings['logo'],
								'color' => $settings['color'],
								'yt_privacy' => $settings['yt_privacy'],
								'rel' => $settings['rel'],
								'vimeo_title' => $settings['vimeo_title'],
								'vimeo_portrait' => $settings['vimeo_portrait'],
								'vimeo_byline' => $settings['vimeo_byline'],
								'download_button' => $settings['download_button'],
								'poster' => $settings['poster'],
							],
						]
					);

					$video->print_element();

				endif; ?>
			</div>
		</div><?php
	}

	protected function render() {
		reyCoreAssets()->add_styles(['reycore-widget-section-skew-styles']);
		$settings = $this->get_settings_for_display();
		$this->render_start($settings);
		$this->render_left($settings);
		$this->render_right($settings);
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
