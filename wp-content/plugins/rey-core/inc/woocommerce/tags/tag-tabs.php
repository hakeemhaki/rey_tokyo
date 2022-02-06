<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Tabs') ):

	class ReyCore_WooCommerce_Tabs
	{

		private static $_instance = null;

		private function __construct(){
			add_action( 'init', [$this, 'initialize']);
		}

		function initialize(){

			add_filter( 'wp', [$this, 'late_init']);
			add_filter( 'woocommerce_product_additional_information_heading', [$this,'rename_additional_info_panel'], 10);
			add_filter( 'woocommerce_product_description_heading', [$this,'rename_description_title'], 10);
			add_filter( 'woocommerce_product_tabs', [$this, 'manage_tabs'], 20);
			add_filter( 'wc_product_enable_dimensions_display', [$this, 'disable_specifications_dimensions'], 10);
			// add_action( 'wp_footer', [$this, 'reviews__start_open'], 999);
			add_filter( 'rey/woocommerce/product_panels_classes', [$this, 'add_blocks_class']);
			add_filter( 'acf/load_value/key=field_5ecae99f56e6d', [$this, 'load_custom_tabs'], 10, 3);
			add_action( 'woocommerce_after_single_product_summary', [$this, 'move_reviews_tab_outside'], 10 );
			add_action( 'woocommerce_single_product_summary', [$this, 'prevent_short_desc_if_in_accordions'], 0 );
			add_action( 'reycore/woocommerce/product/tabs/before', [$this, 'remove_tabs_titles'] );

			// add attr description
			if( get_theme_mod('woocommerce_product_page_attr_desc', false) ){
				add_filter('woocommerce_attribute', [$this, 'add_attribute_descriptions'], 20, 2);
			}

			$this->add_summary_accordion_tabs();
		}

		/**
		 * Add Information Panel
		 *
		 * @since 1.0.0
		 **/
		function information_panel_content()
		{
			echo reycore__parse_text_editor( reycore__get_option( 'product_info_content' ) );

			if( current_user_can('administrator') && apply_filters('reycore/woocommerce/tabs_blocks/show_info_help', true) ){
				echo '<p class="__notice">';
					echo reycore__get_svg_icon(['id'=>'rey-icon-help']);
					printf(
						__('&nbsp; <small>If you want to edit this text, access <strong><a href="%s" target="_blank">Customizer > WooCommerce > Product page - Tabs/Blocks</a></strong> and you should be able to find <strong>Information</strong> editor in there. This notice is only visible for Administrators.</small>', 'rey-core'),
						add_query_arg( ['autofocus[section]' => 'shop_product_section_tabs'], admin_url( 'customize.php' ) ) );
				echo '</p>';
			}

		}

		/**
		 * Rename Description title
		 *
		 * @since 1.6.10
		 **/
		function rename_description_title($heading)
		{
			$title = get_theme_mod('product_content_blocks_title', '');

			// disable title
			if( $title == '0' ){
				return false;
			}

			// check if custom title
			if( $title ){
				return $title;
			}

			return $heading;
		}


		/**
		 * Rename Additional Information Panel
		 *
		 * @since 1.0.0
		 **/
		function rename_additional_info_panel($heading)
		{

			$title = get_theme_mod('single_specifications_title', '');

			// disable title
			if( $title == '0' ){
				return false;
			}

			// check if custom title
			if( $title ){
				return $title;
			}

			return esc_html__( 'Specifications', 'rey-core' );
		}

		function manage_tabs( $tabs ){

			$type = get_theme_mod('product_content_layout', 'blocks');

			if( $type === 'tabs' ){
				reyCoreAssets()->add_scripts(['reycore-wc-product-page-mobile-tabs']);
			}

			/**
			 * Adds Information Block/Tab
			 */

			$ip = reycore__get_option('product_info', '');

			if( ($ip = reycore__get_option('product_info', '')) && ($ip === true || $ip === '1' || $ip === 'custom') ) {

				$info_tab_title = __( 'Information', 'rey-core' );

				if( $custom_info_tab_title = get_theme_mod('single__product_info_title', '') ){
					$info_tab_title = $custom_info_tab_title;
				}

				$tabs['information'] = [
					'title'    => $info_tab_title,
					'priority' => absint(get_theme_mod('single_custom_info_priority', 15)),
					'callback' => [$this, 'information_panel_content'],
				];

			}

			// change priorities
			foreach ([
				'description' => get_theme_mod('single_description_priority', 10),
				'additional_information' => get_theme_mod('single_specs_priority', 20),
				'reviews' => get_theme_mod('single_reviews_priority', 30),
			] as $key => $value) {
				if( isset($tabs[$key]) && isset($tabs[$key]['priority']) ){
					$tabs[$key]['priority'] = absint($value);
				}
			}

			// Description title
			if( $desc_title = get_theme_mod('product_content_blocks_title', '') ){
				$tabs['description']['title'] = $desc_title;
			}

			// Specs title
			if( $specs_title = get_theme_mod('single_specifications_title', '') ){
				$tabs['additional_information']['title'] = $specs_title;
			}

			// disable specs tab
			if( ! get_theme_mod('single_specifications_block', true) || get_field('single_specifications_block') === false ){
				unset( $tabs['additional_information'] );
			}

			// disable reviews tab, to print outside
			if( get_theme_mod('single_tabs__reviews_outside', false) && $type === 'tabs' ){
				unset( $tabs['reviews'] );
			}

			// disable description
			if( ! get_theme_mod('product_tab_description', true) ){
				unset( $tabs['description'] );
			}

			/**
			 * Custom Tabs
			 */
			$custom_tabs = get_theme_mod('single__custom_tabs', '');

			if( is_array($custom_tabs) && !empty($custom_tabs) && class_exists('ACF') ){

				$custom_tabs_content = get_field('product_custom_tabs');

				foreach ($custom_tabs as $key => $c_tab) {

					$default_content = isset($c_tab['content']) ? reycore__parse_text_editor($c_tab['content']) : '';

					$tab_content = isset($custom_tabs_content[$key]['tab_content']) && !empty($custom_tabs_content[$key]['tab_content']) ? reycore__parse_text_editor( $custom_tabs_content[$key]['tab_content'] ) : $default_content;

					if( empty($tab_content) ){
						continue;
					}

					$title = isset($custom_tabs_content[$key]['tab_title']) && !empty($custom_tabs_content[$key]['tab_title']) && ! apply_filters('reycore/woocommerce/custom_tabs/force_default_title', false) ? reycore__parse_text_editor( $custom_tabs_content[$key]['tab_title'] ) : $c_tab['text'];

					$tabs['custom_tab_' . $key] = [
						'title' => $title,
						'priority' => absint($c_tab['priority']),
						'callback' => function() use ($tab_content) {
							echo reycore__parse_text_editor($tab_content);
						},
						'type' => 'custom'
					];

				}
			}

			return $tabs;
		}


		function move_reviews_tab_outside() {

			$maybe[] = get_theme_mod('single_tabs__reviews_outside', false) && get_theme_mod('product_content_layout', 'blocks') === 'tabs' && wc_reviews_enabled();

			if( $product = wc_get_product() ){
				$maybe[] = $product->get_reviews_allowed();
			}

			if( in_array(false, $maybe, true) ){
				return;
			}

			reycore__get_template_part('template-parts/woocommerce/single-block-reviews');
		}

		function wrap_specifications_block(){
			echo '<div class="rey-summarySpecs">';
			woocommerce_product_additional_information_tab();
			echo '</div>';
		}


		/**
		 * Move Specifications / Additional Information block/tab into Summary
		 *
		 * @since 1.6.7
		 */
		function late_init(){

			if( is_product() ){
				add_filter( 'the_content', [$this, 'add_description_toggle']);
			}

			$this->move_specs_block();
		}

		function move_specs_block(){

			if( ! get_theme_mod('single_specifications_block', true) ){
				return;
			}

			if( ! ($pos = get_theme_mod('single_specifications_position', '')) ){
				return;
			}

			// move specifications / additional in summary
			add_action( 'woocommerce_single_product_summary', [$this, 'wrap_specifications_block'], $pos );

			add_filter( 'woocommerce_product_tabs', function( $tabs ) {
				unset( $tabs['additional_information'] );
				return $tabs;
			}, 99 );

		}


		function disable_specifications_dimensions(){
			return get_theme_mod('single_specifications_block_dimensions', true);
		}

		function remove_tabs_titles( $layout ){

			if( 'tabs' !== $layout ){
				return;
			}

			if( get_theme_mod('product_content_layout', 'blocks') !== 'tabs' ){
				return;
			}

			if( ! get_theme_mod('product_content_tabs_disable_titles', true) ){
				return;
			}

			add_filter('woocommerce_product_description_heading', '__return_false');
			add_filter('woocommerce_product_additional_information_heading', '__return_false');
			add_filter('woocommerce_post_class', function($classes){
				$classes['remove-titles'] = '--tabs-noTitles';
				return $classes;
			});
		}

		/**
		 * Customize product page's blocks
		 *
		 * @since 1.0.12
		 **/
		function add_blocks_class( $classes )
		{
			if( get_theme_mod('product_content_layout', 'blocks') === 'blocks' ){
				$classes[] = get_theme_mod('product_content_blocks_desc_stretch', false) ? '--stretch-desc' : '';
			}

			return $classes;
		}

		function add_description_toggle($content){

			if( get_theme_mod('product_content_blocks_desc_toggle', false) ){
				return sprintf(
					'<div class="rey-prodDescToggle u-toggle-text-next-btn %s">%s</div><button class="btn btn-line-active"><span data-read-more="%s" data-read-less="%s"></span></button>',
					apply_filters('reycore/productdesc/mobile_only', false) ? '--mobile' : '',
					$content,
					esc_html_x('Read more', 'Toggling the product excerpt in Compact layout.', 'rey-core'),
					esc_html_x('Less', 'Toggling the product excerpt in Compact layout.', 'rey-core')
				);
			}

			return $content;
		}

		function load_custom_tabs($value, $post_id, $field) {

			if ($value !== false) {
				return $value;
			}

			$tabs = get_theme_mod('single__custom_tabs', '');

			if( is_array($tabs) && !empty($tabs) ){
				$value = [];
				foreach ($tabs as $key => $tab) {
					$value[]['field_5ecae9c356e6e'] = $tab['text'];
				}
			}

			return $value;
		}

		function display_summary_accordion_tabs(){

			add_filter('woocommerce_product_description_heading', '__return_false');
			add_filter('woocommerce_product_additional_information_heading', '__return_false');
			add_filter('reycore/woocommerce/blocks/headings', '__return_false');

			reycore__get_template_part('template-parts/woocommerce/single-accordion-tabs');

			remove_filter('woocommerce_product_description_heading', '__return_false');
			remove_filter('woocommerce_product_additional_information_heading', '__return_false');
			remove_filter('reycore/woocommerce/blocks/headings', '__return_false');

			reyCoreAssets()->add_scripts('reycore-wc-product-page-accordion-tabs');
		}

		function add_summary_accordion_tabs(){

			if( empty( get_theme_mod('single__accordion_items', []) ) ){
				return;
			}

			$position = absint( get_theme_mod('single__accordion_position', '39') );

			add_action('woocommerce_single_product_summary', [$this, 'display_summary_accordion_tabs'], $position);
		}

		function add_attribute_descriptions($value, $attribute){

			if( $terms_ids = $attribute->get_options() ){

				$taxonomy = str_replace('pa_', '', $attribute->get_taxonomy());

				foreach ($terms_ids as $term_id) {

					if( $desc = term_description( $term_id, $taxonomy) ){
						$desc_no_tags = strip_tags($desc);
						$new_content = '<br><small class="woocommerce-product-attributes-item__desc">' . $desc_no_tags . '</small>';
						$value = str_replace('</p>', $new_content . '</p>', $value);
					}
				}
			}

			return $value;
		}

		public static function determine_acc_tab_to_start_opened( $index ){

			$acc_to_start_opened = [];

			if( get_theme_mod('single__accordion_first_active', false) ){
				$acc_to_start_opened[] = 1;
			}

			$should_start_opened = apply_filters('reycore/woocommerce/single_acc_tabs/start_opened', $acc_to_start_opened);

			// for easier, human readability
			$should_start_opened_minus = [];
			foreach ($should_start_opened as $key => $value) {
				$should_start_opened_minus[] = $value - 1;
			}

			return in_array($index, $should_start_opened_minus, true);
		}

		public static function render_short_description(){

			add_filter('theme_mod_product_short_desc_enabled', '__return_true');
			add_filter('theme_mod_product_short_desc_toggle_v2', '__return_false');

			woocommerce_template_single_excerpt();

			remove_filter('theme_mod_product_short_desc_enabled', '__return_true');
			remove_filter('theme_mod_product_short_desc_toggle_v2', '__return_false');

		}

		public function prevent_short_desc_if_in_accordions(){

			$accordion_tabs = get_theme_mod('single__accordion_items', []);

			if( empty(wp_list_filter($accordion_tabs, ['item' => 'short_desc'])) ){
				return;
			}

			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 35 );
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return Base
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_WooCommerce_Tabs::getInstance();

endif;
