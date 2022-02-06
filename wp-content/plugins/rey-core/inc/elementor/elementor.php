<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('ReyCoreElementor')):
	/**
	 * Elementor integration
	 */
	final class ReyCoreElementor
	{
		/**
		 * Holds the reference to the instance of this class
		 * @var ReyCoreElementor
		 */
		private static $_instance = null;

		/**
		 * Holds the widgets that are loaded
		 */
		public $widgets = [];

		/**
		 * Holds the absolute path to widgets folder
		 */
		public $widgets_dir = '';

		/**
		 * Holds the local elementor widgets path
		 */
		public $widgets_folder = 'inc/elementor/widgets/';

		const REY_TEMPLATE = 'template-builder.php';
		/**
		 * ReyCoreElementor constructor.
		 */
		private function __construct()
		{
			// Exit if Elementor is not active
			if ( ! reycore__theme_active() || ! did_action( 'elementor/loaded' ) ) {
				return;
			}

			$this->includes();
			$this->get_widgets();
			$this->init_hooks();
		}

		public function includes(){
			require_once REY_CORE_DIR . 'inc/elementor/widgets-manager.php';
			require_once REY_CORE_DIR . 'inc/elementor/template-library/library.php';
			require_once REY_CORE_DIR . 'inc/elementor/tag-posts.php';
		}

		public function init_hooks(){
			add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ] );
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
			add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );
			add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_scripts'] );
			add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
			add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_frontend_styles'] );
			add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_frontend_scripts'] );
			add_action( 'elementor/editor/after_save', [ $this, 'elementor_to_acf'], 10);
			if ( ! empty( $_REQUEST['action'] ) && 'elementor' === $_REQUEST['action'] && is_admin() ) {
				add_action( 'init', [ $this, 'register_wc_hooks' ], 5 /* Priority = 5, in order to allow plugins remove/add their wc hooks on init. */);
			}
			add_action( 'init', [ $this, 'load_elements_overrides' ], 5 );
			add_filter( 'post_class', [ $this, 'add_product_post_class' ] , 20);
			add_filter( 'elementor/editor/localize_settings', [ $this, 'localized_settings' ], 10 );
			add_action( 'wp', [ $this, 'get_page_settings'], 10);
			add_filter( 'acf/validate_post_id', [ $this, 'acf_validate_post_id'], 10, 2);
			add_action( 'rey/flush_cache_after_updates', [ $this, 'flush_cache' ] );
			add_action( 'reycore/assets/cleanup', [ $this, 'flush_cache' ] );
			add_action( 'acf/init', [ $this, 'add_theme_settings' ] );
			add_action( 'elementor/editor/init', [ $this, 'animations_hide_native_anim_options' ] );
			add_action( 'customize_save_after', [ $this, 'update_elementor_schemes' ]);
			add_action( 'admin_init', [$this, 'filter_html_tag'] );
			add_filter( 'template_include', [ $this, 'template_include' ], 11 /* After Plugins/WooCommerce */ );
			add_filter( 'admin_body_class', [$this, 'shop_page_disable_button']);
			add_filter( 'body_class', [$this, 'add_version_class']);
			add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
			add_action( 'elementor/ajax/register_actions', [$this, 'set_option_event'] );
			add_filter( 'reycore/html_class_attr', [ $this, 'html_classes'], 10 );
			add_action( 'reycore/elementor/widget/construct', [$this, 'widget_init'] );
			add_filter( "theme_elementor_library_templates", [$this, 'library__page_templates_support'] );
			add_filter( 'elementor/frontend/builder_content_data', [$this, 'set_post_id'], 5, 2 );
			add_action( 'elementor/frontend/get_builder_content', [$this, 'unset_post_id'], 20);
			add_action( 'wp_ajax_reycore_element_lazy', [$this, 'load_lazy_element']);
			add_action( 'wp_ajax_nopriv_reycore_element_lazy', [$this, 'load_lazy_element']);
			add_action( 'wp_ajax_reycore_product_grid_load_more', [$this, 'product_grid_load_more']);
			add_action( 'wp_ajax_nopriv_reycore_product_grid_load_more', [$this, 'product_grid_load_more']);
		}

		function set_post_id( $data, $post_id ){

			if( ! isset($GLOBALS['elem_post_id']) && ! isset($GLOBALS['elem_post_id_prev']) ){
				$GLOBALS['elem_post_id_prev'] = $post_id;
			}

			$GLOBALS['elem_post_id'] = $post_id;

			return $data;
		}

		function unset_post_id(){

			if ( isset($GLOBALS['elem_post_id']) && isset($GLOBALS['elem_post_id_prev']) ){
				$GLOBALS['elem_post_id'] = $GLOBALS['elem_post_id_prev'];
				return;
			}

			unset($GLOBALS['elem_post_id']);
		}

		/**
		 * Get major Elementor version
		 *
		 * @since 1.6.12
		 */
		function get_elementor_major_version(){

			if( defined('ELEMENTOR_VERSION') ){
				$version = explode( '.', ELEMENTOR_VERSION);
				if( isset($version[0]) ){
					return absint($version[0]);
				}
			}

			return false;
		}

		function legacy_mode( $mode_name = null ) {
			return \Elementor\Plugin::instance()->get_legacy_mode( $mode_name );
		}

		function is_optimized_dom() {

			if( isset($this->is_optimized_dom) ){
				return $this->is_optimized_dom;
			}

			if( $this->get_elementor_major_version() < 3 ){
				return $this->is_optimized_dom = false;
			}

			return $this->is_optimized_dom = $this->legacy_mode('elementWrappers') !== true; // element wrappers are not enabled
		}

		function is_pushback_fallback_enabled(){
			return apply_filters('reycore/elementor/pushback_fallback', false);
		}

		/**
		 * Adds version class
		 *
		 * @since 1.6.12
		 */
		function add_version_class( $classes ){
			$classes['optimized-dom'] = 'elementor-' . $this->is_optimized_dom() ? 'opt' : 'unopt';
			return $classes;
		}

		public function script_params($params)
		{
			$params['optimized_dom'] = $this->is_optimized_dom();
			$params['el_pushback_fallback'] = $this->is_pushback_fallback_enabled();
			return $params;
		}

		/**
		 * Disable page builder button
		 *
		 * @since 1.6.x
		 */
		function shop_page_disable_button($classes){

			if( class_exists('WooCommerce') && (get_the_ID() === wc_get_page_id('shop')) && apply_filters('reycore/elementor/hide_shop_page_btn', true) ){
				$classes .= ' --prevent-elementor-btn ';
			}

			return $classes;
		}
		/**
		 * Get widgets
		 *
		 * @since 1.0.0
		 */
		public function get_widgets(){
			$this->widgets_dir = trailingslashit( REY_CORE_DIR . $this->widgets_folder );
			array_map( [$this, 'get_widget_basename'], glob( $this->widgets_dir . '*' ));
		}

		public function get_widgets_list(){
			return $this->widgets;
		}

		/**
		 * Get widgets basename
		 *
		 * @since 1.0.0
		 */
		private function get_widget_basename($item){
			if( is_dir($item) ){
				$this->widgets[] = basename( $item );
			}
		}

		/**
		 * On Widgets Registered
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function register_widgets( $widgets_manager ) {

			$disabled_elements = class_exists('ReyCore_WidgetsManager') ? ReyCore_WidgetsManager::get_disabled_elements() : [];

			foreach ( $this->widgets as $widget ) {
				$this->register_widget( $widget, $widgets_manager );
			}
		}

		/**
		 * Register widget by folder name
		 *
		 * @param  string $widget            Widget folder name.
		 * @param  object $widgets_manager Widgets manager instance.
		 * @return void
		 */
		public function register_widget( $widget, $widgets_manager ) {

			$class = ucwords( str_replace( '-', ' ', $widget ) );
			$class = str_replace( ' ', '_', $class );
			$class = sprintf( 'ReyCore_Widget_%s', $class );

			// Load Skins
			foreach ( glob( trailingslashit( $this->widgets_dir . $widget ) . "skin-*.php") as $skin) {
				require_once $skin;
			}
			// Load widget
			if( ( $file = trailingslashit( $this->widgets_dir . $widget ) . $widget . '.php' ) && is_readable($file) ){
				require_once $file;
			}

			if ( class_exists( $class ) ) {
				// Register widget
				$widgets_manager->register_widget_type( new $class );
			}

		}


		/**
		 * Load custom Elementor elements overrides
		 *
		 * @since 1.0.0
		 */
		public function load_elements_overrides()
		{
			$elements_dir = REY_CORE_DIR . 'inc/elementor/custom/';
			$elements = glob( $elements_dir . '*.php' );

			foreach ($elements as $element) {

				$base  = basename( $element, '.php' );
				$class = ucwords( str_replace( '-', ' ', $base ) );
				$class = str_replace( ' ', '_', $class );
				$class = sprintf( 'ReyCore_Element_%s', $class );

				if( ( $file = $elements_dir . $base . '.php' ) && is_readable($file) ){
					require $file;
				}
				if ( class_exists( $class ) ) {
					new $class;
				}
			}
		}

		public function register_controls() {

			require_once REY_CORE_DIR . 'inc/elementor/controls/rey-query.php';

			// Register Controls
			\Elementor\Plugin::instance()->controls_manager->register_control( 'rey-query', new ReyCore__Control_Query() );
		}

		/**
		 * Add Rey Widget Categories
		 *
		 * @since 1.0.0
		 */
		public function add_elementor_widget_categories( $elements_manager ) {

			$elements_manager->add_category(
				'rey-header',
				[
					'title' => __( 'REY Theme - Header', 'rey-core' ),
					'icon' => 'fa fa-plug',
					// 'active' => false,
				]
			);

			$elements_manager->add_category(
				'rey-theme',
				[
					'title' => __( 'REY Theme', 'rey-core' ),
					'icon' => 'fa fa-plug',
				]
			);
			$elements_manager->add_category(
				'rey-theme-covers',
				[
					'title' => __( 'REY Theme - Covers (sliders)', 'rey-core' ),
					'icon' => 'fa fa-plug',
				]
			);
			if( class_exists('WooCommerce') ){
				$elements_manager->add_category(
					'rey-woocommerce',
					[
						'title' => __( 'REY Theme - WooCommerce', 'rey-core' ),
						'icon' => 'fa fa-plug',
					]
				);
			}

		}

		function maybe_load_rey_grid(){

			$opt = ! ( reycore__acf_get_field('elementor_grid', REY_CORE_THEME_NAME) === 'default' );

			if( get_page_template_slug() === 'template-canvas.php' ){
				$opt = false;
			}

			return apply_filters('reycore/elementor/load_grid', $opt);
		}

		function elementor_styles(){

			$elementor_edo_suffix = $this->is_optimized_dom() ? 'opt' : 'unopt';
			$direction_suffix = is_rtl() ? '-rtl' : '';
			$elementor_frontend_dependencies = ['elementor-frontend', 'reycore-elementor-frontend-dom'];

			$styles = [];

			// Use Rey Grid
			if( $this->maybe_load_rey_grid() ){

				$styles['reycore-elementor-frontend-grid'] = [
					'src'     => REY_CORE_URI . 'assets/css/elementor-components/grid-'. $elementor_edo_suffix .'/grid-'. $elementor_edo_suffix . $direction_suffix . '.css',
					'deps'    => ['elementor-frontend'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'high'
				];

				$elementor_frontend_dependencies[] = 'reycore-elementor-frontend-grid';
			}

			// Elementor "Optimized DOM Output" specific styles
			$styles['reycore-elementor-frontend-dom'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/dom-'. $elementor_edo_suffix .'/dom-'. $elementor_edo_suffix . $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			];

			// TODO extra cleanup, make high-priority stylesheet
			$styles['reycore-elementor-frontend'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/general/general'. $direction_suffix . '.css',
				'deps'    => $elementor_frontend_dependencies,
				'version'   => REY_CORE_VERSION,
			];

			$styles['reycore-elementor-entrance-animations'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/animations/animations'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			];

			$styles['reycore-elementor-heading-animation'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading-animation/heading-animation'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];

			$styles['reycore-elementor-heading-highlight'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading-highlight/heading-highlight'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];

			$styles['reycore-elementor-heading-special'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading-special/heading-special'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];

			$styles['reycore-elementor-modal-section'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/modal-section/modal-section'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			];

			$styles['reycore-elementor-scroll-deco'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/scroll-deco/scroll-deco'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];

			$styles['reycore-elementor-scroll-effects'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/scroll-effects/scroll-effects'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];

			$styles['reycore-elementor-section-slideshow'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/section-slideshow/section-slideshow'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];

			$styles['reycore-elementor-sticky-gs'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/sticky-gs/sticky-gs'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			];

			$styles['reycore-elementor-hide-on-demand'] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/hide-on-demand/hide-on-demand'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			];

			return $styles;
		}

		function register_assets(){
			reyCoreAssets()->register_asset('styles', $this->elementor_styles());
			reyCoreAssets()->register_asset('scripts', $this->elementor_scripts());
		}

		/**
		 * Enqueue ReyCore's Elementor Frontend CSS
		 */
		public function enqueue_frontend_styles() {

			reyCoreAssets()->add_styles([
				'reycore-elementor-frontend-dom',
				'reycore-elementor-frontend-grid',
				'reycore-elementor-frontend',
				'rey-wc-elementor'
			]);

			// load entrance animations CSS
			if( $this->animations_enabled() ){
				reyCoreAssets()->add_styles('reycore-elementor-entrance-animations');
			}

			if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) {
				wp_enqueue_style('reycore-frontend-admin');
				wp_enqueue_script('reycore-elementor-disable-element');
			}
		}

		function elementor_scripts(){
			return [

				'jquery-mousewheel' => [
					'src'     => REY_CORE_URI . 'assets/js/lib/jquery-mousewheel.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
					'plugin' => true
				],

				'threejs' => [
					'external' => true,
					'src'     => 'https://cdnjs.cloudflare.com/ajax/libs/three.js/109/three.min.js',
					'deps'    => [],
					'version'   => 'r109',
					'localize' => [
						'name' => 'reyThreeConfig',
						'params' => [
							'displacements' => [
								'https://i.imgur.com/t4AA2A8.jpg',
								'https://i.imgur.com/10UwPUy.jpg',
								'https://i.imgur.com/tO1ukJf.jpg',
								'https://i.imgur.com/iddaUQ7.png',
								'https://i.imgur.com/YbFcFOJ.png',
								'https://i.imgur.com/JzGo2Ng.jpg',
								'https://i.imgur.com/0toUHNF.jpg',
								'https://i.imgur.com/NPnfoR8.jpg',
								'https://i.imgur.com/xpqg1ot.jpg',
								'https://i.imgur.com/Ttm5Vj4.jpg',
								'https://i.imgur.com/wrz3VyW.jpg',
								'https://i.imgur.com/rfbuWmS.jpg',
								'https://i.imgur.com/NRHQLRF.jpg',
								'https://i.imgur.com/G29N5nR.jpg',
								'https://i.imgur.com/tohZyaA.jpg',
								'https://i.imgur.com/YvRcylt.jpg',
							],
						],
					],
				],

				'distortion-app' => [
					'src'     => REY_CORE_URI . 'assets/js/lib/distortion-app.js',
					'deps'    => ['threejs', 'jquery'],
					'version'   => '1.0.0',
				],

				'lottie' => [
					'src'     => REY_CORE_URI . 'assets/js/lib/lottie.min.js',
					'deps'    => ['threejs', 'jquery'],
					'version'   => '5.6.8',
					'plugin' => true
				],

				'reycore-elementor-frontend' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/general.js',
					'deps'    => ['elementor-frontend', 'rey-script', 'reycore-scripts'],
					'version'   => REY_CORE_VERSION,
					'localize' => [
						'name' => 'reyElementorFrontendParams',
						'params' => [
							'compatibilities' => self::get_compatibilities(),
							'ajax_url'      => admin_url( 'admin-ajax.php' ),
							'ajax_nonce'    => wp_create_nonce('reycore-ajax-verification'),
							'is310' => version_compare('3.1.0', ELEMENTOR_VERSION, '<=')
						],
					],
				],

				'reycore-elementor-entrance-animations' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/entrance-animations.js', // load everywhere where aninations enabled
					'deps'    => ['animejs', 'scroll-out'],
					'version'   => REY_CORE_VERSION,
				],

				'reycore-elementor-modal' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/modal.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-scroll-clip' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/scroll-clip.js',
					'deps'    => ['scroll-out'],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-scroll-colorize' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/scroll-colorize.js',
					'deps'    => ['scroll-out'],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-scroll-deco' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/scroll-deco.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-disable-element' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/disable-element.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-accordion' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-accordion.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-button-add-to-cart' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-button-add-to-cart.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-carousel-links' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-carousel-links.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-column-click' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-column-click.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-column-sticky' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-column-sticky.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-column-video' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-column-video.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-header-navigation' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-header-navigation.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-header-wishlist' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-header-wishlist.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-heading' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-heading.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-image-carousel' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-image-carousel.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-section-hod' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-section-hod.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-section-pushback' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-section-pushback.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-section-slideshow' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-section-slideshow.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-section-sticky' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-section-sticky.js',
					'deps'    => ['jquery', 'imagesloaded'],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-section-video' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-section-video.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-prod-grid-section-carousel' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-prod-grid-section-carousel.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-woo-prod-gallery' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-woo-prod-gallery.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],
				'reycore-elementor-elem-lazy-load' => [
					'src'     => REY_CORE_URI . 'assets/js/elementor/elem-lazy-load.js',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
				],

			];
		}

		/**
		 * Load Frontend JS
		 *
		 * @since 1.0.0
		 */
		public function enqueue_frontend_scripts()
		{
			reyCoreAssets()->add_scripts('reycore-elementor-frontend');

			if( $this->is_pushback_fallback_enabled() ){
				reyCoreAssets()->add_scripts('reycore-elementor-elem-section-pushback');
			}
		}

		/**
		 * Load Editor JS
		 *
		 * @since 1.0.0
		 */
		public function editor_scripts() {
			wp_enqueue_style( 'rey-core-elementor-editor-css', REY_CORE_URI . 'assets/css/elementor-editor.css', [], REY_CORE_VERSION );
			wp_enqueue_script( 'rey-core-elementor-editor', REY_CORE_URI . 'assets/js/elementor-editor/elementor-editor.js', [], REY_CORE_VERSION, true );
			wp_localize_script('rey-core-elementor-editor', 'reyElementorEditorParams', [
				'reload_text' => esc_html__('Please save & reload page to apply this setting.', 'rey-core'),
				'rey_typography' => $this->get_typography_names(),
				'rey_icon' => REY_CORE_URI  . 'assets/images/logo-simple-white.svg',
				'rey_title' => esc_html__('Rey - Quick Menu', 'rey-core'),
				'optimized_dom' => $this->is_optimized_dom(),
				'elements_icons_sprite_path' => REY_CORE_URI  . 'assets/images/elementor-el-icons.svg',
			]);
		}

		public function get_typography_names(){

			$pff = $sff = '';

			if( ($primary_typo = get_theme_mod('typography_primary', [])) && isset($primary_typo['font-family']) ){
				$pff = "( {$primary_typo['font-family']} )";
			}
			$primary = sprintf(esc_html__('Primary Font %s', 'rey-core'), $pff);

			if( ($secondary_typo = get_theme_mod('typography_secondary', [])) && isset($secondary_typo['font-family']) ){
				$sff = "( {$secondary_typo['font-family']} )";
			}
			$secondary = sprintf(esc_html__('Secondary Font %s', 'rey-core'), $sff);

			return [
				'primary' => $primary,
				'secondary' => $secondary,
			];
		}

		/**
		 * Push (Sync) elementor meta to ACF fields
		 *
		 * @since 1.0.0
		 */
		function elementor_to_acf( $post_id ) {

			$post_type = get_post_type($post_id);

			// settings to update
			$settings = [];

			// get Elementor' meta
			$em = get_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

			if( empty($em) ){
				return;
			}

			// Title Display
			$settings['title_display'] = isset($em['hide_title']) && $em['hide_title'] == 'yes' ? 'hide' : '';

			if ( class_exists('ReyCore_GlobalSections') && ( $post_type === ReyCore_GlobalSections::POST_TYPE || $post_type === 'revision' ) ) {
				if( isset($em['gs_type']) && ! is_null( $em['gs_type'] ) ){
					$settings['gs_type'] = $em['gs_type'];
				}
			}

			// Transparent gradient
			$settings['rey_body_class'] = isset($em['rey_body_class']) ? $em['rey_body_class'] : '';

			if( !empty($settings) && class_exists('ACF') ){
				foreach ($settings as $key => $value) {
					update_field($key, $value, $post_id);
				}
			}
		}

		/**
		 * Load WooCommerce's
		 * On Editor - Register WooCommerce frontend hooks before the Editor init.
		 * Priority = 5, in order to allow plugins remove/add their wc hooks on init.
		 *
		 * @since 1.0.0
		 */
		public function register_wc_hooks(){
			if( class_exists('WooCommerce') ){
				wc()->frontend_includes();
			}
		}

		/**
		 * Add Product post classes (elementor edit mode)
		 * @since 1.0.0
		 */
		public function add_product_post_class( $classes ) {

			if ( in_array( get_post_type(), [ 'product', 'product_variation' ], true ) ) {

				$classes[] = 'product';

				if( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					$classes = array_diff($classes, ['is-animated-entry']);
				}
			}

			return $classes;
		}

		/**
		 * Add an attribute to html tag
		 *
		 * @since 1.0.0
		 **/
		function filter_html_tag()
		{
			add_filter('language_attributes', function($output){
				$attributes[] = sprintf("data-post-type='%s'", get_post_type());
				return $output . implode(' ', $attributes);
			} );
		}

		/**
		 * Adds Elementor Kit class to the html tag.
		 * Useful because of the Customizer's global colors/fonts.
		 *
		 * @since 1.9.6
		 **/
		function html_classes($classes)
		{
			if( class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->kits_manager) ){
				$classes[] = "elementor-kit-" . \Elementor\Plugin::$instance->kits_manager->get_active_id();
			}
			return $classes;
		}

		/**
		 * Add Rey Config into Elementor's
		 *
		 * @since 1.0.0
		 **/
		public function localized_settings( $settings )
		{

			if( apply_filters('reycore/elementor/quickmenu', true) ){

				$settings['rey'] = [
					'global_links' => [

						'dashboard' => [
							'title' => esc_html__('WordPress Dashboard', 'rey-core'),
							'link' => esc_url( admin_url() ),
							'icon' => 'eicon-wordpress'
						],

						'exit_backend' => [
							'title' => esc_html__('Exit to page backend', 'rey-core'),
							'link' => add_query_arg([
								'post' => get_the_ID(),
								'action' => 'edit',
								], admin_url( 'post.php' )
							),
							'icon' => 'fa fa-code',
							'show_in_el_menu' => false,
						],

						'customizer' => [
							'title' => esc_html__('Customizer Settings', 'rey-core'),
							'link' => add_query_arg([
								'url' => get_permalink(  )
								], admin_url( 'customize.php' )
							),
							'icon' => 'fa fa-paint-brush',
							'class' => '--top-separator'
						],
						'settings' => [
							'title' => esc_html__('Rey Settings', 'rey-core'),
							'link' => add_query_arg([
								'page' => 'rey-settings'
								], admin_url( 'admin.php' )
							),
							'icon' => 'fa fa-cogs'
						],
						'custom_css' => [
							'title' => esc_html__('Additional CSS', 'rey-core'),
							'link' => add_query_arg([
								'autofocus[section]' => 'custom_css',
								'url' => get_permalink(  )
								], admin_url( 'customize.php' )
							),
							'icon' => 'fa fa-code'
						],
						'global_sections' => [
							'title' => esc_html__('Global Sections', 'rey-core'),
							'link' => add_query_arg([
								'post_type' => ReyCore_GlobalSections::POST_TYPE
								], admin_url( 'edit.php' )
							),
							'icon' => 'fa fa-columns'
						],

						'new_page' => [
							'title' => esc_html__('New Page', 'rey-core'),
							'link' => add_query_arg([
								'post_type' => 'page'
								], admin_url( 'post-new.php' )
							),
							'icon' => 'fa fa-edit',
							'class' => '--top-separator'
						],
						'new_global_section' => [
							'title' => esc_html__('New Global Section', 'rey-core'),
							'link' => add_query_arg([
								'post_type' => ReyCore_GlobalSections::POST_TYPE
								], admin_url( 'post-new.php' )
							),
							'icon' => 'fa fa-edit'
						],

					]
				];
			}

			// remove disabled widgets
			if( isset($settings['widgets']) ){
				$disabled_elements = class_exists('ReyCore_WidgetsManager') ? ReyCore_WidgetsManager::get_disabled_elements() : [];
				foreach( $disabled_elements as $element ){
					if(isset($settings['widgets'][$element])){
						unset($settings['widgets'][$element]);
					}
				}
			}

			return $settings;
		}

		/**
		 * Include Rey builder template
		 */
		public function template_include( $template ) {

			if ( is_singular() ) {
				$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( get_the_ID() );
				if ( $document && $document->get_meta( '_wp_page_template' ) == self::REY_TEMPLATE ) {
					$template_path = trailingslashit( get_template_directory() ) . self::REY_TEMPLATE;
					if ( is_readable($template_path) ) {
						$template = $template_path;
					}
				}
			}

			return $template;
		}

		function library__page_templates_support( $templates ){
			$rey_templates = function_exists('rey__page_templates') ? rey__page_templates() : [];
			return $templates + $rey_templates;
		}

		function get_page_settings(){

			// if no meta, not an Elementor page
			if( ! ($elementor_meta = get_post_meta( get_the_ID(), \Elementor\Core\Base\Document::PAGE_META_KEY, true )) ){
				return;
			}

			add_filter( 'rey/site_container/classes', function( $classes ) use ($elementor_meta){
				if( isset($elementor_meta['rey_stretch_page']) && ($stretch = $elementor_meta['rey_stretch_page']) && $stretch === 'rey-stretchPage' ){
					$classes[] = $stretch;
				}
				return $classes;
			}, 10);

			do_action('reycore/elementor/get_page_meta', $elementor_meta);
		}

		/**
		 * Get Document settings
		 *
		 * @since 1.0.0
		 */
		public function get_document_settings( $setting = '' ){

			// Get the current post id
			$post_id = get_the_ID();

			// Get the page settings manager
			$page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );

			// Get the settings model for current post
			$page_settings_model = $page_settings_manager->get_model( $post_id );

			return $page_settings_model->get_settings( $setting );
		}

		/**
		 * Inject HTML into an element/widget output
		 *
		 * @since 1.0.0
		 */
		public static function el_inject_html( $content, $injection, $query )
		{
			// checks
			if ( ( class_exists( 'DOMDocument' ) && class_exists( 'DOMXPath' ) && function_exists( 'libxml_use_internal_errors' ) ) ) {

				// We have to go through DOM, since it can load non-well-formed XML (i.e. HTML).
				$dom = new DOMDocument();

				// The @ is not enough to suppress errors when dealing with libxml,
				// we have to tell it directly how we want to handle errors.
				libxml_use_internal_errors( true );
				@$dom->loadHTML( mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR ); // suppress parser warnings
				libxml_clear_errors();
				libxml_use_internal_errors( false );

				// Get parsed document
				$xpath = new DOMXPath($dom);
				$container = $xpath->query($query);

				if( $container ) {
					$container_node = $container->item(0);

					// Create new node
					$newNode = $dom->createDocumentFragment();
					// add the slideshow html into the newly node
					if ( $newNode->appendXML( $injection ) && $container_node ) {
						// insert before the first child
						$container_node->insertBefore($newNode, $container_node->firstChild);
						// fixed extra html & body tags
						// on some hostings these tags are added even though LIBXML_HTML_NOIMPLIED is added
						$clean_tags = ['<html>', '<body>', '</html>', '</body>'];
						// save the content
						return str_replace($clean_tags, '', $dom->saveHTML());
					}
				}
			}
			else {
				return __( "PHP's DomDocument is not available. Please contact your hosting provider to enable PHP's DomDocument extension." );
			}

			return $content;
		}

		public static function button_styles(){
			$style['simple'] = __( 'REY - Link', 'rey-core' );
			$style['primary'] = __( 'REY - Primary', 'rey-core' );
			$style['secondary'] = __( 'REY - Secondary', 'rey-core' );
			$style['primary-outline'] = __( 'REY - Primary Outline', 'rey-core' );
			$style['secondary-outline'] = __( 'REY - Secondary Outline', 'rey-core' );
			$style['underline'] = __( 'REY - Underlined', 'rey-core' );
			$style['underline-hover'] = __( 'REY - Hover Underlined', 'rey-core' );
			$style['dashed --large'] = __( 'REY - Large Dash', 'rey-core' );
			$style['dashed'] = __( 'REY - Normal Dash', 'rey-core' );
			$style['underline-1'] = __( 'REY - Underline 1', 'rey-core' );
			$style['underline-2'] = __( 'REY - Underline 2', 'rey-core' );

			return $style;
		}

		/**
		 * Wierd bug in Elementor Preview, where ACF
		 * is not getting the proper POST ID
		 *
		 * @since 1.0.0
		 */
		function acf_validate_post_id( $pid, $_pid ){
			if( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) ){
				return $_pid;
			}
			return $pid;
		}

		/**
		 * Flush Elementor cache
		 * - after updates
		 */
		public function flush_cache(){
			if( is_multisite() ){
				$blogs = get_sites();
				foreach ( $blogs as $keys => $blog ) {
					$blog_id = $blog->blog_id;
					switch_to_blog( $blog_id );
						\Elementor\Plugin::$instance->files_manager->clear_cache();
					restore_current_blog();
				}
			}
			else {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
		}

		/**
		 * Display a notice in widgets, in edit mode.
		 * For example requirements of a widget or a simple warning
		 *
		 * @since 1.0.0
		 */
		public static function edit_mode_widget_notice( $presets = [], $args = [] )
		{
			if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) {

				$default_presets = [
					'full_viewport' => [
						'type' => 'warning',
						'title' => __('Requirement!', 'rey-core'),
						'text' => __('This widget is full viewport only. Please access this widget\'s parent section, and <strong>enable Stretch Section</strong> and also select <strong>Content Width to "Full width"</strong>.', 'rey-core'),
						'class' => 'coverEl-notice--needStretch',
					],
					'tabs_modal' => [
						'type' => 'warning',
						'title' => __('Not using properly!', 'rey-core'),
						'text' => __('Please don\'t use this widget into a section with <strong>Tabs</strong> or <strong>Modal</strong> enabled. Please disable those settings.', 'rey-core'),
						'class' => 'coverEl-notice--noTabs',
					]
				];

				$markup = '<div class="rey-elementorNotice rey-elementorNotice--%s %s"><h4>%s</h4><p>%s</p></div>';

				if( !empty($args) ){

					$defaults = [
						'type' => 'warning',
						'text' => __('Warning!', 'rey-core'),
						'text' => __('Text', 'rey-core'),
						'class' => '',
					];

					// Parse args.
					$args = wp_parse_args( $args, $defaults );

					printf( $markup, $args['type'], $args['class'], $args['title'], $args['text'] );
				}

				if( !empty($presets) ){
					foreach ($presets as $preset) {
						if( isset($default_presets[$preset]) ){
							printf(
								$markup,
								$default_presets[$preset]['type'],
								$default_presets[$preset]['class'],
								$default_presets[$preset]['title'],
								$default_presets[$preset]['text']
							);
						}
					}
				}

			}
		}

		public function add_theme_settings(){

			acf_add_local_field(array(
				'key'          => 'field_elementor_grid',
				'name'         => 'elementor_grid',
				'type'         => 'select',
				'label'        => esc_html__('Elementor Grid', 'rey-core'),
				// 'instructions' => __('', 'rey-core'),
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size',
					'id'    => '',
				],
				'choices' => [
					'rey' => esc_html__('Rey grid overrides', 'rey-core'),
					'default' => esc_html__('Default Elementor', 'rey-core'),
				],
				'default_value' => '',
				'allow_null'    => 0,
				'multiple'      => 0,
				'ui'            => 0,
				'parent'        => 'group_5c990a758cfda',
				'menu_order'    => 300,
			));

			/**
			 * Allow disabling entrance animation engine.
			 *
			 * @since 1.0.0
			 */
			acf_add_local_field(array(
				'key'          => 'field_elementor_animations_enable',
				'name'         => 'elementor_animations_enable',
				'label'        => esc_html__('Enable Elementor Animations', 'rey-core'),
				'type'         => 'true_false',
				'instructions' => __('Enable or disable Elementors entrance animation engine. Learn more <a href="https://support.reytheme.com/kb/how-to-use-animated-entrance-effects/" target="_blank">how to use animated entrance effects</a>', 'rey-core'),
				'default_value' => 1,
				'ui' => 1,
				'parent'       => 'group_5c990a758cfda',
				'menu_order'   => 300,
			));
		}

		/**
		 * Checks if entrance animation engine is enabled
		 * @since 1.0.0
		 */
		public function animations_enabled()
		{

			if( isset($this->animations_enabled) ){
				return $this->animations_enabled;
			}

			if( function_exists('reyCoreAssets') && reyCoreAssets()->mobile ){
				return;
			}

			$option = reycore__acf_get_field('elementor_animations_enable', REY_CORE_THEME_NAME);

			return $this->animations_enabled = is_null($option) || $option === true;
		}

		/**
		 * Hides Elementor's native entrance animation options
		 * if Rey's animations are enabled.
		 * @since 1.0.0
		 */
		public function animations_hide_native_anim_options(){
			if($this->animations_enabled()):
				add_action('wp_head', function(){?>
					<style>
						.elementor-control-type-animation { display: none; }
						.elementor-editor-popup .elementor-control-type-animation { display: inherit; }
					</style>
				<?php });
			endif;
		}

		/**
		 * Sync Customizer's colors with Elementor's
		 * Only update if the scheme's haven't been modified in Elementor
		 *
		 * @since 1.0.0
		 */
		public function update_elementor_schemes()
		{

			if( get_option(\Elementor\Core\Schemes\Base::LAST_UPDATED_META) ){
				return;
			}

			// Color Scheme
			$el_scheme_color = get_option( 'elementor_scheme_color' );

			if( $el_scheme_color && is_array($el_scheme_color) ){
				// Theme Text Color
				$text_color = get_theme_mod('style_text_color');
				// Primary
				$el_scheme_color[1] = $text_color ? $text_color : '#373737';
				// Text
				$el_scheme_color[3] = $text_color ? $text_color : '#373737';
				// Accent
				$el_scheme_color[4] = get_theme_mod('style_accent_color', '#212529');

				update_option( 'elementor_scheme_color', $el_scheme_color );
			}

			// Typography
			$el_scheme_typography = get_option( 'elementor_scheme_typography' );

			if( $el_scheme_typography && is_array($el_scheme_typography) )
			{
				foreach($el_scheme_typography as $key => $typography_scheme){
					// Just reset to defaults
					$el_scheme_typography[$key]['font_family'] = '';
				}
				update_option( 'elementor_scheme_typography', $el_scheme_typography );
			}
		}

		/**
		 * Method to get Elementor compatibilities.
		 *
		 * @since 1.0.0
		 */
		public static function get_compatibilities( $support = '' )
		{
			$supports = [
				/**
				 * If Elementor adds video support on columns,
				 * i'll need to add ELEMENTOR_VERSION < x.x.x .
				 */
				'column_video' => true,
				/**
				 * Currently disabled by default. Needs implementation. However still,
				 * i'll need to add support on ELEMENTOR_VERSION > 2.7.0
				 */
				'video_bg_play_on_mobile' => true,
			];

			if( $support && isset($supports[$support]) ){
				return $supports[$support];
			}

			return $supports;
		}

		public static function getReyBadge( $text = '' ){

			if( empty($text) && defined('REY_CORE_THEME_NAME') ){
				$text = REY_CORE_THEME_NAME;
			}

			return sprintf('<span class="rey-elementorBadge">%s</span>', $text);
		}


		function set_option_event( $ajax_manager ) {

			$ajax_manager->register_ajax_action( 'rey_set_opt', function ( $data ){

				if( isset($data['opt']) && $opt = reycore__clean($data['opt']) ){
					$current = get_theme_mod($opt);
					$set = set_theme_mod($opt, !$current);
					return !$current;
				}

			} );
		}

		function widget_init($data){

			if( !$data ){
				return;
			}

			if ( isset($data['settings']) && ($settings = $data['settings']) && isset($data['widgetType']) && ($widgetType = $data['widgetType']) ) {
				add_filter('body_class', function($classes) use ($widgetType){
					$classes[$widgetType] = 'el-' . $widgetType;
					return $classes;
				});
			}

		}

		static public function get_elementor_option( $post_id = null, $option = false ){

			if( !class_exists('\Elementor\Core\Settings\Page\Manager') ){
				return;
			}

			if( ! $post_id ){
				return;
			}

			$elementor_meta = get_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

			if ( $option && isset($elementor_meta[ $option ]) ) {
				return $elementor_meta[ $option ];
			}

			return $elementor_meta;
		}

		function load_lazy_element(){

			if( ! (isset($_REQUEST['qid']) && $qid = absint($_REQUEST['qid'])) ){
				wp_send_json_error();
			}

			if( ! (isset($_REQUEST['element_id']) && $element_id = reycore__clean($_REQUEST['element_id'])) ){
				wp_send_json_error();
			}

			$document_data = '';

			$document = \Elementor\Plugin::$instance->documents->get( $qid );

			if ( $document ) {
				$document_data = $document->get_elements_data();
			}

			if ( empty( $document_data ) ) {
				wp_send_json_error();
			}

			$findings = [];

			\Elementor\Plugin::$instance->db->iterate_data( $document_data, function( $element ) use ($element_id, &$findings) {
				if( $element_id === $element['id'] ){
					$findings[] = $element;
				}
			} );

			if( ! (isset($findings[0]) && $element_data_instance = $findings[0]) ){
				wp_send_json_error('No element found in page.');
			}

			do_action('wp_enqueue_scripts');

			$element_data_instance['settings']['lazy_load'] = '';

			if( isset($_REQUEST['options']) && $options = reycore__clean($_REQUEST['options']) ){
				foreach ($options as $key => $value) {
					$element_data_instance['settings'][$key] = $value;
				}
			}

			$new_element = \Elementor\Plugin::instance()->elements_manager->create_element_instance( $element_data_instance );

			ob_start();
			$new_element->print_element();
			$element_data = ob_get_clean();

			if( empty($element_data) ){
				wp_send_json_error('Empty element data!');
			}

			wp_send_json_success($element_data);
		}

		function product_grid_load_more(){

			if( ! (isset($_REQUEST['qid']) && $qid = absint($_REQUEST['qid'])) ){
				wp_send_json_error();
			}

			if( ! (isset($_REQUEST['element_id']) && $element_id = reycore__clean($_REQUEST['element_id'])) ){
				wp_send_json_error();
			}

			$document_data = '';

			$document = \Elementor\Plugin::$instance->documents->get( $qid );

			if ( $document ) {
				$document_data = $document->get_elements_data();
			}

			if ( empty( $document_data ) ) {
				wp_send_json_error();
			}

			$findings = [];

			\Elementor\Plugin::$instance->db->iterate_data( $document_data, function( $element ) use ($element_id, &$findings) {
				if( $element_id === $element['id'] ){
					$findings[] = $element;
				}
			} );

			if( ! (isset($findings[0]) && $element_data_instance = $findings[0]) ){
				wp_send_json_error('No element found in page.');
			}

			$element_data_instance['settings']['lazy_load'] = '';
			$element_data_instance['settings']['limit'] = 4;

			if( isset($_REQUEST['limit']) && $limit = absint($_REQUEST['limit']) ){
				$element_data_instance['settings']['limit'] = $limit;
			}

			if( isset($_REQUEST['offset']) && $offset = absint($_REQUEST['offset']) ){
				$element_data_instance['settings']['offset'] = $offset;
			}

			if( isset($_REQUEST['options']) && $options = reycore__clean($_REQUEST['options']) ){
				foreach ($options as $key => $value) {
					$valid[] = $value === 'yes';
					$valid[] = $value === 'no';
					$valid[] = $value === '';
					if( in_array(true, $valid, true) ){
						$element_data_instance['settings'][$key] = $value;
					}
				}
			}

			$new_element = \Elementor\Plugin::instance()->elements_manager->create_element_instance( $element_data_instance );

			ob_start();
			$new_element->print_element();
			$element_data = ob_get_clean();

			if( empty($element_data) ){
				wp_send_json_error('Empty element data!');
			}

			wp_send_json_success($element_data);
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCoreElementor
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	function reyCoreElementor() {
		return ReyCoreElementor::getInstance();
	}

	reyCoreElementor();

endif;
