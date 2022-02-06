<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-button',
	'settings'    => 'rey_transfer_mods_from_parent',
	'label'       => esc_html__( 'Copy settings from Parent theme', 'rey-core' ),
	'description' => esc_html__( 'In case you just switched from the Parent theme, you can easily transfer the settings. Make sure to export your current settings first.', 'rey-core' ),
	'section'     => 'cei-section',
	'default'     => 'parent',
	'choices'     => [
		'text' => esc_html__('Copy Settings', 'rey-core'),
		'action' => 'rey_transfer_mods',
		'class' => '--btn-full',
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-button',
	'settings'    => 'rey_transfer_mods_from_child',
	'label'       => esc_html__( 'Copy settings from Child theme', 'rey-core' ),
	'description' => esc_html__( 'In case you just switched from the Child theme, you can easily transfer the settings. Make sure to export your current settings first.', 'rey-core' ),
	'section'     => 'cei-section',
	'default'     => 'child',
	'choices'     => [
		'text' => esc_html__('Copy Settings', 'rey-core'),
		'action' => 'rey_transfer_mods',
		'class' => '--btn-full',
	],
] );
