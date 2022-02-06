<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'woocommerce_product_catalog';

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'iconized_loop_hover_animation',
	'label'       => esc_html__('Hover animation', 'rey-core'),
	'description' => __('Select if products should have an animation effect on hover.', 'rey-core'),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'iconized',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Iconized Skin Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'iconized_loop_inner_padding',
	'label'       => esc_html__( 'Content Inner padding', 'rey-core' ),
	'section'     => $section,
	'default'     => 30,
	'transport'   => 'auto',
	'choices'     => [
		'min'  => 0,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'iconized',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-iconized',
			'property' 		   => '--woocommerce-loop-iconized-padding',
			'units' 		   => 'px',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'iconized_loop_border_size',
	'label'       => esc_html__( 'Border size', 'rey-core' ),
	'section'     => $section,
	'default'     => 1,
	'choices'     => [
		'min'  => 0,
		'max'  => 200,
		'step' => 1,
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-iconized',
			'property' 		   => '--woocommerce-loop-iconized-size',
			'units' 		   => 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'iconized',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'iconized_loop_border_color',
	'label'           => __( 'Border Color', 'rey-core' ),
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
			'value'    => 'iconized',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-iconized',
			'property' 		   => '--woocommerce-loop-iconized-bordercolor',
		],
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'iconized_loop_bg_color',
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
			'value'    => 'iconized',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-iconized',
			'property' 		   => '--woocommerce-loop-iconized-bgcolor',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'iconized_loop_radius',
	'label'       => esc_html__( 'Border radius', 'rey-core' ),
	'section'     => $section,
	'default'     => 0,
	'choices'     => [
		'min'  => 0,
		'max'  => 200,
		'step' => 1,
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products.--skin-iconized',
			'property' 		   => '--woocommerce-loop-iconized-radius',
			'units' 		   => 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'iconized',
		],
	],
	'rey_group_end' => true
] );
