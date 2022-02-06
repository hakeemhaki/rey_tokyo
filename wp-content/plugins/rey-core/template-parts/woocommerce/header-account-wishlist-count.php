<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args();
$wishlist_counter = class_exists('ReyCore_WooCommerce_Wishlist') ? ReyCore_WooCommerce_Wishlist::get_wishlist_counter_html() : '';

?>
<span class="rey-headerAccount-count">

	<?php
		if( $args['wishlist'] && $args['counter'] != '' ):
			echo $wishlist_counter;
		endif;
	?>

	<span class="rey-headerAccount-closeIcon">
		<?php
		echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?>
	</span>

</span>
