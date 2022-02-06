<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyTheme_Updates') ):
	/**
	 * Rey Theme & Rey plugins update manager.
	 *
	 * @since 1.0.0
	 */
	class ReyTheme_Updates
	{
		const OPTION_CLEANUP_NEEDED = 'rey_needs_cleanup';

		public function __construct()
		{
			$this->maybe_delete_transients();

			add_filter( 'pre_set_site_transient_update_themes', [ $this, 'rey_pre_set_transient_update_theme' ] );
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'rey_pre_set_transient_update_plugins' ] );
			add_action( 'delete_site_transient_update_plugins', [ $this, 'delete_transients' ] );
			// add_filter( 'upgrader_package_options', [ $this, 'get_theme_plugins_package' ] );
			add_filter( 'upgrader_process_complete', [ $this, 'after_theme_core_update' ], 10, 2 );
			add_filter( 'upgrader_overwrote_package', [ $this, 'after_theme_core_upgrader_overwrote_package' ], 10, 3 );
			add_action( 'admin_init', [$this, 'flush_rey_cache_after_updates'], 20);
			add_action( 'rey/flush_cache_after_updates', [$this, 'delete_cleanup_option']);
			add_action( 'rey/flush_cache_after_updates', [$this, 'recheck_theme_updates']);
			add_filter( 'plugins_api', [$this, 'get_changelog'], 10, 3);

			add_action('rey/dashboard/box/versions', [$this, 'add_theme_version_box'], 10);
			add_action('rey/dashboard/box/versions', [$this, 'add_core_version_box'], 20);

			add_filter('update_theme_complete_actions', [$this, 'fix_activate_link_on_update_theme'], 20, 2);
		}

		public function delete_transients() {
			// refresh plugin list
			ReyTheme_Plugins::getInstance()->refresh_plugins();
		}

		private function maybe_delete_transients() {
			global $pagenow;

			if ( 'update-core.php' === $pagenow && isset( $_GET['force-check'] ) ) {
				$this->delete_transients();
			}
		}

		/**
		 * After Rey theme or Core were updated.
		 *
		 * Adds a "need cache flush".
		 *
		 * @since 1.0.0
		 */
		public function after_theme_core_update( $upgrader_object, $options ) {

			$cleanup = false;

			if ( isset($options['action']) && $options['action'] == 'update' ){
				if (isset($options['type'])){

					if( $options['type'] == 'plugin' ){
						$plugins = isset($options['plugins']) && !empty($options['plugins']) ? $options['plugins'] : [];
						// coming from TGMPA
						if( isset($options['plugin']) && !empty($options['plugin']) ){
							$plugins = (array) $options['plugin'];
						}
						foreach($plugins as $plugin){
							if ( $plugin == rey__get_core_path()){
								$cleanup = true;
							}
						}
					}

					elseif ( $options['type'] == 'theme' ){
						$themes = isset($options['themes']) && !empty($options['themes']) ? $options['themes'] : [];
						// coming from TGMPA
						if( isset($options['theme']) && !empty($options['theme']) ){
							$themes = (array) $options['theme'];
						}
						foreach( $themes as $theme ){
							if ( $theme == REY_THEME_NAME ){
								error_log(var_export( 'hurray!',1));
							}
						}
					}
				}

				if( $cleanup ){
					update_site_option(self::OPTION_CLEANUP_NEEDED, true);
				}
			}
		}
		public function after_theme_core_upgrader_overwrote_package( $package, $new_data, $type ) {

			$cleanup = false;

			if (isset($type)){
				if( $type == 'plugin' && isset($new_data['Name']) && $new_data['Name'] === 'Rey Core' ){
					$cleanup = true;
				}
				elseif ( $type == 'theme' && isset($new_data['Name']) && $new_data['Name'] === 'Rey' ){
					$cleanup = true;
				}
			}

			if( $cleanup ){
				do_action('rey/flush_cache_after_updates');
			}

		}

		/**
		 * Flush caches after Rey theme or Core updates.
		 *
		 * @since 1.0.0
		 */
		public function flush_rey_cache_after_updates()
		{
			if( get_site_option(self::OPTION_CLEANUP_NEEDED, false) )
			{
				do_action('rey/flush_cache_after_updates');
				rey__log_error( 'err013', esc_html__('Cache flush after update', 'rey') );
			}
		}

		public function delete_cleanup_option(){
			// delete the option
			return delete_site_option(self::OPTION_CLEANUP_NEEDED);
		}

		public function recheck_theme_updates()
		{
			global $wp_current_filter;

			$wp_current_filter[] = 'load-update-core.php';

			wp_clean_update_cache();
			wp_update_themes();

			array_pop($wp_current_filter);

			do_action('load-plugins.php');
		}

		/**
		 * Reflect Rey Theme updates in WordPress Updates section
		 *
		 * @since 1.0.0
		 */
		public function rey_pre_set_transient_update_theme( $transient ) {

			if( empty( $transient->checked[ REY_THEME_NAME ] ) ){
				return $transient;
			}

			if( !ReyTheme_Base::get_purchase_code() ) {
				return $transient;
			}

			$theme_data = ReyTheme_API::getInstance()->get_theme_version();

			// check for errors
			if ( is_wp_error( $theme_data ) || (isset($theme_data['success']) && !$theme_data['success']) ) {
				rey__log_error( 'err002', $theme_data );
				return $transient;
			}

			//check server version against current installed version
			if( isset($theme_data['data']['new_version']) && version_compare( REY_THEME_VERSION, $theme_data['data']['new_version'], '<' ) ){
				$transient->response[ REY_THEME_NAME ] = [
					'new_version' => esc_html( $theme_data['data']['new_version'] ),
					'url' => rey__get_theme_url(),
					'package' => ReyTheme_API::getInstance()->get_download_url()
				];
			}

			return $transient;
		}

		/**
		 * Reflect Rey plugin updates in WordPress Updates section
		 *
		 * @since 1.0.0
		 */
		public function rey_pre_set_transient_update_plugins( $transient ) {

			if( ReyTheme_Base::get_purchase_code() && $rey_plugins = ReyTheme_Plugins::getInstance()->get_rey_plugins() )
			{
				foreach( $rey_plugins as $plugin )
				{
					if( isset($plugin['file_path']) && !empty( $plugin['file_path'] ) )
					{
						$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . $plugin['file_path'];

						if( is_readable( $plugin_path ) )
						{
							$plugin_data = get_plugin_data( $plugin_path );


							if(
								isset($plugin['version']) &&
								version_compare( $plugin_data['Version'], $plugin['version'], '<' ) )
							{
								$plugin_response = [
									'new_version' => esc_html( $plugin['version'] ),
									'url' => rey__get_theme_url(),
									'id' => $plugin['slug'],
									'slug' => $plugin['slug'],
									'plugin' => $plugin['file_path'],
									'package' => ReyTheme_API::getInstance()->get_download_url($plugin['slug']),
									// 'package' => '', // intentionally empty, is updated in `upgrader_package_options`

								];

								if( isset($plugin['icon']) ){
									$plugin_response['icons']['2x'] = $plugin['icon'];
									$plugin_response['icons']['1x'] = $plugin['icon'];
								}

								$transient->response[ $plugin['file_path'] ] = (object) $plugin_response;
							}
						}
					}
				}
			}

			return $transient;
		}

		/**
		 * Update the package_url for REY theme and self hosted plugins
		 *
		 * @since 1.0.0
		 */
		// public function get_theme_plugins_package( $options )
		// {
		// 	if( ReyTheme_Base::get_purchase_code() )
		// 	{
		// 		// Check theme
		// 		if( isset($options['hook_extra']['theme']) && $options['hook_extra']['theme'] == REY_THEME_NAME )
		// 		{
		// 			$download_url = ReyTheme_API::getInstance()->get_theme_download_url();

		// 			if( is_wp_error($download_url) )
		// 			{
		// 				rey__log_error( 'err003', $download_url );
		// 				return $options;
		// 			}

		// 			if( isset($download_url['data']['theme_update_url']) && !empty($download_url['data']['theme_update_url']) )
		// 			{
		// 				$options['package'] = esc_url_raw( $download_url['data']['theme_update_url'] );
		// 			}
		// 		}

		// 		// Check plugins
		// 		elseif( isset($options['package']) )
		// 		{
		// 			if( $plugins = ReyTheme_Plugins::getInstance()->get_rey_plugins() )
		// 			{
		// 				foreach( $plugins as $plugin )
		// 				{
		// 					if(
		// 						isset($plugin['file_path']) && !empty( $plugin['file_path'] ) &&
		// 						(
		// 							// tgmpa install & updates
		// 							$options['package'] === $plugin['file_path'] ||
		// 							// wp updates
		// 							( isset($options['hook_extra']['plugin']) && $options['hook_extra']['plugin']  === $plugin['file_path'] )
		// 						)
		// 					) {
		// 						$download_url = ReyTheme_API::getInstance()->get_plugin_data( $plugin['slug'] );

		// 						if( is_wp_error($download_url) )
		// 						{
		// 							rey__log_error( 'err004', $download_url );
		// 							return $options;
		// 						}

		// 						if( isset($download_url['data']['download_url']) && !empty($download_url['data']['download_url']) )
		// 						{
		// 							$options['package'] = esc_url_raw( $download_url['data']['download_url'] );
		// 						}
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}

		// 	return $options;
		// }

		/**
		 * Retrieves plugin changelog
		 *
		 * @since 1.0.3
		 */
		public function get_changelog($result, $action, $arg)
		{
			// only for 'plugin_information' action
			if( $action !== 'plugin_information' ) {
				return $result;
			}

			$get_plugins_list = get_site_option( ReyTheme_Plugins::PLUGIN_LIST_OPTION, [] );

			if(
				!empty($get_plugins_list) &&
				($plugins = ReyTheme_Plugins::getInstance()->get_rey_plugins()) &&
				array_key_exists($arg->slug, $plugins) && $plugins[$arg->slug]['type'] === 'rey'
			) {

				$content = get_site_transient('rey_changelog');

				if( !$content ){
					// JSON Page of Changelog
					$response = wp_remote_get( 'https://support.reytheme.com/wp-json/wp/v2/pages/372', [
						'timeout' => 40,
						'body' => [],
					] );

					if ( is_wp_error( $response ) ) {
						return false;
					}

					$data = json_decode( wp_remote_retrieve_body( $response ), true );

					if ( empty( $data ) || ! is_array( $data ) ) {
						return false;
					}

					$response_code = wp_remote_retrieve_response_code( $response );

					if ( 200 !== (int) $response_code ) {
						return false;
					}

					if( isset($data['content']['rendered']) ){

						$content = $data['content']['rendered'];

						set_site_transient('rey_changelog', $content, DAY_IN_SECONDS);
					}
				}

				$res = (object) [
					'sections' => [
						'changelog' => $content,
					],
					'slug' => $arg->slug,
					'name' => $plugins[$arg->slug]['name']
				];

				return $res;
			}

			return false;
		}

		static function get_theme_latest_update_info(){

			$html = '';

			if( !current_user_can( 'update_themes' ) ){
				return $html;
			}

			$themes_update = get_site_transient( 'update_themes' );

			$theme_instance = wp_get_theme( REY_THEME_NAME );
			$stylesheet = $theme_instance->get_stylesheet();

			if ( isset( $themes_update->response[ $stylesheet ] ) ) {

				$update      = $themes_update->response[ $stylesheet ];
				$theme_name  = $theme_instance->display( 'Name' );
				$details_url = add_query_arg(
					array(
						'TB_iframe' => 'true',
						'width'     => 1024,
						'height'    => 800,
					),
					'https://support.reytheme.com/changelog/'
				);

				if( !is_multisite() ){
					$update_url  = wp_nonce_url( admin_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $stylesheet ) ), 'upgrade-theme_' . $stylesheet );
				}
				else {
					$update_url  = network_admin_url( 'update-core.php' );
				}

				$html = sprintf(
					'<span class="rey-versions-latest">New Update Available: <span class="__version-no">v%1$s</span></span>
					<span class="rey-versions-links"><a href="%2$s" %3$s>See Changelog</a> or <a href="%4$s" %5$s>Update Now</a>.</span>',
					esc_attr( $update['new_version'] ),
					esc_url( $details_url ),
					sprintf(
						'class="thickbox open-plugin-details-modal" aria-label="%s"',
						esc_attr( __( 'See Changelog' ) )
					),
					$update_url,
					sprintf(
						'class="update-link" aria-label="%s"',
						esc_attr( __( 'Update now' ) )
					)
				);

			}

			return $html;
		}

		static function get_core_latest_update_info(){

			$html = '';

			if( ! current_user_can( 'update_plugins' ) ){
				return $html;
			}

			$plugins = get_site_transient( 'update_plugins' );

			$rc = 'rey-core/rey-core.php';

			if ( isset( $plugins->response ) && is_array( $plugins->response ) && isset($plugins->response[$rc]) ) {

				$response = $plugins->response[$rc];

				if( !is_multisite() ){
					$update_url  = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $rc, 'upgrade-plugin_' . $rc );
					$details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $response->slug . '&section=changelog&TB_iframe=true&width=600&height=800' );
				}
				else {
					$update_url  = network_admin_url( 'update-core.php' );
					$details_url = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $response->slug . '&section=changelog&TB_iframe=true&width=600&height=800' );
				}

				$html = sprintf(
					'<span class="rey-versions-latest">New Update Available: <span class="__version-no">v%1$s</span></span>
					<span class="rey-versions-links"><a href="%2$s" %3$s>See Changelog</a> or <a href="%4$s" %5$s>Update Now</a>.</span>',
					esc_attr( $response->new_version ),
					esc_url( $details_url ),
					sprintf(
						'class="thickbox open-plugin-details-modal" aria-label="%s"',
						esc_attr( __( 'See Changelog' ) )
					),
					$update_url,
					sprintf(
						'class="update-link" aria-label="%s"',
						esc_attr( __( 'Update now' ) )
					)
				);
			}

			return $html;
		}

		function version_box_template($args = []){
			?><tr>
				<th width="160">
					<h4 class="__title <?php echo $args['title_class'] ?>"><?php echo $args['title'] ?></h4>
				</th>
				<td>
					<div class="__content"><?php echo $args['content']; ?></div>
				</td>
			</tr><?php
		}

		function add_theme_version_box(){

			$update_info = self::get_theme_latest_update_info();

			$this->version_box_template([
				'title_class' => '--theme',
				'title' => 'REY THEME',
				'content' => sprintf( '<strong class="__version-no">v%s</strong> %s', REY_THEME_VERSION, $update_info ),
			]);
		}

		function add_core_version_box(){

			if( ! defined('REY_CORE_VERSION') ){
				return;
			}

			$update_info = self::get_core_latest_update_info();

			$this->version_box_template([
				'title_class' => '--core',
				'title' => 'REY CORE',
				'content' => sprintf( '<span class="__version-no">v%s</span> %s', REY_CORE_VERSION, $update_info ),
			]);
		}

		function fix_activate_link_on_update_theme($update_actions, $theme){

			if ( REY_THEME_NAME === $theme && get_template_directory() !== get_stylesheet_directory()) {
				unset($update_actions['activate']);
			}

			return $update_actions;

		}

	}

	new ReyTheme_Updates;

endif;
