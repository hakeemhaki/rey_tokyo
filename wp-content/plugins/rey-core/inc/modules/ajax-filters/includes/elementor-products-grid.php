<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}
if( !class_exists('ReyAjaxFilters_ProductGrid') ):
	/**
	 * Handler for Product Grid element, integration
	 * with product filters
	 *
	 * @since 1.5.4
	 */
	class ReyAjaxFilters_ProductGrid
	{
		private static $_instance = null;

		public $aj_instance;
		public $widget_settings = [];
		public $element_query_vars = [];
		public $pg_settings = [];
		public $filters_are_supported;

		const META_KEY = '_rey_elementor__product_grid__use_filter';

		private function __construct()
		{
			add_action( 'wp', [ $this, 'set_vars']);
			add_action( 'elementor/editor/after_save', [ $this, 'product_grid_filters']);
			add_action( 'elementor/element/reycore-product-grid/section_layout/before_section_end', [$this,'adds_element_options']);
			add_filter( 'reycore/elementor/product_grid/loop_components', [$this, 'pg_components'], 10, 2);
			add_filter( 'reycore/elementor/product_grid/query_args', [$this, 'set_query_args_for_element__from_url_params'], 10, 3);
			add_filter( 'reycore/ajaxfilters/query_url', [$this, 'check_pg_options']);
			add_filter( 'reycore/ajaxfilters/query/on_sale', [$this, 'check_on_sale']);
			add_filter( 'reycore/ajaxfilters/query/featured', [$this, 'check_featured']);
			add_filter( 'reycore/woocommerce_get_filtered_term_product_counts_query', [$this, 'fix_sale_counters']);
			add_filter( 'body_class', [$this, 'body_classes']);
			add_filter( 'reycore/woocommerce/reset_filters_link', [$this, 'reset_filters_link']);
			add_filter( 'reycore/woocommerce/filter_button/custom_sidebar', [$this, 'filter_button_custom_sidebar']);
			add_filter( 'theme_mod_ajaxfilter_active_position', [$this, 'show_active_filters']);
			add_action( 'reycore/ajaxfilters/before_widget_controls', [$this, 'notice_elementor_mode_widgets']);
			// add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render']);
			add_filter( 'reycore/ajaxfilters/tax_query', [$this, 'term_count_tax_query']);
			add_filter( 'reycore/ajaxfilters/meta_query', [$this, 'term_count_meta_query']);
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyAjaxFilters_ProductGrid
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		public function set_vars(){

			$this->aj_instance = reyAjaxFilters();

			if( ! ($this->aj_instance->filters_are_supported && $this->aj_instance->is_filter_page) ){
				return;
			}

			$this->pg_settings = $this->aj_instance->pg_settings;
			$this->set_query_vars();
			$this->aj_instance->set_chosen_filters();
			$this->set_filter_widgets_query_type();
		}

		/**
		 * Checks if PG query is for discounted products only
		 */
		public function check_on_sale($status = false){
			if( ($settings = $this->pg_settings) && isset($settings['query_type']) && $settings['query_type'] === 'sale' ){
				return true;
			}
			return $status;
		}

		/**
		 * Checks if PG is featured
		 */
		public function check_featured($status){
			if( ($settings = $this->pg_settings) && isset($settings['query_type']) && $settings['query_type'] === 'featured' ){
				return true;
			}
			return $status;
		}

		/**
		 * If URL has filters coming from widgets,
		 * modify the product grid query
		 */
		public function set_query_args_for_element__from_url_params( $query_vars, $id, $settings )
		{

			if( !(isset($settings['page_uses_filters']) && $settings['page_uses_filters']) ){
				return $query_vars;
			}

			if( !empty($this->aj_instance->chosen_filters) && isset($this->aj_instance->chosen_filters['chosen'] ) ){

				$vars_from_url = [
					'meta_query' => $this->aj_instance->queryForMeta(),
					'tax_query' => $this->aj_instance->queryForTax(),
					'post__in' => $this->aj_instance->queryForPostIn(),
					'post__not_in' => $this->aj_instance->queryForPostNotIn()
				];

				$query_vars = reycore__wp_parse_args( $vars_from_url, $query_vars );

				if( isset($this->aj_instance->chosen_filters['active_filters']['orderby']) && $orderby = $this->aj_instance->chosen_filters['active_filters']['orderby'] ){
					if( strpos($orderby, 'price') !== false ){
						$query_vars['orderby'] = 'price';
						$query_vars['order'] = $orderby === 'price-desc' ? 'DESC' : 'ASC';
					}
				}

				return $query_vars;
			}


			return $query_vars;
		}

		/**
		 * Gets element settings, and prepares the active filters
		 * for widgets, passing settings as url query params
		 */
		public function check_pg_options( $vars ) {
			return $vars + $this->element_query_vars;
		}

		/**
		 * Set query type for PG element to be passed
		 * on to filters.
		 **/
		public function set_filter_widgets_query_type() {

			$widgets = [
				'category'  => 'reyajfilter-category-filter',
				'attribute' => 'reyajfilter-attribute-filter',
				'tag'       => 'reyajfilter-tag-filter',
			];

			foreach ($widgets as $key => $widget) {

				$widget_id = 'widget_' . $widget;
				$widget_settings = get_option( $widget_id );

				if( !empty($widget_settings) ) {

					unset( $widget_settings['_multiwidget'] );

					if( sizeof($widget_settings) > 1 ){
						foreach ($widget_settings as $i => $value) {
							if( isset( $value['attr_name'] ) && isset( $value['query_type'] ) ){
								$this->widget_settings[$key][$i] = [
									$value['attr_name'] => $value['query_type'] === 'and' ? 'a' : 'o'
								];
							}
						}
					}
					else {
						if( isset($widget_settings[1]) && isset($widget_settings[1]['query_type']) ){
							$this->widget_settings[$key] = $widget_settings[1]['query_type']  === 'and' ? 'a' : 'o';
						}
					}
				}
			}
		}

		/**
		 * Set query vars from PG element settings to be applied in
		 * ajax filter widgets
		 **/
		public function set_query_vars(){

			if( ! ($settings = $this->pg_settings) ){
				return;
			}

			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $query);

			if(  ! reycore__preg_grep_keys('/^attr/', $query) && isset($settings['attribute']) && $taxonomy_name = $settings['attribute'] ){
				if( isset($settings['attribute__' . $taxonomy_name]) && $attribute_terms = $settings['attribute__' . $taxonomy_name] ){
					$attr_ids = [];
					foreach ($attribute_terms as $k => $v) {

						$wc_tax_name = $taxonomy_name;

						if( strpos($taxonomy_name, 'pa_') === false ){
							$wc_tax_name = wc_attribute_taxonomy_name($taxonomy_name);
						}

						$term = get_term_by('slug', $v, $wc_tax_name);

						if( isset($term->term_id) ){
							$attr_ids[] = $term->term_id;
						}
					}
					$attr_query_type = isset($this->widget_settings['attribute']) && isset($this->widget_settings['attribute'][$taxonomy_name]) ? $this->widget_settings['attribute'][$taxonomy_name] : 'o';
					$this->element_query_vars["attr{$attr_query_type}-" . $taxonomy_name ] = implode(',', $attr_ids);
				}
			}

			if( ! reycore__preg_grep_keys('/product-cat/', $query) && isset($settings['categories']) && $categories = $settings['categories'] ){
				$category_ids = [];
				foreach ($categories as $k => $v) {
					$term = get_term_by('slug', $v, 'product_cat');
					if( isset($term->term_id) ){
						$category_ids[] = $term->term_id;
					}
				}
				$cat_query_type = isset($this->widget_settings['category']) ? $this->widget_settings['category'] : 'o';
				$this->element_query_vars['product-cat' . $cat_query_type ] = implode(',', $category_ids);
			}

			if( ! reycore__preg_grep_keys('/product-tag/', $query) && isset($settings['tags']) && $tags = $settings['tags'] ){
				$tags_ids = [];
				foreach ($tags as $k => $v) {
					$term = get_term_by('slug', $v, 'product_tag');
					if( isset($term->term_id) ){
						$tags_ids[] = $term->term_id;
					}
				}
				$tag_query_type = isset($this->widget_settings['tag']) ? $this->widget_settings['tag'] : 'o';
				$this->element_query_vars['product-tag' . $tag_query_type ] = implode(',', $tags_ids);
			}

			if( $this->check_on_sale() ){
				$this->element_query_vars['on-sale'] = true;
			}

			$this->element_query_vars['pg'] = true;
		}

		/**
		 * Render some attributes before rendering
		 **/
		function before_render( $element )
		{

			if( $element->get_unique_name() !== 'reycore-product-grid' ){
				return;
			}

			$settings = $element->get_settings_for_display();

			if( $settings['page_uses_filters'] !== 'yes' ){
				return;
			}

			if( is_array($this->element_query_vars) && !empty($this->element_query_vars) ){
				$element->add_render_attribute( 'wrapper', 'data-pg-query', esc_attr( add_query_arg($this->element_query_vars, '') ) );
			}
		}

		/**
		 * Saves meta key that enables filters for a specific page where
		 * it's enabled in the Product grid element settings
		 */
		public function product_grid_filters( $post_id ){

			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( $document ) {
				$data = $document->get_elements_data();
			}

			if ( empty( $data ) ) {
				return;
			}

			$pg_settings = [];

			\Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) use (&$pg_settings) {
				if ( !empty( $element['widgetType'] ) && $element['widgetType'] === 'reycore-product-grid' ) {
					$pg_settings[] = $element['settings'];
				}
			});

			$settings_data = '';

			// always get the first one
			if( !empty( $pg_settings ) ) {

				if( isset( $pg_settings[0]['page_uses_filters'] ) && $pg_settings[0]['page_uses_filters'] === 'yes' ){
					$settings_data = $pg_settings[0];
				}

				update_post_meta( $post_id, self::META_KEY, $settings_data );
				return;
			}

			// if empty settings, try deleting any existing traces
			delete_post_meta( $post_id, self::META_KEY, $settings_data );
		}

		/**
		 * Filter term count tax query
		 */
		public function term_count_tax_query( $tax_query ){

			if( $this->aj_instance && $this->aj_instance->is_filter_page ){
				return $this->aj_instance->queryForTax();
			}

			return $tax_query;
		}

		/**
		 * Filter term count meta query
		 */
		public function term_count_meta_query( $meta_query ){

			if( $this->aj_instance && $this->aj_instance->is_filter_page ){
				return $this->aj_instance->queryForMeta();
			}

			return $meta_query;
		}

		public function fix_sale_counters( $query ){

			if( ! ($this->aj_instance && $this->aj_instance->is_filter_page) ){
				return $query;
			}

			if( $this->check_on_sale() ){
				global $wpdb;
				$on_sale = sprintf("{$wpdb->posts}.ID IN (%s)", trim(implode(',', array_map('absint', reyajaxfilters_get_sale_products()))));
				$query['where'] .= ' AND ' . $on_sale;
			}

			return $query;
		}

		public function adds_element_options( $element ){

			$element->start_injection( [
				'of' => 'show_view_selector',
			] );

			$element->add_control(
				'page_uses_filters',
				[
					'label' => __( 'Page uses filters', 'rey-core' ),
					'description' => __( 'If enabled, filter widgets can be used for this page. Be sure to add Rey Filter widgets first in any of the sidebars in Widgets page.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'paginate!' => '',
						'show_header!' => '',
						'_skin' => '',
					],
				]
			);

			$element->add_control(
				'show_filter_button',
				[
					'label' => __( 'Show Filter Button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'paginate!' => '',
						'show_header!' => '',
						'_skin' => '',
						'page_uses_filters!' => '',
					],
				]
			);

			global $wp_registered_sidebars;

			$options = [];

			if ( ! $wp_registered_sidebars ) {
				$options[''] = __( 'No sidebars were found', 'rey-core' );
			} else {
				$options[''] = __( 'Choose Sidebar', 'rey-core' );

				foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
					$options[ $sidebar_id ] = $sidebar['name'];
				}
			}

			$element->add_control( 'filter_button_sidebar', [
				'label' => __( 'Sidebar to Open', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $options,
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
					'_skin' => '',
					'page_uses_filters!' => '',
					'show_filter_button!' => '',
				],
			] );

			$element->add_control(
				'show_active_filters',
				[
					'label' => esc_html__( 'Show Active Filters', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default'     => '',
					'options'     => [
						'' => esc_html__( 'None (manually added)', 'rey-core' ),
						'above_grid' => esc_html__( 'Above product grid', 'rey-core' ),
						'above_header' => esc_html__( 'Above grid header', 'rey-core' ),
					],
					'condition' => [
						'paginate!' => '',
						'show_header!' => '',
						'_skin' => '',
						'page_uses_filters!' => '',
					],
				]
			);

			$element->end_injection();
		}

		/**
		 * Filter product grid component flags
		 *
		 * @since 1.5.3
		 */
		public function pg_components( $loop_components, $settings ){

			if( $settings['show_header'] === 'yes' &&
				(isset($settings['page_uses_filters']) && $settings['page_uses_filters'] === 'yes') &&
				(isset($settings['show_filter_button']) && $settings['show_filter_button'] === 'yes') ){
				$loop_components['filter_button'] = true;
			}

			return $loop_components;
		}

		public function body_classes( $classes ){

			if( isset($this->aj_instance->filters_are_supported) && $this->aj_instance->filters_are_supported ){
				$classes[] = 'woocommerce';
			}

			return $classes;
		}

		public function reset_filters_link( $link ){

			if( $this->aj_instance->filters_are_supported && get_post_type() === 'page' ){
				$link = get_permalink();
			}

			return $link;
		}

		public function filter_button_custom_sidebar( $opt ){

			if( ($settings = $this->pg_settings) &&
				isset($settings['page_uses_filters']) && $settings['page_uses_filters'] !== '' &&
				isset($settings['show_filter_button']) && $settings['show_filter_button'] !== '' &&
				isset($settings['filter_button_sidebar']) && ($filter_button_sidebar = $settings['filter_button_sidebar'])
			){
				return $filter_button_sidebar;
			}

			return $opt;
		}

		public function show_active_filters( $opt ){

			if( ($settings = $this->pg_settings) &&
				isset($settings['page_uses_filters']) && $settings['page_uses_filters'] !== '' &&
				isset($settings['show_active_filters']) && ($show_active_filters = $settings['show_active_filters'])
			){
				return $show_active_filters;
			}

			return $opt;
		}

		function notice_elementor_mode_widgets(){

			if( ! (isset($_REQUEST['action']) && $_REQUEST['action'] === 'elementor_ajax') ){
				return;
			}

			printf('<div class="elementor-control-raw-html rey-raw-html --warning">%s</div>', __('Please don\'t add widgets directly into the page and instead use the <strong>Sidebar</strong> Element.', 'Elementor control label', 'rey-core'));
		}

	}

	function ReyAjaxFilters_ProductGrid(){
		return ReyAjaxFilters_ProductGrid::getInstance();
	}

	ReyAjaxFilters_ProductGrid();

endif;
