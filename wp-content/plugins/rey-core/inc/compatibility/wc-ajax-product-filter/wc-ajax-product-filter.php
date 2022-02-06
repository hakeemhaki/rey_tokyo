<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('WCAPF') && !class_exists('ReyCore_Compatibility__WCAPF') ):
	/**
	 * Elementor PRO
	 *
	 * @since 1.3.0
	 */
	class ReyCore_Compatibility__WCAPF
	{
		public function __construct()
		{
			add_filter( 'in_widget_form', [$this, 'filter_widgets_display_type_option'], 5, 3 );
			add_filter( 'widget_update_callback', [$this, 'filter_widgets_display_type_save'], 5, 4 );
			add_filter( 'dynamic_sidebar_params', [$this, 'filters_add_display_type_classes'] );
			add_filter( 'wcapf_settings', [$this, 'wcapf_default_settings']);
			add_filter( 'reycore/woocommerce/get_active_filters', [$this, 'get_active_filters']);
			add_filter( 'reycore/woocommerce/brands/url', [$this, 'brand_url'], 10, 3);
			add_action( 'woocommerce_before_shop_loop', [$this, 'beforeProductsHolder'], 1);
			add_action( 'woocommerce_before_template_part', [$this, 'beforeNoProducts'], 1);
			add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );

			add_action( 'elementor/element/reycore-menu/section_settings/before_section_end', [ $this, 'elementor_menu_cat_skin_option' ] );
			add_filter( 'reycore/elementor/menu/product_categories_skin/render_menu', [$this, 'elementor_menu_cat_skin_html'], 10, 3);
			add_action( 'reycore/elementor/product_grid/show_header', [ $this, 'elementor_product_grid_add_scripts' ] );

		}

		function load_styles(){
            wp_enqueue_style( 'reycore-wcapf-styles', REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
		}

		/**
		 * Hook into Filtering Widgets for WC Ajax Filter & WooCommerce native
		 * to add List display type (color/button/simple list)
		 * & for lists, display in 2 columns.
		 *
		 * @since 1.0.0
		 */
		function filter_widgets_display_type_option( $widget, $return, $instance ) {

			// Add display type option
			if ( 'wcapf-attribute-filter' == $widget->id_base || 'woocommerce_layered_nav' == $widget->id_base ) {

				$options = [
					'list'  => __( 'List', 'rey-core' ),
					'color'  => __( 'Color', 'rey-core' ),
					'button'  => __( 'Button', 'rey-core' )
				];

				$rey_display_type = isset( $instance['rey_display_type'] ) ? $instance['rey_display_type'] : 'list';
				?>
					<p>
						<label for="<?php echo $widget->get_field_id('rey_display_type'); ?>">
							<?php _e( 'List Display Type', 'rey-core' ); ?>
						</label>
						<select name="<?php echo $widget->get_field_name('rey_display_type'); ?>" id="<?php echo $widget->get_field_id('rey_display_type'); ?>">
							<?php
								foreach ($options as $key => $value) {
									echo '<option value="'.$key.'" '. selected( $rey_display_type, $key, false ) .' >'.$value.'</option>';
								}
							?>
						</select>
					</p>
				<?php
			}

			if ( 'wcapf-attribute-filter' == $widget->id_base || 'wcapf-category-filter' == $widget->id_base || 'woocommerce_layered_nav' == $widget->id_base ) {

				$multi_col = isset( $instance['rey_multi_col'] ) ? $instance['rey_multi_col'] : '';
				?>
				<p>
					<input class="checkbox" type="checkbox" id="<?php echo $widget->get_field_id('rey_multi_col'); ?>" name="<?php echo $widget->get_field_name('rey_multi_col'); ?>" <?php checked( true , $multi_col ); ?> />
					<label for="<?php echo $widget->get_field_id('rey_multi_col'); ?>">
						<?php _e( 'Display list on 2 columns', 'rey-core' ); ?>
					</label>
				</p>
				<?php
			}

			if ( 'wcapf-attribute-filter' == $widget->id_base || 'woocommerce_layered_nav' == $widget->id_base ) {
				?>
				<p>
					<label for="<?php echo $widget->get_field_id('rey_width'); ?>">
						<?php _e( 'Item Width (px)', 'rey-core' ); ?>
					</label>
					<input class="tiny-text" type="number" step="1" min="10" max="200" value="<?php echo isset( $instance['rey_width'] ) ? esc_attr($instance['rey_width']) : '' ?>" id="<?php echo $widget->get_field_id('rey_width'); ?>" name="<?php echo $widget->get_field_name('rey_width'); ?>" style="width: 60px" />
				</p>

				<p>
					<label for="<?php echo $widget->get_field_id('rey_height'); ?>">
						<?php _e( 'Item Height (px)', 'rey-core' ); ?>
					</label>
					<input class="tiny-text" type="number" step="1" min="10" max="200" value="<?php echo isset( $instance['rey_height'] ) ? esc_attr($instance['rey_height']) : '' ?>" id="<?php echo $widget->get_field_id('rey_height'); ?>" name="<?php echo $widget->get_field_name('rey_height'); ?>" style="width: 60px" />
				</p>
				<?php
			}

			return $widget;

		}
		/**
		 * Save the new appended options into filtering widgets
		 *
		 * @since 1.0.0
		 */
		function filter_widgets_display_type_save( $instance, $new_instance, $old_instance, $widget ) {

			if ( 'wcapf-attribute-filter' === $widget->id_base || 'wcapf-category-filter' === $widget->id_base || 'woocommerce_layered_nav' === $widget->id_base ) {

				if ( isset( $new_instance['rey_display_type'] ) ) {
					$instance['rey_display_type'] = !empty( $new_instance['rey_display_type'] ) ? $new_instance['rey_display_type'] : 'list';
				}

				if ( isset( $new_instance['rey_multi_col'] ) && !empty( $new_instance['rey_multi_col'] ) ) {
					$instance['rey_multi_col'] = 1;
				}

			}

			if ( 'wcapf-attribute-filter' === $widget->id_base || 'woocommerce_layered_nav' === $widget->id_base ) {

				if ( isset( $new_instance['rey_width'] ) && !empty( $new_instance['rey_width'] ) ) {
					$instance['rey_width'] = $new_instance['rey_width'];
				}

				if ( isset( $new_instance['rey_height'] ) && !empty( $new_instance['rey_height'] ) ) {
					$instance['rey_height'] = $new_instance['rey_height'];
				}
			}

			return $instance;
		}

		/**
		 * Add display type css classes for Filtering Widgets for WC Ajax Filter & WooCommerce native
		 * nav filtering.
		 *
		 * Hooks to the dynamic sidebar and filters the parameters.
		 *
		 * @since 1.0.0
		 */
		function filters_add_display_type_classes( $params ) {

			$widgets = [
				'wcapf-attribute-filter',
				'wcapf-category-filter',
				'woocommerce_layered_nav',
			];

			foreach ($widgets as $widget) {
				$widget_id = 'widget_' . $widget;
				$widget_settings = get_option( $widget_id );

				if( strpos($params[0]['widget_id'], $widget) !== false ){

					if ( isset($widget_settings[$params[1]['number']]['rey_display_type']) && $display_type = $widget_settings[$params[1]['number']]['rey_display_type'] ) {

						$params[0]['before_widget'] = str_replace(
							$widget_id,
							$widget_id . ' rey-filterList rey-filterList--'. $display_type,
							$params[0]['before_widget']
						);

						if ( $display_type == 'color' ) {
							add_filter('woocommerce_layered_nav_term_html', [$this, 'filter_color_attr_html'], 10, 4);
						} else {
							remove_filter('woocommerce_layered_nav_term_html', [$this, 'filter_color_attr_html'], 10, 4);
						}
					}

					if ( isset($widget_settings[$params[1]['number']]['rey_multi_col']) ) {
						$params[0]['before_widget'] = str_replace(
							$widget_id,
							$widget_id . ' rey-filterList-cols',
							$params[0]['before_widget']
						);
					}

					$css = '';

					if ( isset($widget_settings[$params[1]['number']]['rey_width']) && $width = $widget_settings[$params[1]['number']]['rey_width'] ) {
						$css = 'width: '. $width .'px; min-width: '. $width .'px;';
					}

					if ( isset($widget_settings[$params[1]['number']]['rey_height']) && $height = $widget_settings[$params[1]['number']]['rey_height'] ) {
						$css .= 'height: '. $height .'px;';
					}

					if( $css ){
						$the_style = sprintf('<style>#%s.rey-filterList ul li a {%s}</style>', $params[0]['widget_id'], $css);
						$params[0]['before_widget'] = $params[0]['before_widget'] . $the_style;
					}
				}
			}

			// Return the unmodified $params.
			return $params;
		}

		/**
		 * Override Color Attribute html in filter nav
		 *
		 * @since 1.0.0
		 **/
		function filter_color_attr_html($term_html, $term, $link, $count)
		{
			if( $taxonomy = $term->taxonomy ) {

				if( $taxonomy == 'product_cat' ) {
					return $term_html;
				}

				$colors = $color = '';

				if( class_exists('ReyCore_WooCommerce_Variations') ){

					// Customize color attributes
					$colors = ReyCore_WooCommerce_Variations::getInstance()->get_color_attributes();

					$term_id = $term->term_id;

					if( is_array($colors) && !empty($colors) && isset($colors[$term_id]) ) {

						$swatch_tag = ReyCore_WooCommerce_Variations::parse_attribute_color([
							'value' => $term->slug,
							'name' => $term->name
						], $colors[$term_id]['color']);

						$term_html = str_replace( $term->name, $swatch_tag, $term_html );
					}
				}
			}

			return $term_html;
		}

		// Filter Settings
		function wcapf_default_settings($settings) {
			$settings['enable_font_awesome'] = false;
			return $settings;
		}

		/**
		 * HTML wrapper to insert before the shop loop.
		 *
		 * @return string
		 */
		function beforeProductsHolder()
		{
			echo '<div class="rey-wcapf-updater"><div class="rey-lineLoader"></div></div>';
		}

		/**
		 * HTML wrapper to insert before the not found product loops.
		 *
		 * @param  string $template_name
		 * @param  string $template_path
		 * @param  string $located
		 * @return string
		 */
		function beforeNoProducts($template_name = '', $template_path = '', $located = '') {
		    if ($template_name == 'loop/no-products-found.php') {
				echo '<div class="rey-wcapf-updater"><div class="rey-lineLoader"></div></div>';
		    }
		}

		/**
		 * Get active filters
		 *
		 * @since 1.0.0
		 */
		function get_active_filters(){
			$total_filters = 0;
			if( isset($GLOBALS['wcapf']) ){
				$count_filter = $GLOBALS['wcapf']->getChosenFilters();
				if( isset($count_filter['active_filters']['term']) && $active_filters = $count_filter['active_filters']['term'] ) {
					$total_filters = array_sum(array_map("count", $active_filters));
				}
			}
			return $total_filters;
		}

		function brand_url( $brand_url, $brand_attribute_name, $url ){

			$brand_id = wc_get_product_terms( get_the_ID(), wc_attribute_taxonomy_name( $brand_attribute_name ), [ 'fields' => 'ids' ] );

			if( isset($brand_id[0]) ){
				$brand_url = $url . '?attro-'. $brand_attribute_name .'='. $brand_id[0];
			}

			return $brand_url;
		}

		function elementor_menu_cat_skin_option($element){

			$element->add_control(
				'ajaxify',
				[
					'label' => __( 'Enable Ajax Menu', 'rey-core' ),
					'description' => __( 'To enable this option, you must activate "WC Ajax Product Filter" plugin. Please know it\'s only compatible with archive/category pages.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'_skin' => 'product-categories',
					],
				]
			);

		}

		public function elementor_menu_cat_skin_html( $html, $cats, $settings )
		{

			if( !(isset($settings['ajaxify']) && $settings['ajaxify'] === 'yes') ){
				return $html;
			}

			wp_enqueue_script('wcapf-script');

			$html = "<ul class='reyEl-menu-nav wcapf-ajax-term-filter'>";

			foreach ($cats as $i => $cat) {

				$term = get_term_by('slug', $cat, 'product_cat' );
				$term_id = 0;
				if( is_object($term) && $term->term_id ){
					$term_id = $term->term_id;
				}

				$attributes = 'data-key="product-cato" data-taxonomy-type="product_cat" data-value="'. $term_id .'"';

				$html .= sprintf(
					'<li class="menu-item %3$s"><a href="%2$s" %4$s><span>%1$s</span></a></li>',
					$cat,
					get_term_link( $i, 'product_cat' ),
					is_tax( 'product_cat', $i ) ? 'current-menu-item' : '',
					$attributes
				);
			}

			$html .= '</ul>';

			return $html;
		}

		function elementor_product_grid_add_scripts($elementor_edit_mode){
			if( ! $elementor_edit_mode ) {
				wp_enqueue_style('wcapf-style');
				wp_enqueue_script('wcapf-script');
			}
		}

	}

	new ReyCore_Compatibility__WCAPF;
endif;
