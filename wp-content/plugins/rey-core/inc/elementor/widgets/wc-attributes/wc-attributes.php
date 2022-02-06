<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Wc_Attributes') ):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Wc_Attributes extends \Elementor\Widget_Base {

	private $letters = [];
	private $_settings = [];
	private $_attribute_terms = [];

	public function get_name() {
		return 'reycore-wc-attributes';
	}

	public function get_title() {
		return __( 'WooCommerce Attributes', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-menu-toggle';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#woocommerce-attributes';
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['attr_id']
        );

        return $element;
	}

	private $colors = [];

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$terms = $this->get_wc_terms();

		$this->add_control(
			'attr_id',
			[
				'label' => __( 'Select Attribute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $terms,
			]
		);

		$this->add_control(
			'attr_list',
			[
				'label' => esc_html__( 'Attributes Count', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all'  => esc_html__( 'All', 'rey-core' ),
					'limited'  => esc_html__( 'Limited number', 'rey-core' ),
					'handpicked'  => esc_html__( 'Manually Handpicked', 'rey-core' ),
					'handpicked_order'  => esc_html__( 'Manually Handpicked (Custom order)', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'attr_limit',
			[
				'label' => esc_html__( 'Display limit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'attr_id!' => '',
					'attr_list' => 'limited',
				],
			]
		);

		foreach($terms as $term => $term_label):

			$this->add_control(
				'attr_custom_' . $term,
				[
					'label' => sprintf( esc_html__( 'Select one or more %s attributes', 'rey-core' ), $term_label ),
					'placeholder' => esc_html__('- Select-', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
						'taxonomy' => wc_attribute_taxonomy_name( $term ),
					],
					'multiple' => true,
					'default' => [],
					'label_block' => true,
					'condition' => [
						'attr_id' => $term,
						'attr_list' => 'handpicked',
					],
				]
			);


		endforeach;

		// Custom order
		$custom_order_attr = new \Elementor\Repeater();

			$custom_order_attr->add_control(
				'attr',
				[
					'label' => esc_html__( 'Select attribute', 'rey-core' ),
					'placeholder' => esc_html__('- Select-', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
					],
					'label_block' => true,
					'default' => [],
				]
			);

		$this->add_control(
			'handpicked_order_attrs',
			[
				'label' => __( 'Manually add attributes (with ordering)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $custom_order_attr->get_controls(),
				'default' => [],
				'condition' => [
					'attr_list' => 'handpicked_order',
				],
			]
		);

		$this->add_control(
			'layout',
			[
				'label' => esc_html__( 'Layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => esc_html__( 'Default', 'rey-core' ),
					'alphabetic'  => esc_html__( 'Alphabetic List', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_sett_alphabetic',
			[
				'label' => __( 'Alphabetic Layout settings', 'rey-core' ),
				'condition' => [
					'layout' => 'alphabetic',
				],
			]
		);

		$this->add_responsive_control(
			'alphabetic_columns',
			[
				'label' => __( 'Letter Blocks Columns', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 4,
				'step' => 1,
				'condition' => [
					'layout' => 'alphabetic',
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphaItem' => '-ms-flex-preferred-size: calc(100% / {{VALUE}}); flex-basis: calc(100% / {{VALUE}});',
				],
				'render_type' => 'template',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'alphabetic_title_typo',
				'label' => esc_html__( 'Alphabetic Titles - Typo', 'rey-core' ),
				'selector' => '{{WRAPPER}} .reyEl-wcAttr-alphaItem > h4',
			]
		);

		$this->add_control(
			'alphabetic_title_color',
			[
				'label' => esc_html__( 'Alphabetic Titles - Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphaItem > h4' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'alphabetic_menu_title',
			[
			   'label' => esc_html__( 'ALPHABETIC MENU', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'alphabetic_menu',
			[
				'label' => esc_html__( 'Show Alphabetic Menu', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'layout' => 'alphabetic',
				],
			]
		);

		$this->add_control(
			'alphabetic_menu_align',
			[
				'label' => __( 'Alphabetic Menu - Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu' => 'justify-content: {{VALUE}}',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'alphabetic_menu_typo',
				'label' => esc_html__( 'Alphabetic Menu - Typo', 'rey-core' ),
				'selector' => '{{WRAPPER}} .reyEl-wcAttr-alphMenu a',
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_control(
			'alphabetic_menu_color',
			[
				'label' => esc_html__( 'Alphabetic Menu - Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'alphabetic_menu_spacing',
			[
			   'label' => esc_html__( 'Alphabetic Menu - Spacing', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 9,
						'max' => 180,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu a' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'alphabetic_menu_margin',
			[
				'label' => __( 'Alphabetic Menu - Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_sett_advanced',
			[
				'label' => __( 'Advanced settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'hide_empty',
			[
				'label' => __( 'Hide Empty', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => '',
				'condition' => [
					'attr_id!' => '',
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'name',
				'options' => [
					'name'  => __( 'Name', 'rey-core' ),
					'menu_order' => __( 'Menu Order', 'rey-core' ),
					'count'  => __( 'Count', 'rey-core' ),
					'rand'  => __( 'Random', 'rey-core' ),
				],
				'condition' => [
					'attr_id!' => '',
				],
			]
		);

		$this->add_control(
			'url',
			[
				'label' => __( 'Custom URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'eg: https://your-link.com', 'rey-core' ),
				'show_external' => false,
				'default' => [
					'url' => '',
				],
				'description' => __( 'Leave empty to link at default shop page.', 'rey-core' ),
				'condition' => [
					'attr_id!' => '',
				],
			]
		);

		$this->add_control(
			'filter_type',
			[
				'label' => __( 'Link Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => __( 'WooCommerce Default', 'rey-core' ),
					'wc_ajax'  => __( 'Rey - Ajax Filter', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'display',
			[
				'label' => __( 'Display as', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'list',
				'options' => [
					'list'  => __( 'List', 'rey-core' ),
					'color'  => __( 'Color', 'rey-core' ),
					'clist'  => __( 'Color List', 'rey-core' ),
					'button'  => __( 'Button', 'rey-core' ),
					'image'  => __( 'Image', 'rey-core' ),
				],
				// 'prefix_class' => 'reyEl-wcAttr--'
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __( 'Columns', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 4,
				'step' => 1,
				'condition' => [
					'display' => 'list',
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list > li' => '-ms-flex-preferred-size: calc(100% / {{VALUE}}); flex-basis: calc(100% / {{VALUE}});',
				],
				'render_type' => 'template',

			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-filterList--list a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .rey-filterList--list a',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Text Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'prefix_class' => 'elementor%s-align-',
			]
		);

		$this->add_control(
			'width',
			[
				'label' => esc_html__( 'Item Width (px)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-filterList a' => 'width: {{VALUE}}px',
				],
				'condition' => [
					'display!' => ['list', 'clist'],
				],
			]
		);

		$this->add_control(
			'height',
			[
				'label' => esc_html__( 'Item Height (px)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-filterList a' => 'height: {{VALUE}}px',
				],
				'condition' => [
					'display!' => ['list', 'clist'],
				],
			]
		);

		$this->add_control(
			'enable_widget_height',
			[
				'label' => esc_html__( 'Custom widget height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--wCustomHeight-',
			]
		);

		$this->add_control(
			'widget_height',
			[
				'label' => esc_html__( 'Widget Height', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 500,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}.--wCustomHeight-yes .reyEl-wcAttr' => 'height: {{VALUE}}px',
				],
				'condition' => [
					'enable_widget_height!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_wc_terms(){

		$attrs = [];

		foreach( wc_get_attribute_taxonomies() as $attribute ) {
			$attribute_name = $attribute->attribute_name;
			$attrs[$attribute_name] = $attribute->attribute_label;
		}

		return $attrs;
	}

	protected function get_attributes(){

		if ( !empty($this->_settings['attr_id']) ) :

			$query_args['orderby'] = $this->_settings['orderby'];
			$query_args['taxonomy'] = wc_attribute_taxonomy_name($this->_settings['attr_id'] );
			$query_args['hide_empty'] = $this->_settings['hide_empty'] === 'yes';

			if( $this->_settings['attr_list'] === 'limited' && ($attr_limit = $this->_settings['attr_limit']) ){
				$query_args['number'] = $attr_limit;
			}

			if( $this->_settings['attr_list'] === 'handpicked' &&
				isset($this->_settings[ 'attr_custom_' . $this->_settings['attr_id'] ]) && ($custom_terms = $this->_settings[ 'attr_custom_' . $this->_settings['attr_id'] ]) ){

				return reyCoreHelper()->get_terms( [
					'term_taxonomy_id'  => $custom_terms,
				] + $query_args );
			}

			if( $this->_settings['attr_list'] === 'handpicked_order' &&
				isset($this->_settings[ 'handpicked_order_attrs' ]) && ($handpicked_ordered_terms = $this->_settings[ 'handpicked_order_attrs' ]) ){
				$handpicked_ordered_terms__clean  = array_filter( wp_list_pluck($handpicked_ordered_terms, 'attr') );
				$query_args['include']  = $handpicked_ordered_terms__clean;
				$query_args['orderby']  = 'include';
				unset($query_args['taxonomy']);
				return get_terms( $query_args );
			}

			return reyCoreHelper()->get_terms( $query_args );
		endif;

		return [];
	}

	protected function get_term_tag( $attr, $type = 'color' ) {

		if( class_exists('ReyCore_WooCommerce_Variations') ){

			$term_id = $attr->term_id;

			if( 'color' === $type ){
				$colors = ReyCore_WooCommerce_Variations::getInstance()->get_color_attributes();

				if( is_array($colors) && !empty($colors) && isset($colors[$term_id]) ){

					$swatch_tag = ReyCore_WooCommerce_Variations::parse_attribute_color([
						'value' => $attr->slug,
						'name' => $attr->name
					], $colors[$term_id]['color']);

					return $swatch_tag;
				}
			}

			elseif( 'image' === $type ){

				$images = ReyCore_WooCommerce_Variations::getInstance()->get_image_attributes();

				if( is_array($images) && !empty($images) && isset($images[$term_id]) ){

					$swatch_tag = wp_get_attachment_image( $images[$term_id]['image'], 'thumbnail' );
					// $swatch_tag = wp_get_attachment_image_src( absint($images[$term_id]['image'] ), 'thumbnail');
					// $swatch_tag = $swatch_tag[0];

					return $swatch_tag;
				}
			}
		}

		return '';
	}

	public function render_link($attr){

		$settings = $this->get_settings_for_display();

		$url = !empty($settings['url']['url']) ? $settings['url']['url'] : get_permalink( wc_get_page_id( 'shop' ) );

		$html = '';

		// ajax filters
		if( $settings['filter_type'] == 'wc_ajax' ) {
			$link = sprintf('%1$s?attro-%2$s=%3$s', $url, $settings['attr_id'], $attr->term_id);
		}

		// WooCommerce Default
		else {

			// check if has archive
			$attributes = wc_get_attribute_taxonomies();
			$attribute = array_filter($attributes, function($value) use ($attr){
				return wc_attribute_taxonomy_name( $value->attribute_name ) === $attr->taxonomy && $value->attribute_public;
			});
			// if term has archive, get its link
			if( !empty($attribute) ){
				$link = get_term_link( $attr );
			}
			// use WC's built-in filter links
			else {
				$link = sprintf('%1$s?filter_%2$s=%3$s&query_type_%2$s=or', $url, $settings['attr_id'], $attr->slug);
			}
		}

		switch( $settings['display'] ):

			case"color":
				$tag = $this->get_term_tag( $attr, 'color' );

				$html = sprintf(
						'<a href="%s">%s</a>',
						esc_url($link),
						$tag
				);
				break;

			case"clist":
				$tag = $this->get_term_tag( $attr, 'color' );

				$html = sprintf(
						'<a href="%s">%s</a>',
						esc_url($link),
						$tag . esc_html( $attr->name )
				);

				break;

			case"image":
				$tag = $this->get_term_tag( $attr, 'image' );

				$html = sprintf(
						'<a href="%s">%s</a>',
						esc_url($link),
						$tag
				);
				break;

			default:
				$html = sprintf( '<a href="%s">%s</a>', esc_url($link), esc_html( $attr->name ) );

		endswitch;

		return apply_filters('reycore/elementor/wc-attributes/render_link', $html, $settings, $attr, $link );
	}

	function get_letters() {

		if( !(isset($this->_settings['layout']) && $this->_settings['layout'] === 'alphabetic') ){
			return false;
		}

		foreach ($this->_attribute_terms as $key => $term) {
			if( strlen($term->name) > 0 ){
				$letter = mb_substr($term->name, 0, 1, 'UTF-8');
				$this->letters[$letter][] = $term;
			}
		}

		return true;
	}

	function render_alphabetic_menu(){

		if( empty( $this->letters ) ){
			return;
		}

		if( !(isset($this->_settings['alphabetic_menu']) && $this->_settings['alphabetic_menu'] === 'yes') ){
			return false;
		}

		$alpha_menu_items = [];

		foreach ($this->letters as $letter => $term) {
			$alpha_menu_items[] .= sprintf( '<li><a href="#letter-%1$s" class="js-scroll-to">%1$s</a></li>', $letter );
		}

		if( !empty($alpha_menu_items) ){
			printf( '<ul class="reyEl-wcAttr-alphMenu">%s</ul>', implode('', $alpha_menu_items) );
		}
	}

	public function render_attributes($attribute_terms = []){

		if( !empty($attribute_terms) ) {
			$class = $this->_settings['columns'] > 1 ? '--flex' : '';
			echo '<ul class="reyEl-wcAttr-list ' . $class . '">';
			foreach( $attribute_terms as $key => $attr ) {
				printf( '<li>%s</li>', $this->render_link( $attr ) );
			}
			echo '</ul>';

		}
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		reyCoreAssets()->add_styles(['reycore-widget-wc-attributes-styles', 'rey-wc-tag-attributes']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();
		$this->_attribute_terms = $this->get_attributes();

		$this->get_letters();

		$classes = [
			'rey-element',
			'reyEl-wcAttr',
			'rey-filterList',
			'rey-filterList--' . $this->_settings['display']
		];

		if( !empty( $this->letters ) ){
			$classes[] = 'reyEl-wcAttr--alphabeticList';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

			<?php
				if( !empty( $this->letters ) ){

					$this->render_alphabetic_menu();

					foreach ($this->letters as $letter => $terms) {
						echo '<div class="reyEl-wcAttr-alphaItem">';
						printf('<h4 id="letter-%1$s">%1$s</h4>', $letter);
						$this->render_attributes($terms);
						echo '</div>';
					}
				}
				else {
					$this->render_attributes( $this->_attribute_terms );
				}
			?>
		</div>
		<?php
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}

endif;
