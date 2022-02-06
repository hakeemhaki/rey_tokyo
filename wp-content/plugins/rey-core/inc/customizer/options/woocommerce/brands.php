<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_rey_brands';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Brands', 'rey-core'),
	'priority'       => 80,
	'panel'			=> 'woocommerce'
));

$brand = class_exists('ReyCore_WooCommerce_Brands') ? ReyCore_WooCommerce_Brands::getInstance()->brand_attribute() : '';

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'brand_taxonomy',
	'label'       => esc_html__( 'Brand Taxonomy', 'rey-core' ),
	'description' => esc_html__( 'If you\'re using product brands, choose the taxonomy. Leave default if unsure.', 'rey-core' ),
	'section'     => $section,
	'default'     => $brand,
	'choices'     => reycore_customizer__wc_taxonomies([
		'exclude' => [
			'product_cat',
			'product_tag'
		]
	]) + [$brand => esc_html__('Default', 'rey-core')],
] );

reycore_customizer__title([
	'title'       => esc_html__('Catalog display', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'loop_show_brads',
		'label'       => esc_html__( 'Enable Link', 'rey-core' ),
		'section'     => $section,
		'default'     => '1',
	] );


reycore_customizer__title([
	'title'       => esc_html__('Product Page', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'brands__pdp',
		'label'       => esc_html__( 'Brand display', 'rey-core' ),
		'section'     => $section,
		'default'     => 'link',
		'choices'     => [
			'none' => esc_html_x('Disabled', 'Customizer control choice', 'rey-core'),
			'link' => esc_html_x('Link', 'Customizer control choice', 'rey-core'),
			'image' => esc_html_x('Image', 'Customizer control choice', 'rey-core'),
			'both' => esc_html_x('Both Image and Link', 'Customizer control choice', 'rey-core')
		]
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'rey-number',
		'settings'    => 'brands__pdp_image_size',
		'label'       => esc_html__( 'Brand image size', 'rey-core' ) . ' (px)',
		'section'     => $section,
		'default'     => 80,
		'choices'     => [
			'min'  => 5,
			'max'  => 400,
			'step' => 1,
		],
		'active_callback' => [
			[
				'setting'  => 'brands__pdp',
				'operator' => 'in',
				'value'    => ['image', 'both'],
			],
		],
		'transport'   => 'auto',
		'output'      		=> [
			[
				'element'  		=> ':root',
				'property' 		=> '--pdp-brand-image-size',
				'units'    		=> 'px',
				'media_query' => '@media (min-width: 992px)'
			],
		],
	] );
