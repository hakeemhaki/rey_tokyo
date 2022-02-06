<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( function_exists( 'trp_enable_translatepress' ) && !class_exists('ReyCore_Compatibility__TranslatePress') ):
	class ReyCore_Compatibility__TranslatePress
	{
		public function __construct()
		{
			add_filter('trp_force_search', [$this, 'force_search'], 10);
			add_action('reycore/woocommerce/search/before_get_data', [$this, 'ajax_search_before_get_data']);
		}

		function ajax_search_before_get_data(){

			// force translated title
			add_filter( 'the_title', function($title){
				$trp = TRP_Translate_Press::get_trp_instance();
				$translation_render = $trp->get_component( 'translation_render' );
				return $translation_render->translate_page($title);
			}, 20);

		}

		function force_search( $status ){

			if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'reycore_ajax_search' ){
				return true;
			}

			return $status;
		}

	}

	new ReyCore_Compatibility__TranslatePress;
endif;
