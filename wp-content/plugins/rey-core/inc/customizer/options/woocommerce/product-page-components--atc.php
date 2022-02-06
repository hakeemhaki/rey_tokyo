<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------ ADD TO CART STUFF ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('Add To Cart', 'rey-core'),
	'description' => esc_html__('Adding to cart functionalities.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'style_attr'  => '--border-size: 3px;',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_page_ajax_add_to_cart',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Ajax Add to Cart', 'rey-core' ),
		__('This option will enable the Ajax add to cart functionality. WooCommerce doesn\'t have this option built-in, so Rey\'s implementation might not be compatible with a certain plugin you\'re using, so it would be best to keep it disabled.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'yes',
	'choices'     => array(
        'yes' => esc_attr__('Yes', 'rey-core'),
        'no' => esc_attr__('No', 'rey-core')
	),
	'input_attrs' => [
		'data-control-class' => '--separator-bottom'
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_page_after_add_to_cart_behviour',
	'label'       => esc_html__( 'After "Added To Cart" Behaviour', 'rey-core' ),
	'section'     => $section,
	'default'     => 'cart',
	'choices'     => [
		'' => esc_html__( 'Do nothing', 'rey-core' ),
		'cart' => esc_html__( 'Open Cart Panel', 'rey-core' ),
		'checkout' => esc_html__( 'Redirect to Checkout', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'product_page_ajax_add_to_cart',
			'operator' => '==',
			'value'    => 'yes',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'enable_text_before_add_to_cart',
	'label'       => esc_html__( 'Enable text Before "Add to cart" Button', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	]
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'editor',
	'settings'    => 'text_before_add_to_cart',
	// 'label'       => esc_html__( 'Add text or shortcodes', 'rey-core' ),
	'label'       => '',
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'enable_text_before_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Add text or shortcodes', 'rey-core' ),
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'enable_text_after_add_to_cart',
	'label'       => esc_html__( 'Enable text After "Add to cart" Button', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'editor',
	'settings'    => 'text_after_add_to_cart',
	'label'       => esc_html__( 'Add text or shortcodes.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'enable_text_after_add_to_cart',
			'operator' => '==',
			'value'    => true,
			],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Add text or shortcodes', 'rey-core' ),
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single_atc_qty_controls_styles',
	'label'       => esc_html__( 'Quantity Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'default',
	'choices'     => [
		'default' => esc_html__( 'Default', 'rey-core' ),
		'basic' => esc_html__( 'Basic', 'rey-core' ),
		'select' => esc_html__( 'Select Box', 'rey-core' ),
		'disabled' => esc_html__( 'Disabled', 'rey-core' ),
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_atc_qty_controls',
	'label'       => esc_html__( 'Enable Quantity "+ -" controls', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'single_atc_qty_controls_styles',
			'operator' => '!=',
			'value'    => 'select',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'single_atc__color_bg',
	'label'       => esc_html__( 'Button Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'transport'   		=> 'auto',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> '.woocommerce .rey-cartBtnQty',
			'property' 		=> '--accent-color',
		]
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'single_atc__color_text',
	'label'       => esc_html__( 'Button Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'transport'   		=> 'auto',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> '.woocommerce .rey-cartBtnQty',
			'property' 		=> '--accent-text-color',
		]
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'single_atc__color_text_hover',
	'label'       => esc_html__( 'Button Hover Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'transport'   		=> 'auto',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> '.woocommerce .rey-cartBtnQty .button.single_add_to_cart_button:hover',
			'property' 		=> 'color',
		]
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'single_atc__color_bg_hover',
	'label'       => esc_html__( 'Button Hover Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'transport'   		=> 'auto',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> '.woocommerce .rey-cartBtnQty .button.single_add_to_cart_button:hover',
			'property' 		=> 'background-color',
		]
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'single_atc__text',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Button Text', 'rey-core' ),
		esc_html__( 'Change button text. Use 0 to disable it.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Add to cart', 'rey-core'),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'select',
	'settings' => 'single_atc__icon',
	'label'    => esc_html_x( 'Button Icon', 'Customizer control label', 'rey-core' ),
	'section'  => $section,
	'default'  => '',
	'choices'  => [
		''        => esc_html__( 'No Icon', 'rey-core' ),
		'bag'     => esc_html__( 'Shopping Bag', 'rey-core' ),
		'bag2'    => esc_html__( 'Shopping Bag 2', 'rey-core' ),
		'bag3'    => esc_html__( 'Shopping Bag 3', 'rey-core' ),
		'basket'  => esc_html__( 'Shopping Basket', 'rey-core' ),
		'basket2' => esc_html__( 'Shopping Basket 2', 'rey-core' ),
		'cart'    => esc_html__( 'Shopping Cart', 'rey-core' ),
		'cart2'   => esc_html__( 'Shopping Cart 2', 'rey-core' ),
		'cart3'   => esc_html__( 'Shopping Cart 3', 'rey-core' ),
	],
] );

// ReyCoreKirki::add_field( 'rey_core_kirki', [
// 	'type'        => 'text',
// 	'settings'    => 'single_atc__text_backorders',
// 	'label'       => esc_html__( 'Backorders - Button Text', 'rey-core' ),
// 	'section'     => $section,
// 	'default'     => '',
// 	'input_attrs'     => [
// 		'placeholder' => esc_html__('eg: Pre-order', 'rey-core'),
// 	],
// ] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_atc__stretch',
	'label'       => esc_html__( 'Stretch Button', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

do_action('reycore/customizer/after_single_atc_fields', $section);
