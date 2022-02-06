<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$classes = [
	'btn',
	'rey-mainNavigation-mobileBtn',
	'rey-headerIcon',
];

$attributes = [
	'aria-label' => esc_html__('Open menu', 'rey'),
];

$args = rey__header_nav_params();

if(  ! empty($args['load_hamburger']) ){
	if( isset($args['load_hamburger']['attributes']) && ($custom_attributes = $args['load_hamburger']['attributes']) ){
		$attributes = array_merge($attributes, $custom_attributes);
	}
	if( isset($args['load_hamburger']['classes']) && ($custom_classes = $args['load_hamburger']['classes']) ){
		$classes = array_merge($classes, $custom_classes);
	}
} ?>

<button class="<?php esc_attr_e( implode(' ', $classes) ) ?>" <?php echo rey__implode_html_attributes( $attributes ) ?>>
	<span></span>
	<span></span>
	<span></span>
</button>
<!-- .rey-mainNavigation-mobileBtn -->
<div class="rey-mobileBtn-helper"></div>
