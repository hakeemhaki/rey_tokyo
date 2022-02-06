<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined( 'YITH_YWRAQ_VERSION' ) && !class_exists('ReyCore_Compatibility__YithRequestAQuote') ):

	class ReyCore_Compatibility__YithRequestAQuote
	{
		const ASSET_HANDLE = 'reycore-yithraq';

		public function __construct()
		{
			add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'button_html'], 999);
			add_action( 'reycore/woocommerce/loop/after_skin_init', [$this, 'maybe_wrap_link']);
			add_filter( 'yith_ywraq_product_already_in_list_message', [$this, 'already_added_text']);
			add_filter( 'yith_ywraq_product_added_to_list_message', [$this, 'added_text']);
			add_filter( 'ywraq_add_to_quote_args', [$this, 'link_class'], 20);
			add_action( 'acf/init', [ $this, 'add_plugin_settings' ] );
		}

		public function already_added_text( $text ){

			if( !is_single() && function_exists('YITH_Request_Quote') ){
				return sprintf(__('<a href="%s" class="btn btn-line-active">ALREADY ADDED!</a>', 'rey-core'), YITH_Request_Quote()->get_raq_page_url() );
			}

			return $text;
		}

		public function added_text( $text ){

			if( !is_single() && function_exists('YITH_Request_Quote') ){
				return sprintf(__('<a href="%s" class="btn btn-line-active">PRODUCT ADDED!</a>', 'rey-core'), YITH_Request_Quote()->get_raq_page_url() );
			}

			return $text;
		}

		public function link_class( $args ){

			if(get_option( 'ywraq_show_btn_link' ) !== 'button'){

				$class = $args['class'] . ' btn btn-line-active';

				$args['class'] = $class;

				if( isset($args['args']['class']) ){
					$args['args']['class'] = $class;
				}
			}

			return $args;
		}

		public function button_html( $html ){

			$is_enabled = true;

			if( class_exists('ACF') && get_field('yith_raq_enable_button', REY_CORE_THEME_NAME) === false ){
				$is_enabled = false;
			}

			if( !$is_enabled ){
				return $html;
			}

			if( class_exists('ACF') && get_field('yith_raq_enable_button', REY_CORE_THEME_NAME) ){
				return $html;
			}

			$product = wc_get_product();

			if( !$product ){
				return;
			}

			$html = (get_option( 'ywraq_hide_add_to_cart' ) === 'yes' ? '' : $html);

			ob_start();
			if( function_exists('YITH_YWRAQ_Frontend') ){
				YITH_YWRAQ_Frontend()->print_button();
			}
			$btn = ob_get_clean();

			$html .= apply_filters('reycore/compatibility/yith_raq/btn_html', $btn, $html);

			return $html;
		}

		public function maybe_wrap_link( $loop ){

			if( !method_exists($loop, 'wrap_add_to_cart_button') ){
				return;
			}

			add_filter( 'reycore/compatibility/yith_raq/btn_html', function($btn) use ($loop){
				return $loop->wrap_add_to_cart_button( $btn );
			});
		}

		public function enqueue_scripts(){
			reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
		}

		public function register_scripts(){
            wp_register_style( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
            wp_register_script( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/script.js', ['jquery'], REY_CORE_VERSION, true );
		}

		public function add_plugin_settings(){

			acf_add_local_field(array(
				'key'        => 'field_yith_raq_title',
				'type'       => 'message',
				'parent'     => 'group_5c990a758cfda',
				'message'    => __('<h1>YITH Request a quote</h1>', 'rey-core'),
				'menu_order' => 300,
			));

			acf_add_local_field(array(
				'key'          => 'field_yith_raq_enable_button',
				'name'         => 'yith_raq_enable_button',
				'label'        => esc_html__('Enable Button in Catalog', 'rey-core'),
				'type'         => 'true_false',
				'instructions' => esc_html__('Enable or disable the Request a Quote button in the products in catalog.', 'rey-core'),
				'default_value' => 1,
				'ui' => 1,
				'parent'       => 'group_5c990a758cfda',
				'menu_order'   => 300,
			));

		}

	}

	new ReyCore_Compatibility__YithRequestAQuote();
endif;
