<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<nav class="rey-searchPanel__suggestions" aria-label="<?php echo esc_attr__( 'Search Suggestions', 'rey-core' ); ?>">
	<h4><?php echo esc_html_x('TRENDING', 'Search keywords title', 'rey-core');?></h4>
	<?php
	if( !empty($args['keywords']) ):
		$keywords_arr = array_map( 'trim', explode( ',', $args['keywords'] ) );
		foreach ($keywords_arr as $kwd):
			printf('<button aria-label="' . esc_html_x('Search for %1$s', 'Suggestion button', 'rey-core') . '">%1$s</button>', esc_html( $kwd ));
		endforeach;
	endif;
	?>
</nav>
