<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WCFM_Elementor') && !class_exists('ReyCore_Compatibility__WCFMEM') ):
	class ReyCore_Compatibility__WCFMEM
	{
		public function __construct()
		{
			add_filter( 'reycore/ajaxfilters/js_params', [$this, 'filter_params'], 20);
		}

		function filter_params($params){

			if( isset($params['shop_loop_container']) ){
				$params['shop_loop_container'] .= ', div[data-elementor-type="wcfmem-store"] .reyajfilter-before-products';
			}

			if( isset($params['not_found_container']) ){
				$params['not_found_container'] .= ', div[data-elementor-type="wcfmem-store"] .reyajfilter-before-products';
			}

			return $params;
		}
	}

	new ReyCore_Compatibility__WCFMEM;
endif;
