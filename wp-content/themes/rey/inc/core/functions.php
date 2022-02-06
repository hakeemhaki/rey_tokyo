<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if(!function_exists('rey__get_theme_url')):
	/**
	 * Get Rey Theme URL
	 *
	 * @since 1.0.0
	 **/
	function rey__get_theme_url()
	{
		return esc_url( wp_get_theme( REY_THEME_NAME )->get('ThemeURI') );
	}
endif;


if(!function_exists('rey__get_core_path')):
	/**
	 * Get Core Plugin Path
	 *
	 * @since 1.0.0
	 **/
	function rey__get_core_path()
	{
		return sprintf('%s/%s.php', REY_THEME_CORE_SLUG, REY_THEME_CORE_SLUG);
	}
endif;


if(!function_exists('rey__config')):
	/**
	 * Internal configuration settings
	 *
	 * @param $setting The setting to pull
	 * @return string
	 *
	 * @since 1.0.0
	 */
	function rey__config( $setting = '' ) {

		$settings = apply_filters('rey/config_settings', [
			'animate_blog_items' => true
		]);

		if( isset($settings[$setting]) ) {
			return $settings[$setting];
		}
		return false;
	}
endif;


if(!function_exists('rey__get_post_id')):
	/**
	 * Wrapper for queried object
	 *
	 * @since 1.0.0
	 */
	function rey__get_post_id() {

		if( class_exists('WooCommerce') && is_shop() ){
			return wc_get_page_id('shop');
		}
		elseif( is_home() ){
			return absint( get_option('page_for_posts') );
		}
		elseif( is_tax() || is_archive() || is_author() || is_category() || is_tag() ){
			if( apply_filters('rey/get_queried_object_id', false, 'rey' ) ){
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


if(!function_exists('rey__acf_get_field')):
	/**
	 * Get ACF Field - wrapper for get_field
	 *
	 * @since 1.0.0
	 **/
	function rey__acf_get_field( $name, $pid = false, $return = false )
	{

		if( !$pid ) {
			$pid = rey__get_post_id();
		}

		// check for ACF and get the field
		if( class_exists('ACF') && $opt = get_field( $name, $pid ) )  {
			return $opt;
		}

		return $return;
	}
endif;


if(!function_exists('rey__get_option')):
	/**
	 * Get Option - wrapper for get_theme_mod and get_field
	 * overrides rey__get_option from theme
	 *
	 * @since 1.0.0
	 **/
	function rey__get_option( $name, $default = false )
	{

		// check for ACF and get the field
		if( $opt = rey__acf_get_field( $name ) )  {
			return apply_filters("rey_acf_option_{$name}", $opt);
		}

		return get_theme_mod( $name, $default);
	}
endif;


if(!function_exists('rey__is_blog_list')):
	/**
	 * Check if it's a blog listing
	 *
	 * @since 1.0.0
	 **/
	function rey__is_blog_list()
	{
		return apply_filters('rey/is_blog_list', ( is_archive() || is_author() || is_category() || is_home() || is_single() || is_tag()) && ( 'post' == get_post_type() || rey__ctp_supports_blog() ));
	}
endif;


if(!function_exists('rey__wp_parse_args')):
	/**
	 * Recursive wp_parse_args WordPress function which handles multidimensional arrays
	 * @url http://mekshq.com/recursive-wp-parse-args-wordpress-function/
	 * @param  array &$a Args
	 * @param  array $b Defaults
	 * @since: 1.0.0
	 */
	function rey__wp_parse_args( &$a, $b )
	{
		$a = (array)$a;
		$b = (array)$b;
		$result = $b;
		foreach ( $a as $k => &$v )
		{
			if ( is_array( $v ) && isset( $result[ $k ] ) )
			{
				$result[ $k ] = rey__wp_parse_args( $v, $result[ $k ] );
			}
			else
			{
				$result[ $k ] = $v;
			}
		}
		return $result;
	}
endif;


if(!function_exists('rey__can_show_post_thumbnail')):
	/**
	 * Determines if post thumbnail can be displayed.
	 * @since 1.0.0
	 */
	function rey__can_show_post_thumbnail() {

		$can = ! post_password_required() && ! is_attachment() && has_post_thumbnail();

		if(
			(!is_singular() && !get_theme_mod('blog_thumbnail_visibility', true)) ||
			(is_singular() && !get_theme_mod('post_thumbnail_visibility', true))
		){
			$can = false;
		}

		return apply_filters( 'rey/content/post_thumbnail', $can );
	}
endif;


if(!function_exists('rey__estimated_reading_time')):
	/**
	 * Get estimated read time (minutes) for current post in loop.
	 * @return int Estimated time in minutes to read post
	 * @since 1.0.0
	 */
	function rey__estimated_reading_time(){
		$post = get_post();
		$words = str_word_count(strip_tags($post->post_content));
		$minutes = floor($words / 120);
		if($minutes == 0) $minutes = 1;
		return $minutes;
	}
endif;


if(!function_exists('rey__words_limit')):
	/**
	 * Truncate a string based on provided word count and include terminator
	 * @param       string $string      String to be truncated
	 * @param       int $length         Number of characters to allow before split
	 * @param       string $terminator  (Optional) String terminator to be used
	 * @return      string              Truncated string with add terminator
	 * @since 1.0.0
	 */
	function rey__words_limit($string, $length, $terminator = ""){

		if(mb_strlen($string) <= $length){
			$string = $string;
		}
		else{
			$string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length)) . $terminator;
		}

		return $string;
	}
endif;


if(!function_exists('rey__unique_id')):
	/**
	 * Get unique ID.
	 *
	 * This is a PHP implementation of Underscore's uniqueId method. A static variable
	 * contains an integer that is incremented with each call. This number is returned
	 * with the optional prefix. As such the returned value is not universally unique,
	 * but it is unique across the life of the PHP process.
	 * @staticvar int $id_counter
	 *
	 * @param string $prefix Prefix for the returned ID.
	 * @return string Unique ID.
	 * @since 1.0.0
	 */
	function rey__unique_id( $prefix = '' ) {
		static $id_counter = 0;
		if ( function_exists( 'wp_unique_id' ) ) {
			return wp_unique_id( $prefix );
		}
		return $prefix . (string) ++$id_counter;
	}
endif;


if(!function_exists('rey__get_first_img')):
	/**
	 * Get first image in post
	 *
	 * @param post - POST ID
	 * @param attachment_id bool . Return id instead of URL.
     * @return string
	 * @since 1.0.0
	 */
	function rey__get_first_img($post = 0, $attachment_id = false) {

		if ( ! $post = get_post( $post ) )
			return array();

		$img = '';
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);

		if( isset($matches[1][0]) ){
			$img = $matches[1][0];
		}

		if( !empty($img) ) {

			// Return Img ID
			if( $attachment_id ) {
				// cleanup path from url query's
				$img_url = parse_url($img);
				$img = sprintf('%s://%s%s', $img_url['scheme'], $img_url['host'], $img_url['path']);
				return attachment_url_to_postid( $img );
			}
			else {
				return $img;
			}
		}
		return false;
	}
endif;


if(!function_exists('rey__log_error')):
	/**
	 * Log Errors
	 *
	 * @since 1.0.0
	 **/
	function rey__log_error( $source, $error )
	{
		if( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ){
			error_log(var_export( [$source, $error] ,1));
		}
	}
endif;

if(!function_exists('rey__clean')):
	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	function rey__clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'rey__clean', $var );
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


if(!function_exists('rey__is_godaddy')):
	/**
	 * This function will search in various places for any of the default GoDaddy files,
	 * and if any is found then we assume this is a GoDaddy hosting
	 * @return bool
	 * @since 1.0.0
	 */
	function rey__is_godaddy()
	{
		$root       = trailingslashit(ABSPATH);
		$pluginsDir = (defined('WP_CONTENT_DIR') ? trailingslashit(WP_CONTENT_DIR) . 'mu-plugins/' : $root . 'wp-content/mu-plugins/');

		if ( is_file( $root . 'gd-config.php' )) {
			return true;
		}
		elseif ( is_dir($pluginsDir . 'gd-system-plugin') || is_file($pluginsDir . 'gd-system-plugin.php') ) {
			return true;
		}
		elseif ( class_exists('\WPaaS\Plugin') ) {
			return true;
		}
		return false;
	}
endif;


if(!function_exists('rey__maybe_disable_obj_cache')):
	/**
	 * This function will temporarily disable WordPress object cache
	 * Useful for hosts such as GoDaddy that causes WP transients API not working properly
	 * @return bool
	 * @since 1.0.0
	 */
	function rey__maybe_disable_obj_cache()
	{
		$status = false;

		if( rey__is_godaddy() ){
			$status = true;
		}

		if( apply_filters('rey/temporarily_disable_obj_cache', $status) ){
			wp_using_ext_object_cache( false );
		}
	}
endif;


if(!function_exists('rey__wp_filesystem')):
	/**
	 * Retrieve the reference to the instance of the WP file system
	 * @return $wp_filesystem
	 * @since 1.0.0
	 */
	function rey__wp_filesystem()
	{
		//#! Set the permission constants if not already set.
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', ( fileperms( ABSPATH ) & 0777 | 0755 ) );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
		}

		//#! Setup a new instance of WP_Filesystem_Direct and use it
		global $wp_filesystem;

		if ( ! ( $wp_filesystem instanceof \WP_Filesystem_Base ) ) {
			if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
			}
			$wp_filesystem = new \WP_Filesystem_Direct( [] );
		}
		return $wp_filesystem;
	}
endif;


if(!function_exists('rey__wp_kses')):
	/**
	 * Allow basic tags
	 *
	 * @since 1.0.0
	 **/
	function rey__wp_kses($string = '')
	{
		return wp_kses($string, [
			'a' => [
				'class' => [],
				'href' => [],
				'target' => []
			],
			'code' => [
				'class' => []
			],
			'strong' => [],
			'br' => [],
			'em' => [],
			'p' => [
				'class' => []
			],
			'span' => [
				'class' => []
			],
		]);
	}
endif;


if(!function_exists('rey__implode_html_attributes')):
	/**
	 * Implode and escape HTML attributes for output.
	 *
	 * @since 1.9.4
	 * @param array $raw_attributes Attribute name value pairs.
	 * @return string
	 */
	function rey__implode_html_attributes( $raw_attributes ) {
		$attributes = array();
		foreach ( $raw_attributes as $name => $value ) {
			$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}
		return implode( ' ', $attributes );
	}
endif;
