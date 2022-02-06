<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'header_cart_options';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Shopping Cart', 'rey-core'),
	'priority'       => 60,
	'panel'			 => 'header_options'
));

$default_header_conditions = [
	'setting'  => 'header_layout_type',
	'operator' => '==',
	'value'    => 'default',
];

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_enable_cart',
	'label'       => esc_html__( 'Enable Shopping Cart?', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		$default_header_conditions,
	],
] );

reycore_customizer__title([
	'title'       => esc_html__('Cart Button', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'bottom',
	'upper'       => true,
]);

$header_type__is_default = get_theme_mod('header_layout_type', 'default') === 'default';

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart_layout',
	'label'    => reycore_customizer__title_tooltip(
		esc_html__( 'Cart Layout', 'rey-core' ),
		(! $header_type__is_default ? esc_html__('In case this option is not working, please check the "Header - Cart" element from the current Header Global Section , and edit its settings.', 'rey-core') : '')
	),
	'section'     => $section,
	'default'     => 'bag',
	'choices'     => [
		'bag' => esc_html__( 'Icon - Shopping Bag', 'rey-core' ),
		'bag2' => esc_html__( 'Icon - Shopping Bag 2', 'rey-core' ),
		'bag3' => esc_html__( 'Icon - Shopping Bag 3', 'rey-core' ),
		'basket' => esc_html__( 'Icon - Shopping Basket', 'rey-core' ),
		'basket2' => esc_html__( 'Icon - Shopping Basket 2', 'rey-core' ),
		'cart' => esc_html__( 'Icon - Shopping Cart', 'rey-core' ),
		'cart2' => esc_html__( 'Icon - Shopping Cart 2', 'rey-core' ),
		'cart3' => esc_html__( 'Icon - Shopping Cart 3', 'rey-core' ),
		'text' => esc_html__( 'Text (deprecated)', 'rey-core' ),
		'disabled' => esc_html__( 'No Icon', 'rey-core' ),
	],
	// 'active_callback' => [
	// 	[
	// 		'setting'  => 'header_enable_cart',
	// 		'operator' => '==',
	// 		'value'    => true,
	// 	],
	// ],
] );

// TODO: Remove in 2.0
ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'header_cart_text',
	'label'    => reycore_customizer__title_tooltip(
		esc_html__( 'Cart Text', 'rey-core' ),
		esc_html__( 'Use {{total}} string to add the cart totals.', 'rey-core' ) .( ! $header_type__is_default ? '<br>' . esc_html__('In case this option is not working, please check the "Header - Cart" element from the current Header Global Section , and edit its settings.', 'rey-core') : '')
	),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_cart',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_cart_layout',
			'operator' => '==',
			'value'    => 'text',
		],
	],
	'input_attrs' => [
		'placeholder' => esc_html__( 'eg: CART', 'rey-core' )
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'header_cart_text_v2',
	'label'    => reycore_customizer__title_tooltip(
		esc_html__( 'Cart Text', 'rey-core' ),
		esc_html__( 'Use {{total}} string to add the cart totals.', 'rey-core' ) .( ! $header_type__is_default ? '<br>' . esc_html__('In case this option is not working, please check the "Header - Cart" element from the current Header Global Section , and edit its settings.', 'rey-core') : '')
	),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_cart',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		// TODO: remove in v2.0
		// added this to make sure there aren't 2 text fields
		[
			'setting'  => 'header_cart_layout',
			'operator' => '!=',
			'value'    => 'text',
		],
	],
	'input_attrs' => [
		'placeholder' => esc_html__( 'eg: CART', 'rey-core' )
	]
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart_hide_empty',
	'label'    => reycore_customizer__title_tooltip(
		esc_html__( 'Hide empty cart?', 'rey-core' ),
		esc_html__( 'Will hide the cart icon if no products in cart.', 'rey-core' ) .( ! $header_type__is_default ? '<br>' . esc_html__('In case this option is not working, please check the "Header - Cart" element from the current Header Global Section , and edit its settings.', 'rey-core') : '')
	),
	'section'     => $section,
	'default'     => 'no',
	'choices'     => [
		'yes' => esc_html__( 'Yes', 'rey-core' ),
		'no' => esc_html__( 'No', 'rey-core' ),
	],
] );

/* ------------------------------------ PANEL ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Cart Panel', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'bottom',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__panel_disable',
	'label'       => esc_html__( 'Disable Cart Panel', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart__title',
	'label'       => esc_html__( 'Panel Title', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Shopping Bag', 'rey-core'),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart__panel_width',
	'label'       => esc_html__( 'Panel Width Type', 'rey-core' ),
	'section'     => $section,
	'default'     => 'default',
	'choices'     => [
		'default'   => esc_html__( 'Default', 'rey-core' ),
		'px'  => esc_html__( 'Custom in Pixels (px)', 'rey-core' ),
		'vw' => esc_html__( 'Custom in Viewport (vw)', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        		=> 'rey-number',
	'settings'    		=> 'header_cart__panel_width__vw',
	'label'       		=> esc_attr__( 'Panel Width (vw)', 'rey-core' ),
	'section'           => $section,
	'default'     		=> 90,
	'choices'     		=> [
		'min'  => 10,
		'max'  => 100,
		'step' => 1,
	],
	'transport'   		=> 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-cart-width',
			'units'    		=> 'vw',
		]
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__panel_width',
			'operator' => '==',
			'value'    => 'vw',
		],
	],
	'responsive' => true
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        		=> 'rey-number',
	'settings'    		=> 'header_cart__panel_width__px',
	'label'       		=> esc_attr__( 'Panel Width (px)', 'rey-core' ),
	'section'           => $section,
	'default'     		=> 470,
	'choices'     		=> array(
		'min'  => 200,
		'max'  => 2560,
		'step' => 10,
	),
	'transport'   		=> 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-cart-width',
			'units'    		=> 'px',
		]
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__panel_width',
			'operator' => '==',
			'value'    => 'px',
		],
	],
	'responsive' => true
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'header_cart__bg_color',
	'label'       => esc_html__( 'Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   		=> 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-cart-bgcolor',
		]
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart__text_theme',
	'label'       => esc_html__( 'Text color theme', 'rey-core' ),
	'section'     => $section,
	'default'     => 'def',
	'choices'     => [
		'def' => esc_html__( 'Default', 'rey-core' ),
		'light' => esc_html__( 'Light', 'rey-core' ),
		'dark' => esc_html__( 'Dark', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__btns_inline',
	'label'       => esc_html__( 'Cart/Checkout Buttons inline', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__btn_cart__enable',
	'label'       => esc_html__( 'Enable Cart Button', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart__btn_cart__text',
	'label'       => esc_html__( 'Cart Button Text', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html_x('eg: View Cart', 'Customizer control placeholder text.', 'rey-core'),
	],
	'rey_group_start' => [
		'label'    => esc_html__( 'Options', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__btn_cart__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'header_cart__btn_cart__color',
	'label'       => esc_html__( 'Cart Button Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.rey-cartPanel .woocommerce-mini-cart__buttons .button--cart',
			'property' 		=> 'color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__btn_cart__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'header_cart__btn_cart__bg',
	'label'       => esc_html__( 'Cart Button BG. Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.rey-cartPanel .woocommerce-mini-cart__buttons .button--cart',
			'property' 		=> 'background-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__btn_cart__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__recent',
	'label'    => reycore_customizer__title_tooltip(
		esc_html__( 'Recently viewed products', 'rey-core' ),
		esc_html__( 'This will show up a list of 10 of the most recently viewed products.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__close_extend',
	'label'    => reycore_customizer__title_tooltip(
		esc_html__( 'Extend closing triggers', 'rey-core' ),
		esc_html__( 'This extends the close button as well as add a custom Continue shoppings button.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart__close_text',
	'label'       => esc_html__( 'Close Button Text', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: CLOSE', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__close_extend',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label'    => esc_html__( 'Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__continue_shop',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Add "Continue Shopping" Button', 'rey-core' ),
		esc_html__('Adds a Continue Shopping button after the products', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'header_cart__close_extend',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );

reycore_customizer__separator([
	'section' => $section,
	'id'      => 'c1',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_cart_gs',
	'label'       => esc_html__( 'Empty Cart Content', 'rey-core' ),
	'description' => esc_html__( 'Add custom Elementor content into the Cart Panel if no products are added into it.', 'rey-core' ),
	'section'     => $section,
	'default'     => 'none',
	'choices'     => class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections('generic', ['none' => '- None -']) : [],
	'active_callback' => [
		[
			'setting'  => 'header_cart_hide_empty',
			'operator' => '==',
			'value'    => 'no',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_show_shipping',
	'label'       => esc_html__( 'Show Shipping under subtotal', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_show_qty',
	'label'       => esc_html__( 'Show quantity controls', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_show_subtotal',
	'label'       => esc_html__( 'Show items subtotal', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_shipping_bar__enable',
	'label'       => esc_html__( 'Show "Free Shipping" bar', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart_shipping_bar__text',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Text', 'rey-core' ),
		__('Override the text. Use <code>{{diff}}</code> to add the difference amount.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('You\'re only {{diff}} away from free shipping.', 'rey-core'),
		'data-control-class' => '--text-lg',
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart_shipping_bar__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Options', 'rey-core')
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_shipping_bar__show_over',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Show over threshold?', 'Customizer control text', 'rey-core' ),
		esc_html_x('Show the bar when it reaches over the minimum amount threshold?', 'Customizer control text', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'header_cart_shipping_bar__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart_shipping_bar__show_over_text',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Text (Over Threshold)', 'rey-core' ),
		esc_html_x('Override the text when the bar reaches over the minimum amount threshold.', 'Customizer control text', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => '',
		'data-control-class' => '--text-md',
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart_shipping_bar__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_cart_shipping_bar__show_over',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_shipping_bar__cart_page',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Add to Cart page', 'Customizer control text', 'rey-core' ),
		esc_html_x('If enabled will display on Cart page too.', 'Customizer control text', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'header_cart_shipping_bar__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart_shipping_bar__min',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Manual input value', 'Customizer control text', 'rey-core' ),
		esc_html_x('By default minimum free shipping value gets automatically calculated, however you can manually override it, but please know it\'s only for display, it won\'t change your shipping costs.', 'Customizer control text', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html_x('eg: 20', 'Customizer control text', 'rey-core'),
		'data-control-class' => '--text-md',
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart_shipping_bar__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart_coupon',
	'label'       => esc_html__( 'Coupon Code form', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );


/* ------------------------------------ CROSS-SELLS ------------------------------------ */

reycore_customizer__title([
	'title'   => esc_html__('CROSS-SELLS', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'border'  => 'top',
	'description' => esc_html__( 'Cross-sells are manually picked products that are shown when a user adds a product to cart. To pick cross-sells, edit any product and edit their Linked products.', 'rey-core' ),
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart__cross_sells_btn_text',
	'label'       => esc_html__( 'Button text', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => __( 'eg: Add to order', 'woocommerce' ),
	],
] );

/* ------------------------------------ CROSS-SELLS BUBBLE ------------------------------------ */

reycore_customizer__title([
	'title'   => esc_html__('SIDE BUBBLE', 'rey-core'),
	'section' => $section,
	'size'    => 'xs',
	'border'  => 'top',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__cross_sells_bubble',
	'label'       => esc_html__( 'Enable', 'rey-core' ),
	'description' => esc_html__( 'After a product has been added to cart, a bubble with the products Cross-sells items will be displayed.', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart__cross_sells_bubble_title',
	'label'       => esc_html__( 'Title', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => __( 'You may also like&hellip;', 'woocommerce' ),
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__cross_sells_bubble',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'header_cart__cross_sells_bubble_limit',
	'label'       => esc_html__( 'Products Limit', 'rey-core' ),
	'section'     => $section,
	'default'     => 3,
	'choices'     => [
		'min'  => 1,
		'max'  => 20,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__cross_sells_bubble',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


/* ------------------------------------ CROSS-SELLS CAROUSEL ------------------------------------ */

reycore_customizer__title([
	'title'   => esc_html__('CROSS-SELLS CAROUSEL', 'rey-core'),
	'section' => $section,
	'size'    => 'xs',
	'border'  => 'top',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__cross_sells_carousel',
	'label'       => esc_html__( 'Enable Carousel', 'rey-core' ),
	'description' => esc_html__( 'This will display a carousel containing all the cross-sells products linked to the products in the cart.', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_cart__cross_sells_carousel_title',
	'label'       => esc_html__( 'Title', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => __( 'You may also like&hellip;', 'woocommerce' ),
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__cross_sells_carousel',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'header_cart__cross_sells_carousel_limit',
	'label'       => esc_html__( 'Products Limit', 'rey-core' ),
	'section'     => $section,
	'default'     => 10,
	'choices'     => [
		'min'  => 1,
		'max'  => 20,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'header_cart__cross_sells_carousel',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_cart__cross_sells_carousel_mobile',
	'label'       => esc_html__( 'Show only on Mobile', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'header_cart__cross_sells_carousel',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-header-settings/#shopping-cart',
	'section' => $section
]);

/** WooCommerce placeholder section */

ReyCoreKirki::add_section('header_cart_options_woo', array(
    'title'          => esc_attr__('Cart Panel (Header)', 'rey-core'),
	'priority'       => 60,
	'panel'			 => 'woocommerce'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'custom',
	'settings'    => 'header_cart_options_woo_placeholder',
	'section'     => 'header_cart_options_woo',
	'priority'    => 10,
	'default'     => '',
] );
