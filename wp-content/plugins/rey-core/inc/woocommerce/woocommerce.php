<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_WooCommerce_Base') ):

class ReyCore_WooCommerce_Base {

	private static $_instance = null;

	/**
	 * Rest Endpoint
	 */
	const REY_ENDPOINT = 'rey/v1';


	private function __construct(){
		$this->init_hooks();
		$this->includes();
	}

	function includes(){
		require_once REY_CORE_DIR . 'inc/woocommerce/functions.php';

		// Load tags
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/general.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-mini-cart.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-sidebar.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-wishlist.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/checkout.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/cart.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/brands.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/variations.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-login.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-tabs.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-product-navigation.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-store-notice.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-single-gs.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-badges.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-video.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-related-products.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-estimated-delivery.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-360.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-refund.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-reviews.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/loop-skins.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/loop.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/single.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/search.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-request-quote.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-teasers.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-before-after.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-stretch-products.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-fixed.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-taxonomies.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-button-atc.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-short-desc.php';
		require_once REY_CORE_DIR . 'inc/woocommerce/tags/tag-product-archive.php';

	}

	function init_hooks()
	{
		// Remove default wrappers.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end' );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		add_action( 'after_setup_theme', [ $this, 'add_support'], 10 );
		add_action( 'wp', [ $this, 'wp_actions'], 6);
		add_filter( 'woocommerce_enqueue_styles', [ $this, 'enqueue_styles'], 10000 ); // suprases Cartflows override
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts'] );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'admin_bar_menu', [$this, 'shop_page_toolbar_edit_link'], 100);
		add_filter( 'body_class', [ $this, 'body_classes'], 20 );
		add_filter( 'rey/css_styles', [ $this, 'css_styles'] );
	}

	function add_support(){

		// Register theme features.
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );

		add_theme_support( 'woocommerce', [
			'product_grid::max_columns' => 6,
			'product_grid' => [
				'max_columns'=> 6
			],
		 ] );
	}

	/**
	 * General actions
	 * @since 1.0.0
	 **/
	public function wp_actions()
	{
		if( function_exists('rey_action__before_site_container') && function_exists('rey_action__after_site_container') ){
			// add rey wrappers
			add_action( 'woocommerce_before_main_content', 'rey_action__before_site_container', 0 );
			add_action( 'woocommerce_after_main_content', 'rey_action__after_site_container', 10 );
		}

		// disable shop functionality
		if( reycore_wc__is_catalog() ){
			add_filter( 'woocommerce_is_purchasable', '__return_false');
		}

		if( apply_filters('reycore/woocommerce/prevent_atc_when_not_purchasable', false) &&
			($product = wc_get_product()) && ! $product->is_purchasable() ){
			remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
		}

		// disable post thumbnail (featured-image) in woocommerce posts
		if ( is_woocommerce() ) {
			add_filter( 'rey__can_show_post_thumbnail', '__return_false' );
		}

		// force flexslider to be disabled
		add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false', 100 );
	}


	/**
	 * Enqueue CSS for this theme.
	 *
	 * @param  array $styles Array of registered styles.
	 * @return array
	 */
	public function enqueue_styles( $styles )
	{
		// Override WooCommerce general styles
		$styles['woocommerce-general'] = [
			'src'     => REY_CORE_URI . 'assets/css/woocommerce.css',
			'deps'    => '',
			'version' => REY_CORE_VERSION,
			'media'   => 'all',
			'has_rtl' => true,
		];

		// disable smallscreen stylesheet
		if( isset($styles['woocommerce-smallscreen']) ){
			unset( $styles['woocommerce-smallscreen'] );
		}

		// disable layout stylesheet
		if( isset($styles['woocommerce-layout']) ){
			unset( $styles['woocommerce-layout'] );
		}

		return $styles;
	}


	function woocommerce_styles(){

		$rtl = reyCoreAssets()::rtl();
		$is_catalog = apply_filters('reycore/woocommerce/css_is_catalog', is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() );

		return [
			'rey-wc-general' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/general' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_woocommerce'
			],
			'rey-wc-loop' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/general/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog;
				},
			],
				'rey-wc-loop-header' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/header/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-general'],
					'version'   => REY_CORE_VERSION,
					'callback' => function() use ($is_catalog){
						return $is_catalog;
					},
				],
				'rey-wc-loop-grid-skin-metro' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-skin-metro/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-loop-grid-skin-masonry2' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-skin-masonry2/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-loop-grid-skin-scattered' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-skin-scattered/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-loop-grid-mobile-list-view' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-mobile-list-view/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-loop-item-skin-basic' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/item-skin-basic/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-loop-item-skin-wrapped' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/item-skin-wrapped/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
				],
			'rey-wc-product' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general'],
				'version'   => REY_CORE_VERSION,
			],
				'rey-wc-product-gallery' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-product-mobile-gallery' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/mobile-gallery/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-product-reviews' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/reviews/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-product-skin-default' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/skin-default/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-product-skin-fullscreen' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/skin-fullscreen/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-product-skin-compact' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/skin-compact/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
				],
			'rey-wc-cart' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-cart/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_cart() || is_checkout();
				},
			],
			'rey-wc-checkout' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-checkout/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general', 'rey-wc-cart'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_cart() || is_checkout();
				},
			],
			'rey-wc-myaccount' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-myaccount/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general', 'rey-wc-cart'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_page( wc_get_page_id( 'myaccount' ) );
				},
			],
			'rey-wc-elementor' => [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/woocommerce/woocommerce' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			],
			'rey-wc-tag-widgets' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/tag-widgets' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog && (reycore_wc__check_filter_panel() || reycore_wc__check_filter_sidebar_top() || reycore_wc__check_shop_sidebar());
				},
			],
			'rey-wc-tag-attributes' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/attributes' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog && (reycore_wc__check_filter_panel() || reycore_wc__check_filter_sidebar_top() || reycore_wc__check_shop_sidebar());
				},
			],
			'rey-wc-tag-variations' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-variations/tag-variations' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			],
			'rey-wc-tag-stretch' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-stretch/tag-stretch' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog;
				},
			],
			'rey-plugin-wvs' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/plugin-wvs/plugin-wvs' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			],
			'rey-wc-header-account-panel-top' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-account-panel-top/header-account-panel-top' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			],
			'rey-wc-header-account-panel' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-account-panel/header-account-panel' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-header-mini-cart-top' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-mini-cart-top/header-mini-cart-top' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			],
			'rey-wc-header-mini-cart' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-mini-cart/header-mini-cart' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-header-wishlist' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-wishlist/header-wishlist' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
		];

	}

	function woocommerce_scripts(){

		$is_catalog = is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag();

		return [
			'reycore-woocommerce' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/general.js',
				'deps'    => ['rey-script', 'reycore-scripts'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_woocommerce'
			],
			'reycore-wc-cart-update' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/cart-update.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_cart'
			],
			'reycore-wc-checkout-classic' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/checkout-classic.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_checkout'
			],
			'reycore-wc-header-account-forms' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-account-forms.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-account-panel' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-account-panel.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-wishlist' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-wishlist.js',
				'deps'    => ['reycore-woocommerce', 'wp-util'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-ajax-search' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-ajax-search.js',
				'deps'    => ['reycore-woocommerce', 'imagesloaded', 'wp-util'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-minicart' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-minicart.js',
				'deps'    => ['reycore-woocommerce', 'simple-scrollbar'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-cart-crosssells-bubble' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-cart-crosssells-bubble.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-cart-crosssells-panel' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-cart-crosssells-panel.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-count-loadmore' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-count-loadmore.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-discount-badges' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-discount-badges.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-equalize' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-equalize.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-filter-count' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-filter-count.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-filter-panel' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-filter-panel.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-grids' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-grids.js',
				'deps'    => ['reycore-woocommerce', 'imagesloaded'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog;
				},
			],
			'reycore-wc-loop-slideshows' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-slideshows.js',
				'deps'    => ['reycore-woocommerce', 'imagesloaded'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-stretch' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-stretch.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-toggable-widgets' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-toggable-widgets.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog && get_theme_mod('sidebar_shop__toggle__enable', false) && (reycore_wc__check_filter_panel() || reycore_wc__check_filter_sidebar_top() || reycore_wc__check_shop_sidebar());
				},
			],
			'reycore-wc-loop-variations' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-variations.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-viewselector' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-viewselector.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-carousels' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-carousels.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				// 'callback' => 'is_cart'
			],
			'reycore-wc-product-page-accordion-tabs' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-accordion-tabs.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-ajax-add-to-cart' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-ajax-add-to-cart.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-fixed-summary' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-fixed-summary.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-general' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-general.js',
				'deps'    => ['reycore-woocommerce', 'imagesloaded'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_product'
			],
			'reycore-wc-product-gallery' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-gallery.js',
				'deps'    => ['wc-single-product', 'reycore-woocommerce', 'scroll-out'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_product'
			],
			'reycore-wc-product-page-mobile-tabs' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-mobile-tabs.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-qty-controls' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-qty-controls.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-related-prod-carousel' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-related-prod-carousel.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],

			'reycore-wc-product-page-sticky' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-sticky.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],

		];
	}

	function register_assets(){
		reyCoreAssets()->register_asset('styles', $this->woocommerce_styles());
		reyCoreAssets()->register_asset('scripts', $this->woocommerce_scripts());
	}

	/**
	 * Enqueue scripts for WooCommerce
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts()
	{
		if( apply_filters('reycore/woocommerce/load_all_styles', false) ){
			foreach( $this->woocommerce_styles() as $handle => $style ){
				reyCoreAssets()->add_styles($handle);
			}
		}

		// Pass visibility style
		if( is_checkout() || is_page( wc_get_page_id( 'myaccount' ) ) ){
			reyCoreAssets()->add_styles('reycore-pass-visibility');
		}
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['woocommerce'] = true;
		$params['rest_url'] = esc_url_raw( rest_url( self::REY_ENDPOINT ) );
		$params['rest_nonce'] = wp_create_nonce( 'wp_rest' );
		$params['catalog_cols'] = reycore_wc_get_columns('desktop');
		$params['added_to_cart_text'] = reycore__texts('added_to_cart_text');
		$params['cannot_update_cart'] = reycore__texts('cannot_update_cart');
		$params['disable_header_cart_panel'] = get_theme_mod('header_cart__panel_disable', false);
		$params['header_cart_panel'] = [
			'apply_coupon_nonce'  => wp_create_nonce( 'apply-coupon' ),
			'remove_coupon_nonce' => wp_create_nonce( 'remove-coupon' ),
			'refresh_timeout'     => 1500,
		];
		$params['site_id'] = is_multisite() ? get_current_blog_id() : 0;
		$params['checkout_url'] = get_permalink( wc_get_page_id( 'checkout' ) );
		$params['after_add_to_cart'] = get_theme_mod('product_page_after_add_to_cart_behviour', 'cart');
		$params['js_params'] = [
			'select2_overrides' => true,
			'scattered_grid_max_items' => 7,
			'scattered_grid_custom_items' => [],
			'product_item_slideshow_nav' => get_theme_mod('loop_slideshow_nav', 'dots'),
			'product_item_slideshow_dots_hover' => get_theme_mod('loop_slideshow_nav', 'dots') !== 'arrows' && get_theme_mod('loop_slideshow_nav_hover_dots', false),
			'product_item_slideshow_slide_auto' => get_theme_mod('loop_slideshow_hover_slide', true),
			'product_item_slideshow_disable_mobile' => get_theme_mod('loop_extra_media_disable_mobile', get_theme_mod('loop_slideshow_disable_mobile', false) ),
			'scroll_top_after_variation_change' => get_theme_mod('product_page_scroll_top_after_variation_change', false),
			'scroll_top_after_variation_change_desktop' => false,
			'equalize_product_items' => [],
			'ajax_search_letter_count' => 3,
			'cart_update_threshold' => 1000,
			'cart_update_by_qty' => true,
			'photoswipe_light' => false,
			'customize_pdp_atc_text' => true,
			'infinite_cache' => get_theme_mod('loop_pagination_cache_products', true),
			'acc_animation' => 0
		];

		$params['currency_symbol'] = get_woocommerce_currency_symbol();
		$currency_position = get_option('woocommerce_currency_pos');
		$currency_before = $currency_after = '';
		if ($currency_position === 'left') {
			$currency_before = $params['currency_symbol'];
		} elseif ($currency_position === 'left_space') {
			$currency_before = $params['currency_symbol'] . ' ';
		} elseif ($currency_position === 'right') {
			$currency_after = $params['currency_symbol'];
		} elseif ($currency_position === 'right_space') {
			$currency_after = ' ' . $params['currency_symbol'];
		}

		$params['price_format'] = $currency_before . '{{price}}' . $currency_after;
		$params['total_text'] = __( 'Total:', 'woocommerce' );

		if( !isset($params['ajaxurl']) ){
			$params['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$params['ajax_nonce'] = wp_create_nonce( 'rey_nonce' );
		}

		if( get_theme_mod('product_items_eq', false) ){
			$params['js_params']['equalize_product_items'] = [
				'.woocommerce-loop-product__title',
				// '.rey-productVariations',
			];
		}

		$params['price_thousand_separator'] = wc_get_price_thousand_separator();
		$params['price_decimal_separator'] = wc_get_price_decimal_separator();
		$params['price_decimal_precision'] = wc_get_price_decimals();

		return $params;
	}

	/**
	 * Add Edit Page toolbar link for Shop Page
	 *
	 * @since 1.0.0
	 */
	function shop_page_toolbar_edit_link( $admin_bar ){
		if( is_shop() ){
			$admin_bar->add_menu( array(
				'id'    => 'edit',
				'title' => __('Edit Shop Page', 'rey-core'),
				'href'  => get_edit_post_link( wc_get_page_id('shop') ),
				'meta'  => array(
					'title' => __('Edit Shop Page', 'rey-core'),
				),
			));
		}
	}

	/**
	 * Filter body css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{
		if( get_theme_mod('shop_catalog', false) == true ) {
			$classes[] = '--catalog-mode';
		}

		return $classes;
	}

	/**
	 * Filter css styles
	 * @since 1.1.2
	 */
	function css_styles($styles)
	{
		$styles[] = sprintf( ':root{ --woocommerce-grid-columns:%d; }', reycore_wc_get_columns('desktop') );
		return $styles;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Base
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}

endif;

ReyCore_WooCommerce_Base::getInstance();
