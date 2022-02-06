<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('ReyCore_WidgetsManager')):
	/**
	 * Elementor elements manager.
	 *
	 * @since  1.1.2
	 */
	class ReyCore_WidgetsManager
	{
		const MENU_SLUG = REY_CORE_THEME_NAME . '-widgets-manager';

		const DB_OPTION = 'reycore-disabled-elements';

		public function __construct()
		{
			$this->init_hooks();
		}

		function init_hooks(){
			add_action( 'admin_menu', [$this, 'register_admin_menu'], 120 );
			add_action( 'wp_ajax_change_widget_status', [$this, 'change_widget_status'] );
			add_action( 'wp_ajax_rey_activate_element', [$this, 'rey_activate_element'] );
			add_action( 'wp_ajax_get_elementor_pages', [$this, 'get_elementor_pages'] );
			add_action( 'wp_ajax_check_elements_status', [$this, 'check_elements_status'] );
			add_action( 'wp_ajax_disable_unused_elements', [$this, 'disable_unused_elements'] );
			add_action( 'elementor/init', [$this, 'should_render_element'] );
			add_action( 'elementor/editor/wp_head', [$this, 'print_head_styles'] );
		}

		public function register_admin_menu() {
			if( $dashboard_id = reycore__get_dashboard_page_id() ){
				$title = __( 'Elements Manager', 'rey-core' );
				add_submenu_page(
					$dashboard_id,
					$title,
					$title,
					'manage_options',
					self::MENU_SLUG,
					[ $this, 'render_page' ]
				);
			}
		}

		public static function get_disabled_elements(){
			return get_option( self::DB_OPTION, apply_filters('reycore/elementor_widgets/disabled_defaults', [
				'reycore-cover-distortion',
				'reycore-hoverbox-distortion'
			]) );
		}

		function render_elements(){

			$widgets_per_category = [];
			$categories = \Elementor\Plugin::$instance->elements_manager->get_categories();
			$all_widgets = \Elementor\Plugin::$instance->widgets_manager->get_widget_types();
			$rey_widgets_list = reyCoreElementor()->get_widgets_list();

			foreach ($all_widgets as $key => $widget) {

				if( ! array_key_exists(str_replace('reycore-', '', $key), array_flip($rey_widgets_list) ) ){
					continue;
				}

				$widget_categories = $widget->get_categories();
				$widgets_per_category[$widget_categories[0]][] = $widget;
			}

			foreach ($widgets_per_category as $key => $elements) {
				printf('<h2>%s</h2>', $categories[$key]['title']);
				foreach ($elements as $key => $el) {
					$this->render_element($el);
				}
			}
		}

		function render_element( $widget ){

			$id = $widget->get_unique_name();
			$is_active = array_search($id, self::get_disabled_elements() ) === false;

			?>
			<div class="rey-elManager-item <?php echo $is_active ? '--active' : ''; ?>" data-element="<?php echo esc_attr($id) ?>">
				<i class="<?php echo $widget->get_icon() ?>" aria-hidden="true"></i>
				<h2><?php echo $widget->get_title() ?></h2>
				<div class="rey-toggleSwitch">
					<span class="rey-spinnerIcon"></span>
					<div class="rey-toggleSwitch-box">
						<input id="rey-toggSwitch<?php echo esc_attr($id) ?>" type="checkbox" <?php echo $is_active ? 'checked' : '' ?>>
						<label for="rey-toggSwitch<?php echo esc_attr($id) ?>">
							<span class="rey-toggleSwitch-label" data-activate="<?php echo esc_attr(__('Activate Element', 'rey-core')) ?>" data-deactivate="<?php echo esc_attr(__('Deactivate Element', 'rey-core')) ?>"></span>
						</label>
					</div>
				</div>
				<div class="rey-elManager-notice">
					<?php esc_html_e('Not in use. Safe to deactivate element.', 'rey-core') ?>
				</div>
			</div>
			<?php
		}

		public function render_page(){

			$this->load_font_awesome();

			?>
			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&amp;display=swap">
			<div class="wrap rey-elManager-wrapper">
				<div class="rey-elManager-cleanup">
					<button class="elManager-disableUnused"><span><?php esc_html_e('DISABLE UNUSED ELEMENTS', 'rey-core') ?></span><span class="rey-spinnerIcon"></span></button>
					<button class="elManager-scanButton"><span><?php esc_html_e('SCAN UNUSED ELEMENTS', 'rey-core') ?></span><span class="rey-spinnerIcon"></span></button>
				</div>
				<h1><?php esc_html_e('Elements Manager (Elementor Widgets)', 'rey-core') ?></h1>
				<p class="description"><?php _e('Use this tool to control what elements to load in Elementor. Keep in mind that this tool only disables elements in Elementor <strong>edit mode</strong>.', 'rey-core') ?></p>
				<div class="rey-elManager">
					<?php
						$this->render_elements();
					?>
				</div>
			</div><!-- /.wrap -->
			<?php
		}

		/**
		 * Change widget status
		 */
		function change_widget_status(){

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey-core') );
			}

			$disabled_elements = self::get_disabled_elements();

			if( isset($_POST['status']) && isset($_POST['element']) ){
				$element = sanitize_text_field($_POST['element']);

				// activate
				if( $_POST['status'] === 'true' ){
					if (($key = array_search($element, $disabled_elements)) !== false) {
						unset($disabled_elements[$key]);
					}
				}
				// deactivate
				elseif( $_POST['status'] === 'false' ){
					$disabled_elements[] = $element;
				}

				// cleanup
				$disabled_elements = array_values(array_unique($disabled_elements));

				if( update_option(self::DB_OPTION, $disabled_elements) ){
					wp_send_json_success($disabled_elements);
				}
			}
			wp_send_json_error();
		}

		/**
		 * Change widget status
		 */
		function disable_unused_elements(){

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey-core') );
			}

			$disabled_elements = self::get_disabled_elements();

			if( isset($_POST['items']) && is_array($_POST['items']) && !empty($_POST['items']) ){

				foreach( $_POST['items'] as $item ){
					$disabled_elements[] = sanitize_text_field($item);
				}

				// $rey_widgets_list = reyCoreElementor()->get_widgets_list();
				// array_flip($rey_widgets_list);
				// if( ! array_key_exists(str_replace('reycore-', '', $key), array_flip($rey_widgets_list) ) ){


				if( update_option(self::DB_OPTION, $disabled_elements) ){
					wp_send_json_success($disabled_elements);
				}
			}

			wp_send_json_error();
		}

		/**
		 * Ajax Activate element from Elementor preview
		 */
		function rey_activate_element(){

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey-core') );
			}

			$disabled_elements = self::get_disabled_elements();

			if( isset($_POST['element']) ){

				$element = sanitize_text_field($_POST['element']);

				// activate
				if (($key = array_search($element, $disabled_elements)) !== false) {
					unset($disabled_elements[$key]);
				}

				if( update_option(self::DB_OPTION, $disabled_elements) ){
					wp_send_json_success();
				}
			}
			wp_send_json_error();
		}


		/**
		 * Get Elementor built pages to be used for iterating data.
		 */
		function get_elementor_pages(){

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			global $wpdb;

			$post_ids = $wpdb->get_col(
				'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
						WHERE `meta_key` = \'_elementor_version\';'
			);

			if ( empty( $post_ids ) ) {
				wp_send_json_error(esc_html('Empty post list.'));
			}

			$clean_post_ids = [];

			foreach ( $post_ids as $post_id ) {
				if( 'revision' === get_post_type($post_id) ){
					continue;
				}
				$clean_post_ids[] = $post_id;
			}

			wp_send_json_success( $clean_post_ids );
		}

		/**
		 * Iterate a post data
		 */
		function check_elements_status(){

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey-core') );
			}

			if( !isset($_GET['post_id']) ){
				wp_send_json_error( esc_html('Missing post id.') );
			}

			$document = \Elementor\Plugin::$instance->documents->get( absint($_GET['post_id']) );

			if ( $document ) {
				$data = $document->get_elements_data();
			}

			if ( empty( $data ) ) {
				wp_send_json_error( esc_html('No data to iterate.') );
			}

			$to_return = [];
			$rey_widgets = reyCoreElementor()->get_widgets_list();

			\Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) use ($rey_widgets, &$to_return) {

				if ( !empty( $element['widgetType'] ) && array_key_exists( str_replace('reycore-', '', $element['widgetType']), array_flip($rey_widgets) ) ) {
					$to_return[] = $element['widgetType'];
				}
			} );

			wp_send_json_success( $to_return );
		}

		/**
		 * Prevents disabled element from rendering
		 */
		function should_render_element(){

			foreach( self::get_disabled_elements() as $element ){
				add_filter("elementor/widget/render_content", function($widget_content, \Elementor\Widget_Base $widget_base) use ($element) {
					if( $widget_base->get_unique_name() === $element ){

						if( is_user_logged_in() ){

							$activate_text = '';

							if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
								$activate_text = sprintf(__('You can activate this element simply by <a href="#" data-element="%s" class="js-click-activate-element">clicking here</a>, or by accessing <a href="%s" target="_blank">Rey Theme > Elements manager</a> to manage all elements.', 'rey-core'), esc_attr($element), admin_url('admin.php?page=' . self::MENU_SLUG) );
							}

							return sprintf( __('<div class="rey-disabledElement">Element is disabled. %s</div>', 'rey-core'), $activate_text);
						}

						return '';
					}
					return $widget_content;
				}, 10, 2);
			}
		}

		/**
		 * Add special disabled label to icons
		 */
		function print_head_styles(){

			$style = '';

			foreach( self::get_disabled_elements() as $element ){
				$style .= sprintf('.elementor-element[data-widget-type="%s"]:before { content:"%s"; }', $element, esc_html__('DISABLED', 'rey-core'));
				$style .= sprintf('#elementor-panel-elements .elementor-element[data-widget-type="%s"] .icon:before { top: 1.6em; }', $element);
			}

			if( empty($style) ){
				return;
			}

			printf('<style>%s</style>', $style);
		}

		function load_font_awesome(){
			wp_enqueue_style( 'font-awesome' );
			if( class_exists('\Elementor\Icons_Manager') ){
				Elementor\Icons_Manager::enqueue_shim();
			}
		}

	}

	new ReyCore_WidgetsManager;
endif;
