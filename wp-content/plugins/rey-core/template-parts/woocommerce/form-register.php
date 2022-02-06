<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$user_value = ( ! empty( $_POST['username'] ) ) ? wp_unslash( $_POST['username'] ) : '';
$email_value = ( ! empty( $_POST['email'] ) ) ? wp_unslash( $_POST['email'] ) : '';
$action = !reycore_wc__get_account_panel_args('ajax_forms') && wc_get_page_id( 'myaccount' ) ? sprintf('action="%s"', esc_attr(get_permalink(wc_get_page_id( 'myaccount' )))) : '';

?>
<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	<div class="rey-accountPanel-form rey-registerForm ">

		<h4 class="rey-accountPanel-title"><?php esc_html_e( 'Create an account', 'rey-core' ); ?></h4>

		<form  <?php echo $action ?> method="post" class="register woocommerce-form woocommerce-form-register js-rey-woocommerce-form-register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="rey-form-row rey-form-row--text <?php echo ($user_value ? '--has-value' : ''); ?>">
					<label class="rey-label" for="reg_username"><?php esc_html_e( 'Username', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="rey-input rey-input--text" name="username" id="reg_username" autocomplete="username" value="<?php echo esc_attr($user_value); ?>" required /><?php // @codingStandardsIgnoreLine ?>
				</p>

			<?php endif; ?>

			<p class="rey-form-row rey-form-row--text <?php echo ($email_value ? '--has-value' : ''); ?>">
				<label class="rey-label" for="reg_email"><?php esc_html_e( 'Email address', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="email" class="rey-input rey-input--text" name="email" id="reg_email" autocomplete="email" value="<?php echo esc_attr($email_value); ?>" required pattern="[\w]{1,}[\w.+-]{0,}@[\w-]{2,}([.][a-zA-Z]{2,}|[.][\w-]{2,}[.][a-zA-Z]{2,})$" /><?php // @codingStandardsIgnoreLine ?>
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="rey-form-row rey-form-row--text">
					<label class="rey-label" for="reg_password"><?php esc_html_e( 'Password', 'rey-core' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="password" class="rey-input rey-input--text --suports-visibility" name="password" id="reg_password" autocomplete="new-password" required />
				</p>

			<?php endif; ?>

			<div class="rey-form-row">
				<?php do_action( 'woocommerce_register_form' ); ?>
			</div>

			<p class="">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="btn-line-active submit-btn" name="register" value="<?php echo esc_attr__( 'Register', 'rey-core' ); ?>" aria-label="<?php esc_html_e('Register', 'rey-core') ?>"><?php esc_html_e( 'CREATE ACCOUNT', 'rey-core' ); ?></button>
			</p>

			<div class="rey-accountForms-notice"></div>

			<div class="rey-accountPanel-links rey-accountForms-links">
				<a class="btn btn-line" <?php echo apply_filters('reycore/woocommerce/account_links/login_btn_attributes', 'data-location="rey-loginForm"'); ?>><?php esc_html_e( 'LOGIN', 'rey-core' ); ?></a>
				<a class="btn btn-line" <?php echo apply_filters('reycore/woocommerce/account_links/forget_btn_attributes', 'data-location="rey-forgetForm"'); ?>><?php esc_html_e( 'Forgot password', 'rey-core' ); ?></a>
			</div>

			<?php do_action( 'woocommerce_register_form_end' ); ?>
		</form>

	</div>

<?php endif; ?>
