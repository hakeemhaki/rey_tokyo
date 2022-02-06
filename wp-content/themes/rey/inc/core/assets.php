<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists('ReyTheme_Assets') ):
	/**
	 * Handles assets.
	 *
	 * @since 2.0.0
	 */
	class ReyTheme_Assets
	{

		/**
		 * Holds the reference to the instance of this class
		 * @var ReyTheme_Assets
		 */
		private static $_instance = null;

		const STYLE_HANDLE = 'rey-wp-style';

		private $widgets_scripts_loaded;

		protected $has_registered_assets = false;

		/**
		 * ReyTheme_Assets constructor.
		 */
		private function __construct() {
			add_action( 'reycore/assets/register_scripts', [$this, 'reycore_register_assets'], 5);
			add_action( 'wp_enqueue_scripts', [$this, 'fallback_assets'], 6);
			add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'], 9 ); // make sure it's first
			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin']);
			add_filter( 'reycore/assets/excludes_choices', [$this, 'add_excludes_choices']);
			add_action( 'get_sidebar', [$this, 'do_widget_script'] );
		}

		public static function rtl(){
			return is_rtl() ? '-rtl' : '';
		}

		function get_styles()
		{
			$rtl = self::rtl();

			/**
			 * Params:
			 * `src` Path to style;
			 * `deps` Dependencies;
			 * `version` Asset version;
			 * `priority` The loading priority sequence. Not specified means `medium`, while `low` will load in footer;
			 * `enqueue` Enqueue on page load, always;
			 * `callback` Function to run to check if it should load. `enqueue` not needed when callback is added;
			 */

			$styles = [

				// 'rey-wp-style' => [
				// 	'src'      => get_template_directory_uri() . '/style' . $rtl . (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '': '.min') . '.css',
				// 	'deps'     => [],
				// 	'priority' => 'high',
				// 	'enqueue'  => true
				// ],

				'rey-general' => [
					'src'      => REY_THEME_URI . '/assets/css/components/general/style' . $rtl . '.css',
					'priority' => 'high',
					'enqueue'  => true,
					'desc' => 'Theme General'
				],
				'rey-animations'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/animations/style' . $rtl . '.css',
					'priority' => 'high',
					'enqueue'  => true,
					'desc' => 'Animation Keyframes'
				],
				'rey-buttons'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/buttons/style' . $rtl . '.css',
					'priority' => 'high',
					'enqueue'  => true,
					'desc' => 'Buttons '
				],
				'rey-forms'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/forms/style' . $rtl . '.css',
					'priority' => 'high',
					'enqueue'  => true,
					'desc' => 'Forms'
				],
				// Loaded by default because there are times when users use header's elements inside the content.
				'rey-header'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/header/style' . $rtl . '.css',
					'priority' => 'high',
					'enqueue'  => true,
					'desc' => 'Site Header'
				],
					'rey-header-default'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-default/style' . $rtl . '.css',
						'priority' => 'high'
					],
					'rey-header-drop-panel'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-drop-panel/style' . $rtl . '.css',
						'priority' => 'high'
					],
					'rey-header-menu'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-menu/style' . $rtl . '.css',
						'priority' => 'high'
					],
					'rey-header-search'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-search/style' . $rtl . '.css',
						'priority' => 'high'
					],
				'rey-gutenberg'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/gutenberg/style' . $rtl . '.css',
					'enqueue'  => true,
					'desc' => 'Gutenberg'
				],
				'rey-slick'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/slick/style' . $rtl . '.css',
				],
				'rey-widgets'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/widgets/style' . $rtl . '.css',
				],
				'rey-blog'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/blog/style' . $rtl . '.css',
					'callback' => 'rey__is_blog_list',
				],
				'rey-page404'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/page404/style' . $rtl . '.css',
					'callback' => 'is_404',
					'priority' => 'high',
				],
				'rey-pagination'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/pagination/style' . $rtl . '.css',
					'priority' => 'low'
				],
				'rey-searchbox'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/searchbox/style' . $rtl . '.css',
					'priority' => 'high',
				],
				'rey-presets'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/presets/style' . $rtl . '.css',
					'priority' => 'low',
					'enqueue'  => true,
					'desc' => 'Utility Classes'
				],
				'rey-footer'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/footer/style' . $rtl . '.css',
					'priority' => 'low'
				],
			];

			foreach ($styles as $key => $style) {

				if( !isset($style['deps']) ){
					$styles[$key]['deps'] = [self::STYLE_HANDLE];
				}
				if( !isset($style['version']) ){
					$styles[$key]['version'] = REY_THEME_VERSION;
				}

				$styles[$key]['path'] = REY_THEME_URI;
				$styles[$key]['dir'] = REY_THEME_DIR;
			}

			return $styles;

		}

		/**
		 * Rey Scripts
		 *
		 * @since 2.0.0
		 **/
		function get_scripts()
		{
			$scripts = [

				'imagesloaded' => [
					'callback' => 'rey__is_blog_list',
					'plugin' => true
				],

				'wp-util' => [
					'deps'    => ['jquery'],
					'plugin' => true
				],

				'masonry' => [
					'callback' => 'rey__is_blog_list',
					'plugin' => true
				],

				'comment-reply' => [
					'callback' => function(){
						return is_singular() && comments_open() && get_option( 'thread_comments' );
					},
					'plugin' => true
				],

				'slick' => [
					'src'      => REY_THEME_URI . '/assets/js/lib/slick.js',
					'deps'    => ['jquery'],
					'version'  => '1.8.1',
					'plugin' => true
				],

				'scroll-out' => [
					'src'      => REY_THEME_URI . '/assets/js/lib/scroll-out.js',
					'version'  => '2.2.3',
					'callback' => 'rey__is_blog_list',
					'plugin' => true
				],

				'rey-script' => [
					'src'      => REY_THEME_URI . '/assets/js/rey.js',
					'deps'     => ['jquery'],
					'localize' => [
						'name'   => 'reyParams',
						'params' => apply_filters('rey/main_script_params', [
							'icons_path'      => esc_url( rey__svg_sprite_path() ),
							'theme_js_params' => [
								'menu_delays'            => get_theme_mod('header_nav_hover_delays', true),
								'menu_hover_overlay'     => get_theme_mod('header_nav_hover_overlay', true),
								'menu_hover_timer'       => 500,
								'menu_items_hover_timer' => 150,
								'menu_items_leave_timer' => 300,
								'menu_items_open_event'  => 'hover',
								'menu_trigger_events'  => false,
							],
							'debug'      => defined('WP_DEBUG') && WP_DEBUG,
							'ajaxurl'    => admin_url( 'admin-ajax.php' ),
							'ajax_nonce' => wp_create_nonce( 'rey_nonce' ),
							'preloader_timeout' => false
						])
					],
					'enqueue' => true
				],

				'rey-blog' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-blog.js',
					'deps'    => ['jquery', 'scroll-out'],
					'callback' => 'rey__is_blog_list',
				],

				'rey-drop-panel' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-drop-panel.js',
					'deps'    => ['jquery'],
				],

				'rey-main-menu' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-main-menu.js',
					'deps'    => ['jquery'],
				],

				'rey-searchform' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-searchform.js',
					'deps'    => ['jquery'],
				],

			];

			foreach ($scripts as $key => $script) {

				if( !isset($script['src']) ){
					continue;
				}

				if( !isset($script['version']) ){
					$scripts[$key]['version'] = REY_THEME_VERSION;
				}

				$scripts[$key]['path'] = REY_THEME_URI;
				$scripts[$key]['dir'] = REY_THEME_DIR;
			}

			return $scripts;
		}

		function reycore_register_assets(){
			reyCoreAssets()->register_asset('styles', $this->get_styles());
			reyCoreAssets()->register_asset('scripts', $this->get_scripts());
			$this->has_registered_assets = true;
		}

		/**
		 * Fallback when Core is disabled or outdated.
		 */
		function fallback_assets() {

			if( $this->has_registered_assets ){
				return;
			}

			foreach( $this->get_styles() as $handle => $style ){

				wp_register_style($handle, $style['src'], $style['deps'], $style['version']);

				if( $this->maybe_enqueue( $style ) ){
					wp_enqueue_style( $handle );
				}
			}

			foreach( $this->get_scripts() as $handle => $script ){

				if( isset($script['src']) ){
					wp_register_script(
						$handle,
						$script['src'],
						isset($script['deps']) ? $script['deps'] : [],
						isset($script['version']) ? $script['version'] : REY_THEME_VERSION,
						isset($script['in_footer']) ? $script['in_footer'] : true
					);
					if( isset($script['localize']) ){
						wp_localize_script($handle, $script['localize']['name'], $script['localize']['params']);
					}
				}

				if( $this->maybe_enqueue( $script ) ){
					wp_enqueue_script( $handle );
				}
			}
		}

		function maybe_enqueue( $asset ){

			$enqueue = false;

			// always enqueue
			if( isset($asset['enqueue']) && $asset['enqueue'] ){
				$enqueue = true;
			}

			else {
				// check callback
				if( isset($asset['callback']) ){
					if( is_callable($asset['callback']) && call_user_func($asset['callback']) ){
						$enqueue = true;
					}
				}
			}

			return $enqueue;
		}

		/**
		 * Enqueue Styles based of conditions
		 */
		function enqueue_scripts(){

			/**
			 * Load main stylesheet
			 */
			wp_enqueue_style(
				self::STYLE_HANDLE,
				get_template_directory_uri() . '/style' . self::rtl() . (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '': '.min') . '.css',
				[],
				REY_THEME_VERSION
			);

		}

		function add_excludes_choices( $choices ){
			return array_merge($choices, wp_list_filter( $this->get_styles(), [ 'enqueue' => true ] ));
		}

		function enqueue_admin() {

			// Scripts
			wp_enqueue_script( 'masonry' );
			wp_enqueue_script( 'rey-admin-scripts', REY_THEME_URI . '/assets/js/rey-admin.js', ['jquery', 'masonry' ], REY_THEME_VERSION, true );
			wp_localize_script('rey-admin-scripts', 'reyAdminParams', apply_filters('rey/admin_script_params', [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			]));

			// Styles
			wp_enqueue_style('rey-admin-styles', REY_THEME_URI . '/assets/css/rey-admin.css', false, REY_THEME_VERSION);
		}

		function add_styles( $handler ){
			if( function_exists('reyCoreAssets') ){
				reyCoreAssets()->add_styles($handler);
			}
			else {
				wp_enqueue_style($handler);
			}
		}

		function add_scripts( $handler ){
			if( function_exists('reyCoreAssets') ){
				reyCoreAssets()->add_scripts($handler);
			}
			else {
				wp_enqueue_script($handler);
			}
		}

		function do_widget_script(){

			if( ! $this->widgets_scripts_loaded ){
				$this->add_styles('rey-widgets');
				$this->widgets_scripts_loaded = true;
			}

		}


		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyTheme_Assets
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	function reyAssets(){
		return ReyTheme_Assets::getInstance();
	}

	reyAssets();

endif;
