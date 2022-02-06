<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_AssetsManager') ):

	final class ReyCore_AssetsManager
	{
		private static $_instance = null;

		/**
		 * Used to determine if page has stored data.
		 */
		public $has_data = false;

		/**
		 * Store Filesystem
		 */
		private $filesystem;

		/**
		 * All registered styles and scripts.
		 */
		protected $registered_styles = [];
		protected $registered_scripts = [];

		/**
		 * Styles and scripts that has been added throughout the page load
		 */
		protected $styles = [];
		protected $scripts = [];
		protected $localized_scripts = [];
		protected $css_to_exclude = [];

		/**
		 * Styles and scripts handles that have been stored already.
		 */
		protected $stored_styles = [];

		/**
		 * Path where to save files.
		 */
		private $dir_path;

		/**
		 * Should retry writing of a file
		 */
		private $retry_css_to_write = [];

		/**
		 * Storing conditions
		 */
		private $store_conditions = [];

		/**
		 * Should cache separately for mobiles.
		 * Causes issues invalidating cache, and regenerates data.
		 */
		public $mobile = false;

		private static $wp_uploads_dir = [];

		/**
		 * Meta key for css.
		 */
		const META_KEY = '_rey_css';
		const META_KEY_LOGGED_IN = '_rey_css_logged_in';

		/**
		 * Types of data
		 */
		const CSS_TYPES = ['header', 'footer'];

		/**
		 * Detects paths in CSS.
		 */
		const ASSETS_REGEX = '/url\s*\(\s*(?!["\']?data:)(?![\'|\"]?[\#|\%|])([^)]+)\s*\)([^;},\s]*)/i';

		private function __construct()
		{

			// setup
			add_action( 'init', [$this, 'init']);
			//prepare for storing
			add_action( 'wp', [$this, 'wp']);
			// hook for registering
			add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 5 );
			// enqueue assets
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_head_styles']);
			// dequeue stuff
			add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_styles']);
			// enqueue stuff in footer
			add_action( 'wp_footer', [$this, 'enqueue_footer_styles'] );
			// enqueue scripts later in footer
			add_action( 'wp_footer', [$this, 'enqueue_footer_scripts'], 15 );
			// write data based compiled assets
			add_action( 'wp_footer', [$this, 'prepare_to_write'], 20);
			// unload scripts that are in combined version
			add_filter('script_loader_tag', [$this, 'script_loader_tag'], 10, 2);
			// unload styles
			add_filter('style_loader_tag', [$this, 'style_loader_tag'], 10, 2);

			// Cleanup
			add_action( 'customize_save_perf__css_exclude', [$this, 'clear__customize_save_perf__css_exclude']);
			add_action( 'acf/save_post', [$this, 'clear__acf_save_perf__css_exclude'], 20);
			add_action( 'rey/flush_cache_after_updates', [$this, 'clear__basic'], 20);
			add_action( 'elementor/admin/after_create_settings/elementor', [$this, 'clear__basic'], 10);
			add_action( 'wp_ajax__refresh_assets', [$this, 'clear__asses_admin_bar']);
			add_action( 'save_post', [$this, 'preload_page']);
			add_action( 'elementor/editor/after_save', [ $this, 'preload_page'], 10);
			add_action( 'saved_term', [$this, 'preload_term']);

			add_filter('theme_mod_perf__enable_flying_scripts', [$this, 'disable_flying_pages'], 10);
		}

		function init(){

			$this->debug = defined('REY_DEBUG_ASSETS') && REY_DEBUG_ASSETS;

			$this->maybe_load_natural();

			$this->settings = apply_filters('reycore/assets/settings', [
				'load_natural_css' => $this->load_natural_css, // will load assets in natural order. Works only if stored data is disabled.
				'store_data'       => true, // adds ability to store the assets handles in DB, to enqueue later
				'save_css'         => true, // combines and minifies handles, based of stored handles in DB
				'save_js'          => ! $this->load_natural_js, // combines and minifies handles
				'mobile'           => false, // should behave differently in mobile (Work in progress)
				'defer'            => true
			]);

			$this->meta_key = ! is_user_logged_in() ? self::META_KEY : self::META_KEY_LOGGED_IN;

			$this->get_filesystem();
			$this->clear_data();

			$this->mobile = $this->settings['mobile'] && reycore__is_mobile();
		}

		function get_filesystem(){

			if( ! $this->settings['save_css'] && ! $this->settings['save_js'] ){
				return;
			}

			if( !($filesystem = reycore__wp_filesystem()) ){
				return;
			}

			$this->filesystem = $filesystem;

			$dir_path = self::get_base_uploads_dir();

			if ( ! $this->filesystem->is_dir( $dir_path ) ) {
				$this->filesystem->mkdir( $dir_path );
			}

			$this->dir_path = trailingslashit( $dir_path );
		}

		function wp(){

			$this->css_to_exclude = reycore__get_option( 'perf__css_exclude', ['rey-presets'] );

			if( $this->maybe_dequeue_wp_gutenberg_blocks() ){
				$this->css_to_exclude[] = 'rey-gutenberg';
			}

			$this->check_for_data_store();

			if( ! $this->should_store_data ){
				return;
			}

			$this->set_storing_conditions();
			$this->set_data();
		}

		public static function default_data(){

			$data = [];

			foreach (self::CSS_TYPES as $type) {
				$data[$type] = [];
			}

			return $data;
		}

		function set_data(){

			$this->stored_styles = self::default_data();

			// check if any data saved
			if( $data = $this->get_data() ){

				$this->has_data = true;

				if( isset($data) ){
					$this->stored_styles = $data;
				}
			}
			// stored data is empty,
			// but maybe there's some manually excluded css?
			else {
				// append a fake item, to prevent loading all styles on initial load.
				if( ! empty($this->css_to_exclude) ){
					$this->stored_styles['header'][] = 'fake-handle';
				}
			}

		}

		function get_excludes(){
			return $this->css_to_exclude;
		}

		function set_storing_conditions(){

			$this->store_conditions = [
				'search' => is_search(),
				'is404' => is_404(),
				'post' => is_singular() || is_home() || is_front_page() || self::is_shop(),
				'term' => is_tax() || is_archive(),
			];

			if( ! in_array(true, $this->store_conditions, true) ){
				self::log( 'Assets - Data cannot be written! ' . reycore__current_url() );
			}
		}

		public function get_conditions(){
			return $this->store_conditions;
		}

		function elementor_edit(){
			return class_exists('\Elementor\Plugin') &&
				\Elementor\Plugin::$instance->editor &&
				( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() );
		}

		function check_for_data_store(){

			// stop storing anything and load natural
			if( $this->settings['load_natural_css'] ){
				$this->should_store_data = false;
				return;
			}

			// stop storing anything if specified
			if( ! $this->settings['store_data'] ){
				$this->should_store_data = false;
				return;
			}

			$c = [];

			if( is_admin() || is_feed() || is_embed() || wp_doing_cron() ){
				$c[] = true;
			}

			if ( isset( $_REQUEST['action'] ) && ( 'heartbeat' == strtolower( $_REQUEST['action'] ) ) ) {
				$c[] = true;
			}

			if( is_customize_preview() ){
				$c[] = true;
			}

			if( $this->elementor_edit() ){
				$c[] = true;
			}

			$this->should_store_data = ! in_array(true, $c, true);
		}

		function register_asset( $type, $assets ){
			if( $type === 'styles' ){
				$this->registered_styles = array_merge($this->registered_styles, (array) $assets);
			}
			elseif( $type === 'scripts' ){
				$this->registered_scripts = array_merge($this->registered_scripts, (array) $assets);
			}
		}

		function register_assets(){

			/**
			 * Hook to register Rey styles.
			 * @since 2.0.0
			 */
			do_action('reycore/assets/register_scripts');

			foreach( $this->registered_styles as $handle => $style ){
				wp_register_style($handle, $style['src'], $style['deps'], $style['version']);
			}

			foreach( $this->registered_scripts as $handle => $script ){
				if( isset($script['src']) ){
					wp_register_script(
						$handle,
						$script['src'],
						isset($script['deps']) ? $script['deps'] : [],
						isset($script['version']) ? $script['version'] : REY_CORE_VERSION,
						isset($script['in_footer']) ? $script['in_footer'] : true
					);
					if( isset($script['localize']) && is_array($script['localize']['params']) ){
						wp_localize_script($handle, $script['localize']['name'], $script['localize']['params']);
					}
				}
			}

		}

		function maybe_enqueue( $asset, $key = '' ){

			$enqueue = false;

			// always enqueue
			if( isset($asset['enqueue']) && $asset['enqueue'] ){

				$enqueue = true;

				if( in_array($key, $this->css_to_exclude, true) ){
					$enqueue = false;
				}

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

		function enqueue_mandatory(){

			foreach( $this->registered_styles as $handle => $style ){
				if( $this->maybe_enqueue( $style, $handle ) ){
					$this->add_styles($handle);
				}
			}

			foreach( $this->registered_scripts as $handle => $script ){
				if( $this->maybe_enqueue( $script, $handle ) ){
					$this->add_scripts($handle);
				}
			}

		}

		/**
		 * Enqueue Header styles
		 */
		function enqueue_head_styles(){

			// Just load everything in elementor mode
			if( $this->elementor_edit() ){
				self::enqueue_all_styles(array_keys($this->registered_styles));
				return;
			}

			// Load any mandatory script
			$this->enqueue_mandatory();

			if( $this->settings['load_natural_css'] ){
				return;
			}

			// just load everything if data cannot be saved
			if( ! (isset($this->should_store_data) && $this->should_store_data) ){
				self::enqueue_all_styles(array_keys($this->registered_styles));
				return;
			}

			// load head styles
			$this->enqueue_styles('header');
		}

		/**
		 * Enqueue Footer
		 */
		function enqueue_footer_styles(){

			if( ! $this->settings['load_natural_css'] ){
				$this->enqueue_styles('footer', false);
			}

		}

		function enqueue_styles( $type, $with_deps = true ){

			// On first load, without any data saved;
			if( empty($this->stored_styles[$type]) ){

				if( $type === 'header' ){

					foreach($this->registered_styles as $handle => $asset){

						// Exclude Elementor widgets
						// (They're loaded separately in Elementor Rey's widget assets (before_parse_css))
						if( strpos($handle, 'reycore-widget-') !== false ){
							continue;
						}

						// Low priority
						if( isset($asset['priority']) && $asset['priority'] === 'low' ){
							continue;
						}

						// Redundant?
						if( wp_style_is($handle, 'enqueued') ){
							continue;
						}

						// in excludes?

						// check callback
						if( isset($asset['callback']) && is_callable($asset['callback']) ){
							if( ! call_user_func($asset['callback']) ){
								continue;
							}
						}

						wp_enqueue_style($handle);
					}
				}

				return;
			}

			if( ! isset($this->stored_styles[$type]) ){
				return;
			}

			/**
			 * Data is stored, either load combined, or individual;
			 */
			$enqueue_individual = true;

			if( $this->settings['save_css'] ){
				$hash = $this->hash( $this->stored_styles[$type] );
				$stylesheet_path = $this->dir_path . self::get_stylesheet_basename( $hash, $type );

				if( $this->filesystem && $this->filesystem->is_file( $stylesheet_path ) ){
					if( $stylesheet_url = self::get_stylesheet_url( $hash, $type ) ){

						$deps = $with_deps ? $this->get_style_deps($type) : [];

						wp_enqueue_style( 'reycore-' . $hash , $stylesheet_url, $deps, REY_CORE_VERSION . '.' . filemtime( $stylesheet_path ) );

						$enqueue_individual = false;
					}
				}
				else {
					$this->retry_css_to_write[$type] = true;
				}
			}

			if( $enqueue_individual ){
				foreach($this->stored_styles[$type] as $handle){
					wp_enqueue_style($handle);
				}
			}
		}

		function get_style_deps( $type = 'header' ){

			$deps = [];

			if( empty($this->stored_styles[$type]) ){
				return $deps;
			}

			if( ! $this->settings['save_css'] ){
				return $deps;
			}

			$wp_assets = wp_styles();

			foreach($this->stored_styles[$type] as $handle){

				if( ! (isset($wp_assets->registered[ $handle ]) && ($script = $wp_assets->registered[ $handle ])) ){
					continue;
				}

				$deps = array_merge($script->deps, $deps);
			}

			return array_diff( array_unique($deps), $this->stored_styles[$type] );
		}

		function style_loader_tag($tag, $handle){

			if( ! isset($this->should_store_data) ){
				return $tag;
			}

			if( in_array($handle, ['woocommerce-general'], true) ){
				return '';
			}

			return $tag;
		}

		function maybe_dequeue_wp_gutenberg_blocks(){

			$maybe_dequeue = false;

			if( (bool) reycore__get_option( 'perf__disable_wpblock', false ) ){

				$maybe_dequeue = true;

				if( get_theme_mod('perf__disable_wpblock__posts', true) && is_single() && 'post' == get_post_type() ){
					$maybe_dequeue = false;
				}
			}

			return $maybe_dequeue;
		}

		function dequeue_styles(){

			if( $this->maybe_dequeue_wp_gutenberg_blocks() ){

				wp_dequeue_style( 'wp-block-library' );
				wp_dequeue_style( 'wp-block-library-theme' );

			}

			if( (bool) reycore__get_option( 'perf__disable_wcblock', false ) ){
				wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
			}
		}

		function sort_css_priorities($data){

			$high = $mid = $low = [];

			foreach($data as $key => $handle){

				if( ! isset( $this->registered_styles[ $handle ] ) ){
					continue;
				}

				$style = $this->registered_styles[$handle];

				if( isset($style['priority']) ){
					if( $style['priority'] === 'high' ){
						$high[] = $handle;
					}
					else if( $style['priority'] === 'low' ){
						$low[] = $handle;
					}
				}
				else{
					$mid[] = $handle;
				}
			}

			return [
				'header' => array_merge($high, $mid),
				'footer' => $low,
			];
		}

		function get_styles(){
			$styles = array_unique($this->styles);
			return $this->sort_css_priorities( $styles );
		}

		function add_styles( $handlers ){

			foreach ((array) $handlers as $key => $handler) {

				$is_enqueued = wp_style_is($handler, 'enqueued');

				if( isset($this->collected_styles) ){
					$this->collected_styles[] = $handler;
				}

				// just enqueue style
				if( $this->settings['load_natural_css'] && ! $is_enqueued ){
					wp_enqueue_style($handler);
					// no point in going further
					continue;
				}

				$this->styles[] = $handler;

				// bail if enqueued already
				if( $is_enqueued ) {
					continue;
				}

				// if empty stored data, enqueue style
				if( empty( $this->stored_styles['header'] ) && empty( $this->stored_styles['footer'] ) ){
					wp_enqueue_style($handler);
				}

				// has data
				else {

					$in_header = in_array( $handler, $this->stored_styles['header'], true );
					$in_footer = in_array( $handler, $this->stored_styles['footer'], true );

					// check if it's missing from header AND footer data, and just enqueue it
					if( ! $in_header && ! $in_footer ){
						wp_enqueue_style($handler);
					}
				}
			}
		}

		function get_scripts(){
			return array_unique($this->scripts);
		}

		function add_scripts( $handlers ){
			foreach ((array) $handlers as $key => $handler) {
				$this->scripts[] = $handler;

				if( isset($this->collected_scripts) ){
					$this->collected_scripts[] = $handler;
				}

			}
		}

		function localize_script( $handle, $object_name, $l10n ){

			foreach ( (array) $l10n as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}

				$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}

			$this->localized_scripts[$handle] = "var $object_name = " . wp_json_encode( $l10n ) . ';';

		}

		function output_localized_scripts(){
			if( ! empty($this->localized_scripts) && $loc = array_unique($this->localized_scripts) ){
				printf('<script type="text/javascript">%s</script>', "/* <![CDATA[ */\n" . implode("\n", $loc). "/* ]]> */\n");
			}
		}

		function output_inserted_styles($styles = []){

			if( empty($styles) ){
				$styles = $this->get_styles();
			}

			printf("<script type='text/javascript' id='reystyles-loaded'>\n window.reyStyles=%s; \n</script>", wp_json_encode(array_values($styles)));
		}

		function output_inserted_scripts($scripts){
			printf("<script type='text/javascript' id='reyscripts-loaded'>\n window.reyScripts=%s; \n</script>", wp_json_encode(array_values($scripts)));
		}

		function enqueue_footer_scripts(){

			$this->output_localized_scripts();

			if( $this->elementor_edit() ){
				unset($this->registered_scripts['flying-pages']);
				self::enqueue_all_scripts(array_keys($this->registered_scripts));
				return;
			}

			$scripts = $this->get_scripts();

			if( empty($scripts) ){
				return;
			}

			$this->output_inserted_scripts($scripts);
			$this->output_inserted_styles();

			// Proceed combining JS
			if( $this->settings['save_js'] ){

				// let's grab dependecies
				// and glue with others
				if( $deps = $this->get_script_deps( true ) ){
					$this->scripts = array_merge($this->scripts, $deps);
					$scripts = $this->scripts;
				}

				if( ! $this->filesystem ){
					self::enqueue_all_scripts($scripts);
					return;
				}

				// make hash
				$hash = $this->hash( $scripts );

				$file_url = false;

				if( $this->dir_path ){

					$file_path = $this->dir_path . self::get_scripts_basename( $hash );
					$file_exists = $this->filesystem->is_file( $file_path );

					$js = $data_to_log = [];

					// force localize
					foreach ($scripts as $script) {

						if( isset($this->registered_scripts[$script]['external']) && $this->registered_scripts[$script]['external'] ){
							wp_enqueue_script($script);
							continue;
						}

						if( isset($this->registered_scripts[$script]['plugin']) && $this->registered_scripts[$script]['plugin'] ){
							wp_enqueue_script($script);
							continue;
						}

						if( ! $file_exists && ($script_path = $this->get_asset_path_by_handler($script, 'script')) && $this->filesystem->is_file( $script_path ) ) {
							$js[ $script ] = $this->filesystem->get_contents( $script_path );
							$data_to_log[] = $script;
						}

						// Make sure to keep localizations, as the tags will get removed later
						if ( isset($this->registered_scripts[$script]['localize']) ) {
							wp_enqueue_script($script);
						}

					}

					if( ! $file_exists && !empty($js) ){

						$js[] = $this->debug__print_handles($data_to_log);

						if( $this->filesystem->put_contents( $file_path, implode('', $js) ) ){

							$file_url = self::get_scripts_url( $hash );

							self::log( 'Assets - Combined JS.' );
						}
						else {
							// just load everything
							self::enqueue_all_scripts($scripts);
						}

					}
					else {
						$file_url = self::get_scripts_url( $hash );
					}

					// load script
					if( $file_url ){
						$this->combined_script_handle = 'reycore-' . $hash;
						wp_enqueue_script( $this->combined_script_handle , $file_url, [], REY_CORE_VERSION . '.' . filemtime( $file_path ), true );
					}

				}
				else {
					self::enqueue_all_scripts($scripts);
				}

			}
			else {
				self::enqueue_all_scripts($scripts);
			}
		}

		function script_loader_tag($tag, $handle){

			if( ! isset($this->should_store_data) ){
				return $tag;
			}

			if( $this->settings['save_js'] ){

				// defer combined script
				if( isset($this->combined_script_handle) && $handle === $this->combined_script_handle ){
					$attribute = '';
					if( $this->settings['defer'] ){
						$attribute = 'defer';
					}
					return str_replace( ' src', ' '. $attribute .' src', $tag );
				}

				// remove scripts that are in a combined version
				else if( in_array($handle, $this->get_scripts(), true) ){

					// don't remove dependencies that are flagged as plugins or external
					if( isset($this->registered_scripts[$handle]) ) {

						if( isset($this->registered_scripts[$handle]['plugin']) || isset($this->registered_scripts[$handle]['external']) ){
							return $tag;
						}

						return ''; // remove
					}
				}

			}

			return $tag;
		}

		function get_script_deps( $local_deps = false ){

			if( empty($this->scripts) ){
				return;
			}

			$wp_assets = wp_scripts();

			$deps = [];

			foreach($this->scripts as $handle){

				if( ! (isset($wp_assets->registered[ $handle ]) && ($script = $wp_assets->registered[ $handle ])) ){
					continue;
				}

				$deps = array_merge($script->deps, $deps);
			}

			$dependencies = array_diff( array_unique($deps), $this->scripts );

			if( $local_deps && ($registered_scripts = $this->registered_scripts) ){
				return array_filter($dependencies, function($a) use ($registered_scripts){
					return array_key_exists($a, $registered_scripts) && isset($registered_scripts[$a]['src']) && ! isset( $registered_scripts[$a]['external'] );
				});
			}

			return $dependencies;
		}

		public static function enqueue_all_scripts($scripts){
			foreach ($scripts as $script) {
				wp_enqueue_script($script);
			}
		}

		public static function enqueue_all_styles($styles){
			foreach ($styles as $style) {
				wp_enqueue_style($style);
			}
		}

		/**
		 * Make sure Rey's main style is always first.
		 *
		 * @param array $data
		 * @return array
		 */
		public static function sort( $data, $add_main = false ){

			sort($data);

			if( $add_main && function_exists('reyAssets') ){
				array_unshift($data, reyAssets()::STYLE_HANDLE);
			}

			return array_unique($data);
		}

		function hash( $data ){
			return substr( md5( wp_json_encode( self::sort($data) ) ), 0, 10 );
		}

		/**
		 * Check if new handles should be saved
		 */
		function get_css_to_write( $type, $styles ){

			$hash = $this->hash( $styles );

			$data = [];

			// Data exists
			if( isset( $this->stored_styles[ $type ] ) && ! isset($this->retry_css_to_write[ $type ]) ){

				// check if there are new/removed styles in the page
				if( $hash !== $this->hash( $this->stored_styles[ $type ] ) ){
					$data = $styles;
				}

			}
			// Data doesn't exist
			// just pass styles
			else {
				$data = $styles;
			}

			return $data;
		}

		function prepare_to_write(){

			if( ! (isset($this->should_store_data) && $this->should_store_data) ){
				return;
			}

			$should_write = [];

			$data_to_write = self::default_data();

			if( ! empty($this->stored_styles) ){
				$data_to_write = $this->stored_styles;
			}

			/**
			 * GET STYLES
			 */

			// collect styles added throughout page load
			$styles = $this->get_styles();

			foreach (self::CSS_TYPES as $type) {

				if( isset($styles[$type]) && ($type_styles = $styles[$type]) ){

					$data = $this->get_css_to_write($type, $type_styles);

					if( ! empty($data) ){

						// flag that something's new,
						// and stored data should be updated
						$should_write[] = true;

						// pass data
						$data_to_write[$type] = $data;

						// write stylesheet
						$this->write_css($data, $type);
					}
				}
			}

			if( in_array(true, $should_write, true) ){
				$this->write_data($data_to_write);
			}
		}

		public static function is_shop(){
			return class_exists('WooCommerce') && is_shop();
		}

		function get_data(){

			$data = '';
			$id = get_queried_object_id();

			if( $this->store_conditions['search'] ){
				$data = get_option( $this->meta_key . '_search', false);
			}
			else if( $this->store_conditions['is404'] ){
				$data = get_option( $this->meta_key . '_404', false);
			}
			else if( $this->store_conditions['post'] ){

				if( self::is_shop() ){
					$id = wc_get_page_id( 'shop' );
				}

				$data = get_post_meta($id, $this->meta_key, true);
			}

			else if( $this->store_conditions['term'] ){
				$data = get_term_meta($id, $this->meta_key, true);
			}

			return $data;
		}

		function write_data( $data ){

			// bail if no data to write
			if( empty($data['header']) && empty($data['footer']) ){
				return;
			}

			$id = get_queried_object_id();

			if( self::is_shop() ){
				$id = wc_get_page_id( 'shop' );
			}

			if( $id === 0 ){
				return;
			}

			// if no data saved already, add both logged in and out;
			if( ! $this->has_data ){
				$this->__write_data($id, $data, self::META_KEY);
				$this->__write_data($id, $data, self::META_KEY_LOGGED_IN);
			}
			// if data is already stored, run normally
			else {
				$this->__write_data($id, $data, $this->meta_key);
			}

			do_action('reycore/assets/write_data', $id, $this);

			self::log( sprintf('Assets - Wrote CSS data: %d / %s.', $id, reycore__get_page_title() ) );
		}

		private function __write_data($id, $data, $mkey){

			if( $this->store_conditions['search'] ){
				update_option( $mkey . '_search', $data);
			}
			else if( $this->store_conditions['is404'] ){
				update_option( $mkey . '_404', $data);
			}
			else if( $this->store_conditions['post'] ){
				update_post_meta($id, $mkey, $data);
			}
			else if( $this->store_conditions['term'] ){
				update_term_meta($id, $mkey, $data);
			}

		}

		function write_css( $data, $type = 'header' ){

			if( ! $this->filesystem ){
				return;
			}

			if( empty($data) ){
				return;
			}

			$css = [];

			$hash = $this->hash( $data );

			$data = self::sort($data, ($type === 'header'));
			$data_to_log = [];

			foreach ($data as $style) {
				if( ($stylesheet_file = $this->get_asset_path_by_handler($style)) && $this->filesystem->is_file( $stylesheet_file ) ) {
					// grabs CSS
					$stylesheet_css = $this->filesystem->get_contents( $stylesheet_file );
					$stylesheet_css = self::fixurls($stylesheet_file, $stylesheet_css);
					$css[$style] = $stylesheet_css;
					$data_to_log[] = $style;
				}
			}

			if( ! empty($css) && $this->dir_path ){

				$css_contents = str_replace(
					[': ', ';  ', '; ', '  '],
					[':', ';', ';', ' '],
					preg_replace( "/\r|\n/", '', implode('', $css) )
				);

				$file = $this->dir_path . self::get_stylesheet_basename($hash, $type);

				// if it already exists, don't rewrite it
				if( ! $this->filesystem->is_file( $file ) ){

					self::log( 'Assets - Stored stylesheet for ' . $type );

					$css_contents .= $this->debug__print_handles($data_to_log);

					return $this->filesystem->put_contents( $file, $css_contents );
				}
			}
		}

		private static function get_stylesheet_url( $hash, $position = 'header' ){
			return self::get_base_uploads_url() . self::get_stylesheet_basename($hash, $position);
		}

		private static function get_stylesheet_basename( $hash, $position = 'header' ){
			return sprintf('%s-%s%s.css', $position, $hash, self::rtl());
		}

		private static function get_scripts_url( $hash ){
			return self::get_base_uploads_url() . self::get_scripts_basename($hash);
		}

		private static function get_scripts_basename( $hash ){
			return sprintf('scripts-%s.js', $hash);
		}

		private static function get_wp_uploads_dir() {
			global $blog_id;

			if ( empty( self::$wp_uploads_dir[ $blog_id ] ) ) {
				self::$wp_uploads_dir[ $blog_id ] = wp_upload_dir( null, false );
			}

			return self::$wp_uploads_dir[ $blog_id ];
		}

		public static function get_base_uploads_dir() {
			$wp_upload_dir = self::get_wp_uploads_dir();

			return trailingslashit($wp_upload_dir['basedir']) . REY_CORE_THEME_NAME . '/';
		}

		public static function get_base_uploads_url() {
			$wp_upload_dir = self::get_wp_uploads_dir();

			return trailingslashit(set_url_scheme( $wp_upload_dir['baseurl'] )) . REY_CORE_THEME_NAME . '/';
		}

		public static function log($message){
			if( defined('REY_DEBUG_ASSETS_LOG') && REY_DEBUG_ASSETS_LOG ){
				error_log(var_export( $message,1));
			}
		}

		public function debug__print_handles( $data_to_log ){

			if( defined('REY_DEBUG_ASSETS_LOG') && REY_DEBUG_ASSETS_LOG ){
				return "\r\n" . '/** ' . "\r\n" . implode( "\r\n", $data_to_log ) . "\r\n" . '*/';
			}

		}

		/**
		 * Make sure URL's are absolute iso relative to original CSS location.
		 *
		 * @param string $file filename of optimized CSS-file.
		 * @param string $code CSS-code in which to fix URL's.
		 */
		static function fixurls( $file, $code )
		{
			// Switch all imports to the url() syntax.
			$code = preg_replace( '#@import ("|\')(.+?)\.css.*?("|\')#', '@import url("${2}.css")', $code );


			if ( preg_match_all( self::ASSETS_REGEX, $code, $matches ) ) {

				$wp_content_name = '/' . wp_basename( WP_CONTENT_DIR );
				$wp_root_dir = substr( WP_CONTENT_DIR, 0, strlen( WP_CONTENT_DIR ) - strlen( $wp_content_name ) );
				$wp_root_url = str_replace( $wp_content_name, '', content_url() );

				$file = str_replace( $wp_root_dir, '/', $file );
				/**
				 * Rollback as per https://github.com/futtta/autoptimize/issues/94
				 * $file = str_replace( AUTOPTIMIZE_WP_CONTENT_NAME, '', $file );
				 */
				$dir = dirname( $file ); // Like /themes/expound/css.

				/**
				 * $dir should not contain backslashes, since it's used to replace
				 * urls, but it can contain them when running on Windows because
				 * fixurls() is sometimes called with `ABSPATH . 'index.php'`
				 */
				$dir = str_replace( '\\', '/', $dir );
				unset( $file ); // not used below at all.

				$replace = array();
				foreach ( $matches[1] as $k => $url ) {
					// Remove quotes.
					$url      = trim( $url, " \t\n\r\0\x0B\"'" );
					$no_q_url = trim( $url, "\"'" );
					if ( $url !== $no_q_url ) {
						$removed_quotes = true;
					} else {
						$removed_quotes = false;
					}

					if ( '' === $no_q_url ) {
						continue;
					}

					$url = $no_q_url;
					if ( '/' === $url[0] || preg_match( '#^(https?://|ftp://|data:)#i', $url ) ) {
						// URL is protocol-relative, host-relative or something we don't touch.
						continue;
					} else { // Relative URL.

						$newurl = preg_replace( '/https?:/', '', str_replace( ' ', '%20', $wp_root_url . str_replace( '//', '/', $dir . '/' . $url ) ) );


						/**
						 * Hash the url + whatever was behind potentially for replacement
						 * We must do this, or different css classes referencing the same bg image (but
						 * different parts of it, say, in sprites and such) loose their stuff...
						 */
						$hash = md5( $url . $matches[2][ $k ] );
						$code = str_replace( $matches[0][ $k ], $hash, $code );

						if ( $removed_quotes ) {
							$replace[ $hash ] = "url('" . $newurl . "')" . $matches[2][ $k ];
						} else {
							$replace[ $hash ] = 'url(' . $newurl . ')' . $matches[2][ $k ];
						}
					}
				}

				$code = self::replace_longest_matches_first( $code, $replace );
			}

			return $code;
		}

		/**
		 * Given an array of key/value pairs to replace in $string,
		 * it does so by replacing the longest-matching strings first.
		 *
		 * @param string $string string in which to replace.
		 * @param array  $replacements to be replaced strings and replacement.
		 *
		 * @return string
		 */
		protected static function replace_longest_matches_first( $string, $replacements = array() )
		{
			if ( ! empty( $replacements ) ) {
				// Sort the replacements array by key length in desc order (so that the longest strings are replaced first).
				$keys = array_map( 'strlen', array_keys( $replacements ) );
				array_multisort( $keys, SORT_DESC, $replacements );
				$string = str_replace( array_keys( $replacements ), array_values( $replacements ), $string );
			}

			return $string;
		}

		public static function rtl(){
			return is_rtl() ? '-rtl' : '';
		}

		/**
		 * Determines if the styles should load naturally
		 * if caching plugins are handing the assets.
		 *
		 * @return bool
		 */
		public function maybe_load_natural(){

			$logged_in = is_user_logged_in();

			$this->load_natural_css = $this->debug;
			$this->load_natural_js = $this->debug ? true : $logged_in;
			$this->caching_plugins = self::caching_plugins();

			// It's cart or checkout, likely not handled by caching plugins
			// if( class_exists('WooCommerce') && is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url() ) { }

			/**
			 * Caching plugins are caching the actual page so checking for options
			 * doesn't do anything. Just load everything natural when logged out.
			 */

			if( in_array(true, $this->caching_plugins, true) ){

				if( ! $logged_in ){
					$this->load_natural_css = true;
					$this->load_natural_js = true;
				}

				return;
			}

			/** STOP */

			/**
			 * Test plugins for:
			 * - minify & combine enabled
			 * - if logged in users should be cached
			 */

			if( $this->caching_plugins['wprocket'] ){
				/**
				 * WPROCKET
				 */
				if( (get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_concatenate_css' )) ){
					if ( $logged_in ) {
						if( get_rocket_option( 'cache_logged_user' ) ){
							$this->load_natural_css = true;
						}
					}
					else {
						$this->load_natural_css = true;
					}
				}
				if( (get_rocket_option( 'minify_js' ) || get_rocket_option( 'minify_concatenate_js' )) ){
					if ( $logged_in ) {
						if( get_rocket_option( 'cache_logged_user' ) ){
							$this->load_natural_js = true;
						}
					}
					else {
						$this->load_natural_js = true;
					}
				}
			}

			if( $this->caching_plugins['w3_total_cache'] ){
				/**
				 * W3 Total Cache
				 */
				if( $config = \W3TC\Dispatcher::config() ){

					if( $config->get_boolean( 'minify.css.enable' ) ) {
						if ( $logged_in ) {
							if( ! $config->get_boolean( 'minify.reject.logged' ) ){
								$this->load_natural_css = true;
							}
						}
						else {
							$this->load_natural_css = true;
						}
					}

					if( $config->get_boolean( 'minify.js.enable' ) ) {
						if ( $logged_in ) {
							if( ! $config->get_boolean( 'minify.reject.logged' ) ){
								$this->load_natural_js = true;
							}
						}
						else {
							$this->load_natural_js = true;
						}
					}
				}
			}

			if( $this->caching_plugins['autoptimize'] ){
				/**
				 * Autoptimize
				 */
				if( 'on' === autoptimizeOptionWrapper::get_option( 'autoptimize_css' ) ){
					if ( $logged_in ) {
						if( 'on' === autoptimizeOptionWrapper::get_option( 'autoptimize_optimize_logged', 'on' ) ) {
							$this->load_natural_css = true;
						}
					}
					else {
						$this->load_natural_css = true;
					}
				}
				if( 'on' === autoptimizeOptionWrapper::get_option( 'autoptimize_js' ) ){
					if ( $logged_in ) {
						if( 'on' === autoptimizeOptionWrapper::get_option( 'autoptimize_optimize_logged', 'on' ) ) {
							$this->load_natural_js = true;
						}
					}
					else {
						$this->load_natural_js = true;
					}
				}
			}

			if( $this->caching_plugins['wp_fastest_cache'] ){
				/**
				 * WP Fastest Cache
				 */
				if( isset($GLOBALS["wp_fastest_cache_options"]) && ($options = $GLOBALS["wp_fastest_cache_options"]) ){
					if ( isset($options->wpFastestCacheMinifyCss) || isset($options->wpFastestCacheCombineCss) ) {
						if ( ! $logged_in ) {
							$this->load_natural_css = true;
						}
					}
					if ( isset($options->wpFastestCacheMinifyJs) || isset($options->wpFastestCacheCombineJs) ) {
						if ( ! $logged_in ) {
							$this->load_natural_js = true;
						}
					}
				}
			}

			if( $this->caching_plugins['litespeed'] ){
				/**
				 * LiteSpeed Cache
				 */
				if( LiteSpeed\Conf::val( LiteSpeed\Base::O_OPTM_CSS_MIN ) || LiteSpeed\Conf::val( LiteSpeed\Base::O_OPTM_CSS_COMB ) ){
					$this->load_natural_css = true;
				}
				if( LiteSpeed\Conf::val( LiteSpeed\Base::O_OPTM_JS_MIN ) || LiteSpeed\Conf::val( LiteSpeed\Base::O_OPTM_JS_COMB ) ){
					$this->load_natural_js = true;
				}

			}

			if( $this->caching_plugins['swift_performance_lite'] ){
				/**
				 * Swift Performance Lite
				 */
				if( Swift_Performance_Lite::check_option('merge-styles',1) ){
					if ( $logged_in ) {
						if( Swift_Performance_Lite::check_option('enable-caching-logged-in-users',1) ) {
							$this->load_natural_css = true;
						}
					}
					else {
						$this->load_natural_css = true;
					}
				}
				if( Swift_Performance_Lite::check_option('merge-scripts',1) ){
					if ( $logged_in ) {
						if( Swift_Performance_Lite::check_option('enable-caching-logged-in-users',1) ) {
							$this->load_natural_js = true;
						}
					}
					else {
						$this->load_natural_js = true;
					}
				}
			}

			if( $this->caching_plugins['swift_performance'] ){
				/**
				 * Swift Performance Pro
				 */
				if( Swift_Performance::check_option('merge-styles',1) ){
					if ( $logged_in ) {
						if( Swift_Performance::check_option('enable-caching-logged-in-users',1) ) {
							$this->load_natural_css = true;
						}
					}
					else {
						$this->load_natural_css = true;
					}
				}
				if( Swift_Performance::check_option('merge-scripts',1) ){
					if ( $logged_in ) {
						if( Swift_Performance::check_option('enable-caching-logged-in-users',1) ) {
							$this->load_natural_js = true;
						}
					}
					else {
						$this->load_natural_js = true;
					}
				}
			}

			if( $this->caching_plugins['sg_optimizer'] ){
				/**
				 * SG Optimizer
				 */
				if( SiteGround_Optimizer\Options\Options::is_enabled( 'siteground_optimizer_optimize_css' ) ||
					SiteGround_Optimizer\Options\Options::is_enabled( 'siteground_optimizer_combine_css' ) ) {
					if ( ! $logged_in ) {
						$this->load_natural_css = true;
					}
				}
				if( SiteGround_Optimizer\Options\Options::is_enabled( 'siteground_optimizer_optimize_js' ) ||
					SiteGround_Optimizer\Options\Options::is_enabled( 'siteground_optimizer_combine_js' ) ) {
					if ( ! $logged_in ) {
						$this->load_natural_js = true;
					}
				}
			}

			if( $this->caching_plugins['breeze'] ){
				/**
				 * Breeze
				 */
				$basic = breeze_get_option( 'basic_settings' );
				$advanced = breeze_get_option( 'advanced_settings' );

				if( ! empty( $basic['breeze-minify-css'] ) || ! empty( $advanced['breeze-group-css'] ) ){
					if ( $logged_in ) {
						if( ! $basic['breeze-disable-admin'] ) {
							$this->load_natural_css = true;
						}
					}
					else {
						$this->load_natural_css = true;
					}
				}

				if( ! empty( $basic['breeze-minify-js'] ) || ! empty( $advanced['breeze-group-js'] ) ){
					if ( $logged_in ) {
						if( ! $basic['breeze-disable-admin'] ) {
							$this->load_natural_js = true;
						}
					}
					else {
						$this->load_natural_js = true;
					}
				}
			}

			if( $this->caching_plugins['wp_optimize'] ){
				/**
				 * WP Optimize
				 */
				if( ($wpo_minify_options = wp_optimize_minify_config()->get()) && $wpo_minify_options['enabled'] ){
					if( $wpo_minify_options['enable_css'] ){
						if ( $logged_in ) {
							if( ! $wpo_minify_options['disable_when_logged_in'] ) {
								$this->load_natural_css = true;
							}
						}
						else {
							$this->load_natural_css = true;
						}
					}
					if( $wpo_minify_options['enable_js'] ){
						if ( $logged_in ) {
							if( ! $wpo_minify_options['disable_when_logged_in'] ) {
								$this->load_natural_js = true;
							}
						}
						else {
							$this->load_natural_js = true;
						}
					}
				}
			}

			if( $this->caching_plugins['hummingbird'] ){
				/**
				 * Hummingbird
				 */
				$options = Hummingbird\Core\Utils::get_module( 'minify' )->get_options();
				if( !$logged_in ){
					if( $options['do_assets']['styles'] ){
						$this->load_natural_css = true;
					}
					if( $options['do_assets']['scripts'] ){
						$this->load_natural_js = true;
					}
				}
			}

			if( $this->caching_plugins['nitropack'] ){
				/**
				 * NitroPack
				 */
				if( get_option('nitropack-enableCompression') == 1 ){
					if( ! $logged_in ){
						$this->load_natural_css = true;
						$this->load_natural_js = true;
					}
				}
			}

			// comet_cache_wipe_cache (Probably not needed)
			// https://wordpress.org/plugins/cachify/ (Probably not needed)
			// rt_nginx_helper (Probably not needed)

		}

		public static function caching_plugins(){
			return [
				'wprocket' => defined('WP_ROCKET_VERSION'),
				'w3_total_cache' => defined( 'W3TC' ) && W3TC,
				'autoptimize' => function_exists( 'autoptimize' ),
				'wp_fastest_cache' => class_exists('WpFastestCache'),
				'litespeed' => function_exists( 'run_litespeed_cache' ),
				'swift_performance_lite' => class_exists( 'Swift_Performance_Lite' ),
				'swift_performance' => class_exists( 'Swift_Performance' ),
				'sg_optimizer' => class_exists( 'SiteGround_Optimizer\Options\Options' ),
				'breeze' => function_exists( 'breeze_get_option' ),
				'wp_optimize' => class_exists( 'WP_Optimize' ),
				'hummingbird' => class_exists( 'Hummingbird\\WP_Hummingbird' ),
				'nitropack' => defined( 'NITROPACK_VERSION' ),
				'page_optimize' => defined( 'PAGE_OPTIMIZE_CACHE_DIR' ),
				'flyingpress' => defined( 'FLYING_PRESS_VERSION' ),
			];
		}

		public static function is_caching_plugin_enabled(){
			return in_array(true, self::caching_plugins(), true);
		}

		function get_registered_styles(){
			return $this->registered_styles;
		}

		public function get_excludes_choices( $has_empty = true ){

			$styles = apply_filters('reycore/assets/excludes_choices', []);

			$list = $has_empty ? [ '' => '- Select -' ] : [];

			foreach ($styles as $key => $style) {

				// grab only the ones that load automatically
				// but exclude several mandatory
				if( isset($style['enqueue']) ){
					$list[$key] = isset($style['desc']) ? $style['desc'] : $key;
				}

			}

			return $list;
		}

		private function __clear_the_data__global(){

			if( ! current_user_can('administrator') ){
				return;
			}

			global $wpdb;

			$meta_keys = [
				self::META_KEY,
				self::META_KEY_LOGGED_IN
			];

			foreach ($meta_keys as $meta_key) {

				$key = $wpdb->esc_like( $meta_key );

				$queries = [
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					"DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s",
					"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s"
				];

				$notices = [];

				foreach($queries as $query){
					if(  $wpdb->query( $wpdb->prepare( $query, $key ) ) === false ){
						$notices['q'] = 'Query not made!';
					}
				}

				if( $this->settings['save_css'] && $this->filesystem ){
					if ( $this->filesystem->rmdir( $this->dir_path, true ) ) {
						$this->filesystem->mkdir( $this->dir_path );
					}
					else {
						$notices['r'] = 'Assets not deleted!';
					}
				}

				if( !empty($notices) ){
					foreach ($notices as $key => $value) {
						self::log( $value );
					}
				}
				else {
					self::log( 'Assets cleaned-up! ' . $meta_key );
				}

				do_action('reycore/assets/cleanup');

			}

			if( function_exists('sg_cachepress_purge_everything') ){
				sg_cachepress_purge_everything();
			}

			return true;
		}

		private function __clear_the_data__indiv($id, $is_post = true){

			if( ! current_user_can('administrator') ){
				return;
			}

			foreach ([
				self::META_KEY,
				self::META_KEY_LOGGED_IN
			] as $meta_key) {
				if( $is_post ){
					delete_post_meta($id, $meta_key);
				}
				else {
					delete_term_meta($id, $meta_key);
				}
			}

			// do_action('reycore/assets/cleanup');
		}

		/**
		 * Cleanup after excludes settings modified
		 *
		 * @since 1.0.0
		 */
		function clear__customize_save_perf__css_exclude( $setting )
		{
			if( ! method_exists($setting, 'value') ){
				return;
			}

			$this->__clear_the_data__global();
		}

		function clear__basic() {
			$this->__clear_the_data__global();
		}

		function clear__acf_save_perf__css_exclude( $post_id ) {

			// continue only if there are additions
			if( empty( get_field('perf__css_exclude', $post_id) ) ){
				return;
			}

			$screen = get_current_screen();
			$is_post = isset($screen->base) && $screen->base === 'post';

			$this->__clear_the_data__indiv($post_id, $is_post);
		}

		function clear_data(){
			if( isset($_REQUEST['clear_assets']) && absint($_REQUEST['clear_assets']) === 2 ){
				$this->__clear_the_data__global();
			}
		}

		/**
		 * Refresh Assets through Ajax
		 *
		 * @since 2.0.0
		 **/
		function clear__asses_admin_bar()
		{
			if( $this->__clear_the_data__global() ){

				// Preload
				if( isset($_REQUEST['href']) && $href = str_replace('?', '', reycore__clean($_REQUEST['href'])) ){

					parse_str($href, $args);

					if( isset($args['type']) && isset($args['id']) && $id = absint($args['id']) ){
						$this->__preload_page($id, $args['type']);
					}
				}

				wp_send_json_success();
			}

			wp_send_json_error();
		}

		public function preload_page( $post_id ){

			// prevent slow save in Elementor
			if( isset($this->has_preloaded_page) ){
				return;
			}

			$this->has_preloaded_page = true;

			if( isset($_REQUEST['action']) && $_REQUEST['action'] === 'update' && isset($_REQUEST['menu']) ){
				return;
			}

			if( ! $post_id ){
				return;
			}

			if( get_post_status($post_id) === 'auto-draft' ){
				return;
			}

			$this->__preload_page($post_id, 'post');
		}

		public function preload_term( $term_id ){

			if( isset($_REQUEST['action']) && $_REQUEST['action'] === 'update' && isset($_REQUEST['menu']) ){
				return;
			}

			if( ! $term_id ){
				return;
			}

			$this->__preload_page($term_id, 'term');
		}

		protected function __preload_page( $id, $type = false ){

			if( ! $type ){
				return;
			}

			if( ! $id ){
				return;
			}

			$url = '';
			$id = absint($id);

			if( $type === 'post' ){
				$url = get_permalink($id);
			}
			elseif( $type === 'term' ){
				$url = get_term_link($id);
			}

			if( $url ){
				wp_remote_get( $url, [
					'timeout' => 40
				]);
			}
		}

		function get_asset_path_by_handler( $handler, $type = 'style' ){

			$wp_assets = $type === 'style' ? wp_styles() : wp_scripts();

			if( ! (isset($wp_assets->registered[ $handler ]) && ($script = $wp_assets->registered[ $handler ])) ){
				return;
			}

			if( ! (isset($script->src) && ($src = $script->src)) ){
				return;
			}

			// check if it's an external file

			if ( 0 === strpos( $src, site_url() ) ) {
				$src_integrity = $src;
			} else {
				$src_integrity = site_url() . $src;
			}

			$path = REY_CORE_URI;
			$dir = REY_CORE_DIR;

			// Deal with theme files

			if( $type === 'style' && isset($this->registered_styles[$handler]['path']) && isset($this->registered_styles[$handler]['dir']) ){
				$path = $this->registered_styles[$handler]['path'];
				$dir = $this->registered_styles[$handler]['dir'];
			}

			if( $type === 'script' && isset($this->registered_scripts[$handler]['path']) && isset($this->registered_scripts[$handler]['dir']) ){
				$path = $this->registered_scripts[$handler]['path'];
				$dir = $this->registered_scripts[$handler]['dir'];
			}

			return str_replace($path, $dir, $src_integrity);
		}

		function get_assets_uri( $assets, $type = 'styles' ){

			$assets_to_return = [];

			if( ! isset($assets[$type]) ){
				return $assets_to_return;
			}

			$wp_assets = $type === 'styles' ? wp_styles() : wp_scripts();

			foreach ($assets[$type] as $key => $handler) {

				if( ! (isset($wp_assets->registered[ $handler ]) && ($script = $wp_assets->registered[ $handler ])) ){
					continue;
				}
				if( ! (isset($script->src) && ($src = $script->src)) ){
					continue;
				}

				if ( 0 === strpos( $src, site_url() ) || 0 === strpos( $src, 'http' ) ) {
					$src_ = $src;
				} else {
					$src_ = site_url() . $src;
				}

				$assets_to_return[$handler] = $src_;
			}

			return $assets_to_return;
		}

		function disable_flying_pages( $status ){

			if( class_exists('WooCommerce') && (is_cart() || is_checkout()) ){
				return false;
			}

			if( $this->elementor_edit() ){
				return false;
			}

			if( isset($this->caching_plugins) && !empty($this->caching_plugins) && ($this->caching_plugins['wprocket'] || $this->caching_plugins['litespeed']) ){
				return false;
			}

			return $status;
		}

		function collect_start(){
			$this->collected_styles = [];
			$this->collected_scripts = [];
		}

		function collect_end( $src = false ){

			$collected = [
				'scripts' => [],
				'styles' => [],
			];

			if( isset($this->collected_scripts) ){
				$collected['scripts'] = array_unique($this->collected_scripts);
			}

			if( isset($this->collected_styles) ){
				$collected['styles'] = array_unique($this->collected_styles);
			}

			if( $src ){
				return [
					'scripts' => $this->get_assets_uri($collected, 'scripts'),
					'styles' => $this->get_assets_uri($collected, 'styles'),
				];
			}

			return $collected;
		}

		function get_assets_paths( $assets = [], $src = true ){

			$collected = [
				'scripts' => [],
				'styles' => [],
			];

			if( isset($assets['scripts']) ){
				$collected['scripts'] = array_unique($assets['scripts']);
			}

			if( isset($assets['styles']) ){
				$collected['styles'] = array_unique($assets['styles']);
			}

			if( $src ){
				return [
					'scripts' => $this->get_assets_uri($collected, 'scripts'),
					'styles' => $this->get_assets_uri($collected, 'styles'),
				];
			}

			return $collected;

		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_AssetsManager
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	function reyCoreAssets(){
		return ReyCore_AssetsManager::getInstance();
	}

	reyCoreAssets();

endif;
