<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'cookie_notice';

ReyCoreKirki::add_section($section, [
	'title'          => esc_html__('Cookie Notice', 'rey-core'),
	'priority'       => 130,
	'panel'			 => 'general_options',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'cookie_notice__enable',
	'label'       => esc_html__( 'Select style', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Disabled', 'rey-core' ),
		'side-box' => esc_html__( 'Side box', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'textarea',
	'settings'    => 'cookie_notice__text',
	'label'       => esc_html__( 'Text', 'rey-core' ),
	'section'     => $section,
	'default'     => __('In order to provide you a personalized shopping experience, our site uses cookies. By continuing to use this site, you are agreeing to our cookie policy.', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'cookie_notice__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'input_attrs' => [
		'data-control-class' => '--text-xl',
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'cookie_notice__btn_text',
	'label'       => esc_html__( 'Button text', 'rey-core' ),
	'section'     => $section,
	'default'     => esc_html__('ACCEPT', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'cookie_notice__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'cookie_notice__bg_color',
	'label'       => esc_html__( 'Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--cookie-bg-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'cookie_notice__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'cookie_notice__text_color',
	'label'       => esc_html__( 'Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--cookie-text-color',
		],
	],
	'active_callback' => [
		[
			'setting'  => 'cookie_notice__enable',
			'operator' => '!=',
			'value'    => '',
		],
	],
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/cookie-notice/',
	'section' => $section
]);
