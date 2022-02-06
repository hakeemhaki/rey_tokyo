<?php
/**
 * WC Ajax Active Filters
 */
if (!class_exists('REYAJAXFILTERS_Active_Filters_Widget')) {
	class REYAJAXFILTERS_Active_Filters_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {

			parent::__construct(
				'reyajfilter-active-filters', // Base ID
				__('Rey - Active Filters', 'rey-core'), // Name
				array('description' => __('Shows active filters so users can see and deactivate them.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title' => '',
				'button_text' => '',
				'active_items' => '',
			];

		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget($args, $instance) {

			if ( ! apply_filters('reycore/ajaxfilters/widgets_support', false) ) {
				reyAjaxFilters()->show_widgets_notice_for_pages();
				return;
			}

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$reyAjaxFilters = reyAjaxFilters();

			// enqueue necessary scripts
			reyAjaxFilters()::load_scripts();

			$chosen_filters = $reyAjaxFilters->chosen_filters;

			if( empty($chosen_filters) ){
				return;
			}

			$active_filters = $chosen_filters['active_filters'];
			$found = false;
			$html = $filters_html = '';

			$close = reycore__get_svg_icon(['id' => 'rey-icon-close']);

			if (!empty($active_filters)) {

				$found = true;

				$show_active_items = $instance['active_items'] === '';

				if( ! reyajaxfilter_show_active_tax__deprecated() && is_tax() && $current_tax = get_queried_object() ){
					if( isset($current_tax->term_id) && isset($active_filters['term']) ){
						foreach ($active_filters['term'] as $key => $term_group) {
							if( array_key_exists($current_tax->term_id, $term_group) ){
								if( count($term_group) === 1 ){
									unset($active_filters['term'][$key]);
								}
								else {
									unset($active_filters['term'][$key][$current_tax->term_id]);
								}

								if( empty($active_filters['term']) ){
									unset($active_filters['term']);
								}
							}
						}
					}
				}

				if( $show_active_items ) {
					foreach ($active_filters as $key => $active_filter) {

						if ($key === 'term') {
							foreach ($active_filter as $data_key => $terms) {
								foreach ($terms as $term_id => $term_tax) {
									$term_data = reyajaxfilter_get_term_data($term_id, $term_tax);
									if( isset($term_data->name) ){
										$filters_html .= '<a href="javascript:void(0)" data-key="' . $data_key . '" data-value="' . $term_id . '">' . $close. '<span>' . $term_data->name . '</span></a>';
									}
								}
							}
						}

						if ($key === 'range_min') {
							foreach ($active_filter as $taxonomy => $term_name) {
								$filters_html .= sprintf('<a href="javascript:void(0)" data-key="min-range-%5$s">%1$s<span>%2$s %3$s: %4$s</span></a>',
									$close,
									_x('Min.', 'Range min filter', 'rey-core'),
									wc_attribute_label($taxonomy),
									$term_name,
									str_replace( 'pa_', '', $taxonomy )
								);
							}
						}

						if ($key === 'range_max') {
							foreach ($active_filter as $taxonomy => $term_name) {
								$filters_html .= sprintf('<a href="javascript:void(0)" data-key="max-range-%5$s">%1$s<span>%2$s %3$s: %4$s</span></a>',
									$close,
									_x('Max.', 'Range max filter', 'rey-core'),
									wc_attribute_label($taxonomy),
									$term_name,
									str_replace( 'pa_', '', $taxonomy )
								);
							}
						}

						if ($key === 'keyword') {
							$filters_html .= '<a href="javascript:void(0)" data-key="keyword">' . $close . '<span>' . __('Search For: ', 'rey-core') . $active_filter . '</span></a>';
						}

						if (apply_filters('reycore/ajaxfilters/active_filters/order_display', false) && $key === 'orderby') {
							$filters_html .= '<a href="javascript:void(0)" data-key="orderby">' . $close . '<span>' . __('Orderby: ', 'rey-core') . $active_filter . '</span></a>';
						}

						if( $active_filter !== '' ) {
							if ($key === 'min_price' ) {
								$filters_html .= '<a href="javascript:void(0)" data-key="min-price">'. $close . '<span>' . __('Min Price: ', 'rey-core') . $active_filter . '</span></a>';
							}

							if ($key === 'max_price') {
								$filters_html .= '<a href="javascript:void(0)" data-key="max-price">' . $close . '<span>' . __('Max Price: ', 'rey-core') . $active_filter . '</span></a>';
							}
						}

						if ($key === 'in-stock') {
							$filters_html .= '<a href="javascript:void(0)" data-key="in-stock">' . $close . '<span>' . __('Stock', 'rey-core') . '</span></a>';
						}

						if ($key === 'on-sale') {
							$filters_html .= '<a href="javascript:void(0)" data-key="on-sale">' . $close . '<span>' . __('On Sale', 'rey-core') . '</span></a>';
						}

						if ($key === 'is-featured') {
							$filters_html .= '<a href="javascript:void(0)" data-key="is-featured">' . $close . '<span>' . __('Featured', 'rey-core') . '</span></a>';
						}

						if ($key === 'product-meta') {

							foreach ($active_filter as $hash) {

								$pm_data = reyajaxfilter_get_registered_meta_query_data( $hash );

								if( !empty($pm_data) ) {
									$filters_html .= sprintf( '<a href="javascript:void(0)" data-key="product-meta" data-value="%2$s">%3$s<span>%1$s</span></a>',
										$pm_data['title'],
										$hash,
										$close
									);
								}
							}
						}

					}
				}

				if ( (! empty($filters_html) || ! $show_active_items) && !empty($instance['button_text'])) {

					if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
						$link = home_url();
					} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id('shop') ) ) {
						$link = get_post_type_archive_link( 'product' );
					} elseif ( is_tax( get_object_taxonomies('product') ) ) {
						$link = get_term_link( get_queried_object_id() );
					} elseif( get_query_var('term') && get_query_var('taxonomy') ) {
						$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
					} else {
						$link = get_page_link();
					}

					/**
					 * Search Arg.
					 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
					 */
					if ( get_search_query() ) {
						$link = add_query_arg( 's', rawurlencode( htmlspecialchars_decode( get_search_query() ) ), $link );
					}

					// Post Type Arg
					if ( isset( $_GET['post_type'] ) && $_GET['post_type'] ) {
						$link = add_query_arg( 'post_type', wc_clean( $_GET['post_type'] ), $link );
					}

					$filters_html .= '<a href="javascript:void(0)" class="reset" data-location="' . $link . '">' . $close . '<span>' . $instance['button_text'] . '</span></a>';
				}

				if( !empty($filters_html) ){
					$html .= '<div class="reyajfilter-active-filters">' . $filters_html . '</div>';
				}
			}

			extract($args);

			// Add class to before_widget from within a custom widget
			// http://wordpress.stackexchange.com/questions/18942/add-class-to-before-widget-from-within-a-custom-widget

			if ($found === false) {
				$widget_class = 'reyajfilter-widget-hidden woocommerce reyajfilter-ajax-term-filter';
			} else {
				$widget_class = 'woocommerce reyajfilter-ajax-term-filter';
			}

			// no class found, so add it
			if (strpos($before_widget, 'class') === false) {
				$before_widget = str_replace('>', 'class="' . $widget_class . '"', $before_widget);
			}
			// class found but not the one that we need, so add it
			else {
				$before_widget = str_replace('class="', 'class="' . $widget_class . ' ', $before_widget);
			}

			echo $before_widget;

			if (!empty($instance['title'])) {
				echo $args['before_title'] . apply_filters('widget_title', $instance['title']). $args['after_title'];
			}

			echo $html;

			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form($instance) {

			do_action('reycore/ajaxfilters/before_widget_controls', $instance);

			reyajaxfilter_widget__option( $this, $instance, [
				'name' => 'title',
				'type' => 'text',
				'label' => __( 'Title:', 'rey-core' ),
				'value' => '',
			]);

			reyajaxfilter_widget__option( $this, $instance, [
				'name' => 'button_text',
				'type' => 'text',
				'label' => __( 'Button Text:', 'rey-core' ),
				'value' => '',
			]);

			reyajaxfilter_widget__option( $this, $instance, [
				'name' => 'active_items',
				'type' => 'checkbox',
				'label' => __( 'Hide active items', 'rey-core' ),
				'value' => '1',
			]);

		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update($new_instance, $old_instance) {

			$instance = [];

			foreach ($this->defaults as $key => $value) {
				$instance[$key] = isset($new_instance[$key]) ? reycore__clean( $new_instance[$key] ) : $value;
			}

			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_active_filters_widget')) {
	function reyajaxfilter_register_active_filters_widget() {
		register_widget('REYAJAXFILTERS_Active_Filters_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_active_filters_widget');
}
