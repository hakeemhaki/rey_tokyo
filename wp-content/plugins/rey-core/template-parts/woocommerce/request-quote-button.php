<?php
defined( 'ABSPATH' ) || exit;

$btn_classes = [
	'btn',
	'rey-requestQuote-btn',
	esc_attr( get_theme_mod('request_quote__btn_style', 'btn-line-active') )
];
?>

<div class="rey-requestQuote-wrapper">

	<a href="#" class=" <?php echo implode(' ', $btn_classes) ?> js-requestQuote" data-id="<?php echo get_the_ID() ?>">
		<?php echo $args['button_text']; ?>
	</a>

	<?php if( $after_text = get_theme_mod('request_quote__btn_text_after', '') ): ?>
		<div class="rey-requestQuote-text"><?php echo reycore__parse_text_editor($after_text); ?></div>
	<?php endif; ?>

</div>
