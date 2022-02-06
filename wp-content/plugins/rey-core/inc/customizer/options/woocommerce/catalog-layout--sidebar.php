<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_catalog';

reycore_customizer__title([
	'title'       => esc_html__('Sidebar', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'catalog_sidebar_position',
	'label'       => esc_html__( 'Sidebar Position', 'rey-core' ),
	'description'       => esc_html__( 'Select the placement of the Shop Sidebar or disable it. Default is right.', 'rey-core' ),
	'section'     => $section,
	'default'     => 'right',
	'choices'     => [
		'right' => esc_html__( 'Right', 'rey-core' ),
		'left' => esc_html__( 'Left', 'rey-core' ),
		'disabled' => esc_html__( 'Disabled', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'shop_sidebar_size',
	'label'       => esc_html__( 'Sidebar Size', 'rey-core' ) . ' (%)',
	'section'     => $section,
	'default'     => 16,
	'choices'     => [
		'min'  => 10,
		'max'  => 60,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-sidebar-size',
			'units'    		=> '%',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'shop_sidebar_spacing',
	'label'       => esc_html__( 'Widget Spacing', 'rey-core' ) . ' (px)',
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		// 'min'  => 0,
		'max'  => 150,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-sidebar-widget-spacing',
			'units'    		=> 'px',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'sidebar_title_styles',
	'label'       => esc_html__( 'Custom sidebar titles styles', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'typography',
	'settings'    => 'sidebar_title_typo',
	'label'       => esc_attr__('Sidebar title', 'rey-core'),
	'section'     => $section,
	'default'     => [
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'font-weight' => '',
		'text-transform' => '',
		'variant' => '',
		'color' => '',
	],
	'output' => [
		[
			'element' => '.rey-ecommSidebar .widget-title',
		],
	],
	'load_choices' => true,
	'transport' => 'auto',
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'sidebar_title_styles',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Sidebar title options', 'rey-core')
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'sidebar_title_layouts',
	'label'       => esc_html__( 'Sidebar Title styles', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Default', 'rey-core' ),
		'bline' => esc_html__( 'Bottom Lined', 'rey-core' ),
		'sline' => esc_html__( 'Side Line', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'sidebar_title_styles',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'sidebar_shop__toggle__enable',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Toggable Widgets', 'rey-core' ),
		__('This will make the widgets inside the sidebars toggable.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'sidebar_shop__toggle__status',
	'label'       => esc_html__( 'Status', 'rey-core' ),
	'section'     => $section,
	'default'     => 'all',
	'choices'     => [
		'all' => esc_html_x('All closed', 'Customizer option choice', 'rey-core'),
		'except_first' => esc_html_x('All closed except first', 'Customizer option choice', 'rey-core')
	],
	'active_callback' => [
		[
			'setting'  => 'sidebar_shop__toggle__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Toggable widgets options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'sidebar_shop__toggle__indicator',
	'label'       => esc_html__( 'Indicator type', 'rey-core' ),
	'section'     => $section,
	'default'     => 'plusminus',
	'choices'     => [
		'plusminus' => esc_html__( 'Plus Minus', 'rey-core' ),
		'arrow' => esc_html__( 'Arrow', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'sidebar_shop__toggle__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'sidebar_shop__toggle__exclude',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Exclude IDs', 'rey-core' ),
		__('Add a list of css widget ids, separated by comma.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: #some_widget', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'sidebar_shop__toggle__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );
