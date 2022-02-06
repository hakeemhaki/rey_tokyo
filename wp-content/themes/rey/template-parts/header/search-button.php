<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="rey-headerSearch rey-headerSearch--form rey-headerIcon">

	<button class="btn js-rey-headerSearch-form" aria-label="<?php esc_html_e('Search', 'rey') ?>">
		<?php echo rey__get_svg_icon(['id' => 'rey-icon-search', 'class' => 'icon-search']) ?>
		<?php echo rey__get_svg_icon(['id' => 'rey-icon-close', 'class' => 'icon-close']) ?>
	</button>

	<?php get_search_form() ?>
</div>
