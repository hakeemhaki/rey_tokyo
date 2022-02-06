<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Single__FullScreen') ):

class ReyCore_WooCommerce_Single__FullScreen extends ReyCore_WooCommerce_Single
{
	const TYPE = 'fullscreen';

	public  function __construct()
	{
		add_action('init', [$this, 'init']);
	}

	function init(){

		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init', [$this, 'load_hooks'] );
			return;
		}

		$this->load_hooks();
	}

	public function load_hooks()
	{
		if( $this->get_single_active_skin() !== self::TYPE ){
			return;
		}

		remove_action( 'woocommerce_before_single_product', 'woocommerce_output_all_notices', 10 ); // move notices
		add_filter( 'woocommerce_post_class', [$this, 'product_page_classes'], 20, 2 );
		add_filter( 'rey/header/header_classes', [$this, 'header_classes'], 20 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_output_all_notices', 2 );
		add_filter( 'reycore/header_helper/overlap_classes', [$this, 'header_overlapping_helper'], 100);
		add_filter( 'reycore/woocommerce/short_desc/can_reposition', '__return_true' );
		add_action( 'wp', [ $this, 'wp' ]);
	}

	function wp (){

		if( ! is_product() ){
			return;
		}

		$priority = 3;

		if( $this->breadcrumb_enabled() ){
			add_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', $priority );
		}

		add_action( 'woocommerce_single_product_summary', [ ReyCore_WooCommerce_ProductNavigation::getInstance(), 'get_navigation' ], $priority); // right after summary begins
	}

	function header_overlapping_helper ( $classes ){

		if( ! is_product() ){
			return $classes;
		}

		$classes['desktop'] = '--dnone-desktop';
		$classes['tablet'] = '';
		$classes['mobile'] = '';

		return $classes;
	}

	/**
	 * Filter product page's css classes
	 */
	function product_page_classes($classes, $product)
	{
		// make sure it's a product page
		if( in_array('rey-product', $classes) )
		{
			if( get_theme_mod('single_skin_default_flip', false) ){
				$classes['reversed'] = '--reversed';
			}
			if(
				get_theme_mod('single_skin_fullscreen_stretch_gallery', false) &&
				get_theme_mod('product_gallery_layout', 'vertical') === 'cascade' ){
				$classes['stretch-gallery'] = '--fullscreen-stretch-gallery';
			}
			if(
				get_theme_mod('single_skin_fullscreen_custom_height', false) &&
				( get_theme_mod('product_gallery_layout', 'vertical') === 'vertical' ||
				get_theme_mod('product_gallery_layout', 'vertical') === 'horizontal' )
			){
				$classes['fs_custom_height'] = '--fs-custom-height';
			}
		}
		return $classes;
	}

	function header_classes($classes){

		if( 'header-pos--rel' === $classes['position'] && get_theme_mod('single_skin_fullscreen__header_rel_abs', true) ){
			$classes['header_rel_abs'] = '--fullscreen-header-rel-abs';
		}

		return $classes;
	}


}
new ReyCore_WooCommerce_Single__FullScreen;
endif;
