<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


// Check if filters are active
$count = reycore_wc__get_active_filters();
$target = reycore_wc__check_filter_btn();

$btn_classes =  implode( ' ', apply_filters('reycore/filters/btn_class', [
	'rey-filterBtn--pos-' . get_theme_mod('ajaxfilter_panel_btn_pos', 'right'),
	$count ? '--has-filters' : '',
	$target !== 'filters-sidebar' ? '--dnone-lg' : ''
]) );

reyCoreAssets()->add_styles('rey-wc-tag-widgets');

?>
<div class="rey-filterBtn <?php echo esc_attr($btn_classes); ?> " data-target="<?php echo esc_attr($target) ?>">

	<button class="btn btn-line rey-filterBtn__label js-rey-filterBtn-open" aria-label="<?php esc_html_e('Open filters', 'rey-core') ?>">
		<?php
			echo reycore__get_svg_icon(['id' => 'rey-icon-sliders']) ?>
		<span><?php esc_html_e('FILTER', 'rey-core') ?></span>
		<?php
		// Show count
		if( $count ): ?>
			<span class="rey-filterBtn__count" data-count="<?php echo esc_attr($count); ?>"></span>
		<?php endif; ?>
	</button>

	<?php
	// Show close button
	if( $count ): ?>
	<button class="rey-filterBtn__reset js-rey-filter-reset" data-tooltip-text="<?php esc_html_e('RESET FILTERS', 'rey-core') ?>" data-location="<?php echo reycore_wc__reset_filters_link(); ?>"  aria-label="<?php esc_html_e('RESET FILTERS', 'rey-core') ?>">
		<?php
		echo reycore__get_svg_icon(['id' => 'rey-icon-close']); ?>
	</button>
	<?php endif; ?>

</div>
