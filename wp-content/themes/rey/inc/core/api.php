<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists('ReyTheme_API') ):
	/**
	 * Handles API calls to Rey API server.
	 *
	 * @since 1.0.0
	 */
	class ReyTheme_API
	{

		/**
		 * Holds the reference to the instance of this class
		 * @var ReyTheme_API
		 */
		private static $_instance = null;

		const API_SITE_URL = 'https://api.reytheme.com/';
		const API_URL = self::API_SITE_URL . 'wp-json/rey-api/v1/';
		const API_FILES_URL = self::API_SITE_URL . 'wp-content/uploads/api_routes/';

		/**
		 * API Endpoints
		 */
		const REY_API_ENDPOINT__REGISTER = 'register/';
		const REY_API_ENDPOINT__DEREGISTER = 'deregister/';
		const REY_API_ENDPOINT__NEWSLETTER = 'subscribe_newsletter/';
		const REY_API_ENDPOINT__GET_THEME_DOWNLOAD_URL = 'get_theme_download_url/';
		const REY_API_ENDPOINT__GET_PLUGINS = 'get_plugins/';
		const REY_API_ENDPOINT__GET_PLUGIN_DATA = 'get_plugin_data/';
		const REY_API_ENDPOINT__GET_DEMOS = 'get_demos/';
		const REY_API_ENDPOINT__GET_DEMO_DATA = 'get_demo_data/';
		const REY_API_ENDPOINT__GET_TEMPLATE_DATA = 'get_template_data/';


		/**
		 * ReyTheme_API constructor.
		 */
		private function __construct() {}

		public function get_api_url(){
			if( defined('DEV_REY_API_URL') ){
				return DEV_REY_API_URL;
			}
			return self::API_URL;
		}

		function get_purchase_code(){
			return ReyTheme_Base::get_purchase_code();
		}

		/**
		 * Send Registration request to API Server
		 *
		 * @since 1.0.0
		 */
		public function register($args){
			return $this->remote_post( $args, self::REY_API_ENDPOINT__REGISTER );
		}

		/**
		 * Send *DE*Registration request to API Server
		 *
		 * @since 1.0.0
		 */
		public function deregister($args){
			return $this->remote_post( $args, self::REY_API_ENDPOINT__DEREGISTER );
		}

		/**
		 * Get Theme version for update checks
		 *
		 * @since 1.0.0
		 */
		public function get_theme_version(){
			return $this->file_remote_get( self::API_FILES_URL . 'version.json' );
		}

		/**
		 * Get file versions
		 *
		 * @since 2.0.0
		 */
		public function get_file($file){
			return $this->file_remote_get( self::API_FILES_URL . $file );
		}

		/**
		 * Get theme download url for updates
		 *
		 * @since 1.0.0
		 */
		public function get_theme_download_url(){
			return $this->remote_get( [
				'purchase_code' => $this->get_purchase_code()
			], self::REY_API_ENDPOINT__GET_THEME_DOWNLOAD_URL );
		}

		/**
		 * Get plugins list
		 *
		 * @since 1.0.0
		 */
		public function get_plugins( $required = 3 ){
			return $this->remote_get( [
				'purchase_code' => $this->get_purchase_code(),
				'required' => $required
			], self::REY_API_ENDPOINT__GET_PLUGINS );
		}

		/**
		 * Get plugin data + download url
		 *
		 * @since 1.0.0
		 */
		public function get_plugin_data( $slug ){
			return $this->remote_get( [
				'purchase_code' => $this->get_purchase_code(),
				'slug' => $slug
			], self::REY_API_ENDPOINT__GET_PLUGIN_DATA );
		}


		/**
		 * Get demos list
		 *
		 * @since 1.0.0
		 */
		public function get_demos(){
			return $this->remote_get( [
				'purchase_code' => $this->get_purchase_code()
			], self::REY_API_ENDPOINT__GET_DEMOS );
		}

		/**
		 * Get demo data + download url
		 *
		 * @since 1.0.0
		 */
		public function get_demo_data( $slug ){
			return $this->remote_get( [
				'purchase_code' => $this->get_purchase_code(),
				'slug' => $slug
			], self::REY_API_ENDPOINT__GET_DEMO_DATA );
		}


		/**
		 * Get templates list
		 * @param $type all,library,demo_bar
		 *
		 * @since 1.0.0
		 */
		public function get_templates( $type = 'all' ){
			return $this->file_remote_get( self::API_FILES_URL . sprintf('templates_%s.json', $type) );
		}

		/**
		 * Get template download url
		 *
		 * @since 1.0.0
		 */
		public function get_template_data( $sku ){
			return $this->remote_get( [
				'purchase_code' => $this->get_purchase_code(),
				'sku' => $sku
			], self::REY_API_ENDPOINT__GET_TEMPLATE_DATA );
		}


		/**
		 * Send newsletter subscription request to API server
		 *
		 * @since 1.0.0
		 */
		public function subscribe_newsletter($email_address){
			return $this->remote_post( [
				'email_address' => $email_address
			], self::REY_API_ENDPOINT__NEWSLETTER );
		}

		/**
		 * Makes a download URL for either theme or plugins.
		 *
		 * @since 1.4.2
		 */
		public function get_download_url( $item = 'theme', $version = '' ){
			return add_query_arg([
				'download_item' => $item,
				'version' => $version,
				'purchase_code' => $this->get_purchase_code()
				], self::API_SITE_URL
			);
		}

		/**
		 * Wrapper for wp_remote_post, used for
		 * API Server requests
		 *
		 * @since 1.0.0
		 */
		protected function remote_post( $body_args, $endpoint ) {

			$response = wp_remote_post( $this->get_api_url() . $endpoint, [
				'timeout' => 40,
				'body' => (array) $body_args,
			] );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			return $this->parse_remote_data( $response );
		}

		/**
		 * Wrapper for wp_remote_get, used for
		 * API Server requests
		 *
		 * @since 1.0.0
		 */
		protected function remote_get( $body_args, $endpoint ) {

			$args = apply_filters('rey/api/remote_get/args', [
				'timeout' => 40,
				'body' => (array) $body_args,
			], $endpoint);

			$args['body']['ftime'] = current_time('timestamp');

			$response = wp_remote_get( $this->get_api_url() . $endpoint, $args );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			return $this->parse_remote_data( $response );
		}

		/**
		 * Wrapper for wp_remote_get, used for
		 * API Server requests on JSON files
		 *
		 * @since 1.0.7
		 */
		protected function file_remote_get( $filepath ) {

			$response = wp_remote_get( $filepath, [
				'timeout' => 40
			] );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			return $this->parse_remote_data( $response );
		}

		/**
		 * Parse the remote data
		 *
		 * @since 1.0.7
		 */
		protected function parse_remote_data( $response ){

			$data = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $data ) || ! is_array( $data ) ) {
				return new \WP_Error( 'no_json', $this->get_error_message('no_json') );
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== (int) $response_code ) {
				return new \WP_Error( $response_code, sprintf(
					esc_html__( '%s (HTTP Error)', 'rey' ),
					$this->get_response_error( $data )
				) );
			}

			return $data;
		}

		public function get_response_error( $data ){
			if( is_array($data) ){
				$return = [];
				array_walk_recursive($data, function($a) use (&$return) {
					$return[] = $a;
				});
				return implode('; ', $return);
			}
			else {
				return $data;
			}
		}

		/**
		 * Predefined error codes & messages
		 *
		 * @since 1.0.0
		 */
		public function get_errors() {
			return apply_filters('rey/api/errors', [
				'revoked' => rey__wp_kses( __( '<strong>Your purchase has been cancelled</strong> ( most likely due to a refund request ). Please consider acquiring a new license.', 'rey' ) ),
				'another_item' => rey__wp_kses( __( 'This purchase code seems to belong to another item, not Rey Theme. Please double check the purchase code.', 'rey' ) ),
				'not_valid' => rey__wp_kses( __( 'The purchase code is invalid. Please double check it.', 'rey' ) ),
				'invalid_request' => rey__wp_kses( __( 'Sorry, invalid API request.', 'rey' ) ),
				'retry' => rey__wp_kses( __( 'An error occurred, please try again.', 'rey' ) ),
				'already_exists' => rey__wp_kses( __( 'This purchase code is already in use. Either purchase a new Rey Theme license, or deregister this purchase code from the other site where its in use, or <a href="https://support.reytheme.com/deregister-rey/" target="_blank">use this tool</a> to force deregister.', 'rey' ) ),
				'no_json' => rey__wp_kses( __( 'Response from API server seems to be empty. Please try again, or make sure your server is not blocking the API calls towards https://api.reytheme.com/. Follow <a href="https://support.reytheme.com/kb/response-from-api-server-seems-to-be-empty/" target="_blank">this article</a> for more informations.', 'rey' ) ),
			] );
		}

		/**
		 * Get predefined errors
		 *
		 * @since 1.0.0
		 */
		public function get_error_message( $error ) {
			$errors = $this->get_errors();

			if ( isset( $errors[ $error ] ) ) {
				$error_msg = $errors[ $error ];
			} else {
				$error_msg = $error . esc_html__( ' If the problem persists, contact our support.', 'rey' );
			}

			return $error_msg;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyTheme_API
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

endif;
