<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_RecommendedProducts') ):

class ReyCore_Wc_RecommendedProducts
{
	private $settings = [];

	protected $product_ids = [];

	const META_KEY = '_rey_recommended_ids';

	const ASSET_HANDLE = 'reycore-recommended-products';

	public function __construct()
	{
		// Work in progress
		return;

		add_action( 'reycore/kirki_fields/after_field=single_product_page_related_custom_replace', [ $this, 'add_customizer_options' ] );
		add_action('init', [$this, 'init']);
		add_action('wp', [$this, 'wp']);
	}

	function is_enabled(){
		return get_theme_mod('single_pdp__recommended', false);
	}

	function init()
	{
		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'woocommerce_product_options_related', [$this, 'add_extra_product_edit_options'] );
		add_action( 'woocommerce_update_product', [$this, 'process_extra_product_edit_options'] );
	}

	function wp(){

		if( ! $this->is_enabled() ){
			return;
		}

		$this->products_ids = $this->get_products_ids();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'woocommerce_single_product_summary', [$this, 'output'], 50);
	}

	function enqueue_scripts(){

		if( ! is_product() ){
			return;
		}

		reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
		reyCoreAssets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_assets(){

		reyCoreAssets()->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		reyCoreAssets()->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js',
				'deps'    => ['rey-script', 'reycore-scripts', 'reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	function output(){

		$classes = [];

		// make sure to
		if( get_theme_mod('single_pdp__recommended_width', '') && get_theme_mod('single_pdp__recommended_height', '') ){
			$classes[] = '--cover';
		}

		reycore__get_template_part('template-parts/woocommerce/single-recommended-products', false, false, [
			'ids' => $this->products_ids,
			'quickview' => true,
			'class' => implode(' ', $classes)
		]);

	}

	function add_extra_product_edit_options(){

		if( ! $this->is_enabled() ){
			return;
		}

		?>
		<div class="options_group">

			<p class="form-field hide_if_grouped hide_if_external">
				<label for="rey_recommended_products"><?php esc_html_e( 'Recommended products (Summary)', 'rey-core' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="rey_recommended_products" name="<?php echo self::META_KEY ?>[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'rey-core' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( get_the_ID() ); ?>">
					<?php

					$product_ids = $this->get_products_ids();

					if( is_array($product_ids) ):
						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
							}
						}
					endif;
					?>
				</select> <?php echo wc_help_tip( __( 'Select custom recommended products.', 'rey-core' ) ); // WPCS: XSS ok. ?>
			</p>

		</div>

		<?php
	}

	function process_extra_product_edit_options( $product_id ) {

		if ( ! $this->is_enabled() ) {
			return;
		}

		$save = [];

		if( isset($_POST[ self::META_KEY ]) ){
			$save = wc_clean( $_POST[ self::META_KEY ] );
		}

		update_post_meta( $product_id, self::META_KEY, $save );

	}

	public function get_products_ids( $product_id = '' ){

		if( !empty( $this->product_ids ) ){
			return $this->product_ids;
		}

		if( empty($product_id) ){
			$product_id = get_the_ID();
		}

		return get_post_meta($product_id, self::META_KEY, true);
	}

	function add_customizer_options($field_args){

		reycore_customizer__title([
			'title'       => esc_html__('Summary Recommended Products', 'rey-core'),
			'section'     => $field_args['section'],
			'size'        => 'sm',
			'border'      => 'top',
			'upper'       => true,
			'style_attr'  => '--border-size: 3px;',
		]);


		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'toggle',
			'settings'    => 'single_pdp__recommended',
			'label'       => esc_html__( 'Enable Recommended products', 'rey-core' ),
			'section'     => $field_args['section'],
			'default'     => false,
		] );


		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'     => 'text',
			'settings' => 'single_pdp__recommended_title',
			'label'    => esc_html__('Title', 'rey-core'),
			'section'  => $field_args['section'],
			'default'  => '',
			'active_callback' => [
				[
					'setting'  => 'single_pdp__recommended',
					'operator' => '==',
					'value'    => true,
				],
			],
			'input_attrs' => [
				'placeholder' => esc_html__('eg: Recommended products', 'rey-core')
			]
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'rey-number',
			'settings'    => 'single_pdp__recommended_width',
			'label'       => esc_html__('Thumb Width', 'rey-core') . ' (px)',
			'section'     => $field_args['section'],
			'default'     => '',
			'choices'     => [
				'min'  => 50,
				'max'  => 250,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_pdp__recommended',
					'operator' => '==',
					'value'    => true,
				],
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--pdp-recommended-img-width',
					'units'    		=> 'px',
				],
			],
		] );

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'rey-number',
			'settings'    => 'single_pdp__recommended_height',
			'label'       => esc_html__('Thumb height', 'rey-core') . ' (px)',
			'section'     => $field_args['section'],
			'default'     => '',
			'choices'     => [
				// 'min'  => 50,
				'max'  => 250,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_pdp__recommended',
					'operator' => '==',
					'value'    => true,
				],
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--pdp-recommended-img-height',
					'units'    		=> 'px',
				],
			],
		] );
	}

}

new ReyCore_Wc_RecommendedProducts;

endif;
