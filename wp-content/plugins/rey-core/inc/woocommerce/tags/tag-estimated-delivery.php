<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_EstimatedDelivery') ):

	class ReyCore_WooCommerce_EstimatedDelivery
	{

		const VARIATIONS_KEY = '_rey_estimated_delivery_variation';

		private static $_instance = null;

		public $args = [];

		private function __construct()
		{
			add_action( 'wp', [$this, 'init']);
			add_action( 'reycore/woocommerce/product_ajax', [$this, 'init']);
			add_filter( 'acf/prepare_field/name=estimated_delivery__days', [$this, 'append_suffix_acf_option']);
			add_filter( 'acf/prepare_field/name=estimated_delivery__days_margin', [$this, 'append_suffix_acf_option_margin']);
			add_action( 'woocommerce_product_after_variable_attributes', [$this, 'variation_settings_fields'], 10, 3 );
			add_action( 'woocommerce_save_product_variation', [$this, 'save_variation_settings_fields'], 10, 2 );
		}

		public static function is_enabled(){
			return get_theme_mod('single_extras__estimated_delivery', false);
		}

		public static function allow_variations(){
			return get_theme_mod('estimated_delivery__variations', false);
		}

		static function is_product(){
			return reycore_wc__is_product();
		}

		function append_suffix_acf_option( $field ) {
			if( isset($field['append']) ) {
				$field['append'] = sprintf(esc_html__('Global: %s', 'rey-core'), get_theme_mod('estimated_delivery__days', 3));
			}
			return $field;
		}

		function append_suffix_acf_option_margin( $field ) {
			if( isset($field['append']) ) {
				$field['append'] = sprintf(esc_html__('Global: %s', 'rey-core'), get_theme_mod('estimated_delivery__days_margin', ''));
			}
			return $field;
		}

		function set_settings(){

			$position = [
				'default' => [
					'tag' => 'woocommerce_single_product_summary',
					'priority' => 39
				],
				'custom' => []
			];

			$position_option = get_theme_mod('estimated_delivery__position', 'default');

			$this->settings = apply_filters('reycore/woocommerce/estimated_delivery', [
				'days_text' => esc_html_x('days', 'Estimated delivery string', 'rey-core'),
				'date_format' => "l, M dS",
				'exclude_dates' => [], // array (YYYY-mm-dd) eg. array("2012-05-02","2015-08-01")
				'margin_excludes' => [], // ["Saturday", "Sunday"]
				'position' => isset($position[ $position_option ]) ? $position[ $position_option ] : $position[ 'default' ],
				'use_locale' => get_theme_mod('estimated_delivery__locale', false),
				'locale' => get_locale(),
				'locale_format' => get_theme_mod('estimated_delivery__locale_format', "%A, %b %d"),
				'variations' => self::allow_variations()
			]);

		}

		function init(){

			if( ! self::is_enabled() ){
				return;
			}

			if( ! self::is_product() ){
				return;
			}

			$this->set_settings();
			$this->set_args();

			if( wp_doing_ajax() && isset($_REQUEST['id']) && $product_id = absint($_REQUEST['id']) ){
				$this->args['product'] = wc_get_product($product_id);
			}

			if( isset($this->settings['position']['tag']) ){
				add_action($this->settings['position']['tag'], [$this, 'display'], $this->settings['position']['priority']);
			}

			add_shortcode('rey_estimated_delivery', [$this, 'display']);

			add_filter( 'woocommerce_available_variation', [$this, 'load_variation_settings_fields'] );
			add_action( 'woocommerce_single_product_summary', [$this, 'display_shipping_class'], 39);
		}

		function set_args(){
			$this->args = [
				'product' => wc_get_product(),
				'days' => reycore__get_option('estimated_delivery__days', 3),
				'days_individual' => reycore__acf_get_field( 'estimated_delivery__days' ),
				'margin' => reycore__get_option('estimated_delivery__days_margin', ''),
				'excludes' => get_theme_mod('estimated_delivery__exclude', ["Saturday", "Sunday"]),
				'inventory' => get_theme_mod('estimated_delivery__inventory', ['instock']),
			];
		}

		public function display( $atts = [] ){

			if( !isset($this->settings) ){
				$this->set_settings();
			}

			if( empty($this->args) ){
				$this->set_args();
			}

			if( isset($atts['id']) && $product_id = absint($atts['id']) ){
				$this->args['product'] = wc_get_product($product_id);
			}

			$this->output($this->args);
		}

		protected function output($args) {

			$args = wp_parse_args($args, [
				'custom_days' => '',
				'product' => false,
				'product_id' => 0
			]);

			if( $product_id = $args['product_id'] ){
				$args['product'] = wc_get_product($product_id);
			}

			if( ! $args['product'] ){
				return;
			}

			if( $custom_days = $args['custom_days'] ){
				$args['days'] = $custom_days;
			}

			$args['stock_status'] = $args['product']->get_stock_status();
			$args['date'] = $this->calculate_date(strtotime('today'), absint($args['days']), $args['excludes'] );

			// It's out of stock && has fallback text
			if( $args['stock_status'] === 'outofstock' && ! in_array( $args['stock_status'], $args['inventory'], true) &&
				($text = get_theme_mod('estimated_delivery__text_outofstock', '')) ){
				$this->print_wrapper( $text );
			}

			// It's on backorder && has fallback text
			else if( $args['stock_status'] === 'onbackorder' && ! in_array( $args['stock_status'], $args['inventory'], true) &&
				($text = get_theme_mod('estimated_delivery__text_onbackorder', '')) ){
				$this->print_wrapper( $text );
			}

			if( ! in_array( $args['stock_status'], $args['inventory'], true) ){
				return;
			}

			$display_type = get_theme_mod('estimated_delivery__display_type', 'number');

			if( ! $custom_days && $args['days_individual'] == -1 ){
				return;
			}

			$html = sprintf('<span class="rey-estimatedDelivery-title">%s</span>&nbsp;',
				get_theme_mod('estimated_delivery__prefix',
				esc_html__('Estimated delivery:', 'rey-core'))
			);

			if( ! $custom_days && $args['days_individual'] == '0' ){
				$html .= sprintf('<span class="rey-estimatedDelivery-date">%s</span>', esc_html__('Today', 'rey-core') );
			}

			else {

				$margin_date = '';

				if( $display_type === 'date' ){

					if( $args['margin'] ){
						$margin_excludes = $this->settings['margin_excludes'] ? $this->settings['margin_excludes'] : $args['excludes'];
						$margin_date = ' - ' . $this->calculate_date(strtotime('today'), absint($args['days']) + absint($args['margin']), $margin_excludes);
					}

					$html .= sprintf('<span class="rey-estimatedDelivery-date">%s%s</span>',
						$args['date'],
						$margin_date
					);
				}
				else {

					if( $args['margin'] ){
						$margin_date = ' - ' . absint($args['margin']);
					}

					$html .= sprintf('<span class="rey-estimatedDelivery-date">%1$s %2$s</span>',
						$args['days'] . $margin_date,
						$this->settings['days_text']
					);
				}
			}

			$this->print_wrapper( $html );
		}

		function print_wrapper($html){

			if( reycore__acf_get_field('estimated_delivery__hide') ){
				return;
			}

			if( ! $html ){
				return;
			}

			if( $custom_text = reycore__acf_get_field('estimated_delivery__custom_text') ){
				$html = $custom_text;
			}

			echo apply_filters( 'reycore/woocommerce/estimated_delivery/output', sprintf('<div class="rey-estimatedDelivery">%s</div>', $html), $this );
		}

		function calculate_date($timestamp, $days, $skipdays = []) {

			$i = 1;

			while ($days >= $i) {
				$timestamp = strtotime("+1 day", $timestamp);
				if ( (in_array(date("l", $timestamp), $skipdays)) || (in_array(date("Y-m-d", $timestamp), $this->settings['exclude_dates'])) )
				{
					$days++;
				}
				$i++;
			}

			if( $this->settings['use_locale'] ){
				setlocale(LC_TIME, $this->settings['locale']);
				return strftime($this->settings['locale_format'], $timestamp);
			}

			return date($this->settings['date_format'], $timestamp);
		}

		public function display_shipping_class(){

			if( ! get_theme_mod('single_extras__shipping_class', false) ){
				return;
			}

			global $product;

			if( $shipping_class = $product->get_shipping_class() ) {
				$term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
				if( is_a($term, 'WP_Term') ){
					echo apply_filters('reycore/woocommerce/product_page/shipping_class', '<p class="rey-shippingClass">' . $term->name . '</p>', $term);
				}
			}
		}

		function variation_settings_fields( $loop, $variation_data, $variation ) {

			if( ! self::is_enabled() ){
				return;
			}

			if( ! self::allow_variations() ){
				return;
			}

			woocommerce_wp_text_input([
				'id'            => self::VARIATIONS_KEY. $loop,
				'name'          => self::VARIATIONS_KEY. '[' . $loop . ']',
				'value'         => get_post_meta( $variation->ID, self::VARIATIONS_KEY, true ),
				'label'         => __( 'Estimated days delivery', 'rey-core' ),
				'desc_tip'      => true,
				'description'   => __( 'Add an estimation delivery date for this variation.', 'rey-core' ),
				'wrapper_class' => 'form-row form-row-full',
				'class' => 'input-text',
			]);
		}

		function save_variation_settings_fields( $variation_id, $loop ) {

			if( ! self::is_enabled() ){
				return;
			}

			if( ! self::allow_variations() ){
				return;
			}

			if ( isset( $_POST[self::VARIATIONS_KEY][ $loop ] ) ) {
				update_post_meta( $variation_id, self::VARIATIONS_KEY, reycore__clean( $_POST[self::VARIATIONS_KEY][ $loop ] ));
			}
		}

		function load_variation_settings_fields( $variation ) {

			if( ! self::is_product() ){
				return $variation;
			}

			if( ! $this->settings['variations'] ){
				return $variation;
			}

			if( ! ( $variation_estimation = get_post_meta( $variation[ 'variation_id' ], self::VARIATIONS_KEY, true ) ) ){
				return $variation;
			}

			ob_start();

			$args = $this->args;
			$args['custom_days'] = $variation_estimation;
			$args['product_id'] = $variation[ 'variation_id' ];

			$this->output($args);

			$variation['estimated_delivery'] = ob_get_clean();

			return $variation;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return Base
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

	}

	ReyCore_WooCommerce_EstimatedDelivery::getInstance();

endif;
