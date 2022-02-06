<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Reviews') ):

	class ReyCore_WooCommerce_Reviews
	{

		public function __construct() {
			add_action( 'init', [$this, 'init']);
		}

		function init(){
			add_filter( 'comments_template', [$this, 'comments_template_loader'] );
			add_filter( 'reycore/woocommerce/single/reviews_btn', [$this, 'reviews_btn']);
			add_filter( 'reycore/woocommerce/single/reviews_classes', [$this, 'reviews_block_classes']);
			add_action( 'wp_ajax_reycore_load_more_reviews', [$this, 'load_more_reviews']);
			add_action( 'wp_ajax_nopriv_reycore_load_more_reviews', [$this, 'load_more_reviews']);
			add_action( 'woocommerce_review_before_comment_meta', [$this, 'handle_stars_in_review'], 5);
			add_filter( 'get_avatar', [$this, 'hide_avatar']);
			// add_action( 'reycore/woocommerce/reviews/before', [$this, 'before_reviews']);
			// add_action( 'reycore/woocommerce/reviews/after', [$this, 'after_reviews']);
		}

		function reviews_btn($classes){

			if( get_theme_mod('single_reviews_start_opened', false) ){
				$classes[] = '--toggled';
			}

			return $classes;
		}

		function get_review_layout(){
			return get_theme_mod('single_reviews_layout', 'default');
		}

		function reviews_block_classes($classes){

			$classes['style'] = '--style-' . esc_attr($this->get_review_layout());

			if( get_theme_mod('single_reviews_ajax', true) ){
				$classes['is_ajax'] = '--ajax';
			}

			if( get_theme_mod('single_reviews_start_opened', false) ){
				$classes['is_visible'] = '--visible';
			}

			return $classes;
		}

		function hide_avatar($avatar){

			if( 'minimal' === $this->get_review_layout() ){
				return '';
			}

			if( ! get_theme_mod('single_reviews_avatar', true) ){
				return '';
			}

			return $avatar;
		}

		function before_review_text(){
			echo '<div class="rey-descWrap">';
		}

		function after_review_text(){
			echo '</div>';
		}

		function handle_stars_in_review(){

			if( 'minimal' !== $this->get_review_layout() ){
				return;
			}

			remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10);
			add_action( 'woocommerce_review_comment_text', [$this, 'before_review_text'], 9);
			add_action( 'woocommerce_review_comment_text', 'woocommerce_review_display_rating', 9);
			add_action( 'woocommerce_review_comment_text', [$this, 'verified'], 9);
			add_action( 'woocommerce_review_comment_text', [$this, 'after_review_text'], 11);

		}

		function verified(){
			global $comment;
			$verified = wc_review_is_from_verified_owner( $comment->comment_ID );
			if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) {
				echo '<span class="woocommerce-review__verified verified">(' . esc_attr__( 'verified owner', 'woocommerce' ) . ')</span> ';
			}
		}

		function load_more_reviews(){

			if( ! (isset($_REQUEST['qid']) && $product_id = absint($_REQUEST['qid'])) ){
				wp_send_json_error(['error' => 'Missing PID']);
			}

			if( ! isset($_REQUEST['page']) ){
				wp_send_json_error(['error' => 'Missing Page']);
			}

			add_filter( 'option_thread_comments', '__return_false' );

			$order = 'newest';

			if( isset($_REQUEST['order']) && $custom_order = reycore__clean($_REQUEST['order']) ){
				$order = $custom_order;
			}

			$limit = get_theme_mod('single_reviews_ajax_limit', 5);
			$page = absint($_REQUEST['page']);

			add_filter('option_comments_per_page', function() use ($limit){
				return $limit;
			});

			add_action('pre_get_comments', function($query) use ($limit, $page, $product_id, $order){

				$query->query_vars['post_id'] = $product_id;
				$query->query_vars['number'] = $limit;
				$query->query_vars['offset'] = $limit * $page;

				if( 'newest' === $order ){
					$query->query_vars['order'] = 'DESC';
				}
				elseif( 'oldest' === $order ){
					$query->query_vars['order'] = 'ASC';
				}
				elseif( 'highest' === $order ){
					$query->query_vars['orderby'] = 'meta_value_num';
					$query->query_vars['meta_key'] = 'rating';
					$query->query_vars['order'] = 'DESC';
				}
				elseif( 'lowest' === $order ){
					$query->query_vars['orderby'] = 'meta_value_num';
					$query->query_vars['meta_key'] = 'rating';
					$query->query_vars['order'] = 'ASC';
				}

			});

			ob_start();

				wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', [
					'callback'          => 'woocommerce_comments',
					'per_page'          => $limit,
					'max_depth'         => 0,
					'page'              => 0,
				] ) );

			$data = ob_get_clean();

			if( empty($data) ){
				wp_send_json_error(['error' => 'Empty content.']);
			}

			wp_send_json_success($data);
		}

		function comments_template_loader( $template ){

			if( ! apply_filters('reycore/woocommerce/single/reviews_template', true) ){
				return $template;
			}

			if ( get_post_type() !== 'product' ) {
				return $template;
			}

			// if ( ! is_product() ) {
			// 	return false;
			// }

			$fn = 'single-product-reviews.php';

			// check if child theme template exists (from WooCommerce)
			if ( file_exists( trailingslashit( get_stylesheet_directory() ) . WC()->template_path() . $fn ) ) {
				return $template;
			}

			$check_dirs = array(
				trailingslashit( STYLESHEETPATH ),
				trailingslashit( TEMPLATEPATH ),
				trailingslashit( REY_CORE_DIR ),
			);

			foreach ( $check_dirs as $dir ) {

				if ( file_exists( $dir . 'template-parts/woocommerce/' . $fn ) ) {

					reyCoreAssets()->add_styles('rey-wc-product-reviews');

					return $dir . 'template-parts/woocommerce/' . $fn;
				}
			}

		}
	}

	new ReyCore_WooCommerce_Reviews;
endif;
