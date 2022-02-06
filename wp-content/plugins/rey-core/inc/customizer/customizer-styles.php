<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// prevent enqueue rey styles, instead to hook into kirki's css
add_filter('rey/allow_enqueue_custom_styles', '__return_false');


if(!function_exists('reycore__custom_css_option_name')):
	/**
	 * Custom CSS Option name
	 *
	 * @since 1.5.4
	 **/
	function reycore__custom_css_option_name() {
		return 'rey__custom_css_option';
	}
endif;


if(!function_exists('reycore__css_cache_is_enabled')):
	/**
	 * Check if CSS cache is enabled
	 *
	 * @since 1.6.0
	 **/
	function reycore__css_cache_is_enabled()
	{
		if( ! class_exists('ACF') ){
			return false;
		}

		$field = reycore__acf_get_field('rey_cache_css_enable', REY_CORE_THEME_NAME);

		if( is_null($field) || (bool) $field === true ){
			return true;
		}

		return false;
	}
endif;


if(!function_exists('reycore__has_cached_styles')):
	function reycore__has_cached_styles(){
		return reycore__css_cache_is_enabled() && get_option( reycore__custom_css_option_name() );
	}
endif;

if(!function_exists('reycore__maybe_load_customizer_options')):
	/**
	 * Prevent loading Kirki's CSS module
	 *
	 * @since 1.6.2
	 */
	function reycore__maybe_load_customizer_options( $status ) {
		global $wp_customize;

		// it's frontend and styles exist, bail
		if( reycore__has_cached_styles() && ! $wp_customize ){

			// remove adding styles from kirki
			if( class_exists('Kirki_Modules_CSS') ){
				remove_action( 'init', [Kirki_Modules_CSS::get_instance(), 'init'] );
			}

			// exit
			return false;
		}

		return $status;
	}
	add_filter('reycore/customizer_load_options', 'reycore__maybe_load_customizer_options');
endif;


if(!function_exists('reycore__get_minified_css')):
	/**
	 * Minify CSS
	 *
	 * @since 1.6.2
	 */
	function reycore__get_minified_css(){
		if( function_exists('rey__custom_styles') && ($styles_output = rey__custom_styles()) && is_array($styles_output) ){
			$styles_output = implode(' ', $styles_output);
			$styles_output = str_replace(array("\r\n", "\r", "\n"), '', $styles_output);
			return $styles_output;
		}
	}
endif;


if(!function_exists('reycore__make_dynamic_css')):
	/**
	 * Cache CSS
	 *
	 * @since 1.6.2
	 */
	function reycore__make_dynamic_css(){

		if(
			is_customize_preview() || wp_doing_ajax() || wp_doing_cron() || is_admin() ||
			is_feed() || is_preview() || (defined( 'REST_REQUEST' ) && REST_REQUEST) ||
			(isset($_REQUEST['editor']) && 1 === absint($_REQUEST['editor']))
			){
			return;
		}

		if( ! reycore__css_cache_is_enabled() ){
			return;
		}

		if( get_option( reycore__custom_css_option_name() ) ) {
			return;
		}

		// Grab Kirki's styles
		ob_start();
			if( class_exists('Kirki_Modules_CSS') ){
				Kirki_Modules_CSS::get_instance()->print_styles();
			}
		$styles = ob_get_clean();

		// bail if no styles from Kirki
		if( ! $styles ){
			return;
		}

		// Grab Rey's styles
		$styles .= reycore__get_minified_css();

		// cache CSS
		update_option( reycore__custom_css_option_name(), $styles );
	}
	add_action('wp', 'reycore__make_dynamic_css');
endif;


if(!function_exists('reycore__print_inline_style')):
	/**
	 * Print Inline Styles
	 *
	 * @since 1.5.4
	 **/
	function reycore__print_inline_style()
	{
		if( ! reycore__css_cache_is_enabled() ){
			return;
		}

		if( $css = get_option( reycore__custom_css_option_name() ) ) {
			echo '<style id="reycore-inline-styles">' . $css . '</style>';
		}
	}
	add_action( 'wp_head', 'reycore__print_inline_style', 998 );
endif;


if(!function_exists('reycore__custom_css_option_delete')):
	/**
	 * Clearn Custom CSS Option
	 *
	 * @since 1.5.4
	 **/
	function reycore__custom_css_option_delete() {

		if( ! reycore__css_cache_is_enabled() ){
			return;
		}

		return delete_option( reycore__custom_css_option_name() );
	}
	add_action( 'customize_save_after', 'reycore__custom_css_option_delete', 20);
	add_action( 'activated_plugin', 'reycore__custom_css_option_delete', 20);
	add_action( 'deactivated_plugin', 'reycore__custom_css_option_delete', 20);
	add_action( 'wp_update_nav_menu', 'reycore__custom_css_option_delete', 20);
	add_action( 'rey/flush_cache_after_updates', 'reycore__custom_css_option_delete', 20);
	// add_action( 'pt-ocdi/after_all_import_execution', 'reycore__custom_css_option_delete', 110 );
endif;


if(!function_exists('reycore__theme_settings_save')):
	/**
	 * Clearn Custom CSS Transients
	 *
	 * @since 1.5.4
	 **/
	function reycore__theme_settings_save( $post_id ) {
		if ( $post_id === REY_CORE_THEME_NAME ) {
			reycore__custom_css_option_delete();
		}
	}
	add_action( 'acf/save_post', 'reycore__theme_settings_save', 20);
endif;


if(!function_exists('reycore__print_page_inline_style')):
	/**
	 * Print Inline Styles for pages
	 *
	 * @since 1.5.4
	 **/
	function reycore__print_page_inline_style()
	{
		if( $css = apply_filters('reycore/page_css_styles', []) ) {
			echo '<style id="reycore-page-inline-styles">' . implode( '', $css ) . '</style>';
		}
	}
	add_action( 'wp_head', 'reycore__print_page_inline_style', 998 );
endif;


if(!function_exists('reycore__add_css_cache_settings')):
	/**
	 * Adds option in theme settings
	 */
	function reycore__add_css_cache_settings(){

		if( !is_admin() ){
			return;
		}

		acf_add_local_field([
			'key'          => 'field_rey_cache_css_enable',
			'name'         => 'rey_cache_css_enable',
			'label'        => esc_html__('Enable CSS Cache', 'rey-core'),
			'type'         => 'true_false',
			'instructions' => esc_html__('Control if want Customizer\'s generated css to be cached. This should result in performance improvements.', 'rey-core'),
			'default_value' => 1,
			'ui' => 1,
			'parent'       => 'group_5c990a758cfda',
			'menu_order'   => 300,
		]);
	}
	add_action( 'acf/init', 'reycore__add_css_cache_settings' );
endif;


if(!function_exists('reycore__refresh_dynamic_css_ajax')):
	/**
	 * Refresh Dynamic CSS through Ajax
	 *
	 * @since 1.6.0
	 **/
	function reycore__refresh_dynamic_css_ajax()
	{
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error();
		}

		if( reycore__custom_css_option_delete() ){
			wp_send_json_success();
		}
	}
	add_action('wp_ajax__refresh_dynamic_css', 'reycore__refresh_dynamic_css_ajax');
endif;

if(!function_exists('reycore__enqueue_styles_in_kirki')):
	/**
	 * Load Rey's custom CSS in HEAD
	 */
	function reycore__enqueue_styles_in_kirki( $styles ){

		if( ! reycore__css_cache_is_enabled() || is_customize_preview() ){
			$styles .= reycore__get_minified_css();
		}

		return $styles;
	}
	add_filter('kirki_rey_core_kirki_dynamic_css', 'reycore__enqueue_styles_in_kirki');
endif;
