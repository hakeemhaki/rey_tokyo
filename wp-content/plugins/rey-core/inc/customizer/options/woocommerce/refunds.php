<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_rey_refunds';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Refunds', 'rey-core'),
	'priority'       => 90,
	'panel'			=> 'woocommerce'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'refunds__enable',
	'label'       => esc_html__( 'Enable Refunds', 'rey-core' ),
	'description'       => esc_html__( 'Once enabled, a new page in your Account dashboard should be visible. This page contains a contact form which will help customers choose a product from a specific order, to ask for a refund. ', 'rey-core' ) . sprintf(__('<br><strong>Note:</strong> <a href="%s" target="_blank">Resave Permalinks</a> or it will give 404 error.', 'rey-core'), admin_url('options-permalink.php')),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'refunds__menu_text',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Menu text', 'rey-core'),
		__('This text will show up in the My Account dashboard menu.', 'rey-core')
	),
	'section'     => $section,
	'default'     => esc_html__('Request Return', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'refunds__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'refunds__page_title',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Page Title', 'rey-core'),
		__('This page title show up in the My Account dashboard menu - Returns page.', 'rey-core')
	),
	'section'     => $section,
	'default'     => esc_html__('Request Return', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'refunds__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'editor',
	'settings'    => 'refunds__content',
	'label'       => esc_html__( 'Content before form', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'refunds__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );
