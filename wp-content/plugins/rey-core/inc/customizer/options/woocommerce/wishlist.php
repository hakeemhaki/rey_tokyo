<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'woocommerce_rey_wishlist';

$can_show_options = !class_exists('TInvWL_Public_AddToWishlist');

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html_x('Wishlist', 'Customizer control title', 'rey-core'),
	'priority'       => 90,
	'panel'			=> 'woocommerce'
));

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'wishlist__enable',
		'label'       => esc_html__( 'Enable Wishlist', 'rey-core' ),
		'section'     => $section,
		'default'     => true,
	] );
endif;


if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'dropdown-pages',
		'settings'    => 'wishlist__default_url',
		'label'       => esc_html__( 'Wishlist page', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
		'choices' => [
			'' => esc_html__('- Select page -', 'rey-core')
		],
		'allow_addition' => true,
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'wishlist__inj_type',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__('Page content inject', 'rey-core'),
			__('Select how you want to inject the product grid into the page. If you\'re choosing Shortcode, please use <code>[rey_wishlist hide_title="no"]</code>.', 'rey-core'),
			[ 'clickable' => true ]
		),
		'section'     => $section,
		'default'     => 'override',
		'choices'     => [
			'override' => esc_html__( 'Override page', 'rey-core' ),
			'append' => esc_html__( 'Append to end of page', 'rey-core' ),
			'custom' => esc_html__( 'Add custom shortcode', 'rey-core' ),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );

endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'wishlist__icon_type',
		'label'       => esc_html__( 'Icon Type', 'rey-core' ),
		'section'     => $section,
		'default'     => 'heart',
		'choices'     => [
			'heart' => esc_html__( 'Heart', 'rey-core' ),
			'favorites' => esc_html__( 'Ribbon', 'rey-core' ),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'wishlist__after_add',
		'label'       => esc_html__( 'After add to list', 'rey-core' ),
		'section'     => $section,
		'default'     => 'notice',
		'choices'     => [
			'' => esc_html__( 'Do nothing', 'rey-core' ),
			'notice' => esc_html__( 'Show Notice', 'rey-core' ),
			// 'modal' => esc_html__( 'Show Modal with products', 'rey-core' ),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

reycore_customizer__title([
	'title'       => esc_html__('Catalog', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'active_callback' => [
		[
			'setting'  => 'wishlist__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'loop_wishlist_enable',
	'label'       => esc_html__( 'Enable button', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'wishlist__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'wishlist_loop__mobile',
	'label'       => esc_html__( 'Enable button on mobile', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'wishlist__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'loop_wishlist_enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'loop_wishlist_position',
	'label'       => esc_html__( 'Button Position', 'rey-core' ),
	'section'     => $section,
	'default'     => class_exists('ReyCore_WooCommerce_Wishlist') ? ReyCore_WooCommerce_Wishlist::catalog_default_position() : 'bottom',
	'choices'     => [
		'bottom' => esc_html__( 'Bottom', 'rey-core' ),
		'topright' => esc_html__( 'Thumb. top right', 'rey-core' ),
		'bottomright' => esc_html__( 'Thumb. bottom right', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'wishlist__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'loop_wishlist_enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Wishlist options', 'rey-core')
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'wishlist_loop__icon_style',
	'label'       => esc_html__( 'Wishlist Icon Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'minimal',
	'choices'     => [
		'minimal' => esc_html__( 'Minimal', 'rey-core' ),
		'boxed' => esc_html__( 'Boxed', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'wishlist__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'loop_wishlist_enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'loop_wishlist_position',
			'operator' => 'in',
			'value'    => ['topright', 'bottomright'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'wishlist_loop__tooltip',
	'label'       => esc_html__( 'Show tooltip', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'wishlist__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'loop_wishlist_enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );

if( $can_show_options ):
	reycore_customizer__title([
		'title'       => esc_html__('Product Page', 'rey-core'),
		'section'     => $section,
		'size'        => 'md',
		'border'      => 'top',
		'upper'       => true,
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	]);
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'wishlist_pdp__enable',
		'label'       => esc_html__( 'Enable button', 'rey-core' ),
		'section'     => $section,
		'default'     => true,
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'wishlist_pdp__wtext',
		'label'       => esc_html__( 'Text visibility', 'rey-core' ),
		'section'     => $section,
		'default'     => 'show_desktop',
		'choices' => [
			'' => esc_html__('Hide', 'rey-core'),
			'show' => esc_html__('Show', 'rey-core'),
			'show_desktop' => esc_html__('Show text on desktop only', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'wishlist_pdp__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'wishlist_pdp__tooltip',
		'label'       => esc_html__( 'Show tooltip', 'rey-core' ),
		'section'     => $section,
		'default'     => false,
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'wishlist_pdp__enable',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'wishlist_pdp__wtext',
				'operator' => '==',
				'value'    => '',
			],
		]
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'wishlist_pdp__position',
		'label'       => esc_html__( 'Button Position', 'rey-core' ),
		'section'     => $section,
		'default'     => 'inline',
		'choices'     => [
			'inline' => esc_html__( 'Inline with ATC. button', 'rey-core' ),
			'before' => esc_html__( 'Before ATC. button', 'rey-core' ),
			'after' => esc_html__( 'After ATC. button', 'rey-core' ),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'wishlist_pdp__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'wishlist_pdp__btn_style',
		'label'       => esc_html__( 'Button Style', 'rey-core' ),
		'section'     => $section,
		'default'     => 'btn-line',
		'choices'     => [
			'none' => esc_html__( 'None', 'rey-core' ),
			'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
			'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
			'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
			'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
			'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'wishlist_pdp__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;


if( $can_show_options ):
	reycore_customizer__title([
		'title'       => esc_html__('Texts', 'rey-core'),
		'section'     => $section,
		'size'        => 'md',
		'border'      => 'top',
		'upper'       => true,
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	]);
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__text',
		'label'       => esc_html__( 'Wishlist title', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'input_attrs'     => [
			'placeholder' => esc_html_x('Wishlist', 'Placeholder in Customizer control.', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__texts_add',
		'label'       => esc_html__( 'Add text', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'input_attrs'     => [
			'placeholder' => esc_html__('Add to wishlist', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__texts_rm',
		'label'       => esc_html__( 'Remove text', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'input_attrs'     => [
			'placeholder' => esc_html__('Remove from wishlist', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__texts_added',
		'label'       => esc_html__( '"Added" Notice - text', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'sanitize_callback' => 'wp_kses_post',
		'input_attrs'     => [
			'placeholder' => esc_html__('Added to wishlist!', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'wishlist__after_add',
				'operator' => '==',
				'value'    => 'notice',
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__texts_btn',
		'label'       => esc_html__( '"Added" Notice - button text', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'sanitize_callback' => 'wp_kses_post',
		'input_attrs'     => [
			'placeholder' => esc_html__('VIEW WISHLIST', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'wishlist__after_add',
				'operator' => '==',
				'value'    => 'notice',
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__texts_page_title',
		'label'       => esc_html__( 'Empty page - title', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'sanitize_callback' => 'wp_kses_post',
		'input_attrs'     => [
			'placeholder' => __('Wishlist is empty.', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__texts_page_text',
		'label'       => esc_html__( 'Empty page - text', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'sanitize_callback' => 'wp_kses_post',
		'input_attrs'     => [
			'placeholder' => __('You don\'t have any products added in your wishlist. Search and save items to your liking!', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;

if( $can_show_options ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'wishlist__texts_page_btn_text',
		'label'       => esc_html__( 'Empty page - button text', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'sanitize_callback' => 'wp_kses_post',
		'input_attrs'     => [
			'placeholder' => __('SHOP NOW', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'wishlist__enable',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );
endif;
