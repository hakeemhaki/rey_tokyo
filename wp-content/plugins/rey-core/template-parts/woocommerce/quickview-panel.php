<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;

$post = get_post( $args['pid'] );
setup_postdata( $post );

do_action( 'reycore/woocommerce/quickview/before_render' );

$classes = [
	'fit' => '--image-fit-' . get_theme_mod('loop_quickview_image_fit', 'cover')
];

?>
<div class="rey-quickview-mask"></div>
<div class="rey-quickview-mask"></div>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( implode(' ', $classes), $args['pid'] ); ?> data-id="<?php the_ID(); ?>">

	<?php
		/**
		 * Hook: woocommerce_before_single_product_summary.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="summary entry-summary">
		<div class="summary-inner js-scrollbar">
			<?php
				if( class_exists('ReyCore_WooCommerce_ProductNavigation') ){
					// ReyCore_WooCommerce_ProductNavigation::getInstance()->get_navigation();
				}
				/**
				 * Hook: woocommerce_single_product_summary.
				 *
				 * @hooked woocommerce_template_single_title - 5
				 * @hooked woocommerce_template_single_rating - 10
				 * @hooked woocommerce_template_single_price - 10
				 * @hooked woocommerce_template_single_excerpt - 20
				 * @hooked woocommerce_template_single_add_to_cart - 30
				 * @hooked woocommerce_template_single_meta - 40
				 * @hooked woocommerce_template_single_sharing - 50
				 * @hooked WC_Structured_Data::generate_product_data() - 60
				 */
				do_action( 'woocommerce_single_product_summary' );
			?>
		</div>
	</div>

</div>
<?php
wp_reset_postdata();
