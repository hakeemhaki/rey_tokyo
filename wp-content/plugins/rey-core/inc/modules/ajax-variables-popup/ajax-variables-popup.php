<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_AjaxVariablesPopup') ):

class ReyCore_Wc_AjaxVariablesPopup
{
	private $settings = [];

	const ASSET_HANDLE = 'reycore-ajax-variables-popup';

	public function __construct()
	{
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 20 );
		add_action( 'wp_ajax_loop_variable_product_add_to_cart', [$this, 'loop_variable_product_add_to_cart'] );
		add_action( 'wp_ajax_nopriv_loop_variable_product_add_to_cart', [$this, 'loop_variable_product_add_to_cart'] );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/woocommerce/ajax/scripts', [$this, 'loop_enqueue_scripts']);
		add_action( 'reycore/woocommerce/loop/scripts', [ $this, 'loop_enqueue_scripts' ] );
		add_action( 'reycore/elementor/product_grid/lazy_load_assets', [ $this, 'loop_enqueue_scripts' ] );
		add_filter( 'reycore/woocommerce/loop/add_to_cart/content', [ $this, 'add_preloader'], 20, 2 );
	}

	public function loop_enqueue_scripts(){

		if( ! $this->is_enabled() ){
			return;
		}

		// script
		reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
		wp_enqueue_script( 'wc-add-to-cart-variation' ); // can't laod it dynamically

		reyCoreAssets()->localize_script( self::ASSET_HANDLE, 'reyAjaxVariablesParams', [
			'styles' => self::get_assets_src('styles'),
			'scripts' => self::get_assets_src('scripts')
		] );

		// style
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

	public function script_params($params)
	{
		$params['loop_ajax_variable_products'] = $this->is_enabled();
		return $params;
	}

	public static function get_assets_src( $type = 'styles' ){

		if( is_product() ){
			return [];
		}

		$assets = [
			'styles' => [
				'rey-wc-product',
				'rey-plugin-wvs',
				'rey-wc-general',
			],
			'scripts' => [
				'reycore-wc-product-page-general',
			]
		];

		if( class_exists('ReyCore_WooCommerce_Single') && ReyCore_WooCommerce_Single::product_page_ajax_add_to_cart() ){
			$assets['scripts'][] = 'reycore-wc-product-page-ajax-add-to-cart';
		}

		if( get_theme_mod('single_atc_qty_controls', false) ){
			$assets['scripts'][] = 'reycore-wc-product-page-qty-controls';
		}

		return reyCoreAssets()->get_assets_uri($assets, $type);

	}

	function loop_variable_product_add_to_cart() {

		if( ! ( isset($_REQUEST['product_id']) && $product_id = absint($_REQUEST['product_id']) ) ){
			wp_send_json_error( esc_html__('Product ID not found.', 'rey-core') );
		}

		global $post, $product;

		remove_all_actions('woocommerce_before_add_to_cart_button');
		remove_all_actions('woocommerce_after_add_to_cart_button');

		if( class_exists('ReyCore_WooCommerce_Single') ){
			add_action( 'woocommerce_before_add_to_cart_button', [ ReyCore_WooCommerce_Single::getInstance(), 'wrap_cart_qty' ], 10);
			add_action( 'woocommerce_after_add_to_cart_button', 'reycore_wc__generic_wrapper_end', 5);
		}

		if( ($product = wc_get_product($product_id)) && $product->is_purchasable() && $product->is_type('variable') ){

			// Include WooCommerce frontend stuff
			wc()->frontend_includes();

			$post = get_post( $product_id );
			setup_postdata( $post );

			ob_start();

			echo sprintf('<div class="rey-productLoop-variationsForm woocommerce" data-id="%s">', $product_id);
			echo '<div class="rey-productLoop-variationsForm-overlay rey-overlay"></div>';
				echo '<div class="product">';
					echo '<span class="rey-productLoop-variationsForm-pointer"></span>';
					echo sprintf('<span class="rey-productLoop-variationsForm-close">%s</span>', reycore__get_svg_icon(['id' => 'rey-icon-close']));
					woocommerce_variable_add_to_cart();
				echo '</div>';
			echo '</div>';
			$data = ob_get_clean();

			wp_reset_postdata();

			wp_send_json_success( $data );
		}

		wp_send_json_error( esc_html__('Product not purchasable.', 'rey-core') );
	}

	function add_preloader( $content, $product ){

		if( ! $this->is_enabled() ){
			return $content;
		}

		if( $product->get_type() === 'variable' ){
			return $content . '<span class="rey-lineLoader __ajax-preloader"></span>';
		}

		return $content;
	}

	public function is_enabled() {
		return get_theme_mod('loop_ajax_variable_products', false);
	}

}

new ReyCore_Wc_AjaxVariablesPopup;

endif;
