<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$section = 'layout_options';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Layout Settings', 'rey-core'),
	'priority'       => 5,
	'panel'			=> 'general_options'
));


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'custom_container_width',
	'label'       => esc_html__( 'Container Width (Desktop)', 'rey-core' ),
	'section'     => $section,
	'default'     => 'default',
	'priority'    => 10,
	'choices'     => [
		'default'   => esc_html__( 'Default', 'rey-core' ),
		'full' => esc_html__( 'Full Width', 'rey-core' ),
		'px'  => esc_html__( 'Pixels (px)', 'rey-core' ),
		'vw' => esc_html__( 'Viewport (vw)', 'rey-core' ),
		// 'pc' => esc_html__( 'Percent (%)', 'rey-core' ),
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        		=> 'slider',
	'settings'    		=> 'container_width_vw',
	'label'       		=> esc_attr__( 'Container Width (vw)', 'rey-core' ),
	'section'           => $section,
	'default'     		=> 90,
	'priority'          => 10,
	'choices'     		=> array(
		'min'  => 50,
		'max'  => 99,
		'step' => 1,
	),
	'transport'   		=> 'auto',
	'active_callback' => [
		[
			'setting'  => 'custom_container_width',
			'operator' => '==',
			'value'    => 'vw',
		],
	],
));

ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        		=> 'slider',
	'settings'    		=> 'container_width_px',
	'label'       		=> esc_attr__( 'Container Width (px)', 'rey-core' ),
	'section'           => $section,
	'default'     		=> 1440,
	'priority'          => 10,
	'choices'     		=> array(
		'min'  => 1025,
		'max'  => 2560,
		'step' => 10,
	),
	'transport'   		=> 'auto',
	'output'      		=> array(
		array(
			'element'  		=> ':root',
			'property' 		=> '--container-max-width',
			'units'    		=> 'px',
		),
	),
	'active_callback' => [
		[
			'setting'  => 'custom_container_width',
			'operator' => '==',
			'value'    => 'px',
		],
	],
));


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'dimensions',
	'settings'    => 'site_padding',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Site Padding', 'rey-core' ),
		__( 'Will add padding around the site container (<strong>including header, content and footer</strong>). Dont forget to include unit (eg: px, em, rem).', 'rey-core' )
	),
	'section'     => $section,
	'priority'    => 20,
	'default'     => [
		'padding-top'    => '',
		'padding-right'  => '',
		'padding-bottom' => '',
		'padding-left'   => '',
	],
	'choices'     => [
		'labels' => [
			'padding-top'  => esc_html__( 'Top', 'rey-core' ),
			'padding-right' => esc_html__( 'Right', 'rey-core' ),
			'padding-bottom'  => esc_html__( 'Bottom', 'rey-core' ),
			'padding-left' => esc_html__( 'Left', 'rey-core' ),
		],
	],
	'transport'   		=> 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--site',
		],
	],
	'input_attrs' => [
		'data-needs-unit' => 'px',
		'data-control-class' => 'dimensions-4-cols',
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'dimensions',
	'settings'    => 'content_padding',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Content Padding', 'rey-core' ),
		__( 'Will add padding around the <strong>content</strong> container only. Dont forget to include unit (eg: px, em, rem).', 'rey-core' )
	),
	'section'     => $section,
	'priority'    => 20,
	'default'     => [
		'padding-top'    => '',
		'padding-right'  => '',
		'padding-bottom' => '',
		'padding-left'   => '',
	],
	'choices'     => [
		'labels' => [
			'padding-top'  => esc_html__( 'Top', 'rey-core' ),
			'padding-right' => esc_html__( 'Right', 'rey-core' ),
			'padding-bottom'  => esc_html__( 'Bottom', 'rey-core' ),
			'padding-left' => esc_html__( 'Left', 'rey-core' ),
		],
	],
	'transport'   		=> 'auto',
	'output'      		=> array(
		array(
			'element'  		=> ':root',
			'property' 		=> '--content',
		),
	),
	'input_attrs' => [
		'data-needs-unit' => 'px',
		'data-control-class' => 'dimensions-4-cols',
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'accessibility__hide_btn_focus',
	'priority'    => 20,
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Hide Button Focus (Accessibility)', 'rey-core' ),
		__( 'This will hide the outlines added on buttons when focusing.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => true,
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#layout-settings',
	'section' => $section
]);
