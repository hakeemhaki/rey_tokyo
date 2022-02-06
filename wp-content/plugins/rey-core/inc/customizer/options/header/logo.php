<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Header > Logo
 */

$section = 'header_logo_options';

// Logo
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Logo', 'rey-core'),
	'priority'       => 20,
	'panel'			 => 'header_options'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'dimensions',
	'settings'    => 'my_setting',
	'label'       => esc_html__( 'Logo size', 'rey-core' ),
	'section'     => $section,
	'default'     => [
		'max-width'  => '',
		'max-height' => '',
	],
	'choices'     => [
		'labels' => [
			'max-width'  => esc_html__( 'Width (eg 100px)', 'rey-core' ),
			'max-height' => esc_html__( 'Height (eg 50px)', 'rey-core' ),
		],
	],
	'output'      		=> [
		[
			'element'  		=> '.rey-siteHeader.rey-siteHeader--default .rey-siteLogo .custom-logo',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
			],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'image',
	'settings'    => 'logo_mobile',
	'label'       => esc_html__( 'Mobile Logo', 'rey-core' ),
	'description' => esc_html__( 'This logo will be shown on mobile devices.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'save_as' => 'id',
	],
] );

reycore_customizer__notice([
	'section'     => $section,
	'default'     => sprintf(__('<strong>Logo not changing?</strong> Please <a href="%s" target="_blank">follow this article</a> which explains why this is happening.', 'rey-core'), 'https://support.reytheme.com/kb/logo-not-changing/'),
] );


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-header-settings/#logo',
	'section' => $section
]);
