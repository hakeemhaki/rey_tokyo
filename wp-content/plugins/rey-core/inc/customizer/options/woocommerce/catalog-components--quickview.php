<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_catalog_components';


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_quickview',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('QuickView', 'rey-core'),
		__('Choose if you want the quickview button to be displayed.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
    'choices'     => [
        '2' => esc_attr__('Disabled', 'rey-core'),
		'1' => esc_attr__('Enabled - Text Button', 'rey-core'),
		'icon' => esc_attr__('Enabled - Icon Button', 'rey-core'),
    ]
));


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_quickview_style',
	'label'       => esc_html__( 'Button style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'under',
	'priority'    => 10,
	'choices'     => [
		'under' => esc_html__( 'Default (underlined)', 'rey-core' ),
		'hover' => esc_html__( 'Hover Underlined', 'rey-core' ),
		'primary' => esc_html__( 'Primary', 'rey-core' ),
		'primary-out' => esc_html__( 'Primary Outlined', 'rey-core' ),
		'clean' => esc_html__( 'Clean', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Quickview options', 'rey-core')
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_quickview_icon_type',
	'label'       => esc_html__( 'Choose Icon', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'priority'    => 10,
	'choices'     => [
		'eye' => esc_html__( 'Eye icon', 'rey-core' ),
		'dots' => esc_html__( 'Dots', 'rey-core' ),
		// 'bubble' => esc_html__( 'Bubble', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_quickview_position',
	'label'       => esc_html__( 'Button Position', 'rey-core' ),
	'section'     => $section,
	'default'     => 'bottom',
	'choices'     => [
		'bottom' => esc_html__( 'Bottom', 'rey-core' ),
		'topright' => esc_html__( 'Thumb. top right', 'rey-core' ),
		'bottomright' => esc_html__( 'Thumb. bottom right', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'loop_quickview_accent_color',
	'label'       => esc_html__( 'Accent Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .rey-quickviewBtn',
			'property' 		=> '--accent-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'loop_quickview_accent_hover_color',
	'label'       => esc_html__( 'Accent Hover Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .rey-quickviewBtn',
			'property' 		=> '--accent-hover-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'loop_quickview_accent_text_color',
	'label'       => esc_html__( 'Accent Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .rey-quickviewBtn',
			'property' 		=> '--accent-text-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_quickview__link_all',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Quickview ONLY', 'rey-core' ),
		__('This will force all links in product item, to link to the Quickview panel (desktop only).', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_quickview__link_all_hide',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Quickview ONLY - Hide button', 'rey-core' ),
		__('Enable if you want to hide Quickview button.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
		[
			'setting'  => 'loop_quickview__link_all',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

reycore_customizer__title([
	'title'       => esc_html__('Popup settings', 'rey-core'),
	'section'     => $section,
	'size'        => 'sm',
	'border'      => 'none',
	// 'upper'       => true,
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_quickview__panel_style',
	'label'       => esc_html__( 'Open effect', 'rey-core' ),
	'section'     => $section,
	'default'     => 'curtain',
	'choices'     => [
		'curtain' => esc_html__( 'Curtain', 'rey-core' ),
		'slide' => esc_html__( 'Slide up', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'loop_quickview_specifications',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Specifications Content', 'rey-core'),
		__('Choose if you want to show specifications table in quickview panel.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_quickview_gallery_type',
	'label'       => esc_html__( 'Gallery Nav. Type', 'rey-core' ),
	'section'     => $section,
	'default'     => 'vertical',
	'choices'     => [
		'vertical' => esc_html__( 'Vertical', 'rey-core' ),
		'horizontal' => esc_html__( 'Horizontal', 'rey-core' ),
		'bullets' => esc_html__( 'Bullets', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_quickview_image_fit',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Image Fit', 'rey-core' ),
		__('Contain will make sure the main gallery image is uncut, while cover will makes sure to be fit for its container.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'cover',
	'choices'     => [
		'cover' => esc_html__( 'Cover', 'rey-core' ),
		'contain' => esc_html__( 'Contain', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_quickview',
			'operator' => '!=',
			'value'    => '2',
		],
	],
	'rey_group_end' => true
] );
