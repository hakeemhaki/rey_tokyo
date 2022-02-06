<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		$the_cart_items = WC()->cart->get_cart();

		if( apply_filters('reycore/woocommerce/mini_cart/reverse', true) ){
			$the_cart_items = array_reverse($the_cart_items);
		}

		foreach ( $the_cart_items as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('thumbnail'), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

				$classes[] = 'mini_cart_item';
				// $classes[] = 'mini_cart_item--' . $_product->get_type();

				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', implode(' ', $classes), $cart_item, $cart_item_key ) ); ?>">
					<?php
					echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a href="%s" class="remove remove_from_cart_button rey-removeBtn" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">%s</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							esc_attr__( 'Remove this item', 'woocommerce' ),
							esc_attr( $product_id ),
							esc_attr( $cart_item_key ),
							esc_attr( $_product->get_sku() ),
							reycore__get_svg_icon(['id' => 'rey-icon-close'])
						),
						$cart_item_key
					);
					?>
					<?php if ( empty( $product_permalink ) ) : ?>
						<?php echo '<div class="rey-cartImg">'.$thumbnail.'</div>' . wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<a href="<?php echo esc_url( $product_permalink ); ?>" class="woocommerce-mini-cart-thumbTitle">
							<?php echo '<div class="rey-cartImg">'.$thumbnail.'</div>' . wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
					<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<?php do_action( 'reycore/woocommerce/minicart/before_totals' ); ?>

	<div class="woocommerce-mini-cart__total total">

		<div class="woocommerce-mini-cart__totalRow">
			<?php
			/**
			 * Hook: woocommerce_widget_shopping_cart_total.
			 *
			 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
			 */
			do_action( 'woocommerce_widget_shopping_cart_total' ); ?>
		</div>

		<?php

		if ( !is_cart() && !is_checkout() && wc_coupons_enabled() && get_theme_mod('header_cart_coupon', true) ) {

			$coupons = WC()->cart ? WC()->cart->get_coupons() : [];
			$show_form = true;

			if( ! empty($coupons) && get_theme_mod('header_cart_coupon_hide_if_applied', true) ){
				$show_form = false;
			}

			if( ! empty($coupons) ):
				foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<div class="woocommerce-mini-cart__totalRow coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<strong><?php wc_cart_totals_coupon_label( $coupon ); ?></strong>
					<div class="woocommerce-mini-cart__totalCell"><?php wc_cart_totals_coupon_html( $coupon ); ?></div>
				</div>
			<?php endforeach;
			endif;

			if( $show_form ): ?>
			<div class="rey-toggleCoupon">
				<a href="#" class="rey-toggleCoupon-btn"><?php esc_html_e('Have a Coupon?', 'rey-core') ?></a>
				<div class="rey-toggleCoupon-content">
					<?php reycore__get_template_part('template-parts/woocommerce/cart/coupon'); ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="rey-toggleCoupon-response"></div>

		<?php } ?>

	</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
