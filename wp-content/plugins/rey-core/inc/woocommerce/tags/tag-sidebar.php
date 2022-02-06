<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Sidebar') ):

class ReyCore_WooCommerce_Sidebar
{

	/**
	 * Shop sidebar ID
	 */
	const SHOP_SIDEBAR_ID = 'shop-sidebar';
	const FILTER_PANEL_SIDEBAR_ID = 'filters-sidebar';
	const FILTER_TOP_BAR_SIDEBAR_ID = 'filters-top-sidebar';

	private $swidgets = null;
	private $custom_sidebars = [];
	private $_swidgets_checked_conditions = [];

	public function __construct() {

		add_action( 'init', [ $this, 'init'] );
		add_action( 'widgets_init', [ $this, 'register_sidebars'] );
		add_filter( 'rey/sidebar_name', [ $this, 'shop_sidebar'] );
		add_action( 'rey/get_sidebar', [ $this, 'get_shop_sidebar'] );
		add_filter( 'is_active_sidebar', [ $this, 'disable_product_sidebar'] );
		add_filter( 'rey/content/site_main_class', [ $this, 'main_classes'], 10 );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'wp_ajax_rey_custom_sidebars_links', [$this, 'custom_sidebars_links']);
	}

	function default_sidebars(){
		return [
			self::SHOP_SIDEBAR_ID,
			self::FILTER_PANEL_SIDEBAR_ID,
			self::FILTER_TOP_BAR_SIDEBAR_ID,
		];
	}

	public function script_params($params)
	{
		if( $this->toggle_enabled() ){
			$params['js_params']['sidebar_toggle__status'] = get_theme_mod('sidebar_shop__toggle__status', 'all');
			$params['js_params']['sidebar_toggle__exclude'] = get_theme_mod('sidebar_shop__toggle__exclude', '');
		}

		return $params;
	}

	function init(){
		add_filter('sidebars_widgets', [$this, 'sidebar_widgets']);
	}

	function sidebar_widgets($sidebars_widgets){

		if( wp_doing_ajax() || is_admin() ){
			return $sidebars_widgets;
		}

		if( ! empty($this->swidgets) ){
			return array_merge($sidebars_widgets, $this->swidgets);
		}

		$default_sidebars = $this->default_sidebars();
		$custom_sidebars = $this->get_custom_sidebars();

		// bail if no custom sidebars
		if( empty($custom_sidebars) ){
			return $sidebars_widgets;
		}

		if( empty($custom_sidebar['terms']) ){
			return $sidebars_widgets;
		}

		// check conditions
		$conditions = [];
		foreach ($custom_sidebars as $custom_sidebar) {
			foreach ($custom_sidebar['terms'] as $term) {
				$conditions[] = is_tax($term->taxonomy, $term->term_id);
			}
		}

		if( ! in_array(true, $conditions, true) ){
			return $sidebars_widgets;
		}

		foreach ($default_sidebars as $default_single_sidebar) {

			foreach ($sidebars_widgets as $key => $sidebars_widget) {

				if( $key === $default_single_sidebar ){
					continue;
				}

				if( strpos($key, $default_single_sidebar ) === 0 ){
					$sidebars_widgets[$default_single_sidebar] = $sidebars_widget;
					$this->swidgets[$default_single_sidebar] = $sidebars_widget;
				}
			}

		}

		return $sidebars_widgets;
	}

	public static function can_output_shop_sidebar(){
		return apply_filters('reycore/woocommerce/sidebars/can_output_shop_sidebar', is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy());
	}

	/**
	 * Show Shop sidebar
	 *
	 * @since 1.0.0
	 */
	public function shop_sidebar($sidebar)
	{
		if( self::can_output_shop_sidebar() ){
			return self::SHOP_SIDEBAR_ID;
		}
		return $sidebar;
	}

	/**
	 * Get Shop Sidebar
	 * @hooks to rey/get_sidebar
	 */
	public function get_shop_sidebar( $position )
	{
		if(
			self::can_output_shop_sidebar() &&
			is_active_sidebar(self::SHOP_SIDEBAR_ID) &&
			get_theme_mod('catalog_sidebar_position', 'right') == $position
		) {
			get_sidebar();
		}
	}


	/**
	 * Disable sidebar on product pages
	 *
	 * @since 1.0.0
	 */
	public function disable_product_sidebar( $status ) {

		global $wp_query;

		if ( $wp_query->is_singular && $wp_query->get('post_type') === 'product' && get_theme_mod('single_skin__default__sidebar', '') === '' ) {
			return false;
		}

		return $status;
	}

	/**
	 * Filter main wrapper's css classes
	 *
	 * @since 1.0.0
	 **/
	public function main_classes($classes)
	{
		if( self::can_output_shop_sidebar() && is_active_sidebar(self::SHOP_SIDEBAR_ID) && get_theme_mod('catalog_sidebar_position', 'right') !== 'disabled' ) {
			$classes[] = '--has-sidebar';
		}

		return $classes;
	}


	function toggle_enabled(){
		return get_theme_mod('sidebar_shop__toggle__enable', false);
	}

	/**
	 * Register sidebars
	 *
	 * @since 1.0.0
	 **/
	public function register_sidebars()
	{
		$title_class = $after_title = '';

		if( $this->toggle_enabled() ){
			$title_class = 'rey-toggleWidget';
			$after_title = '';
			if( get_theme_mod('sidebar_shop__toggle__indicator', 'plusminus') === 'plusminus' ){
				$after_title .= reycore__get_svg_icon__core(['id'=>'reycore-icon-minus', 'class' => '__indicator __minus']);
				$after_title .= reycore__get_svg_icon__core(['id'=>'reycore-icon-plus', 'class' => '__indicator __plus']);
			}
			else {
				$after_title .= reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow', 'class' => '__indicator __arrow']);
			}
		}

		$tag = apply_filters('reycore/woocommerce/sidebars/titles_tag', 'h3');

		$default_sidebars = [
			self::SHOP_SIDEBAR_ID => [
				'name'          => esc_html__( 'Shop Sidebar', 'rey-core' ),
				'id'            => self::SHOP_SIDEBAR_ID,
				'description'   => esc_html__('This sidebar will be visible on the shop pages.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title {$title_class}'>",
				'after_title'   => $after_title . "</{$tag}>",
			],
			self::FILTER_PANEL_SIDEBAR_ID => [
				'name'          => esc_html__( 'Filter Panel', 'rey-core' ),
				'id'            => self::FILTER_PANEL_SIDEBAR_ID,
				'description'   => esc_html__('This sidebar should contain WooCommerce filter widgets.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title {$title_class}'>",
				'after_title'   => $after_title . "</{$tag}>",
			],
			self::FILTER_TOP_BAR_SIDEBAR_ID => [
				'name'          => esc_html__( 'Filter Top Bar', 'rey-core' ),
				'id'            => self::FILTER_TOP_BAR_SIDEBAR_ID,
				'description'   => esc_html__('This sidebar should contain WooCommerce filter widgets horizontally before the products.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title'>",
				'after_title'   => "</{$tag}>",
			],
		];

		foreach ($default_sidebars as $key => $sidebar) {
			register_sidebar( $sidebar );
		}

		if( get_theme_mod('single_skin__default__sidebar', '') !== '' ){
			register_sidebar( [
				'name'          => esc_html__( 'Product Page', 'rey-core' ),
				'id'            => 'product-page-sidebar',
				'description'   => esc_html__('This will be displayed only on Product Pages.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title'>",
				'after_title'   => "</{$tag}>",
			] );
		}

		if( $custom_sidebars = $this->get_custom_sidebars() ){

			foreach ($custom_sidebars as $id => $custom_sidebar) {

				$the_sidebar = $default_sidebars[ $custom_sidebar['type'] ];
				$the_sidebar['name'] = $custom_sidebar['name'];
				$the_sidebar['id'] = $id;

				$active_taxs = [];

				foreach ($custom_sidebar['terms'] as $term) {
					if(isset($term->name) ){
						$active_taxs[] = sprintf('<a href="#widgets-only-visible-terms--%s" target="_blank">%s</a>', $term->term_id, $term->name);
					}
				}

				if( !empty($active_taxs) ){
					$the_sidebar['description'] .= ' ' . esc_html__('Only visible for: ', 'rey-core') . implode(', ', $active_taxs);
				}

				register_sidebar( $the_sidebar );
			}

		}

	}

	private function get_custom_sidebars(){

		if( ! empty($this->custom_sidebars) ){
			return $this->custom_sidebars;
		}

		$the_custom_sidebars = [];

		if( $loop_sidebars = get_theme_mod('loop_sidebars', '') ){

			foreach ($loop_sidebars as $key => $sidebar) {

				$terms = [];

				if( isset($sidebar['categories']) && $categories = $sidebar['categories'] ){
					$terms = array_merge($categories, $terms);
				}

				if( isset($sidebar['attributes']) && ($attributes = $sidebar['attributes']) ){
					$terms = array_merge($attributes, $terms);
				}

				if( ! (isset($sidebar['name']) && $name = $sidebar['name']) ){
					continue;
				}

				if( ! (isset($sidebar['type']) && $type = $sidebar['type']) ){
					continue;
				}

				$default_sidebars = $this->default_sidebars();

				if( ! in_array($type, $default_sidebars, true) ){
					continue;
				}

				$id = $type . '-' . sanitize_title($name);
				$sidebar['terms'] = !empty($terms) ? get_terms( ['include' => $terms] ) : [];

				$the_custom_sidebars[$id] = $sidebar;
			}

		}

		return $this->custom_sidebars = $the_custom_sidebars;
	}

	function custom_sidebars_links(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json( ['error' => 'Invalid security nonce.'] );
		}

		if( ! (isset($_REQUEST['term_ids']) && $term_ids = reycore__clean($_REQUEST['term_ids'])) ){
			wp_send_json( ['error' => 'Empty terms.'] );
		}

		$terms = get_terms(['include'=>$term_ids]);
		$term_links = [];

		foreach ($terms as $term) {
			$term_links[$term->term_id] = get_term_link($term);
		}

		wp_send_json( $term_links );

	}

}

new ReyCore_WooCommerce_Sidebar;

endif;
