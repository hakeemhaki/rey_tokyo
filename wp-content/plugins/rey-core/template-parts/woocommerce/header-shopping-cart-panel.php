<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mini Cart
 */
if ( !class_exists('WooCommerce') ) {
    return;
}

$panel_class = 'rey-cartPanel';

if( $args['inline_buttons'] ){
	$panel_class .= ' --btns-inline';
}
?>

<div class="rey-cartPanel-wrapper rey-sidePanel js-rey-cartPanel woocommerce">
	<div class="<?php echo esc_attr($panel_class); ?>">

		<div class="rey-cartPanel-header">

			<div class="__tabs">

				<div class="__tab --active" data-item="cart">
					<h3 class="rey-cartPanel-title"><?php echo $args['cart_title'] ?> <span class="rey-cartPanel-counter __nb"><?php echo is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : ''; ?></span></h3>
				</div>

				<?php if( $args['recent']['enabled'] ): ?>
				<div class="__tab" data-item="recent">
					<h3 class="rey-cartPanel-title"><?php echo $args['recent']['title'] ?> <span class="__nb"><?php echo count($args['recent']['ids'] ) ?></span></h3>
				</div>
				<?php endif; ?>

				<?php do_action('reycore/woocommerce/mini-cart/tabs'); ?>

			</div>

			<button class="btn rey-cartPanel-close rey-sidePanel-close js-rey-sidePanel-close" aria-label="<?php esc_html_e('Close cart', 'rey-core') ?>">
				<?php
				if( get_theme_mod('header_cart__close_extend', false) && ($close_text = get_theme_mod('header_cart__close_text', '')) ){
					printf( '<span>%s</span>', $close_text );
				} ?>
				<?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']); ?>
			</button>

		</div>

		<div class="__tab-content --active" data-item="cart">
			<?php the_widget( 'WC_Widget_Cart', 'title='); ?>
		</div>

		<?php if( $args['recent']['enabled'] ):
			reycore__get_template_part('template-parts/woocommerce/header-shopping-cart-recent', false, false, [
				'ids' => $args['recent']['ids'],
				'quickview' => $args['recent']['quickview']
			]);
		endif; ?>

		<?php do_action('reycore/woocommerce/mini-cart/after_content'); ?>
	</div>
</div>
