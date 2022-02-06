<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( function_exists('relevanssi_do_query') && !class_exists('ReyCore_Compatibility__Relevanssi') ):

	class ReyCore_Compatibility__Relevanssi
	{
		public function __construct()
		{
			add_action('reycore/woocommerce/search/search_products_query', [$this, 'search_query']);
			add_filter('reycore/woocommerce/components_add_remove/priority', [$this, 'change_loop_components_priority']);
		}

		public function search_query( $the_query ) {
			relevanssi_do_query( $the_query );
		}

		function change_loop_components_priority(){
			return 9;
		}
	}

	new ReyCore_Compatibility__Relevanssi;
endif;
