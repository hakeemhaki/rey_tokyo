<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_AtcButtonsIcon') ):

class ReyCore_Wc_AtcButtonsIcon
{
	private $settings = [];

	public function __construct()
	{
		add_action( 'wp', [$this, 'init'] );
	}

	public function init()
	{
		add_filter('reycore/woocommerce/loop/add_to_cart/content', [$this, 'add_icon__catalog'], 10, 2);
		add_filter('reycore/woocommerce/single_product/add_to_cart_button/variation', [$this,'add_icon__product_page'], 20, 3);
		add_filter('reycore/woocommerce/single_product/add_to_cart_button/simple', [$this, 'add_icon__product_page'], 20, 3);
	}

	function add_icon__catalog($html, $product) {
		$icon = ($cart_icon = get_theme_mod('loop_atc__icon', '')) ? reycore__get_svg_icon__core([ 'id'=> 'reycore-icon-' . $cart_icon ]) : '';

		if( ! $icon ){
			return $html;
		}

		return $icon . $html;
	}

	function add_icon__product_page($html, $product, $text) {

		$icon = ($cart_icon = get_theme_mod('single_atc__icon', '')) ? reycore__get_svg_icon__core([ 'id'=> 'reycore-icon-' . $cart_icon ]) : '';

		if( ! $icon ){
			return $html;
		}

		$search = '<span class="single_add_to_cart_button-text">';
		return str_replace($search, $icon . $search, $html);
	}

}

new ReyCore_Wc_AtcButtonsIcon;

endif;
