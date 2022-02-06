<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'rey_performance';

// Create Panel and Sections
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Performance Settings', 'rey-core'),
	'priority'       => 120,
	'panel'			=> 'general_options'
));

reycore_customizer__title([
	'title'       => esc_html__('Gutenberg Editor', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'perf__disable_wpblock',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Disable WordPress Block Styles', 'Customizer control title', 'rey-core' ),
		esc_html_x( 'Will disable WordPress\'s built-in Gutenberg editor styles. Enable this option if you don\'t use blocks throughout the site.', 'Customizer control description', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'perf__disable_wpblock__posts',
	'label'       => esc_html_x( 'Keep in blog posts', 'Customizer control title', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'perf__disable_wpblock',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label'    => esc_html__( 'Options', 'rey-core' ),
	],
	'rey_group_end' => true
] );

if( class_exists('WooCommerce') ):
	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'perf__disable_wcblock',
		'label'       => reycore_customizer__title_tooltip(
			esc_html_x( 'Disable WooCommerce Block Styles', 'Customizer control title', 'rey-core' ),
			esc_html_x( 'Will disable WooCommerce\'s built-in Gutenberg editor styles. Enable this option if you don\'t use WooCommerce blocks throughout the site.', 'Customizer control description', 'rey-core')
		),
		'section'     => $section,
		'default'     => false,
	] );
endif;

reycore_customizer__title([
	'title'       => esc_html__('Assets', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'perf__enable_flying_scripts',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Enable Flying Pages', 'Customizer control title', 'rey-core' ),
		esc_html_x( 'Flying Pages will prefetch pages before the user click on links, making them load instantly. Please make sure that your caching plugin doesn\'t alread have a built-in links preloader functionality.', 'Customizer control description', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'perf__modals_load_always',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Always load modal scripts', 'Customizer control title', 'rey-core' ),
		esc_html_x( 'By default modal scripts load on demand. If you have custom anchor links that should open modals, enable this option.', 'Customizer control description', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'perf__css_exclude',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x( 'Exclude CSS Stylesheets', 'Customizer control title', 'rey-core' ),
		esc_html_x( 'You can choose specific stylesheets to exclude from being loaded globally in the site. Not recommended unless you specifically want this for various purposes such as overrides or things like that. There\'s an option in individual pages backend too.', 'Customizer control description', 'rey-core')
	),
	'section'     => $section,
	'multiple'    => 100,
	'choices'     => function_exists('reyCoreAssets') ? reyCoreAssets()->get_excludes_choices( false ) : [],
	'default'     => [
		'rey-presets'
	],
	'input_attrs' => [
		'data-control-class' => '--block-label',
	],
] );



ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'perf__preload_assets',
	'label'       => _x( 'Preload assets', 'Customizer control title', 'rey-core' ),
	'description'       => _x( 'Preload assets that are important to your site\'s top area to load them faster. Make sure not to add more than 1-2 items as otherwise it would make more harm. More about preloading at <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content" target="_blank">Mozilla docs - Preloading content</a>.', 'Customizer control description', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'type' => 'text',
		'value' => esc_html__('Preloaded asset', 'Customizer control title', 'rey-core'),
		'field' => 'type',
	],
	'button_label' => esc_html_x('New Asset', 'Customizer control title', 'rey-core'),
	'default'      => [],
	'fields' => [
		'type' => [
			'type'        => 'text',
			'label'       => esc_html_x('Type (eg: image, font, video etc.)', 'Customizer control title', 'rey-core'),
		],
		'path' => [
			'type'        => 'text',
			'label'       => esc_html_x('URL', 'Customizer control title', 'rey-core'),
		],
		'mime' => [
			'type'        => 'text',
			'label'       => esc_html_x('MIME-type (eg: image/jpeg etc.)', 'Customizer control title', 'rey-core'),
		],
		'media' => [
			'type'        => 'text',
			'label'       => esc_html_x( 'Media (eg: (max-width: 600px))', 'Customizer control title', 'rey-core' ),
		],
		'crossorigin' => [
			'type'        => 'select',
			'label'       => esc_html_x( 'Cross-Origin', 'Customizer control title', 'rey-core' ),
			'default'     => 'no',
			'choices'     => [
				'no' => esc_html__( 'No', 'rey-core' ),
				'yes' => esc_html__( 'Yes', 'rey-core' ),
			],
		],
	],

] );
