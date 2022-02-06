<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add Config for Kirki Settings
ReyCoreKirki::add_config('rey_core_kirki', array(
	'capability'    => 'edit_theme_options',
	'option_type'   => 'theme_mod'
));

if(!function_exists('reycore_customizer__load_options')):
	/**
	 * Load Customizer Options
	 *
	 * @since 1.6.0
	 **/
	function reycore_customizer__load_options()
	{

		if( is_admin() ){
			if( ! is_customize_preview() ){
				return;
			}
		}

		// minor improvement for ajax calls getting kirki fonts
		if( wp_doing_ajax() && isset($_REQUEST['action']) &&
			in_array( $_REQUEST['action'], ['kirki_fonts_google_all_get', 'kirki_fonts_standard_all_get'], true ) ){
			return;
		}

		if( wp_doing_ajax() && isset($_REQUEST['wc-ajax']) && $_REQUEST['wc-ajax'] === 'get_refreshed_fragments' ){
			return;
		}

		if( function_exists('wp_doing_cron') && wp_doing_cron() ){
			return;
		}

		if( ! apply_filters('reycore/customizer_load_options', true) ){
			return;
		}

		do_action('reycore/customizer/before_init');

		include_once(REY_CORE_DIR . 'inc/customizer/options/general-options.php');
		include_once(REY_CORE_DIR . 'inc/customizer/options/header-options.php');
		include_once(REY_CORE_DIR . 'inc/customizer/options/footer-options.php');
		include_once(REY_CORE_DIR . 'inc/customizer/options/blog-options.php');

		if( class_exists('\Elementor\Plugin') ){
			include_once(REY_CORE_DIR . 'inc/customizer/options/cover-options.php');
		}

		if (class_exists('WooCommerce')) {
			include_once(REY_CORE_DIR . 'inc/customizer/options/woocommerce-options.php');
		}

		include_once(REY_CORE_DIR . 'inc/customizer/options/various-options.php');

		do_action('reycore/customizer/init');
	}

	add_action('init', 'reycore_customizer__load_options', 9);
endif;


// Adds different style to customizer
add_filter('kirki_config',function($config) {


	$config['disable_loader'] = true;
	$config['url_path'] = REY_CORE_URI . 'inc/vendor/kirki';

	if( ! is_customize_preview() ){
		if( ! wp_doing_ajax() ){
			$config['disable_google_fonts'] = true;
		}
	}

	return $config;
});


/**
 * Customizer Settings
 */
add_action('customize_register', function($wp_customize) {

	$wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.rey-logoTitle a',
				'render_callback' => function() {
					bloginfo( 'name' );
				},
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.rey-logoDescription',
				'render_callback' => function() {
					bloginfo( 'description' );
				},
			)
		);
	}

	// Reassign default sections to panels.
	$wp_customize->get_section( 'title_tagline' )->panel     = 'general_options';
	$wp_customize->get_section( 'static_front_page' )->panel = 'general_options';
	// move logo option from Site identity, to Header Logo section
	$wp_customize->get_control( 'custom_logo' )->section = 'header_logo_options';

});


add_action('admin_enqueue_scripts', function(){
	if( is_customize_preview() ){
		add_filter('disable_wvs_admin_enqueue_scripts', '__return_true');
	}
}, 9);
