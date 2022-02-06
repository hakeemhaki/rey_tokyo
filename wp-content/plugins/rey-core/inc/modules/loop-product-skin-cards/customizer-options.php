<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/* ------------------------------------ Basic Skin options ------------------------------------ */

$section = 'woocommerce_product_catalog';

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'cards_loop_hover_animation',
	'label'       => esc_html__('Hover animation', 'rey-core'),
	'description' => __('Select if products should have an animation effect on hover.', 'rey-core'),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'cards',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Cards Skin Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'cards_loop_inner_padding',
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
			'value'    => 'cards',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-loop-cards-padding',
			'units' 		   => 'px',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'cards_loop_border_color',
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
			'value'    => 'cards',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-loop-cards-bordercolor',
		],
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'cards_loop_bg_color',
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
			'value'    => 'cards',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-loop-cards-bgcolor',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'cards_loop_expand_thumbnails',
	'label'       => esc_html__('Expand thumbnails?', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'cards',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'cards_loop_square_corners',
	'label'       => esc_html__('Use Square Corners?', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_skin',
			'operator' => '==',
			'value'    => 'cards',
		],
	],
	'rey_group_end' => true
] );
