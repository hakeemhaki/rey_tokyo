<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Single__Default') ):

class ReyCore_WooCommerce_Single__Default extends ReyCore_WooCommerce_Single
{
	private static $_instance = null;

	const TYPE = 'default';

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
		add_action( 'wp', [ $this, 'wp' ]);
		add_action( 'rey/get_sidebar', [ $this, 'get_product_page_sidebar'] );
		add_filter( 'rey/sidebar_name', [ $this, 'product_page_sidebar'] );
		add_filter( 'rey/content/sidebar_class', [ $this, 'sidebar_classes'], 10 );
		add_filter( 'rey/content/site_main_class', [ $this, 'main_classes'], 10 );
		add_filter( 'theme_mod_single_skin_cascade_bullets', [ $this, 'disable__cascade_bullets'], 90 );
		add_filter( 'reycore/woocommerce/short_desc/can_reposition', '__return_true' );
	}

	function wp (){

		if( ! is_product() ){
			return;
		}

		$priority = 1;

		// make sure to include breadcrumbs and nav into fixed summary block.
		if( ReyCore_WooCommerce_FixedSummary::getInstance()::$is_enabled ){
			$priority = 3;
		}

		if( $this->breadcrumb_enabled() ){
			add_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', $priority );
		}

		add_action( 'woocommerce_single_product_summary', [ ReyCore_WooCommerce_ProductNavigation::getInstance(), 'get_navigation' ], $priority); // right after summary begins
	}

	/**
	 * Filter product page's css classes
	 */
	function product_page_classes($classes, $product)
	{
		if( (is_array($classes) && in_array('rey-product', $classes)) && get_theme_mod('single_skin_default_flip', false) == true ){
			$classes[] = '--reversed';
		}
		return $classes;
	}


	/**
	 * Show Product page sidebar
	 *
	 * @since 1.6.15
	 */
	public function product_page_sidebar($sidebar)
	{
		if( is_product() ){
			return 'product-page-sidebar';
		}
		return $sidebar;
	}

	/**
	 * Check if sidebar is active
	 * @since 1.6.15
	 */
	function is_pp_sidebar_active(){
		return is_product() &&
		is_active_sidebar('product-page-sidebar') &&
		get_theme_mod('single_skin__default__sidebar', '') !== '';
	}

	/**
	 * Get Shop Sidebar
	 * @hooks to rey/get_sidebar
	 * @since 1.6.15
	 */
	public function get_product_page_sidebar( $position )
	{
		if(
			$this->is_pp_sidebar_active() &&
			get_theme_mod('single_skin__default__sidebar', '') === $position
		) {
			get_sidebar('product-page-sidebar');
		}
	}

	/**
	 * Filter main wrapper's css classes
	 *
	 * @since 1.6.15
	 **/
	public function main_classes($classes)
	{
		if( $this->is_pp_sidebar_active() ) {
			$classes[] = '--has-sidebar';

			if( get_theme_mod('single_skin__default__sidebar_mobile', true) ) {
				$classes[] = '--sidebar-hidden-mobile';
			}
		}

		return $classes;
	}

	/**
	 * Filter sidebar wrapper's css classes
	 *
	 * @since 1.6.15
	 **/
	public function sidebar_classes($classes)
	{
		if( $this->is_pp_sidebar_active() && get_theme_mod('single_skin__default__sidebar_mobile', true) ) {
			$classes[] = '--sidebar-hidden-mobile';
		}

		return $classes;
	}

	function disable__cascade_bullets($status){
		if( $this->is_pp_sidebar_active() ) {
			$status = false;
		}
		return $status;
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

ReyCore_WooCommerce_Single__Default::getInstance();
