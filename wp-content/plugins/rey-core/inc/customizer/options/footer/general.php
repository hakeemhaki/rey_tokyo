<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'footer_general_section';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('General', 'rey-core'),
	'priority'       => 1,
	'panel'			=> 'footer_options'
));

reycore_customizer__add_field([
	'type'        => 'rey-hf-global-section',
	'settings'    => 'footer_layout_type',
	'section'     => $section,
	'default'     => 'default',
	'choices'     => [
		'type' => 'footer',
		'global_sections' => apply_filters('reycore/options/footer_layout_options', [], false)
	],
	'label'       => esc_html__('Select Footer Layout', 'rey-core')
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'custom_footer_width',
	'label'       => esc_html__( 'Custom Footer Width', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'footer_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
		[
			'setting'  => 'custom_footer_width',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', array(
	'type'        		=> 'slider',
	'settings'    		=> 'footer_width',
	'label'       		=> esc_attr__( 'Container Width', 'rey-core' ),
	'section'           => $section,
	'default'     		=> 1440,
	'choices'     		=> array(
		'min'  => '990',
		'max'  => '2560',
		'step' => '10',
	),
	'transport'   		=> 'auto',
	'output'      		=> array(
		array(
			'element'  		=> ':root',
			'property' 		=> '--footer-default--max-width',
			'units'    		=> 'px',
		),
	),
	'active_callback' => [
		[
			'setting'  => 'footer_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
		[
			'setting'  => 'custom_footer_width',
			'operator' => '==',
			'value'    => true,
		],
	],
));

reycore_customizer__title([
	'title'   => esc_html__('Copyright text', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'border'  => 'top',
	'upper'   => true,
	'active_callback' => [
		[
			'setting'  => 'footer_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'footer_copyright_automatic',
	'label'       => esc_html__( 'Automated Copyright text', 'rey-core' ),
	'section'     => $section,
	'default'     => '1',
	'active_callback' => [
		[
			'setting'  => 'footer_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'textarea',
	'settings'    => 'footer_copyright',
    'label'       => esc_html__('Copyright text', 'rey-core'),
    'description' => __('Enter the text that will appear in footer as copyright. <small>Note: It accepts HTML tags.</small>', 'rey-core'),
	'section'     => $section,
	'active_callback' => [
		[
			'setting'  => 'footer_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
		[
			'setting'  => 'footer_copyright_automatic',
			'operator' => '==',
			'value'    => false,
		],
	],
	'partial_refresh'    => [
		'header_site_title' => [
			'selector'        => '.rey-siteFooter__copyright',
			'render_callback' => function() {
				return get_theme_mod('footer_copyright', '');
			},
		],
	],
));


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'footer_reveal',
	'label'       => esc_html_x( 'Footer Reveal on Scroll', 'Customizer control label', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'footer_reveal_fade',
	'label'       => esc_html_x( 'Fade while revealing', 'Customizer control label', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'footer_reveal',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html_x('Options', 'Customizer control label', 'rey-core'),
	],
	'rey_group_end' => true
] );

// Fixed Reveleaing footer
// Overlay, color picker


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-footer-settings/#general',
	'section' => $section
]);
