<?php


if(!function_exists('rey__svg_sprite_path')):
	/**
	 * Retrieve Rey Icon Sprite Path
	 *
	 * @since 1.0.0
	 **/
	function rey__svg_sprite_path()
	{
		return apply_filters('rey/theme/svg_path', REY_THEME_URI .'/assets/images/icons/icon-sprite.svg');
	}
endif;


if(!function_exists('rey__get_svg_icon')):
	/**
	 * Get embed SVG icon from sprite
	 *
	 * @since 1.0.0
	 */
	function rey__get_svg_icon( $args = [] )
	{
		// Make sure $args are an array.
		if ( !is_array($args) && empty( $args ) ) {
			return esc_html__( 'Please define default parameters in the form of an array.', 'rey' );
		}

		// Define an icon.
		if ( false === array_key_exists( 'id', $args ) ) {
			return esc_html__( 'Id Missing.', 'rey' );
		}

		// Set defaults.
		$defaults = [
			'id' => '',
			'title' => '',
			'style' => '',
			'class' => '',
			'link' => '',
			'target' => '_self',
			'color' => '',
			'hover_color' => '',
			'version' => REY_THEME_VERSION,
		];

		// Parse args.
		$args = wp_parse_args( $args, $defaults );

		// Set aria hidden.
		$attributes['aria-hidden'] = 'aria-hidden="true"';

		// Set ARIA.
		if ( $args['title'] ) {
			$attributes['aria-hidden'] = '';
			$unique_id = uniqid();
			$attributes['aria-labelledby'] = 'aria-labelledby="title-' . $unique_id . '"';
		}

		if ( $args['style'] ) {
			$attributes['style'] = 'style="' . esc_attr($args['style']) . '"';
		}

		$attributes['role'] = 'role="img"';

		$svg = '';
		$svgColorLink = '';

		if( $args['link'] ) {
			// add custom mouse attributes so we can handle hover
			$svgColorLink = 'onmouseover="this.style.color=\''. esc_attr($args['hover_color']) .'\'" onmouseout="this.style.color=\''. esc_attr($args['color']) .'\'"';
			$svg .= '<a class="rey-iconLink" href="'.esc_url($args['link']).'" target="'.esc_attr($args['target']).'">';
		}

		// Begin SVG markup.
		$svg .= '<svg class="rey-icon rey-icon-' . esc_attr( $args['id'] ) . ' ' . esc_attr( $args['class'] ) . '" ' . implode( ' ', $attributes ) . ' '. $svgColorLink .'>';

		// Display the title.
		if ( $args['title'] ) {
			$svg .= '<title id="title-' . $unique_id . '">' . esc_html( $args['title'] ) . '</title>';
		}

		$sprite_path = rey__svg_sprite_path();

		if( isset($args['sprite_path']) ){
			$sprite_path = $args['sprite_path'];
		}

		$symbol = '#';

		if( apply_filters('rey/svg_icon/append_version', false) ){
			$symbol = '?' . $args['version'] . '#';
		}

		$svg .= ' <use href="'. esc_url($sprite_path) . $symbol . esc_html( $args['id'] ) . '" xlink:href="'. esc_url($sprite_path) . $symbol . esc_html( $args['id'] ) . '"></use> ';

		$svg .= '</svg>';

		if( $args['link'] ) {
			$svg .= '</a>';
		}

		return apply_filters('rey/svg_icon', $svg, $args);
	}
endif;

if(!function_exists('rey__echo_svg_icon')):
	/**
	 * Echo get icon
	 *
	 * @since 1.0.0
	 */
	function rey__echo_svg_icon( $args = [] )
	{
		echo rey__get_svg_icon( $args );
	}
endif;

if ( ! function_exists( 'rey__arrowSvgMarkup' ) ) :
	/**
	 * Arrow SVG.
	 * @since 1.0.0
	 */
	function rey__arrowSvgMarkup(){

		$svg = apply_filters( 'rey/svg_arrow_markup',
			'<svg width="50px" height="8px" viewBox="0 0 50 8" version="1.1" class="--default" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M0.928904706,3.0387609 L44.0113745,3.0387609 L44.0113745,4.97541883 L0.928904706,4.97541883 C0.415884803,4.97541883 2.13162821e-14,4.54188318 2.13162821e-14,4.00708986 C2.13162821e-14,3.47229655 0.415884803,3.0387609 0.928904706,3.0387609 Z" class="rey-arrowSvg-dash"></path><path d="M49.6399545,3.16320794 L45.1502484,0.129110528 C45.0056033,0.0532149593 44.8474869,0.0092610397 44.685796,3.99680289e-14 C44.5479741,0.0112891909 44.4144881,0.0554642381 44.2956561,0.129110528 C44.0242223,0.2506013 43.8503957,0.531340097 43.8559745,0.839218433 L43.8559745,6.90741326 C43.8503957,7.21529159 44.0242223,7.49603039 44.2956561,7.61752116 C44.5594727,7.77895738 44.8864318,7.77895738 45.1502484,7.61752116 L49.6399545,4.58342375 C49.8682741,4.42554586 50.0055358,4.15892769 50.0055358,3.87331584 C50.0055358,3.587704 49.8682741,3.32108583 49.6399545,3.16320794 Z"></path></svg>');

		return '<div class="rey-arrowSvg rey-arrowSvg--%1$s %2$s" aria-label="%1$s" %3$s>' . $svg . '</div>';

	}
endif;

if ( ! function_exists( 'rey__arrowSvg' ) ) :
	/**
	 * Arrow SVG.
	 * @since 1.0.0
	 */
	function rey__arrowSvg($args = []){

		$args = wp_parse_args($args, [
			'right' => true,
			'class' => '',
			'attributes' => '',
		]);

		return sprintf(
			rey__arrowSvgMarkup(),
			($args['right'] ? 'right' : 'left'),
			$args['class'],
			$args['attributes']
		);
	}
endif;

add_action('wp_footer', function(){

	// if( ! apply_filters( 'rey/svg_arrow_template', false ) ){
	// 	return;
	// }

	$arrow_markup = sprintf( rey__arrowSvgMarkup(), '{{{data.direction}}}', false, false );

	echo "<script type='text/html' id='tmpl-reyArrowSvg'>{$arrow_markup}</script>";
});

if(!function_exists('rey__kses_post_with_svg')):
	/**
	 * Add SVG support to wp_kses
	 * @since 1.0.0
	 */
	function rey__kses_post_with_svg( $html = '' )
	{
		if( $html === '' ){
			return;
		}

		$kses_defaults = wp_kses_allowed_html( 'post' );

		$svg_args = [
			'svg'   => [
				'class' => true,
				'aria-hidden' => true,
				'aria-labelledby' => true,
				'role' => true,
				'xmlns' => true,
				'width' => true,
				'height' => true,
				'viewbox' => true, // <= Must be lower case!
			],
			'g'     => [ 'fill' => true ],
			'use'   => [ 'href' => true, 'xlink:href' => true ],
			'title' => [ 'title' => true ],
			'path'  => [ 'd' => true, 'fill' => true,  ],
		];

		$allowed_tags = array_merge( $kses_defaults, $svg_args );

		return wp_kses( $html, $allowed_tags );
	}
endif;
