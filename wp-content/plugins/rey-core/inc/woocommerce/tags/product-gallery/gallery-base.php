<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if( !class_exists('ReyCore_WooCommerce_ProductGallery_Base') ):
/**
 * This will initialize Rey's product page galleries
 */
class ReyCore_WooCommerce_ProductGallery_Base
{
	private static $_instance = null;

	private function __construct()
	{
		$this->load_current_gallery_type();

		add_action('init', [$this, 'init']);
	}

	function init(){
		add_filter( 'woocommerce_single_product_image_gallery_classes', [$this, 'gallery_classes'], 10);
		add_filter( 'woocommerce_gallery_image_html_attachment_image_params', [$this, 'filter_image_attributes'], 20);
		add_filter( 'reycore/woocommerce/product_image/params', [$this, 'gallery_params'], 10 );
		add_action( 'reycore/woocommerce/product_image/before_gallery', [$this, 'mobile_gallery'], 5);
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'add_zoom_custom_target'], 20);
		add_filter( 'woocommerce_single_product_zoom_enabled', [$this, 'enable_gallery_zoom']);
		add_filter( 'woocommerce_single_product_photoswipe_enabled', [$this, 'enable_photoswipe']);
		add_filter( 'woocommerce_single_product_zoom_options', [$this,'add_zoom_custom_target_option']);
		add_filter( 'body_class', [ $this, 'body_classes'], 30 );
		add_filter( 'woocommerce_get_image_size_gallery_thumbnail', [$this, 'disable_thumbs_cropping']);
		add_action( 'reycore/woocommerce/product_image/before_gallery', [$this, 'load_assets']);
	}

	function load_assets()
	{
		if( $this->is_gallery_with_thumbs() ){
			reyCoreAssets()->add_scripts('rey-splide');
			reyCoreAssets()->add_styles('rey-splide');
		}
		else {
			// all galleries except vertical/horizontal
			reyCoreAssets()->add_scripts('scroll-out');
		}
	}

	static function get_gallery_types(){
		return [
			'vertical' => esc_html__('Vertical Layout', 'rey-core'),
			'horizontal' => esc_html__('Horizontal Layout', 'rey-core'),
			'grid' => esc_html__('Grid Layout', 'rey-core'),
			'cascade' => esc_html__('Cascade Layout', 'rey-core'),
			'cascade-grid' => esc_html__('Cascade - Grid Layout', 'rey-core'),
			'cascade-scattered' => esc_html__('Cascade - Scattered Layout', 'rey-core'),
			// TODO
			// 'default' => esc_html__('WooCommerce Default', 'rey-core'),
			// - future: centered slider
			// - future: full-screen with side content
		];
	}

	function load_current_gallery_type()
	{
		foreach (self::get_gallery_types() as $gallery_type => $value) {
			$gallery_skin_path = REY_CORE_DIR . "inc/woocommerce/tags/product-gallery/gallery-{$gallery_type}.php";
			if( is_readable($gallery_skin_path) ){
				require_once $gallery_skin_path;
			}
		}
	}


	function get_active_gallery_type(){
		return get_theme_mod('product_gallery_layout', 'vertical');
	}


	/**
	 * Adds the main image's thumb
	 * Used for Vertical, Horizontal.
	 *
	 * @since 1.0.0
	 */
	function add_main_thumb(){

		if( ! ($product = wc_get_product()) ){
			return;
		}

		// if( $variation_id = reycore_wc__get_default_variation($product) ){
		// 	$variation_product = wc_get_product($variation_id);
		// 	echo wc_get_gallery_image_html( $variation_product->get_image_id() );
		// 	return;
		// }

		if(
			($post_thumbnail_id = $product->get_image_id()) &&
			($gallery_image_ids = $product->get_gallery_image_ids()) &&
			count($gallery_image_ids) > 0 ){
			echo wc_get_gallery_image_html( $post_thumbnail_id );
		}
	}

	function thumbs_markup__start(){
		if( ! $this->is_gallery_with_thumbs() ){
			return;
		} ?>
		<div class="woocommerce-product-gallery__thumbs">
			<div class="splide__track">
				<div class="splide__list">
		<?php
	}

	function thumbs_markup__end(){
		if( ! $this->is_gallery_with_thumbs() ){
			return;
		}
		?></div></div></div><?php
	}

	/**
	 * Add dataset attributes for image gallery's thumbs
	 * They'll be used for transferring their full-src to active image & zoom functionality
	 * Used for Vertical, Horizontal.
	 *
	 * @since 1.0.0
	 */
	function add_image_datasets($html, $post_thumbnail_id)
	{
		// Add Preview Source URL
		$preview_src = wp_get_attachment_image_src($post_thumbnail_id, 'woocommerce_single');
		$preview_srcset = wp_get_attachment_image_srcset($post_thumbnail_id, 'woocommerce_single');
		$preview_sizes = wp_get_attachment_image_sizes($post_thumbnail_id, 'woocommerce_single');

		if( isset($preview_src[0]) && !empty($preview_srcset) ){
			$attributes = 'data-preview-src="' . $preview_src[0] . '"';
			$attributes .= ' data-preview-srcset="' . $preview_srcset . '"';
			$attributes .= ' data-preview-sizes="' . $preview_sizes . '"';
			$attributes .= ' data-src';

			$html = str_replace('data-src', $attributes, $html);
		}

		return $html;
	}

	/**
	 * Replace all thumbs with `woocommerce_single` sized images.
	 * Used for Grid,
	 *
	 * @since 1.0.0
	 */
	function thumbs_to_single_size($html, $post_thumbnail_id)
	{
		if( $post_thumbnail_id ){
			$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
		}

		return $html;
	}

	/**
	 * Filter Gallery wrapper classes
	 *
	 * @since 1.0.0
	 **/
	function gallery_classes($classes)
	{
		$classes['loading'] = '--is-loading';

		$classes['gallery_type'] = 'woocommerce-product-gallery--' . esc_attr( $this->get_active_gallery_type() );

		if( $this->is_gallery_with_thumbs() ){

			$classes['gallery_flip_thumbs'] = '--gallery-thumbs';

			if( get_theme_mod('product_gallery_thumbs_flip', false) ){
				$classes['gallery_flip_thumbs'] = '--flip-thumbs';
			}

			if( ($max_thumbs = get_theme_mod('product_gallery_thumbs_max', '')) && absint($max_thumbs) > 1 ){
				$classes['gallery_max_thumbs'] = '--max-thumbs';
			}

			$classes['gallery_thumbs_style'] = '--thumbs-nav-' . get_theme_mod('product_gallery_thumbs_nav_style', 'boxed');

			if( get_theme_mod('product_gallery_thumbs_disable_cropping', false) ){
				$classes['gallery_thumbs_nocrop'] = '--thumbs-no-crop';
			}

		}

		// if( $product = wc_get_product() ){
		// 	$classes['thumbs-count'] = '--thumbs-count-' . count($product->get_gallery_image_ids());
		// }

		if(
			$this->is_gallery_with_thumbs() &&
			get_theme_mod('custom_main_image_height', false)
		){
				$classes['main-image-container-height'] = '--main-img-height';

		}

		return $classes;
	}

	function is_gallery_with_thumbs(){
		return $this->get_active_gallery_type() === 'vertical' || $this->get_active_gallery_type() === 'horizontal';
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{
		if( in_array('single-product', $classes) )
		{
			if( $this->is_gallery_with_thumbs() && ! get_theme_mod('product_page_summary_fixed__gallery', false) ){
				unset($classes['fixed_summary']);
			}

			$classes['gallery_type'] = '--gallery-' . $this->get_active_gallery_type();
		}

		return $classes;
	}

	function gallery_params($params)
	{
		$params['active_gallery_type'] = $this->get_active_gallery_type();
		$params['gallery__enable_thumbs'] = $this->is_gallery_with_thumbs();
		$params['gallery__enable_animations'] = in_array($this->get_active_gallery_type(), ['grid', 'cascade', 'cascade-scattered', 'cascade-grid'], true);
		$params['product_page_gallery_max_thumbs'] = get_theme_mod('product_gallery_thumbs_max', '');
		$params['product_page_gallery_thumbs_nav_style'] = get_theme_mod('product_gallery_thumbs_nav_style', 'boxed');
		$params['cascade_bullets'] = get_theme_mod('single_skin_cascade_bullets', true);
		$params['product_page_gallery_arrows'] = get_theme_mod('product_page_gallery_arrow_nav', false);
		$params['gallery_should_min_height'] = false;
		$params['gallery_thumb_gallery_slider'] = $this->is_gallery_with_thumbs();
		$params['gallery_thumb_event'] = get_theme_mod('product_gallery_thumbs_event', 'click');
		$params['gallery_wait_defaults_initial_load'] = false;
		$params['product_page_gallery_open'] = get_theme_mod('product_page_gallery__btn__enable', true);
		$params['product_page_gallery_open_icon'] = get_theme_mod('product_page_gallery__btn__icon', 'reycore-icon-plus-stroke');
		$params['product_page_gallery_open_hover'] = get_theme_mod('product_page_gallery__btn__text_enable', false);
		$params['product_page_gallery_open_text'] = get_theme_mod('product_page_gallery__btn__text', esc_html__('OPEN GALLERY', 'rey-core'));
		$params['product_page_mobile_gallery_nav'] = get_theme_mod('product_gallery_mobile_nav_style', 'bars');
		$params['product_page_mobile_gallery_nav_thumbs'] = 4;
		$params['product_page_mobile_gallery_arrows'] = get_theme_mod('product_gallery_mobile_arrows', false);
		return $params;
	}

	/**
	 * Prepare mobile gallery slider
	 *
	 * @since 1.0.0
	 */
	public function mobile_gallery( $image_ids = [], $product_id = 0 )
	{
		if( ! apply_filters('reycore/woocommerce/allow_mobile_gallery', true) ){
			return;
		}

		$gallery_html = '';
		$product = $product_id ? wc_get_product($product_id) : wc_get_product();
		$gallery_images = [];
		$size = apply_filters('reycore/woocommerce/mobile_gallery_size', 'woocommerce_single');

		if( !empty($image_ids) ){
			$gallery_image_ids = $image_ids;
		}
		else {
			$gallery_image_ids = reycore_wc__get_product_images_ids();
		}

		if( empty($gallery_image_ids) ){
			$placeholder_image = get_option( 'woocommerce_placeholder_image', 0 );
			$gallery_image_ids = (array) $placeholder_image;
		}

		// get gallery
		if( $product && !empty($gallery_image_ids) ){

			$product_id = $product->get_id();

			foreach ($gallery_image_ids as $key => $gallery_img_id) {

				$gallery_img = wp_get_attachment_image_src($gallery_img_id, $size);
				if( $gallery_img ){

					if( apply_filters('reycore/woocommerce/product_mobile_gallery/lazy_load', true) ){

						$src = 'data-splide-lazy="'. $gallery_img[0] .'"';

						if( $key === 0 ){
							$src = 'src="'. $gallery_img[0] .'"';
						}

					}
					else {
						$src = 'src="'. $gallery_img[0] .'"';
					}

					$gallery_images[] = apply_filters('reycore/woocommerce/product_mobile_gallery/html',
						sprintf('<div class="splide__slide"><img class="woocommerce-product-gallery__mobile-img woocommerce-product-gallery__mobile--%5$s no-lazy" %1$s data-index="%2$s" data-no-lazy="1" data-skip-lazy="1" data-full=\'%3$s\' title="%4$s"/></div>',
							$src,
							$key + 1,
							wp_json_encode( wp_get_attachment_image_src($gallery_img_id, 'large') ),
							! $this->maybe_remove_image_title() ? get_the_title($gallery_img_id) : '',
							$key
						),
						$gallery_img_id,
						$gallery_image_ids,
						$key
					);
				}
			}
		}

		if( empty($gallery_images) ){
			return;
		}

		$slider_config = [];
		$nav_html = $thumbs_html = '';

		if( count($gallery_images) > 1 ){

			reyCoreAssets()->add_scripts('rey-splide');
			reyCoreAssets()->add_styles('rey-splide');

			// Arrows
			if( get_theme_mod('product_gallery_mobile_arrows', false) ):
				$nav_html .= sprintf(
					'<div class="r__arrows r__arrows-%2$s">%1$s</div>',
					reycore__svg_arrows([
						'attributes' => [
							'left' => 'data-dir="-1"',
							'right' => 'data-dir="+1"',
						],
						'echo' => false
					]),
					$product_id
				);
				$slider_config['arrows'] = '.r__arrows-' . $product_id;
			endif;

			// Pagination
			if( count($gallery_images) > 1 ):
				if( $nav_style = get_theme_mod('product_gallery_mobile_nav_style', 'bars') ){
					// Thumbs
					if( $nav_style === 'thumbs' ) {
						$thumbs_html .= sprintf(
							'<div class="splide woocommerce-product-gallery__mobile-thumbs"><div class="splide__track"><div class="splide__list">%1$s</div></div></div>',
							implode('', $gallery_images)
						);
					}
					// Basic pagination
					else {
						$bullets = '';

						$bullets_count = count($gallery_images);

						if( strpos(implode('', $gallery_images), 'rey-galleryPlayVideo') !== false ){
							$bullets_count++;
						}

						for( $i = 0; $i < $bullets_count; $i++ ){
							$bullets .= sprintf( '<button data-go="%d" class="%s"></button>', $i, ($i === 0 ? 'is-active' : '') );
						}
						$nav_html .= sprintf(
							'<div class="r__pagination r__pagination-%2$s %3$s">%1$s</div>',
							$bullets,
							$product_id,
							'--nav-' . $nav_style
						);
						$slider_config['pagination'] = '.r__pagination-' . $product_id;
					}
				}
			endif;
		}

		// markup
		$gallery_html = sprintf(
			'<div class="splide woocommerce-product-gallery__mobile --loading %5$s" data-slider-config=\'%2$s\'><div class="splide__track"><div class="splide__list">%1$s</div></div>%3$s</div>%4$s',
			implode('', $gallery_images),
			wp_json_encode($slider_config),
			$nav_html,
			$thumbs_html,
			($thumbs_html ? '--has-thumbs' : '')
		);

		if( ! wp_doing_ajax() ){
			$gallery_html = "<div class='woocommerce-product-gallery__mobileWrapper'>{$gallery_html}</div>";
		}

		if( function_exists('rey__kses_post_with_svg') ){
			echo rey__kses_post_with_svg($gallery_html);
		}
		else {
			echo wp_kses_post( $gallery_html );
		}
	}

	public function filter_image_attributes( $params ){

		if( get_theme_mod('product_page_summary_fixed', false) ){
			$params['data-skip-lazy'] = 1;
			$params['data-no-lazy'] = 1;
		}

		if( $this->maybe_remove_image_title() ){
			$params['title'] = '';
		}

		if( isset($params['class']) ){
			$params['class'] .= ' no-lazy';
		}
		else {
			$params['class'] = 'no-lazy';
		}

		return $params;

	}

	public function maybe_remove_image_title(){
		return apply_filters('reycore/woocommerce/galleries/remove_title', false);
	}

	/**
	 * Enable Gallery Zoom on hover
	 * @since 1.0.0
	 */
	function enable_gallery_zoom(){
		return get_theme_mod('product_page_gallery_zoom', true);
	}

	/**
	 * Enable Gallery Zoom on hover
	 * @since 1.6.1
	 */
	function enable_photoswipe(){
		return get_theme_mod('product_page_gallery_lightbox', true);
	}

	/**
	 * Adds custom target container for zoom image
	 * @since 1.0.0
	 */
	function add_zoom_custom_target($html)
	{
		if( apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ) ){
			return str_replace('</a>', '</a><div class="rey-zoomContainer"></div>', $html);
		}
		return $html;
	}

	/**
	 * Specifies the custom zoom target
	 * @since 1.0.0
	 */
	function add_zoom_custom_target_option($options){
		$options['target'] = '.rey-zoomContainer';
		return $options;
	}

	/**
	 * Adds animation class to gallery item
	 *
	 * @since 1.0.0
	 */

	function add_animation_classes($html, $post_thumbnail_id)
	{
		$product = wc_get_product();
		if( ! $product ){
			global $product;
		}
		if( $product && ($main_image_id = $product->get_image_id()) && $main_image_id === $post_thumbnail_id){
			return $html;
		}

		return str_replace('woocommerce-product-gallery__image', 'woocommerce-product-gallery__image --animated-entry', $html);
	}

	function disable_thumbs_cropping($size){

		if( get_theme_mod('product_gallery_thumbs_disable_cropping', false) ){
			$size['height']  = 9999;
			$size['crop']   = false;
		}

		return $size;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_ProductGallery_Base
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

ReyCore_WooCommerce_ProductGallery_Base::getInstance();
