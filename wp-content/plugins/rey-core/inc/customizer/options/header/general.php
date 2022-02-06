<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'header_general_options';

/**
 * Header > General
 */
// General
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('General', 'rey-core'),
	'priority'       => 10,
	'panel'			 => 'header_options'
));


/**
 * Header General Settings
 */

reycore_customizer__add_field([
	'type'        => 'rey-hf-global-section',
	'settings'    => 'header_layout_type',
	'section'     => $section,
	'default'     => 'default',
	'choices'     => [
		'type' => 'header',
		'global_sections' => apply_filters('reycore/options/header_layout_options', [], false)
	],
	'label'       => esc_html__('Select Header Layout', 'rey-core')
]);


/* ------------------------------------ DEFAULT header options ------------------------------------ */


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'custom_header_width',
	'label'       => esc_html__( 'Custom Header Width', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	// 'priority'    => 20,
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
			],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Default Header Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        		=> 'slider',
	'settings'    		=> 'header_width',
	'label'       		=> esc_attr__( 'Container Width', 'rey-core' ),
	'section'           => $section,
	'default'     		=> 1440,
	// 'priority'          => 30,
	'choices'     		=> array(
		'min'  => '990',
		'max'  => '2560',
		'step' => '10',
	),
	'transport'   		=> 'auto',
	'output'      		=> array(
		array(
			'element'  		=> ':root',
			'property' 		=> '--header-default--max-width',
			'units'    		=> 'px',
		),
	),
	'active_callback' => array(
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
		[
			'setting'  => 'custom_header_width',
			'operator' => '==',
			'value'    => true,
		],
	),
));


ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        		=> 'slider',
	'settings'    		=> 'header_height',
	'label'       		=> esc_attr__( 'Header Height', 'rey-core' ),
	'section'           => $section,
	'default'     		=> 130,
	// 'priority'          => 40,
	'choices'     		=> array(
		'min'  => '20',
		'max'  => '300',
		'step' => '1',
	),
	'transport'   		=> 'auto',
	'output'      		=> array(
		array(
			'element'  		=> ':root',
			'property' 		=> '--header-default--height',
			'units'    		=> 'px',
		),
	),
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
			],
	],
	// 'input_attrs' => array(
	// 	'data-control-class' => 'mb-3',
	// ),
));


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'header_bg_color',
	'label'       => __( 'Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	// 'priority'          => 50,
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-bgcolor',
		],
		[
			'element'  		=> ':root',
			'property' 		=> '--header-default-fixed-shrinking-bg',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'header_text_color',
	'label'       => __( 'Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-text-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
			],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'custom',
	'settings'    => 'header_separator',
	'section'     => $section,
	'default'     => '<hr>',
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
			],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_separator_bar',
	'label'       => esc_html__( 'Separator bar', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	// 'priority'    => 80,
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
			],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'header_separator_bar_color',
	'label'       => esc_html__( 'Separator bar Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	// 'priority'    => 90,
	'active_callback' => array(
		array(
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
		),
		array(
			'setting'  => 'header_separator_bar',
			'operator' => '==',
			'value'    => true,
		),
	),
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-bar-color',
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_separator_bar_mobile',
	'label'       => esc_html__( 'Separator bar - Only on mobile', 'rey-core' ),
	'description' => esc_html__( 'If enabled, the separator bar will only be shown on mobiles.', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	// 'priority'    => 100,
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
		[
			'setting'  => 'header_separator_bar',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => [
		'active_callback' => [
			[
				'setting'  => 'header_layout_type',
				'operator' => '==',
				'value'    => 'default',
			],
		],
	]
] );


/* ------------------------------------ HEADER POSITION ------------------------------------ */


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_position',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Header Position', 'rey-core' ),
		sprintf(__( 'Select how the header will position itself on top. Read more about <a href="%s" target="_blank">Header Positions and Overlapping Content</a>. Please know this option can be overriden in each page individual settings eg: <a href="%s" target="_blank">Header tab</a>. ', 'rey-core' ), 'https://support.reytheme.com/kb/header-positions-and-overlapping-content/', 'https://d.pr/i/wF5UXl'),
		[ 'size'=>290, 'clickable'=>true ]
	),
	'section'     => $section,
	'default'     => 'rel',
	'choices'     => [
		'rel' => esc_html__( 'Relative', 'rey-core' ),
		'absolute' => esc_html__( 'Absolute ( Over Content )', 'rey-core' ),
		'fixed' => esc_html__( 'Fixed (Sticked to top)', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'none',
		],
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top --separator-fat'
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_fixed_overlap', // used for absolute too
	'label'       => esc_html__( 'Overlap Content?', 'rey-core' ),
	'description' => esc_html__( 'If disabled, the header will behave like "relative" header.', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'none',
		],
		[
			'setting'  => 'header_position',
			'operator' => 'in',
			'value'    => ['fixed', 'absolute'],
		],
	],
	'responsive' => true,
	'rey_group_start' => [
		'label'       => esc_html__( 'Header Position options', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_fixed_disable_mobile',
	'label'       => esc_html__( 'Disable "Fixed" on mobiles', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'none',
		],
		[
			'setting'  => 'header_position',
			'operator' => '==',
			'value'    => 'fixed',
		],
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'header_af__zindex',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Header Z-index', 'rey-core' ),
		esc_html__( 'Select a z-index for header wrapper. Choose -1 to set the current z-index to auto. Please use with caution!', 'rey-core' )
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'min'  => '',
		'max'  => 10000000,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'none',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_preloader_animation',
	'label'       => esc_html__( 'Fade-in Header on page load', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'none',
		],
		[
			'setting'  => 'site_preloader',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-header-settings/#general',
	'section' => $section,
	'end' => '<p>Or watch videos, <a href="https://youtu.be/jKRbCF8OowE" target="_blank">How to build the header</a> or <a href="https://youtu.be/XkHSB1cLIas" target="_blank">How to build a Sticky header</a>.</p>'
]);
