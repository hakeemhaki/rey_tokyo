<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_Module_ReyTemplates_Elementor') ):

	class ReyCore_Module_ReyTemplates_Elementor
	{
		public static $instance;

		public $document;

		public static $post_type = '';
		private static $widgets_dir = '';
		private static $widgets = [];

		private $_pid = [];

		private $_built_with_elementor = false;

		public function __construct()
		{

			if( ! (class_exists('Elementor\Plugin') && is_callable( 'Elementor\Plugin::instance' )) ){
				return;
			}

			self::$instance = \Elementor\Plugin::instance();

			self::$post_type = reyTemplates()::POST_TYPE;

			self::$widgets_dir = trailingslashit( REY_CORE_MODULE_DIR . 'custom-templates/elementor-widgets/' );
			array_map( [$this, 'get_widget_basename'], glob( self::$widgets_dir . '*' ));

			add_filter( 'template_include', [ $this, 'template_include' ], 11 );
			add_action( 'elementor/element/wp-post/document_settings/before_section_end', [$this, 'page_settings'], 10);
			add_action( 'wp_enqueue_scripts', [$this, 'load_css'], 500 );
			add_filter( 'elementor/frontend/admin_bar/settings', [$this, 'filter_edit_with_elementor'], 20);
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
			add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );
			add_filter( 'language_attributes', [$this, 'language_attributes'], 20 );
			add_action( 'reycore/templates/elements/before_content_render', [$this, 'preview__elements_before'] );
			add_action( 'reycore/templates/elements/after_content_render', [$this, 'preview__elements_after'] );
			add_action( 'elementor/controls/controls_registered', [$this, 'add_tab'] );
			add_action( 'reycore/templates/tpl/before_render', [$this, 'add_structured_data'] );
			add_filter( 'reycore/woocommerce/sidebars/can_output_shop_sidebar', [ $this, 'disable_sidebar'] );
			add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
			add_filter( 'reycore/admin_bar_menu/nodes', [$this, 'admin_menu_link'], 11 );
		}


		function is_edit_mode(){

			if( is_user_logged_in()  ) {
				return self::$instance->editor->is_edit_mode() || self::$instance->preview->is_preview_mode();
			}

			return false;
		}

		function template_include($template){

			// fallback
			$rt_template = $template;

			$elementor_templates['elementor_canvas'] = 'canvas.php';
			$elementor_templates['elementor_header_footer'] = 'header-footer.php';

			// make sure it's not an elementor template
			if( ! in_array(basename($template), $elementor_templates, true) ){
				$rt_template = REY_CORE_MODULE_DIR . 'custom-templates/elementor-tpl.php';
			}

			// no template override
			if( empty( reyTemplates()->template ) ){

				// probably Edit mode in RT post type,
				// so load RT template
				if( get_post_type() === self::$post_type ){
					return $rt_template;
				}

				return $template;
			}

			// if it's built with Elementor
			// load RT template, in frontend
			if( ( $document = self::$instance->documents->get( reyTemplates()->template['id'] ) ) && $document->is_built_with_elementor() ){

				$this->_built_with_elementor = true;

				return $rt_template;
			}

			return $template;

		}

		function admin_menu_link( $nodes ){

			if( ! isset($nodes['rey_template']) ){
				return $nodes;
			}

			if( $this->_built_with_elementor ){
				if( isset($nodes['main']['class']) ){
					$nodes['main']['class'] .= ' --has-rt-el';
				}
			}

			return $nodes;
		}



		/**
		 * Add page settings into Elementor
		 *
		 * @since 1.0.0
		 */
		function page_settings( $page )
		{

			if( ! (($page_id = $page->get_id()) && $page_id != "") ) {
				return;
			}

			// if( isset(reyTemplates()->template['id']) ){
			// 	$page_id = reyTemplates()->template['id'];
			// }

			$post_type = get_post_type( $page_id );

			if( $post_type === 'revision' && $page_id !== 0 && $revision_post = get_post_parent($page_id) ){
				$post_type = $revision_post->post_type;
			}

			if ( $post_type !== self::$post_type ) {
				return;
			}

			$page->add_control(
				'rey_templates_type',
				[
					'label' => esc_html_x( 'Template Type', 'Elementor control label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html_x( 'Default', 'Elementor control label', 'rey-core' ),
						'full'  => esc_html_x( 'Full Width', 'Elementor control label', 'rey-core' ),
						'canvas'  => esc_html_x( 'Blank Canvas', 'Elementor control label', 'rey-core' ),
					],
				]
			);

			$page->add_control(
				'rey_templates_grid',
				[
					'label' => esc_html_x( 'Grid Type', 'Elementor control label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html_x( '- Inherit -', 'Elementor control label', 'rey-core' ),
						'elementor'  => esc_html_x( 'Elementor Default Grid', 'Elementor control label', 'rey-core' ),
						'rey'  => esc_html_x( 'Rey Grid', 'Elementor control label', 'rey-core' ),
					],
				]
			);

			$template_type = get_field( 'template_type', $page_id );

			$preview_data = [
				'product' => [
					'label' => esc_html_x( 'Select Product for Preview', 'Elementor control label', 'rey-core' ),
					'description' => esc_html_x( 'Leaving empty will get the first product that\'s found.', 'Elementor control label', 'rey-core' ),
					'query_args' => [
						'type' => 'posts',
						'post_type' => 'product',
					],
				],
				'product-archive' => [
					'label' => esc_html_x( 'Select Product Archive for Preview', 'Elementor control label', 'rey-core' ),
					'description' => esc_html_x( 'Leaving empty will get the first product archive that\'s found.', 'Elementor control label', 'rey-core' ),
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'product_cat',
					],
				]
			];

			if( ! isset($preview_data[ $template_type ]) ){
				return;
			}

			$page->add_control(
				'rey_templates_preview_id',
				[
					'label' => $preview_data[ $template_type ]['label'],
					'description' => $preview_data[ $template_type ]['description'],
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => $preview_data[ $template_type ]['query_args'],
				]
			);

			$page->add_control(
				'rey_templates_apply_preview',
				[
					'type' => \Elementor\Controls_Manager::BUTTON,
					'button_type' => 'default',
					'text' => __( 'Apply & Reload', 'rey-core' ),
					'event' => 'rey:editor:apply_preview',
					'default' => $template_type
				]
			);

		}

		function get_settings( $id = null ){

			$settings = [
				'type' => '',
				'grid' => '',
				'preview_id' => '',
			];

			if( ! $id ){
				return $settings;
			}

			if( ! ($document = self::$instance->documents->get( $id )) ) {
				return $settings;
			}

			$doc_settings = $document->get_settings();

			if( isset($doc_settings['rey_templates_type']) ){
				$settings['type'] = $doc_settings['rey_templates_type'];
			}
			if( isset($doc_settings['rey_templates_grid']) ){
				$settings['grid'] = $doc_settings['rey_templates_grid'];
			}
			if( isset($doc_settings['rey_templates_preview_id']) ){
				$settings['preview_id'] = $doc_settings['rey_templates_preview_id'];
			}

			return $settings;

		}

		function load_css(){

			if( ! (isset(reyTemplates()->template['id']) && ($template_id = reyTemplates()->template['id'])) ){
				return;
			}

			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {

				$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );

				if( !empty($css_file) ){
					$css_file->enqueue();
				}

			}
		}

		function filter_edit_with_elementor($settings){

			if( !empty($settings['elementor_edit_page']['children']) ){
				foreach ($settings['elementor_edit_page']['children'] as $id => $value) {
					if( self::$post_type === get_post_type($id) ){
						$title = esc_html__('Custom Template', 'rey-core');
						$settings['elementor_edit_page']['children'][$id]['sub_title'] = $title;
					}
				}
			}

			return $settings;
		}

		/**
		 * Get widgets basename
		 *
		 * @since 1.0.0
		 */
		private function get_widget_basename( $item ){
			if( is_dir( $item ) ){
				self::$widgets[] = basename( $item );
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

			require_once self::$widgets_dir . 'woo-element-base.php';

			foreach ( self::$widgets as $widget ) {
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
			foreach ( glob( trailingslashit( self::$widgets_dir . $widget ) . "skin-*.php") as $skin) {
				require_once $skin;
			}

			// Load widget
			if( ( $file = trailingslashit( self::$widgets_dir . $widget ) . $widget . '.php' ) && is_readable($file) ){
				require_once $file;
			}

			if ( class_exists( $class ) ) {
				// Register widget
				$widgets_manager->register_widget_type( new $class );
			}

		}

		public function maybe_always_show_elements(){
			return apply_filters('reycore/module/rey_templates/always_show_elements', false, $this);
		}

		public function maybe_show_category( $type ){

			// show everywhere
			if( $this->maybe_always_show_elements() ){
				return true;
			}

			$pt = get_post_type();
			$is_ct_pt = $pt === self::$post_type;
			$is_epro_pt = $pt === 'elementor_library';

			if( ! ( $is_ct_pt || $is_epro_pt ) ){
				return false;
			}

			$types = [
				'product'         => 'rey-woocommerce-pdp',
				'product-archive' => 'rey-woocommerce-loop',
			];

			$maybe_show = false;

			foreach ($types as $key => $cat_name) {

				if( $type !== $cat_name ){
					continue;
				}

				// show in product pages in CT REY
				if (
					$is_ct_pt &&
					($template_type = get_field( 'template_type', get_the_ID() )) &&
					$template_type === $key
				) {
					$maybe_show = true;
				}

				// show in product pages in EPRO
				else if(
					$is_epro_pt &&
					($document = \Elementor\Plugin::$instance->documents->get( get_the_ID() )) &&
					$document->get_template_type() === $key
				){
					$maybe_show = true;
				}

			}

			return $maybe_show;
		}

		/**
		 * Add Rey Widget Categories
		 *
		 * @since 1.0.0
		 */
		public function add_elementor_widget_categories( $elements_manager ) {

			if( ! class_exists('WooCommerce') ){
				return;
			}

			$categories = [
				'rey-woocommerce-pdp' => [
					'title' => __( 'REY theme - WooCommerce <strong>Product Page</strong>', 'rey-core' ),
					'icon' => 'fa fa-plug',
				],
				'rey-woocommerce-loop' => [
					'title' => __( 'REY theme - WooCommerce <strong>Product Archive</strong>', 'rey-core' ),
					'icon' => 'fa fa-plug',
				]
			];

			foreach( $categories as $key => $data ){

				if( ! $this->maybe_show_category($key) ){
					continue;
				}

				$elements_manager->add_category($key, $data);
			}

		}

		function language_attributes($output){

			if( get_post_type() !== self::$post_type ){
				return $output;
			}

			if( ! ($type = get_field('template_type', get_the_ID())) ){
				return $output;
			}

			$output .= sprintf(' data-rt="%s"', $type );

			if( $type === 'page' ){
				if( $page_type = get_field('page_types', get_the_ID()) ){
					$output .= sprintf(' data-page-type="%s"', $page_type );
				}
			}

			return $output;
		}

		function __force_preview($post){

			if( ! isset($post->post_type) ){
				return;
			}

			if( $post->post_type !== self::$post_type ){
				return;
			}

			if( isset($_REQUEST[self::$post_type]) ){
				return true;
			}

			if( $this->is_edit_mode() ){
				return true;
			}

			if( wp_doing_ajax() ){
				return true;
			}

			return false;
		}

		function preview__elements_before( $element ){

			global $post;

			if( ! $this->__force_preview($post) ){
				return;
			}

			$type = get_field( 'template_type', $post->ID );

			if( ! isset( $this->_pid[$type] ) ){

				// check if preview is set
				if( ($settings = $this->get_settings( $post->ID )) && !empty($settings['preview_id']) ){
					$this->_pid[$type] = $settings['preview_id'];
				}
				else {
					// get first result's ID
					if( $type ){
						$this->_pid[$type] = $this->default_preview_id( $type );
					}
				}
			}

			if( 'product' === $type ){
				if( isset($this->_pid[$type]) ){
					$GLOBALS['post'] = get_post( $this->_pid[$type] ); // WPCS: override ok.
					setup_postdata( $GLOBALS['post'] );
				}
			}

			else if( 'product-archive' === $type ){

				if( isset($this->_pid[$type]) ){

					$wp_query_posts = query_posts( [
						'post_type' => 'product',
						'tax_query' => [
							[
								'taxonomy' => 'product_cat',
								'field' => 'term_id',
								'terms' => (array) $this->_pid[$type],
								'operator' => 'IN'
							]
						]
					] );

					if( $count = count($wp_query_posts) ){
						wc_set_loop_prop( 'total', $count );
					}

				}
			}

		}

		function preview__elements_after( $element ){

			if( isset( $this->_pid['product'] ) ){
				wp_reset_postdata();
			}

			elseif( isset( $this->_pid['product-archive'] ) ){
				wp_reset_query();
			}

		}

		function default_preview_id( $type = '' ){

			$transient_name = self::$post_type . '_default_preview_id_for_';

			if( $type === 'product' ){

				$id = get_transient( $transient_name . $type );
				$id = 0;

				if( ! $id ){

					$latest_posts = get_posts( [
						'posts_per_page' => 1,
						'post_type' => $type,
					] );

					if ( ! empty( $latest_posts ) ) {
						$id = $latest_posts[0]->ID;
						set_transient( $transient_name . $type, $id, HOUR_IN_SECONDS * 12 );
					}

				}

				if( $id ){
					return $id;
				}

			}

			else if( $type === 'product-archive' ){

				$id = get_transient( $transient_name . $type );
				$id = 0;

				if( ! $id ){

					$latest_terms = (array) get_terms( [
						'number' => 1,
						'taxonomy' => 'product_cat',
						'hide_empty' => true,
						'fields' => 'ids'
					] );


					if ( ! empty( $latest_terms ) ) {
						$id = $latest_terms[0];
						set_transient( $transient_name . $type, $id, HOUR_IN_SECONDS * 12 );
					}

				}

				if( $id ){
					return $id;
				}

			}

			return 0;
		}

		function __before_render( $element ){

			$styles[] = reyTemplates()::ASSET_HANDLE;

			if( strpos($element->get_name(), 'reycore-woo-pdp') !== false ){
				$styles[] = 'rey-wc-product';
			}

			reyCoreAssets()->add_styles($styles);

			do_action('reycore/templates/elements/before_content_render', $this);
		}

		function __after_render( $element ){
			do_action('reycore/templates/elements/after_content_render', $this);
		}

		function __should_render( $element ){

			if( strpos($element->get_name(), 'reycore-woo-pdp') !== false ){

				global $product;

				$product = wc_get_product();

				if( empty( $product ) ){
					return false;
				}

			}

			return true;
		}

		function add_tab( $Controls_Manager ){

			$Controls_Manager::add_tab('info', 'Info');

		}

		function add_structured_data( $template_type ){

			if( 'product' === $template_type ){
				if( $product = wc_get_product() ){
					WC()->structured_data->generate_product_data($product);
				}
			}

			else if( 'product-archive' === $template_type ){
				WC()->structured_data->generate_website_data();
			}

		}

		function disable_sidebar( $status ){

			if( $status && $this->_built_with_elementor ){
				return false;
			}

			return $status;
		}

		public function post_row_actions( $actions, \WP_Post $post ) {

			global $current_screen;

			if ( ! $current_screen ) {
				return $actions;
			}

			if( ! ('edit' === $current_screen->base && self::$post_type === $current_screen->post_type) ){
				return $actions;
			}

			$link = add_query_arg([
					'action'         => 'elementor_library_direct_actions',
					'library_action' => 'export_template',
					'source'         => 'local',
					'_nonce'         => wp_create_nonce( 'elementor_ajax' ),
					'template_id'    => $post->ID,
				],
				admin_url( 'admin-ajax.php' )
			);

			$actions['export-template'] = sprintf( '<a href="%1$s">%2$s</a>', $link, __( 'Export Template', 'rey-core' ) );

			return $actions;
		}
	}
endif;
