<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

reyCoreAssets()->add_styles('rey-wc-tag-widgets');

/**
 * Filter Sidebar Panel
 */
 ?>

<div class="rey-filterPanel-wrapper rey-sidePanel" data-align="<?php echo esc_attr(get_theme_mod('ajaxfilter_panel_pos', 'right')) ?>" id="js-filterPanel">

	<?php do_action('reycore/filters_sidebar/before_panel'); ?>

	<div class="rey-filterPanel">

		<header class="rey-filterPanel__header">
			<button class="rey-sidePanel-close js-rey-sidePanel-close"  aria-label="<?php esc_html_e('Close filters', 'rey-core') ?>">
				<?php
				echo reycore__get_svg_icon(['id' => 'rey-icon-close']); ?>
			</button>
			<button class="rey-filterPanel__reset btn btn-line-active js-rey-filter-reset" data-location="<?php echo reycore_wc__reset_filters_link(); ?>"  aria-label="<?php esc_html_e('RESET FILTERS', 'rey-core') ?>"><?php esc_html_e('RESET FILTERS', 'rey-core'); ?></button>
		</header>

		<div class="rey-filterPanel-content-wrapper">
			<div class="rey-filterPanel-content" data-ss-container>
				<?php do_action('reycore/filters_sidebar/before_widgets'); ?>
				<?php dynamic_sidebar( 'filters-sidebar' ); ?>
				<?php do_action('reycore/filters_sidebar/after_widgets'); ?>
			</div>
		</div>

	</div>
	<?php do_action('reycore/filters_sidebar/after_panel'); ?>
</div>
