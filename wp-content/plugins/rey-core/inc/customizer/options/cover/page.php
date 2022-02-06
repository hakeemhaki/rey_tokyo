<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'section_cover_pages';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_html__('Pages', 'rey-core'),
	'panel'			 => 'cover_panel',
	'priority'       => 10,
));

reycore_customizer__title([
	'title'   => esc_html__('Site Pages', 'rey-core'),
	'description'   => esc_html__('These settings will apply on all pages of your website. You can always disable or change the Page Cover of a specific page, in the page options.', 'rey-core') . '<br><br>' . $main_desc,
	'section' => $section,
	'size'    => 'md',
	'upper'   => true,
	'priority'     => 10,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'cover__pages',
	'label'       => esc_html__( 'Select a Page Cover layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'no',
	'choices'     => $covers,
	'priority'    => 10,
] );

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-page-cover/#pages',
	'section' => $section
]);
