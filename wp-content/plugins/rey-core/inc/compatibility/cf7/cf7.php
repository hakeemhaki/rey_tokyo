<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_Compatibility__CF7') ):

	class ReyCore_Compatibility__CF7
	{
		public function __construct()
		{
			add_filter( 'wpcf7_load_js', '__return_false' );
			add_filter( 'wpcf7_load_css', '__return_false' );
			add_action( 'wpcf7_contact_form', [$this, 'load_scripts']);
			add_filter( 'reycore/cf7/forms', [$this, 'get_forms'], 10);
			add_filter( 'reycore/cf7/control_description', [$this, 'description'], 10);
			add_filter( 'reycore/woocommerce/request_quote/output', [$this, 'request_quote_output'], 10, 2);
			add_action( 'wpcf7_before_send_mail', [$this, 'before_send_mail']);
		}

		function load_scripts(){
			if( function_exists('wpcf7_enqueue_scripts') ){
				wpcf7_enqueue_scripts();
			}
			if( function_exists('wpcf7_enqueue_styles') ){
				wpcf7_enqueue_styles();
			}
		}

		/**
		 * Get forms.
		 *
		 * Retrieve an array of forms from the CF7 plugin.
		 */
		public function get_forms( $forms ) {

			if( ! class_exists('WPCF7') ){
				return $forms;
			}

			if ( $cf7 = get_posts( 'post_type="wpcf7_contact_form"&numberposts=-1' ) ) {
				$forms[ '' ] = esc_html__('- Select -', 'rey-core');
				foreach ( $cf7 as $cform ) {
					$forms[ $cform->ID ] = $cform->post_title;
				}
			}

			return $forms;
		}

		public function description() {

			if( class_exists('WPCF7') ){
				return esc_html__( 'Select the contact form you created in Contact Form 7.', 'rey-core' );
			}

			return __('<p>It seems <a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Contact Form 7</a> is not installed or active. Please activate it to be able to create a contact form to be used with this option.</p>', 'rey-core');
		}

		function request_quote_output( $html, $args ){

			if( ! class_exists('WPCF7') ){
				return $html;
			}

			if( get_theme_mod('request_quote__form_type', 'cf7') !== 'cf7' ){
				return $html;
			}

			if( ! ($cf7_form = get_theme_mod('request_quote__cf7', '')) ){
				return $html;
			}

			$args = wp_parse_args($args, [
				'class' => ''
			]);

			if ( $contact_form = wpcf7_contact_form($cf7_form) ) {
				$html = $contact_form->form_html([
					'html_class' => $args['class']
				]);
			}

			return $html;
		}


		public function before_send_mail( $WPCF7_ContactForm )
		{
			//Get current form
			$wpcf7 = WPCF7_ContactForm::get_current();

			// get current SUBMISSION instance
			$submission = WPCF7_Submission::get_instance();

			// Ok go forward
			if ( $submission ) {

				// get submission data
				$data = $submission->get_posted_data();

				if ( empty( $data ) ) {
					return;
				}

				$mail = $wpcf7->prop( 'mail' );

				$extra = '';

				if( isset($data['rey-request-quote-product-id']) && $product_id = absint($data['rey-request-quote-product-id']) ){

					$extra .= 'Product ID: <strong>'. $product_id .'</strong>.<br>';

					$product = wc_get_product($product_id);
					$product_title = $product->get_title();

					if( $product->get_type() === 'variation' ){
						$product_title = $product->get_name();
					}

					if( $product && $psku = $product->get_sku() ){
						$extra .= 'Product SKU: <strong>'. $psku .'</strong>.<br>';
					}

					$extra .= 'Product: <a href="'. esc_url( get_the_permalink( $product_id ) ) .'"><strong>' . $product_title . '</strong></a>.<br>';

					if( strpos($mail['subject'], '[your-subject]') !== false ){
						$mail['subject'] = str_replace( '[your-subject]', str_replace('&#8211;', '-', $product_title), $mail['subject']);
					}
					else {
						$mail['subject'] = $mail['subject'] . ' - ' . $product_title;
					}

					$extra = apply_filters('reycore/woocommerce/request_quote_mail', $extra, $product_id);
				}

				$mail['body'] = $extra . '<br><br>' . $mail['body'];
				$mail['use_html'] = true;

				// Save the email body
				$wpcf7->set_properties( [
					"mail" => $mail,
				]);

				return $wpcf7;
			}
		}

	}

	new ReyCore_Compatibility__CF7;
endif;
