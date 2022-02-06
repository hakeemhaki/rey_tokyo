<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_catalog_misc';


ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Product Catalog - Misc.', 'rey-core'),
	'priority'       => 11,
	'panel'			=> 'woocommerce'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'shop_catalog',
	'label'       => esc_html__( 'Enable Catalog Mode', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 5,
	'description' => __( 'Enabling catalog mode will disable all cart functionalities.', 'rey-core' ),
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'shop_catalog_page_exclude',
	'label'       => esc_html__( 'Exclude categories from Shop Page', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'priority'    => 5,
	'multiple'    => 100,
	'query_args' => [
		'type' => 'terms',
		'taxonomy' => 'product_cat',
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top --block-label',
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'shop_hide_prices_logged_out',
	'label'    => reycore_customizer__title_tooltip(
		__('Hide prices when logged out', 'rey-core'),
		__('If enabled, product prices will be hidden when the visitor is not logged in.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'priority'    => 5,
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'shop_hide_prices_logged_out_text',
	'label'    => reycore_customizer__title_tooltip(
		__('Show custom text', 'rey-core'),
		__('Add a custom text to display instead of the prices.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'priority'    => 5,
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Login to see prices.', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'shop_hide_prices_logged_out',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'archive__title_back',
	'label'    => reycore_customizer__title_tooltip(
		__('Enable back arrow', 'rey-core'),
		__('If enabled, a back arrow will be displayed in the left side of the product archive.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'priority'    => 5,
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'archive__back_behaviour',
	'label'       => esc_html__( 'Behaviour', 'rey-core' ),
	'section'     => $section,
	'default'     => 'parent',
	'choices'     => [
		'parent' => esc_html__( 'Back to parent', 'rey-core' ),
		'shop' => esc_html__( 'Back to shop page', 'rey-core' ),
		'page' => esc_html__( 'Back to previous page', 'rey-core' ),
	],
	'priority'    => 5,
	'active_callback' => [
		[
			'setting'  => 'archive__title_back',
			'operator' => '==',
			'value'    => true,
			],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Back button options', 'rey-core' ),
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'shop_display_categories__enable',
	'label'    => reycore_customizer__title_tooltip(
		__('Enable Titles before/after Categories', 'rey-core'),
		__('Only available if both "Categories & Products" is selected. This will display heading titles before and after the categories.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'priority'    => 11,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'shop_display_categories__title_cat',
	'label'       => esc_html__( 'Category list title', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__('Shop by Category', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'shop_display_categories__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'priority'    => 11,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'shop_display_categories__title_prod',
	'label'       => esc_html__( 'Product list title', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__('Shop All %s', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'shop_display_categories__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'priority'    => 11,
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-catalog-miscellaneous',
	'section' => $section
]);
