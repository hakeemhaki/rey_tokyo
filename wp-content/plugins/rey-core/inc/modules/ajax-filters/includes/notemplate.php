<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// cover published
if( function_exists('rey_action__after_header_outside') && class_exists('ReyCore_PageCover') && ReyCore_PageCover::instance()->get_cover_id() ){
	rey_action__after_header_outside();
}

if( function_exists('rey__main_content_wrapper_start') ){
	rey__main_content_wrapper_start();
}

if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' );

	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );
		}
	}

	woocommerce_product_loop_end();

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}

do_action( 'woocommerce_after_main_content' );

if( function_exists('rey__main_content_wrapper_end') ){
	rey__main_content_wrapper_end();
}

if( function_exists('reycore_wc__check_filter_panel') && reycore_wc__check_filter_panel() ) {
	reycore__get_template_part('template-parts/woocommerce/filter-panel-sidebar');
}
