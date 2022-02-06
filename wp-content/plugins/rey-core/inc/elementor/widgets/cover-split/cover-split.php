<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Cover_Split')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Cover_Split extends \Elementor\Widget_Base {

	private $_items = [];

	public function __construct( $data = [], $args = null ) {

		do_action('reycore/elementor/widget/construct', $data);

		if( ! empty($data) ):
			// force overlapping on desktop
			add_filter('reycore/header_helper/overlap_classes', function($classes){
				$classes['desktop'] = '--dnone-desktop';
				return $classes;
			});
		endif;

		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'reycore-cover-split';
	}

	public function get_title() {
		return __( 'Cover - Split Slider', 'rey-core' );
	}

	public function get_icon() {
		return 'rey-font-icon-general-r';
	}

	public function get_categories() {
		return [ 'rey-theme-covers' ];
	}

	public function rey_get_script_depends() {
		return [ 'rey-videos', 'jquery-mousewheel', 'imagesloaded', 'reycore-widget-cover-split-scripts' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements-covers/#split-slider';
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
	/**
	 * Main Slide
	 */

		$this->start_controls_section(
			'section_main_content',
			[
				'label' => __( 'Main Slide', 'rey-core' ),
			]
		);

		$this->add_control(
			'main_slide',
			[
				'label' => esc_html__( 'Enable Main Slide', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'main_label',
			[
				'label' => __( 'Label Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'eg: NEW ARRIVAL', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'main_title',
			[
				'label' => __( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'main_subtitle',
			[
				'label' => __( 'Sub-Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'label_block' => true,
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'main_button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'main_button_url',
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
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'label' => esc_html__('Background', 'rey-core'),
				'name' => 'main_background',
				'types' => [ 'classic', 'gradient', 'video' ],
				'selector' => '{{WRAPPER}} .cSplit-slide--mainBg',
				'prefix_class' => 'cSplit-slide--mainBg--',
				'frontend_available' => false,
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->end_controls_section();

	/**
	 * Slides Content
	 */

		$this->start_controls_section(
			'section_slides_content',
			[
				'label' => __( 'Slides', 'rey-core' ),
			]
		);

		$items = new \Elementor\Repeater();

		$items->add_control(
			'image',
			[
			   'label' => __( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'selectors' => [
					// '{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfRight' => 'background-image: url({{URL}})',
				],
			]
		);

		$items->add_control(
			'label',
			[
				'label'       => __( 'Label Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$items->add_control(
			'title',
			[
				'label'       => __( 'Title', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$items->add_control(
			'subtitle',
			[
				'label'       => __( 'Subtitle Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label_block' => true,
			]
		);

		$items->add_control(
			'button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
			]
		);

		$items->add_control(
			'button_url',
			[
				'label' => __( 'Link', 'rey-core' ),
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

		$items->add_control(
			'bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfLeft' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfLeft-inner:before' => 'color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfLeft' => 'color: {{VALUE}}',
				],
			]
		);

		$items->add_responsive_control(
			'img_position',
			[
			   'label' => __( 'Image Position (x & y)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '50% 50%',
				'selectors' => [
					// '{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfRight' => 'background-position: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfRight-img' => 'object-position: {{VALUE}};',
				],
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
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'label' => __( 'Label Text #1', 'rey-core' ),
						'title' => __( 'Title Text #1', 'rey-core' ),
						'subtitle' => __( 'Subtitle Text #1', 'rey-core' ),
						'button_text' => __( 'Button Text #1', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'label' => __( 'Label Text #2', 'rey-core' ),
						'title' => __( 'Title Text #2', 'rey-core' ),
						'subtitle' => __( 'Subtitle Text #2', 'rey-core' ),
						'button_text' => __( 'Button Text #2', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
				]
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'large',
			]
		);

		$this->end_controls_section();

	/**
	 * Social Icons
	 */

		$this->start_controls_section(
			'section_social',
			[
				'label' => __( 'Social Icons', 'rey-core' ),
			]
		);

		$social_icons = new \Elementor\Repeater();

		$social_icons->add_control(
			'social',
			[
				'label' => __( 'Show Elements', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options' => reycore__social_icons_list_select2(),
				'default' => 'wordpress',
			]
		);

		$social_icons->add_control(
			'link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'label_block' => true,
				'default' => [
					'is_external' => 'true',
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
			]
		);

		$this->add_control(
			'social_icon_list',
			[
				'label' => __( 'Social Icons', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $social_icons->get_controls(),
				'default' => [
					[
						'social' => 'facebook',
					],
					[
						'social' => 'twitter',
					],
					[
						'social' => 'google-plus',
					],
				],
				'title_field' => '{{{ social.replace( \'-\', \' \' ).replace( /\b\w/g, function( letter ){ return letter.toUpperCase() } ) }}}',
				'prevent_empty' => false,
			]
		);

		$this->add_control(
			'social_text',
			[
				'label' => __( '"Follow" Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'FOLLOW US', 'rey-core' ),
				'placeholder' => __( 'eg: FOLLOW US', 'rey-core' ),
			]
		);

		$this->end_controls_section();

	/**
	 * Bottom Buttons
	 */

		$this->start_controls_section(
			'section_bottom_links',
			[
				'label' => __( 'Bottom Links', 'rey-core' ),
			]
		);

		$bottom_links = new \Elementor\Repeater();

		$bottom_links->add_control(
			'button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
			]
		);

		$bottom_links->add_control(
			'button_url',
			[
				'label' => __( 'Link', 'rey-core' ),
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


		$this->add_control(
			'bottom_links',
			[
				'label' => __( 'Bottom Links', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $bottom_links->get_controls(),
				'prevent_empty' => false,
			]
		);

		$this->end_controls_section();

	/**
	 * Scroll Button
	 */

		$this->start_controls_section(
			'section_scroll',
			[
				'label' => __( 'Scroll Button', 'rey-core' ),
			]
		);

		$this->add_control(
			'scroll_decoration',
			[
				'label' => __( 'Show Scroll Decoration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'scroll_text',
			[
				'label' => __( '"Scroll" Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'SCROLL', 'rey-core' ),
				'placeholder' => __( 'eg: SCROLL', 'rey-core' ),
				'condition' => [
					'scroll_decoration' => 'yes',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_advanced_sett',
			[
				'label' => __( 'Advanced Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'scroll_disable',
				[
					'label' => esc_html__( 'Disable Scroll Hijack', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'scroll_sensitivity',
				[
					'label' => esc_html__( 'Scroll Sensitivity', 'rey-core' ) . ' (ms)',
					'description' => esc_html__( 'Control the sensitivity of the scroll. The larger the value, the slower will scroll to the next slide.', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 400,
					'min' => 50,
					'max' => 3000,
					'step' => 10,
					'condition' => [
						'scroll_disable' => '',
					],
				]
			);

			$this->add_control(
				'arrows',
				[
					'label' => esc_html__( 'Display Arrows?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'nav_count',
				[
					'label' => esc_html__( 'Show Navigation Count', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => esc_html__( 'Autoplay?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'scroll_disable!' => '',
					],
				]
			);

			$this->add_control(
				'autoplay_duration',
				[
					'label' => esc_html__( 'Autoplay duration', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 3000,
					'max' => 20000,
					'step' => 50,
					'condition' => [
						'autoplay!' => '',
						'scroll_disable!' => '',
					],
				]
			);

		$this->end_controls_section();

	/**
	 * Styles
	 */

		$this->start_controls_section(
			'section_styles_general',
			[
				'label' => __( 'General Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'edges',
			[
				'label' => esc_html__( 'Enable Edges', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'widget_bg_color',
			[
				'label' => __( 'Widget Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .cSplit-borders span' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'edges' => 'yes',
				],
			]
		);

		$this->add_control(
			'widget_text_color',
			[
				'label' => __( 'Widget Text Color', 'rey-core' ),
				'description' => __( 'This will change the color of the side content (header text, social icons, nav, bottom links etc.)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .cSplit-social' => 'color: {{VALUE}}',
					'{{WRAPPER}} .cSplit-nav' => 'color: {{VALUE}}',
					'{{WRAPPER}} .cSplit-bottomLinks' => 'color: {{VALUE}}',
					'body.--cover-split-header:not(.--cover-split-header--top) .rey-siteHeader--custom:not(.--shrank)' => '--header-text-color: {{VALUE}}',
				],
				'condition' => [
					'edges' => 'yes',
				],
			]
		);

		$this->add_control(
			'social_icons_color',
			[
				'label' => __( 'Social Icons Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .cSplit-social' => 'color: {{VALUE}}',
				],
				'condition' => [
					'edges!' => 'yes',
				],
			]
		);

		$this->add_control(
			'nav_color',
			[
				'label' => __( 'Navigation Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .cSplit-nav' => 'color: {{VALUE}}',
				],
				'condition' => [
					'edges!' => 'yes',
				],
			]
		);

		$this->add_control(
			'bottom_links_color',
			[
				'label' => __( 'Bottom Links Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .cSplit-bottomLinks' => 'color: {{VALUE}}',
				],
				'condition' => [
					'edges!' => 'yes',
				],
			]
		);

		$this->add_control(
			'heading_header_tweaks',
			[
				'type' => \Elementor\Controls_Manager::HEADING,
				'label' => __( 'Header Tweaks', 'rey-core' ),
				'description' => __( 'Header Tweaks', 'rey-core' ),
				'separator' => 'before',
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'header_color',
			[
				'label' => esc_html__( 'Header Text Color (At Top)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'body.--cover-split-header.--cover-split-header--top:not(.search-panel--is-opened) .rey-siteHeader--custom:not(.--shrank)' => '--header-text-color: {{VALUE}}',
				],
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'header_gradient',
			[
				'label' => __( 'Add top gradient (for header)', 'rey-core' ),
				'description' => __( 'Enabling this option will add a subtle gradient at the top of the widget, falling behind the header, to help increase its contrast.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'header_invert_logo',
			[
				'label' => __( 'Invert Logo Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'selectors' => [
					'body.--cover-split-header.--cover-split-header--top:not(.search-panel--is-opened) .rey-siteHeader--custom:not(.--shrank) .rey-siteLogo' => '-webkit-filter: invert(100%); filter: invert(100%);',
				],
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'header_custom_logo',
			[
			   'label' => esc_html__( 'Custom Header Logo (to switch)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'condition' => [
					'main_slide' => 'yes',
					'header_invert_logo' => '',
				],
			]
		);

		$this->add_control(
			'header_custom_logo_mobile',
			[
				'label' => __( 'Enable on mobile', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'main_slide' => 'yes',
					'header_invert_logo' => '',
				],
			]
		);

		$this->add_control(
			'prevent_image_cutting',
			[
				'label' => esc_html__( 'Prevent Image Cutting', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'main_slide' => '',
				],
				'prefix_class' => '--prevent-img-',
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles_main',
			[
				'label' => __( 'Main Slide Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'main_slide' => 'yes',
				],
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typo',
				'label' => esc_html__('Label Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-label',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typo',
				'label' => esc_html__('Title Typography', 'rey-core'),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typo',
				'label' => esc_html__('Sub-Title Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-subtitle',
			]
		);

		$this->add_control(
			'main_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'main_bg_color',
			[
				'label' => __( 'Left Half - Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'alpha' => false,
				'default' => '#212529',
				'selectors' => [
					'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft:before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft-inner:before' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'main_bg_color_opacity',
			[
				'label' => __( 'Opacity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => .5,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'tablet_default' => [
					'size' => 0.5,
					'unit' => 'px',
				],
				'mobile_default' => [
					'size' => 1,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft:before' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles_slides',
			[
				'label' => __( 'Slides Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'slides_label_typo',
				'label' => esc_html__('Label Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cSplit-slide:not(.cSplit-slide--main) .cSplit-label',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'slides_title_typo',
				'label' => esc_html__('Title Typography', 'rey-core'),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .cSplit-slide:not(.cSplit-slide--main) .cSplit-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'slides_subtitle_typo',
				'label' => esc_html__('Sub-Title Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cSplit-slide:not(.cSplit-slide--main) .cSplit-subtitle',
			]
		);

		$this->end_controls_section();

	}

	public function render_start($settings)
	{

		$classes = [
			'rey-coverSplit',
			'--loading',
			$settings['edges'] === 'yes' ? '--edges' : ''
		];

		if( $settings['main_slide'] === 'yes' ){
			$classes[] = '--mainSlide';
			$classes[] = '--at-first';
		}

		$slide_settings = [
			'autoplay' => $settings['autoplay'] !== '' && $settings['scroll_disable'] !== '',
			'autoplayDuration' => $settings['autoplay_duration'],
			'scroll' => $settings['scroll_disable'] === '',
			'sensitivity' => $settings['scroll_sensitivity']
		];

		$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode($slide_settings) );

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		if( $settings['header_invert_logo'] === '' && isset($settings['header_custom_logo']['url']) && $logo_swap = esc_url($settings['header_custom_logo']['url']) ){
			$this->add_render_attribute( 'wrapper', 'data-logo', $logo_swap );

			if( isset($settings['header_custom_logo_mobile']) && $settings['header_custom_logo_mobile'] !== '' ){
				$this->add_render_attribute( 'wrapper', 'data-logo-mobile', '1' );
			}

		} ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?>
		</div>
		<?php
		echo ReyCoreElementor::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
	}

	public function render_scroll($settings)
	{
		if( $settings['scroll_decoration'] === 'yes' ): ?>

			<div class="cSplit-scroll">
				<a href="#" class="rey-scrollDeco rey-scrollDeco--default" data-target="next">
					<span class="rey-scrollDeco-text"><?php echo $settings['scroll_text'] ?></span>
					<span class="rey-scrollDeco-line"></span>
				</a>
			</div>

			<?php
			reyCoreAssets()->add_styles('reycore-elementor-scroll-deco');
			reyCoreAssets()->add_scripts('reycore-elementor-scroll-deco');

		endif;
	}

	public function render_slides($settings){

		if( $settings['header_gradient'] !== ''){ ?>
			<div class="cSplit-headerGradient u-transparent-gradient"></div>
		<?php } ?>

		<div class="cSplit-slide--mainBg cSplit--abs">
			<?php
				if( 'video' === $settings['main_background_background'] && $video_link = $settings['main_background_video_link'] ):

					$video_properties = \Elementor\Embed::get_video_properties( $video_link );

					if( isset($video_properties['provider']) && 'youtube' === $video_properties['provider'] ){
						echo reycore__get_youtube_iframe_html([
							'class' => 'rey-background-video-container',
							'video_id' => $video_properties['video_id'],
							'html_id' => 'yt' . $this->get_id(),
							'add_preview_image' => false,
							'mobile' => isset($settings['main_background_play_on_mobile']) && $settings['main_background_play_on_mobile'] === 'yes',
							'params' => [
								'start' => absint( $settings['main_background_video_start'] ),
								'end' => absint( $settings['main_background_video_end'] ),
								'loop' => $settings['main_background_play_once'] === '' ? 1 : 0,
							],
						]);
					}
					else {
						echo reycore__get_video_html([
							'class' => 'rey-background-video-container',
							'video_url' => $video_link,
							'start' => absint( $settings['main_background_video_start'] ),
							'end' => absint( $settings['main_background_video_end'] ),
							'mobile' => isset($settings['main_background_play_on_mobile']) && $settings['main_background_play_on_mobile'] === 'yes',
							'params' => [
								'loop' => $settings['main_background_play_once'] === '' ? 'loop' : '',
							],
						]);
					}

				endif; ?>
		</div>

		<div class="cSplit-slides cSplit--abs">

			<?php
			foreach($this->_items as $key => $item): ?>
			<div class="cSplit-slide cSplit--abs elementor-repeater-item-<?php echo $item['_id'] ?>">

				<div class="cSplit-halfLeft cSplit--abs">
					<div class="cSplit-halfLeft-inner">

						<?php if( $label = $item['label'] ): ?>
						<div class="cSplit-label"><?php echo $label ?></div>
						<?php endif; ?>

						<?php if( $title = $item['title'] ): ?>
						<h2 class="cSplit-title"><?php echo $title ?></h2>
						<?php endif; ?>

						<?php if( $subtitle = $item['subtitle'] ): ?>
						<div class="cSplit-subtitle"><?php echo $subtitle ?></div>
						<?php endif; ?>

						<?php if( $button_text = $item['button_text'] ): ?>
							<div class="cSplit-btn">

								<?php
								$url_key = 'url'.$key;

								$this->add_render_attribute( $url_key , 'class', 'btn' );

								if( isset($item['button_url']['url']) && $url = $item['button_url']['url'] ){
									$this->add_render_attribute( $url_key , 'href', $url );

									if( $item['button_url']['is_external'] ){
										$this->add_render_attribute( $url_key , 'target', '_blank' );
									}

									if( $item['button_url']['nofollow'] ){
										$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
									}
								}
								if( $key === 'main' ){
									$this->add_render_attribute( $url_key , 'class', 'btn-line-active' );
								}
								else {
									$this->add_render_attribute( $url_key , 'class', 'btn-primary-outline btn-dash' );
								}

								$btn_html = sprintf('<a %s>%s</a>', $this->get_render_attribute_string($url_key), $button_text);
								echo apply_filters('reycore/elementor/cover-split/btn_html', $btn_html, $item, $url_key, $key);
							?>
							</div>
							<!-- .cSplit-btn -->
						<?php endif; ?>

					</div>

					<?php echo $this->render_scroll($settings); ?>
				</div>
				<!-- .cSplit-halfLeft -->

				<div class="cSplit-halfRight cSplit--abs">
					<?php
						if( isset($item['image']) && isset($item['image']['id']) && $image_id = $item['image']['id'] ){
							echo reycore__get_attachment_image( apply_filters('reycore/elementor/cover-split/image_args', [
								'image' => $item['image'],
								'size' => $settings['image_size'],
								'attributes' => ['class'=> 'cSplit-halfRight-img cSplit--abs ' ]
							] ) );
						}
					?>
				</div>
			</div>
			<?php
			endforeach; ?>
		</div>
		<?php
	}

	public function render_social($settings){

		if( $social_icon_list = $settings['social_icon_list'] ): ?>

			<div class="cSplit-social">
				<div class="cSplit-socialIcons">
					<?php
					foreach ( $social_icon_list as $index => $item ):

						$link_key = 'link_' . $index;

						$this->add_render_attribute( $link_key, 'href', $item['link']['url'] );

						if ( $item['link']['is_external'] ) {
							$this->add_render_attribute( $link_key, 'target', '_blank' );
						}

						if ( $item['link']['nofollow'] ) {
							$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
						}
						?>
						<a class="cSplit-socialIcons-link" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
							<?php echo reycore__get_svg_social_icon([ 'id'=>$item['social'] ]); ?>
						</a>
					<?php endforeach; ?>
				</div>

				<?php if($social_text = $settings['social_text']): ?>
					<div class="cSplit-socialText"><?php echo $social_text ?></div>
				<?php endif; ?>

			</div>
			<!-- .cSplit-social -->
		<?php endif;
	}

	public function render_borders(){
		?>
		<div class="cSplit-borders">
			<span class="__top"></span>
			<span></span>
			<span></span>
			<span></span>
		</div>
		<?php
	}

	public function render_arrows( $settings ){

		if( $settings['arrows'] === '' ){
			return;
		}

		?>
		<div class="cSplit-arrows">
			<?php
				reycore__svg_arrows([
					'attributes' => [
						'left' => 'data-dir="-1"',
						'right' => 'data-dir="+1"',
					]
				]);
			?>
		</div>
		<?php
	}

	public function render_nav( $settings ){
		?>
		<div class="cSplit-nav">
			<?php
			foreach($this->_items as $key => $item ):
				if( is_numeric($key) ){

					$index = $settings['main_slide'] === 'yes' ? $key + 1 : $key;

					printf(
						'<div class="cSplit-navItem" data-index="%s"><span>%s</span></div>',
						$index,
						sprintf("%02d", $key + 1)
					);
				}
			endforeach; ?>
		</div>
		<?php
	}

	public function render_bottom_links($settings){

		if( $bottom_links = $settings['bottom_links'] ): ?>
			<div class="cSplit-bottomLinks">
				<?php
				foreach ( $bottom_links as $index => $item ):

					$link_key = 'blink_' . $index;

					$this->add_render_attribute( $link_key, 'href', $item['button_url']['url'] );

					if ( $item['button_url']['is_external'] ) {
						$this->add_render_attribute( $link_key, 'target', '_blank' );
					}

					if ( $item['button_url']['nofollow'] ) {
						$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
					}

					?>
					<a class="btn btn-line-active" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
						<?php echo $item['button_text'] ?>
					</a>
				<?php endforeach; ?>
			</div>
			<!-- .cSplit-bottomLinks -->
		<?php endif;
	}

	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-cover-split-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$settings = $this->get_settings_for_display();

		$this->render_start($settings);

		$slides = [];

		if( $settings['main_slide'] === 'yes' ){
			$slides = [
				'main' => [
					'label' => $settings['main_label'],
					'title' => $settings['main_title'],
					'subtitle' => $settings['main_subtitle'],
					'button_text' => $settings['main_button_text'],
					'button_url' => $settings['main_button_url'],
					'_id' => ' cSplit-slide--main',
				]
			];
		}

		$this->_items = array_merge($slides, $settings['items']);

		$this->render_slides($settings);

		if( $settings['edges'] === 'yes'){
			$this->render_borders();
		}

		$this->render_arrows($settings);

		if( $settings['nav_count'] === 'yes'){
			$this->render_nav($settings);
		}

		$this->render_social($settings);
		$this->render_bottom_links($settings);
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
