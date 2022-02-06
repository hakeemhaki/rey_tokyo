<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_WooCommerce_CompareRey') ):

	class ReyCore_WooCommerce_CompareRey  {

		public $is_enabled = false;
		public static $compare_page_id = 0;

		const COOKIE_KEY = 'rey_compare_ids';

		const ASSET_HANDLE = 'reycore-compare';

		private static $_instance = null;

		private function __construct()
		{

			add_action( 'init', [$this, 'init']);
			add_action( 'wp', [$this, 'wp']);
			add_action( 'wp_ajax_rey_compare_add_to_user_meta', [ $this, 'add_to_user_meta'] );
			add_action( 'wp_ajax_nopriv_rey_compare_add_to_user_meta', [ $this, 'add_to_user_meta'] );
			add_action( 'wp_ajax_rey_compare_get_viewed_products', [ $this, 'get_viewed_products'] );
			add_action( 'wp_ajax_nopriv_rey_compare_get_viewed_products', [ $this, 'get_viewed_products'] );
			add_action( 'wp_ajax_rey_compare_get_page_content', [ $this, 'get_page_content'] );
			add_action( 'wp_ajax_nopriv_rey_compare_get_page_content', [ $this, 'get_page_content'] );
			add_action( 'reycore/loop_inside_thumbnail/top-right', [$this, 'catalog_button_html']);
			add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		}

		function init(){

			self::$compare_page_id = self::compare_page_id();

			$this->is_enabled = get_theme_mod('compare__enable', false) && ! is_null(self::$compare_page_id);

		}

		function wp(){

			if( ! $this->is_enabled ){
				return;
			}

			add_action( 'reycore/elementor/product_grid/lazy_load_assets', [$this, 'lazy_load_markup']);

			add_filter('rey/site_content_classes', [$this, 'add_loading'], 10);

			add_action( 'reycore/woocommerce/wishlist/page', [ $this, 'load_scripts' ] );

			add_filter( 'body_class', [$this, 'append_compare_page_class']);

			add_action( 'rey/before_site_container', [$this, 'apply_filter_content']);
			add_action( 'rey/after_site_container', [$this, 'remove_filter_content']);

			add_filter( 'rey/main_script_params', [$this, 'script_params']);

			add_filter( 'reycore/woocommerce/compare/ids', [$this, 'get_compare_ids']);
			add_filter( 'reycore/woocommerce/compare/counter_html', [$this, 'compare_counter_html']);
			add_filter( 'reycore/woocommerce/compare/title', [$this, 'compare_title']);

			add_filter( 'woocommerce_account_menu_items', [$this, 'add_compare_page_to_account_menu']);
			add_filter( 'woocommerce_get_endpoint_url', [$this, 'add_compare_url_endpoint'], 20, 4);

			add_action( 'woocommerce_before_single_product', [$this, 'pdp_button']);

			add_action( 'wp_login', [$this, 'update_ids_after_login'], 10, 2);

			add_action( 'template_redirect', [$this, 'track_products'], 20 );
			add_action( 'reycore/woocommerce/quickview/before_render', [$this, 'track_products'], 20 );
		}

		public static function load_scripts(){
			reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
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


		public static function get_cookie_key( $custom = '' ){
			return self::COOKIE_KEY . '_' . (is_multisite() ? get_current_blog_id() : 0) . ($custom ? '_' . $custom : '');
		}

		function script_params($params){

			$params['compare_url'] = self::compare_url();
			$params['compare_after_add'] = get_theme_mod('compare__after_add', 'notice');
			$params['compare_text_add'] = self::get_texts('add');
			$params['compare_text_rm'] = self::get_texts('rm');

			return $params;
		}

		public static function get_texts( $text = '' ){

			$texts = apply_filters('reycore/woocommerce/compare/texts',  [
				'compare__text' => __('Compare products', 'rey-core'),
				'add' => esc_html__('Compare product', 'rey-core'),
				'rm' => esc_html__('Remove from list', 'rey-core'),
				'btn' => esc_html__('COMPARE NOW', 'rey-core'),
				'page_title' => __('Compare list is empty.', 'rey-core'),
				'page_text' => __('You don\'t have any products added in your list. Search and choose items to your liking!', 'rey-core'),
				'page_btn_text' => __('SHOP NOW', 'rey-core'),
				'close' => esc_html__('CLOSE', 'rey-core'),
				'products' => esc_html__('PRODUCT(s)', 'rey-core'),
				'recently_viewed' => esc_html__('RECENTLY VIEWED PRODUCTS', 'rey-core'),
				'recently_viewed_add' => esc_html__('Add to list', 'rey-core'),
				'reset_list' => esc_html__('RESET LIST', 'rey-core'),
				'reset_list_mobile' => esc_html__('RESET', 'rey-core'),
				'no_products' => esc_html__('No recently viewed products yet.', 'rey-core'),
				'mobile_tip' => esc_html__('Hold and drag the table!', 'rey-core'),
			]);

			if( !empty($text) && isset($texts[$text]) ){
				return $texts[$text];
			}

			return $texts;
		}

		public static function get_cookie_products_ids(){
			$products = [];

			if ( ! empty( $_COOKIE[self::get_cookie_key()] ) ) { // @codingStandardsIgnoreLine.
				$products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key()] ) ) ); // @codingStandardsIgnoreLine.
			}

			return $products;
		}

		public static function get_ids(){

			$products = [];

			if( is_user_logged_in() ){
				$user = wp_get_current_user();
				$products = get_user_meta($user->ID, self::get_cookie_key(), true);
			}
			else {
				$products = self::get_cookie_products_ids();
			}

			return $products;
		}

		function add_loading($classes){

			if( ($compare_page_id = self::$compare_page_id) && is_page($compare_page_id) ){
				if( $this->get_ids() ){
					$classes[] = '--loading';
				}
			}

			return $classes;
		}

		function catalog_button_html(){

			if( ! $this->is_enabled ){
				return;
			}

			if( ! get_theme_mod('compare__loop_enable', true) ){
				return;
			}

			$product = wc_get_product();

			if ( ! ($product && $id = $product->get_id()) ) {
				return;
			}

			reyCoreAssets()->add_scripts('simple-scrollbar');
			reyCoreAssets()->add_styles('simple-scrollbar');

			$button_class = [];
			$active_products = self::get_ids();

			$button_text = self::get_texts('add');

			if( !empty($active_products) && in_array($id, $active_products, true) ){
				$button_class[] = '--in-compare';
				$button_text = self::get_texts('rm');
			}

			if( is_user_logged_in() ){
				$button_class[] = '--supports-ajax';
			}

			$button_content = self::get_compare_icon();

			printf(
				'<a href="%5$s" class="%1$s rey-compareBtn rey-compareBtn-link" data-id="%2$s" title="%3$s" aria-label="%3$s" data-rey-tooltip="%6$s">%4$s</a>',
				esc_attr(implode(' ', $button_class)),
				esc_attr($id),
				$button_text,
				$button_content,
				esc_url( get_permalink($id) ),
				esc_attr(wp_json_encode([
					'text' => $button_text,
					'class' => '--basic',
					'fixed' => true
				]))
			);

			self::load_scripts();

			add_action( 'wp_footer', [$this, 'after_add_markup']);
		}

		function lazy_load_markup(){
			add_action( 'wp_footer', [$this, 'after_add_markup']);
		}

		function pdp_button(){

			if( !get_theme_mod('compare__pdp_enable', true) ){
				return;
			}

			$position = get_theme_mod('compare__pdp_position', 'after');

			$hooks = [
				'before' => [
					'hook' => 'woocommerce_before_add_to_cart_form',
					'priority' => 10
				],
				'inline' => [
					'hook' => 'woocommerce_after_add_to_cart_button',
					'priority' => 2
				],
				'after' => [
					'hook' => 'reycore/woocommerce/single/after_add_to_cart_form',
					'priority' => 0
				],
				'not_purchasable' => [
					'hook' => 'woocommerce_single_product_summary',
					'priority' => 25
				],
			];

			if ( ($product = wc_get_product()) && ! $product->is_purchasable() ) {
				$position = 'not_purchasable';
			}

			add_action( $hooks[$position]['hook'], [$this, 'output_pdp_button'], $hooks[$position]['priority'] );

		}

		function output_pdp_button(){

			$product = wc_get_product();

			if ( ! ($product && $id = $product->get_id()) ) {
				return $btn_html;
			}

			reyCoreAssets()->add_scripts('simple-scrollbar');
			reyCoreAssets()->add_styles('simple-scrollbar');

			$button_class = $text_class = [];
			$active_products = self::get_ids();

			$button_text = self::get_texts('add');

			if( !empty($active_products) && in_array($id, $active_products, true) ){
				$button_class[] = '--in-compare';
				$button_text = self::get_texts('rm');
			}

			if( is_user_logged_in() ){
				$button_class[] = '--supports-ajax';
			}

			$button_content = self::get_compare_icon();

			if( ($btn_style = get_theme_mod('compare__pdp_btn_style', 'btn-line')) && $btn_style !== 'none' ){

				$button_class['btn_style'] = 'btn ' . $btn_style;

				// disable line buttons
				if( in_array($btn_style, ['btn-line', 'btn-line-active'], true) ){
					$text_class['text_style'] = 'btn ' . $btn_style;
					$button_class['btn_style'] = 'btn --btn-text';
				}
			}

			$text_visibility = get_theme_mod('compare__pdp_wtext', 'show_desktop');

			if( $text_visibility && $button_text ){

				if( $text_visibility === 'show_desktop' ){
					$text_class[] = '--dnone-mobile --dnone-tablet';
				}

				$button_content .= sprintf('<span class="rey-compareBtn-text %s">%s</span>', esc_attr(implode(' ', $text_class)), $button_text);

			}

			$attributes = [];

			// only when text is hidden
			if( $text_visibility === '' && get_theme_mod('compare__pdp_tooltip', false) ){
				$attributes['data-rey-tooltip'] = wp_json_encode([
					'text' => $button_text,
					'class' => '--basic',
					'fixed' => true
				]);
			}

			$btn_html = sprintf(
				'<div class="rey-compareBtn-wrapper"><a href="%5$s" class="%1$s rey-compareBtn" data-id="%2$s" title="%3$s" aria-label="%3$s">%4$s</a></div>',
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

		public static function get_compare_icon( $class = '' ){
			return reycore__get_svg_icon__core([
				'id' => 'reycore-icon-compare',
				'class' => 'rey-compareBtn-icon ' . $class
			]);
		}

		/**
		 * Compare page
		 */

		public static function compare_page_id(){

			if( $compare_page_id = get_theme_mod('compare__default_url', '') ){
				return absint($compare_page_id);
			}

			return null;
		}

		public static function compare_url( $url = '' ){

			if( $compare_page_id = self::$compare_page_id ){
				return esc_url( get_permalink($compare_page_id) );
			}

			return $url;
		}

		function append_compare_page_class($classes){

			$classes[] = 'rey-compare';

			if( ($compare_page_id = self::$compare_page_id) && is_page($compare_page_id) ){
				$classes[] = 'woocommerce';
				$classes[] = 'rey-compare-page';
			}

			return $classes;
		}

		function apply_filter_content(){

			if( !($compare_page_id = self::$compare_page_id) ){
				return;
			}

			if( ! is_page($compare_page_id) ){
				return;
			}

			add_filter( 'the_content', [$this, 'append_page_content']);
			remove_all_actions('rey/content/title');
		}

		function remove_filter_content(){
			remove_filter( 'the_content', [$this, 'append_page_content']);
		}

		function append_page_content( $content ){

			if( function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode() ){
				return $content;
			}

			if( !is_main_query() ){
				return $content;
			}

			self::load_scripts();

			add_filter('comments_open', '__return_false', 20, 2);
			add_filter('pings_open', '__return_false', 20, 2);
			add_filter('comments_array', '__return_empty_array', 10, 2);

			ob_start(); ?>

				<div class="rey-compareWrapper --empty"></div>

				<div class="rey-compare-emptyPage">

					<div class="rey-compare-emptyPage-icon">
						<?php echo self::get_compare_icon(); ?>
					</div>

					<div class="rey-compare-emptyPage-title">
						<h2><?php echo self::get_texts('page_title'); ?></h2>
					</div>

					<div class="rey-compare-emptyPage-content">
						<p><?php echo self::get_texts('page_text'); ?></p>
						<a href="<?php echo get_permalink( wc_get_page_id( 'shop' ) ) ?>" class="btn btn-primary">
							<?php echo self::get_texts('page_btn_text') ?>
						</a>
					</div>
				</div>

				<div class="rey-lineLoader rey-compareLoader"></div>

			<?php
			$w_content = ob_get_clean();

			if( apply_filters('reycore/woocommerce/compare/empty_page', true) ){
				return $w_content;
			}
			else {
				return $content . $w_content;
			}

		}

		public function get_page_content(){

			if( ! $this->is_enabled ){
				wp_send_json_error();
			}

			ob_start();
			reycore__get_template_part('inc/modules/compare/compare-page');
			$data = ob_get_clean();

			wp_send_json_success($data);
		}

		function after_add_markup(){

			if( $compare_page_id = self::$compare_page_id ){
				if( is_page($compare_page_id) ){
					return;
				}
			}

			$type = get_theme_mod('compare__after_add', 'notice');

			if( $type === 'notice' ){
				?>
				<div class="rey-compareNotice-wrapper --hidden">
					<div class="rey-compareNotice">
						<div class="rey-compareNotice-inner">
							<div class="rey-compareIcon">
								<?php echo self::get_compare_icon(); ?>
								<a href="#" class="rey-compareClose" data-tooltip-text="<?php echo self::get_texts('close') ?>"><?php echo reycore__get_svg_icon(['id' => 'rey-icon-close']) ?></a>
							</div>
							<div class="rey-compareTitle">
								<h4><?php echo self::get_texts('compare__text'); ?></h4>
								<div class="rey-compareTitle-count">
									<?php echo $this->compare_counter_html(); ?> <?php echo self::get_texts('products') ?>
								</div>
								<div class="rey-lineLoader"></div>
							</div>
							<a href="#" class="btn btn-line rey-compare-recentBtn">
								<span class="--dnone-md --dnone-sm"><?php echo self::get_texts('recently_viewed') ?></span>
								<?php echo reycore__get_svg_icon__core(['id' => 'reycore-icon-grid', 'class' => '__mobile --dnone-lg']) ?>
								<?php echo reycore__get_svg_icon__core(['id' => 'reycore-icon-arrow', 'class' => '__inactive']) ?>
								<?php echo reycore__get_svg_icon(['id' => 'rey-icon-close', 'class' => '__active']) ?>
							</a>
							<a href="#" class="btn btn-line rey-compare-resetBtn">
								<?php
									printf('<span class="--dnone-md --dnone-sm">%s</span>', self::get_texts('reset_list'));
									printf('<span class="--dnone-lg">%s</span>', self::get_texts('reset_list_mobile'));
								?>
							</a>
							<?php if( $compare_url = self::compare_url() ){
								$compare_text = sprintf('<span class="--dnone-md --dnone-sm">%s</span>', self::get_texts('btn'));
								printf('<a href="%1$s" class="btn btn-primary rey-compare-compareBtn">%2$s</a>',
									$compare_url,
									$compare_text . self::get_compare_icon('--dnone-lg')
								);
							} ?>
						</div>
						<div class="rey-compareNotice-recentProducts">
							<div class="rey-compareNotice-recentProducts-inner"></div>
							<div class="rey-lineLoader"></div>
						</div>
					</div>
				</div>

				<?php
			}
		}

		function add_compare_page_to_account_menu($items){

			$c = false;

			if( isset($items['customer-logout']) ){
				$c = $items['customer-logout'];
				unset($items['customer-logout']);
			}

			if( self::$compare_page_id ){

				$counter = '';

				if( ! is_account_page() ){
					$counter = sprintf(' <span class="acc-count">%s</span>', $this->compare_counter_html() );
				}

				$items['rey_compare'] = $this->compare_title() . $counter;
			}

			if( $c ){
				$items['customer-logout'] = $c;
			}

			return $items;
		}

		function add_compare_url_endpoint($url, $endpoint, $value, $permalink){

			if( $endpoint === 'rey_compare') {
				$url = self::compare_url();
			}

			return $url;
		}

		function compare_counter_html(){
			return '<span class="rey-compareCounter-number --empty">0</span>';
		}

		function compare_title(){
			return self::get_texts('compare__text');
		}

		function get_compare_ids( $ids ){

			$product_ids = self::get_ids();

			if( empty($product_ids) ){
				return $ids;
			}

			return array_reverse($product_ids);
		}

		public function add_to_user_meta(){

			if( ! $this->is_enabled ){
				wp_send_json_error();
			}

			if( ! is_user_logged_in() ){
				wp_send_json_error(esc_html__('Not logged in!', 'rey-core'));
			}

			$user = wp_get_current_user();
			$product_ids = self::get_cookie_products_ids();

			if( update_user_meta($user->ID, self::get_cookie_key(), $product_ids) ){
				wp_send_json_success($product_ids);
			}

		}

		public function update_ids_after_login( $user_login, $user){

			$product_ids = self::get_cookie_products_ids();
			$saved_product_ids = get_user_meta($user->ID, self::get_cookie_key(), true);

			if( ! is_array($saved_product_ids) ) {
				$saved_product_ids = [];
			}

			update_user_meta($user->ID, self::get_cookie_key(), array_unique( array_merge($product_ids, $saved_product_ids) ) );
		}

		function track_products() {

			$track = false;

			if ( is_singular( 'product' ) ) {
				$track = true;
			}

			$is_quickview = get_query_var('rey__is_quickview', false) === true;

			if( $is_quickview ){
				$track = true;
			}

			$track = apply_filters('reycore/woocommerce/track_product_view', $track);

			if ( ! $track ) {
				return;
			}

			global $post;

			$viewed_products = [];

			if ( ! empty( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) { // @codingStandardsIgnoreLine.
				$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) ); // @codingStandardsIgnoreLine.
			}

			$product_id = $post->ID;

			if( (is_tax() || is_shop()) && ! $is_quickview ){
				$product_id = '';
			}

			// Unset if already in viewed products list.
			$keys = array_flip( $viewed_products );

			if ( isset( $keys[ $product_id ] ) ) {
				unset( $viewed_products[ $keys[ $product_id ] ] );
			}

			if( $product_id ){
				$viewed_products[] = $product_id;
			}


			if ( count( $viewed_products ) > 15 ) {
				array_shift( $viewed_products );
			}

			// Store for session only.
			wc_setcookie( self::get_cookie_key('recently_viewed'), implode( '|', $viewed_products ) );
		}

		function get_tracked_products(){

			$products = [];

			if ( ! empty( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) { // @codingStandardsIgnoreLine.
				$products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key('recently_viewed')] ) ) ); // @codingStandardsIgnoreLine.
			}

			return $products;
		}

		function get_viewed_products(){

			if( ! $this->is_enabled ){
				wp_send_json_error();
			}

			$ids = $this->get_tracked_products();

			if( empty($ids) ){
				wp_send_json_success(self::get_texts('no_products'));
			}

			$html = '';

			foreach ($ids as $key => $pid) {
				$product = wc_get_product($pid);

				if( ! $product ){
					continue;
				}

				$html .= '<li>';

					$html .= wp_get_attachment_image($product->get_image_id(), 'thumbnail');
					$html .= sprintf('<h4><a href="%2$s">%1$s</a></h4>', $product->get_title(), esc_url( get_the_permalink( $pid ) ));
					$html .= $product->get_price_html();
					$html .= sprintf('<a href="#" class="btn btn-line-active rey-compare-recentProducts-add" data-id="%d">%s</a>', $pid, self::get_texts('recently_viewed_add'));

				$html .= '</li>';

			}

			if( $html ){

				$content = '<ul class="rey-compare-recentProducts">';
				$content .= $html;
				$content .= '</ul>';

				wp_send_json_success($content);
			}

			wp_send_json_success();
		}

		public static function fields( $with_attr = true ) {

	        $fields = [
				'image'       => __( 'Image', 'rey-core' ),
                'title'       => __( 'Title', 'rey-core' ),
                'description' => __( 'Description', 'rey-core' ),
                'sku'         => __( 'Sku', 'rey-core' ),
                'stock'       => __( 'Availability', 'rey-core' ),
                'weight'      => __( 'Weight', 'rey-core' ),
                'dimensions'  => __( 'Dimensions', 'rey-core' ),
			];

	        if( $with_attr ){
	            $fields = array_merge( $fields, self::attribute_taxonomies() );
			}

			$fields['price'] = __( 'Price', 'rey-core' );
			$fields['add-to-cart'] = __( 'Add to cart', 'rey-core' );

			if( $product = wc_get_product() ){
				$fields['add-to-cart'] = $product->single_add_to_cart_text();
			}

	        return apply_filters( 'reycore/woocommerce/compare/fields', $fields );
		}

		public static function attribute_taxonomies() {

            $attributes = [];

			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if( empty( $attribute_taxonomies ) )
				return [];
			foreach( $attribute_taxonomies as $attribute ) {
				$tax = wc_attribute_taxonomy_name( $attribute->attribute_name );
				if ( taxonomy_exists( $tax ) ) {
					$attributes[$tax] = $attribute->attribute_label;
				}
			}

            return $attributes;
        }

		function is_enabled(){
			return $this->is_enabled;
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

	ReyCore_WooCommerce_CompareRey::getInstance();

endif;
