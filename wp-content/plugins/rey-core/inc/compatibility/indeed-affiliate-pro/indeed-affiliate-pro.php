<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'UAP_Main' ) && !class_exists('ReyCore_Compatibility__IndeedAffiliatePro') ):
	class ReyCore_Compatibility__IndeedAffiliatePro
	{
		public function __construct()
		{
			add_filter('rey/main_script_params', [$this, 'main_script_params'], 20);
		}

		public function main_script_params($params){
			$params['js_params']['select2_overrides'] = false;
			return $params;
		}

	}

	new ReyCore_Compatibility__IndeedAffiliatePro;
endif;
