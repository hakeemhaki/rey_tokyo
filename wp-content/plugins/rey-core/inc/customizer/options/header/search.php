<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Header > Search
 */

$section = 'header_search_options';

// Search
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Search', 'rey-core'),
	'priority'       => 30,
	'panel'			 => 'header_options'
));

/**
 * Search Box
 */
ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_enable_search',
	'label'       => esc_html__( 'Enable Search?', 'rey-core' ),
	'section'     => $section,
	'default'     => '1',
] );

reycore_customizer__notice([
	'section'     => $section,
	'default'     => __('<strong>Heads up!</strong><br> You\'re using a Header Global Section, built with Elementor. It\'s likely these settings below won\'t work properly because the "Header - Search" element might have the Override option enabled eg: <a href="https://d.pr/i/npGOxT" target="_blank">https://d.pr/i/npGOxT</a>.', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'default',
		],
	],
] );



ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_search_style',
	'label'       => esc_html__( 'Search Style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'wide',
	// 'priority'    => 10,
	'choices'     => [
		'button' => esc_html__( 'Simple Form', 'rey-core' ),
		'wide' => esc_html__( 'Wide Panel', 'rey-core' ),
		'side' => esc_html__( 'Side Panel', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

// search panel skin

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'      => 'rey-color',
	'settings'  => 'search_bg_color',
	'label'     => __( 'Background Color', 'rey-core' ),
	'section'   => $section,
	'default'   => '',
	// 'priority'  => 20,
	'transport' => 'auto',
	'choices'   => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--search-bg-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => 'in',
			'value'    => ['wide' , 'side'],
		],
	],
	'rey_group_start' => [
		'label' => esc_html__( 'Panel options', 'rey-core' )
	]
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'      => 'rey-color',
	'settings'  => 'search_text_color',
	'label'     => __( 'Text Color', 'rey-core' ),
	'section'   => $section,
	'default'   => '',
	// 'priority'  => 30,
	'transport' => 'auto',
	'choices'   => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--search-text-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => 'in',
			'value'    => ['wide' , 'side'],
		],
	],
] );


/* ------------------------------------ Suggestions ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Extra content', 'rey-core'),
	'description' => esc_html__('Add extra content near the search form.', 'rey-core'),
	'section'     => $section,
	'size'        => 'xs',
	'upper'       => true,
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => '!=',
			'value'    => 'button',
		],
	],
	// 'priority'    => 48,
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'search_complementary',
	'label'       => esc_html__( 'Suggestions content', 'rey-core' ),
	'section'     => $section,
	'default'     => 'menu',
	// 'priority'    => 50,
	'multiple'    => 1,
	'choices'     => [
		'menu' => esc_html__( 'Menu', 'rey-core' ),
		'keywords' => esc_html__( 'Keyword suggestions', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => '!=',
			'value'    => 'button',
		],
	],
] );

$get_all_menus = reyCoreHelper()->get_all_menus();

if( !is_array($get_all_menus) ){
	$get_all_menus = [];
}

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'search_menu',
	'label'       => esc_html__( 'Search menu source', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	// 'priority'    => 60,
	'choices'     => ['' => esc_html__('- Select -', 'rey-core')] + $get_all_menus,
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => '!=',
			'value'    => 'button',
		],
		[
			'setting'  => 'search_complementary',
			'operator' => '==',
			'value'    => 'menu',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'textarea',
	'settings'    => 'search_suggestions',
	'label'       => esc_html__( 'Keywords', 'rey-core' ),
	'section'     => $section,
	'default'  => '',
	// 'priority' => 70,
	'description' => esc_html__( 'Add keyword suggestions, separated by comma ",".', 'rey-core' ),
	'placeholder' => esc_html__( 'eg: t-shirt, pants, trousers', 'rey-core' ),
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => '!=',
			'value'    => 'button',
		],
		[
			'setting'  => 'search_complementary',
			'operator' => '==',
			'value'    => 'keywords',
		],
	],
] );

/* ------------------------------------ Wide panel settings ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Wide Style options', 'rey-core'),
	'description' => esc_html__('Options for Wide panel search style.', 'rey-core'),
	'section'     => $section,
	'size'        => 'xs',
	'upper'       => true,
	// 'priority'    => 80,
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => '==',
			'value'    => 'wide',
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'image',
	'settings'    => 'search_wide_logo',
	'label'       => esc_html__( 'Custom Logo Image', 'rey-core' ),
	'description' => esc_html__( 'This logo will be shown when the search panel is opened.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	// 'priority'    => 90,
	'choices'     => [
		'save_as' => 'id',
	],
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
		[
			'setting'  => 'header_search_style',
			'operator' => '==',
			'value'    => 'wide',
		],
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'header_search__input_placeholder',
	'label'       => esc_html__( 'Text Placeholder', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: type to search..', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


reycore_customizer__title([
	'title'   => esc_html__('Advanced', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'upper'   => true,
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_enable_ajax_search',
	'label'       => esc_html__( 'Enable Ajax Search?', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_enable_categories',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Enable Categories List?', 'rey-core' ),
		__( 'This option will output a select list of categories inside the Search form.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'header_enable_search',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-header-settings/#search',
	'section' => $section
]);
