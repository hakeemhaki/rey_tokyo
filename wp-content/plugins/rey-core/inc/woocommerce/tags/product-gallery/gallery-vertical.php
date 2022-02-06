<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if( !class_exists('ReyCore_WooCommerce_ProductGallery_Vertical') ):
/**
 * This will initialize Rey's product page galleries
 */
class ReyCore_WooCommerce_ProductGallery_Vertical extends ReyCore_WooCommerce_ProductGallery_Base
{
	private static $_instance = null;

	const TYPE = 'vertical';

	private function __construct()
	{
		add_action('reycore/woocommerce/product_image/before_gallery', [$this, 'init']);
	}

	function init(){

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'woocommerce_product_thumbnails', [$this, 'start_wrap'], 0);
		add_action( 'woocommerce_product_thumbnails', [$this, 'add_main_thumb'], 5);
		add_action( 'woocommerce_product_thumbnails', [$this, 'end_wrap'], 1000);
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'add_image_datasets'], 10, 2); // add datasets to image thumbs
	}

	function start_wrap(){

		$this->should_wrap = apply_filters('reycore/woocommerce/thumbs_gallery/should_wrap', (($product = wc_get_product()) && ($gallery_image_ids = $product->get_gallery_image_ids()) && count($gallery_image_ids) > 0), self::TYPE );

		if ( ! $this->should_wrap ){
			return;
		}

		$this->thumbs_markup__start();
	}

	function end_wrap(){
		if ( $this->should_wrap ){
			$this->thumbs_markup__end();
		}
	}

	function is_enabled(){
		return $this->get_active_gallery_type() === self::TYPE;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_ProductGallery_Vertical
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}
endif;

ReyCore_WooCommerce_ProductGallery_Vertical::getInstance();
