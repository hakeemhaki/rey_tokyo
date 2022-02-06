<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'search_page';

// Create Panel and Sections
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Search Page Settings', 'rey-core'),
	'priority'       => 120,
	'panel'			=> 'general_options'
));



if( class_exists('ReyCore_GlobalSections') ):

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'cover__search_page',
		'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
		'section'     => $section,
		'default'     => 'no',
		'choices'     => ReyCore_GlobalSections::get_global_sections('cover', [
			'no'  => esc_attr__( 'Disabled', 'rey-core' )
		]),
	] );

endif;

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'search__header_position',
	'label'       => esc_html__( 'Header Position', 'rey-core' ),
	'section'     => $section,
	'default'     => 'rel',
	'choices'     => [
		'' => esc_html__( 'Inherit', 'rey-core' ),
		'rel' => esc_html__( 'Relative', 'rey-core' ),
		'absolute' => esc_html__( 'Absolute ( Over Content )', 'rey-core' ),
		'fixed' => esc_html__( 'Fixed (Sticked to top)', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'search__header_text_color',
	'label'       => esc_html__( 'Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> 'body.search-results',
			'property' 		=> '--header-text-color',
		]
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'multicheck',
	'settings'    => 'search__include',
	'label'       => esc_html__( 'Include taxonomies in search', 'rey-core' ),
	'description' => esc_html__( 'Please use with attention because it might return too many generic results.', 'rey-core' ),
	'section'     => $section,
	'default'     => [],
	'choices'     => reycore_customizer__wc_taxonomies(),
] );
