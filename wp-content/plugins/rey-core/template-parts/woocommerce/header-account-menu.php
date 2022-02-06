<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();

do_action( 'woocommerce_before_account_navigation' ); ?>

<nav class="woocommerce-MyAccount-navigation">

	<?php if( $heading = apply_filters('reycore/woocommerce/account-menu/heading', sprintf( __('Hello %s,', 'woocommerce'), ($current_user->user_firstname ? $current_user->user_firstname : $current_user->user_login) ), $current_user) ): ?>
	<h4 class="rey-accountPanel-title"><?php echo $heading; ?></h4>
	<?php endif; ?>

	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<?php
					if( $endpoint == 'orders' ){
						$label = sprintf('%s <span class="acc-count">%d</span>', $label, reycore_wc__count_orders($current_user->ID));
					}
					if( reycore_wc__check_downloads_endpoint() && $endpoint == 'downloads' ){
						$label = sprintf('%s <span class="acc-count">%d</span>', $label, reycore_wc__count_downloads($current_user->ID));
					}
				?>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo wp_kses_post($label) ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' );
