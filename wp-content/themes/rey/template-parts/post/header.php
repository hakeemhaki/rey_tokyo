<header class="rey-postHeader">
	<?php
	rey__categoriesList();

	if ( is_singular() ) :
		/**
		 * Displays title
		 */
		do_action('rey/content/title');
	else :
		the_title(
			sprintf( '<h2 class="rey-postTitle %s"><a href="%s" rel="bookmark"><span class="rey-hvLine">',
				get_theme_mod('blog_title_animation', true) ? 'rey-hvLine-parent' : '',
				esc_url( get_permalink() )
			),
			'</span></a></h2>'
		);
	endif;
	?>

	<div class="rey-postInfo">
		<?php
			rey__posted_by();
			rey__posted_date();
			rey__comment_count();
			rey__edit_link();
			echo rey__postDuration(true);
		?>
	</div>

</header><!-- .rey-postHeader -->
