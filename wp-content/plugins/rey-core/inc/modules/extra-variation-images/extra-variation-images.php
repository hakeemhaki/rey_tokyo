<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_ExtraVariationImages') ):

class ReyCore_Wc_ExtraVariationImages
{
	private $settings = [];

	public function __construct()
	{
		add_filter('wvs_install_woo_variation_gallery_notice', '__return_false');

		if( ! apply_filters('reycore/module/extra-variation-images/enable', true) ){
			return;
		}

		// prevent module from loading if Additional Variation Images Gallery for WooCommerce is enabled
		if( class_exists('Woo_Variation_Gallery') || class_exists('WC_Additional_Variation_Images') ){
			add_filter('reycore/woocommerce/allow_mobile_gallery', '__return_false');
			add_action( 'admin_notices', [$this, 'show_notices'] );
			add_action( 'admin_init', [$this, 'dismiss_nag'] );
			return;
		}

		$this->load_files();

		add_action( 'reycore/kirki_fields/after_field=product_page_gallery_arrow_nav', [ $this, 'add_customizer_options' ] );
		add_action( 'init', [$this, 'init'] );
		add_action( 'wp_ajax_rey_extra_variation_enable', [ $this, 'extra_variation_enable' ] );
		add_action( 'wp_ajax_rey_get_extra_variation_images_admin', [ $this, 'get_extra_variation_images_admin' ] );
		add_action( 'wp_ajax_rey_frontend_get_extra_variation_images', [ $this, 'get_extra_variation_images_frontend' ] );
		add_action( 'wp_ajax_nopriv_rey_frontend_get_extra_variation_images', [ $this, 'get_extra_variation_images_frontend' ] );
		add_filter( 'stop_gwp_live_feed', '__return_true' );
		add_filter( 'rey/main_script_params', [$this, 'add_script_params'], 10 );
		add_action( 'woocommerce_before_single_product_summary', [$this, 'before_single_product_summary'], 0);
		add_action( 'woocommerce_after_single_product_summary', [$this, 'after_single_product_summary'], 0);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_elementor_add_to_cart_widget'], 10);
		add_action( 'elementor/frontend/widget/after_render', [$this, 'after_elementor_add_to_cart_widget'], 10);
		add_action( 'elementor/theme/before_do_single', [$this, 'before_single_product_summary']);
		add_action( 'elementor/theme/after_do_single', [$this, 'after_single_product_summary']);
		// add_action( 'reycore/templates/tpl/before_render', [$this, 'before_single_product_summary']);
		// add_action( 'reycore/templates/tpl/after_render', [$this, 'after_single_product_summary']);
	}

	function load_files(){
		require_once REY_CORE_MODULE_DIR . basename(__DIR__) . '/extra-variation-images-impex.php';
	}

	public function init()
	{
		$this->set_settings();

		if( $this->is_enabled() && (! is_admin() || wp_doing_ajax()) && ! $this->settings['surpress_filters'] ){
			// add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_filter( 'woocommerce_available_variation', [$this, 'filter_available_variations'], 10, 3);
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'admin_footer', [ $this, 'templates_admin' ] );
		add_action( 'save_post', [$this, 'save_variation_images'], 1, 2 );
		add_action( 'woocommerce_save_product_variation', [$this, 'save_product_variation'], 10, 2 );

		$impex = new ReyCore_Wc_ExtraVariationImages_ImportExport( $this->settings['key'] );
	}

	public function is_enabled(){
		return get_theme_mod('enable_extra_variation_images', false);
	}

	public function show_notices(){

		// No need for a nag if current user can't install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// No need for a nag if user has dismissed it.
		$dismissed = get_user_meta( get_current_user_id(), 'reycore_extra_images_nag_dismissed', true );
		if ( true === $dismissed || 1 === $dismissed || '1' === $dismissed ) {
			return;
		}

		$plugin = '';
		$plugins = [
			'WC_Additional_Variation_Images' => 'WooCommerce Additional Variation Images',
			'Woo_Variation_Gallery' => 'Additional Variation Images Gallery for WooCommerce',
		];

		foreach ($plugins as $key => $value) {
			if( class_exists($key) ){
				$plugin = $value;
			}
		}

		if( empty($plugin) ){
			return;
		}
		?>

		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
					printf(
						__('%1$s: Psst! I see you\'re using <em>%2$s</em> plugin. Did you know %1$s has the same functionality built-in and better optimised? Please read <a href="%3$s" target="_blank">this article</a> which explains how to use it.', 'rey-core'),
						ucwords(REY_CORE_THEME_NAME),
						$plugin,
						'https://support.reytheme.com/kb/extra-variation-images-internal-module/'
					);
				?>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( '?dismiss-nag=reycore-evi-nag' ), 'reycore-evi-nag-nonce', 'nonce' ) ); ?>"><?php esc_html_e( 'Don\'t show this again', 'rey-core' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Dismisses the nag.
	 */
	public function dismiss_nag() {
		if ( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'reycore-evi-nag-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( get_current_user_id() && isset( $_GET['dismiss-nag'] ) && 'reycore-evi-nag' === $_GET['dismiss-nag'] ) {
				update_user_meta( get_current_user_id(), 'reycore_extra_images_nag_dismissed', true );
			}
		}
	}

	public function add_script_params($params) {
		$params['module_extra_variation_images'] = $this->is_enabled();
		return $params;
	}

	/**
	 * Append customizer options
	 *
	 * @since 1.5.0
	 */
	public function add_customizer_options( $field_args ){

		$field = [
			'type'        => 'toggle',
			'settings'    => 'enable_extra_variation_images',
			'label'       => reycore_customizer__title_tooltip(
				esc_html__( 'Enable extra variation images?', 'rey-core' ),
				esc_html__( 'If enabled, the extra variation images module will be loaded.', 'rey-core' )
			),
			'section'     => $field_args['section'],
			'default'     => false,
		];

		if( isset($field_args['priority']) ){
			$field['priority'] = $field_args['priority'];
		}

		ReyCoreKirki::add_field( 'rey_core_kirki', $field );
	}

	/**
	 * Set settings
	 *
	 * @since 1.5.0
	 */
	private function set_settings(){
		$this->settings = apply_filters('reycore/module/extra_variation_images', [
			'key' => 'rey_extra_variation_images',
			'surpress_filters' => false
		]);

		/**
		 * To make compatible with WC Additional Variation Images plugin,
		 * use this filter in the child theme's functions.php
		 */

		/*
			add_filter('reycore/module/extra_variation_images', function($settings){
				$settings['key'] = '_wc_additional_variation_images';
				return $settings;
			});
		*/
	}

	/**
	 * Get variation image ids
	 *
	 * @since 1.5.0
	 */
	public function get_variation_images( $variation_id = 0 ) {
		return get_post_meta( $variation_id, $this->settings['key'], true );
	}

	/**
	 * Enqueue frontend scripts
	 *
	 * @since 1.5.0
	 */
	public function enqueue_scripts(){
		wp_enqueue_style( 'reycore-module-evi', REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
	}


	public function get_extra_variation_images_admin() {

		if ( ! check_ajax_referer( '_rey_evi_nonce', 'security', false ) ) {
			wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
		}

		if ( ! ( isset( $_POST['variation_ids'] ) && $variation_ids = array_map( 'absint', $_POST['variation_ids'] ) ) ) {
			wp_send_json_error( esc_html__('No variation ids.', 'rey-core') );
		}

		$variation_images = [];

		foreach( $variation_ids as $variation_id ) {

			$ids = $this->get_variation_images( $variation_id );

			$variation_images[ $variation_id ] = [];

			if( !empty($ids) ){
				foreach( explode( ',', $ids ) as $attach_id ) {
					$img = wp_get_attachment_image_src( $attach_id, [50, 50] );
					$variation_images[ $variation_id ][] = [
						'id' => $attach_id,
						'src' => $img[0]
					];
				}
			}
		}

		wp_send_json_success( $variation_images );
	}

	public function extra_variation_enable() {

		if ( ! check_ajax_referer( '_rey_evi_nonce', 'security', false ) ) {
			wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
		}

		wp_send_json_success( set_theme_mod('enable_extra_variation_images', true) );

	}

	/**
	 * Adds extra images params, based on wether
	 * variation has one or multiple images
	 *
	 * @since 1.5.0
	 */
	public function filter_available_variations( $available_attr, $product, $variation ){

		$filter_suppressed = apply_filters('reycore/module/extra-variation-images/maybe_surpress_filter', true);

		// force run on ajax calls
		if(
			isset($_REQUEST['wc-ajax']) &&
			( $_REQUEST['wc-ajax'] === 'get_variation' || $_REQUEST['wc-ajax'] === 'get_variations' )
		){
			$filter_suppressed = false;
		}

		// bail, if filters suppressed
		if( $filter_suppressed ){
			return $available_attr;
		}

		$extra_images = $this->get_variation_images( $variation->get_id() );

		if( $extra_images ){
			$available_attr['extra_images'] = $extra_images;
		}
		else {

			// create a gallery for that single image
			ob_start();
			$this->woocommerce_show_product_images([ $available_attr['image_id'] ]);
			$available_attr['gallery_html'] = ob_get_clean();

			// create a gallery for that single image
			ob_start();
			$this->woocommerce_show_product_images_mobile([ $available_attr['image_id'] ], $product->get_id());
			$available_attr['gallery_html_mobile'] = ob_get_clean();

		}

		$available_attr['parent_product_image_id'] = $product->get_image_id();

		// empty image property
		$available_attr['image'] = [];

		return $available_attr;
	}

	/**
	 * Ajax, get variation gallery html
	 *
	 * @since 1.5.0
	 */
	public function get_extra_variation_images_frontend() {

		if( apply_filters('reycore/woocommerce/extra_images/check_nonce', true) ){
			if ( ! check_ajax_referer( 'rey_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}
		}

		if ( ! ( isset( $_REQUEST['image_ids'] ) && $image_ids = array_map( 'absint', $_REQUEST['image_ids'] ) ) ) {
			wp_send_json_error( esc_html__('No image ids.', 'rey-core') );
		}

		$type = isset($_REQUEST['type']) ? wc_clean($_REQUEST['type']) : 'desktop';
		$product_id = isset($_REQUEST['product_id']) ? absint($_REQUEST['product_id']) : 0;

		$this->fix_gallery_type();

		ob_start();

			if( 'desktop' === $type ){
				$this->woocommerce_show_product_images($image_ids);
			}
			else {
				$this->woocommerce_show_product_images_mobile( $image_ids, $product_id );
			}

		$image_html = ob_get_clean();

		wp_send_json_success( $image_html );
	}

	protected function fix_gallery_type(){
		// Fix gallery type
		add_filter('theme_mod_product_gallery_layout', function($mod){

			if( isset($_REQUEST['gallery_type']) && $gallery_type = reycore__clean($_REQUEST['gallery_type']) ){
				if( class_exists('ReyCore_WooCommerce_ProductGallery_Base') && ($gallery_types = ReyCore_WooCommerce_ProductGallery_Base::get_gallery_types()) && isset($gallery_types[$gallery_type]) ){
					return $gallery_type;
				}
			}

			return $mod;
		});
	}

	/**
	 * Create HTML gallery, based on incoming
	 * image ids.
	 *
	 * @since 1.5.0
	 */
	public function woocommerce_show_product_images($image_ids = []){

		if( ! class_exists('ReyCore_WooCommerce_ProductGallery_Base') ){
			return;
		}

		$gallery_base = ReyCore_WooCommerce_ProductGallery_Base::getInstance();
		$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
		$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', [
			'woocommerce-product-gallery',
			'woocommerce-product-gallery--with-images',
			'woocommerce-product-gallery--columns-' . absint( $columns ),
			'images',
		]); ?>

		<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" data-params='<?php echo wp_json_encode( apply_filters('reycore/woocommerce/product_image/params', []) ); ?>'>
			<figure class="woocommerce-product-gallery__wrapper">
				<?php

				$main_image = apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $image_ids[0], true ), $image_ids[0] );

				// Main image
				if( count($image_ids) === 1) {
					echo $main_image;
					return;
				}
				// Main image + extra
				elseif( $gallery_base->is_gallery_with_thumbs() && count($image_ids) > 0 ) {
					echo $main_image;
				}

				$gallery_base->thumbs_markup__start();

				foreach($image_ids as $i => $image_id){
					if( ! wp_attachment_is_image($image_id) ){
						continue;
					}
					echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $image_id, ! $gallery_base->is_gallery_with_thumbs() ), $image_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
				}

				$gallery_base->thumbs_markup__end();
				?>
			</figure>
		</div>
		<?php
	}

	function before_single_product_summary(){
		add_filter('reycore/module/extra-variation-images/maybe_surpress_filter', '__return_false');
	}

	function after_single_product_summary(){
		remove_filter('reycore/module/extra-variation-images/maybe_surpress_filter', '__return_false');
	}

	function before_elementor_add_to_cart_widget($element){

		if( ! in_array($element->get_unique_name(), ['woocommerce-product-add-to-cart', 'reycore-woo-pdp-add-to-cart'], true) ){
			return;
		}

		add_filter('reycore/module/extra-variation-images/maybe_surpress_filter', '__return_false');
	}

	function after_elementor_add_to_cart_widget($element){

		if( ! in_array($element->get_unique_name(), ['woocommerce-product-add-to-cart', 'reycore-woo-pdp-add-to-cart'], true) ){
			return;
		}

		remove_filter('reycore/module/extra-variation-images/maybe_surpress_filter', '__return_false');
	}


	/**
	 * Create mobile HTML gallery, based on incoming
	 * image ids.
	 *
	 * @since 1.5.0
	 */
	public function woocommerce_show_product_images_mobile($image_ids, $product_id){
		if( ! $image_ids ){
			$image_ids = [];
		}
		if( class_exists('ReyCore_WooCommerce_ProductGallery_Base') ){
			ReyCore_WooCommerce_ProductGallery_Base::getInstance()->mobile_gallery($image_ids, $product_id);
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.5.0
	 */
	public function admin_enqueue_scripts(){

		if ( 'product' !== get_post_type() ) {
			return;
		}

		wp_enqueue_style( 'reycore-module-evi-admin', REY_CORE_MODULE_URI . basename(__DIR__) . '/admin-style.css', [], REY_CORE_VERSION );
		wp_enqueue_script( 'reycore-module-evi-admin', REY_CORE_MODULE_URI . basename(__DIR__) . '/admin-script.js', ['wp-util','jquery'], REY_CORE_VERSION , true);

		wp_localize_script( 'reycore-module-evi-admin', 'reyEviParams', [
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'        => wp_create_nonce( '_rey_evi_nonce' ),
			'media_button_text' => __( 'Add to Variation', 'rey-core' ),
			'media_title'       => __( 'Variation Images', 'rey-core' ),
		] );
	}

	/**
	 * Add extra image variation template
	 *
	 * @since 1.5.0
	 */
	public function templates_admin(){

		if ( 'product' !== get_post_type() ) {
			return;
		} ?>

		<script type="text/html" id="tmpl-rey-extra-images-admin">
			<# var variation_images_ids = [];  #>
			<div class="rey-extraVariationsImages" data-variation-id="{{{data.variation_id}}}">

				<?php $this->show_enable_notice(); ?>

				<ul class="rey-extraVariationsImages-list">
					<# Object.keys(data.variation_images).forEach(function(item) { #>
						<li>
							<a href="#" data-id="{{{data.variation_images[item].id}}}" title="<?php esc_html_e('Click to remove image', 'rey-core') ?>">
								<img src="{{{data.variation_images[item].src}}}" />
							</a>
						</li>
						<# variation_images_ids.push(data.variation_images[item].id); #>
					<# }); #>
				</ul>

				<a href="#" class="button rey-extraVariationsImages-btn"><?php esc_html_e('Add Extra Images', 'rey-core') ?></a>

				<# var ids_string = variation_images_ids.join(','); #>
				<input type="hidden" class="js-rey-extraVariationsImages-save" name="rey_extra_variation_images_thumbnails[{{{data.variation_id}}}]" value="{{{ids_string}}}">
			</div>
		</script>
		<?php
	}

	function show_enable_notice(){
		if( !$this->is_enabled() ){
			printf(
				'<p class="rey-extraVariationsImages-notice rey-adminNotice --error">If you want to display <strong>extra variation images</strong> in frontend, please enable Extra Variation Images module in <a href="%s" target="_blank">Customizer > WooCommerce > Product Page - Layout > Gallery panel</a>, or <a href="#" class="js-enable-extraimages">click here</a> to enable and reload the page.</p>',
				add_query_arg(['autofocus[control]' => 'enable_extra_variation_images'], admin_url( 'customize.php' ))
			);
		}
	}

	/**
	 * Save variation images
	 *
	 * @since 1.5.0
	 */
	public function save_variation_images( $post_id, $post ) {

		$post_id = absint( $post_id );

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check the post type
		if ( $post->post_type !== 'product' )  {
			return;
		}

		if( ! (isset($_POST['rey_extra_variation_images_thumbnails']) && $variation_ids = $_POST['rey_extra_variation_images_thumbnails']) ){
			return;
		}

		array_walk_recursive( $variation_ids, 'sanitize_text_field' );

		foreach( $variation_ids as $variation_id => $attachment_ids ) {
			update_post_meta( $variation_id, $this->settings['key'], $attachment_ids );
		}
	}

	/**
	 * Save product variation
	 *
	 * @since 1.5.0
	 */
	public function save_product_variation( $variation_id, $i ) {

		if( ! (isset($_POST['rey_extra_variation_images_thumbnails']) && $variation_ids = $_POST['rey_extra_variation_images_thumbnails']) ){
			return;
		}

		$variation_ids = sanitize_text_field( $variation_ids[ $variation_id ] );
		update_post_meta( $variation_id, $this->settings['key'], $variation_ids );
	}

}

new ReyCore_Wc_ExtraVariationImages;

endif;
