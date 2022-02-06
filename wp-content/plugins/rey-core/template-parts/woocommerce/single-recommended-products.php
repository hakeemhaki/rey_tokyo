<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( empty( $args['ids'] ) ){
	return;
} ?>

<div class="rey-recommended-items <?php echo $args['class']; ?>">

	<?php

	do_action('reycore/woocommerce/before_recommended_products');

	foreach ($args['ids'] as $key => $product_id):

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			continue;
		}

		if ( ! $product->is_purchasable() ) {
			continue;
		}

		$post = get_post( $product_id );
		setup_postdata( $post );
		?>

		<div class="rey-recommended-item" data-id="<?php echo esc_attr($product_id) ?>">

			<div class="rey-recommended-itemThumb">
				<?php
					woocommerce_template_loop_product_link_open();
					echo $product->get_image( 'woocommerce_thumbnail' );
					woocommerce_template_loop_product_link_close();
				?>
			</div>

			<div class="rey-recommended-itemContent">
				<?php

				if( class_exists('ReyCore_WooCommerce_Loop') ):
					ReyCore_WooCommerce_Loop::getInstance()->component_brands();
				endif;

				echo sprintf(
					'<h4 class="%s"><a href="%s">%s</a></h4>',
					esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'rey-recommended-itemTitle' ) ),
					esc_url(get_the_permalink()),
					get_the_title()
				);

				woocommerce_template_loop_price();

				woocommerce_template_loop_add_to_cart([
					'wrap_button' => false,
					'class'      => implode(
						' ',
						array_filter(
							[
								'btn btn-line-active',
								'product_type_' . $product->get_type(),
								$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
								$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
							]
						)
					),
				]);

				do_action('reycore/woocommerce/recommended_product', $product_id);

				if( $args['quickview'] &&
					class_exists('ReyCore_WooCommerce_QuickView') &&
					in_array(true, reycore_wc_get_loop_components('quickview'), true) ){
					echo ReyCore_WooCommerce_QuickView::getInstance()->get_button_html( $product_id, 'btn btn-line-active' );
				} ?>

			</div>

		</div><?php

		wp_reset_postdata();

	endforeach;

	do_action('reycore/woocommerce/after_recommended_products'); ?>

</div>
