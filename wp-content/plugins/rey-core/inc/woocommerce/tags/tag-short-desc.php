<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_ShortDesc') ):

class ReyCore_WooCommerce_ShortDesc
{
	private static $_instance = null;

	public static $is_enabled = false;

	private function __construct() {
		add_action('init', [$this, 'init']);
		add_action('wp', [$this, 'late_init']);
	}

	function init(){
		self::$is_enabled = get_theme_mod('product_short_desc_enabled', true);
	}

	function late_init(){

		if( ! is_product() ){
			return;
		}

		if( ! self::$is_enabled ){
			add_filter( 'woocommerce_short_description', '__return_empty_string');
			return;
		}

		add_filter( 'woocommerce_short_description', [$this, 'add_excerpt_toggle']);

		$this->reposition();

	}

	function reposition(){

		if( apply_filters( 'reycore/woocommerce/short_desc/can_reposition', false) && get_theme_mod('product_short_desc_after_atc', false) ){
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 35 );
		}
	}

	/**
	 * Creates a read more / less toggle for short description
	 *
	 * @since 1.0.0
	 */
	function add_excerpt_toggle( $excerpt ){

		$short_desc_toggle = get_theme_mod('product_short_desc_toggle_v2', false);

		if( ! $short_desc_toggle || (!is_single() && !get_query_var('rey__is_quickview', false)) ){
			return $excerpt;
		}

		$stript_tags = get_theme_mod('product_short_desc_toggle_strip_tags', true);

		if( $stript_tags ){

			$intro = wp_strip_all_tags($excerpt);
			$limit = 50;

			if ( strlen($intro) > $limit) {

				$full_content = $excerpt;
				// truncate string
				$excerptCut = substr($intro, 0, $limit);
				$endPoint = strrpos($excerptCut, ' ');

				$excerpt = '<div class="u-toggle-text --collapsed">';
					$excerpt .= '<div class="u-toggle-content">';
					$excerpt .= $intro;
					$excerpt .= '</div>';
					$excerpt .= '<button class="btn u-toggle-btn" data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt.', 'rey-core') .'"></button>';
				$excerpt .= '</div>';

				return $excerpt;
			}
		}
		// keep tags
		else{
			$full_content = $excerpt;
			if( $full_content ):
				$excerpt = '<div class="u-toggle-text-next-btn --short">';
				$excerpt .= $full_content;
				$excerpt .= '</div>';
				$excerpt .= '<button class="btn btn-line-active"><span data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt.', 'rey-core') .'"></span></button>';
			endif;
		}

		remove_filter( 'woocommerce_short_description', [$this, 'add_excerpt_toggle']);


		return $excerpt;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_ShortDesc
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}

ReyCore_WooCommerce_ShortDesc::getInstance();

endif;
