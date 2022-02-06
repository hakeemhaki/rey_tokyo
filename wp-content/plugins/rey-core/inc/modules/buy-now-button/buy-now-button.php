<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_BuyNowButton') ):

class ReyCore_Wc_BuyNowButton
{
	private $settings = [];

	const ASSET_HANDLE = 'reycore-buy-now-button';

	public static $replace_atc = false;

	private static $_instance = null;

	private function __construct()
	{
		add_action( 'wp', [$this, 'init'] );
		add_action( 'wp_ajax_rey_buy_now', [$this, 'buy_now']);
		add_action( 'wp_ajax_nopriv_rey_buy_now', [$this, 'buy_now']);
		add_action( 'reycore/customizer/after_single_atc_fields', [ $this, 'add_customizer_options' ] );
	}

	public function init()
	{

		if( ! reycore__get_option('buynow_pdp__enable', false) ){
			return;
		}

		if( self::$replace_atc = get_theme_mod('buynow_pdp__replace_atc', false) ){
			add_filter('reycore/woocommerce/single_product/add_to_cart_button/simple', '__return_empty_string');
			add_filter('reycore/woocommerce/single_product/add_to_cart_button/variation', '__return_empty_string');
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		if( reycore_wc__is_catalog() ){
			return;
		}

		$position = get_theme_mod('buynow_pdp__position', 'inline');

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

		add_action( $hooks[$position]['hook'], [$this, 'display'], $hooks[$position]['priority'] );
	}

	function display(){

		$this->settings = apply_filters('reycore/module/buy_now', [
			'exclude_product_types' => [
				'external',
				'grouped',
			]
		]);

		$product = wc_get_product();

		if ( ! ( $product && $id = $product->get_id() ) ) {
			return;
		}

		if( in_array($product->get_type(), $this->settings['exclude_product_types'], true) ){
			return;
		}

		$classes = $text_class = [];

		$button_text = esc_html__('BUY NOW', 'rey-core');

		if( $custom_button_text = get_theme_mod('buynow_pdp__btn_text', '') ){
			$button_text = $custom_button_text;
		}

		$button_content = self::get_icon();

		if( ! $button_text ){
			$classes[] = '--no-text';
		}

		if( $product->get_type() === 'variable' ){
			$classes[] = '--disabled';
		}

		if( self::$replace_atc ){
			$classes[] = '--replace-atc';
		}

		$attributes = [];

		if( ($btn_style = get_theme_mod('buynow_pdp__btn_style', 'btn-secondary')) && $btn_style !== 'none' ){
			$classes['btn_style'] = 'btn ' . $btn_style;
			if( in_array($btn_style, ['btn-line', 'btn-line-active'], true) ){
				$text_class['text_style'] = 'btn ' . $btn_style;
				$classes['btn_style'] = 'btn --btn-text';
			}
		}

		$text_visibility = get_theme_mod('buynow_pdp__btn_text_visibility', 'show_desktop');

		if( $text_visibility && $button_text ){
			if( $text_visibility === 'show_desktop' ){
				$text_class[] = '--dnone-mobile --dnone-tablet';
			}
			$button_content .= sprintf('<span class="rey-buyNowBtn-text %s">%s</span>', esc_attr(implode(' ', $text_class)), $button_text);
		}

		$attributes['data-disabled-text'] = esc_html__('Please select some product options before proceeding.', 'rey-core');
		$attributes['data-id'] = $id;

		$button_content .= '<span class="rey-lineLoader"></span>';

		reycore__get_template_part('template-parts/woocommerce/buy-now-button', false, false, [
			'classes' => $classes,
			'text' => $button_text,
			'content' => $button_content,
			'attributes' => $attributes
		]);

		self::load_scripts();

	}

	public static function get_icon(){

		if( ! ( $icon = get_theme_mod('buynow_pdp__icon_type', 'bolt') ) ){
			return '';
		}

		if(
			function_exists('reycoreSvg') &&
			$icon === 'custom' &&
			($custom_icon = get_theme_mod('buynow_pdp__icon_custom', '')) &&
			($svg_code = reycoreSvg()->get_inline_svg( [ 'id' => $custom_icon, 'class' => 'rey-buyNowBtn-icon' ] )) ){
			return $svg_code;
		}

		return reycore__get_svg_icon__core([
			'id' => 'reycore-icon-' . $icon,
			'class' => 'rey-buyNowBtn-icon'
		]);
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

	function buy_now(){

		if ( ! (isset( $_REQUEST['product_id'] ) && $product_id = absint( $_REQUEST['product_id'] )) ) {
			wp_send_json_error();
		}

		// Return if cart object is not initialized.
		if ( ! is_object( WC()->cart ) ) {
			wp_send_json_error();
		}

		if( get_theme_mod('buynow_pdp__empty_cart', true) ){
			WC()->cart->empty_cart();
		}

		$quantity = isset($_REQUEST['quantity']) ? absint( $_REQUEST['quantity'] ) : 1;

		if ( isset( $_REQUEST['variation_id'] ) && $variation_id = absint( $_REQUEST['variation_id'] ) ) {
			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
		}
		else{
			WC()->cart->add_to_cart( $product_id, $quantity );
		}

		wp_send_json_success([
			'checkout_url' => wc_get_checkout_url()
		]);

	}

	function add_customizer_options($section){

		reycore_customizer__title([
			'title'       => esc_html__('BUY NOW', 'rey-core'),
			'description' => esc_html__('Settings for buy now button.', 'rey-core'),
			'section'     => $section,
			'size'        => 'md',
			'border'      => 'top',
			'style_attr'  => '--border-size: 3px;',
			'upper'       => true,
		]);

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'toggle',
			'settings'    => 'buynow_pdp__enable',
			'label'       => esc_html__( 'Enable button', 'rey-core' ),
			'section'     => $section,
			'default'     => false,
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'text',
			'settings'    => 'buynow_pdp__btn_text',
			'label'       => esc_html__( 'Button text', 'rey-core' ),
			'section'     => $section,
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('BUY NOW', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__btn_text_visibility',
			'label'       => esc_html__( 'Text visibility', 'rey-core' ),
			'section'     => $section,
			'default'     => 'show_desktop',
			'choices' => [
				'' => esc_html__('Hide', 'rey-core'),
				'show' => esc_html__('Show', 'rey-core'),
				'show_desktop' => esc_html__('Show text on desktop only', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__position',
			'label'       => esc_html__( 'Button Position', 'rey-core' ),
			'section'     => $section,
			'default'     => 'inline',
			'choices'     => [
				'inline' => esc_html__( 'Inline with ATC. button', 'rey-core' ),
				'before' => esc_html__( 'Before ATC. button', 'rey-core' ),
				'after' => esc_html__( 'After ATC. button', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__btn_style',
			'label'       => esc_html__( 'Button Style', 'rey-core' ),
			'section'     => $section,
			'default'     => 'btn-secondary',
			'choices'     => [
				'none' => esc_html__( 'None', 'rey-core' ),
				'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
				'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
				'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
				'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
				'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_bg',
			'label'       => esc_html__( 'Button Background Color', 'rey-core' ),
			'section'     => $section,
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper',
					'property' 		=> '--accent-color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_text',
			'label'       => esc_html__( 'Button Text Color', 'rey-core' ),
			'section'     => $section,
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper',
					'property' 		=> '--accent-text-color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_text_hover',
			'label'       => esc_html__( 'Button Hover Text Color', 'rey-core' ),
			'section'     => $section,
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper .btn:hover',
					'property' 		=> 'color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_bg_hover',
			'label'       => esc_html__( 'Button Hover Background Color', 'rey-core' ),
			'section'     => $section,
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper .btn:hover',
					'property' 		=> 'background-color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__icon_type',
			'label'       => esc_html__( 'Choose icon', 'rey-core' ),
			'section'     => $section,
			'default'     => 'bolt',
			'choices'     => [
				'' => esc_html__( 'Disabled', 'rey-core' ),
				'bolt' => esc_html__( 'Bolt', 'rey-core' ),
				'custom' => esc_html__( 'Custom', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'image',
			'settings'    => 'buynow_pdp__icon_custom',
			'label'       => esc_html__( 'Custom Icon (svg)', 'rey-core' ),
			'section'     => $section,
			'default'     => '',
			'choices'     => [
				'save_as' => 'id',
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__icon_type',
					'operator' => '==',
					'value'    => 'custom',
				],
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'toggle',
			'settings'    => 'buynow_pdp__empty_cart',
			'label'       => esc_html__( 'Empty cart before redirecting', 'rey-core' ),
			'section'     => $section,
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'toggle',
			'settings'    => 'buynow_pdp__replace_atc',
			'label'       => esc_html__( 'Replace Add To Cart button', 'rey-core' ),
			'section'     => $section,
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

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

ReyCore_Wc_BuyNowButton::getInstance();

endif;
