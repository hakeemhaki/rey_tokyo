<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if( function_exists('reycore__ajax_load_more_pagination') ):
	reycore__ajax_load_more_pagination([
		'class' => 'btn btn-line-active js-rey-ajaxLoadMore',
		'target' => '.rey-siteMain ul.products',
		'post_type' => 'product',
	]);
endif;