<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'sticky_gs_section';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Sticky Global Sections', 'rey-core'),
	'priority'       => 80,
	'panel'			 => 'general_options',
));

reycore_customizer__title([
	'description' => esc_html__('Add sticky global sections in your site. For example you can build a Sticky Header which would be totally different than the site header (height, colors, background etc.).', 'rey-core') . '<br><br>',
	'section'     => $section,
	'priority'    => 10,
]);


/* ------------------------------------ TOP CONTENT ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Top Global Section', 'rey-core'),
	'description' => esc_html__('This will be positioned at the top edge of the site.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'color'       => 'accent',
	'upper'       => true,
	'priority'    => 10,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'top_sticky_gs',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Global Section', 'rey-core' ),
		reycore__header_footer_layout_desc('generic section'),
		[ 'size'=>290, 'clickable'=>true ]
	),
	'section'     => $section,
	'default'     => '',
	'priority'    => 10,
	'choices'     => class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections(['generic','header'], ['' => '- Select -']) : [],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'top_sticky_gs_offset',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Activation Offset (px)', 'rey-core' ),
		esc_html__( 'At what distance from the top edge should be displayed. Add value in pixels eg: 100 or 200px etc. or a unique id selector, eg: #my_unique_element . If empty it\'ll be triggered after the site-header exists viewport.', 'rey-core' )
	),
	'section'  => $section,
	'default'     => '',
	'priority' => 10,
	'active_callback' => [
		[
			'setting'  => 'top_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'input_attrs' => [
		'data-control-class' => '--text-md',
	],
	'rey_group_start' => [
		'label'    => esc_html__( 'Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'top_sticky_gs_color',
	'label'       => esc_html__( 'Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'priority'    => 10,
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'top_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--sticky-gs-top-color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'top_sticky_gs_bg_color',
	'label'       => esc_html__( 'Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'priority'    => 10,
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'top_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--sticky-gs-top-bg-color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'multicheck',
	'settings'    => 'top_sticky_gs_hide_devices',
	'label'       => esc_html__( 'Select visibility', 'rey-core' ),
	'section'     => $section,
	'default'     => get_theme_mod('top_sticky_gs_hide_on_mobile', true) === false ? [] : ['mobile'],
	'priority'    => 10,
	'choices'     => [
		'desktop' => esc_html__( 'Hide on desktop', 'rey-core' ),
		'tablet' => esc_html__( 'Hide on tablets', 'rey-core' ),
		'mobile' => esc_html__( 'Hide on mobile', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'top_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'top_sticky_gs_dir_only',
	'label'       => esc_html__( 'Show only when scrolling upwards', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
	'active_callback' => [
		[
			'setting'  => 'top_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'top_sticky_gs_close',
	'label'       => esc_html__( 'Add close button', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
	'active_callback' => [
		[
			'setting'  => 'top_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'input_attrs' => array(
		'data-control-class' => 'mb-3',
	),
	'rey_group_end' => true
] );


/* ------------------------------------ BOTTOM CONTENT ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Bottom Global Section', 'rey-core'),
	'description' => esc_html__('This will be positioned at the bottom edge of the site.', 'rey-core'),
	'section'     => $section,
	'border'      => 'top',
	'size'        => 'md',
	'color'       => 'accent',
	'upper'       => true,
	'priority'    => 10,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'bottom_sticky_gs',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Global Section', 'rey-core' ),
		reycore__header_footer_layout_desc('generic section'),
		[ 'size'=>290, 'clickable'=>true ]
	),
	'section'     => $section,
	'default'     => '',
	'priority'    => 10,
	'choices'     => class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections(['generic','footer'], ['' => '- Select -']) : [],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'bottom_sticky_gs_always_visible',
	'label'       => esc_html__( 'Always visible', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
	'active_callback' => [
		[
			'setting'  => 'bottom_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'rey_group_start' => [
		'label'    => esc_html__( 'Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'bottom_sticky_gs_offset',
	'section'  => $section,
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Activation Offset (px)', 'rey-core' ),
		esc_html__( 'At what distance from the top edge should be displayed. Add value in pixels eg: 100 or 200px etc. or a unique id selector, eg: #my_unique_element . If empty it\'ll be triggered after the site-header exists viewport.', 'rey-core' )
	),
	'default'     => '',
	'priority' => 10,
	'active_callback' => [
		[
			'setting'  => 'bottom_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
		[
			'setting'  => 'bottom_sticky_gs_always_visible',
			'operator' => '==',
			'value'    => false,
		],
	],
	'input_attrs' => [
		'data-control-class' => '--text-md',
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'multicheck',
	'settings'    => 'bottom_sticky_gs_hide_devices',
	'label'       => esc_html__( 'Select visibility', 'rey-core' ),
	'section'     => $section,
	'default'     => get_theme_mod('bottom_sticky_gs_hide_on_mobile', true) === false ? [] : ['mobile'],
	'priority'    => 10,
	'choices'     => [
		'desktop' => esc_html__( 'Hide on desktop', 'rey-core' ),
		'tablet' => esc_html__( 'Hide on tablets', 'rey-core' ),
		'mobile' => esc_html__( 'Hide on mobile', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'bottom_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'bottom_sticky_gs_dir_only',
	'label'       => esc_html__( 'Show only when scrolling upwards', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
	'active_callback' => [
		[
			'setting'  => 'bottom_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'bottom_sticky_gs_close',
	'label'       => esc_html__( 'Add close button', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
	'active_callback' => [
		[
			'setting'  => 'bottom_sticky_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'rey_group_end' => true
] );


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#sticky-global-sections',
	'section' => $section
]);
