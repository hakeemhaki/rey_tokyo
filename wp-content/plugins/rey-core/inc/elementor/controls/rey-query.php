<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore__Control_Query') ):
class ReyCore__Control_Query extends \Elementor\Control_Select2 {

	public function get_type() {
		return 'rey-query';
	}

}
endif;
