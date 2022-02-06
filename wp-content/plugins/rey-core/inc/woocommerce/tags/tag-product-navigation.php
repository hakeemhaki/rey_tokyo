<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_ProductNavigation') ):

class ReyCore_WooCommerce_ProductNavigation
{
	private static $_instance = null;

	private $settings = [];

	private function __construct() {
		add_shortcode('rey_product_navigation', [$this, 'get_navigation']);
	}

	function get_type(){
		return get_theme_mod('product_navigation', '1');
	}

	public function get_settings(){
		$this->settings = apply_filters('reycore/woocommerce/product_nav_settings', [
			'title_limit' => 5,
			'in_same_term' => true,
		]);
	}

	public function get_nav_item( $same_term, $previous = true ){

		$post = get_adjacent_post( $same_term, '', $previous, 'product_cat' );

		if( !$post ){
			return;
		}

		$product = wc_get_product( $post->ID );
		$product_id = $product->get_id();

		return sprintf('
			<span class="rey-productNav__meta" data-id="%1$s" aria-hidden="true" title="%2$s">%3$s</span>
			%7$s
			<div class="rey-productNav__metaWrapper --%4$s">
				<div class="rey-productNav__thumb">%5$s</div>
				%6$s
			</div>',
			esc_attr( $product_id ),
			esc_attr( $product->get_title() ),
			reycore__arrowSvg(!$previous),
			esc_attr($this->get_type()),
			$this->get_thumbnail($product),
			$this->get_extended($product),
			$this->get_screen_text($previous)
		);
	}

	public function get_screen_text($previous = true){

		$text = __( 'Previous product:', 'rey-core' );

		if( !$previous ){
			$text = __( 'Next product:', 'rey-core' );
		}

		return sprintf('<span class="screen-reader-text">%s</span>', $text );
	}

	public function get_thumbnail($product){

		$thumbnail_id = $product->get_image_id();
		$thumbnail_size = 'woocommerce_gallery_thumbnail';

		if( $this->get_type() === 'full' ){
			$thumbnail_size = 'woocommerce_single';
		}

		return !empty($thumbnail_id) ? wp_get_attachment_image( absint($thumbnail_id), $thumbnail_size ) : '';
	}

	public function get_extended($product){

		if( $this->get_type() !== 'extended' && $this->get_type() !== 'full' ){
			return;
		}

		$title = $this->get_title($product, $this->get_type() === 'extended');
		$price = wp_kses_post( $product->get_price_html() );

		return sprintf('<div class="rey-productNav__metaExtend"><div class="rey-productNav__title">%1$s</div><div class="rey-productNav__price">%2$s</div></div>', $title, $price);
	}

	public function get_title($product, $limited = false){

		if( $limited ){
			return reycore__limit_text( $product->get_title(), $this->settings['title_limit'] );
		}

		return $product->get_title();
	}

	public function get_navigation()
	{
		if( $this->get_type() === '2' ) {
			return;
		}

		$this->get_settings();

		$same_term = get_theme_mod('product_navigation_same_term', true);

		the_post_navigation(
			[
				'screen_reader_text' => esc_html__( 'Product navigation', 'rey-core' ),
				'prev_text' => $this->get_nav_item( $same_term, true ),
				'next_text' => $this->get_nav_item( $same_term, false ),
				'in_same_term' => $same_term,
				'taxonomy' => 'product_cat'
			]
		);
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_ProductNavigation
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}

ReyCore_WooCommerce_ProductNavigation::getInstance();

endif;
