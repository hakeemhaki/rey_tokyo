<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_ProductBadges') ):

class ReyCore_WooCommerce_ProductBadges
{

	public $badges = [];

	public $legacy__position = null;

	public $product_id = false;

	private static $_instance = null;

	private function __construct()
	{

		add_action('acf/render_field/key=field_5e541a33cdb3d', [$this, 'legacy__migrate_to_repeater']);
		add_action('woocommerce_before_shop_loop_item', [$this, 'add_actions'], 10);
		add_action('woocommerce_after_shop_loop_item', [$this, 'remove_actions'], 9999);
		add_action('woocommerce_before_single_product', [$this, 'add_badges_product_page'], 0);
		add_action('woocommerce_after_single_product_summary', [$this, 'reset_badges_product_page'], 5);
	}

	public function available_positions(){
		return apply_filters('reycore/woocommerce/product_badges_positions', [
			'top_left' => 'reycore/loop_inside_thumbnail/top-left',
			'top_right' => 'reycore/loop_inside_thumbnail/top-right',
			'bottom_left' => 'reycore/loop_inside_thumbnail/bottom-left',
			'bottom_right' => 'reycore/loop_inside_thumbnail/bottom-right',
			'before_title' => ['woocommerce_before_shop_loop_item_title', 13],
			'after_content' => ['woocommerce_after_shop_loop_item', 999],
		]);
	}

	public function get_badges(){

		global $post;

		if( ! ($product = wc_get_product($post->ID)) ){
			return;
		}

		$this->product_id = $post->ID;

		if( $product->get_type() === 'variation' ){
			$this->product_id   = $product->get_parent_id();
		}

		$this->badges = reycore__acf_get_field('badges', $this->product_id );

		if( $this->badges === false ){
			$this->badges = [];
		}

		return $this->badges;
	}

	function add_actions(){
		$this->get_badges();
		$this->actions();
		$this->legacy__add_badges();
	}

	public function remove_actions(){
		$this->actions(false);
		$this->legacy__remove_badges();
	}

	public function actions($add = true){

		$available_positions = $this->available_positions();

		foreach ($available_positions as $name => $hook) {

			if( is_array($hook) ){
				$hook_position = $hook[0];
				$hook_priority = $hook[1];
			}
			else {
				$hook_position = $hook;
				$hook_priority = 10;
			}

			$method = 'add_action';

			if( ! $add ){
				$method = 'remove_action';
			}

			if( method_exists($this, 'render_badge__' . $name) ){
				call_user_func( $method, $hook_position, [ $this, 'render_badge__' . $name ], $hook_priority );
			}
		}
	}

	function render_badge__top_left(){
		$this->render_badge('top_left');
	}

	function render_badge__top_right(){
		$this->render_badge('top_right');
	}

	function render_badge__bottom_left(){
		$this->render_badge('bottom_left');
	}

	function render_badge__bottom_right(){
		$this->render_badge('bottom_right');
	}

	function render_badge__before_title(){
		$this->render_badge('before_title');
	}

	function render_badge__after_content(){
		$this->render_badge('after_content');
	}

	function render_badge( $position ){

		if( empty($this->badges) ) {
			return;
		}

		foreach ($this->badges as $key => $badge) {

			if( $badge['position'] !== $position ){
				continue;
			}

			if( isset($badge['catalog_page']) && ! $badge['catalog_page'] ){
				continue;
			}

			if( ! (isset($badge['type']) && ($type = $badge['type'])) ){
				continue;
			}

			if( $type === 'text'){
				$this->render_text($badge);
			}
			else if( $type === 'image'){
				$this->render_image($badge);
			}
		}
	}

	public function add_badges_product_page(){

		$product_page_available_positions = [
			'before_title' => ['woocommerce_single_product_summary', 4],
			'before_meta' => ['woocommerce_single_product_summary', 39],
			'after_meta' => ['woocommerce_single_product_summary', 99],
		];

		$product = wc_get_product();
		$product_id = $product->get_id();

		$this->get_badges();

		if( ! is_array($this->badges) ){
			return;
		}

		foreach ($this->badges as $key => $badge) {

			$render = function() use ($badge, $product_id){

				global $post;

				if( $product_id !== $post->ID ){
					return;
				}

				if( ! (isset($badge['type']) && ($type = $badge['type'])) ){
					return;
				}

				if( $type === 'text'){
					$this->render_text($badge);
				}
				else if( $type === 'image'){
					$this->render_image($badge);
				}
			};

			if(
				isset($badge['product_page']) && $badge['product_page'] &&
				isset($product_page_available_positions[ $badge['product_page_position'] ]) &&
				$pp_hook = $product_page_available_positions[ $badge['product_page_position'] ]
			){

				if( is_array($pp_hook) ){
					$pp_hook_position = $pp_hook[0];
					$pp_hook_priority = $pp_hook[1];
				}
				else {
					$pp_hook_position = $pp_hook;
					$pp_hook_priority = 10;
				}

				add_action($pp_hook_position, $render, $pp_hook_priority );
			}

		}
	}

	function reset_badges_product_page(){
		$this->product_id = null;
		$this->badges = [];
	}

	public function render_text( $badge_args = [] ){

		$badge_args = wp_parse_args($badge_args, [
			'text' => '',
			'text_color' => '',
			'text_bg_color' => '',
			'text_size' => '',
			'text_size_tablet' => '',
			'text_size_mobile' => '',
			'show_on_mobile' => '',
			'product_page_as_block' => '',
			'link' => '',
		]);

		if( $text = $badge_args['text'] ){

			$classes[] = $badge_args['show_on_mobile'] ? '--show-mobile' : '';
			$classes[] = '--' . $badge_args['position'];

			if( $badge_args['product_page_as_block'] ){
				$classes[] = '--block';
			}

			$styles = [];

			if( $text_color = $badge_args['text_color'] ){
				$styles['text_color'] = '--badge-text-color:' . $text_color;
			}

			if( $bg_color = $badge_args['text_bg_color'] ){
				$styles['bg_color'] = '--badge-bg-color:' . $bg_color;
			}

			foreach(['', '_tablet', '_mobile'] as $breakpoint){
				if( $text_size = $badge_args['text_size' . $breakpoint ] ){
					$styles['text_size' . $breakpoint] = sprintf( '--badge-text-size%s:%spx', $breakpoint, $text_size );
				}
			}

			$link_start = $link_end = '';

			if( isset($badge_args['link']) && ($link = do_shortcode( $badge_args['link'] )) ){
				$link_start = '<a href="'. $link .'" target="_self">';
				$link_end = '</a>';
			}

			$badge_html = sprintf(
				'<div class="rey-pBadge --text %2$s" style="%3$s">%4$s<span>%1$s</span>%5$s</div>',
				$text,
				esc_attr( implode(' ', $classes) ),
				esc_attr( implode(';', $styles) ),
				$link_start,
				$link_end
			);

			echo apply_filters('reycore/woocommerce/product_badges/text_html', $badge_html, $badge_args, $text, $classes, $styles );

		}
	}

	public function render_image( $badge_args = [] ){

		$badge_args = wp_parse_args($badge_args, [
			'images' => [],
			'show_on_mobile' => '',
			'image_size' => '',
			'image_size_tablet' => '',
			'image_size_mobile' => '',
			'product_page_as_block' => '',
			'link' => ''
		]);

		if( !empty($badge_args['images']) ){
			$images_html = '';

			foreach( $badge_args['images'] as $image_id ){
				$images_html .= wp_get_attachment_image( $image_id['select_image'] );
			}

			$classes[] = $badge_args['show_on_mobile'] ? '--show-mobile' : '';
			$classes[] = '--' . $badge_args['position'];

			if( $badge_args['product_page_as_block'] ){
				$classes[] = '--block';
			}

			$styles = [];

			foreach(['', '_tablet', '_mobile'] as $breakpoint){
				if( $image_size = $badge_args['image_size' . $breakpoint ] ){
					$styles['image_size' . $breakpoint] = sprintf( '--badge-image-size%s:%spx', $breakpoint, $image_size );
				}
			}

			$link_start = $link_end = '';

			if( isset($badge_args['link']) && ($link = do_shortcode( $badge_args['link'] ) ) ){

				$link_start = '<a href="'. $link .'" target="_self">';
				$link_end = '</a>';
			}

			$badge_html = sprintf(
				'<div class="rey-pBadge --image %2$s" style="%3$s">%4$s %1$s %5$s</div>',
				$images_html,
				esc_attr( implode(' ', $classes) ),
				esc_attr( implode(';', $styles) ),
				$link_start,
				$link_end
			);

			echo apply_filters('reycore/woocommerce/product_badges/image_html', $badge_html, $badge_args, $images_html, $classes, $styles );
		}
	}

	/**
	 * LEGACY
	 */

	function legacy__add_badges(){

		if( empty($this->badges) ) {

			$this->legacy__get_position();

			if( $this->legacy__position ){
				$this->legacy__add_remove_badge();
			}
		}
	}

	function legacy__remove_badges(){
		if( $this->legacy__position ){
			$this->legacy__add_remove_badge( false );
		}
	}

	public function legacy__add_remove_badge( $add = true ){

		$available_positions = $this->available_positions();

		if( ! ( isset($available_positions[ $this->legacy__position ]) && $hook = $available_positions[ $this->legacy__position ] ) ){
			return;
		}

		if( is_array($hook) ){
			$hook_position = $hook[0];
			$hook_priority = $hook[1];
		}
		else {
			$hook_position = $hook;
			$hook_priority = 10;
		}

		$callback = 'add_action';

		if( ! $add ){
			$callback = 'remove_action';
		}

		call_user_func( $callback, $hook_position, [ $this, 'legacy__render_badge' ], $hook_priority, 1 );
	}

	public function legacy__get_position(){
		$this->legacy__position = reycore__acf_get_field('badge_position', $this->product_id );
	}

	public function legacy__render_badge(){

		$type = get_field_object('badge_type', $this->product_id);

		if( ! $type ) {
			return;
		}

		if( isset($type['value']) && empty($type['value']) ) {
			return;
		}

		if( $type === 'text'){
			$this->legacy__render_text();
		}
		else if( $type === 'image'){
			$this->legacy__render_image();
		}
	}

	public function legacy__render_text(){

		$product_id = $this->product_id;
		$text = reycore__acf_get_field('badge_text', $product_id);

		if( $text ){

			$classes[] = reycore__acf_get_field('badge_show_on_mobile', $product_id) ? '--show-mobile' : '';
			$classes[] = '--' . $this->legacy__position;

			$styles = [];

			if( $text_color = reycore__acf_get_field('badge_text_color', $product_id) ){
				$styles['text_color'] = '--badge-text-color:' . $text_color;
			}

			if( $bg_color = reycore__acf_get_field('badge_text_bg_color', $product_id) ){
				$styles['bg_color'] = '--badge-bg-color:' . $bg_color;
			}

			foreach(['', '_tablet', '_mobile'] as $breakpoint){
				if( $text_size = reycore__acf_get_field('badge_text_size' . $breakpoint , $product_id) ){
					$styles['text_size' . $breakpoint] = sprintf( '--badge-text-size%s:%spx', $breakpoint, $text_size );
				}
			}

			printf('<div class="rey-pBadge --text %2$s" style="%3$s"><span>%1$s</span></div>', $text, esc_attr( implode(' ', $classes) ), esc_attr( implode(';', $styles) ) );
		}
	}

	public function legacy__render_image(){

		$product_id = $this->product_id;
		$images = reycore__acf_get_field('badge_images', $product_id);

		if( $images ){
			$images_html = '';

			foreach( $images as $image_id ){
				$images_html .= wp_get_attachment_image( $image_id['select_image'] );
			}

			$classes[] = reycore__acf_get_field('badge_show_on_mobile', $product_id) ? '--show-mobile' : '';
			$classes[] = '--' . $this->legacy__position;

			$styles = [];

			foreach(['', '_tablet', '_mobile'] as $breakpoint){
				if( $image_size = reycore__acf_get_field('badge_image_size' . $breakpoint , $product_id) ){
					$styles['image_size' . $breakpoint] = sprintf( '--badge-image-size%s:%spx', $breakpoint, $image_size );
				}
			}

			printf('<div class="rey-pBadge --image %2$s" style="%3$s">%1$s</div>',
				$images_html,
				esc_attr( implode(' ', $classes) ),
				esc_attr( implode(';', $styles) )
			);
		}
	}

	public function legacy__migrate_to_repeater() {

		if( ! apply_filters('reycore/woocommerce/badges/migrate', true) ){
			return;
		}

		global $post;

		$type = get_field_object('badge_type', $post->ID);

		if( ! $type ) {
			return;
		}
		if( isset($type['value']) && empty($type['value']) ) {
			return;
		}

		$settings = [
			[
				'type' => get_field('badge_type'),
				'text' => get_field('badge_text'),
				'text_color' => get_field('badge_text_color'),
				'text_bg_color' => get_field('badge_text_bg_color'),
				'text_size' => get_field('badge_text_size'),
				'text_size_tablet' => get_field('badge_text_size_tablet'),
				'text_size_mobile' => get_field('badge_text_size_mobile'),
				'images' => get_field('badge_images'),
				'image_size' => get_field('badge_image_size'),
				'image_size_tablet' => get_field('badge_image_size_tablet'),
				'image_size_mobile' => get_field('badge_image_size_mobile'),
				'show_on_mobile' => get_field('badge_show_on_mobile'),
				'position' => get_field('badge_position'),
			]
		];

		// wp_using_ext_object_cache( false );

		update_field('badges', $settings, $post->ID);

		// delete existing single fields
		foreach ($settings[0] as $key => $value) {
			delete_field( 'badge_' . $key , $post->ID);
		}

	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return Base
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}

ReyCore_WooCommerce_ProductBadges::getInstance();

endif;
