<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_cart_rey';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Cart Page', 'rey-core'),
	'priority'       => 30,
	'panel'			=> 'woocommerce'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'cart_checkout_hide_flat_rate_if_free_shipping',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Hide Flat rate if Free Shipping', 'rey-core' ),
		esc_html__( 'By default, even if Free Shipping is valid, the Flat rate will be shown. This option will hide it.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'cart_checkout_bar_process',
	'label'       => esc_html__( 'Enable Cart/Checkout process steps', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'cart_checkout_bar_icons',
	'label'       => esc_html__( 'Cart / Checkout Progress Bar Icons', 'rey-core' ),
	'description' => esc_html__( 'Choose what type of symbol to display on the progress block in the Cart & Checkout page.', 'rey-core' ),
	'section'     => $section,
	'default'     => 'icon',
	'priority'    => 10,
	'choices'     => [
		'icon' => esc_html__( 'Check Icons', 'rey-core' ),
		'numbers' => esc_html__( 'Numbers', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'cart_checkout_bar_process',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Extra Options', 'rey-core')
	],
] );


ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'cart_checkout_bar_text1_t',
	'label'       => esc_html__( 'Step 1 (Title)', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'cart_checkout_bar_process',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'placeholder' => 'eg: SHOPPING BAG'
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'cart_checkout_bar_text1_s',
	'label'       => esc_html__( 'Step 1 (SubTitle)', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'cart_checkout_bar_process',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'placeholder' => 'eg: View your items'
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'cart_checkout_bar_text2_t',
	'label'       => esc_html__( 'Step 2 (Title)', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'cart_checkout_bar_process',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'placeholder' => 'eg: SHIPPING AND CHECKOUT'
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'cart_checkout_bar_text2_s',
	'label'       => esc_html__( 'Step 2 (SubTitle)', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'cart_checkout_bar_process',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'placeholder' => 'eg: Enter your details'
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'cart_checkout_bar_text3_t',
	'label'       => esc_html__( 'Step 3 (Title)', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'cart_checkout_bar_process',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'placeholder' => 'eg: CONFIRMATION'
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'cart_checkout_bar_text3_s',
	'label'       => esc_html__( 'Step 3 (SubTitle)', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		[
			'setting'  => 'cart_checkout_bar_process',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'placeholder' => 'eg: Review your order'
	],
	'rey_group_end' => true
] );
