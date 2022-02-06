<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

printf(
	'<div class="rey-buyNowBtn-wrapper"><button class="%1$s rey-buyNowBtn" title="%2$s" aria-label="%2$s" %4$s>%3$s</button></div>',
	esc_attr(implode(' ', $args['classes'])),
	$args['text'],
	$args['content'],
	reycore__implode_html_attributes($args['attributes'])
);
