<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_PriceInAtc') ):

class ReyCore_Wc_PriceInAtc
{
	private $settings = [];

	private $product;

	const ASSET_HANDLE = 'reycore-price-in-atc';

	public function __construct()
	{
		add_action( 'reycore/kirki_fields/after_field=single_atc__stretch', [ $this, 'add_customizer_options' ] );
		add_action( 'wp', [$this, 'init']);
	}

	public function init()
	{
		if( ! is_product() ){
			return;
		}

		$this->product = wc_get_product();

		if( ! $this->product ){
			return;
		}

		if( ! $this->product->is_purchasable() ){
			return;
		}

		if( ! self::is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'reycore/woocommerce/single_product/add_to_cart_button/simple', [ $this, 'add_in_button'], 30, 3 );
		add_filter( 'reycore/woocommerce/single_product/add_to_cart_button/variation', [ $this, 'add_in_button'], 30, 3 );

		$this->settings = apply_filters('reycore/module/price_in_atc', [
			'position' => 'after',
			'separator' => ''
		]);
	}

	public function enqueue_scripts(){
		reyCoreAssets()->add_scripts(['wnumb', self::ASSET_HANDLE]);
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

	function add_in_button($html, $product, $text){

		if( ! apply_filters('reycore/module/price_in_atc/should_print_price', true) ){
			return $html;
		}

		$search = '<span class="single_add_to_cart_button-text">';
		$replace = sprintf(
			'<span class="single_add_to_cart_button-text --price-in-atc" data-position="%2$s"><span class="__price" id="rey-price-in-atc" data-separator="%3$s">%1$s</span>',
			$product->get_price_html(),
			$this->settings['position'],
			$this->settings['separator']
		);
		return str_replace($search, $replace, $html);
	}

	function add_customizer_options($field_args){

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'toggle',
			'settings'    => 'single_product_atc__price',
			'label'       => esc_html__( 'Add price inside the button', 'rey-core' ),
			'section'     => $field_args['section'],
			'default'     => false,
		] );

	}

	public static function is_enabled() {
		return get_theme_mod('single_product_atc__price', false);
	}

}

new ReyCore_Wc_PriceInAtc;

endif;
