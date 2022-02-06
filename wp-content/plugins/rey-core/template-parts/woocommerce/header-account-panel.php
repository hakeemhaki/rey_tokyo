<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args();

$classes = [
	'rey-accountPanel-wrapper',
	'js-rey-accountPanel',
	'--layout-' . $args['display'],
];

if( $args['display'] === 'drop' && $args['drop_close_on_scroll'] ){
	$classes['scroll'] = '--close-on-scroll';
}

if( $args['display'] === 'offcanvas' ){
	$classes[] = 'rey-sidePanel';
} ?>

<div class="<?php echo esc_attr( implode(' ', $classes) ); ?>">
	<div class="rey-accountPanel">

		<?php if( $args['display'] === 'offcanvas' ): ?>
		<button class="rey-sidePanel-close js-rey-sidePanel-close" aria-label="<?php esc_html_e('Close account details', 'rey-core') ?>">
			<?php
			echo reycore__get_svg_icon(['id' => 'rey-icon-close']); ?>
		</button>
		<?php endif; ?>

		<?php do_action('reycore/woocommerce/account_panel'); ?>
	</div>
	<!-- .rey-accountPanel -->
</div>
<!-- .rey-accountPanel-wrapper -->
