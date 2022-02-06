<?php
if( is_singular() ): ?>
<footer class="rey-postFooter js-check-empty">
	<?php
		rey__tagList();

		do_action('rey/single_post/footer');
	?>
</footer><!-- .rey-postFooter -->
<?php endif; ?>
