<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'WC_Brands' ) && !class_exists('ReyCore_Compatibility__WC_Brands') ):

	class ReyCore_Compatibility__WC_Brands
	{
		public $brandsInstance;

		private $settings = [];

		const BRAND_TAX = 'product_brand';

		public function __construct()
		{
			if( isset($GLOBALS['WC_Brands']) ){
				$this->brandsInstance = $GLOBALS['WC_Brands'];
			}

			add_action('init', [$this, 'add_settings']);
			remove_action( 'woocommerce_product_meta_end', [ $this->brandsInstance, 'show_brand' ] );
			add_action( 'woocommerce_single_product_summary', [$this, 'show_brands'], 6 );
			remove_action( 'woocommerce_single_product_summary', [$this, 'show_brands'], PHP_INT_MAX );
			add_action( 'woocommerce_shop_loop_item_title', [$this, 'show_brands'], 4 );
			add_filter( 'reycore/ajaxfilters/registered_taxonomies', [$this, 'register_tax_ajaxfilter']);
			add_filter( 'reycore/ajaxfilters/tax_reset_query', [$this, 'tax_reset']);
			add_filter( 'reycore/elementor/product_grid/attributes', [$this, 'product_grid_attributes_list'], 20);
			add_filter( 'reycore/elementor/product_grid/query_args', [$this, 'product_grid_query_args'], 20, 2);
			add_filter( 'acf/load_field_group', [$this, 'add_singular_settings']);
			add_action( 'reycore/kirki_fields/after_field=cover__shop_page', [ $this, 'add_customizer_options' ] );
			add_filter( 'reycore/cover/get_cover', [$this, 'add_cover'], 40);
		}

		function add_settings(){
			$this->settings = apply_filters('reycore/compatibility/brands/settings', [
				'show_thumbs' => true,
				'show_thumbs_loop' => false,
			] );
		}

		public function show_brands(){
			echo $this->brands_html();
		}

		public function brands_html(){

			if ( !($product = wc_get_product()) ) {
				return;
			}

			$output = '';

			$brands = wp_get_post_terms( $product->get_id(), self::BRAND_TAX );

			foreach( $brands as $brand ) {

				$inner = $brand->name;

				if( is_singular('product') && $this->settings['show_thumbs'] && !in_array( wc_get_loop_prop('name'), ['upsells', 'crosssells', 'related'] ) ){
					$inner = get_brand_thumbnail_image( $brand );

					if( strpos($inner, wc_placeholder_img_src()) !== false ){
						return;
					}
				}

				elseif ( $this->settings['show_thumbs_loop'] ) {
					$inner = get_brand_thumbnail_image( $brand );
				}

				$output .= sprintf('<a href="%1$s" title="%2$s">%3$s</a>',
					esc_url( get_term_link( $brand ) ),
					esc_attr( $brand->name ),
					$inner
				);
			}

			if( empty($output) ){
				return;
			}

			return apply_filters('reycore/compatibility/brands/html', sprintf( '<div class="rey-brandLink --no-fade-links">%s</div>', $output ) );
		}

		function register_tax_ajaxfilter($tax){

  			$tax[] = [
				'id' => self::BRAND_TAX,
				'name' => 'Brand',
			];

			return $tax;
		}

		function tax_reset($items){
			$items[] = self::BRAND_TAX;
			return $items;
		}

		function product_grid_attributes_list($attributes){
			$attributes[self::BRAND_TAX] = esc_html__('Product Brand', 'rey-core');
			return $attributes;
		}

		function product_grid_query_args($query_args){

			if( isset($query_args['tax_query']) ){

				foreach ($query_args['tax_query'] as $key => $value) {

					if( isset($query_args['tax_query'][$key]['taxonomy']) && $query_args['tax_query'][$key]['taxonomy'] === wc_attribute_taxonomy_name( self::BRAND_TAX ) ){
						$query_args['tax_query'][$key]['taxonomy'] = self::BRAND_TAX;
					}
				}

			}

			return $query_args;
		}

		function add_singular_settings($field_group){

			if( function_exists('reycore_acf__is_exporting') && reycore_acf__is_exporting() ){
				return $field_group;
			}

			if( function_exists('reycore_acf__is_editing_group') && reycore_acf__is_editing_group() ){
				return $field_group;
			}

			if( isset($field_group['key']) && $field_group['key'] === 'group_5c4ad0bd35b33' ){

				$field_group['location'][] = [
					[
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'product_brand',
					]
				];
			}

			return $field_group;
		}

		function add_customizer_options( $args ){

			if( ! class_exists('ReyCore_GlobalSections') ){
				return;
			}

			// Shop Page
			reycore_customizer__title([
				'title'       => esc_html__('WooCommerce Brands Page', 'rey-core'),
				'description' => esc_html__('These settings will apply on the WooCommerce Brands pages.', 'rey-core'),
				'section'     => $args['section'],
				'size'        => 'md',
				'border'      => 'top',
				'upper'       => true,
				'priority'    => 10,
			]);

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => 'cover__wc_brands',
				'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => 'no',
				'choices'     => ReyCore_GlobalSections::get_global_sections('cover', [
					'no'  => esc_attr__( 'Disabled', 'rey-core' )
				]),
				'priority'    => 10
			] );

		}

		public function add_cover( $cover ){

			if( ! is_tax(self::BRAND_TAX) ){
				return $cover;
			}

			if( ($acf_page_cover = reycore__acf_get_field('page_cover')) ){

				if( $acf_page_cover === 'no' ){
					return false;
				}

				return $acf_page_cover;
			}

			$cover = get_theme_mod('cover__wc_brands', 'no');

			if( $cover === 'no' ){
				return false;
			}

			return $cover;
		}

	}

	new ReyCore_Compatibility__WC_Brands;
endif;
