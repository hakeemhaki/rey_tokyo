<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!function_exists('reycore__check_feature')):
	/**
	 * Various features
	 *
	 * @since 2.0.0
	 **/
	function reycore__check_feature( $feature = '' )
	{
		$features = [
			'cookie-notice' => get_theme_mod('cookie_notice__enable', '') !== '',
			'footer-reveal' => get_theme_mod('footer_reveal', false),
		];

		if( isset($features[$feature]) ) {
			return $features[$feature];
		}

		return false;
	}
endif;

if(!function_exists('reycore__theme_active')):
	/**
	 * Check if Rey is active
	 *
	 * @since 1.0.0
	 **/
	function reycore__theme_active()
	{
		$theme = wp_get_theme();
		$rey_theme_name = apply_filters('reycore/theme_name', ucfirst(REY_CORE_THEME_NAME));
		return ( $rey_theme_name == $theme->name || $rey_theme_name == $theme->parent_theme );
	}
endif;


if(!function_exists('reycore__get_purchase_code')):
	/**
	 * Get purchase code
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_purchase_code()
	{
		return trim( get_site_option( 'rey_purchase_code' ) );
	}
endif;


if(!function_exists('reycore__get_dashboard_page_id')):
	/**
	 * Get Rey's dashboard page id
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_dashboard_page_id()
	{
		if( class_exists('ReyTheme_Base') ){
			return ReyTheme_Base::DASHBOARD_PAGE_ID;
		}
		return false;
	}
endif;


if(!function_exists('reycore__get_post_id')):
	/**
	 * Wrapper for queried object
	 *
	 * @since 1.0.0
	 */
	function reycore__get_post_id() {

		if( class_exists('WooCommerce') && is_shop() ){
			return wc_get_page_id('shop');
		}
		elseif( is_home() ){
			return absint( get_option('page_for_posts') );
		}
		elseif( is_tax() || is_archive() || is_author() || is_category() || is_tag() ){
			if( apply_filters('rey/get_queried_object_id', false, 'reycore' ) ){
				return get_queried_object_id();
			}
			else {
				return get_queried_object();
			}
		}
		elseif( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) && ($pid = $_GET['preview_id']) ){
			return absint( $pid );
		}

		return false;
	}
endif;

if(!function_exists('reycore__get_post_id_by_url')):
	/**
	 * Get Post ID by URL
	 *
	 * @since 2.0.0
	 **/
	function reycore__get_post_id_by_url()
	{

		if( ($url = reycore__current_url()) && $post_id = absint(url_to_postid( $url )) ){
			return $post_id;
		}

		return 0;
	}
endif;


if(!function_exists('reycore__get_theme_mod')):
	/**
	 * Get theme mod wrapper
	 *
	 * @since 1.6.6
	 **/
	function reycore__get_theme_mod( $mod, $default, $args = [] )
	{
		$args = wp_parse_args($args, [
			'translate' => false,
			'translate_post_type' => false,
		]);

		if( $args['translate'] ){
			return apply_filters('reycore/theme_mod/translate_ids', get_theme_mod($mod, $default), $args['translate_post_type']);
		}

		return get_theme_mod($mod, $default);
	}
endif;


if(!function_exists('reycore__config')):
	/**
	 * Wrapper for ReyConfig
	 *
	 * @since 1.0.0
	 */
	function reycore__config( $setting = '' ) {
		if(function_exists('rey__config')) {
			return rey__config($setting);
		}
		return false;
	}
endif;


if(!function_exists('reycore__exclude_modules')):
	/**
	 * Exclude modules from running since early load
	 *
	 * @since 1.0.0
	 */
	function reycore__exclude_modules() {
		return apply_filters('reycore/exclude_modules', []);
	}
endif;


if(!function_exists('reycore__acf_get_field')):
	/**
	 * Get ACF Field - wrapper for get_field
	 *
	 * @since 1.0.0
	 **/
	function reycore__acf_get_field( $name, $pid = false, $return = false )
	{
		if( ! $pid ) {
			$pid = reycore__get_post_id();
		}

		// check for ACF and get the field
		if( class_exists('ACF') )  {

			$field = get_field( $name, $pid );

			if( ! $field && $return !== false ) {
				return $return;
			}

			return $field;
		}

		return $return;
	}
endif;


if(!function_exists('reycore__get_option')):
	/**
	 * Get Option - wrapper for get_theme_mod and get_field
	 * overrides reycore__get_option from theme
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_option( $name, $default = false, $skip_acf = false )
	{
		// check for ACF and get the field
		if( !$skip_acf && ($opt = reycore__acf_get_field( $name )) )  {
			return apply_filters("rey_acf_option_{$name}", $opt);
		}

		return get_theme_mod( $name, $default);
	}
endif;


if(!function_exists('reycore__get_header_styles')):
	/**
	 * Wrapper for `rey__get_header_styles`
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_header_styles( $add_default = true )
	{
		$defaults = [
			'none'  => esc_html__( 'Disabled', 'rey-core' ),
		];

		if( $add_default ){
			$defaults['default'] = esc_html__( 'Default', 'rey-core' );
		}

		return apply_filters('reycore/options/header_layout_options', $defaults, true);
	}
endif;


if(!function_exists('reycore__get_footer_styles')):
	/**
	 * Get footer styles
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_footer_styles()
	{
		return apply_filters('reycore/options/footer_layout_options', [
			'none'  => esc_attr__( 'None', 'rey-core' ),
			'default'  => esc_attr__( 'Default Footer', 'rey-core' ),
		], true);
	}
endif;


if(!function_exists('reycore__header_footer_layout_desc')):
	/**
	 * Retrieve header / footer list description
	 *
	 * @since 1.0.0
	 **/
	function reycore__header_footer_layout_desc( $type = 'header', $inherit_text = false )
	{

		$suffix = '';

		$layout_type = esc_html(ucfirst($type));

		if( $inherit_text ){
			$suffix = sprintf(
				__('By default inherits the option from Customizer > %s > General.' , 'rey-core'),
				$layout_type
			);
		}

		return sprintf(
			__('A Global Section is a block of content built with Elementor, that\'s embedded into the website. To use or create more %1$s global sections, head over to <a href="%2$s" target="blank">%3$s</a>. %4$s' , 'rey-core'),
			$layout_type,
			admin_url('edit.php?post_type=rey-global-sections'),
			esc_html__('Global Sections', 'rey-core'),
			$suffix
		);
	}
endif;


if(!function_exists('reycore__var_dump')):
	/**
	 * Shows debug info wrapped in var_dump.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  mixed $c Content to display.
	 * @param  bool $hidden Wether or not to hide.
	 * @return void
	 */
	function reycore__var_dump($c, $hidden = true) {
		echo '<div class="' .( $hidden ? 'd-none' : '' ). '">';
		var_dump($c);
		echo '</div>';
	}
endif;


if(!function_exists('reycore__get_hooks')):
	/**
	 * Shows debug info wrapped in var_dump.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  mixed $c Content to display.
	 * @param  bool $hidden Wether or not to hide.
	 * @return void
	 */
	function reycore__get_hooks($hook) {
		global $wp_filter;
		var_dump( $wp_filter[$hook] );
	}
endif;


if(!function_exists('reycore__breadcrumbs_args')):
	/**
	 * Filter WooCommerce Breadcrumbs defaults
	 *
	 * @since 1.0.0
	 */
	function reycore__breadcrumbs_args($args){

		$args['delimiter']   = '<span class="rey-breadcrumbs-del">&#8250;</span>';
		$args['wrap_before'] = '<nav class="rey-breadcrumbs">';
		$args['wrap_after']  = '</nav>';
		$args['before']  = '<div class="rey-breadcrumbs-item">';
		$args['after']  = '</div>';

		return $args;
	}
endif;
add_filter('woocommerce_breadcrumb_defaults', 'reycore__breadcrumbs_args', 5);


/**
 * Remove Instagram Widget Assets
 *
 * @since 1.0.0
 **/
function reycore__instagram_widget_js_assets()
{
	if( class_exists('Wpzoom_Instagram_Widget_API') ):
		wp_dequeue_script( 'zoom-instagram-widget-lazy-load' );
		wp_dequeue_script( 'zoom-instagram-widget' );
		wp_dequeue_script( 'magnific-popup' );
		wp_dequeue_script( 'swiper-js' );
	endif;
}
add_action('wp_print_scripts','reycore__instagram_widget_js_assets');


/**
 * Remove Instagram Widget Assets
 *
 * @since 1.0.0
 **/
function reycore__instagram_widget_css_assets()
{
	if( class_exists('Wpzoom_Instagram_Widget_API') ):
		wp_dequeue_style( 'magnific-popup' );
		wp_dequeue_style( 'swiper-css' );
		wp_dequeue_style( 'zoom-instagram-widget' );
	endif;
}
add_action('wp_print_styles','reycore__instagram_widget_css_assets');


function reycore_add_products_global(){
	$GLOBALS["rey_exclude_products"] = [];
}
add_action('wp', 'reycore_add_products_global', 5);


if(!function_exists('rey__log_error')):
	/**
	 * Log Errors.
	 * Dummy function for Rey theme's `rey__log_error` to avoid errors
	 * if Rey theme is not active.
	 *
	 * @since 1.0.0
	 **/
	function rey__log_error( $source, $error )
	{
		return false;
	}
endif;


if(!function_exists('reycore__wp_parse_args')):
	/**
	 * Recursive wp_parse_args WordPress function which handles multidimensional arrays
	 * @url http://mekshq.com/recursive-wp-parse-args-wordpress-function/
	 * @param  array &$a Args
	 * @param  array $b Defaults
	 * @since: 1.0.0
	 */
	function reycore__wp_parse_args( &$a, $b )
	{
		$a = (array)$a;
		$b = (array)$b;
		$result = $b;
		foreach ( $a as $k => &$v )
		{
			if ( is_array( $v ) && isset( $result[ $k ] ) )
			{
				$result[ $k ] = reycore__wp_parse_args( $v, $result[ $k ] );
			}
			else
			{
				$result[ $k ] = $v;
			}
		}
		return $result;
	}
endif;


if(!function_exists('reycore__format_period')):
	/**
	 * Format microtime
	 *
	 * @since 1.0.0
	 **/
	function reycore__format_period( $duration )
	{
		$hours = (int) ($duration / 60 / 60);
		$minutes = (int) ($duration / 60) - $hours * 60;
		$seconds = (int) $duration - $hours * 60 * 60 - $minutes * 60;

		return ($hours == 0 ? "00":$hours) . ":" . ($minutes == 0 ? "00":($minutes < 10? "0".$minutes:$minutes)) . ":" . ($seconds == 0 ? "00":($seconds < 10? "0".$seconds:$seconds));
	}
endif;


if(!function_exists('reycore__clean')):
	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	function reycore__clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'reycore__clean', $var );
		} else {
			if( is_bool($var) ){
				return filter_var($var, FILTER_VALIDATE_BOOLEAN);
			}
			else {
				return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
			}
		}
	}
endif;

if(!function_exists('reycore__array_values_recursive')):
	/**
	 * Get arrau values recursively
	 * https://davidwalsh.name/get-array-values-with-php-recursively
	 * @since 1.6.6
	 */
	function reycore__array_values_recursive($array) {
		$flat = [];

		foreach($array as $value) {
			if (is_array($value)) {
				$flat = array_merge($flat, reycore__array_values_recursive($value));
			}
			else {
				$flat[] = $value;
			}
		}

		return $flat;
	}
endif;


if(!function_exists('reycore__preloader_is_active')):
	/**
	 * Checks if preloader is active.
	 *
	 * @since 1.0.0
	 */
	function reycore__preloader_is_active() {
		return get_theme_mod('site_preloader', false);
	}
endif;

if(!function_exists('reycore__get_attachment_image')):
	function reycore__get_attachment_image( $args = [] ){

		$defaults = [
			'image' => [
				'id' => '',
				'url' => '',
			],
			'size' => 'large',
			'attributes' => [],
			'key' => 'image',
			'settings' => [],
			'return_url' => false
		];

		// Parse args.
		$args = reycore__wp_parse_args( $args, $defaults );

		if( ! isset($args['image']['id']) ){
			return;
		}

		$image_id = $args['image']['id'];
		$_custom_image = [];

		if( $args['size'] === 'custom' && !empty($args['settings']) ){

			$_custom_image['url'] = \Elementor\Group_Control_Image_Size::get_attachment_image_src( $image_id, $args['key'], $args['settings'] );
			$_custom_image['width'] = 800;
			$_custom_image['height'] = 800;

			if( isset( $args['settings'][ $args['key'] . '_custom_dimension' ] ) ){
				$_custom_image['width'] = $args['settings'][ $args['key'] . '_custom_dimension' ]['width'];
				$_custom_image['height'] = $args['settings'][ $args['key'] . '_custom_dimension' ]['height'];
			}

			// if just the url is needed
			// return custom image URL + attrs (similar to wp_get_attachment_image_src)
			if( $args['return_url'] ){
				return [
					$_custom_image['url'],
					absint($_custom_image['width']),
					absint($_custom_image['height'])
				];
			}
		}

		// return URL array, if specified
		if( $image_id && $args['return_url'] ){
			return wp_get_attachment_image_src( $image_id, $args['size']);
		}

		// if there's a custom Image
		if( isset($_custom_image['url']) && !empty($_custom_image['url']) ){
			$image_url = $_custom_image['url'];
			$args['attributes']['alt'] = trim( strip_tags( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) );
			$args['attributes']['width'] = $_custom_image['width'];
			$args['attributes']['height'] = $_custom_image['height'];
			// Add `loading` attribute.
			if ( wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' ) ) {
				$args['attributes']['loading'] = 'lazy';
			}
		}

		// check if no Image ID provided, make a custom HTML
		elseif( empty($image_id) && isset($args['image']['url']) && !empty($args['image']['url']) ){
			$image_url = $args['image']['url'];
		}

		// we have an image url specified,
		// let's make a custom image instead
		if( !empty($image_url) ){
			return sprintf(
				'<img src="%s" %s>',
				$image_url,
				implode(' ', array_map( function ($k, $v) { return $k .'="'. esc_attr($v) .'"'; }, array_keys($args['attributes']), $args['attributes'] ) )
			);
		}

		return wp_get_attachment_image( $image_id, $args['size'], false, $args['attributes']);
	}
endif;


if(!function_exists('reycore__array_has_string_keys')):
	/**
	 * Chec if array has string keys
	 *
	 * @since 1.0.0
	 **/
	function reycore__array_has_string_keys(array $array) {
		return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
	}
endif;


if(!function_exists('reycore__elementor_edit_mode')):
	/**
	 * Check if Elementor is in edit mode
	 *
	 * @since 1.0.0
	 **/
	function reycore__elementor_edit_mode()
	{
		return class_exists('\Elementor\Plugin') && ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() );
	}
endif;


if(!function_exists('reycore__arrowSvg')):
	/**
	 * wrapper for arrow SVG
	 *
	 * @since 1.0.0
	 **/
	function reycore__arrowSvg( $right = true, $class = '', $attributes = '' )
	{
		if( function_exists('rey__arrowSvg') ){
			return rey__arrowSvg( [
				'right' => $right,
				'class' => $class,
				'attributes' => $attributes,
			] );
		}
		return false;
	}
endif;


if(!function_exists('reycore__get_template_part')):
	/**
	 * Get template part alternative for ReyCore plugin
	 *
	 * @since 1.0.0
	 **/
	function reycore__get_template_part( $slug, $path = false, $skip_theme = false, $args = [] )
	{
		$cache_key = sanitize_key( implode( '-', ['template-part', $slug, REY_CORE_VERSION] ) );
		$template  = (string) wp_cache_get( $cache_key, 'rey-core' );

		if ( ! $template ) {

			if( !$path ){
				$path = REY_CORE_DIR;
			}

			if ( file_exists( STYLESHEETPATH . '/' . $slug . ".php" ) ) {
				$template = STYLESHEETPATH . '/' . $slug . ".php";
			}
			elseif ( ! $skip_theme && file_exists( TEMPLATEPATH . '/' . $slug . ".php" ) ) {
				$template = TEMPLATEPATH . '/' . $slug . ".php";
			}
			elseif ( file_exists( $path . $slug . ".php" ) ) {
				$template = $path . $slug . ".php";
			}

			if( ! (defined('REY_DEV_MODE') && REY_DEV_MODE) ) {
				wp_cache_set( $cache_key, $template, 'rey-core' );
			}
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'reycore/get_template_part', $template, $slug );

		if ( $template ) {
			load_template( $template, false, $args );
		}
	}
endif;


if(!function_exists('reycore__wp_filesystem')):
	/**
	 * Retrieve the reference to the instance of the WP file system
	 * Wrapper for `rey__wp_filesystem` in the theme.
	 * @return $wp_filesystem
	 * @since 1.0.0
	 */
	function reycore__wp_filesystem()
	{
		if( function_exists('rey__wp_filesystem') ){
			return rey__wp_filesystem();
		}
	}
endif;

if(!function_exists('reycore__download_sideload_file')):
/**
 * Download file without adding it into Media Library
 *
 * @since 1.0.0
 **/
function reycore__download_sideload_file( $url = '' )
{
	if( empty($url) ){
		return;
	}

	// Gives us access to the download_url() and wp_handle_sideload() functions
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	$timeout_seconds = 5;

	// Download file to temp dir
	$temp_file = download_url( $url, $timeout_seconds );

	if ( !is_wp_error( $temp_file ) ) {

		$file = array(
			'name'     => basename($url),
			'type'     => 'application/json',
			'tmp_name' => $temp_file,
			'error'    => 0,
			'size'     => filesize($temp_file),
		);

		$overrides = array(
			// Tells WordPress to not look for the POST form
			// fields that would normally be present as
			// we downloaded the file from a remote server, so there
			// will be no form fields
			// Default is true
			'test_form' => false,
			'test_size' => true, // Setting this to false lets WordPress allow empty files, not recommended
			'test_type' => false,
		);

		// Move the temporary file into the uploads directory
		$result = wp_handle_sideload( $file, $overrides );

		// 	$filename  = $result['file']; // Full path to the file
		// 	$local_url = $result['url'];  // URL to the file in the uploads dir
		// 	$type      = $result['type']; // MIME type of the file
		return $result;
	}
}
endif;



if(!function_exists('reycore__kirki_typography_process')):
	/**
	 * Process Kirki's typography CSS
	 *
	 * @return empty string
	 * @since 1.0.0
	 **/
	function reycore__kirki_typography_process($args = [])
	{
		$defaults = [
			'name' => '',
			'prefix' => '',
			'supports' => [
				// options:
				// 'font-family', 'font-size', 'line-height', 'variant', 'font-weight', 'letter-spacing', 'text-transform'
			],
			'wrap' => false,
			'default_values' => []
		];
		$args = reycore__wp_parse_args($args, $defaults);

		if( $args['name'] ) {

			$mod = get_theme_mod($args['name'], $args['default_values']);

			$css = '';

			if( !empty($mod) ){
				foreach ($mod as $key => $value) {
					if( in_array($key, $args['supports']) && !empty($value) ){
						$css .= $args['prefix'] . "{$key}: {$value};";
					}
				}
			}

			if( !empty($css) && $args['wrap'] ){
				$css = $args['wrap'] . "{{$css}}";
			}

			return $css;
		}
		return '';
	}
endif;

if(!function_exists('reycore__parse_text_editor')):
	/**
	 * Parse text coming from rich editor
	 *
	 * @since 1.1.0
	 **/
	function reycore__parse_text_editor( $content ) {

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}
endif;


if(!function_exists('reycore__remove_filters_with_method_name')):
	/**
	 * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
	 * Solution from https://github.com/herewithme/wp-filters-extras/
	 *
	 * @since 1.3.0
	 */
	function reycore__remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
		global $wp_filter;
		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}
		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
					// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
					if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
						unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
					} else {
						unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
					}
				}
			}
		}
		return false;
	}
endif;

if(!function_exists('reycore__remove_filters_for_anonymous_class')):
	/**
	 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
	 * Solution from https://github.com/herewithme/wp-filters-extras/
	 *
	 * @since 1.3.0
	 */
	function reycore__remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
		global $wp_filter;
		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}
		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class, class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
					// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
					if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
						unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
					} else {
						unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
					}
				}
			}
		}
		return false;
	}
endif;


if(!function_exists('reycore__get_tinv_url')):
	/**
	 * Legacy function
	 * @remove in 2.0
	 */
	function reycore__get_tinv_url(){
		if( class_exists('ReyCore_WooCommerce_Wishlist') ){
			return ReyCore_WooCommerce_Wishlist::get_wishlist_url();
		}

		return false;
	}
endif;


if(!function_exists('reycore__get_rey_logo')):
	/**
	 * Get logo image
	 *
	 * @since 1.3.0
	 **/
	function reycore__get_rey_logo($theme = 'black')
	{
		return REY_CORE_URI . sprintf( 'assets/images/logo-simple-%s.svg', esc_attr($theme) );
	}
endif;


if(!function_exists('reycore__preg_grep_keys')):
	/**
	 * Preg Grep for array keys
	 *
	 * @since 1.5.3
	 **/
	function reycore__preg_grep_keys($pattern, $input, $flags = 0) {
		return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}
endif;

if(!function_exists('reycore__current_url')):
	/**
	 * Get current url
	 *
	 * @since 1.6.10
	 **/
	function reycore__current_url()
	{
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
endif;


if(!function_exists('reycore__page_url')):
	function reycore__page_url(){

		$pid = get_queried_object_id();

		if( class_exists('WooCommerce') && is_shop() ){
			$pid = wc_get_page_id( 'shop' );
		}

		return esc_url(get_permalink($pid));
	}
endif;


if(!function_exists('reycore__get_post_types_list')):
	/**
	 * Get CTP list
	 *
	 * @since 1.6.6
	 **/
	function reycore__get_post_types_list( $args = [] )
	{

		$args = wp_parse_args($args, [
			'include' => [],
			'exclude' => [],
		]);

		$exclude = array_merge(['attachment', 'elementor_library', 'rey-global-sections', 'rey-templates'], $args['exclude']);

		$post_types_objects = get_post_types([
				'public' => true,
			], 'objects'
		);

		$post_types_objects = apply_filters( 'reycore/internal/post_type_objects', $post_types_objects );

		$options = [];

		foreach ( $post_types_objects as $cpt_slug => $post_type ) {

			if ( in_array( $cpt_slug, $exclude, true ) ) {
				continue;
			}

			$options[ $cpt_slug ] = $post_type->labels->name;
		}

		return $options;
	}
endif;

if(!function_exists('reycore__can_add_public_content')):
	/**
	 * Can add public content
	 *
	 * @since 1.7.0
	 **/
	function reycore__can_add_public_content()
	{
		return apply_filters('reycore/can_add_public_content', true);
	}
endif;


if(!function_exists('reycore__compare_values')):
	/**
	 * Compares the 2 values given the condition
	 *
	 * @param mixed  $value1   The 1st value in the comparison.
	 * @param mixed  $value2   The 2nd value in the comparison.
	 * @param string $operator The operator we'll use for the comparison.
	 * @return boolean whether The comparison has succeded (true) or failed (false).
	 */
	function reycore__compare_values( $value1, $value2, $operator ) {
		if ( '===' === $operator ) {
			return $value1 === $value2;
		}
		if ( '!==' === $operator ) {
			return $value1 !== $value2;
		}
		if ( ( '!=' === $operator || 'not equal' === $operator ) ) {
			return $value1 != $value2; // phpcs:ignore WordPress.PHP.StrictComparisons
		}
		if ( ( '>=' === $operator || 'greater or equal' === $operator || 'equal or greater' === $operator ) ) {
			return $value2 >= $value1;
		}
		if ( ( '<=' === $operator || 'smaller or equal' === $operator || 'equal or smaller' === $operator ) ) {
			return $value2 <= $value1;
		}
		if ( ( '>' === $operator || 'greater' === $operator ) ) {
			return $value2 > $value1;
		}
		if ( ( '<' === $operator || 'smaller' === $operator ) ) {
			return $value2 < $value1;
		}
		if ( 'contains' === $operator || 'in' === $operator ) {
			if ( is_array( $value1 ) && is_array( $value2 ) ) {
				foreach ( $value2 as $val ) {
					if ( in_array( $val, $value1 ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
						return true;
					}
				}
				return false;
			}
			if ( is_array( $value1 ) && ! is_array( $value2 ) ) {
				return in_array( $value2, $value1 ); // phpcs:ignore WordPress.PHP.StrictInArray
			}
			if ( is_array( $value2 ) && ! is_array( $value1 ) ) {
				return in_array( $value1, $value2 ); // phpcs:ignore WordPress.PHP.StrictInArray
			}
			return ( false !== strrpos( $value1, $value2 ) || false !== strpos( $value2, $value1 ) );
		}
		return $value1 == $value2; // phpcs:ignore WordPress.PHP.StrictComparisons
	}
endif;


if(!function_exists('reycore__get_fallback_mod')):
	function reycore__get_fallback_mod( $mod, $default, $fb_args = [] ){

		$override = apply_filters( "reycore_theme_mod_{$mod}", NULL );

		if( !is_null( $override ) ) {
			return $override;
		}

		// check cst. option default
		if( $mod === '' || is_null( get_theme_mod($mod, NULL) ) ){

			// Fallback args.
			$fb_args = wp_parse_args([
				'mod' => '',
				'value' => '',
				'compare' => '',
			], $fb_args);

			// it's a new installation so just return default
			if( is_null( get_theme_mod($fb_args['mod'], NULL) ) ){
				return $default;
			}

			// it's an existing installation
			// and have to return old value

			// is a true/false and needs comparison
			if( $fb_args['compare'] && $fb_args['value'] ){
				return reycore__compare_values( get_theme_mod($fb_args['mod']), $fb_args['value'], $fb_args['compare'] );
			}

			// just once
			$old_mod_value = get_theme_mod($fb_args['mod']);

			return $old_mod_value;
		}

		// just return the new option
		else {
			return get_theme_mod($mod, $default);
		}

	}
endif;


if(!function_exists('reycore__get_operators')):
	/**
	 * Operators
	 *
	 * @since 1.9.5
	 **/
	function reycore__get_operators( $item = '' )
	{
		$list = [
			'==' => esc_html__('Is equal to', 'rey-core'),
			'!=' => esc_html__('Is not equal to', 'rey-core'),
			'>' => esc_html__('Is greater than', 'rey-core'),
			'<' => esc_html__('Is less than', 'rey-core'),
			'!=empty' => esc_html__('Is not empty', 'rey-core'),
			'==empty' => esc_html__('Is empty', 'rey-core'),
			// '==contains' => esc_html__('Value contains (eg: 5,10,15)', 'rey-core'),
			// LIKE
			// NOT LIKE
			// IN
			// NOT IN
			// BETWEEN
			// NOT BETWEEN
			// NOT EXISTS
			// CONTAINS
		];

		if( ! empty($item) && isset($list[$item]) ){
			return $list[$item];
		}

		return $list;
	}
endif;

if(!function_exists('reycore__implode_html_attributes')):
	/**
	 * Implode and escape HTML attributes for output.
	 *
	 * @since 1.9.4
	 * @param array $raw_attributes Attribute name value pairs.
	 * @return string
	 */
	function reycore__implode_html_attributes( $raw_attributes ) {
		$attributes = array();
		foreach ( $raw_attributes as $name => $value ) {
			$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}
		return implode( ' ', $attributes );
	}
endif;


if(!function_exists('reycore__is_mobile')):
	/**
	 * Checks if mobile
	 *
	 * @since 1.9.7
	 **/
	function reycore__is_mobile( $include_ipad = false )
	{

		$status = wp_is_mobile();

		if( $status && (! $include_ipad && strpos( $_SERVER['HTTP_USER_AGENT'], 'iPad' )) ){
			$status = false;
		}

		return apply_filters('reycore/is_mobile', $status );
	}
endif;


if(!function_exists('reycore__supports_mobile_caching')):
	/**
	 * Determines if separate mobile caching is supported
	 *
	 * @since 2.0.5
	 **/
	function reycore__supports_mobile_caching()
	{
		return apply_filters('reycore/supports_mobile_caching', false );
	}
endif;


if(!function_exists('reycore__get_terms_by_common_posts')):
	/**
	 * Returns terms from a different taxonomy using common published posts as search criteria.
	 * Example: get list of post tags assigned to all posts in a specific category.
	 *
	 * @param array $ids Array of source term ids.
	 * @param string $source Source taxonomy.
	 * @param string $target Target taxonomy.
	 *
	 * @return array
	 */
	function reycore__get_terms_by_common_posts( array $ids = [], $source = 'category', $target = 'post_tag' ) {

		$ids = array_filter( array_map( 'absint', $ids ) );

		if( !empty( $ids ) ) {
			global $wpdb;

			$ids_sql = join( ', ', array_fill( 0, count( $ids ), '%d' ) );

			$sql = "SELECT DISTINCT
				tterms.term_id as id
			FROM
				{$wpdb->posts} as p1
				LEFT JOIN {$wpdb->term_relationships} as r1 ON p1.ID = r1.object_ID
				LEFT JOIN {$wpdb->term_taxonomy} as stermtax ON r1.term_taxonomy_id = stermtax.term_taxonomy_id
				LEFT JOIN {$wpdb->terms} as sterms ON stermtax.term_id = sterms.term_id,
				{$wpdb->posts} as p2
				LEFT JOIN {$wpdb->term_relationships} as r2 ON p2.ID = r2.object_ID
				LEFT JOIN {$wpdb->term_taxonomy} as ttermtax ON r2.term_taxonomy_id = ttermtax.term_taxonomy_id
				LEFT JOIN {$wpdb->terms} as tterms ON ttermtax.term_id = tterms.term_id
			WHERE (
				stermtax.taxonomy = %s
				AND sterms.term_id IN ( {$ids_sql} )
				AND ttermtax.taxonomy = %s
				AND p1.ID = p2.ID
				AND p1.post_status = 'publish'
				AND p2.post_status = 'publish'
			)";

			$query = call_user_func_array( [$wpdb, 'prepare'], array_merge(
				array( $sql ),
				array( $source ),
				$ids,
				array( $target )
			) );

			$results = $wpdb->get_results( $query );

			$terms = empty( $results ) ? [] :  wp_list_pluck( $results, 'id' );

			return empty( $terms ) ? [] : get_terms([
				'taxonomy' => $target,
				'include' => $terms
			]);
		}

		return [];
	}
endif;

/**
 * Deprecated
 *
 * @return empty array
 */
function reycore__get_all_menus(){
	return [];
}


if(!function_exists('reycore__get_post_id_from_slug')):
	/**
	 * Retrieves post id from a slug
	 *
	 * @param string $name
	 * @param string $post_type
	 * @return string
	 */
	function reycore__get_post_id_from_slug( $name, $post_type = 'post' ){

		$query = new WP_Query([
			'name'        => $name,
			'post_status' => 'publish',
			'post_type'   => $post_type,
			'numberposts' => 1,
			'fields'      => 'ids',
		]);

		$posts = $query->get_posts();

		return array_shift( $posts );

	}
endif;
