<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined('WP_ROCKET_VERSION') && !class_exists('ReyCore_Compatibility__WPRocket') ):

	class ReyCore_Compatibility__WPRocket
	{
		public function __construct()
		{
			add_filter( 'rocket_cdn_reject_files', [$this, 'exclude_files_cdn'], 20, 2);
			add_filter( 'rocket_excluded_inline_js_content', [$this, 'exclude_inline_js'], 10);
			// add_filter('rocket_exclude_defer_js', [$this, 'exclude_defer_js'], 10);
			// add_filter('rocket_defer_inline_exclusions', [$this, 'exclude_inline_defer_js'], 10);
			add_filter( 'rocket_cpcss_excluded_post_types', [$this, 'exclude_cpt_cpcss'], 10);
			add_action( 'rocket_critical_css_generation_process_complete', [$this, 'add_extra_cpcss']);
			add_action( 'reycore/kirki_fields/after_field=perf__preload_assets', [ $this, 'add_customizer_options' ] );
			add_action( 'rey/flush_cache_after_updates', [ $this, 'empty_theme_mod' ] );
			add_filter( 'reycore/elementor/revslider/waitforinit', [ $this, 'revslider_defer' ] );
			add_filter( 'reycore/is_mobile', [ $this, 'is_mobile' ] );
			add_filter( 'reycore/supports_mobile_caching', [ $this, 'cache_separately' ] );
			add_filter( 'reycore/mobile_improvements', [ $this, 'supports_mobile_improvements' ] );
			add_filter( 'theme_mod_site_preloader', [ $this, 'disable_site_preloader' ] );
			add_action( 'reycore/kirki_fields/before_field=site_preloader', [ $this, 'add_preloader_option_notice' ] );
			add_action( 'wp_footer', [ $this, 'enqueue_delay_js_handler' ] );
			add_filter( 'rocket_delay_js_exclusions', [$this, 'exclude_delay_js'], 10);
			add_filter( 'body_class', [$this, 'body_class'], 10);

		}

		function body_class($classes){

			if( is_user_logged_in() ){
				return $classes;
			}

			if ( get_rocket_option( 'delay_js' ) ) {
				$classes['rey_wpr'] = '--not-ready';
			}

			return $classes;
		}

		public function exclude_files_cdn($files) {

			// (.*).svg

			if( function_exists('rey__svg_sprite_path') ){
				$files[] = rey__svg_sprite_path();
			}

			if( function_exists('reycore__icons_sprite_path') ){
				$files[] = reycore__icons_sprite_path();
			}

			if( function_exists('reycore__social_icons_sprite_path') ){
				$files[] = reycore__social_icons_sprite_path();
			}

			return $files;
		}

		function exclude_defer_js( $pattern ) {

			return $pattern;
		}

		function exclude_inline_defer_js( $pattern ) {

			if( is_array($pattern) ){
				// $pattern[] = 'revapi';
			}
			else {
				// $pattern .= '|revapi';
			}

			return $pattern;
		}

		function exclude_inline_js( $pattern ) {

			$pattern[] = 'rey-no-js';

			return $pattern;
		}

		function exclude_cpt_cpcss( $cpt ) {

			if( class_exists('ReyCore_GlobalSections') ){
				$cpt[] = ReyCore_GlobalSections::POST_TYPE;
			}

			return $cpt;
		}

		function revslider_defer( $status ) {

			// defer is disabled, can return predefined
			if ( ! get_rocket_option( 'defer_all_js' ) ) {
				return $status;
			}

			// defer per post is disabled, can return predefined
			if ( is_rocket_post_excluded_option( 'defer_all_js' ) ) {
				return $status;
			}

			return false;
		}


		function add_extra_cpcss(){

			$css = [];

			$css[] = 'body{overflow-y: scroll}';

			// Tabs
			$css[] = '.elementor-section.rey-tabs-section>.elementor-container>.elementor-row>.elementor-column:not(:first-child),.elementor-section.rey-tabs-section.--tabs-loaded>.elementor-container>.elementor-row>.elementor-column:not(.--active-tab),.elementor-section.rey-tabs-section>.elementor-container>.elementor-column:not(:first-child),.elementor-section.rey-tabs-section.--tabs-loaded>.elementor-container>.elementor-column:not(.--active-tab){visibility:hidden;opacity:0;position:absolute;left:0;top:0;pointer-events:none}';

			// Sticky Social Icons
			$css[] = '.rey-stickySocial.--position-left{left:-150vw}.rey-stickySocial.--position-right{right:150vw}';

			// Header panels
			$css[] = '.rey-compareNotice-wrapper,.rey-scrollTop,.rey-wishlist-notice-wrapper{left:-150vw;opacity:0;visibility:hidden;pointer-events:none}';
			$css[] = '.rey-accountPanel-wrapper.--layout-drop{display:none}';

			// Mega menus
			$css[] = '.rey-mega-gs,.depth--0>.sub-menu{display:none !important;}';

			// Separator in menu
			$css[] = '.rey-mainMenu--desktop.rey-mainMenu--desktop .menu-item.depth--0.--separated{position:relative;padding-left:0.625rem;margin-left:1.25rem;}@media(min-width:1025px){.rey-mainMenu--desktop.rey-mainMenu--desktop .menu-item.depth--0.--separated{padding-left:var(--header-nav-x-spacing);margin-left:calc(var(--header-nav-x-spacing) * 2);}}';

			// product thumbs
			$css[] = '.woocommerce ul.products li.product .rey-productThumbnail .rey-thumbImg, .woocommerce ul.products li.product .rey-productThumbnail .rey-productThumbnail__second, .woocommerce ul.products li.product .rey-productThumbnail img {backface-visibility: visible;}';

			// dashed button
			$css[] = '.elementor-element.elementor-button-dashed.--large .elementor-button .elementor-button-text {padding-right:50px;}.elementor-element.elementor-button-dashed.--large .elementor-button .elementor-button-text:after {width: 35px;}';

			// svg's
			$css[] = '.elementor-icon svg{visibility:hidden;max-width: 1rem;}';

			// Cookie notice
			$css[] = '.rey-cookieNotice.--visible{left: var(--cookie-distance);opacity: 1;transform: translateY(0)}';

			// Helper classes
			$css[] = '.--hidden{display:none!important} @media(max-width:767px){.--dnone-sm,.--dnone-mobile{display:none!important}} @media(min-width:768px) and (max-width:1025px){.--dnone-md,.--dnone-tablet{display:none!important}} @media(min-width:1025px){.--dnone-lg,.--dnone-desktop{display:none!important}}';

			// Nest cover
			$css[] = '.rey-coverNest .cNest-slide,.cNest-nestLines{opacity: 0}';

			$css[] = get_theme_mod('perf__wprocket_extra_critical', '');

			if( !($filesystem = reycore__wp_filesystem()) ){
				return;
			}

			$dir_path = WP_CONTENT_DIR . '/cache/critical-css/' . (is_multisite() ? get_current_blog_id() : 1);

			if ( ! $filesystem->is_dir( $dir_path ) ) {
				return;
			}

			if( ! ($list = $filesystem->dirlist( $dir_path )) ){
				return;
			}

			$css_files = array_filter($list, function($file){
				return ($info = pathinfo($file['name'])) && $info['extension'] === 'css';
			});

			if( empty($css_files) ){
				return;
			}

			$success = [];

			foreach ($css_files as $key => $css_file) {

				$file_path = trailingslashit($dir_path) . $key;

				$data = $filesystem->get_contents( $file_path );
				$data .= implode('', $css);

				if( $filesystem->put_contents( $file_path, $data ) ){
					$success[] = true;
				}

			}

			if( in_array(true, $success, true) ){
				rocket_clean_domain();
			}
		}

		function empty_theme_mod(){
			set_theme_mod('perf__wprocket_extra_critical', '');
		}

		function is_mobile( $status ){

			if ( ! class_exists( 'WP_Rocket_Mobile_Detect' ) ) {
				return $status;
			}

			$detect = new WP_Rocket_Mobile_Detect();

			if ( $detect->isMobile() ) {
				return true;
			}

			return $status;
		}

		function cache_separately( $status ){

			if( get_rocket_option( 'cache_mobile' ) && get_rocket_option( 'do_caching_mobile_files' ) ){
				return true;
			}

			return $status;
		}

		function supports_mobile_improvements( $status ){

			if( get_theme_mod('perf__wprocket_mobile_improvements', false) ){
				return true;
			}

			return $status;
		}

		function add_customizer_options( $args ){

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'textarea',
				'settings'    => 'perf__wprocket_extra_critical',
				'label'       => esc_html_x( 'Extra Critical CSS styles', 'Customizer control title', 'rey-core' ),
				'description' => esc_html_x( 'Append extra styles to WPRocket\'s CPCSS in case something is not rendering properly. Works only if "Optimize CSS delivery" is enabled.', 'Customizer control description', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html_x('eg: .selector {}', 'Customizer control description', 'rey-core'),
					'data-control-class' => '--block-label',
				],
			] );

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'toggle',
				'settings'    => 'perf__wprocket_mobile_improvements',
				'label'       => esc_html__( 'Add Mobile Improvements', 'rey-core' ),
				'description' => sprintf(_x( 'Requires WPRocket Mobile Cache & Cache Separately to be enabled. Please <a href="%s" target="_blank">read more here</a> on how to use this option.', 'Customizer control description', 'rey-core' ), 'https://support.reytheme.com/kb/mobile-improvements/'),
				'section'     => $args['section'],
				'default'     => false,
			] );

		}

		function exclude_delay_js( $pattern ) {

			/**
			 * Excluding scripts beats the purpose of this option.
			 * When an exclusion is added, its dependencies must also be added and it's jQuery, Rey Core, Elementor frontend, etc.
			 */

			$pattern[] = 'reycore-delay-js';

			return $pattern;
		}

		function enqueue_delay_js_handler(){
			if ( get_rocket_option( 'delay_js' ) ) {
				wp_enqueue_script('reycore-delay-js');
			}
		}

		function disable_site_preloader($mod){

			if ( get_rocket_option( 'delay_js' ) ) {
				return false;
			}

			return $mod;
		}

		function add_preloader_option_notice($args){

			if ( get_rocket_option( 'delay_js' ) ) {
				reycore_customizer__notice([
					'section'     => $args['section'],
					'default'     => esc_html_x('Heads up! The preloader is disabled because WPRocket\'s Delay JS option is enabled.', ' Customizer control label', 'rey-core')
				] );
			}

		}

	}

	new ReyCore_Compatibility__WPRocket;
endif;
