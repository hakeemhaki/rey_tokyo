<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require REY_CORE_DIR . 'inc/includes/tag-blog-teasers.php';

if(!function_exists('reycore__texts')):
	/**
	 * Text strings
	 *
	 * @since 1.6.6
	 **/
	function reycore__texts( $text = '' )
	{
		$texts = apply_filters('reycore/texts', [
			'qty' => esc_attr_x( 'Qty', 'Product quantity input tooltip', 'rey-core' ),
			'cannot_update_cart' => esc_html__('Couldn\'t update cart!', 'rey-core'),
			'added_to_cart_text' => esc_html__('ADDED TO CART', 'rey-core'),
		]);

		if( !empty($text) && isset($texts[$text]) ){
			return $texts[$text];
		}
	}
endif;

if(!function_exists('reycore__get_svg_icon')):
	/**
	 * Wrapper for Rey Theme's rey__get_svg_icon()
	 *
	 * @since 1.0.0
	 */
	function reycore__get_svg_icon( $args = [] ) {
		if( function_exists('rey__get_svg_icon') ){
			return rey__get_svg_icon( $args );
		}
		return false;
	}
endif;

if(!function_exists('reycore__social_icons_sprite_path')):
	/**
	 * Retrieve social icon sprite path
	 *
	 * @since 1.3.7
	 **/
	function reycore__social_icons_sprite_path()
	{
		return REY_CORE_URI . 'assets/images/social-icons-sprite.svg';
	}
endif;


if(!function_exists('reycore__get_svg_social_icon')):
	/**
	 * Wrapper for Rey Theme's rey__get_svg_icon()
	 * with the addition of the social icon sprite.
	 *
	 * @since 1.0.0
	 */
	function reycore__get_svg_social_icon( $args = [] ) {
		if( function_exists('rey__get_svg_icon') ){
			$args['sprite_path'] = reycore__social_icons_sprite_path();
			return rey__get_svg_icon( $args );
		}
		return false;
	}
endif;


if(!function_exists('reycore__icons_sprite_path')):
	/**
	 * Retrieve icon sprite path
	 *
	 * @since 1.3.7
	 **/
	function reycore__icons_sprite_path()
	{
		return REY_CORE_URI . 'assets/images/icon-sprite.svg';
	}
endif;


if(!function_exists('reycore__get_svg_icon__core')):
	/**
	 * Wrapper for Rey Theme's rey__get_svg_icon()
	 * with the addition of the social icon sprite.
	 *
	 * @since 1.0.0
	 */
	function reycore__get_svg_icon__core( $args = [] ) {
		if( function_exists('rey__get_svg_icon') ){
			$args['sprite_path'] = reycore__icons_sprite_path();
			$args['version'] = REY_CORE_VERSION;
			return rey__get_svg_icon( $args );
		}
		return false;
	}
endif;


if(!function_exists('reycore__social_sharing_icons_list')):
	/**
	 * Social Icons List
	 *
	 * @helper https://gist.github.com/HoldOffHunger/1998b92acb80bc83547baeaff68aaaf4
	 *
	 * @since 1.3.0
	 **/
	function reycore__social_sharing_icons_list()
	{
		return apply_filters('reycore/social_sharing', [
			'digg' => [
				'title' => esc_html__('Digg', 'rey-core'),
				'url' => 'http://digg.com/submit?url={url}',
				'icon' => 'digg',
				'color' => '005be2'
			],
			'mail' => [
				'title' => esc_html__('Mail', 'rey-core'),
				'url' => 'mailto:?body={url}',
				'icon' => 'envelope',
				// 'color' => ''
			],
			'facebook' => [
				'title' => esc_html__('FaceBook', 'rey-core'),
				'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
				'icon' => 'facebook',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'facebook-share',
					'size' => 'width=580,height=296'
				])),
				'color' => '#1877f2'
			],
			'facebook-f' => [
				'title' => esc_html__('Facebook', 'rey-core'),
				'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
				'icon' => 'facebook-f',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'facebook-share',
					'size' => 'width=580,height=296'
				])),
				'color' => '#1877f2'
			],
			'linkedin' => [
				'title' => esc_html__('LinkedIn', 'rey-core'),
				'url' => 'http://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}',
				'icon' => 'linkedin',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'linkedin-share',
					'size' => 'width=930,height=720'
				])),
				'color' => '#007bb5'
			],
			'pinterest' => [
				'title' => esc_html__('Pinterest', 'rey-core'),
				'url' => 'http://pinterest.com/pin/create/button/?url={url}&description={title}',
				'icon' => 'pinterest',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'pinterest-share',
					'size' => 'width=490,height=530'
				])),
				'color' => '#e82b2d'
			],
			'pinterest-p' => [
				'title' => esc_html__('Pinterest P', 'rey-core'),
				'url' => 'http://pinterest.com/pin/create/button/?url={url}&description={title}',
				'icon' => 'pinterest-p',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'pinterest-share',
					'size' => 'width=490,height=530'
				])),
				'color' => '#e82b2d'
			],
			'reddit' => [
				'title' => esc_html__('Reddit', 'rey-core'),
				'url' => 'https://reddit.com/submit?url={url}&title={title}',
				'icon' => 'reddit',
				'color' => '#ff4500'
			],
			'skype' => [
				'title' => esc_html__('Skype', 'rey-core'),
				'url' => 'https://web.skype.com/share?url={url}&text={text}',
				'icon' => 'skype',
				'color' => '#00aff0'
			],
			'tumblr' => [
				'title' => esc_html__('Tumblr', 'rey-core'),
				'url' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={url}&title={title}',
				'icon' => 'tumblr',
				'color' => '#35465d'
			],
			'twitter' => [
				'title' => esc_html__('Twitter', 'rey-core'),
				'url' => 'http://twitter.com/share?text={title}&url={url}',
				'icon' => 'twitter',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'twitter-share',
					'size' => 'width=550,height=235'
				])),
				'color' => '#1da1f2'
			],
			'vk' => [
				'title' => esc_html__('VK', 'rey-core'),
				'url' => 'http://vk.com/share.php?url={url}&title={title}',
				'icon' => 'vk',
				'color' => '#4a76a8'
			],
			'weibo' => [
				'title' => esc_html__('Weibo', 'rey-core'),
				'url' => 'http://service.weibo.com/share/share.php?url={url}&appkey=&title={title}&pic=&ralateUid=',
				'icon' => 'weibo',
				'color' => '#df2029'
			],
			'whatsapp' => [
				'title' => esc_html__('WhatsApp', 'rey-core'),
				'url' => 'https://wa.me/?text={title}+{url}',
				'icon' => 'whatsapp',
				'color' => '#25d366'
			],
			'xing' => [
				'title' => esc_html__('Xing', 'rey-core'),
				'url' => 'https://www.xing.com/spi/shares/new?url={url}',
				'icon' => 'xing',
				'color' => '#026466'
			],
			'copy' => [
				'title' => esc_html__('Copy URL', 'rey-core'),
				'url' => '#',
				'icon' => 'link',
				'url_attributes' => 'data-url="{url}" class="js-copy-url u-copy-url"',
				'color' => '#a3a7ab'
			],
			'print' => [
				'title' => esc_html__('Print URL', 'rey-core'),
				'url' => '#',
				'icon' => 'print',
				'url_attributes' => 'class="js-print-url"',
				'color' => '#a3a7ab'
			],
		] );
	}
endif;


if ( ! function_exists( 'reycore__socialShare' ) ) :
	/**
	 * Prints HTML with social sharing.
	 * @since 1.0.0
	 */
	function reycore__socialShare( $args = [])
	{
		$title = urlencode( html_entity_decode( get_the_title(), ENT_COMPAT, 'UTF-8') );
		$url = esc_url( get_the_permalink() );

		$defaults = [
			'share_items' => apply_filters('reycore/post/social_share', [ 'twitter', 'facebook', 'linkedin', 'pinterest', 'mail' ], $title, $url),
			'class' => '',
			'colored' => false
		];

		$args = wp_parse_args( $args, $defaults );

		$classes = esc_attr($args['class']);

		if( $args['colored'] ){
			$classes .= ' --colored';
		}

		if( is_array($args['share_items']) && !empty($args['share_items']) ): ?>
			<ul class="rey-postSocialShare <?php echo $classes; ?>">
				<?php

				$all_icons = reycore__social_sharing_icons_list();

				foreach($args['share_items'] as $item):
					echo '<li class="rey-shareItem--'. $item .'">';

					if( isset($all_icons[$item]) ){

						$cleanup = function($string) use ($url, $title) {
							$cleaned_up = str_replace('{url}', $url, $string);
							$cleaned_up = str_replace('{title}', $title, $cleaned_up);
							return $cleaned_up;
						};

						$attributes = isset($all_icons[$item]['url_attributes']) ? $cleanup($all_icons[$item]['url_attributes']) : '';

						if( $args['colored'] && isset($all_icons[$item]['color']) ){
							$attributes .= sprintf(' style="background-color: %s;"', $all_icons[$item]['color']);
						}

						printf( '<a href="%1$s" %2$s title="%3$s" rel="noreferrer" target="%5$s">%4$s</a>',
							$cleanup( $all_icons[$item]['url'] ),
							$attributes,
							esc_attr(get_the_title()),
							reycore__get_svg_social_icon( ['id' => $all_icons[$item]['icon']] ),
							apply_filters('reycore/social_sharing/target', '_blank', $item)
						);
					}

					echo '</li>';
				endforeach;
				?>
			</ul>
			<!-- .rey-postSocialShare -->
		<?php

		reyCoreAssets()->add_styles('reycore-post-social-share');

		endif;
	}
endif;


if(!function_exists('reycore__social_icons_list')):
	/**
	 * Social Icons List
	 *
	 * @since 1.0.0
	 **/
	function reycore__social_icons_list()
	{
		return [
			'android',
			'apple',
			'behance',
			'bitbucket',
			'codepen',
			'delicious',
			'deviantart',
			'digg',
			'discord',
			'dribbble',
			'envelope',
			'facebook',
			'facebook-f',
			'flickr',
			'foursquare',
			'free-code-camp',
			'github',
			'gitlab',
			'globe',
			'google-plus',
			'houzz',
			'instagram',
			'jsfiddle',
			'link',
			'linkedin',
			'medium',
			'meetup',
			'mixcloud',
			'odnoklassniki',
			'patreon',
			'pinterest',
			'pinterest-p',
			'product-hunt',
			'reddit',
			'rss',
			'shopping-cart',
			'skype',
			'slideshare',
			'snapchat',
			'soundcloud',
			'spotify',
			'stack-overflow',
			'steam',
			'stumbleupon',
			'telegram',
			'thumb-tack',
			'tiktok',
			'tripadvisor',
			'tumblr',
			'twitch',
			'twitter',
			'viber',
			'vimeo',
			'vimeo-v',
			'vk',
			'weibo',
			'weixin',
			'whatsapp',
			'wordpress',
			'xing',
			'yelp',
			'youtube',
			'500px',
		];
	}
endif;


if(!function_exists('reycore__social_icons_list_select2')):
	/**
	 * Social Icons List for a select list
	 *
	 * @since 1.0.0
	 **/
	function reycore__social_icons_list_select2( $type = 'social' )
	{
		$new_list = [];

		if( $type === 'social' ){
			$list = reycore__social_icons_list();

			foreach( $list as $v ){
				$new_list[$v] = ucwords(str_replace('-',' ', $v));
			}
		}
		elseif( $type === 'share' ){
			$list = reycore__social_sharing_icons_list();

			foreach( $list as $k => $v ){
				$new_list[$k] = $v['title'];
			}
		}

		return $new_list;
	}
endif;


if(!function_exists('reycore__get_page_title')):
	/**
	 * Get the page title
	 *
	 * @since 1.0.0
	 */
	function reycore__get_page_title() {
		$title = '';

		if ( class_exists('WooCommerce') && is_shop() ) {

			$shop_page_id = wc_get_page_id( 'shop' );
			$page_title   = get_the_title( $shop_page_id );
			$title = apply_filters( 'woocommerce_page_title', $page_title );
		}
		elseif ( is_home() ) {
			$title = get_the_title( get_option( 'page_for_posts' ) );
		}
		elseif ( is_singular() ) {
			$title = get_the_title();
		} elseif ( is_search() ) {
			/* translators: %s: Search term. */
			$title = sprintf( __( 'Search Results for: %s', 'rey-core' ), get_search_query() );
			// show page
			if ( get_query_var( 'paged' ) ) {
				/* translators: %s is the page number. */
				$title .= sprintf( __( '&nbsp;&ndash; Page %s', 'rey-core' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'rey-core' ) );
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'rey-core' ) );
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'rey-core' ) );
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title', 'rey-core' );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( is_404() ) {
			$title = __( 'Page Not Found', 'rey-core' );
		}

		$title = apply_filters( 'reycore/tags/get_the_title', $title );

		return $title;
	}
endif;



if(!function_exists('reycore__get_video_html')):
	/**
	 * Get HTML5 video markup
	 *
	 * @since 1.0.0
	 */
	function reycore__get_video_html( $args = [] ){

		$defaults = [
			'video_url' => '',
			'class' => '',
			'params' => [
				'class'=>'rey-hostedVideo-inner elementor-background-video-hosted elementor-html5-video',
				'loop' => 'loop',
				'muted'=>'muted',
				'autoplay'=>'autoplay',
				// 'preload'=>'metadata',
			],
			'start' => 0,
			'end' => 0,
			'mobile' => false,
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_url']) ){
			return;
		}

		$args['params']['src'] = esc_attr($args['video_url']);

		if( $args['start'] || $args['end'] ){
			$args['params']['src'] = sprintf( '%s#t=%s%s',
				$args['params']['src'],
				$args['start'] ? $args['start'] : 0,
				$args['end'] ? ',' . $args['end'] : ''
			);
		}

		if( !$args['mobile'] ){
			$args['class'] .= ' elementor-hidden-phone';
		}
		else {
			$args['params']['playsinline'] = 'playsinline';
		}

		reyCoreAssets()->add_styles('reycore-videos');

		return sprintf(
			'<div class="rey-hostedVideo %s" data-video-params=\'%s\'></div>',
				esc_attr($args['class']),
				wp_json_encode($args['params'])
		);
	}
endif;

if(!function_exists('reycore__get_youtube_iframe_html')):
	/**
	 * Get YouTube video iframe HTML
	 *
	 * @since 1.0.0
	 */
	function reycore__get_youtube_iframe_html( $args = [] ){

		$defaults            =  [
			'video_id'          => '',
			'video_url'         => '',
			'class'             => '',
			'html_id'           => '',
			'add_preview_image' => false,
			'preview_inside' 	=> false,
			'mobile'            => false,
			'params'            => [
				'enablejsapi'      => 1,
				'rel'              => 0,
				'showinfo'         => 0,
				'controls'         => 0,
				'autoplay'         => 1,
				'disablekb'        => 1,
				'mute'             => 1,
				'fs'               => 0,
				'iv_load_policy'   => 3,
				'loop'             => 1,
				'modestbranding'   => 1,
				'start'            => 0,
				'end'              => 0,
			]
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_id']) && !empty($args['video_url']) ){
			$args['video_id'] = reycore__extract_youtube_id( $args['video_url'] );
			$args['params']['start'] = reycore__extract_youtube_start( $args['video_url'] );
		}

		if( empty($args['video_id']) ){
			return false;
		}

		$preview = '';

		if( $args['add_preview_image'] ){
			$preview = reycore__get_youtube_preview_image_html([
				'video_id' => $args['video_id'],
				'class' => $args['class'],
			]);
		}

		if( !$args['mobile'] ){
			$args['class'] .= ' elementor-hidden-phone';
		}
		else {
			$args['params']['playsinline'] = 1;
		}

		$preview_inside = '';
		$preview_outside = $preview;

		if( $args['preview_inside'] ){
			$preview_inside = $preview;
			$preview_outside = '';
			$args['class'] .= ' --preview-inside';
		}

		reyCoreAssets()->add_styles('reycore-videos');

		return sprintf(
			'<div class="rey-youtubeVideo %1$s" data-video-params=\'%2$s\' data-video-id="%3$s"><div class="rey-youtubeVideo-inner elementor-background-video-embed" id="%4$s" ></div>%5$s</div>%6$s',
				esc_attr($args['class']),
				wp_json_encode($args['params']),
				esc_attr($args['video_id']),
				esc_attr($args['html_id']),
				$preview_inside,
				$preview_outside
		);
	}
endif;

if(!function_exists('reycore__get_youtube_preview_image_html')):
	/**
	 * Get YouTube video preview image HTML
	 *
	 * @since 1.0.0
	 */
	function reycore__get_youtube_preview_image_html( $args = [] ){

		$defaults = [
			'video_id' => '',
			'class' => '',
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_id']) ){
			return;
		}

		return sprintf(
			'<div class="rey-youtubePreview %2$s"><img src="//img.youtube.com/vi/%1$s/maxresdefault.jpg" data-default-src="//img.youtube.com/vi/%1$s/hqdefault.jpg" alt="" /></div>',
			esc_attr($args['video_id']),
			esc_attr($args['class'])
		);
	}
endif;


if(!function_exists('reycore__extract_youtube_id')):
	/**
	 * Extract Youtube ID from URL
	 *
	 * @since 1.0.0
	 **/
	function reycore__extract_youtube_id( $url )
	{
		// Here is a sample of the URLs this regex matches: (there can be more content after the given URL that will be ignored)
		// http://youtu.be/dQw4w9WgXcQ
		// http://www.youtube.com/embed/dQw4w9WgXcQ
		// http://www.youtube.com/watch?v=dQw4w9WgXcQ
		// http://www.youtube.com/?v=dQw4w9WgXcQ
		// http://www.youtube.com/v/dQw4w9WgXcQ
		// http://www.youtube.com/e/dQw4w9WgXcQ
		// http://www.youtube.com/user/username#p/u/11/dQw4w9WgXcQ
		// http://www.youtube.com/sandalsResorts#p/c/54B8C800269D7C1B/0/dQw4w9WgXcQ
		// http://www.youtube.com/watch?feature=player_embedded&v=dQw4w9WgXcQ
		// http://www.youtube.com/?feature=player_embedded&v=dQw4w9WgXcQ
		// It also works on the youtube-nocookie.com URL with the same above options.
		// It will also pull the ID from the URL in an embed code (both iframe and object tags)
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);

		if( isset($match[1]) && $youtube_id = $match[1] ){
			return $youtube_id;
		}

		return false;
	}
endif;


if(!function_exists('reycore__extract_youtube_start')):
	/**
	 * Extract Youtube start
	 *
	 * @since 1.0.0
	 **/
	function reycore__extract_youtube_start( $url )
	{
		parse_str($url, $query);

		if( isset($query['t']) && $start = absint($query['t']) ){
			return $start;
		}

		return 0;
	}
endif;


if(!function_exists('reycore__get_next_posts_url')):
	/**
	 * Retrieves the next posts page link.
	 * based on `get_next_posts_link`
	 *
	 * @since 1.0.0
	 *
	 * @global int      $paged
	 * @global WP_Query $wp_query
	 *
	 * @param int    $max_page Optional. Max pages. Default 0.
	 * @return string|void next posts url.
	 */
	function reycore__get_next_posts_url( $max_page = 0 ) {
		global $paged, $wp_query;

		if ( ! $max_page ) {
			$max_page = $wp_query->max_num_pages;
		}

		if ( ! $paged ) {
			$paged = 1;
		}


		$nextpage = intval( $paged ) + 1;

		if ( ! is_single() && ( $nextpage <= $max_page ) ) {
			return next_posts( $max_page, false );
		}
	}
endif;


if(!function_exists('reycore__ajax_load_more_pagination')):
	/**
	 * Show ajax load more pagination markup
	 *
	 * @since 1.0.0
	 **/
	function reycore__ajax_load_more_pagination( $args = [] )
	{
		reyCoreAssets()->add_scripts(['scroll-out', 'reycore-load-more', 'reycore-wc-loop-count-loadmore']);
		reyCoreAssets()->add_styles('reycore-ajax-load-more');

		$pagination_args = apply_filters('reycore/load_more_pagination_args', wp_parse_args( $args, [
			'url'          => reycore__get_next_posts_url(),
			'class'        => 'btn btn-line-active',
			'post_type'    => get_post_type(),
			'target'       => 'ul.products',
			'text'         => esc_html__('SHOW MORE', 'rey-core'),
			'end_text'     => esc_html__('END', 'rey-core'),
			'ajax_counter' => get_theme_mod('loop_pagination_ajax_counter', false),
		]));

		if( $pagination_args['url'] ){

			$attributes = [];

			$attributes['data-post-type'] = esc_attr( $pagination_args['post_type'] );
			$attributes['data-target'] = esc_attr( $pagination_args['target'] );
			$attributes['data-text'] = _x($pagination_args['text'], 'Ajax load more posts or products button text.', 'rey-core');
			$attributes['data-end-text'] = _x($pagination_args['end_text'], 'Ajax load more end text.', 'rey-core');

			$attributes['href'] = esc_url( $pagination_args['url']);
			$attributes['class'] = 'rey-ajaxLoadMore-btn ' . esc_attr( $pagination_args['class']);

			printf( '<nav class="rey-ajaxLoadMore"><a %s></a><div class="rey-lineLoader"></div></nav>',
				reycore__implode_html_attributes( apply_filters('reycore/load_more_pagination/output_attributes', $attributes, $pagination_args) )
			);
		}
	}
endif;

if(!function_exists('reycore__remove_paged_pagination')):
	/**
	 * Remove default pagination in blog
	 *
	 * @since 1.0.0
	 */
	function reycore__remove_paged_pagination() {
		if( get_theme_mod('blog_pagination', 'paged') !== 'paged' ){
			remove_action('rey/post_list', 'rey__pagination', 50);
		}
	}
endif;
add_action('wp', 'reycore__remove_paged_pagination');


if(!function_exists('reycore__pagination')):
	/**
	 * Wrapper for wp pagination
	 *
	 * @since 1.0.0
	 */
	function reycore__pagination() {
		if( ($blog_pagination = get_theme_mod('blog_pagination', 'paged')) && $blog_pagination !== 'paged' ){
			reycore__get_template_part( 'template-parts/misc/pagination-' . $blog_pagination );
		}
	}
endif;
add_action('rey/post_list', 'reycore__pagination', 50);


if(!function_exists('reycore__get_post_term_thumbnail')):
/**
 * Extract Thumbnail ID & URL from Post or WooCOmmerce Term
 *
 * @since 1.3.0
 **/
function reycore__get_post_term_thumbnail()
{
	if( class_exists('WooCommerce') && is_tax() ){
		$term = get_queried_object();
		$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		return [
			'id' => $thumb_id,
			'url' => wp_get_attachment_url(  $thumb_id )
		];
	}
	elseif( is_singular() ){
		return [
			'id' => get_post_thumbnail_id(),
			'url' => get_the_post_thumbnail_url()
		];
	}
}
endif;

if(!function_exists('reycore__single_post_add_share_buttons')):
	/**
	 * Adds social sharing icons in single post footer
	 *
	 * @since 1.0.0
	 */
	function reycore__single_post_add_share_buttons(){

		if( ! get_theme_mod('post_share', true) ) {
			return;
		}

		$classes = ['text-center', 'text-sm-right'];

		$style = get_theme_mod('post_share_style', '');
		$is_colored = $style === '' || $style === 'round_c';

		if( $style ){
			$classes[] = '--' . $style;
		}

		reycore__socialShare([
			'class' => implode(' ', $classes),
			'colored' => $is_colored,
			'share_items' => get_theme_mod('post_share_icons_list', ['facebook-f', 'twitter', 'linkedin', 'pinterest-p', 'mail'])
		]);

	}
endif;
add_action('rey/single_post/footer', 'reycore__single_post_add_share_buttons' );

if(!function_exists('reycore__limit_text')):
	/**
	 * Limit words in a string
	 *
	 * @since 1.3.7
	 **/
	function reycore__limit_text($text, $limit)
	{
		if (str_word_count($text, 0) > $limit) {
			$words = str_word_count($text, 2);
			$pos = array_keys($words);
			$text = substr($text, 0, $pos[$limit]) . '...';
		}
		return $text;
	}
endif;


if(!function_exists('reycore__sidebar_wrap_before')):
	/**
	 * Wrap sidebar widgets into a block
	 *
	 * @since 1.5.0
	 **/
	function reycore__sidebar_wrap_before( $index )
	{
		if( !is_admin() ){

			$rey_shop_sidebars = [
				'shop-sidebar',
				'filters-sidebar',
				'filters-top-sidebar'
			];

			$classes[] = in_array($index, $rey_shop_sidebars) ? 'rey-ecommSidebar' : '';
			$classes[] = ($sidebar_title_layout = get_theme_mod('sidebar_title_layouts', '')) ? 'widget-title--' . $sidebar_title_layout : '';

			printf( '<div class="rey-sidebarInner-inside %s">', implode(' ', $classes) );
		}
	}
	add_action( 'dynamic_sidebar_before', 'reycore__sidebar_wrap_before', 0 );
endif;


if(!function_exists('reycore__sidebar_wrap_after')):
	/**
	 * Wrap sidebar widgets into a block
	 *
	 * @since 1.5.0
	 **/
	function reycore__sidebar_wrap_after()
	{
		if( !is_admin() ){
			echo '</div>';
		}
	}
	add_action( 'dynamic_sidebar_after', 'reycore__sidebar_wrap_after', 90 );
endif;


if(!function_exists('reycore__remove_404_page')):
	/**
	 * Remove default 404 page
	 *
	 * @since 1.5.0
	 */
	function reycore__remove_404_page() {
		if( get_theme_mod('404_gs', '') !== '' ){
			remove_action('rey/404page', 'rey__404page', 10);
		}
	}
endif;
add_action('wp', 'reycore__remove_404_page');


if(!function_exists('reycore__404page')):
	/**
	 * Add global section 404 page content
	 *
	 * @since 1.5.0
	 */
	function reycore__404page() {
		if( $gs = get_theme_mod('404_gs', '') ){
			echo ReyCore_GlobalSections::do_section( $gs );
		}
	}
endif;
add_action('rey/404page', 'reycore__404page');

add_filter('rey/404page/container_classes', function($class){

	if( $gs = get_theme_mod('404_gs', '') && get_theme_mod('404_gs_stretch', false) ){
		$class .= ' --stretch';
	}

	return $class;
});



if(!function_exists('reycore__filter_scripts_params')):
	/**
	 * Filter rey script params
	 *
	 * @since 1.5.0
	 **/
	function reycore__filter_scripts_params($params) {

		$params['header_fix_elementor_zindex'] = get_theme_mod('header_af__zindex_elementor', false);
		$params['check_for_empty'] = ['.--check-empty', '.rey-mobileNav-footer'];

		return $params;
	}
	add_filter('rey/main_script_params', 'reycore__filter_scripts_params', 10, 3);
endif;

if(!function_exists('reycore__filter_nav_classes')):
	/**
	 * Filter nav classes
	 *
	 * @since 1.9.0
	 **/
	function reycore__filter_nav_classes($classes, $args, $screen) {

		if( 'desktop' === $screen && ! get_theme_mod('header_nav_hover_delays', true) ){
			$classes[] = '--prevent-delays';
		}

		return $classes;
	}
	add_filter('rey/header/nav_classes', 'reycore__filter_nav_classes', 10, 3);
endif;


if(!function_exists('reycore__svg_arrows')):
	/**
	 * Print Arrow Icons
	 *
	 * @since 1.6.10
	 **/
	function reycore__svg_arrows( $args = [] )
	{

		$args = wp_parse_args($args, [
			'type' => '',
			'class' => '',
			'echo' => true,
			'single' => false,
			'custom_icon' => '',
			'attributes' => [
				'left' => '',
				'right' => '',
			]
		]);

		if( $args['type'] === '' ){

			if( $args['single'] ){
				$arrowsSvg = reycore__arrowSvg();
			}
			else {
				$arrowsSvg = reycore__arrowSvg(false, $args['class'], $args['attributes']['left']) . reycore__arrowSvg(true, $args['class'], $args['attributes']['right']);
			}

			if( $args['echo'] ){
				echo $arrowsSvg;
				return;
			}
			else {
				return $arrowsSvg;
			}
		}

		$svg['chevron'] = '<svg viewBox="0 0 40 64" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="39.5 32 6.83 64 0.5 57.38 26.76 32 0.5 6.62 6.83 0"></polygon></svg>';

		$filter = function( $html ) use ($svg, $args){

			if( $args['type'] = 'custom' && $args['custom_icon'] ){
				return $args['custom_icon'];
			}

			if( isset($svg[$args['type']]) ){
				return $svg[$args['type']];
			}

			return $html;
		};

		add_filter('rey/svg_arrow_markup', $filter);

			if( $args['single'] ){
				$arrowsSvg = reycore__arrowSvg();
			}
			else {
				$arrowsSvg = reycore__arrowSvg(false, $args['class'], $args['attributes']['left']) . reycore__arrowSvg(true, $args['class'], $args['attributes']['right']);
			}

		remove_filter('rey/svg_arrow_markup', $filter);

		if( $args['echo'] ){
			echo $arrowsSvg;
			return;
		}
		else {
			return $arrowsSvg;
		}

	}
endif;


if(!function_exists('reycore__scroll_to_top')):
	/**
	 * Scroll to top button
	 *
	 * @since 1.6.10
	 **/
	function reycore__scroll_to_top()
	{

		if( !($style = get_theme_mod('scroll_to_top__enable', '')) ){
			return;
		}

		$html = sprintf('<span class="rey-scrollTop-text">%s</span>', get_theme_mod('scroll_to_top__text', esc_html__('TOP', 'rey-core') ));

		$classes[] = '--' . $style;

		// Hide devices
		$hide_devices = get_theme_mod('scroll_to_top__hide_devices', []);
		foreach ($hide_devices as $value) {
			$classes[] = '--dnone-' . $value;
		}

		$classes[] = '--pos-' . get_theme_mod('scroll_to_top__position', 'right');

		if(
			function_exists('reycoreSvg') &&
			($custom_icon = get_theme_mod('scroll_to_top__custom_icon', '')) &&
			($svg_code = reycoreSvg()->get_inline_svg( [ 'id' => $custom_icon ] )) ){
			$html .= $svg_code;
		}
		else {

			if( $style === 'style1' ){
				$html .= reycore__svg_arrows([
					'echo'   => false,
					'single' => true
				]);
			}

			else if( $style === 'style2' ){
				$html .= reycore__svg_arrows([
					'type'   => 'chevron',
					'echo'   => false,
					'single' => true
				]);
			}
		}

		printf(
			'<a href="#scrolltotop" class="rey-scrollTop %1$s" data-entrance="%3$d">%2$s</a>',
			implode(' ', $classes),
			apply_filters('reycore/scroll_to_top/html', $html),
			get_theme_mod('scroll_to_top__entrance_point', 0)
		);

		reyCoreAssets()->add_styles('reycore-scroll-top');
		reyCoreAssets()->add_scripts('reycore-scroll-top');

	}
	add_action('rey/after_site_wrapper', 'reycore__scroll_to_top');
endif;


if( ! class_exists('ReyCore_Walker_Nav_Menu') ):
class ReyCore_Walker_Nav_Menu extends Walker_Nav_Menu {

	/**
	 * Starts the element output.
	 *
	 * @since 3.0.0
	 * @since 4.4.0 The {@see 'nav_menu_item_args'} filter was added.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {

		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @since 4.4.0
		 *
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param WP_Post  $item  Menu item data object.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

		/**
		 * Filters the CSS classes applied to a menu item's list item element.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string[] $classes Array of the CSS classes that are applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filters the ID applied to a menu item's list item element.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string   $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names . '>';

		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		if ( '_blank' === $item->target && empty( $item->xfn ) ) {
			$atts['rel'] = 'noopener noreferrer';
		} else {
			$atts['rel'] = $item->xfn;
		}
		$atts['href']         = ! empty( $item->url ) ? $item->url : '';
		$atts['aria-current'] = $item->current ? 'page' : '';

		/**
		 * Filters the HTML attributes applied to a menu item's anchor element.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 *     @type string $title        Title attribute.
		 *     @type string $target       Target attribute.
		 *     @type string $rel          The rel attribute.
		 *     @type string $href         The href attribute.
		 *     @type string $aria_current The aria-current attribute.
		 * }
		 * @param WP_Post  $item  The current menu item.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filters a menu item's title.
		 *
		 * @since 4.4.0
		 *
		 * @param string   $title The menu item's title.
		 * @param WP_Post  $item  The current menu item.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$in_link_before = '';

		if( ( apply_filters('reycore/menu_nav/support_icons', true) && reycore__acf_get_field('enable_menu_item_icon', $item->ID) && $icon_id = reycore__acf_get_field('menu_item_icon', $item->ID)) ){
			if( function_exists('reycoreSvg') && $svg_code = reycoreSvg()->get_inline_svg( [ 'id' => $icon_id ] ) ){
				if( $depth === 0 ){
					$in_link_before .= $svg_code;
				}
				else {
					$title = $svg_code . $title;
				}

				$attributes .= ' data-has-icon';

				reyCoreAssets()->add_styles('reycore-menu-icons');
			}
		}

		$item_output = '';

		if( isset($args->before) ){
			$item_output  = $args->before;
		}
		$item_output .= '<a' . $attributes . '>';
		$item_output .= $in_link_before;
		if( isset($args->before) && isset($args->after) ){
			$item_output .= $args->link_before . $title . $args->link_after;
		}
		$item_output .= '</a>';
		if( isset($args->after) ){
			$item_output .= $args->after;
		}

		/**
		 * Filters a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $item_output The menu item's starting HTML output.
		 * @param WP_Post  $item        Menu item data object.
		 * @param int      $depth       Depth of menu item. Used for padding.
		 * @param stdClass $args        An object of wp_nav_menu() arguments.
		 */
		$output_with_mega = $item_output;

		if( isset($args->rey_mega_menu) && $args->rey_mega_menu && $depth == 0 ) {
			$output_with_mega = apply_filters( 'walker_nav_menu_start_el_mega', $item_output, $item, $depth, $args );
		}

		$output .= apply_filters( 'walker_nav_menu_start_el', $output_with_mega, $item, $depth, $args );

	}
}
endif;


if(!function_exists('reycore__get_current_url')):
/**
 * Get Current URL
 *
 * @since 1.7.0
 **/
function reycore__get_current_url( $alt = false )
{
	if( $alt ){
		return ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}
endif;

if(!function_exists('reycore__cookie_notice')):
	/**
	 * Cookie Notice Markup
	 *
	 * @since 1.7.0
	 **/
	function reycore__cookie_notice()
	{
		if( ! reycore__can_add_public_content() ){
			return;
		}

		if( ! reycore__check_feature('cookie-notice') ){
			return;
		} ?>
		<aside class="rey-cookieNotice">
			<div class="rey-cookieNotice-text">
				<?php echo do_shortcode( get_theme_mod('cookie_notice__text', __('In order to provide you a personalized shopping experience, our site uses cookies. By continuing to use this site, you are agreeing to our cookie policy.', 'rey-core')) ); ?>
			</div>
			<a class="btn btn-primary-outline"><?php echo get_theme_mod('cookie_notice__btn_text', __('ACCEPT', 'rey-core')); ?></a>
		</aside>

		<?php
		reyCoreAssets()->add_styles('reycore-cookie-notice');
		reyCoreAssets()->add_scripts('reycore-cookie-notice');
	}
	add_action('wp_footer', 'reycore__cookie_notice', 5);
endif;

if(!function_exists('reycore__html_class_attr')):
	/**
	 * Adds class attribute to html tag
	 *
	 * @since 1.9.6
	 **/
	function reycore__html_class_attr($output)
	{
		$classes = esc_attr( implode(' ', array_filter(apply_filters('reycore/html_class_attr', []))));

		if( !empty($classes) ){

			// check if already has class attribute
			if( strpos($output, 'class="') !== false ){
				$output = str_replace('class="', 'class="' . $classes . ' ', $output);
			}
			else {
				$output .= sprintf(' class="%s"', $classes);
			}
		}

		return $output;
	}
	add_filter( 'language_attributes', 'reycore__html_class_attr', 100 );
endif;


if(!function_exists('reycore_wc__modal_template')):

	function reycore_wc__modal_template(){

		if( ! apply_filters('reycore/modals/always_load', get_theme_mod('perf__modals_load_always', false)) ){
			return;
		} ?>

		<script type="text/html" id="tmpl-reycore-modal-tpl">
			<div class="rey-modal {{{data.wrapperClass}}}">
				<div class="rey-modalOverlay"></div>
				<div class="rey-modalInner">
					<button class="rey-modalClose" aria-label="<?php esc_html_e('Close', 'rey-core') ?>"><?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?></button>
					<div class="rey-modalLoader">
						<div class="rey-lineLoader"></div>
					</div>
					<div class="rey-modalContent {{{data.contentClass}}}"></div>
				</div>
			</div>
		</script>

		<?php
		reyCoreAssets()->add_styles('reycore-modals');
		reyCoreAssets()->add_scripts(['reycore-modals', 'wp-util']);
	}
endif;
add_action('wp_footer', 'reycore_wc__modal_template', 5);

/**
 * Preload Assets
 * https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content
 *
 * @since 2.0.0
 */
add_action('wp_head', function(){

	foreach (get_theme_mod('perf__preload_assets', []) as $key => $asset) {

		$attributes = [];

		// eg: image, font, video
		if( $type = $asset['type'] ){
			$attributes['as'] = $type;
		}

		// eg: image/jpeg, image/svg+xml, font/woff2, video/mp4
		if( $mime = $asset['mime'] ){
			$attributes['type'] = $mime;
		}

		// eg: (max-width: 600px)
		if( $media = $asset['media'] ){
			$attributes['media'] = $media;
		}

		if( $path = $asset['path'] ){
			$attributes['href'] = $path;
		}

		// External
		if( $asset['crossorigin'] === 'yes' ){
			$attributes['crossorigin'] = '';
		}

		if( ! empty($attributes) ){
			printf(
				'<link rel="preload" %s/>',
				reycore__implode_html_attributes($attributes)
			);
		}
	}

}, 5);


if(!function_exists('reycore__get_picture')):
	/**
	 * Add picture tag.
	 *
	 * @param array $args
	 * @return string
	 */
	function reycore__get_picture($args = []){

		$args = wp_parse_args($args, [
			'id' => 0,
			'size' => 'medium',
			'class' => '',
			'disable_mobile' => false,
		]);

		if( ! $args['id'] ){
			return;
		}

		$image_size = $args['size'];
		$image_html = wp_get_attachment_image( $args['id'], $image_size, false, [ 'class' => $args['class']] );

		if( $args['disable_mobile'] ){

			$image_srcset = wp_get_attachment_image_srcset($args['id'], $image_size);

			if( ! $image_srcset && $image_src = wp_get_attachment_image_src( $args['id'], $image_size ) ){
				$image_srcset = $image_src[0];
				$image_srcset .= $args['disable_mobile'] && isset($image_src[1]) ? sprintf(' %dw', $image_src[1]) : '';
			}

			$media = '(min-width: 768px)';
			$pixel = '<source media="(max-width: 767px)" sizes="1px" srcset="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7 1w"/>';

			return sprintf('<picture class="%5$s">%3$s<source media="%4$s" srcset="%2$s"/>%1$s</picture>',
				$image_html,
				$image_srcset,
				$pixel,
				$media,
				$args['class']
			);
		}

		return $image_html;
	}
endif;


if(!function_exists('reycore__footer_reveal')):
	/**
	 * Adds Revealing footer
	 *
	 * @since 2.0.0
	 **/
	function reycore__footer_reveal($classes)
	{
		if( get_theme_mod('footer_reveal', false) ){

			$classes['footer_reveal'] = '--footer-reveal';

			if( get_theme_mod('footer_reveal_fade', false) ){
				$classes['footer_reveal_fate'] = '--footer-reveal-fade';
			}

		}

		return $classes;
	}
	add_filter('body_class', 'reycore__footer_reveal');
endif;


if(!function_exists('reycore__post_thumbnail_size')):
	function reycore__post_thumbnail_size($size)
	{
		if( $custom_size = get_theme_mod('post_thumbnail_image_size', '') ){
			return $custom_size;
		}
		return $size;
	}
	add_filter('post_thumbnail_size', 'reycore__post_thumbnail_size');
endif;
