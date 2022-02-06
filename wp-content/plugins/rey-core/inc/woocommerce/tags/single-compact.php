<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Single__Compact') ):

class ReyCore_WooCommerce_Single__Compact extends ReyCore_WooCommerce_Single
{
	private static $_instance = null;

	const TYPE = 'compact';

	private function __construct()
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

		add_filter( 'woocommerce_post_class', [$this, 'product_page_classes'], 20, 2 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		add_filter('reycore/woocommerce/product_page/share/classes', [$this, 'make_sharing_vertical_sticky']);

		// move images after summary
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
		add_action( 'woocommerce_after_single_product_summary', 'woocommerce_show_product_images', 0 );

		add_action( 'woocommerce_single_product_summary', [ $this, 'start_left_wrap' ], 2);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 25);
		add_action( 'woocommerce_single_product_summary', [ $this, 'start_right_wrap' ], 25);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 100);

		add_action( 'woocommerce_single_product_summary', [ ReyCore_WooCommerce_ProductNavigation::getInstance(), 'get_navigation' ], 1);

		add_action( 'wp', [ $this, 'wp' ]);

		$this->move_product_meta();
	}

	function wp (){

		if( ! is_product() ){
			return;
		}

		if( $this->breadcrumb_enabled() ){
			add_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', 1 );
		}
	}

	function move_product_meta(){

		if( ! $this->product_meta_is_enabled() ){
			return;
		}

		// move meta to the bottom
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 0 );
	}

	/**
	 * Make the sharing buttons vertical
	 *
	 * @since 1.0.0
	 */
	function make_sharing_vertical_sticky($classes){
		$classes[] = '--vertical';
		$classes[] = '--sticky';
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

		}
		return $classes;
	}

	/**
	 * Wrap left summary - start
	 *
	 * @since 1.0.0
	 **/
	function start_left_wrap()
	{ ?>
		<div class="rey-leftSummary"><?php
	}

	/**
	 * Wrap left summary - start
	 *
	 * @since 1.0.0
	 **/
	function start_right_wrap()
	{ ?>
		<div class="rey-rightSummary"><?php
	}


	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Single
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

ReyCore_WooCommerce_Single__Compact::getInstance();
