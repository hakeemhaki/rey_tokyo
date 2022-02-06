<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

ReyCoreKirki::add_panel('cover_panel', array(
    'title'          => esc_html__('Page Cover', 'rey-core'),
	'priority'       => 30,
));

$covers = class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections('cover', [
	'no'  => esc_attr__( 'Disabled', 'rey-core' )
]) : [];

$main_desc = sprintf('%s <a href="%s" target="blank">%s</a>.',
	esc_html__('To use or create more page cover layouts, head over to', 'rey-core'),
	admin_url('edit.php?post_type=rey-global-sections'),
	esc_html__('Global Sections', 'rey-core')
);

require REY_CORE_DIR . 'inc/customizer/options/cover/page.php';
require REY_CORE_DIR . 'inc/customizer/options/cover/blog.php';
if( class_exists('WooCommerce') ){
	require REY_CORE_DIR . 'inc/customizer/options/cover/shop.php';
}
require REY_CORE_DIR . 'inc/customizer/options/cover/frontpage.php';
