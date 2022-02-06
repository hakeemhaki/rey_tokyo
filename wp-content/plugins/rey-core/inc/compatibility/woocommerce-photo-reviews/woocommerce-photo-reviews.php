<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION' ) && !class_exists('ReyCore_Compatibility__WooPhotoReviews') ):

	class ReyCore_Compatibility__WooPhotoReviews
	{
		private $settings = [];

		const ASSET_HANDLE = 'reycore-woo-photo-reviews-styles';

		public function __construct()
		{
			add_action( 'init', [ $this, 'init' ] );
			add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			reycore__remove_filters_for_anonymous_class( 'admin_init', 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Admin', 'check_update', 10 );
			add_filter( 'woocommerce_reviews_title', [$this, 'title_improvement'], 20, 3);
			add_filter( 'reycore/woocommerce/single/reviews_template', '__return_false', 20, 3);
		}

		function title_improvement($reviews_title, $count, $product){

			// reset
			$reviews_title = '';
			// rating
			$rating_average = $product->get_average_rating();
			$reviews_title .= sprintf('<div class="rey-reviewTop">%s <span><strong>%s</strong>/5</span></div>', wc_get_rating_html( $rating_average, $count ), $rating_average);
			// title
			$reviews_title .= sprintf( '<div class="rey-reviewTitle">' . esc_html( _n( '%s Customer review', '%s Customer reviews', $count, 'rey-core' ) ) . '</div>' , esc_html( $count ) );

			return $reviews_title;
		}

		public function init(){
			$this->settings = apply_filters('reycore/woo_photo_reviews/params', []);
		}

		public function enqueue_scripts(){
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
		}

		public function register_scripts(){
            wp_register_style( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}

	}

	new ReyCore_Compatibility__WooPhotoReviews();
endif;
