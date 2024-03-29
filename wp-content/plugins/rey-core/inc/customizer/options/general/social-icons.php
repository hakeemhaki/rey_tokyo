<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'social_icons';

ReyCoreKirki::add_section($section, [
	'title'          => esc_html__('Sticky Social Icons', 'rey-core'),
	'priority'       => 130,
	'panel'			 => 'general_options',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'social__enable',
	'label'       => esc_html__( 'Enable Social Icons', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'social__icons',
	'label'       => esc_html__('Add icons', 'rey-core'),
	'description'       => __('For svg icons, please visit <a href="https://simpleicons.org/" target="_blank">Simple Icons</a> library or <a href="https://www.iconfinder.com/social-media-icons" target="_blank">IconFinder</a>.', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'type' => 'text',
		'value' => esc_html__('Social icon', 'rey-core'),
	],
	'button_label' => esc_html__('New Icon', 'rey-core'),
	'default'      => [],
	'fields' => [
		'text' => [
			'type'        => 'text',
			'label'       => esc_html__('Title', 'rey-core'),
		],
		'url' => [
			'type'        => 'text',
			'label'       => esc_html__('URL', 'rey-core'),
		],
		'image' => [
			'type'        => 'image',
			'label'       => esc_html__( 'Icon image (svg)', 'rey-core' ),
			'choices'     => [
				'save_as' => 'id',
			],
		],
		'color' => [
			'type'        => 'color',
			'label'       => esc_html__( 'Color', 'rey-core' ),
		],
		'bg_color' => [
			'type'        => 'color',
			'label'       => esc_html__( 'Background Color', 'rey-core' ),
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'social__text',
	'label'       => esc_html__( 'Title before', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Follow us', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

reycore_customizer__separator([
	'section' => $section,
	'id'      => 'a1',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'social__layout',
	'label'       => esc_html__( 'Layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'minimal',
	'choices'     => [
		'minimal' => esc_html__( 'Minimal', 'rey-core' ),
		'boxed' => esc_html__( 'Boxed', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'social__position',
	'label'       => esc_html__( 'Position', 'rey-core' ),
	'section'     => $section,
	'default'     => 'right',
	'choices'     => [
		'right' => esc_html__( 'Right', 'rey-core' ),
		'left' => esc_html__( 'Left', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'social__verticalize',
	'label'       => esc_html__( 'Verticalize', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'social__visibility',
	'label'       => esc_html__( 'Visibility', 'rey-core' ),
	'section'     => $section,
	'default'     => 'always',
	'choices'     => [
		'always' => esc_html__( 'Always show', 'rey-core' ),
		'up' => esc_html__( 'Show when scrolling upwards', 'rey-core' ),
		'down' => esc_html__( 'Show when scrolling downwards', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'social__distance_top',
	'label'       => esc_html__( 'Top Distance', 'rey-core' ) . ' (vh)',
	'section'     => $section,
	'default'     => 30,
	'choices'     => [
		'min'  => 1,
		'max'  => 100,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--r-social-icons-dist-top',
			'units'    		=> 'vh',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'social__distance_side',
	'label'       => esc_html__( 'Side Distance', 'rey-core' ) . ' (px)',
	'section'     => $section,
	'default'     => 40,
	'choices'     => [
		'min'  => 0,
		'max'  => 200,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--r-social-icons-dist-side',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'social__diff',
	'label'       => esc_html__( '', 'rey-core' ),
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Difference Color (Invert)', 'Customizer control title', 'rey-core' ),
		esc_html_x( 'Useful when icons will overlap a similar colored area. This option will force to invert the color', 'Customizer control description', 'rey-core' )
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'social__bg_color',
	'label'       => esc_html__( 'Boxed - Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--r-social-icons-bgcolor',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'social__layout',
			'operator' => '==',
			'value'    => 'boxed',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'social__boxed_radius',
	'label'       => esc_html__( 'Boxed - Border radius', 'rey-core' ),
	'section'     => $section,
	'default'     => 0,
	'choices'     => [
		'min'  => 0,
		'max'  => 300,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--r-social-icons-boxed-radius',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'social__layout',
			'operator' => '==',
			'value'    => 'boxed',
		],
	],
] );

reycore_customizer__separator([
	'section' => $section,
	'id'      => 'a2',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'social__distance_size',
	'label'       => esc_html__( 'Items Distance', 'rey-core' ) . ' (px)',
	'section'     => $section,
	'default'     => 14,
	'choices'     => [
		'min'  => 5,
		'max'  => 80,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--r-social-icons-dist-size',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'social__color',
	'label'       => esc_html__( 'Items Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--r-social-icons-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'social__diff',
			'operator' => '==',
			'value'    => false,
		],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'typography',
	'load_choices' => true,
	'settings'    => 'social__typo',
	'label'       => esc_attr__('Items Typography', 'rey-core'),
	'section'     => $section,
	'default'     => [
		'font-family'      => '',
		'font-size'      => '',
		'letter-spacing' => '',
		'text-transform' => '',
		'variant' => '',
		'font-weight' => '',
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.rey-stickySocial',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'social__btn_line',
	'label'       => esc_html__( 'Underline links', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'social__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );
