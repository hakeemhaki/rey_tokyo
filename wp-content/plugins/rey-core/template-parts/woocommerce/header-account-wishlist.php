<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args();
$wishlist_url = class_exists('ReyCore_WooCommerce_Wishlist') ? ReyCore_WooCommerce_Wishlist::get_wishlist_url() : '';
$wishlist_counter = class_exists('ReyCore_WooCommerce_Wishlist') ? ReyCore_WooCommerce_Wishlist::get_wishlist_counter_html() : '';

if( reycore_wc__get_account_panel_args('wishlist') ): ?>
	<div class="rey-accountWishlist-wrapper">
		<h4 class="rey-accountPanel-title">
			<?php
			if( $wishlist_url ){
				printf( '<a href="%s">', esc_url( $wishlist_url ) );
			}
				echo apply_filters('reycore/woocommerce/wishlist/title', esc_html__('WISHLIST', 'rey-core'));

			if( $wishlist_url ){
				echo '</a>';
			}

			if( $args['wishlist'] && $args['counter'] != '' ){
				echo $wishlist_counter;
			} ?>

		</h4>
		<div class="rey-wishlistPanel-container --prod-<?php echo $args['wishlist_prod_layout'] ?>">
			<div class="rey-accountWishlist rey-wishlistPanel"></div>
			<div class="rey-lineLoader"></div>
		</div>
	</div>
	<?php
endif;
