<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Header Options
 */

// Create Panel and Sections
ReyCoreKirki::add_panel('header_options', array(
	'title'       => esc_attr__('Header', 'rey-core'),
    'priority'    => 2
));

require REY_CORE_DIR . 'inc/customizer/options/header/general.php';
require REY_CORE_DIR . 'inc/customizer/options/header/logo.php';
require REY_CORE_DIR . 'inc/customizer/options/header/search.php';
require REY_CORE_DIR . 'inc/customizer/options/header/nav.php';
require REY_CORE_DIR . 'inc/customizer/options/header/account.php';

if( class_exists('WooCommerce') ){
	require REY_CORE_DIR . 'inc/customizer/options/header/cart.php';
}
