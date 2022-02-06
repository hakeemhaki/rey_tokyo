<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('ReyCore_MegaMenus')):
	/**
	 * Mega Menus Manager
	 *
	 * @since 1.0.0
	 */
	class ReyCore_MegaMenus
	{
		private static $_instance = null;

		const SUPPORTED_MENUS = 'rey_mega_menus_supported_menus';

		/**
		 * ReyCore_MegaMenus constructor.
		 */
		private function __construct()
		{
			add_filter( 'acf/load_field_group', [$this, 'add_locations']);
			add_filter( 'acf/prepare_field/name=menu_global_section', [$this, 'add_global_sections_into_lists']);
			// add_action( 'wp_nav_menu_item_custom_fields', [$this, 'add_notice']);
			add_action( 'admin_footer', [$this, 'add_options']);
			add_action( 'wp_update_nav_menu', [$this, 'save_mm_option']);
			add_filter( 'walker_nav_menu_start_el_mega', [$this, 'render_mega_menu'], 10, 4 );
			add_filter( 'nav_menu_css_class', [$this, 'mega_menu_item_classes'], 20, 4);
			add_filter( 'wp_nav_menu_args', [$this, 'set_mega_menu_support']);
			add_filter( 'rey/css_styles', [$this, 'main_menu_submenus_styles'] );
			add_filter( 'nav_menu_link_attributes', [$this, 'mega_menu_link_args'], 10, 4);
		}

		function add_locations($field_group){

			if( function_exists('reycore_acf__is_exporting') && reycore_acf__is_exporting() ){
				return $field_group;
			}

			if( function_exists('reycore_acf__is_editing_group') && reycore_acf__is_editing_group() ){
				return $field_group;
			}

			if( isset($field_group['key']) && $field_group['key'] === 'group_5c4f2dec3824e' ){

				$supported_menus = get_option(self::SUPPORTED_MENUS, []);

				foreach ($supported_menus as $menu_id) {
					$field_group['location'][] = [
						[
							'param' => 'nav_menu_item',
							'operator' => '==',
							'value' => $menu_id,
						]
					];
				}
			}

			return $field_group;
		}

		/**
		 * Populate ACF's Mega Menu Global Sections list
		 *
		 * @since 1.0.0
		 */
		function add_global_sections_into_lists( $field ) {

			$generic_sections_items = ReyCore_GlobalSections::get_global_sections('megamenu');

			if( isset($field['choices']) && is_array($generic_sections_items) && !empty($generic_sections_items) ) {
				$field['choices'] = $field['choices'] + $generic_sections_items;
			}

			return $field;
		}

		/**
		 * Show notice for missing Mega menu items
		 */
		function add_notice() {

			global $nav_menu_selected_id;

			if( ! is_admin() ){
				return;
			}

			if( $menu_locations = get_nav_menu_locations() ){
				if( isset($menu_locations['main-menu']) && $menu_locations['main-menu'] !== absint( $nav_menu_selected_id ) ){
					printf('<p class="field-rey-notice description description-wide">%s</p>', esc_html__('Looking for Mega Menu settings? Make sure to check the "Main Menu" checkbox at the bottom of this page, or "Enable Mega Menus".', 'rey-core') );
				}
			}
		}

		/**
		 * Add mega menu support form options
		 *
		 * @since 1.6.10
		 */
		function add_options(){

			if( ! (($screen = get_current_screen()) && isset($screen->base) && 'nav-menus' === $screen->base) ){
				return;
			}

			global $nav_menu_selected_id;

			$supported_menus = get_option(self::SUPPORTED_MENUS, []);
			$is_enabled = in_array($nav_menu_selected_id,$supported_menus, true) ? 'checked="checked"' : '';

			if( $menu_locations = get_nav_menu_locations() ){
				if( isset($menu_locations['main-menu']) && $menu_locations['main-menu'] === absint( $nav_menu_selected_id ) ){
					$is_enabled = 'checked="checked"';
				}
			} ?>

			<script>
				jQuery(document).ready(function () {

					var html = '<fieldset class="menu-settings-group rey-enable-mega-menus">' +
									'<legend class="menu-settings-group-name howto"><?php esc_html_e('Enable Mega Menus', 'rey-core')?></legend>' +
									'<div class="menu-settings-input checkbox-input">' +
										'<input type="checkbox" name="rey-enable-mega-menus" id="rey-enable-mega-menus" value="1" <?php echo esc_attr($is_enabled); ?> <label for="rey-enable-mega-menus"><?php esc_html_e('Yes', 'rey-core')?></label>' +
									'</div>' +
								'</fieldset>';

					jQuery(html).insertAfter(jQuery('.menu-settings-group.menu-theme-locations'));
				})
			</script>
			<?php
		}

		/**
		 * Save mega menu enable flag for specific menu
		 *
		 * @since 1.6.10
		 */
		function save_mm_option( $nav_menu_selected_id ){

			$supported_menus = get_option(self::SUPPORTED_MENUS, []);

			if( isset($_POST['rey-enable-mega-menus']) && absint($_POST['rey-enable-mega-menus']) === 1 ){
				$supported_menus[] = $nav_menu_selected_id;
			}
			else {
				if (($key = array_search($nav_menu_selected_id, $supported_menus)) !== false) {
					unset($supported_menus[$key]);
				}
			}

			update_option(self::SUPPORTED_MENUS, array_unique($supported_menus));
		}

		/**
		 * Filter menus to assign support for mega menus
		 *
		 * @since 1.6.10
		 */
		function set_mega_menu_support($args){

			$args['rey_mega_menu'] = false;
			$header = reycore__get_option('header_layout_type', 'default');

			// only if custom GS header
			if( ! in_array($header, ['default', 'none'], true) ){

				if( ! isset($args['element_type']) ){
					return $args;
				}

				// check for supported elements. Only Header Nav. atm;
				$supported_elements = [
					'reycore-header-navigation'
				];

				if( ! in_array($args['element_type'], $supported_elements, true) ){
					return $args;
				}
			}

			if( is_object($args['menu']) && isset($args['menu']->term_id) ){
				$term_id = $args['menu']->term_id;
			}
			else {
				$term = get_term_by('slug', $args['menu'], 'nav_menu');
				$term_id = isset($term->term_id) ? $term->term_id : $args['menu'];
			}

			$supported_menus = get_option(self::SUPPORTED_MENUS, []);

			if(
				in_array($term_id, $supported_menus, true) ||
				(($menu_locations = get_nav_menu_locations()) && isset($menu_locations['main-menu']) && $menu_locations['main-menu'] === absint( $term_id ) )
			){
				$args['rey_mega_menu'] = true;
				$args['menu_class'] = $args['menu_class'] . ' --megamenu-support';
			}

			if( $args['rey_mega_menu'] && $header === 'default' ){
				if( class_exists('ReyCore_Walker_Nav_Menu') ){
					$args['walker'] = new ReyCore_Walker_Nav_Menu;
				}
			}

			return $args;
		}

		/**
		 * Render Mega Menu Global section
		 *
		 * @since: 1.0.0
		 */
		public function render_mega_menu( $item_output, $item, $depth, $args) {

			if( ! class_exists('ReyCore_GlobalSections') ){
				return $item_output;
			}

			if(
				reycore__acf_get_field('mega_menu', $item->ID) && reycore__acf_get_field('mega_menu_type', $item->ID) == 'global_sections' &&
				$gs_id = reycore__acf_get_field('menu_global_section', $item->ID)
			) {

				if( isset($GLOBALS['gs_mega_menu']) && $GLOBALS['gs_mega_menu'] === $gs_id ){
					return $item_output;
				}

				$GLOBALS['gs_mega_menu'] = $gs_id;

				// load section
				$item_output .= sprintf( '<div class="rey-mega-gs">%s</div>', ReyCore_GlobalSections::do_section( $gs_id ) );

				unset($GLOBALS['gs_mega_menu']);

			}

			return $item_output;
		}

		/**
		 * Mega Menu CSS Classes
		 *
		 * @since: 1.0.0
		 */
		function mega_menu_item_classes( $classes, $item, $args, $depth)
		{
			if( isset($args->rey_mega_menu) && $args->rey_mega_menu && $depth === 0 ) {
				if ( reycore__acf_get_field('mega_menu', $item->ID) ) {
					$classes['type'] = '--is-mega';
					if( reycore__acf_get_field('mega_menu_type', $item->ID) == 'columns' && reycore__acf_get_field('mega_menu_columns', $item->ID) ) {
						$classes[] = '--is-mega--cols-' . reycore__acf_get_field('mega_menu_columns', $item->ID);
					}
					$classes[] = '--mega-' . reycore__acf_get_field('panel_layout', $item->ID);
					// make sure to add it
					// to help global sections
					if( !in_array('menu-item-has-children', $classes) ) {
						$classes[] = 'menu-item-has-children';
					}

					reyCoreAssets()->add_styles('reycore-mega-menu');
					reyCoreAssets()->add_scripts('reycore-mega-menu');
				}
			}

			return $classes;
		}

		/**
		 * Add menu items styles
		 *
		 * @since 1.5.0
		 **/
		function main_menu_submenus_styles($styles)
		{
			$menu_items = get_posts( [
				'order'       => 'ASC',
				'orderby'     => 'menu_order',
				'post_type'   => 'nav_menu_item',
				'post_status' => 'publish',
				'output'      => ARRAY_A,
				'output_key'  => 'menu_order',
				'nopaging'    => true,
				'update_post_term_cache' => false,
				'fields' => 'ids'
			] );

			foreach( $menu_items as $menu_item ){

				if( ! ($has_styles = reycore__acf_get_field('panel_styles', $menu_item )) ){
					continue;
				}

				$css_styles = [];

				if( $text_color = reycore__acf_get_field('panel_text_color', $menu_item ) ){
					$css_styles[] = sprintf('--link-color:%s;', $text_color);
				}

				if( $panel_bg_color = reycore__acf_get_field('panel_bg_color', $menu_item ) ){
					$css_styles[] = sprintf('--body-bg-color:%s;', $panel_bg_color);
				}

				$panel_padding = reycore__acf_get_field('panel_padding', $menu_item );
				if( !is_null($panel_padding) && $panel_padding !== '' && $panel_padding !== false ){
					$css_styles[] = sprintf('--submenus-padding:%spx;', absint($panel_padding));
				}

				if( !empty($css_styles) ){
					$styles[] = sprintf('.rey-mainMenu--desktop .menu-item.menu-item-has-children.menu-item-%1$s {%2$s}',
						$menu_item,
						implode('', $css_styles)
					);
				}
			}

			return $styles;
		}

		/**
		 * Filter Mega Menu link attributes to add sub-pabel's
		 * menu width
		 *
		 * @since: 1.0.0
		 */
		function mega_menu_link_args( $atts, $item, $args, $depth )
		{
			if(
				isset($args->rey_mega_menu) && $args->rey_mega_menu && $depth == 0 &&
				reycore__acf_get_field('mega_menu', $item->ID) &&
				reycore__acf_get_field('panel_layout', $item->ID) == 'custom'
			) {
				$atts['data-panel-width'] = reycore__acf_get_field('panel_width', $item->ID);
			}

			return $atts;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_MegaMenus
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_MegaMenus::getInstance();
endif;
