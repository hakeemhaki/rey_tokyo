<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_product_catalog_components';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Product Catalog - Components', 'rey-core'),
	'priority'       => 11,
	'panel'			=> 'woocommerce'
));

/* ------------------------------------ GENERAL COMPONENTS ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('General components', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_add_to_cart',
	'label'       => esc_html__( 'Enable Add to Cart button', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_ajax_variable_products',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Ajax show variations', 'rey-core' ),
		__('If enabled, variable products with "Select Options" button, will show the variations form on click.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Add to cart button options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_add_to_cart_style',
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
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'loop_add_to_cart_accent_color',
	'label'       => esc_html__( 'Accent Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .button, .tinvwl-loop-button-wrapper, .rey-loopQty',
			'property' 		=> '--accent-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'loop_add_to_cart_accent_hover_color',
	'label'       => esc_html__( 'Accent Hover Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .button, .tinvwl-loop-button-wrapper, .rey-loopQty',
			'property' 		=> '--accent-hover-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'loop_add_to_cart_accent_text_color',
	'label'       => esc_html__( 'Accent Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> '.woocommerce ul.products li.product .rey-productInner .button, .tinvwl-loop-button-wrapper, .rey-loopQty',
			'property' 		=> '--accent-text-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'loop_atc__text',
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
	'settings' => 'loop_atc__icon',
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

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_add_to_cart_mobile',
	'label'       => esc_html__( 'Show button on mobiles', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_supports_qty',
	'label'       => esc_html__( 'Show quantity controls', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_add_to_cart',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
	'rey_group_end' => true
] );



require REY_CORE_DIR . 'inc/customizer/options/woocommerce/catalog-components--quickview.php';


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_show_categories',
    'label'       => esc_html__('Category', 'rey-core'),
    'description' => __('Choose if you want to display product categories.', 'rey-core'),
	'section'     => $section,
	'default'     => '2',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_categories__exclude_parents',
	'label'       => esc_html__( 'Exclude parent categories', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_show_categories',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'rey_group_start' => [
		'label'  => esc_html__('Options', 'rey-core'),
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_ratings',
    'label'       => esc_html__('Ratings', 'rey-core'),
    'description' => __('Choose if you want ratings to be displayed.', 'rey-core'),
	'section'     => $section,
	'default'     => '2',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_short_desc',
    'label'       => esc_html__('Short description', 'rey-core'),
    'description' => __('Choose if you want to show the product excerpt.', 'rey-core'),
	'section'     => $section,
	'default'     => '2',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_new_badge',
    'label'       => esc_html__('New Badge', 'rey-core'),
    'description' => __('Choose if you want to show a "New" badge on products newer than 30 days.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'loop_short_desc_limit',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Limit words', 'rey-core' ),
		esc_html__( 'Limit the number of words. 0 means full, not truncated.', 'rey-core' )
	),
	'section'  => $section,
	'default'  => 8,
	'active_callback' => [
		[
			'setting'  => 'loop_short_desc',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'input_attrs' => [
		'data-control-class' => 'eg: 8'
	],
	'rey_group_start' => [
		'label' => esc_html__('Excerpt options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_short_desc_mobile',
	'label'       => esc_html__( 'Show on mobiles', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'loop_short_desc',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_sold_out_badge',
    'label'       => esc_html__('Sold Out Badge', 'rey-core'),
    'description' => __('Choose if you want to show a "SOLD OUT" badge on products out of stock.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        'in-stock' => esc_attr__('Show - In Stock', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_featured_badge',
	'label'       => esc_html__( 'Featured Badge', 'rey-core' ),
	'section'     => $section,
	'default'     => 'hide',
	'choices'     => [
		'show' => esc_html__( 'Show', 'rey-core' ),
		'hide' => esc_html__( 'Hide', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'loop_featured_badge__text',
	'label'       => esc_html__( 'Text', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__('FEATURED', 'rey-core'),
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: FEATURED', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_featured_badge',
			'operator' => '==',
			'value'    => 'show',
			],
	],
	'rey_group_start' => [
		'label' => esc_html__('Badge settings', 'rey-core')
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_product_count',
	'label'       => esc_html__( 'Product Count text', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'loop_product_count__text',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Custom Text', 'rey-core'),
		__('You can use variables such as {{FIRST}} , {{LAST}}, {{TOTAL}}.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'loop_product_count',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Settings', 'rey-core')
	],
	'rey_group_end' => true
] );




/* ------------------------------------ PRICE & LABELS ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Price & Labels', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_show_prices',
    'label'       => esc_html__('Price', 'rey-core'),
    'description' => __('Choose if you want to hide prices.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'typography',
	'settings'    => 'loop_price_typo',
	'label'       => esc_attr__('Price typography', 'rey-core'),
	'section'     => $section,
	'default'     => [
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'font-weight' => '',
		'text-transform' => '',
		'variant' => '',
	],
	'output' => [
		[
			'element' => '.woocommerce ul.products li.product .price',
		],
	],
	'load_choices' => true,
	'transport' => 'auto',
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'rey_group_start' => [
		'label' => esc_html_x('Price options', 'Customizer control label', 'rey-core')
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'loop_price_color',
	'label'       => esc_html_x( 'Price Color', 'Customizer control label', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce ul.products li.product .price',
			'property' 		   => 'color',
		],
	],
	'rey_group_end' => true,
] );

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_show_sale_label',
    'label'       => esc_html__('Sale/Discount Label', 'rey-core'),
    'description' => __('Choose if you want to display a sale/discount label products.', 'rey-core'),
	'section'     => $section,
	'default'     => 'percentage',
    'choices'     => [
		'' => esc_attr__('Disabled', 'rey-core'),
        'sale' => esc_attr__('Sale Label (top right)', 'rey-core'),
        'percentage' => esc_attr__('Discount Percentage', 'rey-core'),
        'save' => esc_attr__('Save $$', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'loop_sale__save_text',
	'label'       => esc_html__( 'Save Text', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Save', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
		[
			'setting'  => 'loop_show_sale_label',
			'operator' => '==',
			'value'    => 'save',
		],
	],
	'rey_group_start' => [
		'label' => esc_html_x('Label options', 'Customizer control label', 'rey-core')
	]

] );


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_discount_label',
    'label'       => esc_html__('Discount Label Position', 'rey-core'),
    'description' => __('Choose the discount label position.', 'rey-core'),
	'section'     => $section,
	'default'     => 'price',
    'choices'     => array(
        'price' => esc_attr__('In Price', 'rey-core'),
        'top' => esc_attr__('Top Right', 'rey-core'),
	),
	'active_callback' => [
		[
			'setting'  => 'loop_show_sale_label',
			'operator' => '==',
			'value'    => 'percentage',
		],
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'rey-color',
	'settings'    => 'loop_discount_label_color',
	'label'       => __( 'Sale Price & Discount Color', 'rey-core' ),
	'section'     => $section,
    'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'loop_show_prices',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-discount-color',
		],
	],
	'rey_group_end' => true
));

/* ------------------------------------ VARIATION ATTRIBUTES ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Variation Attributes', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'woocommerce_loop_variation',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Attributes Type', 'rey-core'),
		__('Choose if you want to display product variation swatches into product listing, by selecting which variation should be displayed.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'disabled',
    'choices'     => array_merge([
		'disabled' => __('Disabled','rey-core')
	], class_exists('ReyCore_WooCommerce_Variations') ? ReyCore_WooCommerce_Variations::get_attributes_list() : [] )
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'woocommerce_loop_variation_force_regular',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Force display as attribute', 'rey-core' ),
		esc_html__( 'This option will force display attributes that are not used for product variations (for example attributes that are shown in Specifications block).', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'woocommerce_loop_variation_position',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Position', 'rey-core'),
		__('Choose the position of the swatches.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'after',
    'choices'     => [
		'first' => __('Before title','rey-core'),
		'before' => __('Before "Add to cart" button','rey-core'),
		'after' => __('After "Add to cart" button','rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'dimensions',
	'settings'    => 'woocommerce_loop_variation_size',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Attribute swatches sizes', 'rey-core'),
		__('Customize the sizes of the swatches.', 'rey-core')
	),
	'section'     => $section,
    'default'     => [
		'width'  => '',
		'height' => '',
	],
	'input_attrs' => [
		'data-needs-unit' => 'px',
		'placeholder' => 'eg: 10px',
	],
	'transport'   		=> 'auto',
	'output'      		=> [
		[
			'choice'      => 'width',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-swatches-width',
		],
		[
			'choice'      => 'height',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-swatches-height',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],

]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'woocommerce_loop_variation_limit',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Attributes display limit', 'rey-core'),
		__('Limit how many attributes to display. 0 is unlimited.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 0,
    'choices'     => [
		'min'  => 0,
		'max'  => 80,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'woocommerce_loop_variation',
			'operator' => '!=',
			'value'    => 'disabled',
		],
	],
]);

/* ------------------------------------ VIEW SWITCHER ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('View Switcher', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'loop_view_switcher',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('View Switcher', 'rey-core'),
		__('Choose if you want to display the products per row switcher.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
    )
));

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'loop_view_switcher_options',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'View Switcher - Columns', 'rey-core' ),
		esc_html__( 'Add columns variations, separated by comma, up to 6 columns.', 'rey-core' )
	),
	'section'  => $section,
	'default'  => esc_html__( '2, 3, 4', 'rey-core' ),
	'placeholder'  => esc_html__( 'eg: 2, 3, 4', 'rey-core' ),
	'active_callback' => [
		[
			'setting'  => 'loop_view_switcher',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'input_attrs' => [
		'data-control-class' => '--text-md'
	]
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-catalog-components',
	'section' => $section
]);
