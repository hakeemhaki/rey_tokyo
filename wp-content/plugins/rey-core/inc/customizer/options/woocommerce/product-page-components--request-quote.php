<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_components';


/* ------------------------------------ REQUEST A QUOTE ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('Request a Quote (Send enquiry)', 'rey-core'),
	'description' => esc_html__('Add a button in product pages that opens a modal containing a contact form.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'style_attr'  => '--border-size: 3px;',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__type',
	'label'       => esc_html__( 'Select which products', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'None', 'rey-core' ),
		'all' => esc_html__( 'All products', 'rey-core' ),
		'products' => esc_html__( 'Specific products', 'rey-core' ),
		'categories' => esc_html__( 'Specific category products', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'request_quote__products',
	'label'       => esc_html__( 'Select products (comma separated)', 'rey-core' ),
	'placeholder' => esc_html__( 'eg: 100, 101, 102', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '==',
			'value'    => 'products',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__categories',
	'label'       => esc_html__( 'Select categories', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'multiple'    => 100,
	'query_args' => [
		'type' => 'terms',
		'taxonomy' => 'product_cat',
	],
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '==',
			'value'    => 'categories',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__form_type',
	'label'       => esc_html__( 'Select Form Type', 'rey-core' ),
	'section'     => $section,
	'default'     => 'cf7',
	'choices'     => [
		'cf7' => esc_html__( 'Contact Form 7', 'rey-core' ) . (! class_exists('WPCF7') ? esc_html__(' (Not installed)', 'rey-core') : ''),
		'wpforms' => esc_html__( 'WP Forms', 'rey-core' ) . (! function_exists('wpforms') ? esc_html__(' (Not installed)', 'rey-core') : ''),
	],
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__cf7',
	'label'       => esc_html__( 'Select Contact Form', 'rey-core' ),
	'description' => apply_filters('reycore/cf7/control_description', ''),
	'section'     => $section,
	'default'     => '',
	'choices'     => apply_filters('reycore/cf7/forms', []),
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
		[
			'setting'  => 'request_quote__form_type',
			'operator' => '==',
			'value'    => 'cf7',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__wpforms',
	'label'       => esc_html__( 'Select Contact Form', 'rey-core' ),
	'description' => apply_filters('reycore/wpforms/control_description', ''),
	'section'     => $section,
	'default'     => '',
	'choices'     => apply_filters('reycore/wpforms/forms', []),
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
		[
			'setting'  => 'request_quote__form_type',
			'operator' => '==',
			'value'    => 'wpforms',
		],
	],
] );



ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'request_quote__btn_text',
	'label'       => esc_html__( 'Button Text', 'rey-core' ),
	'placeholder' => esc_html__( 'eg: Request a quote', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__( 'Request a Quote', 'rey-core' ),
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'request_quote__btn_style',
	'label'       => esc_html__( 'Button Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'btn-line-active',
	'choices'     => [
		'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
		'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
		'btn-primary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
		'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
		'btn-primary-outline btn--block' => esc_html__( 'Regular outline & Full width', 'rey-core' ),
		'btn-secondary' => esc_html__( 'Regular', 'rey-core' ),
		'btn-secondary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'textarea',
	'settings'    => 'request_quote__btn_text_after',
	'label'       => esc_html__( 'Text after button', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'request_quote__var_aware',
	'label'       => reycore_customizer__title_tooltip(
		_x('Variation aware', 'Customizer Control Label', 'rey-core'),
		_x('If enabled, the button will only be clickable when a variation is selected.', 'Customizer Control Label', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'request_quote__type',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );
