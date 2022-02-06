<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$html = '';

if( ! empty( $args['ids'] ) ){

	$html .= '<div data-ss-container>';
	$html .= '<div class="rey-cartRecent-items">';

	$thumb_classes = '';

	if( class_exists('ReyCore_WooCommerce_Loop') && ReyCore_WooCommerce_Loop::getInstance()->is_custom_image_height() ){
		$thumb_classes .= ' --customImageContainerHeight';
	}

	foreach ($args['ids'] as $key => $product_id) {

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			continue;
		}

		if ( ! $product->is_purchasable() ) {
			continue;
		}

		$post = get_post( $product_id );
		setup_postdata( $post );

		ob_start();	?>

		<div class="rey-cartRecent-item" data-id="<?php echo esc_attr($product_id) ?>">

			<div class="rey-cartRecent-itemThumb <?php echo esc_attr($thumb_classes) ?>">
				<?php
					woocommerce_template_loop_product_link_open();
					woocommerce_template_loop_product_thumbnail();
					woocommerce_template_loop_product_link_close();
				?>
			</div>

			<div class="rey-cartRecent-itemContent">
				<?php

					if( class_exists('ReyCore_WooCommerce_Loop') ):
						ReyCore_WooCommerce_Loop::getInstance()->component_brands();
					endif;

					echo sprintf(
						'<h4 class="%s"><a href="%s">%s</a></h4>',
						esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'rey-cartRecent-itemTitle' ) ),
						esc_url(get_the_permalink()),
						get_the_title()
					);

					woocommerce_template_loop_price();

					woocommerce_template_loop_add_to_cart([
						'wrap_button' => false,
						'supports_qty' => false,
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

					do_action('reycore/woocommerce/cart/cart_recent', $product_id);

					if( $args['quickview'] &&
						class_exists('ReyCore_WooCommerce_QuickView') &&
						in_array(true, reycore_wc_get_loop_components('quickview'), true) ){
						echo ReyCore_WooCommerce_QuickView::getInstance()->get_button_html( $product_id, 'btn btn-line-active' );
					}
				?>
			</div>

		</div><?php

		$html .= ob_get_clean();

		wp_reset_postdata();

	}

	$html .= '</div>';
	$html .= '</div>';
}

if( empty($html) ){
	$html = sprintf('<p class="woocommerce-mini-cart__empty-message">%s</p>', esc_html__('No products in the list.', 'rey-core'));
}

?>

<div class="__tab-content rey-cartRecent-itemsWrapper" data-item="recent">
	<?php echo $html; ?>
</div>
