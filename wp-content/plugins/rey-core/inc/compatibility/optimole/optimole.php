<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( function_exists( 'optml' ) && !class_exists('ReyCore_Compatibility__Optimole') ):
	class ReyCore_Compatibility__Optimole
	{
		public function __construct()
		{
			add_filter('optml_dont_replace_url', [$this, 'dont_replace_url'], 20, 2);
		}

		public function dont_replace_url($status, $url) {

			if( strpos($url, 'icon-sprite.svg') !== false || strpos($url, 'social-icons-sprite.svg') !== false ){
				return true;
			}

			return $status;
		}

	}

	new ReyCore_Compatibility__Optimole;
endif;
