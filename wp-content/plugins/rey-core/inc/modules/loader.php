<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! defined('REY_CORE_MODULE_DIR') )
	define( 'REY_CORE_MODULE_DIR', plugin_dir_path( __FILE__ ) );

if( ! defined('REY_CORE_MODULE_URI') )
	define( 'REY_CORE_MODULE_URI', plugin_dir_url( __FILE__ ) );


$dirs = array_filter( glob( REY_CORE_MODULE_DIR . '/*'), 'is_dir' );

foreach($dirs as $dir){

	$name = basename($dir, '.php');

	if( in_array( $name, defined('REY_CORE_MODULES_EXCLUDE') ? REY_CORE_MODULES_EXCLUDE : [] ) ){
		continue;
	}

	if( ( $file = trailingslashit($dir) . $name . '.php' ) && is_readable($file) ){
		require_once $file;
	}

}
