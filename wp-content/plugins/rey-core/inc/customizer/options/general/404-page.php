<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = '404page';

// Create Panel and Sections
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('404 Page Settings', 'rey-core'),
	'priority'       => 120,
	'panel'			=> 'general_options'
));

ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => '404_gs',
	'label'       => esc_html__( 'Global Section', 'rey-core' ),
	'description' => esc_html__('Select a generic global section to override the 404 page.', 'rey-core'),
	'section'     => $section,
	'default'     => '',
	'choices'     => class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections(['generic'], ['' => '- Select -']) : [],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => '404_gs_stretch',
	'label'       => esc_html__( 'Stretch Content', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => '404_gs',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );
