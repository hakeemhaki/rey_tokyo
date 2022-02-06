<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_catalog_content';

ReyCoreKirki::add_section($section, [
	'title'          => esc_html__('Product Catalog - Content', 'rey-core'),
	'priority'       => 11,
	'panel'			=> 'woocommerce'
]);

reycore_customizer__title([
	'title'       => esc_html__('Product List Teasers', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

if( class_exists('ReyCore_GlobalSections') ):

	// $item_size = wc_get_default_products_per_row();

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'repeater',
		'settings'    => 'loop_teasers',
		'label'       => esc_html__('Add Teasers', 'rey-core'),
		'description' => __('Select generic global sections to be assigned in product catalog in specific locations.', 'rey-core'),
		'section'     => $section,
		'row_label' => [
			'value' => esc_html__('Global Section', 'rey-core'),
			'type'  => 'field',
			'field' => 'gs',
		],
		'button_label' => esc_html__('New teaser', 'rey-core'),
		'default'      => [],
		'fields' => [
			'gs' => [
				'type'        => 'select',
				'label'       => esc_html__('Select Global Section', 'rey-core'),
				'choices'     => ReyCore_GlobalSections::get_global_sections('generic', ['' => esc_html__('- Select -', 'rey-core')]),
			],
			'size' => [
				'type'    => 'number',
				'label'   => esc_html__('Choose Size', 'rey-core'),
				'default' => 2,
				'choices' => [
					'min'  => 1,
					'max'  => 10,
					'step' => 1,
				],
			],
			'row' => [
				'type'    => 'number',
				'label'   => esc_html__('Nth row to show in', 'rey-core'),
				'default' => 1,
				'choices' => [
					'min'  => 1,
					'max'  => 30,
					'step' => 1,
				],
			],
			'position' => [
				'type'        => 'select',
				'label'       => esc_html__('Select Position in Row', 'rey-core'),
				'default' => 'end',
				'choices'     => [
					'start' => esc_html__('Start', 'rey-core'),
					'end' => esc_html__('End', 'rey-core')
				],
			],
			'repeat' => [
				'type'        => 'select',
				'label'       => esc_html__('Repeat on each page', 'rey-core'),
				'default' => 'no',
				'choices'     => [
					'no' => esc_html__('No', 'rey-core'),
					'yes' => esc_html__('Yes', 'rey-core'),
				],
			],
			'categories' => [
				'type'        => 'select',
				'label'       => esc_html__('Assign on Categories', 'rey-core'),
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_cat',
				],
				'multiple' => 100
			],
			'shop_page' => [
				'type'        => 'select',
				'label'       => esc_html__('Show on Shop page', 'rey-core'),
				'default' => 'no',
				'choices'     => [
					'no' => esc_html__('No', 'rey-core'),
					'yes' => esc_html__('Yes', 'rey-core'),
				],
			],
			'tags_page' => [
				'type'        => 'select',
				'label'       => esc_html__('Show on Tag pages', 'rey-core'),
				'default' => 'no',
				'choices'     => [
					'no' => esc_html__('No', 'rey-core'),
					'yes' => esc_html__('Yes', 'rey-core'),
				],
			],
		],
	] );

endif;


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'loop_sidebars',
	'label'       => esc_html__('Custom Sidebars', 'rey-core'),
	'description' => __('Create custom sidebars and show them on specific archives.', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Sidebar', 'rey-core'),
		'type'  => 'field',
		'field' => 'name',
	],
	'button_label' => esc_html__('New sidebar', 'rey-core'),
	'default'      => [],
	'fields' => [

		'name' => [
			'type'        => 'text',
			'label'       => esc_html__('Sidebar name', 'rey-core'),
			'default'     => '',
		],

		'type' => [
			'type'        => 'select',
			'label'       => esc_html__('Sidebar type', 'rey-core'),
			'default'     => 'shop-sidebar',
			'choices'     => [
				'shop-sidebar' => esc_html__('Shop Sidebar', 'rey-core'),
				'filters-sidebar' => esc_html__('Filter Panel', 'rey-core'),
				'filters-top-sidebar' => esc_html__('Filter Top Bar', 'rey-core'),
			],
		],

		'categories' => [
			'type'        => 'select',
			'label'       => esc_html__('Assign on Categories', 'rey-core'),
			'query_args' => [
				'type' => 'terms',
				'taxonomy' => 'product_cat',
			],
			'multiple' => 100
		],

		'attributes' => [
			'type'        => 'select',
			'label'       => esc_html__('Assign on Attributes', 'rey-core'),
			'query_args' => [
				'type' => 'terms',
				'taxonomy' => 'all_attributes',
			],
			'multiple' => 100
		],
	],
] );
