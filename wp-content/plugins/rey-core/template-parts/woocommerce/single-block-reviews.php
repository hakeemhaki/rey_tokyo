<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! wc_review_ratings_enabled() ){
	return;
}

$reviews_count = 0;

if( $product = wc_get_product() ){
	$reviews_count = $product->get_review_count() ;
} ?>

<div class="rey-wcPanels <?php echo implode(' ', array_map('esc_attr', apply_filters('rey/woocommerce/product_panels_classes', []))) ?>">
	<?php

		do_action('reycore/woocommerce/before_block_reviews'); ?>

		<div class="rey-wcPanel rey-wcPanel--reviews">
			<div class="rey-wcPanel-inner">
				<div class="rey-reviewsBtn js-toggle-target <?php echo implode(' ', array_map('esc_attr', apply_filters('reycore/woocommerce/single/reviews_btn', ['btn', 'btn-secondary-outline', 'btn--block']))) ?>" data-target=".rey-wcPanel--reviews #reviews" role="button">
					<span><?php printf( __( 'Reviews (%d)', 'rey-core' ), $reviews_count ) ?></span>
				</div>
				<?php
					comments_template();
				?>
			</div>
		</div>
		<?php

		do_action('reycore/woocommerce/after_block_reviews');
	?>
</div>
