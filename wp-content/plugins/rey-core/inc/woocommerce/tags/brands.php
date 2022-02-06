<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Brands') ):

class ReyCore_WooCommerce_Brands
{

	private static $_instance = null;

	public $has_archive = false;

	private $brand_terms = [];

	/**
	 * ReyCore_WooCommerce_Brands constructor.
	 */
	private function __construct()
	{
		// bail if already using WooCommerce Brands
		if( class_exists( 'WC_Brands' ) ){
			return;
		}

		add_action( 'admin_init', [$this, 'admin_filters'] );
		add_action( 'woocommerce_single_product_summary', [$this, 'product_page_show_brands'], 6 );
		add_filter( 'woocommerce_structured_data_product', [$this, 'structured_data_brands'], 10, 2 );
		add_filter( 'saswp_modify_product_schema_output', [$this, 'saswp_structured_data_brands'], 10 );
		add_filter( 'wp', [$this, 'brand_tax_has_archive'], 10 );
		add_filter( 'facebook_for_woocommerce_integration_prepare_product', [$this, 'facebook_catalog_brand'], 10, 2 );
		add_filter( 'reycore/kirki_fields/field=search__include', [$this, 'add_to_search'], 20);
		add_action( 'reycore/kirki_fields/after_field=cover__shop_tag', [$this, 'add_brand_cover_option'], 10);
		add_filter( 'reycore/cover/get_cover', [$this, 'brand_cover'], 10);
		add_action( 'acf/init', [$this, 'add_brand_options_acf'], 10);
		add_action( 'elementor/element/reycore-wc-attributes/section_sett_advanced/before_section_end', [$this, 'elementor_add_brand_settings'], 10);
		add_filter( 'reycore/elementor/wc-attributes/render_link', [$this, 'elementor_output_add_image'], 10, 4);
	}

	function add_to_search($field){
		$key = $this->brand_attribute();
		$field['choices'][$key] = esc_html__( 'Brand names', 'rey-core' );
		return $field;
	}

	function structured_data_brands( $markup, $product ){

		if( ! is_array($markup) ){
			return $markup;
		}

		$markup['brand'] = [
			"@type" => "Thing",
			'name' => $this->get_brand_name()
		];

		return $markup;
	}

	/**
	 * Compatibility with `Schema & Structured Data for WP` plugin
	 * @since 1.3.0
	 */
	function saswp_structured_data_brands( $data ){

		if( isset($data['brand']) ){
			$data['brand']['name'] = $this->get_brand_name();
		}

		return $data;
	}

	/**
	 * Get Brand Attribute Name
	 *
	 * @since 1.0.0
	 **/
	function brand_attribute()
	{
		$brand = apply_filters('reycore/woocommerce/brand_attribute', get_theme_mod('brand_taxonomy', 'pa_brand'));

		return wc_clean( $brand );
	}

	/**
	 * Get Brand Attribute Name
	 *
	 * @since 1.0.0
	 **/
	function brand_attribute_excl_pa()
	{
		return wc_attribute_taxonomy_slug( $this->brand_attribute() );
	}

	function get_brands( $field = '' ){

		$product = wc_get_product();

		if( ! $product ){
			return;
		}

		$taxonomy = $this->brand_attribute();
		$terms = get_the_terms($product->get_id(), $taxonomy);

		if($terms && !empty($field) ){
			return wp_list_pluck($terms, $field);
		}

		return $terms;
	}

	/**
	 * Get Product Brand
	 *
	 * @since 1.0.0
	 */
	function get_brand_name( $id = false ){

		if( $custom_brand = apply_filters('reycore/structured_data/brand', false) ){
			return $custom_brand;
		}

		$product = wc_get_product( $id );

		if ( $product && $brand = $product->get_attribute( $this->brand_attribute() ) ) {
			return $brand;
		}

		return false;
	}

	function brand_tax_has_archive(){

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach ( $attribute_taxonomies as $attribute ) {
			if( $attribute->attribute_name === $this->brand_attribute_excl_pa() ){
				$this->has_archive = (bool) $attribute->attribute_public;
			}
		}

		return $this->has_archive;
	}

	public function brands_tax_exists(){
		return taxonomy_exists( $this->brand_attribute() );
	}

	/**
	 * Show brand attribute in loop & product
	 *
	 * @since 1.0.0
	 */
	function get_brand_link($brand = []){

		if ( empty($brand) ) {
			return '';
		}

		$brand_attribute_name = $this->brand_attribute();

		if( $this->has_archive && ($term_link = get_term_link( $brand, $brand_attribute_name )) && is_string($term_link) ){
			return esc_url( $term_link );
		}

		$shop_url = get_permalink( wc_get_page_id( 'shop' ) );

		// Default attribute filtering
		$brand_attribute_name = $this->brand_attribute_excl_pa();
		$brand_url = sprintf( '%1$s?filter_%2$s=%3$s', $shop_url, $brand_attribute_name, $brand->slug );

		return esc_url( apply_filters('reycore/woocommerce/brands/url', $brand_url, $brand_attribute_name, $shop_url ) );
	}

	/**
	 * Get Brands HTML
	 *
	 * @since 1.0.0
	 */
	function get_brands_html( $source = 'catalog' ){

		if ( ! ( $this->brands_tax_exists() && ( $brands = $this->get_brands() ) ) ) {
			return;
		}

		$product = wc_get_product();

		if( ! $product ){
			global $product;
		}

		if( $product && apply_filters('reycore/woocommerce/brands/check_visibility', is_product() ) ){
			$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );
			if( ! isset($attributes[ $this->brand_attribute() ]) ){
				return;
			}
		}

		if( method_exists($this, $source . '__brand_output') && ($html = call_user_func( [$this, $source . '__brand_output'], $brands )) ){
			echo apply_filters('reycore/woocommerce/brands/html', sprintf( '<div class="rey-brandLink">%s</div>', $html));
		}
	}

	function catalog__brand_output( $brands ){

		$html = '';

		foreach ($brands as $brand) {
			$html .= sprintf('<a href="%s">%s</a>', $this->get_brand_link( $brand ), $brand->name);
		}

		return $html;
	}

	function pdp__brand_output( $brands ){

		$html = '';
		$type = get_theme_mod('brands__pdp', 'link');
		$taxonomy = $this->brand_attribute();

		foreach ($brands as $brand) {

			$brand_content = '';

			if( $type === 'image' || $type === 'both' ){

				if( $image_id = reycore__acf_get_field( 'rey_brand_image', $taxonomy . '_' . $brand->term_id) ){
					$image_size = get_post_mime_type($image_id) === 'image/svg+xml' ? 'full' : 'thumbnail';
					$image_size = apply_filters( 'reycore/woocommerce/brands/pdp_brand_image_size', $image_size, $image_id, $type );
					$brand_content .= wp_get_attachment_image($image_id, $image_size);
				}
			}

			if( $type === 'link' || $type === 'both' ){
				$brand_content .= sprintf('<span class="__text">%s</span>', $brand->name);
			}

			if( $brand_content ){
				$html .= sprintf('<a href="%1$s">%2$s</a>', $this->get_brand_link( $brand ), $brand_content);
			}
		}

		return $html;
	}

	/**
	 * Show brand attribute in loop
	 *
	 * @since 1.0.0
	 */
	function loop_show_brands(){
		return $this->get_brands_html();
	}

	/**
	 * Show brand attribute in product
	 *
	 * @since 1.0.0
	 */
	function product_page_show_brands(){
		if( get_theme_mod('brands__pdp', 'link') === 'none' ){
			return;
		}

		return $this->get_brands_html('pdp');
	}

	/**
	 * Append brand to facebook catalog
	 * if using Facebook official plugin
	 *
	 * @since 1.6.8
	 */
	function facebook_catalog_brand( $product_data, $id ) {

		if( $brand_name = $this->get_brand_name($id) ) {
			$product_data['brand'] = $brand_name;
		}

		return $product_data;
	}


	function admin_filters(){

		if( ! $this->brands_tax_exists() ){
			return;
		}

		$this->brand_tax_has_archive();

		add_filter('manage_product_posts_columns', [$this, 'add_column']);
		add_action('manage_posts_custom_column', [$this, 'populate_column'], 10, 2);

		add_action( 'restrict_manage_posts', [$this, 'admin__add_filter_list'], 20 );
		add_action( 'pre_get_posts', [$this, 'admin__filter_products_list'] );

		// bulk edit
		add_action('woocommerce_product_bulk_edit_end',  [$this, 'admin__bulk_edit_add_brands_field']);
		add_action('woocommerce_product_bulk_edit_save',  [$this, 'admin__bulk_save'], 99);

		add_action( 'admin_head', [$this, 'fix_posts_table_layout_fixed']);

	}

	function fix_posts_table_layout_fixed(){
		echo '<style>
		table.wp-list-table.fixed.posts {
			table-layout: auto;
		}</style>';
	}

	function add_column( $column_array ) {
		$column_array['brand'] = esc_html__('Brand', 'rey-core');
		return $column_array;
	}

	function populate_column( $column_name, $post_id ) {
		if( $column_name === 'brand' ) {
			echo $this->get_brand_name($post_id);
		}
	}


	function admin__add_filter_list( $post_type, $args = [] ){

		if( $post_type !== 'product' ) {
			return;
		}

		$args = wp_parse_args($args, [
			'name'             => 'rey_brand_term',
			'by'               => 'slug',
			'check_active'     => true,
			'hide_empty'       => true,
			'unbranded_option' => true
		]);

		$brands = get_terms([
			'taxonomy'   => $this->brand_attribute(),
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => $args['hide_empty'],
			'parent'     => 0,
		]);

		$brands_options = [];

		$active = '';

		if( $args['check_active'] && isset($_GET[$args['name']]) && $active_brand = wc_clean($_GET[$args['name']]) ){
			$active = $active_brand;
		}

		foreach ($brands as $key => $brand) {
			if( isset($brand->{$args['by']}) && isset($brand->name) ){
				$brands_options[] = sprintf('<option value="%1$s" %3$s>%2$s</option>', $brand->{$args['by']}, $brand->name, selected($active, $brand->{$args['by']}, false));
			}
		}

		if( !empty($brands_options) ){
			echo sprintf('<select name="%s">', $args['name']);
			echo sprintf('<option value="">%s</option>', esc_html__('Select a brand', 'rey-core'));

			if( $args['unbranded_option'] ){
				echo sprintf('<option value="-1" %2$s>%1$s</option>', esc_html__('Unbranded', 'rey-core'), selected($active, '-1', false));
			}

			echo implode( '', $brands_options );
			echo '</select>';
		}
	}

	function admin__filter_products_list( $query ){
		global $pagenow;

		if ( ! ($query->is_admin && $pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'product') ) {
			return $query;
		}

		if( ! (isset($_GET['rey_brand_term']) && $active_brand = wc_clean($_GET['rey_brand_term'])) ){
			return $query;
		}

		$tax_query = [
			'relation' => 'AND',
		];

		$query_data = [
			'taxonomy'         => $this->brand_attribute(),
			'field'            => 'slug',
			'terms'            => $active_brand,
			'operator'         => 'IN',
			'include_children' => false,
		];

		if( $active_brand === '-1' ){
			$query_data = [
				'taxonomy'         => $this->brand_attribute(),
				'operator'         => 'NOT EXISTS',
			];
		}

		$query->tax_query->queries[] = $query_data;

		foreach ( $query->tax_query->queries as $q ) {
			$tax_query[] = $q;
		}

		$query->set('tax_query', $tax_query);

	}

	function admin__bulk_edit_add_brands_field() {
		?>
		<div class="inline-edit-group">
		  <label class="alignleft">
			 <span class="title"><?php _e( 'Brand', 'rey-core' ); ?></span>
			 <span class="input-text-wrap">
				<?php
					$this->admin__add_filter_list('product', [
						'name'             => 'rey_brand_term_bulk',
						'by'               => 'term_id',
						'check_active'     => false,
						'hide_empty'       => false,
						'unbranded_option' => false,
					]);
				?>
			 </span>
			</label>
		</div>
		<?php
	}

	function admin__bulk_save( $product ){

		if( ! (isset($_REQUEST['rey_brand_term_bulk']) && $brand = absint($_REQUEST['rey_brand_term_bulk'])) ){
			return;
		}

		$brand_tax_name = $this->brand_attribute();
		$product_id = $product->get_id();

		$meta_attributes = get_post_meta( $product->get_id(), '_product_attributes', true );

		/**
		 * WC_Product_Variable_Data_Store_CPT
		 * read_attributes
		 */
		if ( ! empty( $meta_attributes ) && is_array( $meta_attributes ) ) {

			$attributes   = array();
			$force_update = false;
			$has_brand = false;

			foreach ( $meta_attributes as $meta_attribute_key => $meta_attribute_value ) {
				$meta_value = array_merge(
					array(
						'name'         => '',
						'value'        => '',
						'position'     => 0,
						'is_visible'   => 0,
						'is_variation' => 0,
						'is_taxonomy'  => 0,
					),
					(array) $meta_attribute_value
				);

				// Check if is a taxonomy attribute.
				if ( ! empty( $meta_value['is_taxonomy'] ) ) {
					if ( ! taxonomy_exists( $meta_value['name'] ) ) {
						continue;
					}
					$id      = wc_attribute_taxonomy_id_by_name( $meta_value['name'] );
					$options = wc_get_object_terms( $product->get_id(), $meta_value['name'], 'term_id' );
				} else {
					$id      = 0;
					$options = wc_get_text_attributes( $meta_value['value'] );
				}

				// tell only to modify it
				if( $meta_value['name'] === $brand_tax_name ){
					$options = array_map('absint', wc_get_text_attributes( $brand ));
					$has_brand = true;
					$force_update = true;
				}

				$attribute = new WC_Product_Attribute();
				$attribute->set_id( $id );
				$attribute->set_name( $meta_value['name'] );
				$attribute->set_options( $options );
				$attribute->set_position( $meta_value['position'] );
				$attribute->set_visible( $meta_value['is_visible'] );
				$attribute->set_variation( $meta_value['is_variation'] );
				$attributes[] = $attribute;
			}

			// doesn't have brand, add it
			if( ! $has_brand ){
				$b_attribute = new WC_Product_Attribute();
				$b_attribute->set_id( wc_attribute_taxonomy_id_by_name( $brand_tax_name ) );
				$b_attribute->set_name( $brand_tax_name );
				$b_attribute->set_options( array_map('absint', wc_get_text_attributes( $brand )) );
				$b_attribute->set_position( count($attributes) + 1 );
				$b_attribute->set_visible( true );
				$b_attribute->set_variation( false );
				$attributes[] = $b_attribute;
				$force_update = true;
			}

			$product->set_attributes( $attributes );

			if ( $force_update ) {
				$data_store   = WC_Data_Store::load( 'product' );
				$data_store->update( $product );
			}
		}
	}

	function add_brand_cover_option( $args ){

		if( ! $this->brand_tax_has_archive() ){
			return;
		}

		if( ! function_exists('reycore_customizer__title') ){
			return;
		}

		// Shop Brands
		reycore_customizer__title([
			'title'       => esc_html__('Brands', 'rey-core'),
			'description' => esc_html__('Select a page cover to display in product brands. You can always disable or change the Page Cover of a specific brand, in its options.', 'rey-core'),
			'section'     => $args['section'],
			'size'        => 'md',
			'border'      => 'none',
			'upper'       => true,
		]);

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'select',
			'settings'    => 'cover__shop_brands',
			'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
			'section'     => $args['section'],
			'default'     => '',
			'choices'     => class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections('cover', [
				'no'  => esc_attr__( 'Disabled', 'rey-core' ),
				'' => esc_html__('- Inherit -', 'rey-core')
			]) : [],
		] );
	}

	function brand_cover( $cover ){

		if( is_tax( $this->brand_attribute() ) && ($cover_brands = get_theme_mod('cover__shop_brands', '')) ) {
			$cover = $cover_brands;
		}

		return $cover;
	}

	function add_brand_options_acf(){

		if( function_exists('acf_add_local_field_group') ):

			acf_add_local_field_group(array(
				'key' => 'group_5fcba3fc1d798',
				'title' => 'Brand options',
				'fields' => array(
					array(
						'key' => 'field_5fcba41e2d985',
						'label' => 'Brand Image',
						'name' => 'rey_brand_image',
						'type' => 'image',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'id',
						'preview_size' => 'medium',
						'library' => 'all',
						'min_width' => '',
						'min_height' => '',
						'min_size' => '',
						'max_width' => '',
						'max_height' => '',
						'max_size' => '',
						'mime_types' => '',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'taxonomy',
							'operator' => '==',
							'value' => $this->brand_attribute(),
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));

		endif;
	}

	function elementor_add_brand_settings( $element ){

		$brand_attribute = $this->brand_attribute_excl_pa();

		$element->add_control(
			'brand_options_title',
			[
			   'label' => esc_html__( 'BRANDS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
				],
			]
		);

		$element->add_control(
			'show_brand_images',
			[
				'label' => esc_html__( 'Show Brand Images', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
				],
			]
		);

		$element->add_responsive_control(
			'brand_images_size',
			[
				'label' => esc_html__( 'Image size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
					'show_brand_images!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .__img-link img' => 'max-width: {{VALUE}}px;',
				],
			]
		);

		$element->add_control(
			'hide_brand_link',
			[
				'label' => esc_html__( 'Text link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'rey-core' ),
				'label_off' => esc_html__( 'Hide', 'rey-core' ),
				'return_value' => 'none',
				'default' => '',
				'condition' => [
					'attr_id' => $brand_attribute,
					'display' => 'list',
					'show_brand_images!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .__img-link .__text:not(:only-child)' => 'display: {{VALUE}};',
				],
			]
		);

	}

	function elementor_output_add_image($html, $settings, $attr, $link){

		if( $settings['display'] !== 'list' ){
			return $html;
		}

		if( $settings['attr_id'] !== $this->brand_attribute_excl_pa() ){
			return $html;
		}

		if( $settings['show_brand_images'] !== 'yes' ){
			return $html;
		}

		$link_content = '';

		if( $image_id = reycore__acf_get_field( 'rey_brand_image', $attr->taxonomy . '_' . $attr->term_id) ){
			$image_size = get_post_mime_type($image_id) === 'image/svg+xml' ? 'full' : 'thumbnail';
			$link_content .= wp_get_attachment_image($image_id, $image_size);
		}

		$link_content .= sprintf('<span class="__text">%s</span>', esc_html( $attr->name ));

		return sprintf( '<a href="%s" class="__img-link">%s</a>', esc_url($link), $link_content );
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore_WooCommerce_Brands
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}
endif;

ReyCore_WooCommerce_Brands::getInstance();
