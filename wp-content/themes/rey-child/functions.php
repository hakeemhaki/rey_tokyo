<?php

/**
 * Theme functions and definitions.
 * This child theme is purposed to Rey Theme
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 */

/*
* If your child theme has more than one .css file (eg. ie.css, style.css, main.css) then
* you will have to make sure to maintain all of the parent theme dependencies.
*
* Make sure you're using the correct handle for loading the parent theme's styles.
* Failure to use the proper tag will result in a CSS file needlessly being loaded twice.
* This will usually not affect the site appearance, but it's inefficient and extends your page's loading time.
*
* @link https://codex.wordpress.org/Child_Themes
*/


/**
 * Load styles
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'rey-wp-style-child', get_stylesheet_uri() );
}, PHP_INT_MAX );
