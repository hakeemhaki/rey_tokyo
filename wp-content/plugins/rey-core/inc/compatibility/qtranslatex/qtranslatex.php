<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'QTX_Translator' ) && !class_exists('ReyCore_Compatibility__QtranslateX') ):
	/**
	 * QTranslateX Plugin Compatibility
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Compatibility__QtranslateX
	{
		private static $_instance = null;

		private function __construct()
		{
			add_action('rey/header/row', [$this, 'header'], 60);
			add_action('rey/mobile_nav/footer', [$this, 'mobile'], 10);
		}

		/**
		 * Get QtranslateX data
		 *
		 * @since 1.0.0
		 **/
		function data(){

			if( function_exists('qtranxf_getLanguage') && function_exists('qtranxf_getSortedLanguages') && function_exists('qtranxf_flag_location') && function_exists('qtranxf_convertURL') ):
				$languages = [];

				global $q_config;
				if( $q_config ):
					$flag_location = qtranxf_flag_location();
					foreach( qtranxf_getSortedLanguages() as $language ) {
						$url = '';
						if(is_404()) {
							$url = home_url();
						}
						$languages[$language] = [
							'code' => $language,
							'flag' => $flag_location . $q_config['flag'][$language],
							'name' => $q_config['language_name'][$language],
							'active' => $language == $q_config['language'],
							'url' => qtranxf_convertURL($url, $language, false, true)
						];
					}
				endif;

				return [
					'current' => qtranxf_getLanguage(),
					'languages' => $languages,
					'type' => 'qtranslatex'
				];

			endif;

			return false;
		}

		/**
		 * Add language switcher for QtranslateX into Header
		 *
		 * @since 1.0.0
		 **/
		function header($options = []){
			if($data = $this->data()) {
				echo reycore__language_switcher_markup($data, $options);
			}
		}

		/**
		 * Add language switcher for QtranslateX into Mobile menu panel
		 *
		 * @since 1.0.0
		 **/
		function mobile(){
			if($data = $this->data()) {
				echo reycore__language_switcher_markup_mobile($data);
			}
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__QtranslateX::getInstance();
endif;
