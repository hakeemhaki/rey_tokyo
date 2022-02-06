<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && class_exists('ReyCore_WooCommerce_Loop') && !class_exists('ReyCore_WooCommerce_Loop_Skin_Proto') ):
/**
 * Proto Products Loop Skin
 */
class ReyCore_WooCommerce_Loop_Skin_Proto extends ReyCore_WooCommerce_Loop
{
	const TYPE = 'proto';
	const ASSET_HANDLE = 'reycore-loop-product-skin-proto';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/loop/register_skin', [$this, 'register_skin'] );
		add_action( 'reycore/kirki_fields/after_field=loop_skin', [ $this, 'add_customizer_options' ] );
		add_action( 'init', [$this, 'init'] );
	}

	function register_skin(){
		reyCoreLoopSkins()->add_skin([self::TYPE => esc_html__('Proto Skin', 'rey-core')]);
	}

	public function init()
	{
		add_filter( 'reycore/loop/component_hooks', [$this, 'get_component_hooks'] );
		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'load_skin_hooks']);
		add_action( 'reycore/woocommerce/loop/after_grid', [$this, 'remove_skin_hooks']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_styles']);
	}

	public function load_skin_hooks()
	{
		if( $this->get_loop_active_skin() !== self::TYPE ){
			return;
		}

		reyCoreAssets()->add_styles(self::ASSET_HANDLE);

		add_action( 'woocommerce_before_shop_loop_item', [$this, 'apply_extra_thumbs_filter'], 10);
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'remove_extra_thumbs_filter'], 10);
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'wrap_product_buttons'], 9);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 199);
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 19);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		add_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 19);
		add_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		add_filter( 'post_class', [$this,'custom_css_classes'], 20 );
		add_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );

		do_action( 'reycore/woocommerce/loop/after_skin_init', $this, self::TYPE);

	}

	public function remove_skin_hooks()
	{
		if( $this->get_loop_active_skin() !== self::TYPE ){
			return;
		}

		remove_action( 'woocommerce_before_shop_loop_item', [$this, 'apply_extra_thumbs_filter'], 10);
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'remove_extra_thumbs_filter'], 10);
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'wrap_product_buttons'], 9);
		remove_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 199);
		remove_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 19);
		remove_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		remove_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 19);
		remove_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		remove_filter( 'post_class', [$this,'custom_css_classes'], 20 );
		remove_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );


	}

	function add_customizer_options(){
		require_once REY_CORE_MODULE_DIR . 'loop-product-skin-proto/customizer-options.php';
	}

	/**
	 * Override default components.
	 *
	 * @since 1.3.0
	 */
	public function get_component_hooks( $components ){

		if( $this->get_loop_active_skin() !== self::TYPE ){
			return $components;
		}

		$component_hooks  =  [
			'brands'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'callback'      => [ $this, 'component_brands' ],
				'priority'      => 60,
			],
			'category'       => [
				'type'          => 'action',
				'tag'           => 'woocommerce_before_shop_loop_item_title',
				'callback'      => [ $this, 'component_product_category'],
				'priority'      => 70,
			],
			'quickview'      => [
				'bottom' => [
					'callback'      => [ $this, 'component_quickview_button' ],
				],
				'topright' => [
					'callback'      => [ $this, 'component_quickview_button' ],
				],
				'bottomright' => [
					'callback'      => [ $this, 'component_quickview_button' ],
				],
			],
			'wishlist'       => [
				'bottom' => [
					'callback'      => [ $this, 'component_wishlist' ],
				],
				'topright' => [
					'callback'      => [ $this, 'component_wishlist' ],
				],
				'bottomright' => [
					'callback'      => [ $this, 'component_wishlist' ],
				],
			],
		];

		$component_hooks = reycore__wp_parse_args( $component_hooks, $components );

		return $component_hooks;
	}

	function is_padded(){
		return get_theme_mod('proto_loop_padded', false);
	}

	/**
	 * Wraps the product details so it can be absolutely positioned
	 *
	 * @since 1.0.0
	 */
	public function wrap_product_details(){
		?>
		<div class="rey-loopDetails <?php echo $this->is_padded() ? '--padded' : '' ?>">
		<?php
	}

	/**
	 * Wrap product buttons
	 *
	 * @since 1.0.0
	 **/
	function wrap_product_buttons()
	{ ?>
		<div class="rey-loopButtons">
		<?php
	}


	/**
	 * Item Component - Quickview button
	 *
	 * @since 1.0.0
	 */
	public function component_quickview_button(){
		$start = $end = '';

		if( get_theme_mod('loop_quickview_position', 'bottom') !== 'bottom' ){
			$start = '<div class="rey-thPos-item --no-margins">';
			$end = '</div>';
		}

		echo $start;
		if( class_exists('ReyCore_WooCommerce_QuickView') ){
			echo ReyCore_WooCommerce_QuickView::getInstance()->get_button_html();
		}
		echo $end;
	}

	/**
	 * Item Component - Wishlist
	 *
	 * @since 1.3.0
	 */
	public function component_wishlist(){
		$start = $end = '';

		if( get_theme_mod('loop_wishlist_position', 'bottom') !== 'bottom' ){
			$start = '<div class="rey-thPos-item --no-margins">';
			$end = '</div>';
		}

		echo $start;
		echo ReyCore_WooCommerce_Wishlist::get_button_html();
		echo $end;
	}


	/**
	 * Adds custom CSS Classes
	 *
	 * @since 1.1.2
	 */
	function custom_css_classes( $classes )
	{
		if( apply_filters('reycore/woocommerce/loop/prevent_custom_css_classes', is_admin() && !wp_doing_ajax() ) ){
			return $classes;
		}

		if ( $this->is_product() ) {
			if( get_theme_mod('proto_loop_hover_animation', true) ) {
				$classes['hover-animated'] = 'is-animated';
			}
		}

		if( $this->is_padded() ){
			$classes['shadow_active'] = '--shadow-' . get_theme_mod('proto_loop_shadow', '1');
			$classes['shadow_hover'] = '--shadow-h-' . get_theme_mod('proto_loop_shadow_hover', '3');
		}

		if( ($general_css_classes = $this->general_css_classes()) && is_array($general_css_classes) ){
			$classes = array_merge($classes, $general_css_classes);
		}

		return $classes;
	}

	public function register_styles(){

		$styles[ self::ASSET_HANDLE ] = [
			'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css',
			'deps'    => [],
			'version'   => REY_CORE_VERSION,
		];

		reyCoreAssets()->register_asset('styles', $styles);
	}

}

new ReyCore_WooCommerce_Loop_Skin_Proto;

endif;
