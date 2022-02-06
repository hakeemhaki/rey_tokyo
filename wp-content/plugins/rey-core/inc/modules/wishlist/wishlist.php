<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_WooCommerce_WishlistRey') ):

	class ReyCore_WooCommerce_WishlistRey  {

		public static $is_enabled = false;
		public static $page_id = 0;
		public static $show_in_catalog_mode = true;

		const COOKIE_KEY = 'rey_wishlist_ids';

		const ASSET_HANDLE = 'reycore-wishlist';

		private static $_instance = null;

		private function __construct()
		{
			add_action( 'init', [$this, 'init']);
			add_action( 'wp', [$this, 'wp']);
			add_action( 'wp_ajax_rey_wishlist_add_to_user_meta', [ $this, 'add_to_user_meta'] );
			add_action( 'wp_ajax_nopriv_rey_wishlist_add_to_user_meta', [ $this, 'add_to_user_meta'] );
			add_action( 'wp_ajax_rey_wishlist_get_page_content', [ $this, 'get_page_content'] );
			add_action( 'wp_ajax_nopriv_rey_wishlist_get_page_content', [ $this, 'get_page_content'] );
			add_filter( 'reycore/woocommerce/wishlist/ids', [$this, 'get_wishlist_ids']);
			add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
			add_filter( 'reycore/woocommerce/wishlist/button_html', [$this, 'catalog_button_html']);
			add_filter( 'reycore/woocommerce/wishlist/url', [$this, 'wishlist_url']);
		}

		function init(){
			self::$page_id = self::wishlist_page_id();
			self::$is_enabled = $this->is_enabled();
		}

		function wp(){

			if( ! self::$is_enabled ){
				return;
			}

			if( reycore_wc__is_catalog() && ! apply_filters('reycore/woocommerce/wishlist/catalog_mode', true ) ){
				return;
			}

			add_action( 'reycore/elementor/product_grid/lazy_load_assets', [$this, 'lazy_load_markup']);

			add_filter('rey/site_content_classes', [$this, 'add_loading'], 10);

			add_action( 'rey/before_site_container', [$this, 'apply_filter_content']);
			add_action( 'rey/after_site_container', [$this, 'remove_filter_content']);

			add_filter( 'body_class', [$this, 'append_wishlist_page_class']);
			add_filter( 'rey/main_script_params', [$this, 'script_params']);

			add_filter( 'reycore/woocommerce/wishlist/counter_html', [$this, 'wishlist_counter_html']);
			add_filter( 'reycore/woocommerce/wishlist/title', [$this, 'wishlist_title']);

			add_filter( 'woocommerce_account_menu_items', [$this, 'add_wishlist_page_to_account_menu']);
			add_filter( 'woocommerce_get_endpoint_url', [$this, 'add_wishlist_url_endpoint'], 20, 4);

			add_action( 'woocommerce_before_single_product', [$this, 'pdp_button']);

			add_action( 'wp_login', [$this, 'update_ids_after_login'], 10, 2);

			add_action( 'reycore/woocommerce/wishlist/render_products', [$this, 'render_products']);

			add_shortcode('rey_wishlist', [$this, 'wishlist_page_output']);

			// TODO:
			// Modal choice instead of tooltip with table list
			// Shareble url
		}

		public static function load_scripts(){

			reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);

			do_action('reycore/woocommerce/ajax/scripts');

		}

		public function register_assets(){

			reyCoreAssets()->register_asset('styles', [
				self::ASSET_HANDLE => [
					'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				]
			]);

			reyCoreAssets()->register_asset('scripts', [
				self::ASSET_HANDLE => [
					'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js',
					'deps'    => ['rey-script', 'reycore-scripts', 'reycore-woocommerce'],
					'version'   => REY_CORE_VERSION,
				]
			]);

		}


		public static function get_cookie_key(){
			return self::COOKIE_KEY . '_' . (is_multisite() ? get_current_blog_id() : 0);
		}

		function script_params($params){

			$params['wishlist_url'] = self::wishlist_url();
			$params['wishlist_after_add'] = get_theme_mod('wishlist__after_add', 'notice');
			$params['wishlist_text_add'] = self::get_texts('wishlist__texts_add');
			$params['wishlist_text_rm'] = self::get_texts('wishlist__texts_rm');

			return $params;
		}

		public static function get_texts( $text = '' ){

			$defaults = [
				'wishlist__text' => _x('Wishlist', 'Title', 'rey-core'),
				'wishlist__texts_add' => esc_html__('Add to wishlist', 'rey-core'),
				'wishlist__texts_rm' => esc_html__('Remove from wishlist', 'rey-core'),
				'wishlist__texts_added' => esc_html__('Added to wishlist!', 'rey-core'),
				'wishlist__texts_btn' => esc_html__('VIEW WISHLIST', 'rey-core'),
				'wishlist__texts_page_title' => __('Wishlist is empty.', 'rey-core'),
				'wishlist__texts_page_text' => __('You don\'t have any products added in your wishlist. Search and save items to your liking!', 'rey-core'),
				'wishlist__texts_page_btn_text' => __('SHOP NOW', 'rey-core'),
			];

			if( !empty($text) ){

				$opt = get_theme_mod($text, $defaults[$text]);

				if( empty($opt) ){
					$opt = $defaults[$text];
				}

				return $opt;
			}

			return '';

		}

		function get_cookie_products_ids(){
			$products = [];

			if ( ! empty( $_COOKIE[self::get_cookie_key()] ) ) { // @codingStandardsIgnoreLine.
				$products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key()] ) ) ); // @codingStandardsIgnoreLine.
			}

			return $products;
		}

		function get_ids(){

			$products = [];

			if( is_user_logged_in() ){

				$user = wp_get_current_user();
				$products = get_user_meta($user->ID, self::get_cookie_key(), true);

			}
			else {
				$products = $this->get_cookie_products_ids();
			}

			return $products;
		}

		function catalog_button_html( $btn_html ){

			if( ! self::$is_enabled ){
				return $btn_html;
			}

			$product = wc_get_product();

			if( ! $product ){
				global $product;
			}

			if ( ! ($product && $id = $product->get_id()) ) {
				return $btn_html;
			}

			$button_class = [];
			$active_products = $this->get_ids();
			$position = get_theme_mod('loop_wishlist_position', 'bottom');

			$button_text = self::get_texts('wishlist__texts_add');

			if( !empty($active_products) && in_array($id, $active_products, true) ){
				$button_class['is_active'] = '--in-wishlist';
				$button_text = self::get_texts('wishlist__texts_rm');
			}

			if( get_theme_mod('wishlist_loop__mobile', false)){

				// place it above the thumbnail
				$button_class['mobile'] = '--show-mobile--top';

				// if ATC. button is enabled, leave it in item's footer
				if( get_theme_mod('loop_add_to_cart_mobile', false) || $position === 'topright' ){
					$button_class['mobile'] = '--show-mobile';
				}

			}

			if( is_user_logged_in() ){
				$button_class['supports_ajax'] = '--supports-ajax';
			}

			// style icon only when on icon over thumbnail
			if(
				in_array($position, ['topright', 'bottomright'], true) &&
				$icon_style = get_theme_mod('wishlist_loop__icon_style', 'minimal')
			){
				$button_class['icon_style'] = '--icon-style-' . $icon_style;
			}

			$attributes = [];

			if( get_theme_mod('wishlist_loop__tooltip', false) ){
				$attributes['data-rey-tooltip'] = wp_json_encode([
					'text' => $button_text,
					'class' => '--basic',
					'fixed' => true
				]);
			}

			add_action( 'wp_footer', [$this, 'after_add_markup']);

			self::load_scripts();

			return sprintf(
				'<a href="%5$s" class="%1$s rey-wishlistBtn rey-wishlistBtn-link" data-id="%2$s" title="%3$s" aria-label="%3$s" %6$s>%4$s</a>',
				esc_attr(implode(' ', $button_class)),
				esc_attr($id),
				$button_text,
				apply_filters('reycore/woocommerce/wishlist/catalog_btn_content', $this->get_wishlist_icon(), $button_class, $this),
				esc_url( get_permalink($id) ),
				reycore__implode_html_attributes($attributes)
			);

		}

		function lazy_load_markup(){
			add_action( 'wp_footer', [$this, 'after_add_markup']);
		}

		function pdp_button(){

			if( !get_theme_mod('wishlist_pdp__enable', true) ){
				return;
			}

			$position = get_theme_mod('wishlist_pdp__position', 'inline');

			$hooks = [
				'before' => [
					'hook' => 'woocommerce_before_add_to_cart_form',
					'priority' => 10
				],
				'inline' => [
					'hook' => 'woocommerce_after_add_to_cart_button',
					'priority' => 0
				],
				'after' => [
					'hook' => 'reycore/woocommerce/single/after_add_to_cart_form',
					'priority' => 0
				],
			];

			if( reycore_wc__is_catalog() ){
				$position = 'catalog_mode';
				$hooks['catalog_mode'] = [
					'hook' => 'woocommerce_single_product_summary',
					'priority' => 30
				];
			}

			add_action( $hooks[$position]['hook'], [$this, 'output_pdp_button'], $hooks[$position]['priority'] );

		}

		function output_pdp_button(){

			$product = wc_get_product();

			if ( ! ($product && $id = $product->get_id()) ) {
				return $btn_html;
			}

			$button_class = $text_class = [];
			$active_products = $this->get_ids();

			$button_text = self::get_texts('wishlist__texts_add');

			if( !empty($active_products) && in_array($id, $active_products, true) ){
				$button_class[] = '--in-wishlist';
				$button_text = self::get_texts('wishlist__texts_rm');
			}

			if( is_user_logged_in() ){
				$button_class[] = '--supports-ajax';
			}

			$button_content = $this->get_wishlist_icon();

			if( ($btn_style = get_theme_mod('wishlist_pdp__btn_style', 'btn-line')) && $btn_style !== 'none' ){
				$button_class['btn_style'] = 'btn ' . $btn_style;
				// disable line buttons
				if( in_array($btn_style, ['btn-line', 'btn-line-active'], true) ){
					$text_class['text_style'] = 'btn ' . $btn_style;
					$button_class['btn_style'] = 'btn --btn-text';
				}
			}

			$text_visibility = get_theme_mod('wishlist_pdp__wtext', 'show_desktop');

			if( $text_visibility && $button_text ){
				if( $text_visibility === 'show_desktop' ){
					$text_class[] = '--dnone-mobile --dnone-tablet';
				}
				$button_content .= sprintf('<span class="rey-wishlistBtn-text %s">%s</span>', esc_attr(implode(' ', $text_class)), $button_text);
			}

			$attributes = [];

			// only when text is hidden
			if( $text_visibility === '' && get_theme_mod('wishlist_pdp__tooltip', false) ){
				$attributes['data-rey-tooltip'] = wp_json_encode([
					'text' => $button_text,
					'class' => '--basic',
					'fixed' => true
				]);
			}

			$btn_html = sprintf(
				'<div class="rey-wishlistBtn-wrapper"><a href="%5$s" class="%1$s rey-wishlistBtn" data-id="%2$s" title="%3$s" aria-label="%3$s" %6$s>%4$s</a></div>',
				esc_attr(implode(' ', $button_class)),
				esc_attr($id),
				$button_text,
				$button_content,
				esc_url( get_permalink($id) ),
				reycore__implode_html_attributes($attributes)
			);

			echo $btn_html;

			self::load_scripts();

			add_action( 'wp_footer', [$this, 'after_add_markup']);

		}

		function get_wishlist_icon(){
			return reycore__get_svg_icon__core([
				'id' => 'reycore-icon-' . get_theme_mod('wishlist__icon_type', 'heart'),
				'class' => 'rey-wishlistBtn-icon'
			]);
		}

		/**
		 * Wishlist page
		 */

		public static function _get_page_id(){

			if( $wishlist_page_id = get_theme_mod('wishlist__default_url', '') ){
				return absint($wishlist_page_id);
			}

			// Inherit from TI Wishlist
			if( ($ti_wishlist = get_option('tinvwl-page')) && isset($ti_wishlist['wishlist']) && $ti_wishlist['wishlist'] !== '' ){
				return absint($ti_wishlist['wishlist']);
			}
		}

		public static function wishlist_page_id(){

			$page_id = self::_get_page_id();

			if( class_exists( 'SitePress' ) ){
				if ( function_exists('icl_get_languages') && ($languages = icl_get_languages()) && is_array( $languages ) && count( $languages ) ) {
					$page_id = apply_filters( 'wpml_object_id', $page_id, 'post' );
				}
			}

			if( function_exists('pll_get_post') ){
				$page_id = pll_get_post($page_id, pll_current_language('slug'));
			}

			return $page_id;
		}

		public static function wishlist_url( $url = '' ){

			if( ! self::$is_enabled ){
				return $url;
			}

			if( $wishlist_page_id = self::$page_id ){
				return esc_url( get_permalink($wishlist_page_id) );
			}

			return $url;
		}

		function append_wishlist_page_class($classes){

			$classes[] = 'rey-wishlist';

			if( ($wishlist_page_id = self::$page_id) && is_page($wishlist_page_id) ){
				$classes[] = 'woocommerce';
				$classes[] = 'rey-wishlist-page';
			}

			return $classes;
		}

		function apply_filter_content(){

			if( !($wishlist_page_id = self::$page_id) ){
				return;
			}

			if( ! is_page($wishlist_page_id) ){
				return;
			}

			do_action('reycore/woocommerce/wishlist/page');

			add_filter( 'the_content', [$this, 'append_wishlist_page_content']);
			remove_all_actions('rey/content/title');
		}

		function remove_filter_content(){
			remove_filter( 'the_content', [$this, 'append_wishlist_page_content']);
		}

		function wishlist_page_output($atts = []){

			self::load_scripts();

			$classes = '';

			if( isset($atts['hide_title']) && $atts['hide_title'] === 'yes' ){
				$classes .= ' --hide-title';
			}

			ob_start(); ?>

			<div class="rey-wishlistWrapper --empty <?php echo esc_attr($classes); ?>"></div>

			<div class="rey-wishlist-emptyPage" data-id="<?php echo esc_attr( self::$page_id ); ?>">
				<div class="rey-wishlist-emptyPage-icon">
					<?php echo $this->get_wishlist_icon(); ?>
				</div>
				<div class="rey-wishlist-emptyPage-title">
					<h2><?php echo self::get_texts('wishlist__texts_page_title'); ?></h2>
				</div>
				<div class="rey-wishlist-emptyPage-content">
					<p><?php echo self::get_texts('wishlist__texts_page_text'); ?></p>
					<a href="<?php echo get_permalink( wc_get_page_id( 'shop' ) ) ?>" class="btn btn-primary">
						<?php echo self::get_texts('wishlist__texts_page_btn_text') ?>
					</a>
				</div>
			</div>

			<div class="rey-lineLoader rey-wishlistLoader"></div>

			<?php
			return ob_get_clean();
		}

		function append_wishlist_page_content( $content ){

			if( function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode() ){
				return $content;
			}

			if( !is_main_query() ){
				return $content;
			}

			self::load_scripts();

			if( class_exists('ReyCore_WooCommerce_Loop') ){
				ReyCore_WooCommerce_Loop::getInstance()->load_scripts();
			}

			add_filter('comments_open', '__return_false', 20, 2);
			add_filter('pings_open', '__return_false', 20, 2);
			add_filter('comments_array', '__return_empty_array', 10, 2);

			$wishlist__inj_type = get_theme_mod('wishlist__inj_type', 'override');

			if( $wishlist__inj_type === 'override' ){
				return do_shortcode('[rey_wishlist]');
			}
			else if( $wishlist__inj_type === 'append' ){
				return $content . do_shortcode('[rey_wishlist]');
			}

			return $content;
		}


		public function get_products_page_html( $product_ids = [] ){

			$product_ids = array_reverse($product_ids);

			if( ! (isset($_GET['pid']) && $post_id = absint($_GET['pid'])) ){
				$url     = wp_get_referer();
				$post_id = url_to_postid( $url );
			}

			// Include WooCommerce frontend stuff
			wc()->frontend_includes();

			add_action( 'reycore/loop_inside_thumbnail/top-left', [$this, 'add_remove_buttons']);

			add_filter('reycore/loop_components', function($components){

				// loop components
				$components['view_selector'] = false;
				$components['filter_button'] = false;
				$components['filter_top_sidebar'] = false;
				$components['wishlist'] = [
					'bottom' => false,
					'topright' => false,
					'bottomright' => false,
				];

				return $components;
			});

			$title = '<header class="rey-pageHeader"><h1 class="rey-pageTitle entry-title">' . get_the_title($post_id) . '</h1></header>';

			if( ! ( isset($_GET['hide-title']) && absint($_GET['hide-title']) === 1 ) ){
				echo apply_filters('reycore/woocommerce/wishlist/title_output', $title);
			}

			echo do_shortcode('[products ids="' . implode(',', $product_ids) . '"]');
		}

		function add_loading($classes){

			if( ($wishlist_page_id = self::$page_id) && is_page($wishlist_page_id) ){
				if( $this->get_ids() ){
					$classes[] = '--loading';
				}
			}

			return $classes;
		}

		/**
		 * Used throughout app
		 */
		public function render_products( $columns = 3 ){

			$product_ids = $this->get_ids();

			if( empty($product_ids) ){
				return;
			}

			echo '<div class="rey-wishlistProds">';

				printf('<h3 class="rey-wishlistProds-title">%s</h3>', esc_html__('Some of your favorite products', 'rey-core'));

				wc_set_loop_prop( 'columns', $columns );
				$wishlist_products = array_filter( array_map( 'wc_get_product', $product_ids ), 'wc_products_array_filter_visible' );

				woocommerce_product_loop_start();
					foreach ( $wishlist_products as $wprod ) :
						$post_object = get_post( $wprod->get_id() );
						setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
						wc_get_template_part( 'content', 'product' );
						wp_reset_postdata();
					endforeach;
				woocommerce_product_loop_end();

			echo '</div>';
		}

		public function get_page_content(){

			if( ! self::$is_enabled ){
				wp_send_json_error();
			}

			$product_ids = $this->get_ids();

			$data = '';

			if( !empty($product_ids) ){
				ob_start();
				$this->get_products_page_html($product_ids);
				$data = ob_get_clean();
			}

			wp_send_json_success($data);
		}

		public static function is_ajax_call(){
			return wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'rey_wishlist_get_page_content';
		}

		function add_remove_buttons(){

			global $product;

			if( ! $product ){
				return;
			}

			printf('<a class="rey-wishlist-removeBtn" href="#" data-id="%1$d" data-rey-tooltip="%4$s" aria-label="%3$s">%2$s</a>',
				$product->get_id(),
				reycore__get_svg_icon(['id' => 'rey-icon-close']),
				self::get_texts('wishlist__texts_rm'),
				esc_attr(wp_json_encode([
					'text' => self::get_texts('wishlist__texts_rm'),
					'class' => '--basic',
					'fixed' => true
				]))
			);
		}

		function after_add_markup(){

			if( ! reycore__can_add_public_content() ){
				return;
			}

			$type = get_theme_mod('wishlist__after_add', 'notice');

			if( $type === 'notice' ){

				$url = '';

				if( $wishlist_url = self::wishlist_url() ){
					$url = sprintf('<a href="%1$s" class="btn btn-line-active">%2$s</a>',
						$wishlist_url,
						self::get_texts('wishlist__texts_btn')
					);
				}

				printf( '<div class="rey-wishlist-notice-wrapper --hidden"><div class="rey-wishlist-notice"><span>%1$s</span> %2$s</div></div>',
					self::get_texts('wishlist__texts_added'),
					$url
				);
			}
		}

		function add_wishlist_page_to_account_menu($items){

			$c = false;

			if( isset($items['customer-logout']) ){
				$c = $items['customer-logout'];
				unset($items['customer-logout']);
			}

			if( self::$page_id ){

				$counter = '';

				if( ! is_account_page() ){
					$counter = sprintf(' <span class="acc-count">%s</span>', $this->wishlist_counter_html() );
				}

				$items['rey_wishlist'] = $this->wishlist_title() . $counter;
			}

			if( $c ){
				$items['customer-logout'] = $c;
			}

			return $items;
		}

		function add_wishlist_url_endpoint($url, $endpoint, $value, $permalink){

			if( $endpoint === 'rey_wishlist') {
				$url = self::wishlist_url();
			}

			return $url;
		}

		function wishlist_counter_html(){
			return '<span class="rey-wishlistCounter-number --empty"></span>';
		}

		function wishlist_title(){
			return self::get_texts('wishlist__text');
		}

		function get_wishlist_ids( $ids ){

			if( ! self::$is_enabled ){
				return [];
			}

			$product_ids = $this->get_ids();

			if( empty($product_ids) ){
				return $ids;
			}

			return array_reverse($product_ids);
		}

		public function add_to_user_meta(){

			if( ! is_user_logged_in() ){
				wp_send_json_error(esc_html__('Not logged in!', 'rey-core'));
			}

			$user = wp_get_current_user();
			$product_ids = $this->get_cookie_products_ids();

			if( update_user_meta($user->ID, self::get_cookie_key(), $product_ids) ){
				wp_send_json_success($product_ids);
			}
		}

		public function update_ids_after_login( $user_login, $user){

			$product_ids = $this->get_cookie_products_ids();
			$saved_product_ids = get_user_meta($user->ID, self::get_cookie_key(), true);

			if( ! is_array($saved_product_ids) ) {
				$saved_product_ids = [];
			}

			update_user_meta($user->ID, self::get_cookie_key(), array_unique(array_merge($product_ids, $saved_product_ids)) );
		}

		function is_enabled(){

			if( class_exists('TInvWL_Public_AddToWishlist') ){
				return;
			}

			if( ! function_exists('reycore_wc__check_wishlist') ){
				return;
			}

			if( ! reycore_wc__check_wishlist() ){
				return;
			}

			return true;
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
	ReyCore_WooCommerce_WishlistRey::getInstance();

endif;
