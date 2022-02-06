<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_ButtonAddToCart') ):

class ReyCore_WooCommerce_ButtonAddToCart
{
	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	function init(){
		add_action( 'woocommerce_loop_add_to_cart_link', [$this, 'add_to_cart_link'], 5, 3);
		add_filter( 'woocommerce_product_add_to_cart_text', [$this, 'loop_add_to_cart_text'], 10, 2);
		add_filter( 'reycore/woocommerce/loop/add_to_cart/before', [$this, 'quantity_start'], 10, 3);
		add_filter( 'reycore/woocommerce/loop/add_to_cart/after', [$this, 'quantity_end'], 10, 3);
		add_filter( 'woocommerce_quantity_input_min', [$this, 'quantity_min'], 10, 2);
		add_filter( 'woocommerce_quantity_input_max', [$this, 'quantity_max'], 10, 2);
		add_filter( 'woocommerce_quantity_input_step', [$this, 'quantity_step'], 10, 2);
	}

	public static function add_to_cart_classes( $args ){

		$classes[] = esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' );

		if( $btn_style = get_theme_mod('loop_add_to_cart_style', 'under') ){
			$classes[] = 'rey-btn--' . esc_attr($btn_style);
		}

		if( get_theme_mod('loop_add_to_cart_mobile', false) ){
			$classes[] = '--mobile-on';
		}

		return implode(' ', $classes);
	}

	/**
	 * Some plugins filter but don't get attributes back
	 */
	function add_to_cart_link( $html, $product, $args ){

		if( ($text = $product->add_to_cart_text()) ){
			$text = sprintf('<span class="__text">%s</span>', $text);
		}

		$quantity = isset( $args['quantity'] ) ? $args['quantity'] : 1;

		if( $min = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ) ){
			if( $min > $quantity ){
				$quantity = $min;
			}
		}

		return sprintf(
			'%6$s<a href="%1$s" data-quantity="%2$s" class="%3$s" %4$s>%5$s</a>%7$s',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( $quantity ) ,
			self::add_to_cart_classes($args),
			isset( $args['attributes'] ) ? reycore__implode_html_attributes( $args['attributes'] ) : '',
			apply_filters('reycore/woocommerce/loop/add_to_cart/content', $text, $product),
			apply_filters('reycore/woocommerce/loop/add_to_cart/before', '', $product, $args),
			apply_filters('reycore/woocommerce/loop/add_to_cart/after', '', $product, $args)
		);

	}

	function override_qty_style(){
		return 'basic';
	}

	function quantity_start($html, $product, $args){

		if( ! $this->maybe_add_quantity_in_loop($product, $args) ){
			return $html;
		}

		add_filter('theme_mod_single_atc_qty_controls', '__return_true');
		add_filter('theme_mod_single_atc_qty_controls_styles', [$this, 'override_qty_style']);

		$defaults = array_map('intval', [
			'input_value'  	=> 1,
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'step' 		=> apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
		] );

		$quantity = woocommerce_quantity_input( $defaults, $product, false );

		$quantity = str_replace('cartBtnQty-control --minus --disabled', 'cartBtnQty-control --minus', $quantity);
		$quantity = str_replace('cartBtnQty-control --plus --disabled', 'cartBtnQty-control --plus', $quantity);

		if( $defaults['input_value'] === $defaults['min_value'] ){
			$quantity = str_replace('cartBtnQty-control --minus', 'cartBtnQty-control --minus --disabled', $quantity);
		}
		else if( $defaults['max_value'] > $defaults['min_value'] && $defaults['input_value'] === $defaults['max_value'] ) {
			$quantity = str_replace('cartBtnQty-control --plus', 'cartBtnQty-control --plus --disabled', $quantity);
		}

		$classes = [];

		if( get_theme_mod('loop_add_to_cart_mobile', false) ){
			$classes[] = '--mobile-on';
		}

		if( $btn_style = get_theme_mod('loop_add_to_cart_style', 'under') ){
			$classes[] = '--btn-style-' . esc_attr($btn_style);
		}

		return '<div class="rey-loopQty '. implode(' ', $classes) .'">'. $quantity;

	}

	function quantity_end($html, $product, $args){

		if( ! $this->maybe_add_quantity_in_loop($product, $args) ){
			return $html;
		}

		remove_filter('theme_mod_single_atc_qty_controls', '__return_true');
		remove_filter('theme_mod_single_atc_qty_controls_styles', [$this, 'override_qty_style']);

		return '</div>';
	}

	function maybe_add_quantity_in_loop($product, $args){

		if( ! get_theme_mod('loop_supports_qty', false) ){
			return;
		}

		if( ! $product->is_purchasable() ){
			return;
		}

		if( $product->get_type() !== 'simple' ){
			return;
		}

		if( ! $product->is_in_stock() ){
			return;
		}

		if( isset($args['supports_qty']) && ! $args['supports_qty'] ){
			return;
		}

		reyCoreAssets()->add_scripts( 'reycore-wc-product-page-qty-controls' );

		return true;
	}

	function loop_add_to_cart_text( $text, $product ){

		if(
			// $product->get_type() === 'simple' &&
			// $product->is_purchasable() &&
			// $product->is_in_stock() &&
			get_theme_mod('loop_atc__text', '') !== '' ){

				$custom_text = get_theme_mod('loop_atc__text', '');

				if( $custom_text === '0' ){
					return '';
				}

			return $custom_text;
		}

		return $text;
	}

	function quantity_min($val, $product){

		if( !$product ){
			return $val;
		}

		$product_id = $product->get_id();

		if( 'variation' === $product->get_type() ){
			$product_id = $product->get_parent_id();
		}

		if( $qty_data = get_field( 'quantity_options', $product_id ) ){
			if( isset($qty_data['minimum']) && $custom = $qty_data['minimum'] ){
				return $custom;
			}
		}

		return $val;
	}

	function quantity_max($val, $product){

		if( !$product ){
			return $val;
		}

		$product_id = $product->get_id();

		if( 'variation' === $product->get_type() ){
			$product_id = $product->get_parent_id();
		}

		if( $qty_data = get_field( 'quantity_options', $product_id ) ){
			if( isset($qty_data['maximum']) && $custom = $qty_data['maximum'] ){
				return $custom;
			}
		}

		return $val;
	}

	function quantity_step($val, $product){

		if( !$product ){
			return $val;
		}

		$product_id = $product->get_id();

		if( 'variation' === $product->get_type() ){
			$product_id = $product->get_parent_id();
		}

		if( $qty_data = get_field( 'quantity_options', $product_id ) ){
			if( isset($qty_data['step']) && $custom = $qty_data['step'] ){
				return $custom;
			}
		}

		return $val;
	}
}

new ReyCore_WooCommerce_ButtonAddToCart;

endif;
