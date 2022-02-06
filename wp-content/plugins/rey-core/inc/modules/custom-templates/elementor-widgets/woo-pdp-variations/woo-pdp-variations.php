<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WooCommerce') && !class_exists('ReyCore_Widget_Woo_Pdp_Variations')):

class ReyCore_Widget_Woo_Pdp_Variations extends ReyCore_Widget_Woo_Base {

	public function get_name() {
		return 'reycore-woo-pdp-variations';
	}

	public function get_title() {
		return __( 'Variations (PDP)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function get_categories() {
		return [ 'rey-woocommerce-pdp' ];
	}

	public function show_in_panel() {
		return false;
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {



	}

	function render_template() {

		global $product;

		// Enqueue variation scripts.
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		// Get Available variations?
		$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

		reycore__get_template_part('template-parts/woocommerce/single-variable-noform', false, false, [
			'available_variations' => $get_variations ? $product->get_available_variations() : false,
			'attributes'           => $product->get_variation_attributes(),
		]);


	}

}
endif;
