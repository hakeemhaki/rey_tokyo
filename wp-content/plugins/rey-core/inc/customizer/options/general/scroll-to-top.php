<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'scroll_to_top';

ReyCoreKirki::add_section($section, [
	'title'          => esc_html__('Scroll to top button', 'rey-core'),
	'priority'       => 130,
	'panel'			 => 'general_options',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'scroll_to_top__enable',
	'label'       => esc_html__( 'Select style', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Disabled', 'rey-core' ),
		'style1' => esc_html__( 'Style #1 - Minimal', 'rey-core' ),
		'style2' => esc_html__( 'Style #2 - Box', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'scroll_to_top__text',
	'label'       => esc_html__( 'Button text', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__('TOP', 'rey-core'),
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: TOP', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'scroll_to_top__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'scroll_to_top__color',
	'label'       => esc_html__( 'Button Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--scrolltotop-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'scroll_to_top__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'image',
	'settings'    => 'scroll_to_top__custom_icon',
	'label'       => esc_html__( 'Custom Icon', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'save_as' => 'id',
	],
	'active_callback' => [
		[
			'setting'  => 'scroll_to_top__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'scroll_to_top__bottom_position',
	'label'       => esc_html__( 'Distance from bottom', 'rey-core' ) . ' (vh)',
	'section'     => $section,
	'default'     => 10,
	'choices'     => [
		'min'  => 0,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'scroll_to_top__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--scroll-top-bottom',
			'units'    		=> 'vh',
		],
	],
	'responsive' => true,
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'scroll_to_top__entrance_point',
	'label'       => esc_html__( 'Entrance point', 'rey-core' ) . ' (%)',
	'section'     => $section,
	'default'     => 0,
	'choices'     => [
		'min'  => 0,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'scroll_to_top__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'scroll_to_top__position',
	'label'       => esc_html__( 'Select Position', 'rey-core' ),
	'section'     => $section,
	'default'     => 'right',
	'choices'     => [
		'right' => esc_html__( 'Right', 'rey-core' ),
		'left' => esc_html__( 'Left', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'scroll_to_top__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'multicheck',
	'settings'    => 'scroll_to_top__hide_devices',
	'label'       => esc_html__( 'Select visibility', 'rey-core' ),
	'section'     => $section,
	'default'     => [],
	'priority'    => 10,
	'choices'     => [
		'desktop' => esc_html__( 'Hide on desktop', 'rey-core' ),
		'tablet' => esc_html__( 'Hide on tablets', 'rey-core' ),
		'mobile' => esc_html__( 'Hide on mobile', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'scroll_to_top__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );
