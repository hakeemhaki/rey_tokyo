<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_WooCommerce_QuickView') ):

	/**
	 * ReyCore_WooCommerce_QuickView class.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_WooCommerce_QuickView  {

		private static $_instance = null;

		private $loaded_assets = false;

		const ACTION = 'get_quickview_product';

		const ASSET_HANDLE = 'reycore-quickview';

		private function __construct()
		{
			add_action( 'wp_ajax_' . self::ACTION, [ $this, 'get_product'] );
			add_action( 'wp_ajax_nopriv_' . self::ACTION, [ $this, 'get_product'] );
			add_action( 'wp', [$this, 'wp']);

			// force default skin
			add_filter('theme_mod_single_skin', function( $opt ){
				if( $this->check_if_quickview_request() ){
					return 'default';
				}
				return $opt;
			});

			// force vertical gallery
			add_filter('theme_mod_product_gallery_layout', function( $opt ){
				if( $this->check_if_quickview_request() ){
					return 'vertical';
				}
				return $opt;
			});

			add_action( 'reycore/elementor/product_grid/lazy_load_assets', [$this, 'lazy_load_markup']);

		}

		function wp(){

			if( ! $this->is_enabled() ){
				return;
			}

			add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
			add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
			add_action( 'reycore/woocommerce/ajax/scripts', [$this, 'load_assets']);
			add_action( 'wp_footer', [ $this, 'panel_markup'], 10 );
		}

		function check_if_quickview_request(){

			if( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === self::ACTION  ){
				return true;
			}

			return false;
		}

		/**
		 * Filter main script's params
		 *
		 * @since 1.0.0
		 **/
		public function script_params($params)
		{

			$params['quickview_only'] = get_theme_mod('loop_quickview__link_all', false);
			$params['quickview_gallery_type'] = get_theme_mod('loop_quickview_gallery_type', 'vertical');

			return $params;
		}

		public function register_assets(){

			reyCoreAssets()->register_asset('styles', [
				self::ASSET_HANDLE => [
					'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low'
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


		/**
		 * Adds scripts. No need to load in Head,
		 * as it's a late event based script.
		 */
		public function load_assets()
		{
			if( $this->loaded_assets ){
				return;
			}

			add_filter('reycore/quickview/can_load_markup', '__return_true');

			/**
			 * Load for desktop only
			 * since it's a desktop only feature.
			 */
			if( reyCoreAssets()->mobile ){
				return;
			}

			reyCoreAssets()->add_scripts('animejs');

			// enqueued here to prevent load on mobile
			reyCoreAssets()->add_styles( self::ASSET_HANDLE );

			reyCoreAssets()->add_scripts( self::ASSET_HANDLE );
			reyCoreAssets()->localize_script( self::ASSET_HANDLE, 'reyQuickviewParams', [
				'styles' => self::get_assets_src('styles'),
				'scripts' => self::get_assets_src('scripts')
			] );

			wp_enqueue_script( 'wc-add-to-cart-variation' ); // can't laod it dynamically

			$this->loaded_assets = true;
		}

		public static function get_assets_src( $type = 'styles' ){

			$assets_to_return = [];

			$assets = [
				'styles' => [
					'rey-wc-general',
					'rey-wc-product',
					'rey-wc-product-gallery',
					'simple-scrollbar',
					'reycore-post-social-share',
					'rey-plugin-wvs',
					'rey-splide'
				],
				'scripts' => [
					'rey-splide',
					'simple-scrollbar',
					'imagesloaded',
					'reycore-wc-product-page-general',
					'reycore-wc-product-gallery',
				]
			];

			if( is_product() ){
				unset($assets['styles']['rey-wc-product']);
				unset($assets['styles']['rey-wc-product-gallery']);
				unset($assets['styles']['reycore-post-social-share']);
				unset($assets['styles']['rey-splide']);
				unset($assets['scripts']['reycore-wc-product-page-general']);
				unset($assets['scripts']['reycore-wc-product-gallery']);
				unset($assets['scripts']['rey-splide']);
			}

			if( class_exists('ReyCore_WooCommerce_Single') && ReyCore_WooCommerce_Single::product_page_ajax_add_to_cart() ){
				$assets['scripts'][] = 'reycore-wc-product-page-ajax-add-to-cart';
			}

			if( get_theme_mod('single_atc_qty_controls', false) ){
				$assets['scripts'][] = 'reycore-wc-product-page-qty-controls';
			}

			if( ! empty( get_theme_mod('single__accordion_items', []) ) ){
				$assets['scripts'][] = 'reycore-wc-product-page-accordion-tabs';
			}

			return reyCoreAssets()->get_assets_uri($assets, $type);

		}

		/**
		 * Get product
		 *
		 * @since   1.0.0
		 */
		public function get_product()
		{
			if( ! (isset($_POST['id']) && ($pid = absint($_POST['id']))) ){
				wp_send_json_error(esc_html__('Missing product ID.', 'rey-core'));
			}

			$this->fix_page();

			ob_start();

			reycore__get_template_part('template-parts/woocommerce/quickview-panel', false, false, [
				'pid' => $pid
			]);

			$data = ob_get_clean();

			wp_send_json_success($data);
		}

		public function panel_markup()
		{
			if( ! $this->is_enabled() ){
				return;
			}

			if( ! apply_filters('reycore/quickview/can_load_markup', false) ){
				return;
			}

			if( is_admin() || is_checkout() ){
				return;
			}

			if( ! reycore__can_add_public_content() ){
				return;
			}

			reycore__get_template_part('template-parts/woocommerce/quickview-markup');
		}

		private function fix_page(){

			// Include WooCommerce frontend stuff
			wc()->frontend_includes();

			set_query_var('rey__is_quickview', true);

			if( get_theme_mod('shop_catalog', false) == true ){
				add_filter( 'woocommerce_is_purchasable', '__return_false');
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
			}

			do_action('reycore/woocommerce/product_ajax');

			add_filter('woocommerce_post_class', function($classes){

				if( array_key_exists('product_page_class', $classes) ){
					unset($classes['product_page_class']);
				}

				return $classes;
			});

			// re-load gallery skin
			if( class_exists('ReyCore_WooCommerce_ProductGallery_Base') ){
				ReyCore_WooCommerce_ProductGallery_Base::getInstance()->load_current_gallery_type();
			}

			// disable mobile gallery (panel is lg+)
			add_filter('reycore/woocommerce/allow_mobile_gallery', '__return_false');

			// make short desc shorter
			add_filter('reycore_theme_mod_product_short_desc_toggle_v2', '__return_true');

			remove_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', 1 );

			/**
			 * add custom title with link
			 */
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			add_action( 'woocommerce_single_product_summary', function() {
				echo sprintf( '<h1 class="product_title entry-title"><a href="%s">%s</a></h1>',
					get_the_permalink(),
					get_the_title()
				);
			}, 5 );

			/**
			 * add specifications
			 */
			add_action('woocommerce_single_product_summary', function(){

				if( reycore_wc_get_loop_components('quickview') && get_theme_mod('loop_quickview_specifications', true) ){

					global $product;

					if( !$product ){
						return;
					}

					ob_start();
					do_action( 'woocommerce_product_additional_information', $product );
					$content = ob_get_clean();

					if( ! empty($content) ){
						echo '<div class="rey-qvSpecs">';
						$heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Specifications', 'rey-core' ) );
						if ( $heading ) :
							printf('<h2>%s</h2>', esc_html( $heading ));
						endif;
						echo $content;
						echo '</div>';
					}
				}

			}, 100);
		}

		/**
		 * Print quickview button
		 */
		public function get_button_html( $product_id = '', $button_class = 'button' )
		{

			$this->load_assets();

			if( get_theme_mod('loop_quickview__link_all', false) && get_theme_mod('loop_quickview__link_all_hide', false) ){
				return;
			}

			if( $product_id !== '' ){
				$id = $product_id;
				$product = wc_get_product($id);
			}
			else {

				$product = wc_get_product();

				if( ! $product ){
					global $product;
				}

				if ( ! ($product && $id = $product->get_id()) ) {
					return;
				}

			}

			$text = apply_filters('reycore/woocommerce/quickview/text', esc_html_x('QUICKVIEW', 'Quickview button text in products listing.', 'rey-core'));

			$button_content = $text;
			$type = get_theme_mod('loop_quickview', '1');
			$btn_style = get_theme_mod('loop_quickview_style', 'under');

			if( $type === 'icon' ){
				$button_content = reycore__get_svg_icon__core([ 'id'=>'reycore-icon-' . get_theme_mod('loop_quickview_icon_type', 'eye') ]);
				$button_class .= ' rey-btn--' . $btn_style;
				$button_class .= ' rey-btn--qicon';
			}

			if( $type === '1' ){
				$button_class .= ' rey-btn--' . $btn_style;
			}

			$btn_html = sprintf(
				'<a href="%5$s" class="%1$s rey-quickviewBtn js-rey-quickviewBtn" data-id="%2$s" title="%3$s">%4$s</a>',
				$button_class,
				esc_attr($id),
				$text,
				$button_content,
				esc_url( get_permalink($id) )
			);

			add_action( 'wp_footer', [ $this, 'panel_markup'], 10 );

			return apply_filters('reycore/woocommerce/quickview/btn_html', $btn_html, $product);
		}

		function lazy_load_markup( $el_settings ){

			if( ! $this->is_enabled() ){
				return;
			}

			if( isset($el_settings['hide_quickview']) && '' !== $el_settings['hide_quickview'] ){
				return;
			}

			$this->load_assets();

			add_action( 'wp_footer', [ $this, 'panel_markup'], 10 );
		}

		function is_enabled(){
			$qv_positions = reycore_wc_get_loop_components('quickview');
			return in_array(true, $qv_positions, true);
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_WooCommerce_QuickView
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_WooCommerce_QuickView::getInstance();

endif;
