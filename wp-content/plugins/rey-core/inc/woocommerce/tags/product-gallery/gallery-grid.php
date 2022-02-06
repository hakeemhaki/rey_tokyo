<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if( !class_exists('ReyCore_WooCommerce_ProductGallery_Grid') ):
/**
 * This will initialize Rey's product page galleries
 */
class ReyCore_WooCommerce_ProductGallery_Grid extends ReyCore_WooCommerce_ProductGallery_Base
{
	private static $_instance = null;

	const TYPE = 'grid';

	private function __construct()
	{
		add_action('reycore/woocommerce/product_image/before_gallery', [$this, 'init']);
	}

	function init(){

		if( ! $this->is_enabled() ){
			return;
		}

		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'thumbs_to_single_size'], 10, 2);
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'add_animation_classes'], 10, 2);
		add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false', 20);

	}

	function is_enabled(){
		return $this->get_active_gallery_type() === self::TYPE;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_ProductGallery_Grid
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

ReyCore_WooCommerce_ProductGallery_Grid::getInstance();
