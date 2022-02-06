<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Loop_Skin_Wrapped') ):
/**
 * Wrapped Products loop skin
 */
class ReyCore_WooCommerce_Loop_Skin_Wrapped extends ReyCore_WooCommerce_Loop
{
	const TYPE = 'wrapped';

	public function __construct()
	{
		add_action( 'init', [$this, 'init'] );
	}

	public function init()
	{
		add_filter( 'reycore/loop/component_hooks', [$this, 'get_component_hooks'] );
		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'load_skin_hooks']);
		add_action( 'reycore/woocommerce/loop/after_grid', [$this, 'remove_skin_hooks']);
	}

	public function load_skin_hooks()
	{
		if( $this->get_loop_active_skin() !== self::TYPE ){
			return;
		}

		add_action( 'woocommerce_before_shop_loop_item', [$this, 'apply_extra_thumbs_filter'], 10);
		add_action( 'woocommerce_after_shop_loop_item', [$this, 'remove_extra_thumbs_filter'], 10);
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 20);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		add_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 20);
		add_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		add_filter( 'post_class', [$this,'custom_css_classes'], 20 );
		add_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );
		add_filter( 'reycore/woocommerce/product_badges_positions', [$this, 'set_product_badges_positions'] );
		add_filter( 'rey/main_script_params', [ $this, 'wrapped_script_params'], 20 );
		add_filter( 'theme_mod_loop_extra_media_disable_mobile', '__return_true', 10 );

		do_action( 'reycore/woocommerce/loop/after_skin_init', $this, self::TYPE);
	}


	public function remove_skin_hooks()
	{
		if( $this->get_loop_active_skin() !== self::TYPE ){
			return;
		}

		remove_action( 'woocommerce_before_shop_loop_item', [$this, 'apply_extra_thumbs_filter'], 10);
		remove_action( 'woocommerce_after_shop_loop_item', [$this, 'remove_extra_thumbs_filter'], 10);
		remove_action( 'woocommerce_before_shop_loop_item_title', [$this, 'wrap_product_details'], 20);
		remove_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 200 );
		remove_action( 'woocommerce_before_subcategory_title', [$this, 'wrap_product_details'], 20);
		remove_action( 'woocommerce_after_subcategory_title', 'reycore_wc__generic_wrapper_end', 200 );
		remove_filter( 'post_class', [$this,'custom_css_classes'], 20 );
		remove_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );
		remove_filter( 'reycore/woocommerce/product_badges_positions', [$this, 'set_product_badges_positions'] );
		remove_filter( 'rey/main_script_params', [ $this, 'wrapped_script_params'], 20 );
		remove_filter( 'theme_mod_loop_extra_media_disable_mobile', '__return_true', 10 );

	}

	/**
	 * Override default components.
	 *
	 * @since 1.3.0
	 */
	public function get_component_hooks($components){

		if( $this->get_loop_active_skin() !== self::TYPE ){
			return $components;
		}

		$component_hooks  =  [
			'brands'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => [ $this, 'component_brands' ],
				'priority'      => 60,
			],
			'category'       => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => [ $this, 'component_product_category'],
				'priority'      => 70,
			],
			'prices'         => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_price',
				'priority'      => 60,
			],
			'title'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => 'woocommerce_template_loop_product_title',
				'priority'      => 50,
			],
			'excerpt'          => [
				'type'          => 'action',
				'tag'           => 'woocommerce_after_shop_loop_item',
				'callback'      => [$this, 'component_excerpt'],
				'priority'      => 50,
			],
			'new_badge'         => [
				'tag'           => 'reycore/loop_inside_thumbnail/top-right',
			],
			'variations'         => [
				'type'          => 'action',
				'tag'           => 'reycore/loop_inside_thumbnail/top-left',
				'priority'      => 10,
			],
		];

		$component_hooks = reycore__wp_parse_args( $component_hooks, $components );

		return $component_hooks;
	}

	/**
	 * Wraps the product details so it can be absolutely positioned
	 *
	 * @since 1.0.0
	 */
	public function wrap_product_details(){
		?>
		<div class="rey-loopWrapper-details">
		<?php
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
			if( get_theme_mod('wrapped_loop_hover_animation', '1') == '1' ) {
				$classes['hover-animated'] = 'is-animated';
			}

			if( get_theme_mod('wrapped_loop_item_height', '') !== '' ) {
				$classes['custom-height'] = '--custom-height';
			}

		}

		if( $general_css_classes = $this->general_css_classes() ){
			$classes = array_merge($classes, $general_css_classes);
		}

		return $classes;
	}

	function set_product_badges_positions($positions){

		return [
			'top_left' => 'reycore/loop_inside_thumbnail/top-left',
			'top_right' => 'reycore/loop_inside_thumbnail/top-right',
			'bottom_left' => ['woocommerce_before_shop_loop_item_title', 21],
			'bottom_right' => ['woocommerce_before_shop_loop_item_title', 21],
			'before_title' => ['woocommerce_before_shop_loop_item_title', 21],
			'after_content' => ['woocommerce_after_shop_loop_item', 199],
		];

	}

	public function wrapped_script_params( $params ){

		// reset
		if( get_theme_mod('product_items_eq', false) && isset($params['js_params']['equalize_product_items']) ){
			$params['js_params']['equalize_product_items'] = [];
		}

		return $params;
	}

}
new ReyCore_WooCommerce_Loop_Skin_Wrapped;

endif;
