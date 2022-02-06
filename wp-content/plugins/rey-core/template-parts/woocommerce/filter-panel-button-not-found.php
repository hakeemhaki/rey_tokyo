<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


// Check if filters are active
$count = reycore_wc__get_active_filters();


?>

<div class="rey-filterBtn--noRes">

	<?php if( function_exists('reyAjaxFilters') && reyAjaxFilters()->check_widgets_support() ): ?>
	<button class="btn-line-active js-rey-filterBtn-open" aria-label="<?php esc_html_e('REFINE FILTERS', 'rey-core') ?>">
		<?php esc_html_e('REFINE FILTERS', 'rey-core') ?>
	</button>
	<?php endif; ?>

	<?php
	// Show close button
	if( $count ): ?>
	<button class="btn-line-active js-rey-filter-reset" data-location="<?php echo reycore_wc__reset_filters_link(); ?>" aria-label="<?php esc_html_e('Reset filters', 'rey-core') ?>">
		<?php esc_html_e('RESET', 'rey-core') ?>
	</button>
	<?php endif; ?>

</div>
