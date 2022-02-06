<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<?php if ( $args['search_menu_source'] ) : ?>
	<nav class="rey-searchPanel__qlinks" aria-label="<?php echo esc_attr__( 'Search Menu', 'rey-core' ); ?>">
		<?php
		$search_menu_object = wp_get_nav_menu_object( $args['search_menu_source'] );
		if( isset($search_menu_object->name) ){
			echo '<h4>'. esc_html( $search_menu_object->name ) .'</h4>';
		}
		reyCoreHelper()->wp_nav_menu([
			'menu'       => $args['search_menu_source'],
			'menu_class' => 'rey-searchMenu list-unstyled',
			'container'  => '',
			'depth'      => 1,
			'items_wrap' => '<ul id = "%1$s" class = "%2$s">%3$s</ul>',
			'fallback_cb' 	=> '__return_empty_string',
		]);
		?>
	</nav><!-- .rey-searchPanel__qlinks -->
<?php endif; ?>
