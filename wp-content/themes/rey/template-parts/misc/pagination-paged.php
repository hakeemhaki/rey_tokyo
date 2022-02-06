<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pagination_args = apply_filters( 'rey/blog_pagination_args', [
	'base'            => get_pagenum_link(1) . '%_%',
	'show_all'        => false,
	'end_size'        => 1,
	'prev_next'       => true,
	'prev_text'       => rey__arrowSvg(false),
	'next_text'       => rey__arrowSvg(),
	'type'            => 'plain',
	'add_args'        => false,
	'add_fragment'    => ''
] );

if ( $paginate_links = paginate_links($pagination_args) ) {
	printf( '<nav class="rey-pagination">%s</nav>', $paginate_links);
}
