<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General Options
 */

ReyCoreKirki::add_panel('general_options', array(
	'title'       => esc_html__('General Settings', 'rey-core'),
    'priority'    => 1
));

require REY_CORE_DIR . 'inc/customizer/options/general/layout.php';
require REY_CORE_DIR . 'inc/customizer/options/general/style.php';
require REY_CORE_DIR . 'inc/customizer/options/general/typography.php';
require REY_CORE_DIR . 'inc/customizer/options/general/preloader.php';
require REY_CORE_DIR . 'inc/customizer/options/general/performance.php';

if( class_exists('\Elementor\Plugin') ){
	require REY_CORE_DIR . 'inc/customizer/options/general/global-sections.php';
	require REY_CORE_DIR . 'inc/customizer/options/general/sticky-global-sections.php';
	require REY_CORE_DIR . 'inc/customizer/options/general/404-page.php';
}

require REY_CORE_DIR . 'inc/customizer/options/general/search-page.php';
require REY_CORE_DIR . 'inc/customizer/options/general/scroll-to-top.php';
require REY_CORE_DIR . 'inc/customizer/options/general/cookie-notice.php';
require REY_CORE_DIR . 'inc/customizer/options/general/social-icons.php';

// Site Identity

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#site-identity',
	'section' => 'title_tagline'
]);
