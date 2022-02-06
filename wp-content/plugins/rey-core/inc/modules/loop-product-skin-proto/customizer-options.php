<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'woocommerce_product_catalog';

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'proto_loop_hover_animation',
	'label'    => reycore_customizer__title_tooltip(
		__('Hover animation', 'rey-core'),
		__('Select if products should have an animation effect on hover.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'proto',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Proto Skin Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'proto_loop_padded',
	'label'       => reycore_customizer__title_tooltip(
		__('Inner padding', 'rey-core'),
		__('Adds a surrounding padding to the product text, and enables the shadows.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'proto',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'proto_loop_text_color',
	'label'           => __( 'Text Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'proto',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-proto',
			'property' 		   => '--woocommerce-loop-proto-color',
		],
	]
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'proto_loop_link_color',
	'label'           => __( 'Link Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'proto',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-proto',
			'property' 		   => '--woocommerce-loop-proto-color-link',
		],
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'proto_loop_bg_color',
	'label'           => __( 'Background Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'proto',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-proto',
			'property' 		   => '--woocommerce-loop-proto-bgcolor',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'proto_loop_shadow',
	'label'       => esc_html__( 'Box Shadow', 'rey-core' ),
	'section'     => $section,
	'default'     => '1',
	'choices'     => [
		'' => esc_html__( 'Disabled', 'rey-core' ),
		'1' => esc_html__( 'Level 1', 'rey-core' ),
		'2' => esc_html__( 'Level 2', 'rey-core' ),
		'3' => esc_html__( 'Level 3', 'rey-core' ),
		'4' => esc_html__( 'Level 4', 'rey-core' ),
		'5' => esc_html__( 'Level 5', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'proto',
		],
		[
			'setting'  => 'proto_loop_padded',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'proto_loop_shadow_hover',
	'label'       => esc_html__( 'Hover Box Shadow', 'rey-core' ),
	'section'     => $section,
	'default'     => '3',
	'choices'     => [
		'' => esc_html__( 'Disabled', 'rey-core' ),
		'1' => esc_html__( 'Level 1', 'rey-core' ),
		'2' => esc_html__( 'Level 2', 'rey-core' ),
		'3' => esc_html__( 'Level 3', 'rey-core' ),
		'4' => esc_html__( 'Level 4', 'rey-core' ),
		'5' => esc_html__( 'Level 5', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'proto',
		],
		[
			'setting'  => 'proto_loop_padded',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );
