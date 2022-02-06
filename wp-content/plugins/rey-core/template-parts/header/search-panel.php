<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<div class="rey-searchPanel rey-searchForm rey-searchAjax js-rey-ajaxSearch" data-style="<?php echo esc_attr( $args['search_style'] ) ?>">

	<button class="btn rey-searchPanel-close js-rey-searchPanel-close" aria-label="<?php esc_html_e('Search close', 'rey-core') ?>">
		<?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?>
	</button>
	<!-- .rey-searchPanel-close -->

	<div class="rey-searchPanel-inner">

		<form role="search" action="<?php echo esc_url(home_url('/')) ?>" method="get">
			<?php
			$id = uniqid('search-input-');
			echo apply_filters(
				'rey/search_form/title',
				sprintf(
					'<label for="%3$s">%1$s %2$s</label>',
					esc_html__('Search', 'rey-core'),
					str_replace(['http://', 'https://'], '', get_site_url()),
					esc_attr($id)
				),
				$id
			); ?>
			<input type="search" name="s" placeholder="<?php echo esc_attr( get_theme_mod('header_search__input_placeholder', __( 'type to search..', 'rey-core' )) ); ?>" id="<?php echo esc_attr($id) ?>" />
			<?php do_action('rey/search_form'); ?>
			<?php do_action('wpml_add_language_form_field'); ?>
		</form>

		<?php do_action('reycore/search_panel/after_search_form', $args); ?>
		<!-- .row -->
	</div>
</div>
<!-- .rey-searchPanel -->
