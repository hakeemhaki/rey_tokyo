<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Shop Options
 */

/**
 * Customizer Settings
 */
add_action('customize_register', function($wp_customize) {

	$wp_customize->get_panel( 'woocommerce' )->priority = 40;

	// Catalog Layout
	$wp_customize->get_section( 'woocommerce_product_catalog' )->title = esc_html__('Product Catalog - Layout', 'rey-core');
	$wp_customize->get_control( 'woocommerce_catalog_columns' )->priority = 1;
	$wp_customize->get_control( 'woocommerce_catalog_rows' )->priority = 3;

	// Misc. - Reassign default sections to panels.
	$wp_customize->get_control( 'woocommerce_shop_page_display' )->priority = 10;
	$wp_customize->get_control( 'woocommerce_shop_page_display' )->section = 'woocommerce_product_catalog_misc';
	$wp_customize->get_control( 'woocommerce_category_archive_display' )->priority = 10;
	$wp_customize->get_control( 'woocommerce_category_archive_display' )->section = 'woocommerce_product_catalog_misc';
	$wp_customize->get_control( 'woocommerce_default_catalog_orderby' )->priority = 15;
	$wp_customize->get_control( 'woocommerce_default_catalog_orderby' )->section = 'woocommerce_product_catalog_misc';

	// demo store
	$wp_customize->get_control( 'woocommerce_demo_store' )->priority = 10;
	$wp_customize->get_control( 'woocommerce_demo_store_notice' )->priority = 15;
	$wp_customize->get_section( 'woocommerce_store_notice' )->priority = 25;

	// Images
	$wp_customize->get_section( 'woocommerce_product_images' )->priority  = 20;
	$wp_customize->get_control( 'woocommerce_single_image_width' )->priority = 10;
	$wp_customize->get_control( 'woocommerce_thumbnail_image_width' )->priority = 20;
	$wp_customize->get_control( 'woocommerce_thumbnail_cropping' )->priority = 30;

	// checkout
	$wp_customize->get_section( 'woocommerce_checkout' )->priority  = 30;

});


require REY_CORE_DIR . 'inc/customizer/options/woocommerce/catalog-layout.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/catalog-components.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/catalog-content.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/catalog-misc.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-layout.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-components.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-content.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/product-page-tabs.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/images.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/brands.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/cart.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/checkout.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/store_notice.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/wishlist.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/compare.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/refunds.php';
require REY_CORE_DIR . 'inc/customizer/options/woocommerce/advanced_settings.php';
