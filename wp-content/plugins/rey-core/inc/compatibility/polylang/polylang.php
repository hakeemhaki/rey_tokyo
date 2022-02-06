<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'Polylang' ) && !class_exists('ReyCore_Compatibility__Polylang') ):
	/**
	 * Polylang Plugin Compatibility
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Compatibility__Polylang
	{
		private static $_instance = null;

		private function __construct()
		{
			add_action('rey/header/row', [$this, 'header'], 60);
			add_action('rey/mobile_nav/footer', [$this, 'mobile'], 10);
			add_filter( 'reycore/woocommerce/variations/terms_transients', [$this, 'variation_transients'] );
			add_filter( 'reycore/elementor/gs_id', [$this, 'maybe_translate_id'] );
			add_filter( 'reycore/theme_mod/translate_ids', [$this, 'maybe_translate_id'] );

		}

		/**
		 * Get PolyLang data
		 *
		 * @since 1.0.0
		 **/
		function data(){

			if( function_exists('pll_current_language') && function_exists('pll_the_languages') ):
				$languages = [];
				$translations = pll_the_languages([
					'raw' => 1,
					'hide_if_empty' => 0
				]);

				$flag = false;

				if( !empty($translations) ){

					foreach ($translations as $key => $language) {
						$languages[$key] = [
							'code' => $key,
							'flag' => $language['flag'],
							'name' => $language['name'],
							'active' => $language['current_lang'],
							'url' => $language['url']
						];

						if( $language['current_lang'] ){
							$flag = $language['flag'];
						}
					}

					return [
						'current' => pll_current_language(),
						'current_flag' => $flag,
						'languages' => $languages,
						'type' => 'polylang'
					];
				}
			endif;

			return false;
		}

		/**
		 * Add language switcher for PolyLang into Header
		 *
		 * @since 1.0.0
		 **/
		function header($options = []){
			if($data = $this->data()) {
				echo reycore__language_switcher_markup($data, $options);
			}
		}

		/**
		 * Add language switcher for PolyLang into Mobile menu panel
		 *
		 * @since 1.0.0
		 **/
		function mobile(){
			if($data = $this->data()) {
				echo reycore__language_switcher_markup_mobile($data);
			}
		}

		function variation_transients( $transients ){

			foreach ($transients as $name => $transient) {
				$transients[$name] = sprintf('%s_%s', $transient, pll_current_language());
			}

			return $transients;
		}

		public function maybe_translate_id( $data ){

			if( ! function_exists('pll_get_post') ){
				return $data;
			}

			if( ! apply_filters('reycore/multilanguage/translate_ids', true) ){
				return $data;
			}

            if( $translated_id = pll_get_post($data, pll_current_language('slug')) ){
				return $translated_id;
			}

			if ( is_array( $data ) ) {
				$translated_ids = [];
				foreach ($data as $post_id) {
					if( $tid = pll_get_post($post_id, pll_current_language('slug')) ){
						$translated_ids[] = $tid;
					}
				}
				if( !empty($translated_ids) ){
					return $translated_ids;
				}
			} else {
				if( $translated_id = pll_get_post($data, pll_current_language('slug')) ){
					return $translated_id;
				}
			}

			return $data;
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__Polylang::getInstance();
endif;
