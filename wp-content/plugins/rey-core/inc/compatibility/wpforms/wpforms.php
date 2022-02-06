<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_Compatibility__WPForms') ):

	class ReyCore_Compatibility__WPForms
	{
		public function __construct()
		{
			add_filter('reycore/wpforms/forms', [$this, 'get_forms'], 10);
			add_filter('reycore/wpforms/control_description', [$this, 'get_notice'], 10);
			add_filter('reycore/woocommerce/request_quote/output', [$this, 'request_quote_output'], 10, 2);
			add_action('wpforms_email_body', [$this, 'add_custom_content'], 10);
		}

		/**
		 * Get forms.
		 *
		 * Retrieve an array of forms from the CF7 plugin.
		 */
		public function get_forms( $forms ) {

			if( ! function_exists('wpforms') ){
				return $forms;
			}

			$wpforms = wpforms()->form->get();

			if ( ! empty( $wpforms ) ) {
				$forms[ '' ] = esc_html__('- Select -', 'rey-core');
				foreach ( $wpforms as $form ) {
					$forms[ absint( $form->ID ) ] = esc_html( $form->post_title );
				}
			}

			return $forms;
		}

		/**
		 * Get notice.
		 *
		 */
		public function get_notice() {

			if( function_exists('wpforms') ){
				return esc_html__( 'Select the contact form you created in WP Forms.', 'rey-core' );
			}

			return __('<p>It seems <a href="https://wordpress.org/plugins/wpforms-lite/" target="_blank">WPForms</a> is not installed or active. Please activate it to be able to create a contact form to be used with this option.</p>', 'rey-core');
		}

		function request_quote_output( $html, $args ){

			if( ! function_exists('wpforms') ){
				return $html;
			}

			if( get_theme_mod('request_quote__form_type', 'cf7') !== 'wpforms' ){
				return $html;
			}

			if( ! ($wpform = get_theme_mod('request_quote__wpforms', '')) ){
				return $html;
			}

			$args = wp_parse_args($args, [
				'class' => ''
			]);

			$shortcode = sprintf( '[wpforms id="%d"]', $wpform );

			return sprintf( '<div class="rey-wpforms-form %s">%s</div>', $args['class'], do_shortcode( $shortcode ) );
		}

		function add_custom_content( $email ) {

			if( get_theme_mod('request_quote__form_type', 'cf7') !== 'wpforms' ){
				return;
			}

			if( ! ($wpform = get_theme_mod('request_quote__wpforms', '')) ){
				return;
			}

			if ( ! ( isset($email->form_data) && absint( $email->form_data['id'] ) === absint($wpform) ) ) {
				return;
			}

			if( isset($_REQUEST['rey-request-quote-product-id']) && $product_id = absint($_REQUEST['rey-request-quote-product-id']) ){


				$extra = 'Product ID: <strong>'. $product_id .'</strong>.<br>';

				$product = wc_get_product($product_id);
				$product_title = $product->get_title();

				if( $product->get_type() === 'variation' ){
					$product_title = $product->get_name();
				}

				if( $product && $psku = $product->get_sku() ){
					$extra .= 'Product SKU: <strong>'. $psku .'</strong>.<br>';
				}

				$extra .= 'Product: <a href="'. esc_url( get_the_permalink( $product_id ) ) .'"><strong>' . $product_title . '</strong></a>.<br>';
				$extra = apply_filters('reycore/woocommerce/request_quote_mail', $extra, $product_id);

				ob_start();
				$email->get_template_part( 'field', $email->get_template(), true );
				$field_item = ob_get_clean();

				$field_item = str_replace( '{field_name}', esc_html_x('Product data:', 'WPForms product data title in email.', 'rey-core'), $field_item );
				$field_item = str_replace( '{field_value}', $extra, $field_item );

				echo $field_item;
			}

		}
	}

	new ReyCore_Compatibility__WPForms;
endif;
