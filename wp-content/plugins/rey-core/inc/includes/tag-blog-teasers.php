<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_Blog_Teasers') ):

class ReyCore_Blog_Teasers
{
	private static $_instance = null;

	private function __construct()
	{
		$this->settings = [
			'min_limit_of_blocks' => 6,
			'title_tag' => 'h4'
		];

		add_filter('the_content', [$this, 'the_content']);
	}

	function get_related_posts( $args = [] ){

		$args['content'] = [
			'blockName' => 'acf/reycore-posts-v1',
			'attrs' => [
				'id' => uniqid('block_'),
				'name' => 'acf/reycore-posts-v1',
				'data' => [
					'columns'            => 1,
					'limit'              => isset($args['related_limit']) && !empty($args['related_limit']) ? $args['related_limit'] : 3,
					'gap_size'           => 30,
					'vertical_separator' => true,
					'numerotation'       => 'roman',
					'query_type'         => 'related',
					'order_by'           => 'date',
					'order_direction'    => 'asc',
					'show_image'         => false,
					'show_date'          => false,
					'show_categories'    => false,
					'show_excerpt'       => false,
					'title_size'         => 'xs',
				],
				'align' => '',
				'mode' => 'preview',
			],
		];

		$output = render_block( $this->get_container_data($args) );

		// check if it's empty
		if( strpos($output, '__no-posts') !== false ){
			return;
		}

		return $output;
	}

	function get_single_post( $args = [] ){

		$args['content'] = [
			'blockName' => 'acf/reycore-posts-v1',
			'attrs' => [
				'id' => uniqid('block_'),
				'name' => 'acf/reycore-posts-v1',
				'data' => wp_parse_args($args, [
					'columns'            => 1,
					'limit'              => 1,
					'gap_size'           => 30,
					'vertical_separator' => false,
					'numerotation'       => false,
					'query_type'         => 'manual',
					'order_by'           => 'date',
					'order_direction'    => 'asc',
					'image_alignment'    => 'top',
					'show_image'         => true,
					'show_date'          => false,
					'show_categories'    => true,
					'show_excerpt'       => false,
					'title_size'         => 'xs',
				]),
				'align' => '',
				'mode' => 'preview',
			],
		];

		$output = render_block( $this->get_container_data($args) );

		// check if it's empty
		if( strpos($output, '__no-posts') !== false ){
			return;
		}

		return $output;

	}

	function get_global_section( $args = [] ){

		if( ! (isset($args['global_section']) && $gs = $args['global_section']) ){
			return;
		}

		$args['content'] = [
			'blockName' => 'acf/reycore-elementor-global-sections',
			'attrs' => [
				'id' => uniqid('block_'),
				'name' => 'acf/reycore-elementor-global-sections',
				'data' => [
					'global_section' => $gs,
				],
				'align' => '',
				'mode' => 'preview',
			],
		];

		$output = render_block( $this->get_container_data($args) );

		return $output;

	}

	function get_container_data( $args = [] ){

		$args = wp_parse_args($args, [
			'heading'      => '',
			'heading_tag'  => $this->settings['title_tag'],
			'offset_align' => 'semi',
			'align'        => 'right',
			'width'        => 325,
			'content'      => '',
		]);

		if( ! $args['content'] ){
			return;
		}

		$offset = $style = '';

		if( in_array($args['align'], ['left', 'right'], true) ){
			$offset = "--offsetAlign-" . $args['offset_align'];
			$style .= "--max-width:{$args['width']}px;width:100%";
		}

		if( 'center' === $args['align'] ){
			$style .= "max-width:100%";
		}

		$data = [
			'blockName' => 'reycore/container-v1',
			'innerBlocks' => [],
			'innerContent' => [
				"<div class='wp-block-reycore-container-v1 align{$args['align']} reyBlock-container-v1 {$offset}' data-align='{$args['align']}' style='{$style}'><div class='reyBlock-containerInner'>",
				NULL,
			],
		];

		if( $heading = $args['heading'] ) {

			$heading_html = sprintf('<%1$s>%2$s</%1$s>', $args['heading_tag'], $heading);
			$data['innerBlocks'][] = [
				'blockName' => 'core/heading',
				'innerContent' => [ $heading_html ],
			];
			// placeholder for inner content
			$data['innerContent'][] = null;

		}

		$data['innerBlocks'][] = $args['content'];
		$data['innerContent'][] = '</div></div>';

		return $data;
	}

	function add_inner_content( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'has_position_checks' => true,
		]);

		$output = '';

		global $post;

		if( ! (isset($post->post_content) && ($post_content = $post->post_content)) ){
			return $output;
		}

		if( ! ($teasers = $args['data']) ){
			return $output;
		}

		$blocks = parse_blocks( $post_content );

		if( count( array_filter(wp_list_pluck($blocks, 'blockName')) ) < $this->settings['min_limit_of_blocks'] ){
			return $output;
		}

		$_skip = $_rendered = [];

		foreach ($blocks as $block_key => $block) {

			$rendered_block = render_block($block);

			foreach ([
				't' => 0.25,
				'm' => 0.5,
				'b' => 0.75
			] as $pos => $percent) {

				if( ! (isset($teasers[$pos]) && $teasers_options = $teasers[$pos]) ){
					continue;
				}

				foreach ($teasers_options as $key => $t_options) {

					if( isset($_rendered[$pos][$key]) && $_rendered[$pos][$key] ){
						continue;
					}

					if( ( (isset($_skip[$pos][$key]) && $_skip[$pos][$key]) || ( $block_key === absint( count($blocks) * $percent ) ) ) ){

						if( $args['has_position_checks'] ){

							// if it's full or wide, just skip, nothing can be done about them
							if( $t_options['align'] !== 'center' && isset($block['attrs']['align']) && in_array($block['attrs']['align'], ['wide', 'full'], true) ){
								$_skip[$pos][$key] = true;
								continue;
							}

						}

						// if it's something else, not a paragraph, but aligned semi (bc full is ok), just set it full
						if( ! is_null($block['blockName'] ) && $block['blockName'] !== 'core/paragraph' && $t_options['offset_align'] === 'semi' ){
							$t_options['offset_align'] = 'full';
						}

						if( $teaser_content = $this->get_content($t_options) ){
							$output .= $teaser_content;
						}

						$_rendered[$pos][$key] = true;
					}
				}
			}

			$output .= $rendered_block;
		}

		return $output;
	}

	function get_content( $option ){

		if( 'single' === $option['type'] ){
			return $this->get_single_post( $option );
		}

		else if( 'related' === $option['type'] ){
			return $this->get_related_posts( $option );
		}

		else if( 'global_section' === $option['type'] ){
			return $this->get_global_section( $option );
		}

	}

	function the_content($content){

		if( is_admin() ){
			return $content;
		}

		if( ! is_singular('post') ){
			return $content;
		}

		$blog_teasers = get_theme_mod('blog_teasers', []);

		if( empty($blog_teasers) ){
			return $content;
		}

		$top_content = $bottom_content = '';

		$__inner_content = [
			't' => [],
			'm' => [],
			'b' => [],
		];

		foreach ($blog_teasers as $option) {

			if( '0%' === $option['position'] ){
				$top_content .= $this->get_content($option);
			}
			elseif( '25%' === $option['position'] ){
				$__inner_content['t'][] = $option;
			}
			elseif( '50%' === $option['position'] ){
				$__inner_content['m'][] = $option;
			}
			elseif( '75%' === $option['position'] ){
				$__inner_content['b'][] = $option;
			}
			elseif( '100%' === $option['position'] ){
				$bottom_content .= $this->get_content($option);
			}

		}

		if( !empty($__inner_content['t']) || !empty($__inner_content['m']) || !empty($__inner_content['b']) ){

			$inner_content = $this->add_inner_content([
				'data'                => $__inner_content,
				'has_position_checks' => true,
			]);

			if( $inner_content ){
				$content = $inner_content;
			}

		}

		return $top_content . $content . $bottom_content;

	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_Blog_Teasers
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}
ReyCore_Blog_Teasers::getInstance();
endif;
