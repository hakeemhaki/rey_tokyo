<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'SitePress' ) && !class_exists('ReyCore_Compatibility__Wpml') ):
	/**
	 * WPML Plugin Compatibility
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Compatibility__Wpml
	{
		private static $_instance = null;

		private function __construct()
		{
			add_action('rey/header/row', [$this, 'header'], 60);
			add_action('rey/mobile_nav/footer', [$this, 'mobile'], 10);
			add_filter( 'reycore/woocommerce/variations/terms_transients', [$this, 'variation_transients'] );
			add_filter( 'reycore/elementor/gs_id', [$this, 'maybe_translate_id'], 10, 2 );
			add_filter( 'reycore/theme_mod/translate_ids', [$this, 'maybe_translate_id'], 10, 2 );
			add_filter( 'reycore/translate_ids', [$this, 'maybe_translate_id'], 10, 2 );
			add_filter( 'wcml_multi_currency_ajax_actions', [$this, 'multi_currency_ajax_actions'] );
		}

		/**
		 * Get WPML data
		 *
		 * @since 1.0.0
		 **/
		function data(){

			if( defined('ICL_LANGUAGE_CODE') ):
				$languages = [];
				$translations = apply_filters( 'wpml_active_languages', NULL, [
					'skip_missing' => 0
				] );

				if( !empty($translations) ){
					foreach ($translations as $key => $language) {
						$languages[$key] = [
							'code' => $key,
							'flag' => $language['country_flag_url'],
							'name' => $language['native_name'],
							'active' => $language['active'],
							'url' => $language['url']
						];
						if( $language['active'] ){
							$flag = $language['country_flag_url'];
						}
					}
					return [
						'current' => ICL_LANGUAGE_CODE,
						'current_flag' => $flag,
						'languages' => $languages,
						'type' => 'wpml'
					];
				}
			endif;

			return false;
		}

		/**
		 * Add language switcher for WPML into Header
		 *
		 * @since 1.0.0
		 **/
		function header($options = []){
			if($data = $this->data()) {
				echo reycore__language_switcher_markup($data, $options);
			}
		}

		/**
		 * Add language switcher for WPML into Mobile menu panel
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
				$transients[$name] = sprintf('%s_%s', $transient, defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '');
			}

			return $transients;
		}

		/**
         * See if there is a translated page available for the global sections IDs.
         *
         * @since 1.6.3
         * @see    https://wpml.org/documentation/support/creating-multilingual-wordpress-themes/language-dependent-ids/#2
         */
        public function maybe_translate_id( $data, $post_type = '' ) {

			if( ! apply_filters('reycore/multilanguage/translate_ids', true) ){
				return $data;
			}

			if ( is_array( $data ) ) {
				$translated_ids = [];
				foreach ($data as $post_id) {

					if( ! is_numeric($post_id) && in_array($post_type, ['product_cat'], true) ){
						$term = get_term_by('slug', $post_id, $post_type );
						$post_id = is_object($term) && $term->term_id ? $term->term_id : 0;
					}

					if( $tid = apply_filters( 'wpml_object_id', $post_id, $post_type, true ) ){
						$translated_ids[] = $tid;
					}
				}
				if( !empty($translated_ids) ){
					return $translated_ids;
				}
			} else {

				if( ! is_numeric($data) && in_array($post_type, ['product_cat'], true) ){
					$term = get_term_by('slug', $data, $post_type );
					$data = is_object($term) && $term->term_id ? $term->term_id : 0;
				}

				if( $translated_id = apply_filters( 'wpml_object_id', $data, $post_type, true ) ){
					return $translated_id;
				}
			}

            return $data;
		}

		function multi_currency_ajax_actions( $ajax_actions ) {

			$ajax_actions[] = 'reycore_ajax_add_to_cart';
			$ajax_actions[] = 'rey_update_minicart';
			$ajax_actions[] = 'get_quickview_product';

			return $ajax_actions;
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__Wpml::getInstance();
endif;
