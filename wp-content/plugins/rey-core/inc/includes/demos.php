<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_Demos') ):
	/**
	 * Demo manager
	 *
	 * @since 1.0.0
	 */
	final class ReyCore_Demos
	{
		/**
		 * Instance
		 *
		 * @since 1.0.0
		 */
		private static $_instance = null;

		/**
		 * One click demo import instance
		 *
		 * @since 1.0.0
		 */
		private static $ocdi = null;

		/**
		 * Holds the demos
		 *
		 * @since 1.0.0
		 */
		public $demos = [];

		protected $ocdi_settings = [
			'thumbnails' => [
				'option' => 'rey_ocdi_setting_thumbnails',
				'default' => false,
			]
		];

		/**
		 * Transient name for the demos list
		 *
		 * @since 1.0.0
		 */
		const DEMOS_LIST_OPTION = 'reycore_demos';

		/**
		 * Transient for the current demo content urls that's being imported,
		 * for use later
		 *
		 * @since 1.0.0
		 */
		const CURRENT_DEMO_TRANSIENT = 'demo-data-';

		/**
		 * Menu slug for Import demo data page
		 *
		 * @since 1.0.0
		 */
		const MENU_SLUG = 'reycore-demo-import';

		/**
		 * Holds Rey's plugins list.
		 *
		 * @since 1.0.0
		 */
		private $plugins = [];

		private function __construct()
		{
			if( !is_admin() ){
				return;
			}

			if( ! (
				reycore__theme_active() && reycore__get_purchase_code() &&
				class_exists('OCDI_Plugin') && class_exists('\OCDI\OneClickDemoImport') ) ){
				return;
			}

			self::$ocdi = OCDI\OneClickDemoImport::get_instance();

			add_action('after_setup_theme', [$this, 'init_hooks'], 0);
			remove_action( 'admin_menu', [ self::$ocdi, 'create_plugin_page'] );
			add_action( 'admin_menu', [ self::$ocdi, 'create_plugin_page'], 100 ); // move Import Demos links much lower.
			add_action( 'pt-ocdi/before_content_import_execution', [$this, 'before_all_import_execution'], 0 );
			add_action( 'wp_ajax_rey_install_plugin_dependencies', [$this, 'install_plugin_dependencies'] );
			add_action( 'wp_ajax_rey_refresh_demos', [$this, 'ajax_refresh_demos'] );
			add_action( 'after_setup_theme', [$this, 'remove_redux_import'], 11 ); // remove redux (used by Rey Config)
			add_action( 'pt-ocdi/after_content_import_execution', [ $this, 'config_import_fake_redux' ], 40 );
			add_action( 'pt-ocdi/after_import', [$this, 'after_import'], 10 );
			// add_action( 'pt-ocdi/after_all_import_execution', [$this, 'after_all_import_execution'], 100, 3 );
			add_action( 'rey/flush_cache_after_updates', [ $this, 'refresh_demos_after_updates' ] );
			add_action( 'wp_ajax_change_ocdi_setting', [ $this, 'change_ocdi_setting' ] );
			add_filter( 'pt-ocdi/regenerate_thumbnails_in_content_import', [$this, 'disable_ocdi_thumbnail_generation'] );
			add_action( 'reycore/ocdi/after_buttons', [$this, 'add_notices']);
			add_filter( 'woocommerce_create_pages', [$this, 'disable_wc_page_creation'] );

		}

		function init_hooks(){

			$this->get_plugins();

			add_filter( 'pt-ocdi/plugin_page_setup', [$this, 'ocdi_setup'] );
			add_filter( 'pt-ocdi/import_files', [$this, 'setup_demos'] );
			add_filter( 'pt-ocdi/confirmation_dialog_options', [$this, 'confirmation_dialog_options']);
			add_filter( 'pt-ocdi/pre_download_import_files', [$this, 'get_download_urls'] );
			add_action( 'wp_print_scripts', [ $this, 'remove_main_js' ]);
			add_action( 'admin_enqueue_scripts', [ $this, 'add_custom_main_js' ]);
			add_filter( 'pt-ocdi/plugin_page_display_callback_function', [ $this, 'display_plugin_page_callback' ] );
			add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );
			add_filter( 'reycore/admin_script_params', [$this, 'filter_admin_script_params'] );

			// enable logging everything
			// in dev mode
			if( defined('REY_DEV_MODE') ) {
				add_filter( 'pt-ocdi/logger_options', function($options){
					$options['logger_min_level'] = 'debug';
					return $options;
				} );
			}
		}

		/**
		 * OCDI Setup
		 *
		 * @since 1.0.0
		 */
		function ocdi_setup( $default_settings ) {

			if( $dashboard_id = reycore__get_dashboard_page_id() ){
				$default_settings['parent_slug'] = $dashboard_id;
			}
			$default_settings['page_title']  = esc_html__( 'Import Demos' , 'rey-core' );
			$default_settings['menu_title']  = esc_html__( 'Import Demo Data' , 'rey-core' );
			$default_settings['capability']  = 'administrator';
			$default_settings['menu_slug']   = self::MENU_SLUG;

			return $default_settings;
		}

		/**
		 * Override OCDI plugin page callback.
		 */
		public function display_plugin_page_callback() {
			return [$this, 'display_plugin_page'];
		}

		/**
		 * Plugin page display.
		 * Output (HTML) is in another file.
		 */
		public function display_plugin_page() {
			require_once REY_CORE_DIR . 'inc/includes/demos-admin-page.php';
		}

		/**
		 * Disable main.js from OCDI,
		 * because we'll load our own one.
		 *
		 * @since 1.0.0
		 */
		public function remove_main_js(){
			wp_dequeue_script( 'ocdi-main-js' );
		}

		/**
		 * Add our own version of main.js from OCDI
		 *
		 * @since 1.0.0
		 */
		public function add_custom_main_js( $hook ){

			// Enqueue the scripts only on the plugin page.
			if (
				strpos( $hook, self::MENU_SLUG ) !== false ||
				( 'admin.php' === $hook && self::MENU_SLUG === esc_attr( $_GET['import'] ) )
			) {

				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_style( 'wp-jquery-ui-dialog' );

				wp_enqueue_script( 'rey-ocdi-main-js', REY_CORE_URI . 'assets/js/lib/ocdi.js' , array( 'jquery', 'jquery-ui-dialog' ), REY_CORE_VERSION );

				// Get theme data.
				$theme = wp_get_theme();

				wp_localize_script( 'rey-ocdi-main-js', 'ocdi',
					array(
						'ajax_url'         => admin_url( 'admin-ajax.php' ),
						'ajax_nonce'       => wp_create_nonce( 'ocdi-ajax-verification' ),
						'import_files'     => self::$ocdi->import_files,
						'wp_customize_on'  => apply_filters( 'pt-ocdi/enable_wp_customize_save_hooks', false ),
						'import_popup'     => apply_filters( 'pt-ocdi/enable_grid_layout_import_popup_confirmation', true ),
						'theme_screenshot' => $theme->get_screenshot(),
						'texts'            => array(
							'missing_preview_image' => esc_html__( 'No preview image defined for this import.', 'rey-core' ),
							'dialog_title'          => esc_html__( 'Are you sure?', 'rey-core' ),
							'dialog_no'             => esc_html__( 'Cancel', 'rey-core' ),
							'dialog_yes'            => esc_html__( 'Yes, import!', 'rey-core' ),
							'selected_import_title' => esc_html__( 'Selected demo import:', 'rey-core' ),
						),
						'dialog_options' => apply_filters( 'pt-ocdi/confirmation_dialog_options', array() )
					)
				);
			}
		}

		/**
		 * Filter admin script params and append OCDI strings
		 *
		 * @since 1.0.0
		 */
		function filter_admin_script_params($params){

			$params['ocdi_strings'] = [
				'installing_plugins_text' => esc_html__('Installing plugins. Please don\'t close or refresh the page.', 'rey-core'),
				'finished_installing_plugins' => esc_html__('Finished installing plugins, switching to content import.', 'rey-core'),
				'something_went_wrong' => esc_html__('Something went wrong, please retry!', 'rey-core'),
				'seek_help' => sprintf(
					__('It seems that something went wrong. Please check the <a href="%1$s" target="_blank">generated logs</a>, or visit the <a href="%2$s" target="_blank">troubleshooting guide</a>. If you can\'t find a solution, <a href="%3$s" target="_blank">drop me a message</a> describing the problem in detail, and sharing the appropriate credentials (such as an administrator account, or FTP details as well, so i can investigate the problem).', 'rey-core'),
					esc_url_raw( admin_url('upload.php?search=.txt') ),
					'https://support.reytheme.com/kb/importing-demos-common-errors-problems/',
					'https://support.reytheme.com/new/'
					),
				'visit_site' => sprintf(
					__('You can now <strong><a href="%s" target="_blank">visit your site in the frontend</a></strong> and preview the import changes.', 'rey-core'),
					esc_url_raw( site_url() )
					),
				'title_importing' => esc_html__('Importing..', 'rey-core'),
				'title_done' => esc_html__('Done!', 'rey-core'),
			];

			return $params;
		}

		/**
		 * Get Rey's Plugin class instance
		 *
		 * @since 1.0.0
		 */
		function rey_plugins() {
			if( class_exists('ReyTheme_Plugins') ){
				return ReyTheme_Plugins::getInstance();
			}
			return false;
		}

		/**
		 * Get Rey's plugins
		 *
		 * @since 1.0.0
		 */
		public function get_plugins(){

			$plugins = [];

			if( $this->rey_plugins() ) {
				$plugins = $this->rey_plugins()->get_plugins();
			}

			$this->plugins = $plugins;

			return true;
		}

		/**
		 * Add presigned demo download urls.
		 * Filtered on `pt-ocdi/pre_download_import_files` custom hook.
		 *
		 * This will get the expiring demo data link urls.
		 *
		 * @since 1.0.0
		 */
		function get_download_urls( $demo_data ){

			if( isset($demo_data['slug']) && reycore__get_purchase_code() ) {

				if( function_exists('rey__maybe_disable_obj_cache') ){
					rey__maybe_disable_obj_cache();
				}

				$stored_data = get_site_transient( self::CURRENT_DEMO_TRANSIENT . $demo_data['slug'] );

				if( !$stored_data ) {

					$stored_data = [];

					$request = ReyTheme_API::getInstance()->get_demo_data( $demo_data['slug'] );

					if ( !is_wp_error( $request ) )
					{
						if ( isset($request['data']) && is_array($request['data']) && !empty($request['data']))  {
							$stored_data = $request['data'];
						}
						set_site_transient( self::CURRENT_DEMO_TRANSIENT . $demo_data['slug'], $stored_data, 10 * MINUTE_IN_SECONDS );
					}
					else {
						rey__log_error( 'err007', $request );
					}
				}

				if( isset($stored_data['import_file_url']) && $import_file_url = $stored_data['import_file_url'] ){
					$demo_data['import_file_url'] = $import_file_url;
				}
				if( isset($stored_data['import_widget_file_url']) && $import_widget_file_url = $stored_data['import_widget_file_url'] ){
					$demo_data['import_widget_file_url'] = $import_widget_file_url;
				}
				if( isset($stored_data['import_customizer_file_url']) && $import_customizer_file_url = $stored_data['import_customizer_file_url'] ){
					$demo_data['import_customizer_file_url'] = $import_customizer_file_url;
				}
			}

			return $demo_data;
		}

		/**
		 * Install plugin dependencies when importing demos
		 *
		 * @since 1.0.0
		 */
		public function install_plugin_dependencies()
		{
			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if( isset($_POST['page']) && $_POST['page'] === self::MENU_SLUG ){

				// Check if lplugins can be installed
				if( ! $this->rey_plugins() && ! current_user_can('install_plugins') ){
					wp_send_json_error( esc_html__('You\'re not allowed to install plugins!', 'rey-core'));
				}

				// Check if plugins exists
				if( isset($_POST['plugins']) && !empty( $_POST['plugins'] ) ){
					$dependency_list = array_map( 'sanitize_text_field', $_POST['plugins'] );
				}
				else {
					wp_send_json_error( esc_html__('No plugins to install.', 'rey-core'));
				}

				if( function_exists('rey__maybe_disable_obj_cache') ){
					rey__maybe_disable_obj_cache();
				}

				$reyTgmpa = Rey_TGM_Plugin_Activation::get_instance();

				$dependency_plugins = array_intersect_key( $this->plugins, array_flip($dependency_list));
				$plugin = $this->rey_plugins()->get_plugin_for_action( $dependency_plugins );

				$did_plugin = false;

				if( isset($plugin['file_path']) ){
					// do installation
					if( ! $plugin['installed'] ){

						$_GET['plugin'] = $plugin['slug'];

						$_GET['tgmpa-install'] = 'install-plugin'; // BC 2.1-
						$bc_nonce = wp_create_nonce( 'tgmpa-install' ); // BC 2.1-
						$_REQUEST['tgmpa-nonce'] = $bc_nonce; // BC 2.1-

						$_GET['reybase-tgmpa-install'] = 'install-plugin';
						$nonce = wp_create_nonce( 'reybase-tgmpa-install' );
						$_REQUEST['reybase-tgmpa-nonce'] = $nonce;

						if( $reyTgmpa->install_plugin() !== false ){
							$did_plugin = $plugin['slug'];
						}
					}
					// do activation
					else {
						if( ! $plugin['active'] )
						{
							if( $this->rey_plugins()->activate_plugin( $plugin['file_path'] ) !== false ){
								$did_plugin = $plugin['slug'];
							}
						}
					}
				}

				$this->disable_plugin_scripts();

				wp_send_json_success( $did_plugin );
			}
			else {
				wp_send_json_error( esc_html__('Submission URL not matching the page URL.', 'rey') );
			}
		}

		/**
		 * Displays the plugin dependecies in the import modal
		 *
		 * @since 1.0.0
		 */
		private function import_notice_plugin_dependencies( $dependencies, $demo ){

			if( empty($dependencies) ){
				return '';
			}

			$before = '';

			if( ($already_imported = get_option('rey_ocdi_imported_demo', [])) && isset($already_imported['name']) ){
				$before = sprintf(
					__( '<p class="rey-adminNotice --error">Heads up! It seems you already imported %s demo. <br><br>"One click demo import" plugin recommends importing a demo on <strong>a clean WordPress instance</strong> so re-importing a demo over, might cause problems. Try using <a href="%s" target="_blank">WP Reset</a> plugin to reset your WordPress instance before proceeding with a new demo import. <br><br>If you just want to import a specific page or section, please use <a href="%s" target="_blank">Rey\'s Template Library</a>.</p>', 'rey-core' ),
					$already_imported['name'],
					'https://wordpress.org/plugins/wp-reset/',
					'https://support.reytheme.com/kb/template-library-faqs/'
				);
			}

			$plugin_list = '';

			foreach( $dependencies as $dep )
			{
				if( isset($this->plugins[$dep]) )
				{
					$is_installed = $this->plugins[$dep]['installed'];
					$is_active = $this->plugins[$dep]['active'];
					$is_required = $this->plugins[$dep]['required'];

					if( ! $is_active )
					{
						$plugin_list .= sprintf('<li class="" data-slug="%s">', $this->plugins[$dep]['slug']);
						$plugin_list .= '<span class="rey-spinnerIcon"></span>';

						$plugin_list .= sprintf(
							'<input type="checkbox" name="import-demo-%1$s" id="import-demo-%1$s" value="%3$s" checked %2$s >',
							esc_attr($dep . '-' . $demo),
							$is_required ? 'disabled' : '',
							esc_attr($dep)
						);

						$label_content = $this->plugins[$dep]['name'];
						// $label_content .= '<span>'. $this->plugins[$dep]['name'] .'</span>';
						$label_content .= $is_required ? __('<em>[required]</em>', 'rey-core') : '';
						$label_content .= ! $is_active && $is_installed ? __('<em>[inactive]</em>', 'rey-core') : '';

						if( isset($this->plugins[$dep]['desc']) ){
							$label_content .= sprintf('<small>%s</small>', $this->plugins[$dep]['desc']);
						}

						$plugin_list .= sprintf(
							'<label for="import-demo-%s">%s</label>',
							esc_attr($dep . '-' . $demo),
							$label_content
						);


						$plugin_list .= '</li>';
					}
				}
			}

			if( empty($plugin_list) ){
				return $before . __( '<p>All dependency plugins are installed and active! Click on "Yes, import!" to start importing the content and settings.</p>', 'rey-core' );
			}

			$output = $before;
			$output .= __( '<p>The following plugin(s) are used for various purposes in this demo. Deselect if you don\'t want a specific plugin to be installed</p>', 'rey-core' );
			$output .= '<ul class="rey-pluginDeps" id="js-plugin-dep--' . esc_attr($demo) . '">';
			$output .= $plugin_list;
			$output .= '</ul>';

			return $output;
		}


		/**
		 * Removes `redux_import`
		 *
		 * @since 1.0.0
		 */
		function remove_redux_import(){
			if( wp_doing_ajax() ){
				remove_all_actions( 'pt-ocdi/after_content_import_execution', 30 );
			}
		}

		/**
		 * Execute Rey's config import (based on Redux field). Manual mode.
		 *
		 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
		 * @param array $import_files          The filtered import files defined in `pt-ocdi/import_files` filter.
		 * @param int   $selected_index        Selected index of import.
		 */
		public function config_import_fake_redux( $selected_import_files )
		{
			if ( ! empty( $selected_import_files['redux'] ) &&
				isset($selected_import_files['redux'][0]['file_path']) && $config_file = $selected_import_files['redux'][0]['file_path'] ) {
				$this->import_config($config_file, false, false);
			}
		}

		/**
		 * After import is done
		 *
		 * @since 1.0.0
		 */
		function after_import( $demo ){

			if( isset($_GET['import-mode']) && $_GET['import-mode'] === 'manual' ){
				return;
			}

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$stored_data = get_site_transient( self::CURRENT_DEMO_TRANSIENT . $demo['slug'] );

			if( $stored_data )
			{
				// Import Config
				if( isset( $stored_data['import_config'] ) && $import_config_url = $stored_data['import_config'] ) {
					$this->import_config($import_config_url, $demo['slug']);
				}

				// Import Revolution Slider
				if( isset( $stored_data['import_revolution'] ) && $import_revolution_url = $stored_data['import_revolution'] ) {
					$this->import_revolution($import_revolution_url, $demo);
				}
			}
			else {
				rey__log_error( 'err019', __('Import Config link is missing. Probably transient has expired.', 'rey-core') );
			}

			delete_site_transient( self::CURRENT_DEMO_TRANSIENT . $demo['slug'] );

			$this->after_all_import_execution($demo);
		}

		/**
		 * Imports the config.json that holds theme options,
		 * general options, pages, url replacements.
		 *
		 * @since 1.0.0
		 */
		private function import_config( $import_config_url, $demo_slug = 'unspecified', $download = true )
		{

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$this->disable_plugin_scripts();

			if( $download )
			{
				$downloader = new OCDI\Downloader();

				$file_path = $downloader->download_file(
					$import_config_url,
					sprintf('demo-%s-config-import-file-%s.json',
						$demo_slug,
						date( 'Y-m-d__H-i-s' )
					)
				);
			}
			else {
				// we'll use the local file
				$file_path = $import_config_url;
			}

			if ( is_wp_error( $file_path ) ) {
				rey__log_error( 'err011', $file_path );
			}
			else {
				$file_raw  = OCDI\Helpers::data_from_file( $file_path );

				if ( is_wp_error( $file_raw ) ) {
					rey__log_error( 'err012', $file_raw );
				}
				else {

					OCDI\Helpers::append_to_file(
						__( 'The config file was successfully parsed!', 'rey-core' ),
						self::$ocdi->log_file_path,
						esc_html__( 'Importing Theme Config' , 'rey-core' )
					);

					$config = json_decode($file_raw, true);

					$was_configured = [];

					// import Rey theme options
					if( isset($config['rey-theme']) ){
						foreach( $config['rey-theme'] as $option => $value ){
							update_field($option, reycore__clean($value), REY_CORE_THEME_NAME);
						}
						$was_configured[] = 'rey-theme';
					}

					// import options
					if( isset($config['options']) ){
						foreach( $config['options'] as $option => $value ){
							update_option($option, reycore__clean($value));
						}
						$was_configured[] = 'options';
					}

					if( class_exists('ReyCore_MegaMenus') && isset($config['rey_mega_menus']) && ! empty( $config['rey_mega_menus'] ) ) {

						$mega__menus = [];

						foreach( $config['rey_mega_menus'] as $value ){
							$menu_tax = get_term_by( 'slug', reycore__clean($value), 'nav_menu' );
							if( isset($menu_tax->term_id) ){
								$mega__menus[] = $menu_tax->term_id;
							}
						}

						if( ! empty( $mega__menus ) ){
							update_option(ReyCore_MegaMenus::SUPPORTED_MENUS, $mega__menus);
						}

						$was_configured[] = 'rey_mega_menus';
					}

					// assign pages
					if( isset($config['pages']) ){
						foreach( $config['pages'] as $option => $value ){

							if( !is_array($value) )
							{
								$page = get_page_by_title( reycore__clean($value) );
								if(isset( $page ) && $page->ID) {
									update_option($option, $page->ID);
								}
							}
							else {

								$initial_field = get_option( $option );

								foreach ($value as $k => $v) {
									$page = get_page_by_title( reycore__clean($v) );

									if( isset( $page ) && $page->ID ) {
										// set the page id do the sub item
										$initial_field[ $k ] = $page->ID;
										// update option
										update_option($option, $initial_field);
									}
								}
							}
						}
						$was_configured[] = 'pages';
					}

					// assign pages
					if( isset($config['remove_pages']) ){
						foreach( $config['remove_pages'] as $option => $value ){
							$page = get_page_by_title( reycore__clean($value) );
							if(isset( $page ) && $page->ID) {
								wp_delete_post( $page->ID );
							}
						}
						$was_configured[] = 'remove_pages';
					}

					// Assign menu navs
					if( isset($config['nav']) )
					{
						foreach( $config['nav'] as $menu_slug => $menu )
						{
							$the_menu = get_term_by( 'name', reycore__clean($menu), 'nav_menu' );

							if( $the_menu && $the_menu->term_id ){
								set_theme_mod( 'nav_menu_locations', [
									$menu_slug => $the_menu->term_id,
								] );
							}
						}
						$was_configured[] = 'nav';
					}

					// replace upload paths
					if( isset($config['upload-path']) && $upload_path = $config['upload-path'] )
					{
						$uploads_dir = wp_get_upload_dir();
						$this->elementor_replace_urls( reycore__clean($upload_path), $uploads_dir['baseurl'] );
						$this->post_content_replace_urls( reycore__clean($upload_path), $uploads_dir['baseurl'] );
						$was_configured[] = 'upload-path';
					}

					// replace urls
					if( isset($config['source-path']) && $path = $config['source-path'] )
					{
						$this->elementor_replace_urls( reycore__clean($path), get_site_url(), true );
						$this->post_content_replace_urls( reycore__clean($path), get_site_url() );
						$was_configured[] = 'source-path';
					}

					// WooCommerce attribute types
					if( isset($config['wc_attribute_types']) && $attr = $config['wc_attribute_types'] )
					{
						global $wpdb;
						$table_name = $wpdb->prefix . 'woocommerce_attribute_taxonomies';

						foreach ($attr as $key => $value) {
							$query = $wpdb->prepare(
								"UPDATE `$table_name` SET `attribute_type` = '%s' WHERE `$table_name`.`attribute_name` = '%s' AND `$table_name`.`attribute_type` = 'select'; ", reycore__clean( $value['attribute_type'] ), reycore__clean( $value['attribute_name'] )
							);
							$wpdb->query( $query );
						}

						do_action('reycore/demo_import/attributes');

						$was_configured[] = 'woocommerce-attributes';
					}

					// disabled elements
					if( isset($config['elements_manager']) && class_exists('ReyCore_WidgetsManager') ){
						update_option(ReyCore_WidgetsManager::DB_OPTION, reycore__clean( $config['elements_manager'] ));
						$was_configured[] = 'elements_manager';
					}

					foreach ([
						'gs_header' => 'header_layout_type',
						'gs_footer' => 'footer_layout_type',
						'gs_sticky_top' => 'top_sticky_gs',
					] as $gs => $opt) {

						// set header
						if( isset($config[$gs]) && $gs_to_set = $config[$gs] ){

							if( $gs_to_set_id = reycore__get_post_id_from_slug($gs_to_set, 'rey-global-sections') ){
								set_theme_mod($opt, $gs_to_set_id);
							}

							$was_configured[] = $gs;
						}
					}


					if( !empty($was_configured) ){
						OCDI\Helpers::append_to_file(
							sprintf( __( 'The processes: %s were added.', 'rey-core' ), implode(', ', $was_configured) ),
							self::$ocdi->log_file_path,
							esc_html__( 'Importing Theme Config' , 'rey-core' )
						);
					}

					// WooCommerce
					delete_option( '_wc_needs_pages' );
					delete_transient( '_wc_activation_redirect' );
					// Elementor
					delete_transient( 'elementor_activation_redirect' );
				}
			}
		}


		/**
		 * Imports revolution sliders.
		 *
		 * @since 1.0.0
		 */
		private function import_revolution( $import_revolution_url, $demo ){

			if( ! class_exists('RevSliderSliderImport') ){
				return false;
			}

			$downloader = new OCDI\Downloader();

			$file_path = $downloader->download_file(
				$import_revolution_url,
				sprintf('demo-%s-revolution-slider-%s.zip',
					$demo['slug'],
					date( 'Y-m-d__H-i-s' )
				)
			);

			if ( is_wp_error( $file_path ) ) {
				rey__log_error( 'err011', $file_path );
			}
			else {

				OCDI\Helpers::append_to_file(
					__( 'The Revolution Slider file was successfully downloaded!', 'rey-core' ),
					self::$ocdi->log_file_path,
					esc_html__( 'Importing Revolution Slider' , 'rey-core' )
				);

				$i = new RevSliderSliderImport();
				$r = $i->import_slider(true, $file_path);

				if( isset($r['success']) && $r['success'] === true ){
					OCDI\Helpers::append_to_file(
						__( 'The Revolution Slider\'s bundled slider has been imported.', 'rey-core'  ),
						self::$ocdi->log_file_path,
						esc_html__( 'Importing Theme Config' , 'rey-core' )
					);
				}
			}
		}

		/**
		 * Replace old urls with new ones in
		 * Elementor meta data.
		 *
		 * @since 1.0.0
		 */
		private function elementor_replace_urls( $from, $to, $flush = false ) {

			$from = esc_url_raw( $from );
			$to = esc_url_raw( $to );

			if( $from === $to || ! ( filter_var( $from, FILTER_VALIDATE_URL ) && filter_var( $to, FILTER_VALIDATE_URL ) ) ) {
				rey__log_error( 'err013', __('URLs must be different and valid.', 'rey-core') );
				return;
			}

			global $wpdb;

			// @codingStandardsIgnoreStart cannot use `$wpdb->prepare` because it remove's the backslashes
			$data_query = "UPDATE {$wpdb->postmeta} " .
			"SET `meta_value` = REPLACE(`meta_value`, '" . str_replace( '/', '\\\/', $from ) . "', '" . str_replace( '/', '\\\/', $to ) . "') " .
			"WHERE `meta_key` = '_elementor_data' AND `meta_value` LIKE '[%';";

			if ( false === $wpdb->query( $data_query ) ) {
				rey__log_error( 'err014', __('Elementor URLs replacement on `_elementor_data` has errors.') );
			}

			$page_settings_query = $wpdb->prepare( "UPDATE {$wpdb->postmeta} " .
			"SET `meta_value` = REPLACE(`meta_value`, %s, %s) " .
			"WHERE `meta_key` = '_elementor_page_settings' AND `meta_value` LIKE %s", [ $from, $to, '%' . $from . '%' ] );

			if ( false === $wpdb->query( $page_settings_query ) ) {
				rey__log_error( 'err015', __('Elementor URLs replacement on `_elementor_page_settings` has errors.') );
			}

			if( $flush && class_exists('\Elementor\Plugin') ){
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
		}

		/**
		 * Replace old urls with new ones in
		 * post content and guid columns.
		 *
		 * @since 1.0.0
		 */
		private function post_content_replace_urls( $from, $to ) {

			$from = esc_url_raw( $from );
			$to = esc_url_raw( $to );

			if( $from === $to || ! ( filter_var( $from, FILTER_VALIDATE_URL ) && filter_var( $to, FILTER_VALIDATE_URL ) ) ) {
				rey__log_error( 'err016', __('URLs must be different and valid.', 'rey-core') );
				return;
			}

			global $wpdb;

			// post content
			$post_content_query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET `post_content` = REPLACE(`post_content`, %s, %s) ", $from, $to );

			if ( false === $wpdb->query( $post_content_query ) ) {
				rey__log_error( 'err017', __('URLs replacement on `post_content` has errors.') );
			}

			// guid
			$guid_query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET `guid` = REPLACE(`guid`, %s, %s) ", $from, $to );

			if ( false === $wpdb->query( $guid_query ) ) {
				rey__log_error( 'err018', __('URLs replacement on `guid` has errors.') );
			}


		}

		/**
		 * Adds the demos list into OCDI
		 *
		 * @since 1.0.0
		 */
		function setup_demos() {

			$the_demos  = [];

			$this->get_demos();

			if( empty( $this->demos ) ){
				return $the_demos;
			}

			if( isset($this->demos['items']) ){

				foreach( $this->demos['items'] as $k => $demo ){

					if( $demo['visibility'] === 'private' && ! defined('REY_API_KEY') ){
						continue;
					}

					$the_demo = [
						'import_file_name'           => $demo['name'],
						'import_preview_image_url'   => $demo['preview_img'],
						'preview_url'                => $demo['url'],
						'plugin_dependencies'        => $demo['dependencies'],
						'slug'                       => $demo['slug'],
						'visibility'                 => $demo['visibility'],
						'import_notice'              => $this->import_notice_plugin_dependencies( $demo['dependencies'], $demo['slug'] ),
						'import_file_url'            => '',
						'import_widget_file_url'     => '',
						'import_customizer_file_url' => '',
					];

					if( isset($demo['categories']) && is_array($demo['categories']) ){
						$the_demo['categories'] = array_map('ucfirst', $demo['categories']);
					}

					$the_demos[$k] = $the_demo;
				}
			}


			return $the_demos;
		}

		/**
		 * Customizes modal dialog
		 *
		 * @since 1.0.0
		 */
		function confirmation_dialog_options ( $options ) {
			return array_merge( $options, array(
				'width'       => 460,
				'dialogClass' => 'wp-dialog wp-dialog--rey',
				'resizable'   => false,
				'height'      => 'auto',
				'modal'       => true,
			) );
		}

		/**
		 * Get & set a list of required demos.
		 *
		 * @since 1.0.0
		 */
		public function get_demos()
		{
			if( class_exists('ReyTheme_API') && reycore__get_purchase_code() )
			{
				if( function_exists('rey__maybe_disable_obj_cache') ){
					rey__maybe_disable_obj_cache();
				}

				$demos = get_site_option( self::DEMOS_LIST_OPTION );

				if( ! $demos )
				{
					$request = ReyTheme_API::getInstance()->get_demos();

					if ( is_wp_error( $request ) )
					{
						$demos = [];
						rey__log_error( 'err005', $request );
					}
					else
					{
						if ( isset($request['data']) && is_array($request['data']) && !empty($request['data']))  {
							$demos = array_map('reycore__clean', $request['data'] );
						}
					}
					update_site_option( self::DEMOS_LIST_OPTION, $demos, DAY_IN_SECONDS );
				}

				$this->demos = $demos;
			}
		}

		/**
		 * Ajax Refresh demos & plugin lists
		 *
		 * @since 1.0.0
		 */
		public function ajax_refresh_demos(){

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( 'invalid_nonce' );
			}

			$this->refresh_demos_plugins();

			wp_send_json_success();
		}

		/**
		 * Refresh demos & plugin lists after updates
		 *
		 * @since 1.0.0
		 */
		public function refresh_demos_after_updates(){
			$this->refresh_demos_plugins();
		}

		/**
		 * Refresh demos & plugin lists
		 *
		 * @since 1.0.0
		 */
		private function refresh_demos_plugins(){
			// set all plugins
			if( $this->rey_plugins() ){
				$this->rey_plugins()->refresh_plugins();
			}

			// get demos
			delete_site_option( self::DEMOS_LIST_OPTION );
			$this->get_demos();

			// remove any download urls transients (if previously failed)
			foreach( $this->demos as $demo ){
				if( isset($demo['slug']) ){
					delete_site_transient( self::CURRENT_DEMO_TRANSIENT . $demo['slug'] );
				}
			}

			return true;
		}

		/**
		 * Add a start time transient
		 *
		 * @since 1.0.0
		 */
		function before_all_import_execution(){

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			set_transient( 'rey_import_demo_start', microtime(true), HOUR_IN_SECONDS );
		}


		/**
		 * Log import endtime
		 *
		 * @since 1.0.0
		 */
		function after_all_import_execution( $demo ){

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$start = get_transient('rey_import_demo_start');

			OCDI\Helpers::append_to_file(
				sprintf(__( 'Time elapsed to import - %s .', 'rey-core' ), reycore__format_period( microtime(true) - $start ) ),
				self::$ocdi->log_file_path,
				esc_html__( 'End' , 'rey-core' )
			);

			delete_transient('rey_import_demo_start');

			if( isset($demo) && isset($demo['slug']) ){
				update_option('rey_ocdi_imported_demo', [
					'slug' => $demo['slug'],
					'name' => $demo['import_file_name'],
				]);
			}

			set_theme_mod('perf__disable_wpblock', false);
			set_theme_mod('perf__disable_wcblock', false);
			set_theme_mod('perf__enable_flying_scripts', false);
			set_theme_mod('perf__preload_assets', []);

			do_action('rey/flush_cache_after_updates');

			// regenerate lookup tables
			if( function_exists('wc_update_product_lookup_tables_is_running') && function_exists('wc_update_product_lookup_tables') ){
				if ( ! wc_update_product_lookup_tables_is_running() ) {
					wc_update_product_lookup_tables();
				}
			}
		}

		/**
		 * Disable plugin scripts, activations redirectes, etc.
		 *
		 * @since 1.0.0
		 */
		private function disable_plugin_scripts(){

			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			//disable woocommerce setup wizard
			add_filter('woocommerce_create_pages', '__return_empty_array');
			add_filter('woocommerce_enable_setup_wizard', '__return_false');
			// deactivate redirect in Elementor
			delete_transient( 'elementor_activation_redirect' );
			// deactivate redirect in WooCommerce Variation Swatches
			delete_option( 'activate-woo-variation-swatches' );

			// disable wishlist wizard
			add_filter( 'tinvwl_enable_wizard', '__return_false', 10);
			add_filter( 'tinvwl_prevent_automatic_wizard_redirect', '__return_true', 10);

			// disables error in WP Store Location
			if( class_exists('WPSL_Settings') ){
				update_option('wpsl_valid_server_key', 1);
			}

			// Fixes a Fatal error:
			// PHP Fatal error:  Call to a member function set_rating_counts() on boolean in woocommerce/includes/class-wc-comments.php on line 200
			if( class_exists('WC_Comments') ){
				remove_action( 'wp_update_comment_count', 'WC_Comments::clear_transients' );
			}
		}

		public function check_ocdi_setting( $setting ){
			if( array_key_exists($setting, $this->ocdi_settings) ){
				return get_option( $this->ocdi_settings[$setting]['option'], $this->ocdi_settings[$setting]['default'] );
			}
		}

		public function change_ocdi_setting(){

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey-core') );
			}
			if( isset($_POST['status']) && isset($_POST['setting']) ){

				$setting = sanitize_text_field($_POST['setting']);

				if( ! array_key_exists($setting, $this->ocdi_settings) ){
					wp_send_json_error();
				}

				$status = rest_sanitize_boolean($_POST['status']);

				if( update_option( $this->ocdi_settings[$setting]['option'], $status ) ){
					wp_send_json_success($status);
				}
			}

			wp_send_json_error();
		}

		public function disable_ocdi_thumbnail_generation( $status ){

			if( get_option( $this->ocdi_settings['thumbnails']['option'], $this->ocdi_settings['thumbnails']['default'] ) === false ) {
				return false;
			}

			return $status;
		}

		public function disable_wc_page_creation( $pages ){

			if( isset($_REQUEST['action']) && $_REQUEST['action'] === 'rey_install_plugin_dependencies' &&
				isset($_REQUEST['page']) && $_REQUEST['page'] === 'reycore-demo-import' ){
				return [];
			}

			return $pages;
		}

		function add_notices(){

			$min_version = '7.2.0';

			if ( version_compare( phpversion(), $min_version, '<=' ) ) {
				echo '<div class="rey-adminNotice --error">';
				printf( __( 'The <strong>One Click Demo Import</strong> plugin requires <strong>PHP %2$s+</strong> to run properly. Please contact your hosting company and ask them to update the PHP version of your site to at least PHP %2$s.<br> Your current version of PHP is <strong>%1$s</strong>.', 'rey-core' ), phpversion(), $min_version );
				echo '</div>';
			}
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_Demos
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Demos::getInstance();

endif;
