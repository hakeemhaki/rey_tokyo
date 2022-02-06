<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Product_Grid') ):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Product_Grid extends \Elementor\Widget_Base {

	private $query_args = [];

	private $product_archive;

	public function get_name() {
		return 'reycore-product-grid';
	}

	public function get_title() {
		return __( 'Products Grid', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-woocommerce';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return ['reycore-woocommerce'];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'products', 'carousel', 'grid', 'shop' ];
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#products-grid';
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Product_Grid__Carousel( $this ) );
		$this->add_skin( new ReyCore_Widget_Product_Grid__Carousel_Section( $this ) );
		$this->add_skin( new ReyCore_Widget_Product_Grid__Mini( $this ) );
	}

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
			'section_layout',
			[
				'label' => __( 'Layout settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'offset',
			[
				'label' => esc_html__( 'Load more offset', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => 0,
			]
		);

		$this->add_responsive_control(
			'per_row',
			[
				'label' => __( 'Products per row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 6,
				'default' => reycore_wc_get_columns('desktop'),
				'condition' => [
					'_skin' => ['', 'mini'],
				],
				'selectors' => [
					'{{WRAPPER}} ul.products' => '--woocommerce-grid-columns: {{VALUE}}',
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => __( 'Limit products', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 4,
				// 'render_type' => 'template',
				'min' => 1,
				'max' => 200,
			]
		);

		$this->add_control(
			'ajax_load_more',
			[
				'label' => __( 'Ajax Load More (button)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_skin' => ['', 'mini'],
					'paginate' => '',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'ajax_load_more_limit',
			[
				'label' => esc_html__( 'Items per load request', 'rey-core' ),
				'description' => esc_html__( 'How many items to load when clicking on Load more button. Default is the product count of 1 row.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'condition' => [
					'_skin' => ['', 'mini'],
					'paginate' => '',
					'ajax_load_more!' => '',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_max',
			[
				'label' => esc_html__( 'Maximum requests', 'rey-core' ),
				'description' => esc_html__( 'How many requests will be allowed to be made until the button will get disabled.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 80,
				'step' => 1,
				'condition' => [
					'_skin' => ['', 'mini'],
					'paginate' => '',
					'ajax_load_more!' => '',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_text',
			[
				'label' => esc_html__( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: LOAD MORE', 'rey-core' ),
				'condition' => [
					'_skin' => ['', 'mini'],
					'paginate' => '',
					'ajax_load_more!' => '',
				],
			]
		);

		$this->add_control(
			'paginate',
			[
				'label' => __( 'Transform to Product Archive', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_skin' => '',
					'ajax_load_more' => '',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'paginate_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => 'This option transforms this product grid element into an archive-like catalog. <strong>Should only be once in this page</strong>!',
				'content_classes' => 'rey-raw-html --em',
				'condition' => [
					'paginate' => 'yes',
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'show_header',
			[
				'label' => __( 'Show Header', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'paginate' => 'yes',
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'show_view_selector',
			[
				'label' => __( 'Show View Selector', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => esc_html__('If enabled, the grid will use the stored user selected column number.', 'rey-core'),
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
					'_skin' => '',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'shop_catalog',
				'separator' => 'before',
				'condition' => [
					'_skin!' => 'carousel-section',
				],
			]
		);

		$this->add_control(
			'horizontal_desktop',
			[
				'label' => esc_html__( 'Horizontal Scroll desktop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'desktop',
				'prefix_class' => '--horizontal-',
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'horizontal_tablet',
			[
				'label' => esc_html__( 'Horizontal Scroll tablet', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'tablet',
				'prefix_class' => '--horizontal-',
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'horizontal_mobile',
			[
				'label' => esc_html__( 'Horizontal Scroll mobile', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'mobile',
				'prefix_class' => '--horizontal-',
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'horizontal_threshold',
			[
				'label' => esc_html__( 'Size threshold', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0.65,
				'max' => 1,
				'step' => 0.01,
				'selectors' => [
					'{{WRAPPER}} li.product' => '--size-threshold: {{VALUE}}',
				],
				'conditions' => [
					'relation' => 'and',
					'terms' => [
						[
							'name' => '_skin',
							'operator' => '==',
							'value' => ''
						],
						[
							'relation' => 'or',
							'terms' => [
								['name' => 'horizontal_desktop', 'operator' => '!==', 'value' => ''],
								['name' => 'horizontal_tablet', 'operator' => '!==', 'value' => ''],
								['name' => 'horizontal_mobile', 'operator' => '!==', 'value' => ''],
							],
						]
					]
				],
			]
		);

		$this->add_control(
			'lazy_load',
			[
				'label' => esc_html__( 'Lazy Load', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
				'condition' => [
					'_skin' => ['', 'mini', 'carousel'],
					'paginate' => '',
				],
			]
		);

		$this->add_responsive_control(
			'lazy_load_placeholders_height',
			[
				'label' => esc_html__( 'Placeholder height', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--lazy-placeholder-height: {{VALUE}}px',
				],
				'condition' => [
					'_skin' => ['', 'mini', 'carousel'],
					'paginate' => '',
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_placeholders_bg',
			[
				'label' => esc_html__( 'Placeholder color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--lazy-placeholder-bg: {{VALUE}}',
				],
				'condition' => [
					'_skin' => ['', 'mini', 'carousel'],
					'paginate' => '',
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_trigger',
			[
				'label' => esc_html__( 'Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'scroll',
				'options' => [
					'scroll'  => esc_html__( 'On Scroll', 'rey-core' ),
					'click'  => esc_html__( 'On Click', 'rey-core' ),
					'mega-menu'  => esc_html__( 'On Mega Menu display', 'rey-core' ),
				],
				'condition' => [
					'_skin' => ['', 'mini', 'carousel'],
					'paginate' => '',
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_click_trigger',
			[
				'label' => esc_html__( 'Click Selector', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: .custom-unique-selector', 'rey-core' ),
				'condition' => [
					'_skin' => ['', 'mini', 'carousel'],
					'paginate' => '',
					'lazy_load!' => '',
					'lazy_load_trigger' => 'click',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Query Settings
		 */

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Products Query', 'rey-core' ),
			]
		);

		$this->add_control(
            'query_type',
            [
                'label' => esc_html__('Query Type', 'rey-core'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'recent',
                'options' => [
                    'recent'           => esc_html__('Latest', 'rey-core'),
                    'featured'         => esc_html__('Featured', 'rey-core'),
                    'best-selling'     => esc_html__('Best Selling', 'rey-core'),
                    'sale'             => esc_html__('Sale', 'rey-core'),
                    'top'              => esc_html__('Top Rated', 'rey-core'),
                    'manual-selection' => esc_html__('Manual Selection', 'rey-core'),
					'recently-viewed'  => esc_html__('Recently viewed', 'rey-core'),
					'related'  => esc_html__('Related', 'rey-core'),
					'cross-sells'  => esc_html__('Cross-Sells', 'rey-core'),
					'up-sells'  => esc_html__('Up-Sells', 'rey-core'),
					'current_query'  => esc_html__('Current Query', 'rey-core'),
					'custom'  => esc_html__('Custom', 'rey-core'),
                ],
            ]
		);

		// Advanced settings

		$this->add_control(
			'advanced',
			[
				'label' => __( 'Advanced settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'condition' => [
					'query_type!' => ['recently-viewed', 'current_query'],
				],
			]
		);

		$this->add_control(
			'include',
			[
				'label'       => esc_html__( 'Product(s)', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 21, 22',
				'label_block' => true,
				'description' => __( 'Add product IDs separated by comma.', '' ),
				'condition' => [
					'query_type' => ['manual-selection', 'current_query'],
				],
			]
		);

        $this->add_control(
            'categories',
            [
                'label' => esc_html__('Product Category', 'rey-core'),
                'placeholder' => esc_html__('- Select category -', 'rey-core'),
                'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_cat',
					'field' => 'slug'
				],
                'label_block' => true,
                'multiple' => true,
				'default'     => [],
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
				],
            ]
		);

		$this->add_control(
			'categories_query_type',
            [
                'label' => esc_html__('Category - Operator Type', 'rey-core'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'or',
				'options' => [
					'or'  => esc_html__( 'OR (IN)', 'rey-core' ),
					'and'  => esc_html__( 'AND', 'rey-core' ),
					'not_in'  => esc_html__( 'NOT IN', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
					'categories!' => [],
				],
			]
		);

		$this->add_control(
            'tags',
            [
                'label' => esc_html__('Product Tags', 'rey-core'),
                'placeholder' => esc_html__('- Select tag -', 'rey-core'),
                'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'product_tag',
					'field' => 'slug'
				],
                'label_block' => true,
                'multiple' => true,
				'default'     => [],
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
				],
            ]
		);

		$this->add_control(
			'tags_query_type',
            [
                'label' => esc_html__('Tags - Operator Type', 'rey-core'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'or',
				'options' => [
					'or'  => esc_html__( 'OR (IN)', 'rey-core' ),
					'and'  => esc_html__( 'AND', 'rey-core' ),
					'not_in'  => esc_html__( 'NOT IN', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
					'tags!' => [],
				],
			]
		);

		$attributes = apply_filters('reycore/elementor/product_grid/attributes', [] );

		$this->add_control(
			'attribute',
			[
				'label' => __( 'Product Attribute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $attributes,
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
				],
			]
		);

		foreach($attributes as $term => $term_label):
			$this->add_control(
				'attribute__' . $term,
				[
					'label' => sprintf( esc_html__( 'Select one or more %s attributes', 'rey-core' ), $term_label ),
					'placeholder' => esc_html__('- Select -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
						'taxonomy' => $term,
						'field' => 'slug'
					],
					'multiple' => true,
					'default' => [],
					'label_block' => true,
					'condition' => [
						'attribute' => $term,
						'query_type!' => ['manual-selection', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
					],
				]
			);
		endforeach;


		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order by', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' => __( 'Date', 'rey-core' ),
					'title' => __( 'Title', 'rey-core' ),
					'price' => __( 'Price', 'rey-core' ),
					'popularity' => __( 'Popularity', 'rey-core' ),
					'rating' => __( 'Rating', 'rey-core' ),
					'rand' => __( 'Random', 'rey-core' ),
					'menu_order' => __( 'Menu Order', 'rey-core' ),
					// 'menu_order title' => __( 'Menu Order + Title', 'rey-core' ),
					// 'menu_order date' => __( 'Menu Order + Date', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => ['manual-selection', 'recent', 'top', 'best-selling', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __( 'ASC', 'rey-core' ),
					'desc' => __( 'DESC', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => ['manual-selection', 'recent', 'top', 'best-selling', 'recently-viewed', 'current_query', 'related', 'cross-sells', 'up-sells'],
				],
			]
		);

		$this->add_control(
            'custom_product_id',
            [
                'label' => esc_html__('Select the product', 'rey-core'),
                'description' => esc_html__('Leave unselected to automatically get the product ID based on current page.', 'rey-core'),
                'placeholder' => esc_html__('- Select product -', 'rey-core'),
                'type' => 'rey-query',
				'query_args' => [
					'type' => 'posts',
					'post_type' => 'product',
				],
                'label_block' => true,
                'multiple' => false,
				'default'     => [],
				'condition' => [
					'query_type' => ['cross-sells', 'up-sells', 'related'],
				],
            ]
		);

		$this->add_control(
			'hide_out_of_stock',
			[
				'label' => esc_html__( 'Hide Out of stock items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'exclude',
			[
				'label'       => esc_html__( 'Exclude Product(s)', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 21, 22',
				'label_block' => true,
				'description' => __( 'Add product IDs separated by comma.', '' ),
				'condition' => [
					'query_type!' => ['manual-selection', 'recently-viewed', 'related', 'cross-sells', 'up-sells'],
				],
			]
		);

		$this->add_control(
			'exclude_duplicates',
			[
				'label' => __( 'Exclude Duplicate Products', 'rey-core' ),
				'description' => __( 'Exclude duplicate products that were already loaded in this page', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'debug__show_query',
			[
				'label' => esc_html__( 'Debug: Show query', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();

		if( class_exists('ReyCore_WooCommerce_ProductArchive') ){
			ReyCore_WooCommerce_ProductArchive::add_component_display_controls( $this );
			ReyCore_WooCommerce_ProductArchive::add_extra_data_controls( $this );
			ReyCore_WooCommerce_ProductArchive::add_common_styles_controls( $this );
		}

		$this->start_controls_section(
			'section_styles_ajax_load_more',
			[
				'label' => __( 'Ajax Load More - Button styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'ajax_load_more!' => '',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'ajax_load_more_typo',
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .btn.rey-pg-loadmore',
			]
		);

		$this->add_control(
			'ajax_load_more_btn_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-line-active',
				'options' => [
					'btn-simple'  => __( 'Link', 'rey-core' ),
					'btn-primary'  => __( 'Primary', 'rey-core' ),
					'btn-secondary'  => __( 'Secondary', 'rey-core' ),
					'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
					'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'ajax_load_more_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn.rey-pg-loadmore' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_hover_color',
			[
				'label' => __( 'Text Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn.rey-pg-loadmore:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn.rey-pg-loadmore' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_hover_bg_color',
			[
				'label' => __( 'Background Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .btn.rey-pg-loadmore:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-pg-loadmoreWrapper' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_radius',
			[
				'label' => esc_html__( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .btn.rey-pg-loadmore' => 'border-radius: {{VALUE}}px',
				],
			]
		);

		$this->add_control(
			'ajax_load_more_distance',
			[
				'label' => esc_html__( 'Top Distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-pg-loadmoreWrapper' => '--load-more-distance: {{VALUE}}px',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_layout_title',
			[
				'label' => __( 'Title (deprecated)', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_section_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__('These controls are deprecated and will get removed at some point. Please use the Component Styles options and set title styles there.', 'rey-core'),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typo',
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .woocommerce-loop-product__title a',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Title Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-loop-product__title a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label' => __( 'Title Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-loop-product__title a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
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

		reyCoreAssets()->add_styles(['reycore-general']);
		reyCoreAssets()->add_scripts( ['reycore-woocommerce', 'reycore-widget-product-grid-scripts'] );

		$this->_settings = $this->get_settings_for_display();

		if( ! class_exists('ReyCore_WooCommerce_ProductArchive') ){
			return;
		}

		$args = [
			'name'        => 'product_grid_element',
			'filter_name' => 'product_grid',
			'main_class'  => 'reyEl-productGrid',
			'el_instance' => $this
		];

		$this->product_archive = new ReyCore_WooCommerce_ProductArchive( $args, $this->_settings );

		if( $this->product_archive->lazy_start() ){
			return;
		}

		reyCoreAssets()->add_styles( ['rey-wc-loop', 'reycore-widget-product-grid-styles'] );

		if ( $this->product_archive->get_query_results() ) {
			$this->product_archive->render_start();
				$this->product_archive->loop_start();
					$this->product_archive->render_products();
				$this->product_archive->loop_end();
			$this->product_archive->render_end();
		}
		else {
			wc_get_template( 'loop/no-products-found.php' );
		}

		$this->product_archive->lazy_end();

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
