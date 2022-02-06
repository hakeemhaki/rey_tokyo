<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_components';

/**
 * PRODUCT PAGE - components settings
 */

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Product Page - Components', 'rey-core'),
	'priority'       => 15,
	'panel'			=> 'woocommerce'
));

reycore_customizer__title([
    'title'       => esc_html__('General Components', 'rey-core'),
	'description' => esc_html__('Control what components to show or hide in the product page.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'single_breadcrumbs',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Breadcrumbs', 'rey-core'),
		__('Enable or disable the breadcrumbs and customize wether it should display the Home button.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'yes_hide_home',
    'choices'     => array(
        'yes' => esc_attr__('Yes & Show Home', 'rey-core'),
        'yes_hide_home' => esc_attr__('Yes & Hide Home', 'rey-core'),
        'no' => esc_attr__('Hide', 'rey-core')
    ),
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'product_navigation',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Navigation', 'rey-core'),
		__('Select the visibility of the navigation.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
    'choices'     => [
		'2' => esc_html__('Disabled', 'rey-core'),
        '1' => esc_html__('Compact', 'rey-core'),
        'extended' => esc_html__('Extended', 'rey-core'),
        'full' => esc_html__('Full', 'rey-core'),
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_navigation_same_term',
	'label'       => esc_html__( 'Navigate only the same category', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'product_navigation',
			'operator' => '!=',
			'value'    => '2',
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Navigation options', 'rey-core')
	],
	'rey_group_end' => true,
] );


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'single_product_price',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Product Price', 'rey-core'),
		__('Select the visibility of the price.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
));

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'typography',
	'settings'    => 'single_product_price_typo',
	'label'       => esc_attr__('Price Typo.', 'rey-core'),
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
			'element' => '.woocommerce div.product p.price',
		],
	],
	'load_choices' => true,
	'transport' => 'auto',
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Price block options', 'rey-core')
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'single_product_price_color',
	'label'       => esc_html__( 'Price Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce div.product p.price',
			'property' 		   => 'color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_discount_badge_v2',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Discount badge', 'rey-core'),
		__('Select if the discount badge should be displayed.', 'rey-core')
	),
	'section'     => $section,
	'default'     => reycore__get_fallback_mod('', true, [
		'mod' => 'single_discount_badge', // since 1.6.4
		'value' => '1',
		'compare' => '===',
	] ),
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single_product_price_text_type',
	'label'       => esc_html__( 'Text near price', 'rey-core' ),
	'section'     => $section,
	'default'     => 'no',
	'choices' => [
		'no' => esc_html__('Disabled', 'rey-core'),
		'custom_text' => esc_html__('Custom Text', 'rey-core'),
		'free_shipping' => esc_html__('Free Shipping (if available)', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'data-control-class' => '--separator-top',
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'single_product_price_text_custom',
	'label'    => esc_html__('Text to show', 'rey-core'),
	'section'  => $section,
	'default'  => esc_html__('Free Shipping!', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'single_product_price_text_type',
			'operator' => '!=',
			'value'    => 'no',
		],
	],
	'input_attrs' => [
		'placeholder' => esc_html__('eg: Free Shipping', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_product_price_text_shipping_cost',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Minimum Order Amount', 'rey-core'),
		__('Add a Minimum Order Amount to calculate when to show this text. The calculation is [product-price + cart-total > min-order-amount]', 'rey-core')
	),
	'section'     => $section,
	'default'     => 0,
	'choices'     => [
		'min'  => 0,
		'max'  => 1000,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'single_product_price_text_type',
			'operator' => '==',
			'value'    => 'free_shipping',
		],
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'single_product_price_text_color',
	'label'       => esc_html__( 'Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'single_product_price_text_type',
			'operator' => '!=',
			'value'    => 'no',
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce div.product p.price .rey-priceText',
			'property' 		   => 'color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_product_price_text_inline',
	'label'       => esc_html__( 'Show under price?', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'single_product_price',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'single_product_price_text_type',
			'operator' => '!=',
			'value'    => 'no',
		],
	],
	'rey_group_end' => true,
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_short_desc_enabled',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Short Description', 'rey-core'),
		__('Select the visibility of the short description (excerpt).', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'product_short_desc_after_atc',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__('Move after Add To Cart', 'rey-core'),
			__('Enable if you want to reposition the short description after the Add to cart block.', 'rey-core')
		),
		'section'     => $section,
		'default'     => false,
		'active_callback' => [
			[
				'setting'  => 'product_short_desc_enabled',
				'operator' => '==',
				'value'    => true,
			],
		],
		'rey_group_start' => [
			'label'       => esc_html__( 'Options', 'rey-core' ),
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'product_short_desc_toggle_v2',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__('Toggle text', 'rey-core'),
			__('Select if you want to add a "Read more/less" button into the short description.', 'rey-core')
		),
		'section'     => $section,
		'default'     => reycore__get_fallback_mod('', false, [
			'mod' => 'product_short_desc_toggle',
			'value' => 'no',
			'compare' => '===',
		] ),
		'active_callback' => [
			[
				'setting'  => 'product_short_desc_enabled',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );

	ReyCoreKirki::add_field('rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'product_short_desc_toggle_strip_tags',
		'label'       => esc_html__('Toggle text - Strip tags?', 'rey-core'),
		'section'     => $section,
		'default'     => true,
		'active_callback' => [
			[
				'setting'  => 'product_short_desc_enabled',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'product_short_desc_toggle_v2',
				'operator' => '==',
				'value'    => true,
			],
		],
		'rey_group_end' => true
	] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page__hide_stock',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Hide Stock Information', 'rey-core' ),
		sprintf(__('Hide occourances of stock information. To control more stock settings, header over to <a href="%s" target="_blank">WooCommerce Inventory settings</a>.', 'rey-core'), admin_url('admin.php?page=wc-settings&tab=products&section=inventory')),
		[ 'clickable' => true ]
	),
	'section'     => $section,
	'default'     => false,
	'input_attrs' => [
		'data-control-class' => '--separator-top'
	],
] );


require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-components--atc.php';


/* ------------------------------------ META ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('META', 'rey-core'),
	'description' => esc_html__('Meta content after Add to cart button.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'style_attr'  => '--border-size: 3px;',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_product_meta_v2',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Product Meta', 'rey-core'),
		__('Select the visibility of product meta (<strong>SKU, categories, tags</strong>).', 'rey-core')
	),
	'section'     => $section,
	'default'     => reycore__get_fallback_mod('', true, [
		'mod' => 'single_product_meta', // since 1.6.4
		'value' => 'show',
		'compare' => '===',
	] ),
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_sku_v2',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Product SKU', 'rey-core'),
		__('Select the visibility of the product SKU.', 'rey-core')
	),
	'section'     => $section,
	'default'     => reycore__get_fallback_mod('', true, [
		'mod' => 'product_sku', // since 1.6.4 => 2.0.0
		'value' => '1',
		'compare' => '===',
	] ),
	'active_callback' => [
		[
			'setting'  => 'single_product_meta_v2',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single__product_tags',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Product Tags', 'rey-core'),
		__('Select the visibility of the product tags.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_product_meta_v2',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single__product_categories',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Product Categories', 'rey-core'),
		__('Select the visibility of the product tags.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_product_meta_v2',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


/* ------------------------------------ Reviews ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('REVIEWS (LINK IN SUMMARY)', 'rey-core'),
	// 'description' => esc_html__('Meta content after Add to cart button.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'style_attr'  => '--border-size: 3px;',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_product_reviews_after_meta',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Move Reviews after meta', 'rey-core'),
		__('If enabled, this option will move the reviews button after the product meta.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
] );


/* ------------------------------------ Variations ------------------------------------ */


// Variations notice
if( class_exists('Woo_Variation_Swatches') ):

	reycore_customizer__title([
		'section'     => $section,
		'title'    => esc_html__('VARIATION SWATCHES', 'rey-core'),
		'description' => sprintf('<span class="description customize-control-description">%s<a href="%s" target="_blank">%s</a></span>',
			esc_html__('Looking for product variation swatches options? Head over to WooCommerce Variation Swatches ', 'rey-core'),
			admin_url('admin.php?page=woo-variation-swatches-settings'),
			esc_html__('plugin options.', 'rey-core')
		),
		'border'      => 'top',
		'size'        => 'sm',
		'style_attr'  => '--border-size: 3px;',
	]);

endif;

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_product_hide_out_of_stock_variation',
	'label'       => esc_html__( 'Hide Out of Stock Variations', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-components--related.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-components--request-quote.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-components--social.php';

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-page-components',
	'section' => $section
]);
