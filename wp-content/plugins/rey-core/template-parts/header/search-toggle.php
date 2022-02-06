<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args();
$from_sticky = get_query_var('rey__is_sticky', false);

$classes = [];

if( isset($args['classes']) ){
	$classes[] = $args['classes'];
} ?>

<div class="rey-headerSearch rey-headerIcon js-rey-headerSearch <?php echo implode(' ', $classes); ?>">

	<button class="btn rey-headerSearch-toggle js-rey-headerSearch-toggle <?php echo esc_attr( $from_sticky === true ? 'js-from-sticky' : '' ) ?>" aria-label="<?php esc_html_e('Search open', 'rey-core') ?>">

		<?php
		$text = '';

		// legacy
		if( get_theme_mod('header_search_text_enable', false) ){
			$text = esc_html__('Search', 'rey-core');
		}

		if( $content_before = $args['search__before_content'] ){
			$text = $content_before;
		}

		if(!empty($text)){
			echo '<span class="rey-headerSearch-text">' . $text . '</span>';
		} ?>

		<?php
		if( $icon = apply_filters('reycore/woocommerce/header/search_icon', reycore__get_svg_icon([ 'id'=> 'rey-icon-search', 'class' => 'icon-search' ]) ) ) {
			printf('<span class="__icon">%s %s</span>', $icon, reycore__get_svg_icon(['id' => 'rey-icon-close', 'class' => 'icon-close']));
		} ?>
	</button>
	<!-- .rey-headerSearch-toggle -->

</div>
