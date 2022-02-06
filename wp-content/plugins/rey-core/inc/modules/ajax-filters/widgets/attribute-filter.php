<?php
/**
 * Rey Ajax Product Filter by Attribute
 */
if (class_exists('WooCommerce') && !class_exists('REYAJAXFILTERS_Attribute_Filter_Widget')) {
	class REYAJAXFILTERS_Attribute_Filter_Widget extends WP_Widget {

		public $dimensions_support = ['color', 'button', 'image'];
		public $tooltip_support = ['color', 'image'];
		public $search_box_support = ['color', 'color_list', 'image', 'list', 'dropdown'];
		public $checkbox_support = ['dropdown', 'list', 'color_list', 'range_points'];

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'reyajfilter-attribute-filter', // Base ID
				__('Rey Filter - by Attribute', 'rey-core'), // Name
				array('description' => __('Filter WooCommerce products by attribute.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title'              => '',
				'attr_name'          => '',
				'custom_height'      => '',
				'query_type'         => 'or',
				'display_type'       => 'list',
				'hide_empty'         => true,
				'order_by'           => 'name',
				'search_box'         => false,
				'enable_multiple'    => false,
				'show_count'         => false,
				'count_stretch'        => '',
				'hierarchical'       => false,
				'accordion_list'     => false,
				'show_checkboxes'    => false,
				'show_checkboxes__radio'  => false,
				'show_children_only' => false,
				'rey_multi_col'      => false,
				'alphabetic_menu'    => false,
				'show_tooltips'      => false,
				'drop_panel'              => false,
				'drop_panel_keep_active'  => false,
				'rey_width'          => '',
				'rey_height'         => '',
				'placeholder'        => '',
				'dd_width'           => '',
				'range_step'         => 1,
				'range_attrs_show_as_dropdown' => false,
				'range_attrs'       => [],
				'range_attrs_start' => [
					'enable' => '',
					'text' => __('Under', 'rey-core'),
					'max' => '',
				],
				'range_attrs_end'   => [
					'enable' => '',
					'text' => __('Over', 'rey-core'),
					'min' => '',
				],
				// Advanced
				'show_hide_categories'    => 'hide',
				'show_only_on_categories' => [],
				'selective_display' => '',
			];

		}


		public function validate_number( $num ){

			if( ! is_numeric($num) ){
				$num = 1;
			}

			if( is_nan($num) ){
				$num = 1;
			}

			if( $num == 'nan' ){
				$num = 1;
			}

			return $num;
		}

		function range_filter( $args, $step = 1 ) {

			// to be sure that these values are number
			$min = $max = 0;
			$min_key = 'min-range-' . $args['data_key_clean'];
			$max_key = 'max-range-' . $args['data_key_clean'];

			$get_ranges_terms = get_terms([
				'taxonomy' => $args['taxonomy'],
				'hide_empty' => true,
			] );

			$ranges = [];

			if( !empty($get_ranges_terms) ){

				$sorted_terms = wp_list_sort($get_ranges_terms, 'name');

				foreach ($sorted_terms as $key => $term) {

					if( !isset($term->name) ){
						continue;
					}

					$ranges[] = $term->name;

					if( $key === 0 ){
						$min = $term->name;
					}
					else if( ($key + 1) === count($sorted_terms) ){
						$max = $term->name;
					}

				}
			}

			reyCoreAssets()->add_scripts('nouislider');
			reyCoreAssets()->add_styles('nouislider');

			$current_min = isset( $_GET[$min_key] ) ? wp_unslash( $_GET[$min_key] ) : $min; // WPCS: input var ok, CSRF ok.
			$current_max = isset( $_GET[$max_key] ) ? wp_unslash( $_GET[$max_key] ) : $max; // WPCS: input var ok, CSRF ok.

			$attr_label = wc_attribute_label($args['taxonomy']);

			$html = '<div class="reyajfilter-range-filter-wrapper">';
				$html .= '<div class="reyajfilter-range-slider noUi-extended" data-min="' . $this->validate_number($min) . '" data-max="' . $this->validate_number($max) . '" data-set-min="' . $this->validate_number($current_min) . '" data-set-max="' . $this->validate_number($current_max) . '" data-step="'. $step .'" data-range="'. implode(',', $ranges) .'" data-key="'. esc_attr($args['data_key_clean']) .'"></div>';

				// $html .= '<br />';
				// $html .= '<div class="slider-values">';
					// $html .= '<p>' . sprintf(__('Min %s', 'rey-core'), $attr_label) . ': <span class="reyajfilter-slider-value" id="reyajfilter-noui-slider-value-min"></span></p>';
					// $html .= '<p>' . sprintf(__('Max %s', 'rey-core'), $attr_label) . ': <span class="reyajfilter-slider-value" id="reyajfilter-noui-slider-value-max"></span></p>';
				// $html .= '</div>';

			$html .= '</div>';

			return $html;
		}

		function range_points( $args, $instance ){

			$html = $list_html = $dd_html = $prefix = $before = $after = '';

			$min = $max = 0;
			$min_key = 'min-range-' . $args['data_key_clean'];
			$max_key = 'max-range-' . $args['data_key_clean'];

			// show checkboxes
			if( $instance['show_checkboxes'] ){
				$radio = $instance['show_checkboxes__radio'] ? '--radio' : '';
				$prefix = sprintf('<span class="__checkbox %s"></span>', $radio);
			}

			if( $instance['range_attrs_start']['enable'] && $instance['range_attrs_start']['max'] ){
				$price_start = [
					'min' => '',
					'max' => $instance['range_attrs_start']['max'],
					'text' => $instance['range_attrs_start']['text'],
				];
				array_unshift($instance['range_attrs'], $price_start);
			}

			if( $instance['range_attrs_end']['enable'] && $instance['range_attrs_end']['min'] ){
				$instance['range_attrs'][] = [
					'max' => '',
					'min' => $instance['range_attrs_end']['min'],
					'text' => $instance['range_attrs_end']['text'],
				];
			}

			foreach ($instance['range_attrs'] as $range_attrs) {

				$is_selected = false;

				if (isset($_GET[$min_key]) && wp_unslash($_GET[$min_key]) == $range_attrs['min']) {
					$is_selected = true;
					$list_html .= '<li class="chosen">';
				} elseif (isset($_GET[$max_key]) && wp_unslash($_GET[$max_key]) == $range_attrs['max']) {
					$is_selected = true;
					$list_html .= '<li class="chosen">';
				} else {
					$list_html .= '<li>';
				}

				$list_html .= sprintf(
					'<a class="reyajfilter-rangePoints-listItem" href="javascript:void(0)" data-key-min="%1$s" data-value-min="%3$s" data-key-max="%2$s" data-value-max="%4$s">',
					$min_key,
					$max_key,
					$range_attrs['min'],
					$range_attrs['max']
				);

				$list_html .= $prefix;

				$dd_html .= sprintf('<option value="%1$s" data-key-min="%5$s" data-value-min="%2$s" data-key-max="%6$s" data-value-max="%3$s" %4$s>',
					$range_attrs['min'] . $range_attrs['max'],
					$range_attrs['min'],
					$range_attrs['max'],
					selected(true, $is_selected, false),
					$min_key,
					$max_key
				);

				if (isset($range_attrs['label']) && $range_attrs['label']) {
					$list_html .= '<span class="__label">' . $range_attrs['label'] . '</span>';
					$dd_html .= $range_attrs['label'];
				}

				if (isset($range_attrs['text']) && $range_attrs['text']) {
					$list_html .= '<span class="__text">' . $range_attrs['text'] . '</span>';
					$dd_html .= $range_attrs['text'];
				}

				if (isset($range_attrs['min']) && $range_attrs['min']) {
					$list_html .= '<span class="__min">' . $before . $range_attrs['min'] . $after . '</span>';
					$dd_html .= $before . $range_attrs['min'] . $after;
				}

				if (isset($range_attrs['to']) && $range_attrs['to']) {
					$list_html .= '<span class="__to">' . $range_attrs['to'] . '</span>';
					$dd_html .= ' ' . $range_attrs['to'] . ' ';
				}

				if (isset($range_attrs['max']) && $range_attrs['max']) {
					$list_html .= '<span class="__max">' . $before . $range_attrs['max'] . $after . '</span>';
					$dd_html .= $before . $range_attrs['max'] . $after;
				}

				$list_html .= '</a></li>';
				$dd_html .= '</option>';
			}

			if( $instance['range_attrs_show_as_dropdown'] ){

				/*

				// required scripts
				reyCoreAssets()->add_scripts('reyajfilter-select2');
				reyCoreAssets()->add_styles('reyajfilter-select2');

				$placeholder = $instance['placeholder'] ? $instance['placeholder'] : esc_html__('Select', 'rey-core');

				$attributes = sprintf('data-placeholder="%s"', $placeholder);

				if( $instance['show_checkboxes'] ):
					reyCoreAssets()->add_scripts('reyajfilter-select2-multi-checkboxes');
					$attributes .= ' data-checkboxes="true"';
				endif;

				if( isset($instance['dd_width']) && $dropdown_width = $instance['dd_width'] ){
					$attributes .= sprintf(' data-ddcss=\'%s\'', wp_json_encode([
						'min-width' => $dropdown_width . 'px'
					]));
				}

				$html .= '<div class="reyajfilter-dropdown-nav">';
				$html .= '<select class="reyajfilter-select2 reyajfilter-select2-single reyajfilter-select2--prices" style="width: 100%;" '. $attributes .'>';
					$html .= '<option></option>';
					$html .= $dd_html;
				$html .= '</select>';
				$html .= '</div>';

				*/
			}
			else {

				$list_classes[] = '--style-' . ($instance['show_checkboxes'] ? 'checkboxes' : 'default');

				$html .= sprintf('<div class="reyajfilter-layered-nav --range-points %s">', implode(' ', $list_classes));

					$html .= '<ul>';
						$html .= $list_html;
					$html .= '</ul>';
				$html .= '</div>';

			}

			return $html;
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

			if( reyAjaxFilters()->should_hide_widget($instance) ){
				return;
			}

			// enqueue necessary scripts
			reyAjaxFilters()::load_scripts();

			reyCoreAssets()->add_styles('rey-wc-tag-attributes');

			if ( ! ($query_type = $instance['query_type']) ) {
				return;
			}

			if ( ! ($attribute_name = $instance['attr_name']) ) {
				return;
			}

			$display_type = $instance['display_type'];
			$is_list = $display_type === 'list';

			$taxonomy   = wc_attribute_taxonomy_name($attribute_name);
			$data_key   = ($query_type === 'and') ? 'attra-' . $attribute_name : 'attro-' . $attribute_name;

			// parse url
			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $url_array);

			$attr_args = array(
				'taxonomy'           => $taxonomy,
				'data_key'           => $data_key,
				'data_key_clean'     => $attribute_name,
				'query_type'         => $query_type,
				'enable_multiple'    => (bool) $instance['enable_multiple'],
				'show_count'         => (bool) $instance['show_count'],
				'enable_hierarchy'   => (bool) $instance['hierarchical'],
				'show_children_only' => (bool) $instance['show_children_only'],
				'url_array'          => apply_filters('reycore/ajaxfilters/query_url', $url_array),
				'show_tooltips'      => (!empty($instance['show_tooltips']) && in_array($display_type, $this->tooltip_support)) ? $instance['show_tooltips']: '',
				'custom_height'      => (!empty($instance['custom_height']) && in_array($display_type, ['list', 'color_list'])) ? $instance['custom_height']: '',
				'alphabetic_menu'    => (bool) $instance['alphabetic_menu'] && $is_list,
				'accordion_list'     => ((bool) $instance['accordion_list'] && $is_list && (bool) $instance['hierarchical'] ),
				'show_checkboxes'    => ((bool) $instance['show_checkboxes'] && in_array($display_type, $this->checkbox_support)),
				'search_box'         => (bool) $instance['search_box'] && in_array($display_type, $this->search_box_support),
				'hide_empty'         => (bool) $instance['hide_empty'],
				'order_by'           => $instance['order_by'],
				'drop_panel'         => (bool) $instance['drop_panel'],
				'drop_panel_button'  => $instance['title'] ? $instance['title'] : esc_html__('Select Attribute', 'rey-core'),
				'drop_panel_keep_active'  => (bool) $instance['drop_panel_keep_active'],
				'dropdown'           => ($display_type === 'dropdown') && ! (bool) $instance['drop_panel'], // BC
				'placeholder'        => $instance['placeholder'],
				'dd_width'           => $instance['dd_width'],
				'display_type' => $instance['display_type'],
				'widget_id' => $args['widget_id'],
			);

			$attr_args['show_checkboxes__radio'] = $attr_args['show_checkboxes'] && (bool) $instance['show_checkboxes__radio'];

			if ($display_type === 'range') {
				$output['html'] = $this->range_filter($attr_args, $instance['range_step']);
				$output['found'] = true;
			}

			else if ($display_type === 'range_points') {
				$output['html'] = $this->range_points($attr_args, $instance);
				$output['found'] = true;
			}

			else {

				if ($display_type === 'color') {
					add_filter('woocommerce_layered_nav_term_html', 'reyajaxfilter_filter_color_attr_html', 10, 4);
				}
				elseif ($display_type === 'color_list') {
					add_filter('woocommerce_layered_nav_term_html', 'reyajaxfilter_filter_color_list_attr_html', 10, 4);
				}
				elseif ($display_type === 'image') {
					add_filter('woocommerce_layered_nav_term_html', 'reyajaxfilter_filter_image_attr_html', 10, 4);
				}

				// get output
				$output = reyajaxfilter_terms_output($attr_args);

				remove_filter('woocommerce_layered_nav_term_html', 'reyajaxfilter_filter_color_attr_html', 10, 4);
				remove_filter('woocommerce_layered_nav_term_html', 'reyajaxfilter_filter_image_attr_html', 10, 4);
			}


			if( !isset($output['html']) ){
				return;
			}

			$html = $output['html'];
			$found = $output['found'];

			extract($args);

			// Add class to before_widget from within a custom widget
			// http://wordpress.stackexchange.com/questions/18942/add-class-to-before-widget-from-within-a-custom-widget

			// if $selected_terms array is empty we will hide this widget totally
			if ($found === false) {
				$widget_class = 'reyajfilter-widget-hidden woocommerce reyajfilter-ajax-term-filter';
			} else {

				$widget_class = 'woocommerce reyajfilter-ajax-term-filter';

				if( in_array($display_type, $this->dimensions_support) ){
					$widget_class .= ' rey-filterList rey-filterList--' . $display_type;
				}

				if( $display_type === 'color_list' ){
					$widget_class .= ' rey-filterList rey-filterList--clist';
				}

				if( in_array($display_type, ['list', 'color_list'], true) && (bool) $instance['show_count'] && (bool) $instance['count_stretch'] ){
					$widget_class .= ' --count-stretch';
				}

				if( in_array($display_type, ['list', 'color_list'], true) && $instance['rey_multi_col'] ){
					$widget_class .= ' rey-filterList-cols';
				}
			}

			// no class found, so add it
			if (strpos($before_widget, 'class') === false) {
				$before_widget = str_replace('>', 'class="' . $widget_class . '"', $before_widget);
			}
			// class found but not the one that we need, so add it
			else {
				$before_widget = str_replace('class="', 'class="' . $widget_class . ' ', $before_widget);
			}

			if( in_array($display_type, $this->dimensions_support) ){

				$css = '';

				if ( isset($instance['rey_width']) && $width = absint($instance['rey_width']) ) {
					$css = 'width: '. $width .'px; min-width: '. $width .'px;';
				}

				if ( isset($instance['rey_height']) && $height = absint($instance['rey_height']) ) {
					$css .= 'height: '. $height .'px;';
				}

				if( $css ){
					$the_style = sprintf('<style>#%s.rey-filterList ul li a {%s}</style>', $widget_id, $css);
					$before_widget = $before_widget . $the_style;
				}

			}

			echo $before_widget;

			if (!empty($instance['title']) && ! $instance['drop_panel'] ) {
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

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			do_action('reycore/ajaxfilters/before_widget_controls', $instance);

			?>
			<div class="rey-widgetTabs-wrapper">

				<div class="rey-widgetTabs-buttons">
					<span data-tab="basic" class="--active"><?php esc_html_e('Basic options', 'rey-core') ?></span>
					<span data-tab="advanced"><?php esc_html_e('Advanced', 'rey-core') ?></span>
				</div>

				<div class="rey-widgetTabs-tabContent --active" data-tab="basic">
					<?php

					$display_name = $this->get_field_name('display_type');

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'title',
						'type' => 'text',
						'label' => __( 'Title', 'rey-core' ),
						'value' => '',
						'field_class' => 'widefat'
					]);

					$attribute_taxonomies = wc_get_attribute_taxonomies();

					if (!empty($attribute_taxonomies)) {

						$attr_choices = [];

						foreach ($attribute_taxonomies as $taxonomy) {
							$attr_choices[ $taxonomy->attribute_name ] = $taxonomy->attribute_label . ' (' . $taxonomy->attribute_name . ')';
						}

						reyajaxfilter_widget__option( $this, $instance, [
							'name' => 'attr_name',
							'type' => 'select',
							'label' => __( 'Attribute', 'rey-core' ),
							'field_class' => 'widefat',
							'options' => $attr_choices,
						]);

					} else {
						esc_html_e('No attribute found!', 'rey-core');
					}

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'display_type',
						'type' => 'select',
						'label' => __( 'Display Type', 'rey-core' ),
						'field_class' => 'widefat',
						'options' => [
							'list' => esc_html__('List', 'rey-core'),
							'dropdown' => esc_html__('Dropdown (Deprecated)', 'rey-core'),
							'color' => esc_html__('Color', 'rey-core'),
							'color_list' => esc_html__('Color List', 'rey-core'),
							'image' => esc_html__('Image', 'rey-core'),
							'button' => esc_html__('Button', 'rey-core'),
							'range' => esc_html__('Range Slider', 'rey-core'),
							'range_points' => esc_html__('Range Points', 'rey-core'),
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'drop_panel',
						'type' => 'checkbox',
						'label' => __( 'Display as Drop-down', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown', // make sure to avoid dropdown
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'drop_panel_keep_active',
						'type' => 'checkbox',
						'label' => __( 'Keep dropdown open after selection', 'rey-core' ),
						'value' => '1',
						'wrapper_class' => '--dep-left',
						'conditions' => [
							[
								'name' => 'drop_panel',
								'value' => '',
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'enable_multiple',
						'type' => 'checkbox',
						'label' => __( 'Enable multiple filter', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'range',
								'compare' => '!='
							],
							[
								'name' => 'display_type',
								'value' => 'range_points',
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_count',
						'type' => 'checkbox',
						'label' => __( 'Show Counter', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'range',
								'compare' => '!='
							],
							[
								'name' => 'display_type',
								'value' => 'range_points',
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'count_stretch',
						'type' => 'checkbox',
						'label' => __( 'Stretch Counter', 'rey-core' ),
						'value' => '1',
						'wrapper_class' => '--dep-left',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => ['list', 'color_list'],
								'compare' => 'in'
							],
							[
								'name' => 'show_count',
								'value' => '',
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'hierarchical',
						'type' => 'checkbox',
						'label' => __( 'Show hierarchy', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => ['list', 'dropdown'],
								'compare' => 'in'
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_children_only',
						'type' => 'checkbox',
						'label' => __( 'Only show children of the current category', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => ['list', 'dropdown'],
								'compare' => 'in'
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'hide_empty',
						'type' => 'checkbox',
						'label' => __( 'Hide empty', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'range',
								'compare' => '!='
							],
							[
								'name' => 'display_type',
								'value' => 'range_points',
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'order_by',
						'type' => 'select',
						'label' => __( 'Order By', 'rey-core' ),
						'value' => 'name',
						'options' => [
							'name'  => esc_html__( 'Name', 'rey-core' ),
							'menu_order'  => esc_html__( 'Attributes Order', 'rey-core' ),
						],
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'range',
								'compare' => '!='
							],
							[
								'name' => 'display_type',
								'value' => 'range_points',
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_checkboxes',
						'type' => 'checkbox',
						'label' => __( 'Show checkboxes', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => $this->checkbox_support,
								'compare' => 'in'
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_checkboxes__radio',
						'type' => 'checkbox',
						'label' => __( 'Display checkboxes as radio', 'rey-core' ),
						'value' => '1',
						'wrapper_class' => '--dep-left',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => $this->checkbox_support,
								'compare' => 'in'
							],
							[
								'name' => 'show_checkboxes',
								'value' => true,
								'compare' => '=='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'search_box',
						'type' => 'checkbox',
						'label' => __( 'Show search (filter) field', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => $this->search_box_support,
								'compare' => 'in',
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'range_step',
						'type' => 'number',
						'label' => __( 'Range Step', 'rey-core' ),
						'value' => 1,
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'range',
								'compare' => '=='
							],
						],
						'options' => [
							'step' => 0.1,
							'min' => 0.1,
							'max' => 1000,
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => __( 'LIST OPTIONS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						],
						'field_class' => 'rey-widget-innerTitle'
					]);

					$list_condition = wp_json_encode([
						[
							'name' => $display_name,
							'value' => 'list',
							'compare' => '==='
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'rey_multi_col',
						'type' => 'checkbox',
						'label' => __( 'Display list on 2 columns', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
							[
								'name' => 'hierarchical',
								'value' => true,
								'compare' => '!='
							],
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'accordion_list',
						'type' => 'checkbox',
						'label' => __( 'Display list as accordion', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
							[
								'name' => 'hierarchical',
								'value' => true,
								'compare' => '=='
							],
							[
								'name' => 'show_children_only',
								'value' => true,
								'compare' => '!='
							],
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'alphabetic_menu',
						'type' => 'checkbox',
						'label' => __( 'Show alphabetic menu', 'rey-core' ),
						'value' => '1',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'list',
								'compare' => '=='
							],
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'custom_height',
						'type' => 'number',
						'label' => __( 'Custom Height', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => ['list', 'color_list'],
								'compare' => 'in'
							],
						],
						'options' => [
							'step' => 1,
							'min' => 50,
							'max' => 1000,
						],
						'suffix' => 'px'
					]);

					?>

					<p data-condition='<?php echo wp_json_encode([
							[
								'name' => $display_name,
								'value' => $this->dimensions_support,
								'compare' => 'in'
							]
						]); ?>'><strong><?php esc_html_e('COLOR / BUTTON OPTIONS', 'rey-core') ?></strong></p>

					<p id="<?php echo $this->get_field_id('rey_width'); ?>-wrapper" data-condition='<?php echo wp_json_encode([
							[
								'name' => $display_name,
								'value' => $this->dimensions_support,
								'compare' => 'in'
							]
						]); ?>'>
						<label for="<?php echo $this->get_field_id('rey_width'); ?>">
							<?php _e( 'Item Width (px)', 'rey-core' ); ?>
						</label>
						<input class="tiny-text" type="number" step="1" min="10" max="200" value="<?php echo esc_attr($instance['rey_width']) ?>" id="<?php echo $this->get_field_id('rey_width'); ?>" name="<?php echo $this->get_field_name('rey_width'); ?>" style="width: 60px" />
					</p>

					<p id="<?php echo $this->get_field_id('rey_height'); ?>-wrapper" data-condition='<?php echo wp_json_encode([
							[
								'name' => $display_name,
								'value' => $this->dimensions_support,
								'compare' => 'in'
							]
						]); ?>'>
						<label for="<?php echo $this->get_field_id('rey_height'); ?>">
							<?php _e( 'Item Height (px)', 'rey-core' ); ?>
						</label>
						<input class="tiny-text" type="number" step="1" min="10" max="200" value="<?php echo esc_attr($instance['rey_height']) ?>" id="<?php echo $this->get_field_id('rey_height'); ?>" name="<?php echo $this->get_field_name('rey_height'); ?>" style="width: 60px" />
					</p>

					<p id="<?php echo $this->get_field_id('show_tooltips'); ?>-wrapper" data-condition='<?php echo wp_json_encode([
							[
								'name' => $display_name,
								'value' => $this->tooltip_support,
								'compare' => 'in'
							]
						]); ?>'>
						<input id="<?php echo $this->get_field_id('show_tooltips'); ?>" name="<?php echo $this->get_field_name('show_tooltips'); ?>" type="checkbox" value="1" <?php checked( $instance['show_tooltips'] ); ?>>
						<label for="<?php echo $this->get_field_id('show_tooltips'); ?>"><?php esc_html_e('Show tooltips', 'rey-core'); ?></label>
					</p>

					<?php

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'range_attrs',
						'type' => 'range_points',
						'label' => __( 'RANGES', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'range_points',
								'compare' => '==='
							]
						],
						'supports' => ['labels']
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => __( 'DROPDOWN OPTIONS', 'rey-core' ),
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'field_class' => 'rey-widget-innerTitle'
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'placeholder',
						'type' => 'text',
						'label' => __( 'Placeholder', 'rey-core' ),
						'value' => '',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'placeholder' => esc_html__('eg: Choose', 'rey-core')
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'dd_width',
						'type' => 'number',
						'label' => __( 'Custom dropdown width', 'rey-core' ),
						'value' => '',
						'field_class' => 'small-text',
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'dropdown',
								'compare' => '=='
							],
						],
						'options' => [
							'step' => 1,
							'min' => 50,
							'max' => 1000,
						],
						'suffix' => 'px'
					]);

					?>
				</div>
				<!-- end tab -->

				<div class="rey-widgetTabs-tabContent" data-tab="advanced">

					<?php

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_hide_categories',
						'type' => 'select',
						'label' => __( 'Show or Hide widget on certain categories:', 'rey-core' ),
						'value' => 'hide',
						'options' => [
							'show' => esc_html__('Show', 'rey-core'),
							'hide' => esc_html__('Hide', 'rey-core'),
						]
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'show_only_on_categories',
						'type' => 'select',
						'multiple' => true,
						'label' => __( 'Categories:', 'rey-core' ),
						'wrapper_class' => '--stretch',
						'options' => function_exists('reycore_wc__product_categories') ? reycore_wc__product_categories() : []
					]);

					echo '<hr>';

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'selective_display',
						'type' => 'select',
						'label' => __( 'Display widget only on:', 'rey-core' ),
						'value' => '',
						'options' => [
							'' => esc_html__('- Select -', 'rey-core'),
							'shop' => esc_html__('Shop Page', 'rey-core'),
							'cat' => esc_html__('Categories', 'rey-core'),
							'attr' => esc_html__('Attributes', 'rey-core'),
							'tag' => esc_html__('Tags', 'rey-core'),
							'cat_attr_tag' => esc_html__('Categories & Attributes & Tags', 'rey-core'),
						]
					]);

					echo '<hr>';

					reyajaxfilter_widget__option( $this, $instance, [
						'name' => 'query_type',
						'type' => 'select',
						'label' => __( 'Query Type', 'rey-core' ),
						'field_class' => 'widefat',
						'options' => [
							'or' => esc_html__('OR (IN)', 'rey-core'),
							'and' => esc_html__('AND', 'rey-core'),
						],
						'conditions' => [
							[
								'name' => 'display_type',
								'value' => 'range',
								'compare' => '!='
							],
							[
								'name' => 'display_type',
								'value' => 'range_points',
								'compare' => '!='
							],
						],
					]);

					reyajaxfilter_widget__option( $this, $instance, [
						'type' => 'title',
						'label' => '<small>' . __( 'Using "AND" query type is very strict and might return empty results. Not to be confused with multiple filters!', 'rey-core' ) . '</small>',
						'conditions' => [
							[
								'name' => 'query_type',
								'value' => 'and',
								'compare' => '==='
							],
						],
						'wrapper_class' => 'description',
						'field_class' => '',
					]);

					?>

				</div>
				<!-- end tab -->

			</div>

			<?php
			reyajaxfilters__filter_admin_titles( $instance['show_only_on_categories'], $instance['show_hide_categories'] );
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
if (!function_exists('reyajaxfilter_register_attribute_filter_widget')) {
	function reyajaxfilter_register_attribute_filter_widget() {
		register_widget('REYAJAXFILTERS_Attribute_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_attribute_filter_widget');
}
