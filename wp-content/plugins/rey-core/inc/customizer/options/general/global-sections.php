<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'general_gs_section';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Global Sections', 'rey-core'),
	'priority'       => 70,
	'panel'			 => 'general_options',
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'global_sections',
	'label'       => esc_html__('Assign Global Sections', 'rey-core'),
	'description' => sprintf(__('Assign Generic global sections throughout your website. You can create as many as you want in <a href="%s">Global Sections</a> page.', 'rey-core'), admin_url('edit.php?post_type=rey-global-sections') ),
	'section'     => $section,
	'row_label' => array(
		'type' => 'text',
		'value' => esc_html__('Global Section', 'rey-core'),
	),
	'button_label' => esc_html__('New section', 'rey-core'),
	'default'      => [],
	'fields' => array(
		'id' => array(
			'type'        => 'select',
			'label'       => esc_html__('Generic Sections', 'rey-core'),
			'choices'     => class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections('generic', ['' => '- Select -']) : [],
		),
		'hook' => array(
			'type'        => 'select',
			'label'       => esc_html__('Position', 'rey-core'),
			'choices'     => array(
				'' => esc_html__('- Select -', 'rey-core'),
				'before_site_wrapper' => esc_html__('Before Site Wrapper', 'rey-core'),
				'before_header' => esc_html__('Before Header', 'rey-core'),
				'after_header' => esc_html__('After Header', 'rey-core'),
				'before_site_container' => esc_html__('Before Site Container', 'rey-core'),
				'after_site_container' => esc_html__('After Site Container', 'rey-core'),
				'before_footer' => esc_html__('Before Footer', 'rey-core'),
				'after_footer' => esc_html__('After Footer', 'rey-core'),
				'after_site_wrapper' => esc_html__('After Site Wrapper', 'rey-core'),
			),
		),
		// TODO: Conditionals #52
	),
	'priority'       => 20,
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#global-sections',
	'section' => $section
]);
