<?php
// Post Gallery

$gallery_ids = get_post_gallery( get_the_ID(), false );

if ( $gallery_ids ) :
	if( isset( $gallery_ids['ids'] ) ):
		$gallery_html = '';
		foreach(explode(',', $gallery_ids['ids']) as $i => $gitem):

			if( $i > ( apply_filters('rey/post/gallery_limit', 4) - 1 ) ) continue; // allow 3 only
			$gallery_html .= '<div class="rey-slickCarousel__item">';
			$gallery_html .= wp_get_attachment_image( $gitem, 'rey-standard-large', false, ['class'=>"rey-slickCarousel__img"] );
			$gallery_html .= '</div>';
		endforeach;

	if( $gallery_html ):

		reyAssets()->add_scripts('slick');
		reyAssets()->add_styles('rey-slick');

		?>
		<div class="rey-postMedia">
			<div class="rey-slick rey-slickCarousel" data-slick='{"slidesToShow": 1, "slidesToScroll": 1, "adaptiveHeight": true, "arrows": false, "dots": true, "fade": true}'>
			<?php
				echo wp_kses_post($gallery_html);
			?>
			</div>
			<?php rey__categoriesList(); ?>
		</div>
	<?php endif;
	endif;
	?>
<?php
endif; ?>
