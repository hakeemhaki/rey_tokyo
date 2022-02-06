<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_rey_advanced_settings';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Advanced Options', 'rey-core'),
	'priority'       => 100,
	'panel'			=> 'woocommerce'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'fix_variable_product_prices',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Fix Variables Sale Prices Display', 'rey-core' ),
		esc_html__( 'If a Variation product sale prices is different than the others, WooCommerce will not display the proper pricing stucture.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
] );

reycore_customizer__separator([
	'section' => $section,
	'id'      => 'a1',
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'custom_price_range',
	'label'       => esc_html__( 'Variable prices range format', 'rey-core' ),
	'description' => esc_html__( 'Add if you want to have a custom format for the price range in variable products. You can add a prefix or suffix, or use {{min} or {{max}} variables.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: {{min} - {{max}}', 'rey-core'),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'wpr__use_defaults',
	'label'       => esc_html__( 'WooCommerce Photo Reviews - Override Settings', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );
