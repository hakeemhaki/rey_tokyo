<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'woocommerce_template_loop_product_title' ) ):
	/**
	 * Override native function, by adding link into H2 tag.
	 *
	 * Show the product title in the product loop. By default this is an H2.
	 *
	 * @since 1.0.0
	 */
	function woocommerce_template_loop_product_title() {
		global $product;

		echo sprintf(
			'<%4$s class="%1$s"><a href="%2$s">%3$s</a></%4$s>',
			esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ),
			esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
			get_the_title(),
			esc_attr( apply_filters( 'woocommerce_product_loop_title_tag', 'h2', $product ) ) );

	}
endif;


if(!function_exists('reycore_wc__add_account_btn')):
	/**
	 * Add account button and panel markup
	 * @since 1.0.0
	 **/
	function reycore_wc__add_account_btn(){
		if( get_theme_mod('header_enable_account', false) ) {
			reycore__get_template_part('template-parts/woocommerce/header-account');
			// load panel markup
			add_action('rey/after_site_wrapper', 'reycore_wc__add_account_panel');
		}
	}
endif;
add_action('rey/header/row', 'reycore_wc__add_account_btn', 50);


if(!function_exists('reycore_wc__get_account_panel_args')):
	/**
	 * Get account panel options
	 * @since 1.0.0
	 **/
	function reycore_wc__get_account_panel_args( $option = '' ){

		$options = apply_filters('rey/header/account_params', [
			'enabled' => get_theme_mod('header_enable_account', false),
			'button_type' => get_theme_mod('header_account_type', 'text'),
			'button_text' => get_theme_mod('header_account_text', 'ACCOUNT'),
			'button_text_logged_in' => get_theme_mod('header_account_text_logged_in', ''),
			'icon_type' => get_theme_mod('header_account_icon_type', 'rey-icon-user'),
			'wishlist' =>  get_theme_mod('header_account_wishlist', true) && reycore_wc__check_wishlist(),
			'counter' => get_theme_mod('header_account_wishlist_counter', true) && reycore_wc__check_wishlist(),
			'wishlist_prod_layout' => 'grid',
			'show_separately' => true,
			'login_register_redirect' => get_theme_mod('header_account_redirect_type', 'load_menu'),
			'login_register_redirect_url' => get_theme_mod('header_account_redirect_url', ''),
			'ajax_forms' => get_theme_mod('header_account__enable_ajax', apply_filters('reycore/header/account/ajax_forms', true)),
			'forms' => get_theme_mod('header_account__enable_forms', true),
			'display' => get_theme_mod('header_account__panel_display', 'drop'),
			'drop_close_on_scroll' => get_theme_mod('header_account__close_on_scroll', true)
		] );

		if( !empty($option) && isset($options[$option]) ){
			return $options[$option];
		}

		return $options;
	}
endif;


if(!function_exists('reycore_wc__account_nav_wrap_start')):
	function reycore_wc__account_nav_wrap_start() {
		?>
			<div class="woocommerce-MyAccount-navigation-wrapper" <?php reycore_wc__account_redirect_attrs() ?>>
		<?php
	}
	add_action('woocommerce_before_account_navigation', 'reycore_wc__account_nav_wrap_start');
	add_action('woocommerce_after_account_navigation', 'reycore_wc__generic_wrapper_end', 20);
endif;

if(!function_exists('reycore_wc__account_custom_nav')):
	/**
	 * Header Account custom menu items
	 *
	 * @since 1.6.3
	 **/
	function reycore_wc__account_custom_nav() {

		if( ! (($menu_items = get_theme_mod('header_account_menu_items', [])) && is_array($menu_items) ) ){
			return;
		}

		$class = '';

		if( get_theme_mod('header_account_menu_items__glue', true) ){

			$logout['text'] = esc_html__( 'Logout', 'rey-core' );
			$logout['url'] = esc_url( wc_get_account_endpoint_url( 'customer-logout' ) );
			$logout['target'] = '';
			$menu_items[] = $logout;

			$class = '--merged';
		}

		?>
		<nav class="woocommerce-MyAccount-navigation --custom <?php echo esc_attr($class) ?>">
			<ul>
				<?php foreach ( $menu_items as $menu_item ) : ?>
					<li class="myaccount-nav-<?php echo sanitize_title_with_dashes($menu_item['text']) ?>">
						<a href="<?php echo esc_url( $menu_item['url'] ); ?>" target="<?php echo esc_attr($menu_item['target']) ?>"><?php echo esc_html($menu_item['text']) ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav> <?php
	}
	add_action('woocommerce_after_account_navigation', 'reycore_wc__account_custom_nav');
endif;

if(!function_exists('reycore_wc__header_account_merge_navs')):
	/**
	 * Add custom menu icons
	 *
	 * @since 1.6.10
	 **/
	function reycore_wc__header_account_merge_navs($items, $endpoints)
	{
		if( ! get_theme_mod('header_account_menu_items__glue', true) ){
			return $items;
		}

		if( ! (($menu_items = get_theme_mod('header_account_menu_items', [])) && is_array($menu_items) ) ){
			return $items;
		}

		// unset so it can be added in the other menu
		unset($items['customer-logout']);

		return $items;
	}
	add_filter('woocommerce_account_menu_items', 'reycore_wc__header_account_merge_navs', 10, 2);
endif;


if(!function_exists('reycore_wc__account_redirect_attrs')):
/**
 * Redirect attributes for account panel containing login register forms
 *
 * @since 1.4.5
 **/
function reycore_wc__account_redirect_attrs( $args = [] )
{
	$args = wp_parse_args($args, reycore_wc__get_account_panel_args());

	$redirect_type = $args['login_register_redirect'];
	$redirect_url = $args['login_register_redirect_url'];

	if( $redirect_type === 'myaccount' ){

		$redirect_url = wc_get_page_permalink( 'myaccount' );

		if( is_user_logged_in() ){
			$redirect_url = apply_filters( 'woocommerce_login_redirect', $redirect_url, wp_get_current_user() );
		}

	}

	printf( 'data-redirect-type="%s" data-redirect-url="%s" %s',
		esc_attr($redirect_type),
		esc_attr($redirect_url),
		! $args['ajax_forms'] ? 'data-no-ajax' : ''
	);

}
endif;


if(!function_exists('reycore_wc__add_account_panel')):
	/**
	 * Add account button and panel markup
	 * @since 1.0.0
	 **/
	function reycore_wc__add_account_panel(){

		if( reycore_wc__get_account_panel_args('enabled') ) {

			reycore__get_template_part('template-parts/woocommerce/header-account-panel');

			// assets
			reyCoreAssets()->add_styles([
				'rey-wc-header-account-panel-top',
				'rey-wc-header-account-panel',
				'rey-wc-header-wishlist',
			]);

			reyCoreAssets()->add_scripts([
				'reycore-woocommerce',
				'reycore-wc-header-account-panel',
				'reycore-wc-header-wishlist',
				'wp-util',
			]);
		}
	}
endif;


if(!function_exists('reycore_wc__generic_wrapper_end')):
	/**
	 * Ending wrapper
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__generic_wrapper_end()
	{ ?>
		</div>
	<?php }
endif;


if( !function_exists('reycore_wc__checkout_required_span') ):

	/**
	 * Add the required mark to the terms & comditions text
	 * to maintain it on the same line visually.
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__checkout_required_span($text)
	{
		return $text . '<span class="required">*</span>';
	}
endif;
add_filter('woocommerce_get_terms_and_conditions_checkbox_text', 'reycore_wc__checkout_required_span');


if(!function_exists('reycore_wc__placeholder_img_src')):
	/**
	 * Placeholder
	 */
	function reycore_wc__placeholder_img_src( $placeholder ) {

		if( strpos($placeholder, 'woocommerce-placeholder.png') !== false ){
			return defined('REY_CORE_PLACEHOLDER') ? REY_CORE_PLACEHOLDER : $placeholder;
		}

		return $placeholder;
	}
endif;
add_filter('woocommerce_placeholder_img_src', 'reycore_wc__placeholder_img_src');
// add_filter( 'option_woocommerce_placeholder_image', 'reycore_wc__placeholder_img_src' );


if(!function_exists('reycore_wc__get_product_images_ids')):
	/**
	 * Get product's image ids
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__get_product_images_ids( $add_main = true )
	{
		$product = wc_get_product();
		$ids = [];

		if( $product && $main_image_id = $product->get_image_id() ){

			if( $add_main ){
				// get main image' id
				$ids[] = $main_image_id;
			}

			// get gallery
			if( $gallery_image_ids = $product->get_gallery_image_ids() ){
				foreach ($gallery_image_ids as $key => $gallery_img_id) {
					$ids[] = $gallery_img_id;
				}
			}
		}

		return apply_filters('reycore/woocommerce/product_image_gallery_ids', $ids);
	}
endif;


if(!function_exists('reycore_wc__add_mobile_nav_link')):
	/**
	 * Adds dashboard (my account) link into Mobile navigation's footer
	 * @since 1.0.0
	 */
	function reycore_wc__add_mobile_nav_link(){

		$show_account_links = true;

		if( get_theme_mod('shop_catalog', false) === true && apply_filters('reycore/catalog_mode/hide_account', false) ){
			$show_account_links = false;
		}

		if( $show_account_links ) {
			reycore__get_template_part('template-parts/woocommerce/header-mobile-navigation-footer-link');
		}
	}
endif;
add_action('rey/mobile_nav/footer', 'reycore_wc__add_mobile_nav_link', 5);


if(!function_exists('reycore_wc__exclude_products_from_cats')):
	/**
	 * Exclude categories from shop page query
	 *
	 * @since 1.2.0
	 **/
	function reycore_wc__exclude_products_from_cats( $q )
	{
		if( ! ($exclude_cats = reycore__get_theme_mod('shop_catalog_page_exclude', [], [
				'translate' => true,
				'translate_post_type' => 'product_cat',
			])) ){
			return;
		}

		if(!is_shop()){
			return;
		}

		$tax_query = (array) $q->get( 'tax_query' );

		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field' => isset($exclude_cats[0]) && is_numeric($exclude_cats[0]) ? 'term_id' : 'slug',
			'terms' => $exclude_cats,
			'operator' => 'NOT IN'
		);

		$q->set( 'tax_query', $tax_query );
	}
endif;
add_action('woocommerce_product_query', 'reycore_wc__exclude_products_from_cats');

if(!function_exists('reycore_wc__exclude_cats')):
	/**
	 * Exclude categories
	 *
	 * @since 1.6.10
	 **/
	function reycore_wc__exclude_cats($args)
	{
		if( ! ($exclude_cats = reycore__get_theme_mod('shop_catalog_page_exclude', [], [
			'translate' => true,
			'translate_post_type' => 'product_cat',
		])) ){
			return $args;
		}

		$terms_ids = [];

		foreach ($exclude_cats as $term_slug) {
			$term = get_term_by('slug', $term_slug, 'product_cat');
			if( isset($term->term_id) ){
				$terms_ids[] = $term->term_id;
			}
		}

		if( !empty($terms_ids) ){
			$args['exclude'] = $terms_ids;
		}

		return $args;
	}
	add_filter('woocommerce_product_subcategories_args', 'reycore_wc__exclude_cats');
endif;




if(!function_exists('reycore_wc__format_price_range')):
	/**
	 * Remove dash from grouped products
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__format_price_range( $price, $from, $to ) {

		$min = is_numeric( $from ) ? wc_price( $from ) : $from;
		$max = is_numeric( $to ) ? wc_price( $to ) : $to;

		/* translators: 1: price from 2: price to */
		$price = sprintf(
			esc_html_x( '%1$s %2$s', 'Price range: from-to', 'rey-core' ),
			$min,
			$max
		);

		if( $custom_price_range = get_theme_mod('custom_price_range', '') ){

			$custom_price_range = explode(' ', $custom_price_range);
			$custom_price_range = array_map(function($arr) use ($min, $max){
				$arr = str_replace('{{min}}', $min, $arr);
				$arr = str_replace('{{max}}', $max, $arr);
				return "<span class='__custom-price-range'>{$arr}</span>";
			}, $custom_price_range);

			return implode('', $custom_price_range);
		}


		return $price;
	}
endif;
add_filter('woocommerce_format_price_range', 'reycore_wc__format_price_range', 10, 3);


if(!function_exists('reycore_wc__move_banner')):
	/**
	 * Move WooCommerce banner store
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__move_banner()
	{
		remove_action( 'wp_footer', 'woocommerce_demo_store' );

		$hook = 'rey/before_site_wrapper';

		if( get_theme_mod('header_layout_type', 'default') !== 'none' ){
			$hook = 'rey/header/content';
		}

		add_action( $hook, 'woocommerce_demo_store', 5 );
	}
endif;
add_action( 'wp', 'reycore_wc__move_banner' );


if(!function_exists('reycore_wc__qty_input_select')):
	/**
	 * Adds the ability to select number on focus.
	 *
	 * @since 1.3.5
	 */
	function reycore_wc__qty_input_select( $classes ) {
		$classes['select'] = '--select-text';
		return $classes;

	}
endif;
add_filter('woocommerce_quantity_input_classes', 'reycore_wc__qty_input_select');


if( ! function_exists( 'reycore_wc__ajax_add_to_cart' ) ):

	function reycore_wc__ajax_add_to_cart() {

		$data = [];

		// Notices
		ob_start();
		wc_print_notices();
		$data['notices'] = ob_get_clean();

		// Mini cart
		ob_start();
		woocommerce_mini_cart();
		$data['fragments']['div.widget_shopping_cart_content'] = sprintf('<div class="widget_shopping_cart_content">%s</div>', ob_get_clean() );
		$data['fragments'] = apply_filters( 'woocommerce_add_to_cart_fragments', $data['fragments']);

		// Cart Hash
		$data['cart_hash'] = apply_filters( 'woocommerce_add_to_cart_hash',
			WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '',
			WC()->cart->get_cart_for_session()
		);

		$data = apply_filters('reycore/woocommerce/cart/data', $data);

		wp_send_json( $data );
		die();
	}
endif;
add_action( 'wp_ajax_reycore_ajax_add_to_cart', 'reycore_wc__ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_reycore_ajax_add_to_cart', 'reycore_wc__ajax_add_to_cart' );

if(!function_exists('reycore_wc__add_variation_cart_scripts')):
	/**
	 * Add variations add to cart script
	 *
	 * @since 1.4.0
	 **/
	function reycore_wc__add_variation_cart_scripts()
	{
		if( ! get_theme_mod('loop_ajax_variable_products', false) ){
			return;
		}
		// Enqueue variation scripts.
		wp_enqueue_script( 'wc-add-to-cart-variation' );
	}
endif;
add_action( 'wp_enqueue_scripts', 'reycore_wc__add_variation_cart_scripts' );


if(!function_exists('reycore__woocommerce_filter_js')):
	/**
	 * Filter WC JS
	 *
	 * @since 1.0.0
	 **/
	function reycore__woocommerce_filter_js($js)
	{
		$search_for = '.selectWoo( {';
		$replace_with = '.selectWoo( {';
		$replace_with .= 'containerCssClass: "select2-reyStyles",';
		$replace_with .= 'dropdownCssClass: "select2-reyStyles",';
		$replace_with .= 'dropdownAutoWidth: true,';
		$replace_with .= 'width: "auto",';

		return str_replace($search_for, $replace_with, $js);
	}
endif;
add_filter('woocommerce_queued_js', 'reycore__woocommerce_filter_js');


if(!function_exists('reycore_wc__related_change_cols')):
	/**
	 * Filter related products columns no.
	 *
	 * @since 1.5.0
	 **/
	function reycore_wc__related_change_cols( $args )
	{
		$args['posts_per_page'] = reycore_wc_get_columns('desktop');
		$args['columns'] = reycore_wc_get_columns('desktop');

		return $args;
	}
add_filter('woocommerce_output_related_products_args', 'reycore_wc__related_change_cols', 10);
endif;


if(!function_exists('reycore_wc__track_product_view')):
	/**
	 * Track product views.
	 */
	function reycore_wc__track_product_view() {

		$track = false;

		if ( is_singular( 'product' ) ) {
			$track = true;
		}

		if( get_query_var('rey__is_quickview', false) === true ){
			$track = true;
		}

		$track = apply_filters('reycore/woocommerce/track_product_view', $track);

		if ( ! $track ) {
			return;
		}

		global $post;

		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
			$viewed_products = array();
		} else {
			$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
		}

		// Unset if already in viewed products list.
		$keys = array_flip( $viewed_products );

		if ( isset( $keys[ $post->ID ] ) ) {
			unset( $viewed_products[ $keys[ $post->ID ] ] );
		}

		$viewed_products[] = $post->ID;

		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		// Store for session only.
		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}
	add_action( 'template_redirect', 'reycore_wc__track_product_view', 20 );
	add_action( 'reycore/woocommerce/quickview/before_render', 'reycore_wc__track_product_view', 20 );
endif;


if(!function_exists('reycore_wc__fix_variable_sale_product_prices')):
	/**
	 * Fix variable products on sale price display
	 *
	 * @since 1.6.6
	 **/
	function reycore_wc__fix_variable_sale_product_prices($price, $product)
	{
		if( ! get_theme_mod('fix_variable_product_prices', false) ){
			return $price;
		}

		if( ! $product->is_on_sale() ){
			return $price;
		}

		$show_from_in_price = false;

		// Regular Price
		$regular_prices = [
			$product->get_variation_regular_price( 'min', true ),
			$product->get_variation_regular_price( 'max', true )
		];

		$flexible_regular_prices = $regular_prices[0] !== $regular_prices[1];

		if( $show_from_in_price ){
			$regular_price = $flexible_variables_prices ? sprintf( '<span class="woocommerce-Price-from">%1$s</span> %2$s', esc_html__('From:', 'woocommerce'), wc_price( $regular_prices[0] ) ) : wc_price( $regular_prices[0] );
		}
		else {
			$regular_price = wc_price( $regular_prices[0] );
		}

		// Sale Price
		$prod_prices = [
			$product->get_variation_price( 'min', true ),
			$product->get_variation_price( 'max', true )
		];

		if( $show_from_in_price ){
			$prod_price = $prod_prices[0] !== $prod_prices[1] ? sprintf( '<span class="woocommerce-Price-from">%1$s</span> %2$s', esc_html__('From:', 'woocommerce'), wc_price( $prod_prices[0] ) ) : wc_price( $prod_prices[0] );
		}
		else {
			$prod_price = wc_price( $prod_prices[0] );
		}

		if ( $prod_price !== $regular_price ) {
			$prod_price = sprintf('<del>%s</del> <ins>%s</ins>', $regular_price . $product->get_price_suffix(), $prod_price . $product->get_price_suffix() );
		}

		return $prod_price;
	}
	add_filter( 'woocommerce_variable_price_html', 'reycore_wc__fix_variable_sale_product_prices', 10, 2 );
endif;


if(!function_exists('reycore_wc__archive_title_back_button')):
	/**
	 * Add back button to archive titles
	 *
	 * @since 1.6.13
	 **/
	function reycore_wc__archive_title_back_button( $title )
	{

		if( ! get_theme_mod('archive__title_back', false) ){
			return $title;
		}

		if( is_shop() ){
			return $title;
		}

		$id = wc_get_page_id( 'shop' );
		$url = '';
		$behaviour = get_theme_mod('archive__back_behaviour', 'parent');
		$shop_url = get_permalink(wc_get_page_id( 'shop' ));
		$prev_url = 'javascript:window.history.back();';

		if ( is_search() ) {

			$url = $shop_url;

			if( 'page' === $behaviour ){
				$url = $prev_url;
			}
		}

		elseif ( is_tax() ) {

			if( 'parent' === $behaviour ){
				if( ($this_term = get_term(get_queried_object_id())) && isset($this_term->parent) && $this_term->parent !== 0 ){
					$url = get_term_link($this_term->parent);
				}
			}
			elseif( 'shop' === $behaviour ){
				$url = $shop_url;
			}
			elseif( 'page' === $behaviour ){
				$url = $prev_url;
			}
		}

		if( ! $url ){
			return $title;
		}

		$btn = sprintf('<a href="%2$s" class="rey-titleBack">%1$s</a>', reycore__arrowSvg(false), $url);

		return "{$btn}<span>{$title}</span>";
	}
	add_filter('woocommerce_page_title', 'reycore_wc__archive_title_back_button');
endif;


if(!function_exists('reycore__disable_wc_redirect_after_add')):
	/**
	 * Disable "Redirect to the cart page after successful addition" woocommerce option
	 * if Rey's after add to cart option is not disabled.
	 *
	 * @since 1.8.1
	 */
	function reycore__disable_wc_redirect_after_add( $wp_customizer )
	{
		if( get_theme_mod('product_page_after_add_to_cart_behviour', 'cart') !== '' ){
			delete_option('woocommerce_cart_redirect_after_add');
		}
	}
endif;
add_action('customize_save_after', 'reycore__disable_wc_redirect_after_add', 20);


function recore_wc__noscript() {
	?>
	<noscript>
		<style>
		.woocommerce ul.products li.product.is-animated-entry {
			opacity: 1;
			transform: none;
		}
		.woocommerce div.product .woocommerce-product-gallery:after {
			display: none;
		}
		.woocommerce div.product .woocommerce-product-gallery .woocommerce-product-gallery__wrapper {
			opacity: 1;
		}
		</style>
	</noscript>
	<?php
}
add_action( 'wp_head', 'recore_wc__noscript' );


/**
 * Hide discount label if "hide prices for logged out visitors" is enabled
 *
 * @since 1.9.7
 */
add_filter('theme_mod_loop_show_sale_label', function($status){

	if( get_theme_mod('shop_hide_prices_logged_out', false) && ! is_user_logged_in() ){
		return false;
	}

	return $status;

}, 20);


/**
 * Hide prices if "hide prices for logged out visitors" is enabled
 *
 * @since 1.9.7
 */
add_filter( 'woocommerce_get_price_html', function($html, $product){

	if( get_theme_mod('shop_hide_prices_logged_out', false) && ! is_user_logged_in() ){

		if( $custom_text = get_theme_mod('shop_hide_prices_logged_out_text', '') ){
			return '<span class="woocommerce-Price-amount">'. $custom_text .'</span>';
		}

		return '';
	}

	return $html;
}, 100, 2);

add_filter( 'woocommerce_is_purchasable', function($status){

	if( get_theme_mod('shop_hide_prices_logged_out', false) && ! is_user_logged_in() ){
		return false;
	}

	return $status;
});


/**
 * Hide variations that are out of stock.
 * @since 2.0.5
 */
add_filter( 'woocommerce_variation_is_active', function( $is_active, $variation ) {

	if( ! get_theme_mod('single_product_hide_out_of_stock_variation', true) ){
		return $is_active;
	}

	if ( ! $variation->is_in_stock() ) {
		return false;
	}

	return $is_active;
}, 10, 2 );


if(!function_exists('reycore_wc__get_default_variation')):

	function reycore_wc__get_default_variation( $product ){

		if( ! $product->is_type('variable') ){
			return;
		}

		$default_attributes = $product->get_default_attributes();

		if( empty( $default_attributes ) ){
			return;
		}

		if( ! ($available_variations = $product->get_available_variations()) ){
			return;
		}

		// make sure all attributes are selected as default
		if( count($available_variations[0]['attributes']) !== count($default_attributes) ){
			return;
		}

		$variation_id = false;

		foreach($available_variations as $variation_values ){

			if( $variation_id ){
				continue;
			}

			foreach($variation_values['attributes'] as $key => $attribute_value ){

				if( $variation_id ){
					continue;
				}

				$attribute_name = str_replace( 'attribute_', '', $key );

				if( isset($default_attributes[$attribute_name]) && $default_attributes[$attribute_name] === $attribute_value ){
					$variation_id = $variation_values['variation_id'];
				}
			}
		}

		return $variation_id;
	}
endif;


add_filter('woocommerce_before_output_product_categories', function($content){

	if( ! (($display_type = woocommerce_get_loop_display_mode()) && 'both' === $display_type) ){
		return $content;
	}

	if( ! get_theme_mod('shop_display_categories__enable', false) ){
		return $content;
	}

	$classes = ['product-category', 'product', 'rey-categories-loop', '--before'];

	if( get_theme_mod('loop_animate_in', true) ){
		$classes['animated-entry'] = 'is-animated-entry';
	}

	return $content . sprintf(
		'<li class="%2$s"><%1$s>%3$s</%1$s></li>',
		apply_filters('reycore/woocommerce/loop/shop_display_categories/tag', 'h2'),
		implode(' ', $classes),
		get_theme_mod('shop_display_categories__title_cat', esc_html__('Shop by Category', 'rey-core'))
	);
});

add_filter('woocommerce_after_output_product_categories', function($content){

	if( ! (($display_type = woocommerce_get_loop_display_mode()) && 'both' === $display_type) ){
		return $content;
	}

	if( ! get_theme_mod('shop_display_categories__enable', false) ){
		return $content;
	}

	$classes = ['product-category', 'product', 'rey-categories-loop', '--after'];

	if( get_theme_mod('loop_animate_in', true) ){
		$classes['animated-entry'] = 'is-animated-entry';
	}

	$title = esc_html__('Products', 'rey-core');

	if( is_product_category() || is_product_tag() || is_product_taxonomy() ){
		$title = reycore__get_page_title();
	}

	$text = get_theme_mod('shop_display_categories__title_prod', esc_html__('Shop All %s', 'rey-core'));

	$the_title = strpos($text, '%s') !== false ? sprintf($text, $title) : $text;

	return $content . sprintf(
		'<li class="%2$s"><%1$s>%3$s</%1$s></li>',
		apply_filters('reycore/woocommerce/loop/shop_display_categories/tag', 'h2'),
		implode(' ', $classes),
		$the_title
	);
});


add_filter('woocommerce_get_star_rating_html', function($html){

	return str_replace('<span style="width:', '<span style="--rating-width:', $html);

}, 9999);
