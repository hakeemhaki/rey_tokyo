<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ReyCoreKirki::add_panel('footer_options', array(
	'title'       => esc_attr__('Footer', 'rey-core'),
    'priority'    => 3
));

require REY_CORE_DIR . 'inc/customizer/options/footer/general.php';
