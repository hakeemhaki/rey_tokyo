<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$section = 'shop_product_section_layout';

/**
 * PRODUCT PAGE - layout settings
 */

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Product Page - Layout', 'rey-core'),
	'priority'       => 15,
	'panel'			=> 'woocommerce'
));

reycore_customizer__title([
	'title'       => esc_html__('Product Page Layout', 'rey-core'),
	'description' => esc_html__('Customize the product page layout', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'single_skin',
    'label'       => esc_html__('Skin', 'rey-core'),
    'description' => __('Select the product page\'s skin (layout).', 'rey-core'),
	'section'     => $section,
	'default'     => 'default',
	// 'priority'    => 10,
	'choices'     => class_exists('ReyCore_WooCommerce_Single') ? ReyCore_WooCommerce_Single::getInstance()->get_single_skins() : [],
	'rey_preset' => 'page',
));

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single_skin__default__sidebar',
	'label'       => esc_html__( 'Choose Sidebar', 'rey-core' ),
    'description' => __('If enabled, make sure to add widgets in the "Product Page" sidebar.', 'rey-core'),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__('Disabled', 'rey-core'),
		'left' => esc_html__('Left', 'rey-core'),
		'right' => esc_html__('Right', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'default',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Default Skin Options', 'rey-core' ),
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'single_skin__default__sidebar_size',
	'label'       => esc_html__( 'Sidebar Size', 'rey-core' ),
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
			'property' 		=> '--woocommerce-pp-sidebar-size',
			'units'    		=> '%',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'default',
		],
		[
			'setting'  => 'single_skin__default__sidebar',
			'operator' => '!=',
			'value'    => '',
		],
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin__default__sidebar_mobile',
	'label'       => esc_html__( 'Hide sidebar on tablet/mobile', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'default',
		],
		[
			'setting'  => 'single_skin__default__sidebar',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'rey_group_end' => true
] );



/* ------------------------------------ Fullscreen Options ------------------------------------ */

// Stretch Gallery (for fullscreen & Cascade gallery)
ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_fullscreen_stretch_gallery',
	'label'       => esc_html__( 'Stretch Gallery (Cascade)', 'rey-core' ),
    'description' => __('This option will stretch the gallery.', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	'rey_preset' => 'page',
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => '==',
			'value'    => 'cascade',
		],
	],
	'rey_group_start' => [
		'label'       => esc_html__( 'Fullscreen Options', 'rey-core' ),
		'active_callback' => [
			[
				'setting'  => 'single_skin',
				'operator' => '==',
				'value'    => 'fullscreen',
			],
		],
	]
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'single_skin_fullscreen_gallery_color',
	'label'           => __( 'Gallery Background Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	// 'priority'        => 120,
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'       => 'single_skin',
			'operator'      => '==',
			'value'         => 'fullscreen',
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-single-fs-gallery-color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single_skin_fullscreen_valign',
	'label'       => esc_html__( 'Summary vertical alignment', 'rey-core' ),
	'section'     => $section,
	'default'     => 'flex-start',
	'choices'     => [
		'flex-start' => esc_html__( 'Top', 'rey-core' ),
		'center' => esc_html__( 'Center', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
	],
	'output' => [
		[
			'element'  => ':root',
			'property' => '--woocommerce-fullscreen-summary-valign',
		],
	],
] );


ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_fullscreen_custom_height',
	'label'       => esc_html__( 'Custom Summary Height', 'rey-core' ),
    'description' => __('This option will allow setting a custom summary height.', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	// 'priority'    => 125,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'single_skin_fullscreen_summary_height',
	'label'       => esc_html__( 'Summary Min. Height (vh)', 'rey-core' ),
	'section'     => $section,
	'default'     => 100,
	'choices'     => [
		'min'  => 35,
		'max'  => 100,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
		[
			'setting'  => 'single_skin_fullscreen_custom_height',
			'operator' => '==',
			'value'    => true,
		],
	],
	'output'      		=> [
		[
			'media_query'	=> '@media (min-width: 1025px)',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-fullscreen-gallery-height',
			'units'    		=> 'vh',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_fullscreen__header_rel_abs',
	'label'       => esc_html__( '', 'rey-core' ),
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Force "Absolute" header position', 'rey-core' ),
		esc_html__( 'This option forces the header to overlap the content.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
		[
			'setting'  => 'header_position',
			'operator' => '==',
			'value'    => 'rel',
		],
	],
] );


ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_skin_fullscreen__top_padding',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Top padding', 'rey-core' ) . ' (px)',
		esc_html__( 'Customize the top padding.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'max'  => 400,
		'step' => 1,
	],
	'transport' => 'auto',
	'output'    => [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-fullscreen-top-padding',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '==',
			'value'    => 'fullscreen',
		],
	],
	'rey_group_end' => true
] );


/* ------------------------------------ Product summary ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Product Summary', 'rey-core'),
	'description' => esc_html__('Customize the product summary block\'s layout.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
			],
	],
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_default_flip',
	'label'       => esc_html__( 'Flip Gallery & Summary', 'rey-core' ),
    'description' => __('This option will flip the positions of product summary (title, add to cart button) with the images gallery.', 'rey-core'),
	'section'     => $section,
	'default'     => false,
	// 'priority'    => 30,
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default', 'fullscreen'],
		],
	],
	'rey_preset' => 'page',
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'summary_size',
	'label'       => esc_html__( 'Summary Size', 'rey-core' ),
	'description' => __('Control the product summary content size.', 'rey-core'),
	'section'     => $section,
	'rey_preset' => 'page',
	'default'     => 36,
	'choices'     => [
		'min'  => 20,
		'max'  => 60,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-summary-size',
			'units'    		=> '%',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
		],
	],
] );


ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'summary_padding',
	'label'       => esc_html__( 'Summary Padding', 'rey-core' ),
	'description' => __('Control the product summary content padding.', 'rey-core'),
	'section'     => $section,
	'default'     => 0,
	'rey_preset' => 'page',
	'choices'     => [
		'min'  => 0,
		'max'  => 150,
		'step' => 1,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			// 'media_query'	=> '@media (min-width: 1025px)',
			'element'  		=> ':root',
			'property' 		=> '--woocommerce-summary-padding',
			'units'    		=> 'px',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default'],
		],
	],
	'responsive' => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'summary_bg_color',
	'label'           => __( 'Background Color', 'rey-core' ),
	'section'         => $section,
	// 'priority'     => 44,
	'rey_preset' => 'page',
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default', 'fullscreen'],
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-summary-bgcolor',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'            => 'rey-color',
	'settings'        => 'summary_text_color',
	'label'           => __( 'Text Color', 'rey-core' ),
	'section'         => $section,
	'default'         => '',
	'choices'         => [
		'alpha'          => true,
	],
	'transport'       => 'auto',
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => 'in',
			'value'    => ['default', 'fullscreen'],
		],
	],
	'output'          => [
		[
			'element'  		   => '.woocommerce div.product div.summary, .woocommerce div.product div.summary a, .woocommerce div.product .rey-postNav .nav-links a,  .woocommerce div.product .rey-productShare h5, .woocommerce div.product form.cart .variations label, .woocommerce div.product .product_meta, .woocommerce div.product .product_meta a',
			'property' 		   => 'color',
		],
	],
] );



ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_summary_fixed',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Fixed Summary', 'rey-core' ),
		esc_html__( 'This option will make the product summary fixed upon page scrolling, until the product gallery images are outside viewport.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'rey_preset' => 'page',
	'active_callback' => [
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
		],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'product_page_summary_fixed__offset',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Summary top distance', 'rey-core' ) . ' (px)',
		esc_html__( 'Customize the summary top margin.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => '',
	'rey_preset' => 'page',
	'choices'     => [
		'max'  => 400,
		'step' => 1,
	],
	'output'          => [
		[
			'element'  => '.--fixed-summary',
			'property' => '--woocommerce-fixedsummary-offset',
			'units'    => 'px'
		],
	],
	'active_callback' => [
		[
			'setting'  => 'product_page_summary_fixed',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Fixed summary options', 'rey-core')
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'product_page_summary_fixed__offset_active',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Offset', 'rey-core' ) . ' (px)',
		esc_html__( 'Customize the top sticky offset when page has scrolled and sticky is active.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'max'  => 400,
		'step' => 1,
	],
	'output'          => [
		[
			'element'  => ':root',
			'property' => '--woocommerce-fullscreen-top-padding-anim',
			'units'    => 'px'
		],
	],
	'active_callback' => [
		[
			'setting'  => 'product_page_summary_fixed',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_summary_fixed__gallery',
	'section'     => $section,
	'default'     => false,
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Sticky Gallery', 'rey-core' ),
		esc_html__( 'If enabled, the gallery will stick to top while summary is scrolling. Useful for large summaries. Enabled for vertical or horizontal galleries.', 'rey-core' )
	),
	'active_callback' => [
		[
			'setting'  => 'product_page_summary_fixed',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
	'rey_group_end' => true
] );

/* ------------------------------------ Product gallery ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Product Gallery', 'rey-core'),
	'description' => esc_html__('Customize the product page gallery style.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_gallery_layout',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Gallery layout', 'rey-core' ),
		__('Select the gallery layout.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'vertical',
	'rey_preset' => 'page',
	'choices'     => class_exists('ReyCore_WooCommerce_ProductGallery_Base') ? ReyCore_WooCommerce_ProductGallery_Base::get_gallery_types() : [],
] );

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_skin_cascade_bullets',
	'label'       => esc_html__( 'Bullets Navigation (Cascade gallery)', 'rey-core' ),
    'description' => __('This option will add bullets (dots) navigation for the Cascade gallery.', 'rey-core'),
	'section'     => $section,
	'default'     => true,
	'rey_preset' => 'page',
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => '==',
			'value'    => 'cascade',
		],
		[
			'setting'  => 'single_skin',
			'operator' => '!=',
			'value'    => 'compact',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery_zoom',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Enable Hover Zoom', 'rey-core' ),
		__('This option will enable zooming the main image by hovering it.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'rey_preset' => 'page',
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery_lightbox',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Enable Lightbox', 'rey-core' ),
		__('This option enables the lightbox for images when clicking on the icon or images.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery__btn__enable',
	'label'       => esc_html__( 'Open Gallery button', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_page_gallery__btn__icon',
	'label'       => esc_html__( 'Button Icon', 'rey-core' ),
	'section'     => $section,
	'default'     => 'reycore-icon-plus-stroke',
	'choices'     => [
		'reycore-icon-plus-stroke' => esc_html__( 'Plus icon', 'rey-core' ),
		'reycore-icon-zoom' => esc_html__( 'Zoom Icon', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'product_page_gallery__btn__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Button options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery__btn__text_enable',
	'label'       => esc_html__( 'Enable Text', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_page_gallery__btn__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'product_page_gallery__btn__text',
	'label'       => esc_html__( 'Text', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: OPEN GALLERY', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'product_page_gallery__btn__enable',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'product_page_gallery__btn__text_enable',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true,
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_gallery_arrow_nav',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Arrows Navigation', 'rey-core' ),
		__('This option will enable arrows navigation.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
] );

reycore_customizer__title([
	'title'   => esc_html__('THUMBNAILS', 'rey-core'),
	'section' => $section,
	'size'    => 'xs',
	'border'  => 'none',
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'product_gallery_thumbs_max',
	'label'       => esc_html__( 'Max. visible thumbs', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'min'  => 0,
		'max'  => 10,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
	'output'          => [
		[
			'element'  		   => ':root',
			'property' 		   => '--woocommerce-gallery-max-thumbs',
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Thumbs options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_gallery_thumbs_nav_style',
	'label'       => esc_html__( 'Thumbs nav. style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'boxed',
	'choices'     => [
		'boxed' => esc_html__( 'Boxed', 'rey-core' ),
		'minimal' => esc_html__( 'Minimal', 'rey-core' ),
		'edges' => esc_html__( 'Edges', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_gallery_thumbs_event',
	'label'       => esc_html__( 'Thumbs trigger', 'rey-core' ),
	'section'     => $section,
	'default'     => 'click',
	'choices'     => [
		'click' => esc_html__( 'Click', 'rey-core' ),
		'mouseenter' => esc_html__( 'Hover', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_gallery_thumbs_flip',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Flip thumbs position', 'rey-core' ),
		__('This option will flip the thumbnail list on the other side of the main image.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => '==',
			'value'    => 'vertical',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_gallery_thumbs_disable_cropping',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Disable thumbs cropping', 'rey-core' ),
		__('By default WooCommerce is cropping the gallery thumbnails. You can disable this with this option and contain the image in its natural sizes.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_gallery_layout',
			'operator' => 'in',
			'value'    => ['vertical', 'horizontal'],
		],
	],
	'rey_group_end' => true
] );

reycore_customizer__title([
	'title'   => esc_html__('MOBILE GALLERY', 'rey-core'),
	'section' => $section,
	'size'    => 'xs',
	'border'  => 'none',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_gallery_mobile_nav_style',
	'label'       => esc_html__( 'Navigation Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'bars',
	'choices'     => [
		'bars' => esc_html__( 'Norizontal Bars', 'rey-core' ),
		'circle' => esc_html__( 'Circle Bullets', 'rey-core' ),
		'thumbs' => esc_html__( 'Thumbnails', 'rey-core' ),
	],
	'rey_group_start' => [
		'label' => esc_html__('Mobile gallery options', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_gallery_mobile_arrows',
	'label'       => esc_html__( 'Show Arrows', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_page_scroll_top_after_variation_change',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Scroll top on variation change', 'rey-core'),
		esc_html__( 'On mobiles, after a variation is changed, the page will animate and scroll back to the gallery, so that any image swap is visible.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'rey_group_end' => true
] );

// ReyCoreKirki::add_field( 'rey_core_kirki', [
// 	'type'        => 'toggle',
// 	'settings'    => 'product_page_mobile_gallery_offset',
// 	'label'       => reycore_customizer__title_tooltip(
// 		esc_html__('Show part of upcoming image', 'rey-core'),
// 		esc_html__( 'Show a part of the next image to hint there are other images.', 'rey-core' )
// 	),
// 	'section'     => $section,
// 	'default'     => false,
// ] );

reycore_customizer__title([
	'title'       => esc_html__('Typography', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_pdp__product_title',
	'label'       => esc_attr__('Product Title', 'rey-core'),
	'section'     => $section,
	'default'     => [
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'font-weight' => '',
		'variant' => '',
		'color' => '',
	],
	'output' => [
		[
			'element' => '.woocommerce div.product .product_title',
		]
	],
	'load_choices' => true,
	'transport' => 'auto',
	'responsive' => true,
));

/* ------------------------------------ MISC ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('MISC.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);


$demos = reycore_customizer__presets();
$choices = [ '' => esc_html__( 'Default', 'rey-core' ) ];
$presets = [];

foreach ($demos as $key => $demo) {
	$choices[$key] = $demo['title'];
	$presets[$key]['settings'] = $demo['settings']['page'];
}

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'wc_product_layout_presets',
	'label'       => esc_html__( 'Layout Presets', 'rey-core' ),
	'description' => esc_html__( 'These are product page layout presets from each demo.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => $choices,
	'preset' => $presets,
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-woocommerce/#product-page-layout',
	'section' => $section
]);
