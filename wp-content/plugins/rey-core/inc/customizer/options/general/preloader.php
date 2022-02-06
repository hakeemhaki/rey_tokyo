<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'site_preloader_options';

// Create Panel and Sections
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Site Preloader', 'rey-core'),
	'priority'       => 20,
	'panel'			=> 'general_options'
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'site_preloader',
	'label'       => esc_html__( 'Enable Site Preloader', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'priority'    => 10,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'preloader_color',
	'label'       => __( 'Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'priority'    => 10,
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--preloader-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'site_preloader',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

reycore_customizer__title([
	'description' => sprintf(__('Want more preloaders? Head over to <a href="%s" target="_blank">Appearance > Install Plugins</a> and install & activate the <strong>Rey Module - Preloaders Pack</strong> plugin and you\'ll find new options here in this panel.', 'rey-core'), admin_url('admin.php?page=rey-install-required-plugins')) . '<br><br>',
	'section'     => $section,
	'priority'    => 10,
	'size'        => 'sm',
	'border'      => 'none',
]);

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#site-preloader',
	'section' => $section
]);
