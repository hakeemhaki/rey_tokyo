<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( function_exists( 'autoptimize_autoload' ) && !class_exists('ReyCore_Compatibility__Autoptimize') ):
	/**
	 * Wp Store Locator Plugin Compatibility
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Compatibility__Autoptimize
	{
		private static $_instance = null;

		private function __construct()
		{
			add_filter('rey/main_script_params', [$this, 'filter_main_script_params']);
			add_filter('body_class', [$this, 'body_class'], 10);
		}

		public function is_lazy_load_enabled(){
			// check settings option
			return ($autoptimize_img_settings = get_option('autoptimize_imgopt_settings', [])) &&
				// check if lazy load is enabled
				isset($autoptimize_img_settings['autoptimize_imgopt_checkbox_field_3']) && $autoptimize_img_settings['autoptimize_imgopt_checkbox_field_3'] === '1';
		}

		public function filter_main_script_params($params)
		{
			if( $this->is_lazy_load_enabled() ) {
				$params['lazy_load'] = true;
			}

			return $params;
		}

		public function body_class($classes)
		{
			if( $this->is_lazy_load_enabled() ) {
				// $classes[] = '--lazyload-enabled';
			}

			return $classes;
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__Autoptimize::getInstance();
endif;
