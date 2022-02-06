<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('Iconic_Woo_Attribute_Swatches') && !class_exists('ReyCore_Compatibility__Iconic_Woo_Attribute_Swatches') ):

	class ReyCore_Compatibility__Iconic_Woo_Attribute_Swatches
	{
		public $iconic_was;

		const META_KEY = 'iconic_was_term_meta';

		public $swatches = [
			'color' => 'colour-swatch',
			'image' => 'image-swatch',
		];

		public function __construct()
		{
			reycore__remove_filters_for_anonymous_class('woocommerce_layered_nav_term_html', 'Iconic_WAS_Attributes', 'modify_layered_nav_term_html', 10);
			add_filter('reycore/woocommerce/variations/swatches', [$this, 'check_for_swatch'], 10, 4 );
			add_filter('reycore/woocommerce/variations/colors', [$this, 'get_color'], 10, 2 );
			add_filter('reycore/woocommerce/variations/images', [$this, 'get_image'], 10, 2 );
		}

		function check_for_swatch( $check, $attribute_type, $tax, $taxonomy_name ){

			global $iconic_was;

			$swatch_type = $iconic_was->swatches_class()->get_swatch_option( 'swatch_type', false, $tax->attribute_id );

			if( isset( $this->swatches[$attribute_type] ) ){
				return $this->swatches[$attribute_type] === $swatch_type;
			}

			return $check;
		}

		function get_color( $color, $meta ){

			if(
				isset($meta[self::META_KEY][0]) &&
				( $key = unserialize($meta[self::META_KEY][0]) ) &&
				isset( $key[$this->swatches['color']] ) &&
				( $swatch_color = $key[$this->swatches['color']] )
			){
				return $swatch_color;
			}

			return $color;
		}

		function get_image( $image, $meta ){

			if(
				isset($meta[self::META_KEY][0]) &&
				( $key = unserialize($meta[self::META_KEY][0]) ) &&
				isset( $key[$this->swatches['image']] ) &&
				( $swatch_image = $key[$this->swatches['image']] )
			){
				return $swatch_image;
			}

			return $image;
		}
	}

	new ReyCore_Compatibility__Iconic_Woo_Attribute_Swatches;
endif;
