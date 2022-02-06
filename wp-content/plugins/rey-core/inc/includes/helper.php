<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCoreHelper') ):

	class ReyCoreHelper
	{

		private static $_instance = null;

		public $theme_settings = [];

		public $parent_categories = [];

		const STATIC_TRANSIENTS = [];

		const QUERY = '_rey_query_';
		const PRODUCTS = '_rey_products_';
		const TERMS = '_rey_terms_';
		const MENU = '_rey_menu_';
		const CACHE_QUERIES = false;

		private function __construct()
		{
			add_action( 'init', [ $this, 'init' ]);
		}

		function init(){
			$this->prevent_wc_login_reg_process();
			// $this->set_theme_settings();
		}

		function get_theme_setting( $setting, $default = null ){

			// if setting is set
			if( $setting &&
				! empty($this->theme_settings) &&
				isset($this->theme_settings[$setting])
			){
				return $this->theme_settings[$setting];
			}

			return $default;
		}

		function set_theme_settings(){
			$this->theme_settings = get_fields(REY_CORE_THEME_NAME);
		}

		function get_transient( $transient_name, $cb, $expiration = false ){

			if( ! $transient_name ){
				return;
			}

			if( false !== ($content = get_transient($transient_name)) ){
				return $content;
			}

			if( ! $expiration && isset(self::STATIC_TRANSIENTS[$transient_name]) ){
				$expiration = self::STATIC_TRANSIENTS[$transient_name];
			}

			$content = $cb();

			set_transient($transient_name, $content, $expiration);

			return $content;
		}

		function clean_transient($post_id){
			if ($post_id === REY_CORE_THEME_NAME) {

				if( function_exists('rey__maybe_disable_obj_cache') ){
					rey__maybe_disable_obj_cache();
				}

				foreach (self::STATIC_TRANSIENTS as $k => $v){
					delete_transient($k);
				}
			}
		}

		function get_terms($args = []){

			if( empty($args) ){
				return [];
			}

			if( ! self::CACHE_QUERIES ){
				return get_terms($args);
			}

			$tax = '';

			if( isset($args['taxonomy']) ){
				$tax = $args['taxonomy'];
			}

			$name = self::TERMS . $tax . '_' . md5(wp_json_encode($args));

			return $this->get_transient( $name, function() use ($args){
				return get_terms($args);
			}, WEEK_IN_SECONDS);
		}

		function get_products_query($args = []){

			if( empty($args) ){
				return [];
			}

			if( ! self::CACHE_QUERIES ){
				return new WP_Query( $args );
			}

			$name = self::PRODUCTS . md5(wp_json_encode($args));

			// make sure to force
			$args['post_type'] = 'product';

			return $this->get_transient( $name, function() use ($args){
				return new WP_Query( $args );
			}, WEEK_IN_SECONDS);
		}

		function get_query($args = []){

			if( empty($args) ){
				return [];
			}

			if( ! self::CACHE_QUERIES ){
				return new WP_Query( $args );
			}

			$pt = '';

			if( isset($args['post_type']) ){
				$pt = $args['post_type'];
			}

			$name = self::QUERY . $pt . '_' . md5(wp_json_encode($args));

			return $this->get_transient( $name, function() use ($args){
				return new WP_Query( $args );
			}, WEEK_IN_SECONDS);
		}

		function wp_nav_menu($args = []){

			if( empty($args) ){
				return [];
			}

			if( ! self::CACHE_QUERIES ){
				return wp_nav_menu( $args );
			}

			$menu_id = '';

			if( isset($args['menu']) ){
				$menu_id = $args['menu'];
			}

			$name = self::MENU . $menu_id . '_' . md5(wp_json_encode($args));

			return $this->get_transient( $name, function() use ($args){
				return wp_nav_menu( $args );
			}, WEEK_IN_SECONDS);
		}

		/**
		 * Get a list of all WordPress menus
		 *
		 * @since 1.0.0
		 */
		public function get_all_menus( $clean = true ){

			$terms = $this->get_terms( [
				'taxonomy' => 'nav_menu'
			] );

			if( $clean ){

				$menus = [];
				foreach ($terms as $term) {
					$menus[$term->slug] = $term->name;
				}
				return $menus;
			}

			if( !is_array($terms) ){
				return [];
			}
			else {
				return $terms;
			}
		}

		/**
		 * Helps Ajax Login from Rey.
		 * Added here to be loaded before plugins_loaded hook.
		 * @since 1.7.0
		 */
		function prevent_wc_login_reg_process(){
			if( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'reycore_account_forms' ){
				remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_login'], 20 );
				remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_registration'], 20 );
				remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_lost_password'], 20 );
			}
		}

		public static function get_all_image_sizes( $add_default = true ) {
			global $_wp_additional_image_sizes;

			$default_image_sizes = [ 'thumbnail', 'medium', 'medium_large', 'large' ];

			$wp_image_sizes = [];

			foreach ( $default_image_sizes as $size ) {
				$wp_image_sizes[ $size ] = [
					'width' => (int) get_option( $size . '_size_w' ),
					'height' => (int) get_option( $size . '_size_h' ),
					'crop' => (bool) get_option( $size . '_crop' ),
				];
			}

			if ( $_wp_additional_image_sizes ) {
				$wp_image_sizes = array_merge( $wp_image_sizes, $_wp_additional_image_sizes );
			}

			/** This filter is documented in wp-admin/includes/media.php */
			$wp_image_sizes = apply_filters( 'image_size_names_choose', $wp_image_sizes );

			$image_sizes = [];

			if( $add_default ){
				$image_sizes[''] = esc_html__( 'Default', 'rey-core' );
			}

			foreach ( $wp_image_sizes as $size_key => $size_attributes ) {

				$control_title = ucwords( str_replace( '_', ' ', $size_key ) );

				if ( is_array( $size_attributes ) ) {
					$control_title .= sprintf( ' - %d x %d', $size_attributes['width'], $size_attributes['height'] );
				}

				$image_sizes[ $size_key ] = $control_title;
			}

			$image_sizes['full'] = _x( 'Full', 'Image Size Control', 'rey-core' );

			return $image_sizes;
		}

		/**
		 * @param int $number
		 * @return string
		 */
		function numberToRoman($number) {
			$map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
			$returnValue = '';
			while ($number > 0) {
				foreach ($map as $roman => $int) {
					if($number >= $int) {
						$number -= $int;
						$returnValue .= $roman;
						break;
					}
				}
			}
			return $returnValue;
		}


		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCoreHelper
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	function reyCoreHelper(){
		return ReyCoreHelper::getInstance();
	}

	reyCoreHelper();

endif;
