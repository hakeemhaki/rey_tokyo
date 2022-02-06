<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_ProductVideos') ):

	class ReyCore_WooCommerce_ProductVideos
	{
		public $video_url;
		public $product_id;

		private static $_instance = null;

		private function __construct()
		{
			add_action('wp', [$this, 'init'], 5);
		}

		function init(){

			if( ! reycore_wc__is_product() ){
				return;
			}

			$product = wc_get_product();

			if( ! $product ){
				global $product;
			}

			if( ! $product ){
				return;
			}

			$this->product_id = $product->get_id();
			$this->video_url = reycore__acf_get_field('product_video_url', $this->product_id );

			if( ! $this->video_url ){
				return;
			}

			$this->button_over_main_image = reycore__acf_get_field('product_video_main_image', $this->product_id );

			// adds regarless of other setting
			$this->add_to_summary();

			// adds video into gallery
			add_action('woocommerce_before_single_product_summary', [$this, 'add_video_button_into_gallery']);

			if( ! $this->button_over_main_image ){
				add_filter('reycore/woocommerce/product_mobile_gallery/html', [$this, 'add_extra_image__mobile'], 20, 4);
				add_action( 'woocommerce_product_thumbnails', [$this, 'add_extra_image__desktop'], 20);
			}
		}

		function add_extra_image__desktop(){

			if( ($image = reycore__acf_get_field('product_video_gallery_image', $this->product_id )) && isset($image['id']) ){

				echo '<div class="woocommerce-product-gallery__image">';

					$this->print_button([
						'text' => wp_get_attachment_image($image['id'], 'large'),
						'icon' => '<span class="rey-galleryPlayVideo-icon">'. reycore__get_svg_icon__core(['id' => 'reycore-icon-play']) .'</span>',
						'tag' => 'a',
						'attr' => [
							'href' => '#',
							'class' => 'rey-galleryPlayVideo',
						],
					]);

				echo '</div>';

				// load modal scripts
				add_filter( 'reycore/modals/always_load', '__return_true');
			}
		}

		function add_extra_image__mobile($gallery_image, $gallery_img_id, $gallery_image_ids, $index){

			$is_last = ($index + 1) === count($gallery_image_ids);

			if( $is_last && ($image = reycore__acf_get_field('product_video_gallery_image', $this->product_id )) && isset($image['id']) ){

				$gallery_image .= '<div class="splide__slide">';

				$gallery_image .= $this->print_button([
					'text' => wp_get_attachment_image($image['id'], 'large', false, [
						'class' => 'woocommerce-product-gallery__mobile-img no-lazy',
						'data-no-lazy' => 1,
						'data-skip-lazy' => 1,
					]),
					'icon' => '<span class="rey-galleryPlayVideo-icon">'. reycore__get_svg_icon__core(['id' => 'reycore-icon-play']) .'</span>',
					'tag' => 'a',
					'attr' => [
						'href' => '#',
						'class' => 'rey-galleryPlayVideo',
					],
					'echo' => false
				]);

				$gallery_image .= '</div>';

			}

			return $gallery_image;
		}

		function add_video_button_into_gallery(){

			if( $this->button_over_main_image ){
				$this->print_button([
					'text' => '',
					'attr' => [
						'class' => 'rey-singlePlayVideo d-none',
					],
				]);
			}
		}

		function summary_button(){
			$this->print_button([
				'wrap' => true
			]);
		}

		function print_button( $args = [] ){

			$video_url = $this->video_url;

			if( ! $video_url ){
				return;
			}

			$text_ = apply_filters( 'reycore/woocommerce/video/link_text', esc_html__('PLAY PRODUCT VIDEO', 'rey-core') );

			if( $custom_text = reycore__acf_get_field('product_video_link_text', $this->product_id ) ){
				$text_ = $custom_text;
			}

			if( isset($args['custom_text']) ){
				$text_ = $args['custom_text'];
			}

			$args = wp_parse_args($args, [
				'text' => $text_,
				'icon' => reycore__get_svg_icon__core(['id' => 'reycore-icon-play']),
				'tag' => 'span',
				'attr' => [
					'title' => $text_,
					'class' => 'btn btn-line u-btn-icon-sm',
					'data-elementor-open-lightbox' => 'no',
				],
				'wrap' => false,
				'echo' => true
			]);


			$options = [
				'iframe' => esc_url(str_replace('youtu.be/', 'youtube.com/watch?v=', $video_url)),
				'width' => 750,
				'wrapperClass' => 'rey-productVideo '
			];

			if( isset($args['modal_width']) ){
				$options['width'] = absint($args['modal_width']);
			}
			elseif( $width = reycore__acf_get_field('product_video_modal_size', $this->product_id ) ){
				$options['width'] = absint($width);
			}

			if( isset($args['modal_video_ratio']) ){
				$options['ratio'] = absint($args['modal_video_ratio']);
			}
			elseif( $ratio = reycore__acf_get_field('product_video_modal_ratio', $this->product_id ) ){
				$options['ratio'] = reycore__clean($ratio);
			}

			$args['attr']['data-reymodal'] = wp_json_encode($options);

			$button = apply_filters( 'reycore/woocommerce/video_button', sprintf('<%3$s %4$s>%2$s %1$s</%3$s>',
				$args['text'],
				$args['icon'],
				$args['tag'],
				reycore__implode_html_attributes($args['attr'])
			, $args ));

			$print_before = $print_after = '';

			if( $args['wrap'] ){
				$print_before = '<div class="rey-singlePlayVideo-wrapper">';
				$print_after = '</div>';
			}

			// load modal scripts
			add_filter( 'reycore/modals/always_load', '__return_true');

			if( $args['echo'] ){
				echo $print_before . $button . $print_after;
			}
			else {
				return $print_before . $button . $print_after;
			}
		}

		function add_to_summary(){

			if( ! ($summary_position = reycore__acf_get_field('product_video_summary', $this->product_id )) ) {
				return;
			}

			if( $summary_position === 'disabled' ){
				return;
			}

			$available_positions = [
				'after_title'         => 6,
				'before_add_to_cart'  => 29,
				'before_product_meta' => 39,
				'after_product_meta'  => 41,
				'after_share'         => 51,
			];

			add_action( 'woocommerce_single_product_summary', [ $this, 'summary_button' ], $available_positions[$summary_position] );
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return Base
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_WooCommerce_ProductVideos::getInstance();

endif;
