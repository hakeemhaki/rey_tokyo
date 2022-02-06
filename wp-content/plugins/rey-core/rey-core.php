<?php
/**
 * Plugin Name: Rey Core
 * Description: Core plugin for Rey.
 * Plugin URI: http://www.reytheme.com/
 * Version: 2.1.1.2
 * Author: ReyTheme
 * Author URI:  https://twitter.com/mariushoria
 * Text Domain: rey-core
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore') ):

class ReyCore
{
	private static $_instance = null;

	private function __construct()
	{
		$this->define_constants();
		$this->init_hooks();
		$this->includes();
	}

	/**
	 * Initialize Hooks
	 *
	 * @since 1.0.0
	 */
	public function init_hooks()
	{
		add_action( 'admin_notices', [$this, 'show_errors_and_deactivate'] );
		add_action( 'wp_body_open', [$this, 'show_errors_and_deactivate'] );
		add_action( 'admin_enqueue_scripts', [$this, 'register_admin_scripts'], 5);
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_scripts']);
		add_action( 'wp_enqueue_scripts', [$this, 'fallback_enqueue_scripts']);
		add_action( 'plugins_loaded', [$this, 'includes_after_plugins_loaded'] );
		add_filter( 'reycore/assets/excludes_choices', [$this, 'add_excludes_choices']);

		$this->includes_before_plugins_loaded();
	}

	function get_major_version(){
		$v = array_map('absint', explode('.', REY_CORE_VERSION));
		unset($v[2]);
		unset($v[3]);
		return sprintf('%d.%d.0', $v[0], $v[1]);
	}

	/**
	 * Define Constants.
	 * @since 1.0.0
	 */
	private function define_constants()
	{
		$this->define( 'REY_CORE_DIR', plugin_dir_path( __FILE__ ) );
		$this->define( 'REY_CORE_URI', plugin_dir_url( __FILE__ ) );
		$this->define( 'REY_CORE_THEME_NAME', 'rey' );
		$this->define( 'REY_CORE_VERSION', '2.1.1.2' );
		$this->define( 'REY_CORE_PLACEHOLDER', REY_CORE_URI . 'assets/images/placeholder.png' );
		$this->define( 'REY_CORE_REQUIRED_PHP_VERSION', '5.4.0' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Check plugin requirements
	 * @since 1.0.0
	 */
	private function get_errors()
	{
		$errors = array();

		// Check PHP version
		if ( version_compare( phpversion(), REY_CORE_REQUIRED_PHP_VERSION, '<' ) ) {
			$errors['php_version'] = sprintf( __( 'The PHP version <strong>%s</strong> is needed in order to be able to run the <strong>%s</strong> plugin. Please contact your hosting support and ask them to upgrade the PHP version to at least v<strong>%s</strong> for you.', 'rey-core' ),
				REY_CORE_REQUIRED_PHP_VERSION, 'Rey Core', REY_CORE_REQUIRED_PHP_VERSION );
		}

		if ( defined('REY_THEME_VERSION') && version_compare( REY_THEME_VERSION, $this->get_major_version(), '<' ) && current_user_can('administrator') ) {
			$errors['sync'] = sprintf(
				__( '<strong>Rey Theme is outdated and not in sync with Rey Core.</strong> The minimum Rey Core version should be <strong>%2$s</strong>, but currently it\'s %3$s. If they\'re not both at their latest versions, there could be issues or errors since one depends on the other in various aspects. Please check the <a href="%1$s">Updates</a> page and update it to its latest version.', 'rey-core' ) ,
				esc_url( admin_url( 'update-core.php?force-check=1' ) ),
				$this->get_major_version(),
				REY_THEME_VERSION
			);
		}

		return $errors;
	}

	/**
	 * Display errors and deactivate
	 *
	 * @since 1.0.0
	 */
	function show_errors_and_deactivate()
	{
		$errors = $this->get_errors();

		if ( ! empty( $errors ) ) {

			/**
			 * Render the notices about the plugin's requirements
			 */
			echo '<div class="notice notice-error rey-noticeError">';

				foreach ( $errors as $error ) {
					echo "<div class='__item'>{$error}</div>";
				}

				if( isset($errors['php_version']) ){
					echo '<p>' . sprintf( __( '<strong>%s</strong> has been deactivated.', 'rey-core' ), 'Rey Core' ) . '</p>';
				}

			echo '</div>';

			if( is_admin() && isset($errors['php_version']) ){
				$this->deactivate();
			}
		}
	}

	private function deactivate(){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( 'rey-core/rey-core.php' );
		unset( $_GET['activate'], $_GET['plugin_status'], $_GET['activate-multi'] );
		return;
	}

	// Load localization file
	function load_plugin_textdomain(){
		load_plugin_textdomain( 'rey-core', false, plugin_basename(dirname(__FILE__)) . '/languages');
	}

	function register_admin_scripts(){

		// Scripts
		wp_register_script( 'rey-core-admin-script', REY_CORE_URI . 'assets/js/admin.js', ['jquery', 'underscore', 'wp-util'], REY_CORE_VERSION, true );
		wp_localize_script( 'rey-core-admin-script', 'reyCoreAdmin', apply_filters('reycore/admin_script_params', [
			'rey_core_version' => REY_CORE_VERSION,
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'    => wp_create_nonce('reycore-ajax-verification'),
			'back_btn_text' => esc_html__('Back to List', 'rey-core'),
			'back_btn_url'  => admin_url('edit.php?post_type=rey-global-sections'),
			'is_customizer' => false,
			'strings' => [
				'refresh_demos_error' => esc_html__('Error. Please retry!', 'rey-core'),
				'reloading' => esc_html__('Reloading page!', 'rey-core'),
				'refresh_btn_text' => esc_html__('Refresh Demos List', 'rey-core'),
				'help' => esc_html__('Need help?', 'rey-core'),
			],
			'sound_effect' => REY_CORE_URI . 'assets/audio/ding.mp3',
		]) );

		$rtl = reyCoreAssets()::rtl();

		$reycore_styles = [
			'rey-core-admin-style' => [
				'src'     => REY_CORE_URI . 'assets/css/admin'. $rtl .'.css',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'reycore-frontend-admin' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/frontend-admin/frontend-admin' . $rtl . '.css',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
		];

		foreach($reycore_styles as $handle => $style ){
			wp_register_style($handle, $style['src'], $style['deps'], $style['version']);
		}
	}

	function enqueue_admin_scripts(){
		wp_enqueue_script( 'rey-core-admin-script');
		wp_enqueue_style( 'rey-core-admin-style');
	}

	function styles(){

		$rtl = reyCoreAssets()::rtl();

		$styles = [
			'reycore-general' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/general/general' . $rtl . '.css',
				'priority' => 'high',
				'enqueue'  => true,
				'desc' => 'Core General'
			],
			'reycore-header-search-top' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/header-search-top/header-search-top' . $rtl . '.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			],
			'reycore-header-search' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/header-search/header-search' . $rtl . '.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'reycore-main-menu' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/main-menu/main-menu' . $rtl . '.css',
				'priority' => 'high'
			],
			'reycore-ajax-load-more' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/ajax-load-more/ajax-load-more' . $rtl . '.css',
				'priority' => 'low'
			],
			'reycore-cookie-notice' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/cookie-notice/cookie-notice' . $rtl . '.css',
				'priority' => 'high',
				'callback' => function(){
					return reycore__check_feature('cookie-notice');
				},
			],
			'reycore-language-switcher' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/language-switcher/language-switcher' . $rtl . '.css',
				'priority' => 'high',
			],
			'reycore-mega-menu' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/mega-menu/mega-menu' . $rtl . '.css',
			],
			'reycore-menu-icons' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/menu-icons/menu-icons' . $rtl . '.css',
				'priority' => 'high',
			],
			'reycore-modals' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/modals/modals' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-post-social-share' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/post-social-share/post-social-share' . $rtl . '.css',
			],
			'reycore-scroll-top' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/scroll-top/scroll-top' . $rtl . '.css',
			],
			'reycore-side-panel' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/side-panel/side-panel' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-sticky-social' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/sticky-social/sticky-social' . $rtl . '.css',
				'priority' => 'high',
			],
			'reycore-utilities' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/utilities/utilities' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-pass-visibility' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/pass-visibility/pass-visibility' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-videos' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/videos/videos' . $rtl . '.css',
				'priority' => 'high',
			],
			'simple-scrollbar' => [
				'src'     => REY_CORE_URI . 'assets/css/lib/simple-scrollbar.css',
				'deps'      => [],
			],
			'rey-splide' => [
				'src'     => REY_CORE_URI . 'assets/css/lib/splide.css',
				'priority' => 'high',
				'deps'      => [],
			],
		];

		foreach ($styles as $key => $style) {

			if( ! isset($style['deps']) ){
				$styles[$key]['deps'] = function_exists('reyAssets') ? reyAssets()::STYLE_HANDLE : [];
			}

			if( ! isset($style['version']) ){
				$styles[$key]['version'] = REY_CORE_VERSION;
			}
		}

		return $styles;
	}

	function scripts(){

		return [
			'flying-pages' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/flying-pages.js',
				'deps'    => [],
				'version' => '2.1.2-r',
				'enqueue' => get_theme_mod('perf__enable_flying_scripts', false),
				'defer' => true,
				'localize' => [
					'name' => 'FPConfig',
					'params' => [
						'delay' => 0,
						'ignoreKeywords' => ['wp-admin', 'logout', 'wp-login.php', 'add-to-cart=', 'customer-logout'],
						'maxRPS' => 3,
						'hoverDelay' => 50,
					],
				],
				'plugin' => true
			],
			'animejs' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/anime.min.js',
				'deps'    => ['jquery'],
				'version' => '3.1.0',
				'plugin' => true
			],
			'simple-scrollbar' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/simple-scrollbar.js',
				'deps'    => ['jquery'],
				'version' => '0.4.0',
			],
			'wnumb' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/wnumb.js',
				'deps'    => [],
				'version' => '1.2.0',
			],
			'slick' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/slick.js',
				'deps'    => ['jquery'],
				'version' => '1.8.1',
				'plugin' => true
			],
			'splidejs' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/splide.js',
				'deps'    => [],
				'version' => '2.4.21',
				'plugin' => true,
			],
			'rey-splide' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-slider.js',
				'deps'    => ['splidejs'],
				'version' => REY_CORE_VERSION,
				'localize' => [
					'name' => 'reyCoreSplideParams',
					'params' => [
						'classes' => [],
						'direction' => is_rtl() ? 'rtl' : 'ltr',
					],
				],
			],
			'scroll-out' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/scroll-out.js',
				'deps'    => ['jquery'],
				'version' => '2.2.3',
				'callback' => 'rey__is_blog_list',
				'plugin' => true
			],
			'reycore-scripts' => [
				'src'      => REY_CORE_URI . 'assets/js/general/c-general.js',
				'deps'     => ['jquery', 'imagesloaded', 'rey-script'],
				'version'  => REY_CORE_VERSION,
				'localize' => [
					'name' => 'reyCoreParams',
					'params' => apply_filters('reycore/script_params', [
						'icons_path' => reycore__icons_sprite_path(),
						'social_icons_path' => reycore__social_icons_sprite_path(),
						'js_params'     => [
							'sticky_debounce' => 200,
							'dir_aware' => false,
							'svg_icons_version' => apply_filters('rey/svg_icon/append_version', false) ? REY_CORE_VERSION : ''
						]
					]),
				],
				'enqueue' => true
			],
			'rey-videos' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-videos.js',
				'deps'    => ['reycore-scripts'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-cookie-notice' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-cookie-notice.js',
				'deps'    => ['reycore-scripts'],
				'version' => REY_CORE_VERSION,
				'callback' => function(){
					return reycore__check_feature('cookie-notice');
				},
			],
			'reycore-footer-reveal' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-footer-reveal.js',
				'deps'    => ['reycore-scripts'],
				'version' => REY_CORE_VERSION,
				'callback' => function(){
					return reycore__check_feature('footer-reveal');
				},
			],
			'reycore-header-search' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-header-search.js',
				'deps'    => ['reycore-scripts'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-load-more' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-load-more.js',
				'deps'    => ['reycore-scripts', 'scroll-out'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-mega-menu' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-mega-menu.js',
				'deps'    => ['reycore-scripts'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-modals' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-modal.js',
				'deps'    => ['reycore-scripts', 'wp-util'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-scroll-top' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-scroll-to-top.js',
				'deps'    => ['reycore-scripts'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-sticky-global-sections' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-sticky-global-sections.js',
				'deps'    => ['reycore-scripts'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-sticky' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-sticky.js',
				'deps'    => ['reycore-scripts', 'imagesloaded'],
				'version' => REY_CORE_VERSION,
			],
		];
	}

	function register_scripts()
	{
		reyCoreAssets()->register_asset('styles', $this->styles());
		reyCoreAssets()->register_asset('scripts', $this->scripts());

		if( is_user_logged_in() ){
			wp_register_style(
				'reycore-frontend-admin',
				REY_CORE_URI . 'assets/css/general-components/frontend-admin/frontend-admin' . reyCoreAssets()::rtl() . '.css',
				[],
				REY_CORE_VERSION
			);
		}
	}

	function add_excludes_choices( $choices ){
		return array_merge($choices, wp_list_filter( $this->styles(), [ 'enqueue' => true ] ));
	}

	function fallback_enqueue_scripts()
	{
		if( is_user_logged_in() ){
			wp_enqueue_script( 'reycore-frontend-admin', REY_CORE_URI . 'assets/js/general/c-frontend-admin.js', ['jquery', 'reycore-scripts'], REY_CORE_VERSION, true );
		}

		if( function_exists('reyAssets') ){
			return;
		}

		$excludes = reyCoreAssets()->get_excludes();

		foreach (['styles', 'scripts'] as $type) {

			$func = 'all_' . $type;

			if( !( is_callable([$this, $func]) && $assets = call_user_func([$this, $func]) ) ){
				continue;
			}

			foreach( $assets as $handle => $asset ){

				$enqueue = false;

				// always enqueue
				if( isset($asset['enqueue']) && $asset['enqueue'] ){
					$enqueue = ! in_array($handle, $excludes, true);
				}

				else {
					// check callback
					if( isset($asset['callback']) ){
						if( is_callable($asset['callback']) && call_user_func($asset['callback']) ){
							$enqueue = true;
						}
					}
				}

				if( $enqueue ){
					call_user_func( [ reyCoreAssets(), 'add_' . $type ], $handle );
				}
			}
		}
	}


	function includes(){
		//#! Load core files
		require_once REY_CORE_DIR . 'inc/plugin-functions.php';
		// Misc.
		require_once REY_CORE_DIR . 'inc/includes/loader.php';
		// vendor
		require_once REY_CORE_DIR . 'inc/vendor/kirki/kirki.php';
		require_once REY_CORE_DIR . 'inc/vendor/advanced-custom-fields-pro/acf.php';
		require_once REY_CORE_DIR . 'inc/vendor/customizer-export-import/customizer-export-import.php';
	}

	function includes_before_plugins_loaded(){

		// Compatiblity
		require_once REY_CORE_DIR . 'inc/compatibility/pre-loader.php';

		// Modules
		require_once REY_CORE_DIR . 'inc/modules/pre-loader.php';
	}

	function includes_after_plugins_loaded()
	{
		$this->load_plugin_textdomain();

		if ( reycore__theme_active() && class_exists('WooCommerce') ) {
			require_once REY_CORE_DIR . 'inc/woocommerce/woocommerce.php';
		}

		require_once REY_CORE_DIR . 'inc/gutenberg/gutenberg.php';

		// Advanced Custom Fields
		if( class_exists('ACF') )  {
			// load predefined fields
			if( apply_filters('reycore/acf/use_predefined_fields', true) ){
				require_once REY_CORE_DIR . 'inc/acf/acf-fields.php';
			}
			require_once REY_CORE_DIR . 'inc/acf/acf-functions.php';
		}

		// Elementor
		require_once REY_CORE_DIR . 'inc/elementor/elementor.php';
		require_once REY_CORE_DIR . 'inc/elementor/elementor-widget-assets.php';
		require_once REY_CORE_DIR . 'inc/elementor/global-sections.php';
		require_once REY_CORE_DIR . 'inc/elementor/global-sections-visibility.php';
		require_once REY_CORE_DIR . 'inc/elementor/cover.php';
		require_once REY_CORE_DIR . 'inc/elementor/mega-menus.php';

		if( reycore__theme_active() ){

			// Load Kirki fallback & Customizer options
			require_once REY_CORE_DIR . 'inc/customizer/kirki-fallback.php';

			if( class_exists('Kirki') )  {
				require_once REY_CORE_DIR . 'inc/customizer/customizer-options.php';
				require_once REY_CORE_DIR . 'inc/customizer/customizer-functions.php';
				require_once REY_CORE_DIR . 'inc/customizer/customizer-styles.php';
				require_once REY_CORE_DIR . 'inc/customizer/customizer-fields.php';
			}
		}

		// Modules
		if( reycore__theme_active() ){
			require_once REY_CORE_DIR . 'inc/modules/loader.php';
		}

		// Compatiblity
		if( reycore__theme_active() ){
			require_once REY_CORE_DIR . 'inc/compatibility/loader.php';
		}

		do_action('reycore/loaded');
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}
endif;

ReyCore::getInstance();
