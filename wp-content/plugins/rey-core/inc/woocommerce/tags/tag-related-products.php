<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_RelatedProducts') ):

class ReyCore_WooCommerce_RelatedProducts
{

	const META_KEY = '_rey_related_ids';

	protected $product_ids = [];

	protected $to_check = [];

	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	function init(){

		add_action( 'wp', [$this, 'late_init']);
		add_action( 'woocommerce_product_options_related', [$this, 'add_extra_product_edit_options'] );
		add_action( 'woocommerce_update_product', [$this, 'process_extra_product_edit_options'] );
		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'before_grid']);
		add_action( 'reycore/woocommerce/loop/after_grid', [$this, 'after_grid']);
		add_filter( 'woocommerce_related_products', [$this, 'filter_related_products'], 10, 3 );
		add_filter( 'woocommerce_product_related_products_heading', [$this, 'change_title']);
		add_filter( 'woocommerce_output_related_products_args', [$this, 'filter_related_products_args'], 20 );
		add_filter( 'woocommerce_upsell_display_args', [$this, 'filter_upsells_products_args'], 20 );

		$this->to_check[] = 'related';

		if( $this->is_upsells_enabled() ){
			$this->to_check[] = 'up-sells';
		}

	}

	public function late_init(){

		if( ! reycore_wc__is_product() ){
			return;
		}

		if( ! $this->is_enabled() ){
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}

	}

	function before_grid(){

		if( ! ( ($q_name = wc_get_loop_prop( 'name' )) && in_array($q_name, $this->to_check, true) ) ){
			return;
		}

		if( $this->supports_carousel() ){

			reyCoreAssets()->add_scripts(['rey-splide', 'reycore-wc-product-page-related-prod-carousel']);
			reyCoreAssets()->add_styles('rey-splide');
		}

		add_filter( 'post_class', [$this,'add_product_classes'], 30 );
		add_filter( 'reycore/woocommerce/product_loop_classes', [$this, 'add_grid_classes']);
		add_filter( 'reycore/woocommerce/product_loop_attributes', [$this, 'add_grid_attributes'], 10, 2);
		add_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false', 10);
		add_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false', 10);
		add_filter('reycore/woocommerce/loop/prevent_2nd_image', [$this, 'prevent_extra_media_edit_mode']);
		add_filter('reycore/woocommerce/loop/prevent_product_items_slideshow', [$this, 'prevent_extra_media_edit_mode']);

	}

	function after_grid(){

		if( ! (($q_name = wc_get_loop_prop( 'name' )) && in_array($q_name, $this->to_check, true) ) ){
			return;
		}

		remove_filter( 'post_class', [$this,'add_product_classes'], 30 );
		remove_filter( 'reycore/woocommerce/product_loop_classes', [$this, 'add_grid_classes']);
		remove_filter( 'reycore/woocommerce/product_loop_attributes', [$this, 'add_grid_attributes'], 10, 2);
		remove_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false', 10);
		remove_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false', 10);
		remove_filter('reycore/woocommerce/loop/prevent_2nd_image', [$this, 'prevent_extra_media_edit_mode']);
		remove_filter('reycore/woocommerce/loop/prevent_product_items_slideshow', [$this, 'prevent_extra_media_edit_mode']);
	}

	public function change_title( $title ){
		if( $this->is_enabled() && ($related_title = get_theme_mod('single_product_page_related_title', '')) ){
			return $related_title;
		}
		return $title;
	}

	public function add_grid_attributes( $attributes, $settings ){

		if( isset($settings['_skin']) && $settings === 'carousel' ){
			return $attributes;
		}

		$params = [
			'enabled' => $this->supports_carousel(),
			'upsells' => $this->is_upsells_enabled(),
			'autoplay' => true,
			'interval' => 6000,
			'per_page' => reycore_wc_get_columns('desktop'),
			'per_page_tablet' => 3,
			'per_page_mobile' => 2,
			'gap' => 30,
			'gap_mobile' => 15,
		];

		if( $desktop_cols = get_theme_mod('single_product_page_related_columns', '') ){
			$params['per_page' ] = $desktop_cols;
		}

		foreach(['tablet', 'mobile'] as $device){
			if( $cols = get_theme_mod('single_product_page_related_columns_' . $device, '') ){
				$params['per_page_' . $device ] = $cols;
			}
		}

		return $attributes . sprintf(' data-grid-config=\'%s\' ', wp_json_encode($params));

	}

	public function prevent_extra_media_edit_mode($mod){

		if( ! (class_exists('Elementor\Plugin') && is_callable( 'Elementor\Plugin::instance' )) ){
			return $mod;
		}

		if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) {
			return true;
		}

		return $mod;
	}

	function supports_carousel(){
		return get_theme_mod('single_product_page_related_carousel', false);
	}

	function add_extra_product_edit_options(){

		if( ! $this->custom_enabled() ){
			return;
		}

		?>
		<div class="options_group">

			<p class="form-field hide_if_grouped hide_if_external">
				<label for="rey_related_products"><?php esc_html_e( 'Related products', 'rey-core' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="rey_related_products" name="<?php echo self::META_KEY ?>[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'rey-core' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( get_the_ID() ); ?>">
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
				</select> <?php echo wc_help_tip( __( 'Select custom related products.', 'rey-core' ) ); // WPCS: XSS ok. ?>
			</p>

		</div>

		<?php
	}

	public function process_extra_product_edit_options( $product_id ) {

		if ( $this->custom_enabled() ) {

			$save = [];

			if( isset($_POST[ self::META_KEY ]) ){
				$save = wc_clean( $_POST[ self::META_KEY ] );
			}

			update_post_meta( $product_id, self::META_KEY, $save );
		}
	}

	public function filter_related_products($related_posts, $product_id, $args) {

		if( ! $this->custom_enabled() ){
			return $related_posts;
		}

		$hide_if_empty = apply_filters('reycore/woocommerce/related_products/custom_hide_empty', false);

		if ( $custom_related = $this->get_products_ids($product_id)) {

			if( get_theme_mod('single_product_page_related_custom_replace', true) ){
				$related_posts = $custom_related;
			}

			else {

				if( ! $hide_if_empty ){
					$related_posts = array_unique( $custom_related + $related_posts );
					add_filter('woocommerce_product_related_posts_shuffle', '__return_false');
				}
				else {
					$related_posts = $custom_related;
				}

			}

		}
		else if( $hide_if_empty ) {

			return [];

		}

		return $related_posts;
	}

	public function filter_products_args($args, $type = 'related') {

		if( $cols = get_theme_mod('single_product_page_related_columns', '') ){
			$args['columns'] = $cols;
		}

		if( $per_page = get_theme_mod('single_product_page_related_per_page', '') ){
			$args['posts_per_page'] = $per_page;
		}

		if( $this->supports_carousel() ){

			if( absint($args['posts_per_page']) < 8 ){
				$args['posts_per_page'] = 8;
			}

		}

		if ( $type === 'related' && $this->custom_enabled() && get_theme_mod('single_product_page_related_custom_replace', true) ) {
			$args['orderby'] = 'ID';
		}

		return $args;
	}

	public function filter_related_products_args($args) {
		return $this->filter_products_args($args, 'related');
	}

	public function filter_upsells_products_args($args) {

		if( ! $this->is_upsells_enabled() ){
			return $args;
		}

		return $this->filter_products_args($args, 'upsells');
	}

	function add_product_classes($classes){

		if( $this->supports_carousel() ){
			unset($classes['animated-entry']);
		}

		return $classes;
	}

	function add_grid_classes($classes){

		foreach(['tablet', 'mobile'] as $device){
			if( $cols = get_theme_mod('single_product_page_related_columns_' . $device, '') ){
				$classes['columns_' . $device ] = 'columns-' . $device . '-' . $cols;
			}
		}

		if( ! $this->supports_carousel() ){
			return $classes;
		}

		$custom_classes = [
			'--prevent-metro',
			'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
			'--prevent-scattered', // make sure scattered is not applied
			'--prevent-masonry', // make sure masonry is not applied
		];

		return $classes + $custom_classes;
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

	public function custom_enabled(){
		return $this->is_enabled() && get_theme_mod('single_product_page_related_custom', false);
	}

	public function is_enabled(){
		return get_theme_mod('single_product_page_related', true);
	}

	public function is_upsells_enabled(){
		return get_theme_mod('single_product_page_related_upsells', true);
	}

}

new ReyCore_WooCommerce_RelatedProducts;

endif;
