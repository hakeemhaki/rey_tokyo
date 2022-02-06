<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once REY_THEME_DIR . '/inc/libs/class-tgm-plugin-activation.php';

if( !class_exists('ReyTheme_Plugins') ):
	/**
	 * Manager for Rey's plugins
	 * and wrapper for TGMPA Plugin installer
	 *
	 * @since 1.0.0
	 */
	class ReyTheme_Plugins
	{
		private static $_instance = null;

		private $plugins = [];

		const PLUGIN_LIST_OPTION = 'rey_plugins_list';

		/**
		 * TGMPA instance.
		 *
		 * @var    object
		 */
		public $tgmpa;

		private function __construct()
		{
			$this->set_plugins();

			// Get TGMPA.
			if ( class_exists( 'ReyBase__TGM_Plugin_Activation' ) ) {
				$this->tgmpa = isset( $GLOBALS['rey_tgmpa'] ) ? $GLOBALS['rey_tgmpa'] : ReyBase__TGM_Plugin_Activation::get_instance();
			}

			add_filter( 'reybase_tgmpa_load', [$this, 'load_tgmpa'] );
			add_filter( 'reybase_tgmpa_admin_menu_args', [$this, 'tgmpa_menu_item'] );
			add_action( 'reybase_tgmpa_register', [$this, 'register_plugins'] );
			add_action( 'admin_notices', [$this, 'check_plugins_notice']);
			add_filter( 'reybase_tgmpa_table_data_item', [$this, 'filter_reybase_tgmpa_table_data_item'], 10, 2 );
			add_filter( 'reybase_tgmpa_plugin_action_links', [$this, 'filter_reybase_tgmpa_plugin_action_links'], 10, 3 );
			add_filter( 'rey/admin_script_params', [$this, 'add_admin_script_params'] );
			add_action( 'admin_init', [$this, 'tgmpa_refresh_plugins_list']);
			add_filter( 'upgrader_pre_download', [$this, 'pre_upgrader_filter'], 10, 4 );
			add_filter( 'add_menu_classes', [ $this, 'fix_submenu' ] );
			add_action( 'reybase_tgmpa_page_content', [$this, 'page_content']);

		}

		public function pre_upgrader_filter($reply, $package, $upgrader){

			if( ! ReyTheme_Base::get_purchase_code() ) {
				return $reply;
				// return new WP_Error( 'no_credentials',
				// sprintf(
				// 	__('<span class="rey-tgmpaNeedReg">Please <a href="%1$s">register %2$s</a>, to be able to install plugins bundled in %2$s, or install it <a href="%3$s" target="_blank">manually</a>.</span>', 'rey'),
				// 	esc_url(add_query_arg( ['page' => ReyTheme_Base::DASHBOARD_PAGE_ID ], admin_url('admin.php'))),
				// 	ucfirst(REY_THEME_NAME),
				// 	'https://support.reytheme.com/kb/installing-rey-plugins/#installing-plugins-manually'
				// ) );
			}

			if( !( $rey_plugins = $this->get_rey_plugins() ) ) {
				return $reply;
			}

			$sources = wp_list_pluck( $rey_plugins, 'source' );

			$plugin_slug = '';

			foreach ($sources as $key => $value) {
				if( $value === $package && isset($rey_plugins[$key]['slug']) && !empty($rey_plugins[$key]['slug']) && $rey_plugins[$key]['type'] === REY_THEME_NAME ){
					$plugin_slug = $rey_plugins[$key]['slug'];
				}
			}

			if( empty($plugin_slug) ){
				return $reply;
			}

			$upgrader->strings['downloading_package_url'] = esc_html__( 'Getting download link...', 'rey' );
			$upgrader->skin->feedback( 'downloading_package_url' );

			$download_link = ReyTheme_API::getInstance()->get_download_url($plugin_slug);

			if ( ! $download_link ) {
				return new WP_Error( 'no_credentials', esc_html__( 'Download link could not be retrieved', 'rey' ) );
			}

			$upgrader->strings['downloading_package'] = esc_html__( 'Downloading package...', 'rey' );
			$upgrader->skin->feedback( 'downloading_package' );

			$download_file = download_url( $download_link );

			if ( is_wp_error( $download_file ) && ! $download_file->get_error_data( 'softfail-filename' ) ) {
				return new WP_Error( 'download_failed', $upgrader->strings['download_failed'], $download_file->get_error_message() );
			}

			return $download_file;
		}

		/**
		 * Conditionally load TGMPA
		 *
		 * @since 1.0.0
		 */
		public function load_tgmpa( $status )
		{
			return is_admin() || current_user_can( 'install_plugins' );
		}

		/**
		 * Get TGMPA url
		 *
		 * @since 1.0.0
		 */
		public function tgmpa_url()
		{
			return $this->tgmpa->get_tgmpa_url();
		}

		/**
		 * Filter TGMPA's table and append a "need registration" flag
		 *
		 * @since 1.0.0
		 */
		function filter_reybase_tgmpa_table_data_item( $table_item, $plugin ){
			if( isset($plugin['type']) && $plugin['type'] === REY_THEME_NAME && ! ReyTheme_Base::get_purchase_code() ){
				$table_item['needs_registration_for_action'] = true;
			}
			return $table_item;
		}

		/**
		 * Message to show in TGMPA's plugins that
		 * are unregistered.
		 *
		 * @since 1.0.0
		 */
		function reybase_tgmpa_plugin_action_links_message( $plugin_name, $action = '' ){
			return rey__wp_kses( sprintf( __('<span class="rey-tgmpaNeedReg">Please <a href="%1$s">register %2$s</a>, to be able to %3$s %4$s, or %3$s it <a href="%5$s" target="_blank">manually</a>.</span>', 'rey'),
				esc_url(add_query_arg( ['page' => ReyTheme_Base::DASHBOARD_PAGE_ID ], admin_url('admin.php'))),
				REY_THEME_NAME,
				$action,
				$plugin_name,
				'https://support.reytheme.com/kb/installing-rey-plugins/#installing-plugins-manually'
			) );
		}


		/**
		 * Filter TGMPA's action links and depending on the "need registration" flag,
		 * show a notice to manually install if unregistered.
		 *
		 * @since 1.0.0
		 */
		function filter_reybase_tgmpa_plugin_action_links( $action_links, $item_slug, $item )
		{
			if( isset($item['needs_registration_for_action']) )
			{
				if( isset($action_links['install']) ){
					$action_links['install'] = $this->reybase_tgmpa_plugin_action_links_message( $item['plugin'], esc_html__('install', 'rey') );
				}

				if( isset($action_links['update']) ){
					$action_links['update'] = $this->reybase_tgmpa_plugin_action_links_message( $item['plugin'], esc_html__('update', 'rey') );
				}
			}

			return $action_links;
		}

		/**
		 * Show notice if required plugins are not installed or inactive
		 *
		 * @since 1.0.0
		 */
		public function check_plugins_notice()
		{
			$required_plugins = $this->get_required_plugins();

			if( empty($required_plugins) ){
				return;
			}

			$notice_plugins = [];

			foreach( $required_plugins as $plugin ){
				if( ! $plugin['active'] && $plugin['slug'] !== 'one-click-demo-import' ){
					$notice_plugins[] = esc_html($plugin['name']);
				}
			}

			if( !empty($notice_plugins) ){ ?>
			<div class="notice notice-warning js-storeNotice" data-from="required-plugins">
				<p><?php echo rey__wp_kses( sprintf( __('<strong>%1$s theme</strong> requires a few plugins (<strong>%3$s</strong>) to be installed & activated to work properly. <a href="%2$s">Install & Activate plugins</a>', 'rey'),
					ucfirst( REY_THEME_NAME ),
					esc_url(add_query_arg([ 'page' => ReyTheme_Base::DASHBOARD_PAGE_ID ], admin_url('admin.php'))),
					implode(', ', $notice_plugins)
				) ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'rey') ?></span>
				</button>
			</div>
			<?php }
		}

		/**
		 * Get plugins
		 *
		 * @since 1.0.0
		 */
		public function get_plugins( $type = 'all' ){

			if( $type === 'all' ) {
				return $this->plugins;
			}
			else {
				return array_filter($this->plugins, function($v, $k) use ($type){

					// Get REY plugins (self-hosted)
					if( $type === 'rey' ){
						$item = $v['type'] == REY_THEME_NAME;
					}
					// Get required plugins
					else if ($type === 'required'){
						// Special case: One Click Demo Import
						// if a demo has been imported, OCDI is not required any more
						$is_ocdi = $v['slug'] === 'one-click-demo-import' && get_option('rey_import_demo_finished', false) !== false;
						$item = $v['required'] === true && !$is_ocdi;
					}

					return $item;

				}, ARRAY_FILTER_USE_BOTH);
			}
		}

		/**
		 *
		 *
		 * @since 1.0.0
		 */
		public function get_rey_plugins() {
			return $this->get_plugins('rey');
		}

		/**
		 * Get Rey's plugins marked as required.
		 *
		 * @since 1.0.0
		 */
		public function get_required_plugins(){
			return $this->get_plugins('required');
		}

		/**
		 * Set plugin list.
		 *
		 * @param $force (bool). Force check with API server.
		 *
		 * @since 1.0.0
		 */
		private function set_plugins()
		{
			if( function_exists('rey__maybe_disable_obj_cache') ){
				rey__maybe_disable_obj_cache();
			}

			$plugins = get_site_option( self::PLUGIN_LIST_OPTION, [] );

			if( empty($plugins) )
			{
				$plugins = $this->get_default_plugins_list();

				if( ReyTheme_Base::get_purchase_code() )
				{
					$request = ReyTheme_API::getInstance()->get_plugins();

					if ( !is_wp_error( $request ) )
					{
						if ( isset($request['data']) && is_array($request['data']) && !empty($request['data']))  {
							$plugins = array_map('rey__clean', $request['data']);
						}
					}
					else
					{
						rey__log_error( 'err006', $request );
					}
				}

				if( is_array($plugins) && !empty($plugins) ){
					update_site_option( self::PLUGIN_LIST_OPTION, $plugins );
				}
			}

			$this->set_plugin_statuses( $plugins );
		}

		private function set_plugin_statuses( $plugins = [] ){

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$all_plugins = get_plugins();

			if( is_array($plugins) && !empty( $plugins ) ){
				foreach( $plugins as $slug => $plugin ){
					$plugins[ $slug ][ 'installed' ] = isset( $all_plugins[ $plugin['file_path'] ] );
					$plugins[ $slug ][ 'active' ] = $plugins[ $slug ][ 'installed' ] && isset($plugin['php_class']) ? class_exists( $plugin['php_class'] ) : false;
				}
			}

			$this->plugins = $plugins;

		}

		public function refresh_plugins(){
			delete_site_option( self::PLUGIN_LIST_OPTION );
			$this->set_plugins();
		}

		/**
		 * Add TGMPA configuration
		 *
		 * @since 1.0.0
		 */
		private function tgmpa_config( $location = '' ){

			if( $location ){
				$menu = $location;
			}
			else {
				$menu = REY_THEME_NAME . '-install-required-plugins';
			}

			return [
				'id'           => 'rey-tgmpa',
				'menu'         => $menu,
				'has_notices'  => false,
				'is_automatic' => true,
			];

		}

		public function fix_submenu( $menu ) {
			global $submenu;

			if ( is_multisite() && is_network_admin() ) {
				return $menu;
			}

			// Non admin role / custom wp menu.
			if ( empty( $submenu[ ReyTheme_Base::DASHBOARD_PAGE_ID ] ) ) {
				return $menu;
			}

			// Hack to add a link to sub menu.
			foreach ( $submenu[ ReyTheme_Base::DASHBOARD_PAGE_ID ] as &$item ) {
				if ( strpos($item[2], REY_THEME_NAME) === 0 ) {
					$item[4] = 'cls-' . $item[2];
				}
			}

			return $menu;
		}

		function tgmpa_menu_item($args){

			if( $dashboard_id = ReyTheme_Base::DASHBOARD_PAGE_ID ){
				$args['parent_slug'] = $dashboard_id;
				$args['page_title'] = esc_html__('Plugins Manager', 'rey');
				$args['menu_title'] = esc_html__('Plugins Manager', 'rey');
			}

			return $args;
		}

		function page_content(){
			?>
			<div class="rey-ocdi__intro-text">
				<h3><?php esc_html_e('Notes:', 'rey') ?></h3>
				<ul>
					<li>
						<p>These plugins are <strong>not required</strong> and you don't need to install all of them. They're just used throughout the demos. If you import a demo, you'll be prompted to choose which plugins to install.</p>
					</li>
					<li>
						<p>If you don't or can't import a demo and instead want to start from scratch installing plugins, you can head over to <a href="https://support.reytheme.com/kb/installing-rey-plugins/#plugins-that-are-used-in-the-demos" target="_blank">this KB article</a> and see which plugins are used in a particular demo.</p>
					</li>
				</ul>
			</div>
			<?php
		}

		/**
		 * Register plugin configuration for TGMPA
		 *
		 * @since 1.0.0
		 */
		public function register_plugins()
		{
			if( $this->plugins ) {
				reybase_tgmpa( $this->plugins, $this->tgmpa_config() );
			}
		}

		public function activate_plugin( $file_path ){
			// Include the plugin.php file so you have access to the activate_plugin() function
			require_once(ABSPATH .'/wp-admin/includes/plugin.php');
			$activate = activate_plugin( $file_path );
			if ( is_wp_error( $activate ) ) {
				return false; // End it here if there is an error with activation.
			}
			return true;
		}

		/**
		 * Parse list of plugins and return the first one
		 * under certain conditions.
		 *
		 * @since 1.0.0
		 */
		function get_plugin_for_action( $plugins = [] )
		{
			if( !empty($plugins) ){
				return current( array_filter( $plugins, function($v, $k){
					// not installed (or installed and inactive) and premium (but registered)
					return (
						! $v['installed'] || ($v['installed'] && ! $v['active'])) &&
						! ( $v['type'] === REY_THEME_NAME && !ReyTheme_Base::get_purchase_code()
					) ;
				}, ARRAY_FILTER_USE_BOTH) );
			}
			return false;
		}

		/**
		 * Gets the default list of plugins
		 *
		 * @since 1.0.0
		 */
		private function get_default_plugins_list()
		{
			$plugins = [];
			$plugins_json_file = REY_THEME_DIR . '/inc/files/plugins.json';

			if(	($wp_filesystem = rey__wp_filesystem()) && $wp_filesystem->is_file( $plugins_json_file ) ) {
				if( $json_raw = $wp_filesystem->get_contents( $plugins_json_file ) ){
					$plugins = json_decode($json_raw, true );
				}
			}

			return array_map('rey__clean', $plugins);
		}

		/**
		 * Add script params
		 *
		 * @since 1.0.0
		 */
		function add_admin_script_params($params){

			$params['refresh_plugins_text'] = esc_html__('SYNC PLUGINS LIST', 'rey');
			$params['refresh_plugins_url'] = wp_nonce_url( admin_url( sprintf('admin.php?page=%s', REY_THEME_NAME . '-install-required-plugins') ), 'rey_refresh_plugins', 'refresh_plugins_nonce');

			return $params;
		}

		function tgmpa_refresh_plugins_list(){
			if (isset($_GET['refresh_plugins_nonce']) && wp_verify_nonce($_GET['refresh_plugins_nonce'], 'rey_refresh_plugins')) {
				$this->refresh_plugins();
				wp_safe_redirect( admin_url( sprintf('admin.php?page=%s', REY_THEME_NAME . '-install-required-plugins') ) );
				exit();
			}
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyTheme_Plugins
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyTheme_Plugins::getInstance();

endif;
