<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Single_GS') ):

	class ReyCore_WooCommerce_Single_GS
	{
		private static $_instance = null;

		private function __construct() {
			add_action('init', [$this, 'init']);

		}

		function init(){
			add_action( 'wp', [$this, 'global_section_after_content_choose'], 10 );
			add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_product_summary'], 6 );
			add_filter( 'reycore/woocommerce/product_page/content_after', [$this, 'global_section_after_content_per_category'], 10, 2);
			add_filter( 'reycore/woocommerce/product_page/content_after', [$this, 'global_section_after_content_per_page'], 20, 2);
		}

		/**
		 * Adds global section *after product summary*
		 *
		 * @since 1.0.0
		 */
		function global_section_after_product_summary(){

			if( ! class_exists('ReyCore_GlobalSections' ) ){
				return;
			}

			$global_section = '';

			if( ($gs = reycore__get_option('product_content_after_summary', 'none')) && $gs !== 'none' ) {
				$global_section = $gs;
			}

			$global_section = absint( apply_filters( 'reycore/woocommerce/product_page/content_after', $global_section, 'summary' ) );

			if( $global_section ){
				echo ReyCore_GlobalSections::do_section( $global_section );
			}
		}


		function global_section_after_content_choose(){

			if( apply_filters('reycore/woocommerce/global_section_after_content/after_related', false) ){
				add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_content'], 20 );
				return;
			}

			$can_display_before_reviews[] = get_theme_mod('product_content_after_content__before_reviews', false) && wc_reviews_enabled();

			if( $product = wc_get_product() ){
				$can_display_before_reviews[] = $product->get_reviews_allowed();
			}

			if( ! in_array(false, $can_display_before_reviews, true) ){
				add_action( 'reycore/woocommerce/before_block_reviews', [$this, 'global_section_after_content'], 10 );
			}
			else {
				add_action( 'woocommerce_after_single_product_summary', [$this, 'global_section_after_content'], 10 );
			}
		}

		/**
		 * Adds global section *after content*
		 *
		 * @since 1.0.0
		 */
		function global_section_after_content(){
			if( ! class_exists('ReyCore_GlobalSections' ) ){
				return;
			}

			$global_section = '';

			if( ($gs = reycore__get_option('product_content_after_content', 'none')) && $gs !== 'none' ) {
				$global_section = $gs;
			}

			$global_section = absint( apply_filters( 'reycore/woocommerce/product_page/content_after', $global_section, 'content' ) );

			if( $global_section ){
				echo ReyCore_GlobalSections::do_section( $global_section );
			}
		}

		/**
		 * Adds global section after content, to products that belong to certain categories.
		 *
		 * @since 1.4.0
		 */
		function global_section_after_content_per_category( $gs, $position ){

			$choices = get_theme_mod('product_content_after_content_per_category', []);

			if( empty($choices) ){
				return $gs;
			}

			$chosen_gs = [];

			foreach($choices as $gs_item):

				if( ! empty($gs_item['categories']) ){

					$cats = apply_filters('reycore/translate_ids', $gs_item['categories'], 'product_cat');

					if( has_term( $cats, 'product_cat', get_the_ID() ) && $gs_item['position'] === $position ){
						$gs = absint($gs_item['gs']);
						$chosen_gs[] = absint($gs_item['gs']);
					}

				}

				if( ! empty($gs_item['attributes']) ){

					foreach ($gs_item['attributes'] as $key => $term_id) {

						$term = get_term($term_id);

						if( is_wp_error($term) ){
							continue;
						}

						if( isset($term->taxonomy) && has_term( $term_id, $term->taxonomy, get_the_ID() ) && $gs_item['position'] === $position ){
							$gs = absint($gs_item['gs']);
							$chosen_gs[] = absint($gs_item['gs']);
						}
					}

				}

			endforeach;

			if( ! empty($chosen_gs) && ($valid_gs = array_unique($chosen_gs)) && isset($valid_gs[0]) ){
				return $valid_gs[0];
			}

			return $gs;
		}

		/**
		 * Adds global section after content, to products that belong to certain categories.
		 *
		 * @since 1.4.0
		 */
		function global_section_after_content_per_page( $gs, $position ){

			if( $position === 'content' && ($acf_content_gs = reycore__acf_get_field('product_content_after_content')) ){

				if( $acf_content_gs === 'none' ){
					return false;
				}

				return absint($acf_content_gs);
			}

			elseif( $position === 'summary' && ($acf_summary_gs = reycore__acf_get_field('product_content_after_summary')) ){

				if( $acf_summary_gs === 'none' ){
					return false;
				}

				return absint($acf_summary_gs);
			}

			return $gs;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_WooCommerce_Single_GS
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	ReyCore_WooCommerce_Single_GS::getInstance();

endif;
