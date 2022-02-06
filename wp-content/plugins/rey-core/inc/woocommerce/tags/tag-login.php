<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_LoginRegister') ):

class ReyCore_WooCommerce_LoginRegister
{

	public $modal_login_atts = [
		'enabled' => true,
		'forms' => true
	];

	public function __construct() {
		add_action( 'wp_ajax_nopriv_reycore_account_forms', [$this, 'forms_ajax_process'] );
		add_action( 'reycore/woocommerce/account_panel', [$this, 'display_login_forms_account_panel'], 20);
		add_shortcode( 'rey_ajax_login_form', [$this, 'forms_shortcode']);
	}

	function add_shortcode_atts($params){
		return wp_parse_args($this->modal_login_atts, $params);
	}

	function forms_shortcode( $atts = [] ) {

		$this->modal_login_atts['login_register_redirect'] = isset($atts['redirect_type']) ? $atts['redirect_type'] : 'load_menu';
		$this->modal_login_atts['login_register_redirect_url'] = isset($atts['redirect_url']) ? $atts['redirect_url'] : '';
		$this->modal_login_atts['ajax_forms'] = isset($atts['ajax']) ? (absint($atts['ajax']) === 1) : true;

		add_filter('rey/header/account_params', [$this, 'add_shortcode_atts'], 20);
		$this->display_account_forms();
		remove_filter('rey/header/account_params', [$this, 'add_shortcode_atts'], 20);

	}

	function display_account_forms(){

		if( ! is_user_logged_in() ): ?>

			<div class="rey-accountForms" <?php reycore_wc__account_redirect_attrs() ?>>
				<?php

					reyCoreAssets()->add_styles('reycore-pass-visibility');
					reyCoreAssets()->add_scripts('reycore-wc-header-account-forms');

					reycore__get_template_part('template-parts/woocommerce/form-login');
					reycore__get_template_part('template-parts/woocommerce/form-register');
					reycore__get_template_part('template-parts/woocommerce/form-lost-password');

					// Password strength meter.
					if ( 'no' === get_option( 'woocommerce_registration_generate_password' )  || is_edit_account_page() || is_lost_password_page() ) {
						wp_enqueue_script( 'wc-password-strength-meter' );
					}

					do_action( 'woocommerce_after_customer_login_form' ); ?>
			</div>

		<?php else:

			reycore__get_template_part('template-parts/woocommerce/header-account-menu');

		endif;
	}

	function display_login_forms_account_panel(){

		$show_account_forms_and_menu = true;

		// hide if catalog mode
		// and if flagged through filter
		if( get_theme_mod('shop_catalog', false) && apply_filters('reycore/catalog_mode/hide_account', false) ){
			$show_account_forms_and_menu = false;
		}

		$args = reycore_wc__get_account_panel_args();

		if( ! $args['forms'] ){
			$show_account_forms_and_menu = false;
		}

		if( $show_account_forms_and_menu && get_the_ID() !== wc_get_page_id('myaccount') ) {
			$this->display_account_forms();
		}

		if( $args['display'] === 'offcanvas' ){
			reyCoreAssets()->add_styles('reycore-side-panel');
		}
	}


	function forms_ajax_process() {

		if( isset($_POST['action_type']) && ($action_type = reycore__clean($_POST['action_type'])) ):

			$data = [];

			if( ! method_exists($this, "process_{$action_type}") ){
				wp_send_json_error( esc_html__('Incorrect function!', 'rey-core') );
			}

			wc_clear_notices();

			ob_start();
			call_user_func( [$this, "process_{$action_type}"] );
			$data['html'] = ob_get_clean();

			ob_start();
			wc_print_notices();
			$data['notices'] = ob_get_clean();

			wp_send_json_success( $data );

		endif;

		wp_send_json_error( esc_html__('Something went wrong while submitting this form. Please try again!', 'rey-core') );
	}

	public function process_login(){

		// The global form-login.php template used `_wpnonce` in template versions < 3.3.0.
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-login-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		if ( isset( $_POST['login'], $_POST['username'], $_POST['password'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-login' ) ) {

			try {
				$creds = array(
					'user_login'    => trim( wp_unslash( $_POST['username'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'user_password' => $_POST['password'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					'remember'      => isset( $_POST['rememberme'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'woocommerce_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $creds['user_login'] ) ) {
					throw new Exception( '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . __( 'Username is required.', 'rey-core' ) );
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
					}
				}

				// Perform the login.
				$user = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {

					throw new Exception( $user->get_error_message() );

				} else {

					reycore__get_template_part('template-parts/woocommerce/header-account-menu');

				}
			} catch ( Exception $e ) {
				wc_add_notice( apply_filters( 'login_errors', $e->getMessage() ), 'error' );
				do_action( 'woocommerce_login_failed' );
			}
		}
	}

	public function process_register(){

		$nonce_value = isset( $_POST['_wpnonce'] ) ? wp_unslash( $_POST['_wpnonce'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? wp_unslash( $_POST['woocommerce-register-nonce'] ) : $nonce_value; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( isset( $_POST['register'], $_POST['email'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {
			$username = 'no' === get_option( 'woocommerce_registration_generate_username' ) && isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$password = 'no' === get_option( 'woocommerce_registration_generate_password' ) && isset( $_POST['password'] ) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$email    = wp_unslash( $_POST['email'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			try {
				$validation_error  = new WP_Error();
				$validation_error  = apply_filters( 'woocommerce_process_registration_errors', $validation_error, $username, $password, $email );
				$validation_errors = $validation_error->get_error_messages();

				if ( 1 === count( $validation_errors ) ) {
					throw new Exception( $validation_error->get_error_message() );
				} elseif ( $validation_errors ) {
					foreach ( $validation_errors as $message ) {
						wc_add_notice( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $message, 'error' );
					}
					throw new Exception();
				}

				$new_customer = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ), $password );

				if ( is_wp_error( $new_customer ) ) {
					throw new Exception( $new_customer->get_error_message() );
				}

				if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) ) {
					wc_add_notice( __( 'Your account was created successfully and a password has been sent to your email address.', 'woocommerce' ) );
				} else {
					wc_add_notice( __( 'Your account was created successfully. Your login details have been sent to your email address.', 'woocommerce' ) );
				}

				// Only redirect after a forced login - otherwise output a success notice.
				if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
					wc_set_customer_auth_cookie( $new_customer );
				}

				reycore__get_template_part('template-parts/woocommerce/header-account-menu');

			} catch ( Exception $e ) {
				if ( $e->getMessage() ) {
					wc_add_notice( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $e->getMessage(), 'error' );
				}
			}
		}
	}

	public function process_forgot(){

		if ( isset( $_POST['wc_reset_password'], $_POST['user_login'] ) ) {

			$nonce_value = wc_get_var( $_REQUEST['woocommerce-lost-password-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

			if ( ! wp_verify_nonce( $nonce_value, 'lost_password' ) ) {
				return;
			}

			$success = WC_Shortcode_My_Account::retrieve_password();
		}

	}

}

new ReyCore_WooCommerce_LoginRegister;

endif;
