<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_store_notice';


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'woocommerce_store_notice_close_style',
	'label'       => esc_html__( 'Close Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'default',
	'priority'    => 20,
	'choices'     => [
		'default' => esc_html__( 'Default', 'rey-core' ),
		'icon-inside' => esc_html__( 'Close Icon (Inside)', 'rey-core' ),
		'icon-outside' => esc_html__( 'Close Icon (Outside)', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'typography',
	'settings'    => 'woocommerce_store_notice_typography',
	'label'       => esc_attr__('Typography', 'rey-core'),
	'section'     => $section,
	'transport' => 'auto',
	'priority' => 20,
	'default'     => [
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'text-transform' => '',
		'variant' => '',
		'font-weight' => '',
	],
	'output' => [
		[
			'element' => '.woocommerce-store-notice .woocommerce-store-notice-content',
		],
	],
	'load_choices' => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'woocommerce_store_notice_text_color',
	'label'       => esc_html__( 'Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'transport' => 'auto',
	'priority' => 20,
	'choices'     => [
		'alpha' => true,
	],
	'output' => [
		[
			'element' => '.woocommerce-store-notice .woocommerce-store-notice-content',
			'property' => 'color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'woocommerce_store_notice_bg_color',
	'label'       => esc_html__( 'Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'transport' => 'auto',
	'priority' => 20,
	'choices'     => [
		'alpha' => true,
	],
	'output' => [
		[
			'element' => '.woocommerce-store-notice',
			'property' => 'background-color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'woocommerce_store_notice_height',
	'label'       => esc_html__( 'Height', 'rey-core' ),
	'section'     => $section,
	'default'     => 32,
	'priority' => 20,
	'choices'     => [
		'min'  => 0,
		'max'  => 300,
		'step' => 1,
		'suffix' => 'px'
	],
	'transport' => 'auto',
	'output' => [
		[
			'element' => '.woocommerce-store-notice .woocommerce-store-notice-content',
			'property' => 'min-height',
			'units' => 'px',
		],
	],
] );
