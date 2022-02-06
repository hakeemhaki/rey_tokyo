<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Loop') ):
/**
 * Loop fucntionality
 *
 */
class ReyCore_WooCommerce_Loop
{
	private static $_instance = null;

	private function __construct()
	{
		$this->load_default_skins();

		add_action( 'reycore/woocommerce/loop/register_skin', [$this, 'register_skins'] );
		add_action( 'init', [$this, 'init'] );
	}

	function init(){

		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init', [$this, 'load_hooks'] );
			return;
		}

		$this->load_hooks();
	}

	private static function default_skins(){
		return [
			'default'  => esc_attr__('Default', 'rey-core'),
			'basic'    => esc_attr__('Basic Skin', 'rey-core'),
			'wrapped'  => esc_attr__('Wrapped Skin', 'rey-core'),
		];
	}

	function register_skins(){
		reyCoreLoopSkins()->add_skin(self::default_skins());
	}

	/**
	 * Load default skins
	 *
	 * @since 1.0.0
	 **/
	function load_default_skins()
	{
		foreach( self::default_skins() as $skin => $name ){
			$skin_path = REY_CORE_DIR . sprintf("inc/woocommerce/tags/loop-skin-{$skin}.php", $skin);
			if( file_exists($skin_path) && is_readable($skin_path) ){
				require $skin_path;
			}
		}
	}

	public function load_hooks(){


		add_action( 'woocommerce_before_shop_loop', [$this, 'trigger_filter_panel_loading']);

		add_filter( 'rey/content/sidebar_class', [$this, 'mobile_sidebar_add_filter_class'], 10, 2);
		add_filter( 'rey/content/site_main_class', [$this, 'mobile_sidebar_add_filter_class']);

		add_action( 'woocommerce_after_template_part', [$this,'after_no_products'], 1);

		add_filter( 'woocommerce_product_loop_start', [$this, 'filter_product_loop_start']);
		add_filter( 'woocommerce_product_loop_end', [$this, 'filter_product_loop_end']);

		$this->run_pre_loop_hooks();

		add_action( 'woocommerce_before_shop_loop_item', [$this, 'start_wrapper_div'], 0);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 999);

		add_action( 'woocommerce_before_subcategory', [$this, 'start_wrapper_cat_div'], 0);
		add_action( 'woocommerce_after_subcategory', 'reycore_wc__generic_wrapper_end', 999);

		// remove default hooks
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'loop_product_link_open'], 9 );
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'loop_product_link_close'], 12 );
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'thumbnail_wrapper_start'], 5);
		add_action( 'woocommerce_before_shop_loop_item_title', 'reycore_wc__generic_wrapper_end', 12); // thumbnail wrapper end
		add_action( 'woocommerce_before_shop_loop', [$this, 'thumbnail_add_custom_class'], 0 );
		add_action( 'woocommerce_after_shop_loop', [$this, 'thumbnail_remove_custom_class'], 1000 );
		add_action( 'woocommerce_shortcode_before_products_loop', [$this, 'thumbnail_add_custom_class'], 0 );
		add_action( 'woocommerce_shortcode_after_products_loop', [$this, 'thumbnail_remove_custom_class'], 1000 );
		add_filter( 'reycore/load_more_pagination/output_attributes', [$this, 'load_more_output_attributes'], 10, 2);

		add_filter('theme_mod_loop_slideshow_nav_hover_dots', [$this, 'disable_slideshow_hover_on_masonry']);
		add_filter('theme_mod_loop_slideshow_hover_slide', [$this, 'disable_slideshow_hover_on_masonry']);

	}

	public function run_pre_loop_hooks(){

		$hooks = apply_filters('reycore/loop/pre_loop_hooks', [
			'woocommerce_before_shop_loop',
			'woocommerce_shortcode_before_products_loop', // sale_products
			'woocommerce_shortcode_before_sale_products_loop', // sale_products
			'woocommerce_shortcode_before_best_selling_products_loop', // best_selling_products
			'woocommerce_shortcode_before_featured_products_loop', // featured_products
			'woocommerce_shortcode_before_recent_products_loop', // recent_products
			'woocommerce_shortcode_before_top_rated_products_loop', // top_rated_products
		]);

		foreach ($hooks as $hook) {
			add_action( $hook, [$this, 'set_loop_props'], 0);
			add_action( $hook, [$this, 'components_add_remove'], apply_filters('reycore/woocommerce/components_add_remove/priority', 10));
			// Product grids that support a header (ordering, filtering etc.)
			add_action( $hook, [$this, 'header_wrapper_start'], 19 );
			add_action( $hook, [$this, 'header_wrapper_end'], 31 );
		}

		$other_hooks = [
			'woocommerce_cart_collaterals',
			'reycore/woocommerce/wishlist/render_products',
			'reycore/woocommerce/loop/before_grid/name=related',
			'reycore/woocommerce/loop/before_grid/name=up-sells',
		];

		foreach ($other_hooks as $hook) {
			add_action( $hook, [$this, 'set_loop_props'], 0);
			add_action( $hook, [$this, 'components_add_remove'], 5);
			add_action( $hook, [$this, 'thumbnail_add_custom_class'], 5);
		}

	}

	public function set_loop_props(){

		// all components
		$components = reycore_wc_get_loop_components();

		// available components
		$available_components = $this->get_default_component_hooks();

		foreach ($components as $component => $flag) {
			if( is_array($flag) ){
				foreach ($flag as $subcomponent => $subflag) {
					wc_set_loop_prop($component . '_' . $subcomponent, false );
					if( array_key_exists( $subcomponent, $available_components[$component] ) ){
						wc_set_loop_prop( $component . '_' . $subcomponent, $subflag );
					}
				}
			}
			else {
				wc_set_loop_prop($component, false );
				if( array_key_exists( $component, $available_components ) ){
					wc_set_loop_prop($component, $flag );
				}
			}
		}


		$this->load_scripts();

	}

	public function components_add_remove(){

		$component_hooks = $this->get_default_component_hooks();

		foreach ($component_hooks as $key => $component) {

			if( !is_array($component) ){
				continue;
			}

			if( isset( $component['type'] ) ){ // it means it's a regular, not sub-component
				$this->hooks_add_remove($component, wc_get_loop_prop($key));
			}
			else {
				foreach($component as $skey => $subcomponent){
					$this->hooks_add_remove($subcomponent, wc_get_loop_prop( $key . '_' . $skey ));
				}
			}
		}
	}

	/**
	 * Add Remove Hook
	 *
	 * @since 1.0.0
	 **/
	private function hooks_add_remove($item, $to_add)
	{
		if( isset($item['type']) ){
			call_user_func(
				sprintf('%s_%s', ($to_add ? 'add' : 'remove'), $item['type'] ),
				$item['tag'],
				$item['callback'],
				isset($item['priority']) ? $item['priority'] : 10,
				isset($item['accepted_args']) ? $item['accepted_args'] : 1
			);
		}
	}

	/**
	 * Predefined list of components.
	 *
	 * @since 1.0.0
	 */
	public function get_default_component_hooks( $component = '' ){

		$components = apply_filters('reycore/loop/component_hooks', [

			'result_count'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => 'woocommerce_result_count',
				'priority'      => 20,
			],

			'catalog_ordering'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => 'woocommerce_catalog_ordering',
				'priority'      => 30,
			],

			/**
			 * Loop components
			 */

			'view_selector'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => [ $this, 'loop_component_view_selector' ],
				'priority'      => 29,
			],
			'filter_button'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop',
				'callback'      => [ $this, 'loop_component_filter_button' ],
				'priority'      => 20,
			],
			'filter_top_sidebar' => [
				'type'          => 'action',
				'tag'           => 'rey/get_sidebar',
				'callback'      => [ $this, 'loop_component_filter_top_sidebar' ],
				'priority'      => 10,
			],

			/**
			 * Item components
			 */

			'brands'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => [ $this, 'component_brands' ],
				'priority'      => 4,
			],
			'category'       => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => [ $this, 'component_product_category'],
				'priority'      => 5,
			],
			'add_to_cart'    => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_add_to_cart',
				'priority'      => 10,
			],
			'quickview'      => [
				'bottom' => [
					'type'          => 'action',
					'tag'           => 'woocommerce_after_shop_loop_item',
					'callback'      => [ $this, 'component_quickview_button' ],
					'priority'      => 10,
				],
				'topright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/top-right',
					'callback'      => [ $this, 'component_quickview_button' ],
					'priority'      => 10,
				],
				'bottomright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/bottom-right',
					'callback'      => [ $this, 'component_quickview_button' ],
					'priority'      => 10,
				],
			],
			'wishlist'       => [
				'bottom' => [
					'type'          => 'action',
					'tag'           => 'woocommerce_after_shop_loop_item',
					'callback'      => [ $this, 'component_wishlist' ],
					'priority'      => 40,
				],
				'topright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/top-right',
					'callback'      => [ $this, 'component_wishlist' ],
					'priority'      => 10,
				],
				'bottomright' => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/bottom-right',
					'callback'      => [ $this, 'component_wishlist' ],
					'priority'      => 10,
				],
			],
			'ratings'        => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => 'woocommerce_template_loop_rating',
				'priority'      => 3,
			],
			'prices'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item_title',
				'callback'      => 'woocommerce_template_loop_price',
				'priority'      => 10,
			],
			'discount' => [
				'top'       => [
					'type'          => 'action',
					'tag'           => 'reycore/loop_inside_thumbnail/top-right',
					'callback'      => [$this, 'component_discount_percentage_or_sale_to_top'],
					'priority'      => 10
				],
				'price'       => [
					'type'          => 'filter',
					'tag'           => 'woocommerce_get_price_html',
					'callback'      => [$this, 'component_discount_percentage_to_price'],
					'priority'      => 10,
					'accepted_args' => 2
				],
			],
			'thumbnails'     => [
				'type'         => 'action',
				'tag'          => 'woocommerce_before_shop_loop_item_title',
				'callback'     => 'woocommerce_template_loop_product_thumbnail',
				'priority'     => 10,
			],
			'thumbnails_second'     => [
				'type'         => 'action',
				'tag'          => 'woocommerce_before_shop_loop_item_title',
				'callback'     => [ $this, 'add_second_thumbnail' ],
				'priority'     => 10,
			],
			'variations'         => [
				'type'          => 'action',
				'tag'           => 'reycore/variations_html',
				'callback'      => [ $this, 'component_variations' ],
				'priority'      => 10,
			],
			'new_badge'         => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/bottom-left',
				'callback'      => [ $this, 'component_new_badge' ],
				'priority'      => 10,
			],
			'sold_out_badge' => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/top-right',
				'callback'      => [ $this, 'component_sold_out_badge' ],
				'priority'      => 10,
			],
			'featured_badge' => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/top-left',
				'callback'      => [ $this, 'component_featured_badge' ],
				'priority'      => 10,
			],
			'title'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => 'woocommerce_template_loop_product_title',
				'priority'      => 10,
			],
			'excerpt'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_shop_loop_item_title',
				'callback'      => [$this, 'component_excerpt'],
				'priority'      => 10,
			],
		] );

		if( $component && isset($components[$component]) ){
			return $components[$component];
		}

		return $components;
	}

	/**
	 * Work in progress
	 */
	protected function get_default_settings( $setting = '' ){

		$settings = [
			'loop_alignment' => 'left',
		];

		if( isset( $settings[$setting] ) ){
			return $settings[$setting];
		}

		return $settings;
	}

	/**
	 * Item Component - Brands link
	 *
	 * @since 1.0.0
	 */
	public function component_brands(){
		echo ReyCore_WooCommerce_Brands::getInstance()->loop_show_brands();
	}

	/**
	 * Item Component - Category link
	 *
	 * @since 1.0.0
	 */
	public function component_product_category()
	{
		$product = wc_get_product();
		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		$__categories = wp_get_post_terms( $id, 'product_cat', apply_filters('reycore/woocommerce/loop/category_args', [
			'order' 	=> 'ASC',
			'orderby'	=> 'name',
		], $id ) );

		if( is_wp_error( $__categories ) ){
			return;
		}

		if( empty($__categories) ){
			return;
		}

		$categories = [];

		// loop through each cat
		foreach($__categories as $category):

			if( get_theme_mod('loop_categories__exclude_parents', false) ){
				// get the children (if any) of the current cat
				$children = get_categories(['taxonomy' => 'product_cat', 'parent' => $category->term_id ]);
				if ( count($children) == 0 ) {
					// if no children, then get the category name.
					$categories[] = sprintf('<a href="%s">%s</a>', get_term_link($category, 'product_cat'), $category->name);
				}
			}
			else {
				$categories[] = sprintf('<a href="%s">%s</a>', get_term_link($category, 'product_cat'), $category->name);
			}

		endforeach;

		if( empty($categories) ){
			return;
		}

		printf('<div class="rey-productCategories">%s</div>', implode(', ', $categories));

	}

	/**
	 * Item Component - Quickview button
	 *
	 * @since 1.0.0
	 */
	public function component_quickview_button(){
		if( class_exists('ReyCore_WooCommerce_QuickView') ) {
			echo ReyCore_WooCommerce_QuickView::getInstance()->get_button_html();
		}
	}

	/**
	 * Item Component - Wishlist
	 *
	 * @since 1.3.0
	 */
	public function component_wishlist(){
		echo ReyCore_WooCommerce_Wishlist::get_button_html();
	}

	/**
	 * Item Component - Discount label in product price
	 *
	 * @since 1.0.0
	 */
	public function component_discount_percentage_to_price( $html, $product )
	{

		if( ! $product ){
			$product = reycore_wc__get_product();
		}

		if( ! ($product && apply_filters('reycore/woocommerce/discounts/check', ($product->is_on_sale() || $product->is_type( 'grouped' )), $product) ) ){
			return $html;
		}

		$should_add = (! is_product() || in_array( wc_get_loop_prop('name'), ['upsells', 'up-sells', 'crosssells', 'cross-sells', 'related'] ));

		if( $should_add ){

			if ( ($label_pos = get_theme_mod('loop_discount_label', 'price')) && $label_pos == 'price'  ) {

				reyCoreAssets()->add_scripts( 'reycore-wc-loop-discount-badges' );

				$sale_label = get_theme_mod('loop_show_sale_label', 'percentage');

				if ( $sale_label === 'percentage' ){
					return $html . reycore_wc__get_discount_percentage_html();
				}
				else if ( $sale_label === 'save' ){
					return $html . reycore_wc__get_discount_save_html();
				}
			}

		}

		return $html;
	}

	/**
	 * Item Component - discount percentage or SALE label to product, top-right
	 *
	 * @since 1.0.0
	 */
	public function component_discount_percentage_or_sale_to_top()
	{

		if( !(($product = reycore_wc__get_product()) && apply_filters('reycore/woocommerce/discounts/check', ($product->is_on_sale() || $product->is_type( 'grouped' )), $product) ) ){
			return;
		}

		$should_add = (! is_product() || in_array( wc_get_loop_prop('name'), ['upsells', 'up-sells', 'crosssells', 'cross-sells', 'related'] ));

		if( $should_add && reycore_wc_get_loop_components('prices') ){

			$sale_label = get_theme_mod('loop_show_sale_label', 'percentage');

			if( ($label_pos = get_theme_mod('loop_discount_label', 'price')) && $label_pos === 'top' ){
				if ( $sale_label === 'percentage' ){
					echo reycore_wc__get_discount_percentage_html();
				}
				else if ( $sale_label === 'save' ){
					echo reycore_wc__get_discount_save_html();
				}
			}

			elseif( $sale_label === 'sale' ){
				return woocommerce_show_product_loop_sale_flash();
			}
		}
	}

	/**
	 * Item Component - Variations buttons
	 *
	 * @since 1.3.0
	 */
	public function component_variations(){
		echo ReyCore_WooCommerce_Variations::getInstance()->do_variation();
	}

	/**
	 * Item Component - NEW badge to product entry for any product added in the last 30 days.
	*
	* @since 1.0.0
	*/
	public function component_new_badge() {
		$postdate      = get_the_time( 'Y-m-d' ); // Post date
		$postdatestamp = strtotime( $postdate );  // Timestamped post date
		$newness       = apply_filters('reycore/woocommerce/loop/new_badge/newness', 30); // Newness in days
		if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) {
			printf('<div class="rey-itemBadge rey-new-badge">%s</div>', apply_filters('reycore/woocommerce/loop/new_text', esc_html__( 'NEW', 'rey-core' ) ) );
		}
	}

	/**
	 * Item Component - Sold out badge for out of stock items.
	*
	* @since 1.4.4
	*/
	public function component_sold_out_badge() {

		if( ($product = reycore_wc__get_product()) ){

			$badge = '';

			if( $product->is_in_stock() ){
				if( get_theme_mod('loop_sold_out_badge', '1') === 'in-stock' ){
					$badge = apply_filters('reycore/woocommerce/loop/in_stock_text', esc_html__( 'IN STOCK', 'rey-core' ) );
				}
			}
			else {
				if( get_theme_mod('loop_sold_out_badge', '1') === '1' ) {
					$badge = apply_filters('reycore/woocommerce/loop/sold_out_text', esc_html__( 'SOLD OUT', 'rey-core' ) );
				}
			}

			if( empty($badge) ){
				return;
			}

			printf('<div class="rey-itemBadge rey-soldout-badge">%s</div>', $badge );
		}

	}

	/**
	 * Item Component - Sold out badge for out of stock items.
	*
	* @since 1.6.9
	*/
	public function component_featured_badge() {

		$product = wc_get_product();

		if( $product && get_theme_mod('loop_featured_badge', 'hide') === 'show' && $product->is_featured() ){
			printf('<div class="rey-itemBadge rey-featured-badge">%s</div>', get_theme_mod('loop_featured_badge__text', esc_html__('FEATURED', 'rey-core')) );
		}
	}

	function component_excerpt() {

		global $post;

		if ( ! ($excerpt = apply_filters( 'woocommerce_short_description', $post->post_excerpt )) ) {
			return;
		}

		if( $limit = absint( get_theme_mod('loop_short_desc_limit', '8') ) ){
			$excerpt = wp_trim_words($excerpt, $limit);
		}

		$class = '';

		if( get_theme_mod('loop_short_desc_mobile', false) ){
			$class .= ' --show-mobile';
		}

		?>
		<div class="woocommerce-product-details__short-description <?php echo esc_attr($class)  ?>">
			<?php echo $excerpt; // WPCS: XSS ok. ?>
		</div>
		<?php
	}


	/**
	 * Utility
	 *
	 * Utility to check exact product type
	 *
	 * @since 1.1.2
	 */
	function is_product(){
		return in_array( get_post_type(), [ 'product', 'product_variation' ], true );
	}

	function is_custom_image_height(){
		return ! in_array( get_theme_mod('loop_grid_layout', 'default'), [ 'metro' ]) &&
		get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
		get_theme_mod('custom_image_height', false) === true ;
	}

	/**
	 * Utility
	 *
	 * Add animation hover class in loop
	 *
	 * @since 1.0.0
	 */
	function general_css_classes($exclude = [])
	{
		$classes = [];

		if ( in_array( get_post_type(), [ 'product', 'product_variation' ], true ) ) {
			$classes['rey_skin'] = 'rey-wc-skin--' . get_theme_mod('loop_skin', 'basic');
		}

		if ( $this->is_product() ) {

			if( get_theme_mod('loop_animate_in', true) && !in_array('animation-entry', $exclude) && ! reycore__elementor_edit_mode() && wc_get_loop_prop( 'entry_animation' ) !== false ){
				$classes['animated-entry'] = 'is-animated-entry';
			}

			// Check if product has custom height
			// @note: using get_option on WC's cropping option intentionally
			if(
				! in_array('image-container-height', $exclude) &&
				$this->is_custom_image_height()
			){
				$classes['image-container-height'] = ' --customImageContainerHeight';
			}

			if( !in_array('text-alignment', $exclude) ){
				$classes['text-align'] = 'rey-wc-loopAlign-' . get_theme_mod('loop_alignment', 'left');
			}

			// Check if product has more than one image
			if( !in_array('extra-media', $exclude) && ($images = reycore_wc__get_product_images_ids()) && count($images) > 1 ){
				if( ! apply_filters('reycore/woocommerce/loop/prevent_2nd_image', false) ){

					$classes['extra-media'] = '--extraImg-' . get_theme_mod('loop_extra_media', 'second');

					if( $this->loop_extra_media_mobile() ){
						$classes['extra-media-mobile'] = '--extraImg-mobile';
					}

				}
			}

			if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' ){
				$classes[] = '--uncropped';
			}

		}

		return $classes;
	}

	function load_scripts( $args = [] ){

		$args = wp_parse_args($args, [
			'grid_type' => get_theme_mod('loop_grid_layout', 'default')
		]);

		if( get_theme_mod('loop_animate_in', true) ){
			reyCoreAssets()->add_scripts('scroll-out');
		}

		reyCoreAssets()->add_styles([
			'rey-wc-general',
			'rey-wc-loop',
		]);

		switch( $args['grid_type'] ):
			case"masonry":
				wp_enqueue_script('masonry');
				break;
			case"masonry2":
				wp_enqueue_script('masonry');
				reyCoreAssets()->add_styles('rey-wc-loop-grid-skin-masonry2');
				break;
			case"metro":
				reyCoreAssets()->add_styles('rey-wc-loop-grid-skin-metro');
				break;
			case"scattered":
				reyCoreAssets()->add_styles('rey-wc-loop-grid-skin-scattered');
				break;
			case"scattered2":
				reyCoreAssets()->add_styles('rey-wc-loop-grid-skin-scattered');
				break;
		endswitch;

		// List view
		if( self::is_mobile_list_view() ){
			reyCoreAssets()->add_styles('rey-wc-loop-grid-mobile-list-view');
		}

		if( $active_skin = $this->get_loop_active_skin() ){
			reyCoreAssets()->add_styles( 'rey-wc-loop-item-skin-' . $active_skin );
		}

		if( get_theme_mod('product_items_eq', false) ){
			reyCoreAssets()->add_scripts('reycore-wc-loop-equalize');
		}

		reyCoreAssets()->add_scripts(['reycore-wc-loop-grids', 'imagesloaded']);

		do_action('reycore/woocommerce/loop/scripts');
	}

	/**
	 * Handle mobile extra media.
	 * `wp_is_mobile` could've been used, but if a page is cached,
	 * it would stop showing for one of the versions, since there aren't separate cached versions.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	function loop_extra_media_mobile(){

		$default = false;

		if( get_theme_mod('loop_extra_media', 'second') === 'slideshow' ){
			$default = get_theme_mod('loop_slideshow_disable_mobile', false); // legacy option
		}

		$disabled = get_theme_mod('loop_extra_media_disable_mobile', $default);

		return ! $disabled;
	}

	public static function is_mobile_list_view(){
		return reycore_wc_get_columns('mobile') === 1 && get_theme_mod('woocommerce_catalog_mobile_listview', false);
	}

	/**
	 * Utility
	 *
	 * Filter product list start to add custom CSS classes
	 *
	 * @since 1.1.2
	 */
	public function filter_product_loop_start( $html )
	{

		do_action('reycore/woocommerce/loop/before_grid');

		if( $q_name = wc_get_loop_prop( 'name' ) ){
			do_action('reycore/woocommerce/loop/before_grid/name=' . $q_name);
		}

		$search_for = 'class="products';

		$classes['columns_tablet'] = 'columns-tablet-' . reycore_wc_get_columns('tablet');
		$classes['columns_mobile'] = 'columns-mobile-' . reycore_wc_get_columns('mobile');

		if( self::is_mobile_list_view() ){
			$classes['mobile_listview'] = 'rey-wcGrid-mobile-listView';
		}

		$classes['skin'] = '--skin-' . esc_attr( $this->get_loop_active_skin() );

		/**
		 * Grid Gap CSS class
		 */
		$classes['grid_gap'] = 'rey-wcGap-' . esc_attr( get_theme_mod('loop_gap_size', 'default') );

		/**
		 * Product Grid CSS class
		 */

		$classes['grid_layout'] = 'rey-wcGrid-' . esc_attr( get_theme_mod('loop_grid_layout', 'default') );

		if( wc_get_loop_prop( 'is_paginated' ) && wc_get_loop_prop( 'total_pages' ) ){

			$classes['paginated'] = '--paginated';

			if( in_array(get_theme_mod('loop_pagination', 'paged'), ['load-more', 'infinite'], true) ){
				$classes['paginated_infinite'] = '--infinite';
			}
		}

		$cols = wc_get_loop_prop( 'columns' );

		if( $cols >= wc_get_loop_prop( 'total' ) ){
			$classes['prevent_margin'] = '--no-margins';
		}

		$html = str_replace( $search_for, $search_for . ' ' . implode(' ', apply_filters('reycore/woocommerce/product_loop_classes', $classes)), $html );
		$html = str_replace( '<ul', sprintf('<ul data-cols="%s" %s ', esc_attr( $cols ), apply_filters('reycore/woocommerce/product_loop_attributes', '', []) ), $html );

		return $html;
	}

	public function filter_product_loop_end( $html )
	{
		do_action('reycore/woocommerce/loop/after_grid');

		if( $q_name = wc_get_loop_prop( 'name' ) ){
			do_action('reycore/woocommerce/loop/after_grid/name=' . $q_name);
		}

		return $html;
	}

	/**
	 * Loop Component
	 *
	 * Add viewselector
	 *
	 * @since 1.0.0
	 **/
	function loop_component_view_selector()
	{
		reycore__get_template_part('template-parts/woocommerce/view-selector');
		reyCoreAssets()->add_scripts(['reycore-wc-loop-viewselector']);
	}

	/**
	 * Loop Component
	 *
	 * Add Filter button in loop's header
	 *
	 * @since 1.0.0
	 **/
	function loop_component_filter_button()
	{
		reycore__get_template_part('template-parts/woocommerce/filter-panel-button');
		reyCoreAssets()->add_scripts(['reycore-wc-loop-filter-count']);
	}

	/**
	 * Loop utility
	 *
	 * Filter Sidebar - Add CSS class to shop sidebar
	 *
	 * @since 1.0.0
	 **/
	function mobile_sidebar_add_filter_class($classes, $sidebar = '')
	{
		$mobile_btn = reycore_wc__check_filter_btn();

		if( is_singular('product') ){
			return $classes;
		}

		$supported__filter_panel = reycore_wc__check_filter_panel();
		$supported__shop_sidebar = reycore_wc__check_shop_sidebar();
		$supported__filter_top = reycore_wc__check_filter_sidebar_top();

		if( $supported__filter_top || $supported__shop_sidebar ) {
			$classes[] = 'rey-filterSidebar';
		}

		if( $supported__filter_panel ){
			$classes[] = '--filter-panel';
		}

		// also determines if it's a sidebar
		if( $mobile_btn === $sidebar ){
			$classes[] = '--supports-mobile';
			$classes[] = 'rey-filterSidebar';

			reyCoreAssets()->add_styles('reycore-side-panel');
		}

		return $classes;
	}

	/**
	 * Loop Component
	 *
	 * HTML wrapper to insert after the not found product loops.
	 *
	 */
	function after_no_products($template_name = '', $template_path = '', $located = '') {
		if ($template_name == 'loop/no-products-found.php') {
			if( reycore_wc__check_filter_panel() ) {
				reycore__get_template_part('template-parts/woocommerce/filter-panel-button-not-found');
				reyCoreAssets()->add_scripts(['reycore-wc-loop-filter-panel']);
			}
		}
	}

	/**
	 * Loop Component
	 *
	 * Add Filter panel after footer
	 *
	 * @since 1.0.0
	 **/
	function add_filter_sidebar_panel()
	{
		if( reycore_wc__check_filter_panel() ) {
			reycore__get_template_part('template-parts/woocommerce/filter-panel-sidebar');
		}

		if( reycore_wc__check_filter_btn() ){
			reyCoreAssets()->add_scripts(['reycore-wc-loop-filter-panel', 'simple-scrollbar']);
			reyCoreAssets()->add_styles('simple-scrollbar');
		}
	}

	function trigger_filter_panel_loading(){

		if( ! wc_get_loop_prop('is_paginated') ){
			return;
		}

		add_action( 'rey/after_footer', [$this, 'add_filter_sidebar_panel'], 10 );
	}

	/**
	 * Item Component
	 *
	 * Add Filter sidebar before loop
	 *
	 * @since 1.0.0
	 **/
	function loop_component_filter_top_sidebar($position)
	{
		if(
			( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) && reycore_wc__check_filter_sidebar_top()
		) {
			reycore__get_template_part('template-parts/woocommerce/filter-top-sidebar');
		}
	}

	/**
	 * Wrap layout - start
	 *
	 * @since 1.0.0
	 **/
	function start_wrapper_div()
	{
		$id = 0;

		$product = wc_get_product();

		if( ! $product ){
			global $product;
		}

		if ( $product ) {
			if( $product->get_type() === 'variation' ){
				$id = $product->get_parent_id();
			}
			else {
				$id = $product->get_id();
			}
		}

		printf('<div class="rey-productInner" data-id="%d" >', absint($id));
	}

	function start_wrapper_cat_div()
	{
		echo '<div class="rey-productInner">';
	}

	/**
	 * Wrap loop header
	 *
	 * @since 1.0.0
	 **/
	function header_wrapper_start()
	{
		if( ! (wc_get_loop_prop( 'result_count', true ) || wc_get_loop_prop( 'catalog_ordering', true )) ){
			return;
		}

		$classes = [];

		if( reycore_wc__check_filter_btn() && wc_get_loop_prop( 'filter_button' ) === true ){
			$classes['has_btn'] = '--has-filter-btn';
		}

		echo sprintf('<div class="rey-loopHeader %s">', esc_attr( implode(' ', apply_filters('rey/woocommerce/loop/header_classes', $classes ) ) ) );

		reyCoreAssets()->add_styles('rey-wc-loop-header');

	}

	/**
	 * Wrap loop header
	 *
	 * @since 1.0.0
	 **/
	function header_wrapper_end()
	{
		if( ! (wc_get_loop_prop( 'result_count', true ) || wc_get_loop_prop( 'catalog_ordering', true )) ){
			return;
		}

		do_action('reycore/loop_products/before_header_end');

		echo '</div>';
	}

	/**
	 * Utility
	 *
	 * Checks if the thumbnail should be wrapped with link
	 *
	 * @since 1.2.0
	 */
	public function should_wrap_thumbnails_with_link() {

		$status = true;

		if( get_theme_mod('loop_extra_media', 'second') === 'slideshow' && ! apply_filters('reycore/woocommerce/loop/prevent_product_items_slideshow', false) ){
			$status = false;
		}

		return apply_filters('reycore/loop_product/should_wrap_thumbnails_with_link', $status);
	}

	public function loop_product_link_open(){
		if( $this->should_wrap_thumbnails_with_link() ){
			woocommerce_template_loop_product_link_open();
		}
	}

	public function loop_product_link_close(){
		if( $this->should_wrap_thumbnails_with_link() ){
			woocommerce_template_loop_product_link_close();
		}
	}

	public function replace_placeholder_with_variation_image($product, $size, $attr){

		$available_attributes = $product->get_available_variations();

		if( empty($available_attributes) ){
			return;
		}

		$variation_image_id = false;

		foreach(array_reverse($available_attributes) as $variation_values ){
			if( isset($variation_values['image_id']) && !empty($variation_values['image_id']) ){
				$variation_image_id = $variation_values['image_id'];
			}
		}

		if( ! $variation_image_id ){
			return;
		}

		return wp_get_attachment_image( $variation_image_id, $size, false, $attr );
	}

	public function thumbnail_custom_class__filter( $image, $product, $size, $attr ){

		if(strpos($image, 'woocommerce-placeholder') !== false && $product->get_type() === 'variable'){
			if( $var_image = $this->replace_placeholder_with_variation_image($product, $size, $attr) ){
				$image = $var_image;
			}
		}

		return str_replace('class="attachment', 'class="rey-thumbImg img--1 attachment', $image);
	}

	public function thumbnail_add_custom_class(){
		add_filter('woocommerce_product_get_image', [$this, 'thumbnail_custom_class__filter'], 100, 4);
	}

	public function thumbnail_remove_custom_class(){
		remove_filter('woocommerce_product_get_image', [$this, 'thumbnail_custom_class__filter'], 100);
	}

	/**
	 * Utility
	 *
	 * Wrap product thumbnail - start
	 *
	 * @since 1.0.0
	 **/
	function thumbnail_wrapper_start()
	{
		echo '<div class="rey-productThumbnail">';

		foreach([ 'top-left', 'top-right', 'bottom-left', 'bottom-right' ] as $position){

			$hook = 'reycore/loop_inside_thumbnail/' . $position;

			ob_start();
			do_action($hook);
			$th_content = ob_get_clean();

			if( !empty($th_content) ){
				printf('<div class="rey-thPos rey-thPos--%2$s">%1$s</div>', $th_content, $position);
			}
		}
	}

	/**
	 * Utility
	 *
	 * Add second image
	 *
	 * @since 1.0.0
	 **/

	function add_second_thumbnail()
	{
		if( apply_filters('reycore/woocommerce/loop/prevent_2nd_image', false) ){
			return;
		}

		if( ! (get_theme_mod('loop_extra_media', 'second') === 'second') ){
			return;
		}

		if ( ($images = reycore_wc__get_product_images_ids()) && count($images) > 1 ) {

			if( !($product = wc_get_product()) ){
				global $product;
			}

			echo apply_filters('reycore/woocommerce/loop/second_image',
				reycore__get_picture([
					'id' => $images[1],
					'size' => apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' ),
					'class' => 'rey-productThumbnail__second',
					'disable_mobile' => ! $this->loop_extra_media_mobile(),
				]),
				$product,
				$images[1],
				$images
			);
		}
	}

	/**
	 * Utility
	 *
	 * Add thumbnails slideshow
	 *
	 * @since 1.0.0
	 **/
	function add_thumbnails_slideshow( $html, $product, $size )
	{
		if( is_admin() && ! wp_doing_ajax() ){
			return $html;
		}

		if( apply_filters('reycore/woocommerce/loop/prevent_product_items_slideshow', false) ){
			return $html;
		}

		if( get_theme_mod('loop_extra_media', 'second') !== 'slideshow' ){
			return $html;
		}

		reyCoreAssets()->add_scripts(['splidejs', 'rey-splide', 'reycore-wc-loop-slideshows', 'imagesloaded']);
		reyCoreAssets()->add_styles(['rey-splide']);

		if (
			($images = reycore_wc__get_product_images_ids(false)) &&
			!empty($images)  && wc_get_loop_prop( 'product_thumbnails_slideshow', true ) ) {

			$html_images[] = sprintf('<a href="%s" class="splide__slide">%s</a>',
				esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
				wp_get_attachment_image( $product->get_image_id(), $size, false, ['class'=>'rey-productThumbnail-extra'] )
			);

			$max = get_theme_mod('loop_slideshow_nav_max', 4); // minus main

			foreach ($images as $key => $img) {

				if( ($max - 1) < ($key + 1) ) {
					continue;
				}

				$html_images[] = sprintf('<a href="%s" class="splide__slide">%s</a>',
					esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
					reycore__get_picture([
						'id' => $img,
						'size' => $size,
						'class' => 'rey-productThumbnail-extra',
						'disable_mobile' => ! $this->loop_extra_media_mobile(),
					])
				);

			}

			$slider_attributes = [];

			// Start Nav
			$nav_type = get_theme_mod('loop_slideshow_nav', 'dots');
			$nav_html = '';

			// Bullets
			if( $nav_type === 'dots' || $nav_type === 'both' ){

				// pagination id
				$pagination_id = '__pagination-' . $product->get_id();
				// pass it to slider
				$slider_attributes['data-pagination-id'] = $pagination_id;

				$classes[] = $pagination_id;

				if( $bullets_style = get_theme_mod('loop_slideshow_nav_dots_style', 'bars') ){
					$classes[] = '--bullets-style-' . $bullets_style;
				}

				if( ! $this->loop_extra_media_mobile() ){
					$classes[] = '--hide-mobile';
				}

				$nav_html .= sprintf('<div class="rey-productSlideshow-dots %s">', implode(' ', $classes));

				for($i = 0; $i <= count($images); $i++){

					if( $max < ($i + 1) ) {
						continue;
					}

					$nav_html .= sprintf('<button data-go="%d"><span></span></button>', $i);
				}

				$nav_html .= '</div>';
			}

			// Arrows
			if( $nav_type === 'arrows' || $nav_type === 'both' ){
				// arrows nav id
				$arrows_id = '__arrows-' . $product->get_id();
				// add as attribute
				$slider_attributes['data-arrows-id'] = $arrows_id;
				// nav markup
				$nav_html .= sprintf('<div class="rey-productSlideshow-arrows %s">%s</div>',
					$arrows_id,
					reycore__arrowSvg(false, false, 'data-dir="-1"') .
					reycore__arrowSvg(true, false, 'data-dir="+1"')
				);
			}

			$slider_markup = sprintf(
				'<div class="splide %s" %s>',
				(get_theme_mod('loop_slideshow_nav_color_invert', false) ? '--color-invert' : ''),
				reycore__implode_html_attributes($slider_attributes)
			);
			$slider_markup .= '<div class="splide__track">';
			$slider_markup .= sprintf('<div class="rey-productSlideshow splide__list">%s</div>', implode('', $html_images) );
			$slider_markup .= $nav_html;
			$slider_markup .= '</div></div>';

			$html = $slider_markup;
		}
		else {
			return sprintf('<a href="%s" class=" woocommerce-LoopProduct-link woocommerce-loop-product__link">%s</a>',
				esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
				$html
			);
		}

		return $html;
	}

	function disable_slideshow_hover_on_masonry($status){

		if( apply_filters('reycore/woocommerce/loop/prevent_product_items_slideshow', false) ){
			return $status;
		}

		if( get_theme_mod('loop_extra_media', 'second') !== 'slideshow' ){
			return $status;
		}

		if( in_array( get_theme_mod('loop_grid_layout', 'default'), ['masonry', 'masonry2'], true ) ){
			return false;
		}

		return $status;
	}

	function apply_extra_thumbs_filter(){
		add_filter( 'woocommerce_product_get_image', [$this, 'add_thumbnails_slideshow'], 10, 3);
	}

	function remove_extra_thumbs_filter(){
		remove_filter( 'woocommerce_product_get_image', [$this, 'add_thumbnails_slideshow'], 10, 3);
	}


	/**
	 * Work in progress
	 */
	function archive_description_more_tag( $content ){

		if ( !(is_product_taxonomy() || is_product_category() || is_shop()) ) {
			return $content;
		}

		$parts = str_replace( '&lt;!--more--&gt;', '<!--more-->', $content );

		if( strpos($parts, '<!--more-->') !== false ){
			$parts = str_replace(['<p>', '</p>'], '', $parts);
			$parts = explode( '<!--more-->', $parts );

			return $parts[0] . '<button class="btn u-toggle-btn" data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt in Compact layout.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt in Compact layout.', 'rey-core') .'"></button>';
		}

		return $content;
	}


	function load_more_output_attributes( $attributes, $args ){

		if( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) || apply_filters('reycore/load_more_pagination/product', false) ){

			if( $args['ajax_counter'] ){

				$total    = wc_get_loop_prop( 'total' );
				$per_page = wc_get_loop_prop( 'per_page' );
				$paged    = wc_get_loop_prop( 'current_page' );

				$from     = min( $total, $per_page * $paged );
				$to       = $total;

				$counter_current_page = true;

				if( isset($args['counter_current_page']) && ! $args['counter_current_page'] ){
					$counter_current_page = false;
				}

				if( $counter_current_page ){
					$from = $paged;
					if( $total ){
						$to = ceil( $total / $per_page );
					}
				}

				$from = absint($from);
				$to = absint($to);

				if( ($from + 1) === $to ){
					$attributes['data-end-count'] = sprintf('(%s / %s)', $from + 1, $to);
				}

				$attributes['data-count'] = sprintf('(%s / %s)', $from, $to);
			}

			$attributes['data-history'] = get_theme_mod('loop_pagination_ajax_history', true) ? '1' : '0';

			if( $btn_text = get_theme_mod('loop_pagination_ajax_text', '') ){
				$attributes['data-text'] = $btn_text;
			}

			if( $btn_end_text = get_theme_mod('loop_pagination_ajax_end_text', '') ){
				$attributes['data-end-text'] = $btn_end_text;
			}

			$attributes['href'] = str_replace('?reynotemplate=1', '', $attributes['href']);
			$attributes['href'] = str_replace('&reynotemplate=1', '', $attributes['href']);
			$attributes['href'] = str_replace('&#038;reynotemplate=1', '', $attributes['href']);
		}

		return $attributes;
	}

	public function get_loop_active_skin(){
		return get_theme_mod('loop_skin', 'basic');
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Loop
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}

ReyCore_WooCommerce_Loop::getInstance();

endif;
