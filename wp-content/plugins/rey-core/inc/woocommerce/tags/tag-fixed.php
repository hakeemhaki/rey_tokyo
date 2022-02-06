<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_FixedSummary') ):

class ReyCore_WooCommerce_FixedSummary
{
	private static $_instance = null;

	public static $is_enabled = false;

	private function __construct() {
		add_action('init', [$this, 'init']);
		add_action('wp', [$this, 'late_init']);
	}

	function init(){
		self::$is_enabled = get_theme_mod('product_page_summary_fixed', false);
	}

	function late_init(){

		if( ! self::$is_enabled ){
			return;
		}

		if( ! is_product() ){
			return;
		}

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_filter( 'body_class', [ $this, 'body_classes'], 20 );
		add_action( 'reycore/woocommerce/before_single_product_summary', [ $this, 'load_scripts']);
	}

	function load_scripts(){
		reyCoreAssets()->add_scripts(['reycore-sticky', 'reycore-wc-product-page-fixed-summary']);
	}

	function css_first(){
		// return false;
		return apply_filters('reycore/woocommerce/product_page/fixed/css_first', true);
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{
		if( self::$is_enabled && in_array('single-product', $classes) ) {

			$classes['fixed_summary'] = '--fixed-summary';

			if( $this->css_first() ){
				$classes['fixed_css_first'] = '--fixed-summary-cssfirst';
			}

			if( get_theme_mod('product_page_summary_fixed__gallery', false) ){
				$classes['fixed_summary_gallery'] = '--fixed-gallery';
			}

			if( get_theme_mod('product_page_summary_fixed__offset_active', '') !== ''){
				$classes['fixed_summary_animate'] = '--fixed-summary-anim';
			}

		}
		return $classes;
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['fixed_summary'] = [
			'css_first' => $this->css_first(),
			'enabled' => self::$is_enabled,
			'offset' => get_theme_mod('product_page_summary_fixed__offset', ''),
			'offset_bottom' => 0,
			'use_container_height' => true,
			'gallery' => get_theme_mod('product_page_summary_fixed__gallery', false),
			'refresh_fixed_header' => false,
		];

		return $params;
	}


	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_FixedSummary
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}

ReyCore_WooCommerce_FixedSummary::getInstance();

endif;
