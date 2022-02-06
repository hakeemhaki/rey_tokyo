<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-quickviewPanel woocommerce" id="js-rey-quickviewPanel">
	<div class="rey-quickview-container --<?php echo get_theme_mod('loop_quickview__panel_style', 'curtain'); ?>"></div>
	<button class="btn rey-quickviewPanel-close js-rey-quickviewPanel-close" aria-label="<?php esc_attr__('CLOSE', 'rey-core') ?>" ><?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?></button>
	<div class="rey-lineLoader"></div>
</div>
