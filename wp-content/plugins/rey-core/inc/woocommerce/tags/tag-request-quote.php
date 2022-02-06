<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Request a quote button
 *
 * @since 1.2.0
 */
if( !class_exists('ReyCore_WooCommerce_RequestQuote') ):

class ReyCore_WooCommerce_RequestQuote {

	private $defaults = [];

	private $type = '';

	private static $_instance = null;

	private function __construct(){
		add_action('init', [$this, 'init']);
	}

	public function init(){

		if( !($this->type = get_theme_mod('request_quote__type', '')) ) {
			return;
		}

		$this->set_defaults();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/modal_template/show', '__return_true' );
		add_action( 'rey/after_site_wrapper', [$this, 'add_form_modal'], 50);
		add_action( 'woocommerce_single_product_summary', [$this, 'get_button_html'], 30);
		add_shortcode( 'rey_request_quote', [$this, 'get_button_html']);

	}

	public function register_assets(){

		$rtl = reyCoreAssets()::rtl();

		reyCoreAssets()->register_asset('styles', [
			'rey-wc-product-request-quote' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/request-quote/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-product'],
				'version'   => REY_CORE_VERSION,
			],
		]);

		reyCoreAssets()->register_asset('scripts', [
			'reycore-wc-product-page-request-quote' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-request-quote.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'localize' => [
					'name' => 'reycoreRequestQuoteParams',
					'params' => [
						'variation_aware' => $this->defaults['variation_aware'],
						'disabled_text' => $this->defaults['disabled_text']
					],
				],
			],
		]);

	}

	/**
	 * Set defaults
	 *
	 * @since 1.2.0
	 **/
	public function set_defaults()
	{
		$this->defaults = apply_filters('reycore/woocommerce/request_quote_defaults', [
			'title' => get_theme_mod('request_quote__btn_text', esc_html__( 'Request a Quote', 'rey-core' ) ),
			'product_title' => esc_html__( 'PRODUCT: ', 'rey-core' ),
			'close_position' => 'inside',
			'show_in_quickview' => false,
			'variation_aware' => get_theme_mod('request_quote__var_aware', false ),
			'disabled_text' => esc_html__('Please select some product options before requesting quote.', 'rey-core')
		]);
	}


	public function maybe_show_button(){

		if( $this->type === 'products' ){

			if( ! ($products = get_theme_mod('request_quote__products', '')) ){
				return false;
			}

			$get_products_ids = array_map( 'absint', array_map( 'trim', explode( ',', $products ) ) );

			if( ! in_array(get_the_ID(), $get_products_ids) ){
				return false;
			}
		}

		elseif( $this->type === 'categories' ){

			if( ! ($categories = get_theme_mod('request_quote__categories', [])) ){
				return false;
			}

			$terms = wp_get_post_terms( get_the_ID(), 'product_cat' );
			foreach ( $terms as $term ) $product_categories[] = $term->slug;

			if ( ! array_intersect($product_categories, $categories) ) {
				return false;
			}
		}

		if( get_query_var('rey__is_quickview', false) === true && $this->defaults['show_in_quickview'] === false ) {
			return false;
		}

		return true;
	}

	public function add_form_modal(){

		if( ! reycore_wc__is_product() ){
			return;
		}

		if( ! $this->maybe_show_button() ){
			return;
		}

		$form = apply_filters('reycore/woocommerce/request_quote/output', '', [
			'class' => 'rey-form--basic'
		] );

		if( empty($form) ){
			return;
		}

		reycore__get_template_part('template-parts/woocommerce/request-quote-modal', false, false, [
			'form' => $form,
			'defaults' => $this->defaults
		]);

	}


	/**
	* Add the button
	*
	* @since 1.2.0
	*/
	public function get_button_html( $args = [] ){

		if( ! $this->maybe_show_button() ){
			return;
		}

		if( ! empty($args) ){
			$this->defaults = array_merge($this->defaults, $args);
		}

		reycore__get_template_part('template-parts/woocommerce/request-quote-button', false, false, [
			'button_text' => $this->defaults['title']
		]);

		reyCoreAssets()->add_styles('rey-wc-product-request-quote');
		reyCoreAssets()->add_scripts('reycore-wc-product-page-request-quote');

		// load modal scripts
		add_filter('reycore/modals/always_load', '__return_true');
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

ReyCore_WooCommerce_RequestQuote::getInstance();

endif;
