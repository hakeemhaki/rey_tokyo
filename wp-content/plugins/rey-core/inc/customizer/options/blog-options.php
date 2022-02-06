<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ReyCoreKirki::add_panel('blog_options', array(
    'title'       => esc_attr__('Blog', 'rey-core'),
    'priority'    => 4
));

require REY_CORE_DIR . 'inc/customizer/options/blog/blog-page.php';
require REY_CORE_DIR . 'inc/customizer/options/blog/blog-post.php';
