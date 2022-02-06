<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined( 'ELEMENTOR_PRO_VERSION' ) && !class_exists('ReyCore_Compatibility__ElementorPro') ):
	/**
	 * Elementor PRO
	 *
	 * @since 1.3.0
	 */
	class ReyCore_Compatibility__ElementorPro
	{

		private $headers = [];
		private $footers = [];

		const NOTICE_TRANSIENT__HEADER = 'rey_epro__tb_header';
		const NOTICE_TRANSIENT__FOOTER = 'rey_epro__tb_footer';

		public function __construct()
		{
			add_action( 'elementor_pro/init', [ $this, 'on_elementor_pro_init' ] );
		}

		public function on_elementor_pro_init() {

			$this->misc();

			add_action( 'get_header', [ $this, 'handle_theme_support' ], 8 );
			add_filter( 'rey/header/header_classes', [$this, 'header_classes']);
			add_filter( 'rey/footer/footer_classes', [$this, 'footer_classes']);
			add_filter( 'reycore/customizer/pre_text/rey-hf-global-section', [$this, 'customizer_option_tweak'], 20, 2);

			// WooCommerce
			add_filter( 'reycore/woocommerce_cart_item_remove_link', [$this, 'remove_mini_cart_remove_btn']);
			add_action( 'elementor/widget/before_render_content', [$this, 'loop_props_in_elements']);
			add_action( 'elementor/widget/before_render_content', [$this, 'single_elements']);

			add_filter( 'reycore/ajaxfilters/js_params', [$this, 'handle_ajax_filters'], 20);
			add_filter( 'reycore/load_more_pagination_args', [$this, 'handle_pagination'], 20);
			add_filter('rey/site_content_classes', [$this, 'handle_product_post_classes']);

			add_action( 'elementor/element/single/document_settings/before_section_end', [$this, 'add_rey_pb_template_option']);
			add_action( 'elementor/element/archive/document_settings/before_section_end', [$this, 'add_rey_pb_template_option']);
			add_action( 'elementor/element/product/document_settings/before_section_end', [$this, 'add_rey_disable_grid_option'], 10);
			add_action( 'elementor/element/product-archive/document_settings/before_section_end', [$this, 'add_rey_disable_grid_option'], 10);
			add_action( 'elementor/element/single-post/document_settings/before_section_end', [$this, 'add_rey_disable_grid_option'], 10);

			add_action( 'elementor/theme/before_do_single', [$this, 'load_single_assets']);
			add_action( 'elementor/theme/before_do_archive', [$this, 'load_archive_assets']);

			add_action( 'elementor/theme/before_do_single', [$this, 'location_before']);
			add_action( 'elementor/theme/after_do_single', [$this, 'location_after']);
			add_action( 'elementor/theme/before_do_archive', [$this, 'location_before']);
			add_action( 'elementor/theme/after_do_archive', [$this, 'location_after']);

			add_action( 'wp', [$this, 'before_single_template']);

			// add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
			// add_action( 'elementor/frontend/widget/after_render', [$this, 'after_render'], 10);

		}

		private function get_theme_builder_module() {
			return \ElementorPro\Modules\ThemeBuilder\Module::instance();
		}

		private function get_theme_support_instance() {
			$module = $this->get_theme_builder_module();
			return $module->get_component( 'theme_support' );
		}

		public function handle_theme_support() {
			$module = $this->get_theme_builder_module();
			$conditions_manager = $module->get_conditions_manager();

			$this->headers = $conditions_manager->get_documents_for_location( 'header' );
			$this->footers = $conditions_manager->get_documents_for_location( 'footer' );

			$this->remove_action( 'header' );
			$this->remove_action( 'footer' );

			$this->add_support();
		}

		public function remove_action( $action ) {

			if( ! apply_filters('rey/elementor/pro/remove_instances', true) ){
				return;
			}

			$handler = 'get_' . $action;
			$instance = $this->get_theme_support_instance();
			remove_action( $handler, [ $instance, $handler ] );
		}

		public function do_header(){
			$module = $this->get_theme_builder_module();
			$location_manager = $module->get_locations_manager();
			$location_manager->do_location( 'header' );
		}

		public function do_footer(){
			$module = $this->get_theme_builder_module();
			$location_manager = $module->get_locations_manager();
			$location_manager->do_location( 'footer' );
		}

		public function add_support(){

			if ( !empty( $this->headers ) && function_exists('rey__header__content') ) {

				if( ! get_transient(self::NOTICE_TRANSIENT__HEADER) ){
					set_transient(self::NOTICE_TRANSIENT__HEADER, true, MONTH_IN_SECONDS);
				}

				remove_action('rey/header/content', 'rey__header__content');
				add_action('rey/header/content', [$this, 'do_header']);
				add_filter('reycore/header/display', '__return_false');
			}
			else {
				delete_transient(self::NOTICE_TRANSIENT__HEADER);
			}

			if ( !empty( $this->footers ) && function_exists('rey_action__footer__content') ) {

				if( ! get_transient(self::NOTICE_TRANSIENT__FOOTER) ){
					set_transient(self::NOTICE_TRANSIENT__FOOTER, true, MONTH_IN_SECONDS);
				}

				remove_action('rey/footer/content', 'rey_action__footer__content');
				add_action('rey/footer/content', [$this, 'do_footer']);
				add_filter('reycore/footer/display', '__return_false');
			}
			else {
				delete_transient(self::NOTICE_TRANSIENT__FOOTER);
			}
		}

		public function header_classes($classes){

			if( !empty( $this->headers ) && isset($classes['layout']) ){
				$classes['layout'] = 'rey-siteHeader--custom';
			}

			return $classes;
		}

		public function footer_classes($classes){

			if( !empty( $this->footers ) && isset($classes['layout']) ){
				$classes['layout'] = 'rey-siteFooter--custom';
			}

			return $classes;
		}

		function customizer_option_tweak($text, $name){

			if ( get_transient(self::NOTICE_TRANSIENT__HEADER) && $name === 'header_layout_type' ) {
				$text .= sprintf('<p class="rey-precontrol-wrap">%s</p>', __('This option is not available because <strong>Elementor Pro</strong> has a <em><strong>Theme Builder</strong> - Header template</em> published which overrides these options.', 'rey-core'));
			}

			else if ( get_transient(self::NOTICE_TRANSIENT__FOOTER) && $name === 'footer_layout_type' ) {
				$text .= sprintf('<p class="rey-precontrol-wrap">%s</p>', __('This option is not available because <strong>Elementor Pro</strong> has a <em><strong>Theme Builder</strong> - Footer template</em> published which overrides these options.', 'rey-core'));
			}

			return $text;
		}

		function misc(){

			require trailingslashit( REY_CORE_COMPATIBILITY_DIR ) . 'elementor-pro/woocommerce-widgets.php';

			// make search form element use the product catalog template
			add_action('elementor_pro/search_form/after_input', function(){
				echo '<input type="hidden" name="post_type" value="product">';
			});

		}

		function add_rey_pb_template_option($element){
			$element->add_control(
				'rey_page_builder',
				[
					'label' => esc_html__( 'Enable "Rey - Page Builder" template', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
		}

		function add_rey_disable_grid_option($element){

			$element->add_control(
				'rey_page_template_canvas',
				[
					'label' => esc_html__( 'Grid Type', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Elementor Default Grid', 'rey-core' ),
						'rey'  => esc_html__( 'Rey Grid', 'rey-core' ),
					],
				]
			);

		}

		function is_rey_pb() {

			$meta = get_post_meta( get_the_ID(), '_elementor_page_settings', true );

			if( isset($meta['rey_page_builder']) && $meta['rey_page_builder'] === 'yes' ){
				return true;
			}

			return false;
		}

		function location_before( $instance ){

			if( $this->is_rey_pb() && function_exists('rey_action__before_site_container') ){
				rey_action__before_site_container();
			}

		}

		function location_after( $instance ){

			if( $this->is_rey_pb() && function_exists('rey_action__after_site_container') ){
				rey_action__after_site_container();
			}

			add_filter( 'reycore/loop_components', [$this, 'handle_ajax_filters_button_mobile'], 20);
		}

		function load_single_assets(){
			reyCoreAssets()->add_styles(['rey-wc-product', 'rey-wc-product-gallery', 'rey-wc-product-mobile-gallery', 'rey-splide']);
		}

		function load_archive_assets(){
			if( class_exists('ReyCore_WooCommerce_Loop') ){
				ReyCore_WooCommerce_Loop::getInstance()->load_scripts();
			}
		}

		function before_single_template(){

			$manager = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' )->get_conditions_manager();

			$template = false;

			if( $single = $manager->get_documents_for_location( 'single' ) ){
				$template = end($single);
			}

			if( $archive = $manager->get_documents_for_location( 'archive' ) ){
				$template = end($archive);
			}

			if( ! $template ){
				return;
			}

			if( $template_id = $template->get_post()->ID ){
				if( ($meta = get_post_meta( $template_id, '_elementor_page_settings', true )) && isset($meta['rey_page_template_canvas']) ){

					if( $meta['rey_page_template_canvas'] === 'yes' ){
						add_filter( 'reycore/elementor/load_grid', '__return_false', 100);
					}
					else if ($meta['rey_page_template_canvas'] === 'rey') {

						add_action( 'elementor/page_templates/header-footer/before_content', function(){
							if (function_exists('rey_action__before_site_container')){
								rey_action__before_site_container();
							}
						} );

						add_action( 'elementor/page_templates/header-footer/after_content', function(){
							if (function_exists('rey_action__after_site_container')){
								rey_action__after_site_container();
							}
						} );

					}
				}
			}
		}

		/**
		 * WooCommerce
		 */


		/**
		 * Run loop props for Upsell & Related
		 *
		 * @since 1.3.2
		 */
		function loop_props_in_elements( $element ){

			$widgets = [
				'woocommerce-products',
				'woocommerce-product-related',
				'woocommerce-product-upsell',
				'wc-archive-products',
			];

			$widget_name = $element->get_unique_name();

			if( ! in_array($widget_name, $widgets) ){
				return;
			}

			if( !class_exists('ReyCore_WooCommerce_Loop') ){
				return;
			}

			$loop_instance = ReyCore_WooCommerce_Loop::getInstance();

			$loop_instance->set_loop_props();
			$loop_instance->components_add_remove();

			$settings = $element->get_settings_for_display();


			$widget_name_clean = str_replace('-', '_', $widget_name);
			$widget_function = "el_widget__{$widget_name_clean}";

			if( method_exists($this, $widget_function ) ){
				$this->$widget_function($settings);
			}

			// Make sure to change cols
			if( isset($settings['columns']) ){
				wc_set_loop_prop('columns', $settings['columns']);
			}

			add_filter('reycore/woocommerce/columns', function($breakpoints) use ($settings){

				$breakpoints['tablet'] = 3;
				$breakpoints['mobile'] = 2;

				if( isset($settings['columns']) && $desktop = $settings['columns']){
					$breakpoints['desktop'] = $desktop;
				}

				if( isset($settings['columns_tablet']) && $tablet = $settings['columns_tablet']){
					$breakpoints['tablet'] = $tablet;
				}

				if( isset($settings['columns_mobile']) && $mobile = $settings['columns_mobile']){
					$breakpoints['mobile'] = $mobile;
				}

				return $breakpoints;
			});

			// Allow product classes in E templates
			add_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', function ( $status ){
				if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
					return false;
				}
				return $status;
			}, 10 );

		}

		/**
		 * Fix for EPRO custom mini-cart skin
		 *
		 * @since 1.3.1
		 */
		public function remove_mini_cart_remove_btn( $html ){

			$use_mini_cart_template = get_option( 'elementor_use_mini_cart_template', 'no' );

			if ( 'yes' === $use_mini_cart_template ) {
				return false;
			}

			return $html;
		}

		function single_elements( $element )
		{
			if( $element->get_unique_name() === 'woocommerce-product-images' ){
				reyCoreAssets()->add_scripts('reycore-elementor-elem-woo-prod-gallery');
			}
			elseif( $element->get_unique_name() === 'woocommerce-product-add-to-cart' ){
				reyCoreAssets()->add_styles('rey-wc-product');
				reyCoreAssets()->add_scripts(['reycore-wc-product-page-general', 'reycore-wc-product-page-qty-controls']);
			}
		}

		function handle_product_post_classes($classes){

			if( isset($classes['template_type']) && $classes['template_type'] === '--tpl-elementor_header_footer' ){
				$classes['post_type'] = 'product';
			}

			return $classes;
		}

		function handle_pagination($params){

			if( isset($params['target']) ){
				$params['target'] = $params['target'] . ', .elementor-widget-wc-archive-products ul.products, .elementor.elementor-location-archive .reyEl-productGrid.--show-header ul.products';
			}

			return $params;
		}

		function handle_ajax_filters($params){

			if( isset($params['shop_loop_container']) ){
				$params['shop_loop_container'] = $params['shop_loop_container'] . ', .elementor-widget-wc-archive-products .reyajfilter-before-products, .elementor.elementor-location-archive .reyajfilter-before-products, .reyEl-productGrid.--show-header .reyajfilter-before-products';
			}
			if( isset($params['not_found_container']) ){
				$params['not_found_container'] = $params['not_found_container'] . ', .elementor-widget-wc-archive-products .reyajfilter-before-products, .elementor.elementor-location-archive .reyajfilter-before-products, .reyEl-productGrid.--show-header .reyajfilter-before-products';
			}

			return $params;
		}

		function handle_ajax_filters_button_mobile($components){
			$components['filter_button'] = get_theme_mod('ajaxfilter_shop_sidebar_mobile_offcanvas', true);
			return $components;
		}

	}

	new ReyCore_Compatibility__ElementorPro;
endif;
