<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('GTranslate') && ! class_exists('ReyCore_Compatibility__GTranslate') ):
	/**
	 * GTranslate Plugin Compatibility
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Compatibility__GTranslate
	{
		private static $_instance = null;

		private $added_widget = false;

		private function __construct()
		{
			add_action('rey/header/row', [$this, 'header'], 60);
			add_action('rey/mobile_nav/footer', [$this, 'mobile'], 10);
			add_action('reycore/language_switcher_markup', [$this, 'switcher_markup'], 10, 2);
		}

		/**
		 * Get Gtranslate data
		 *
		 * @since 1.0.0
		 **/
		function data(){

			if( ! apply_filters('reycore/compatibility/gtranslate', true) ){
				return;
			}

			$data = get_option('GTranslate');

			$translations = [];

			$widget_look = 'dropdown_with_flags';

			if( isset($data['widget_look']) && ! empty($data['widget_look']) ){
				$widget_look = $data['widget_look'];
			}

			$types = [
				'flags' => [
					"dropdown_with_flags",
					"flags_dropdown",
					"flags",
					"flags_name",
					"flags_code",
					"popup",
				],
				'codes' => [
					"dropdown",
					"lang_names",
					"lang_codes",
					"globe",
				],
			];

			if( in_array($widget_look, $types['flags'], true) && isset($data['fincl_langs']) && ! empty($data['fincl_langs']) ){
				$translations = $data['fincl_langs'];
			}
			elseif( isset($data['incl_langs']) && ! empty($data['incl_langs']) ){
				$translations = $data['incl_langs'];
			}

			if( !empty($translations) ){

				$languages = [];
				$flag = false;

        		$_plugin_url = preg_replace('/^https?:/i', '', plugins_url() . '/gtranslate');
				$_flag_path = sprintf( '%s/flags/%s/', $_plugin_url, 16 );

				$_gt_lang_array_json = '{"af":"Afrikaans","sq":"Albanian","am":"Amharic","ar":"Arabic","hy":"Armenian","az":"Azerbaijani","eu":"Basque","be":"Belarusian","bn":"Bengali","bs":"Bosnian","bg":"Bulgarian","ca":"Catalan","ceb":"Cebuano","ny":"Chichewa","zh-CN":"Chinese (Simplified)","zh-TW":"Chinese (Traditional)","co":"Corsican","hr":"Croatian","cs":"Czech","da":"Danish","nl":"Dutch","en":"English","eo":"Esperanto","et":"Estonian","tl":"Filipino","fi":"Finnish","fr":"French","fy":"Frisian","gl":"Galician","ka":"Georgian","de":"German","el":"Greek","gu":"Gujarati","ht":"Haitian Creole","ha":"Hausa","haw":"Hawaiian","iw":"Hebrew","hi":"Hindi","hmn":"Hmong","hu":"Hungarian","is":"Icelandic","ig":"Igbo","id":"Indonesian","ga":"Irish","it":"Italian","ja":"Japanese","jw":"Javanese","kn":"Kannada","kk":"Kazakh","km":"Khmer","ko":"Korean","ku":"Kurdish (Kurmanji)","ky":"Kyrgyz","lo":"Lao","la":"Latin","lv":"Latvian","lt":"Lithuanian","lb":"Luxembourgish","mk":"Macedonian","mg":"Malagasy","ms":"Malay","ml":"Malayalam","mt":"Maltese","mi":"Maori","mr":"Marathi","mn":"Mongolian","my":"Myanmar (Burmese)","ne":"Nepali","no":"Norwegian","ps":"Pashto","fa":"Persian","pl":"Polish","pt":"Portuguese","pa":"Punjabi","ro":"Romanian","ru":"Russian","sm":"Samoan","gd":"Scottish Gaelic","sr":"Serbian","st":"Sesotho","sn":"Shona","sd":"Sindhi","si":"Sinhala","sk":"Slovak","sl":"Slovenian","so":"Somali","es":"Spanish","su":"Sudanese","sw":"Swahili","sv":"Swedish","tg":"Tajik","ta":"Tamil","te":"Telugu","th":"Thai","tr":"Turkish","uk":"Ukrainian","ur":"Urdu","uz":"Uzbek","vi":"Vietnamese","cy":"Welsh","xh":"Xhosa","yi":"Yiddish","yo":"Yoruba","zu":"Zulu"}';
       			$gt_lang_array = get_object_vars(json_decode($_gt_lang_array_json));

				$current_lang = 'en';

				if( $current_lang = $data['default_language'] ){
					$flag = $_flag_path . $current_lang . '.png';
				}
				if( isset($_COOKIE['googtrans']) && ($cookie = $_COOKIE['googtrans']) ){
					$cookie_parts = explode('/', $cookie);
					if( ! empty($cookie_parts) ){
						$current_lang = end($cookie_parts);
					}
				}

				foreach ($translations as $language) {

					$languages[$language] = [
						'code' => $language,
						'flag' => in_array($widget_look, $types['flags'], true) ? $_flag_path . $language . '.png' : '',
						'name' => in_array($widget_look, ['lang_codes', 'flags_code'], true) ? $language : $gt_lang_array[$language],
						'active' => $current_lang === $language,
						'url' => '#',
						'attr' => 'onclick="' . esc_attr( sprintf("doGTranslate('%s|%s');return false;", $current_lang, $language) ) . '" data-lang="'. $language .'"',
					];

				}

				add_action('wp_footer', [$this, 'add_widget']);

				return [
					'current' => $current_lang,
					'current_flag' => $flag,
					'languages' => $languages,
					'type' => 'gtranslate'
				];
			}

			return false;
		}

		function add_widget(){
			if( ! $this->added_widget ){

				printf( '<div class="rey-gtranslate-widget" style="display:none;">%s</div>', do_shortcode('[gtranslate]')); ?>

				<script>
					(function($){
						$(document).ready(function(){

							var wrapper = $('.rey-langSwitcher--gtranslate'),
								btn = $('.rey-header-dropPanel-btn', wrapper),
								btnCode = $('span', btn),
								links = $('.rey-header-dropPanel-content li a', wrapper);

							links.on('click', function(e){
								e.preventDefault();
								btnCode.html( $(this).attr('data-lang') );
							});

						});
					})(jQuery);
				</script>

				<?php

				$this->added_widget = true;
			}
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

		function switcher_markup($html, $args){



			return $html;
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__GTranslate::getInstance();
endif;
