<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rey_purchase_code = isset($_POST['rey_purchase_code']) ? trim( $_POST['rey_purchase_code'] ) : '';
$rey_email_address = isset($_POST['rey_email_address']) ? sanitize_email( $_POST['rey_email_address'] ) : '';

$rey_subscribe_newsletter = '';

if( isset($_POST['rey_subscribe_newsletter']) && $_POST['rey_subscribe_newsletter'] != 1 ){
	$rey_subscribe_newsletter = '';
}

?>

<div class="reyAdmin-formRow">
	<label for="rey-purchase-code" class="reyAdmin-label --required"><?php esc_html_e( 'Your Purchase Code', 'rey' ); ?></label><br>
	<input id="rey-purchase-code" class="reyAdmin-input" name="rey_purchase_code" type="text" value="<?php echo esc_attr($rey_purchase_code) ?>" placeholder="<?php esc_attr_e( 'e.g. cb0e057f-a05d-4758-b314-024db98eff85', 'rey' ); ?>" required pattern="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$" autocomplete="<?php esc_attr_e(apply_filters('rey/register_form/autocomplete', '')); ?>" />
	<p class="reyAdmin-inputDesc"><?php _e('Login to your Envato Account and access <a href="https://themeforest.net/item/rey-multipurpose-woocommerce-theme/24689383/support" target="_blank">Rey\'s Support Tab</a> where you can find all your Rey Theme purchase codes.', 'rey') ?></p>
</div>

<div class="reyAdmin-formRow">
	<label for="rey-email-address" class="reyAdmin-label"><?php esc_html_e( 'Your Email Address', 'rey' ); ?></label><br>
	<input id="rey-email-address" class="reyAdmin-input" name="rey_email_address" type="text" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="Invalid email address." value="<?php echo esc_attr($rey_email_address) ?>" />
	<p class="reyAdmin-inputDesc"><?php esc_html_e('Not mandatory, but recommended to keep an open communication channel for very important announcements.', 'rey') ?></p>
</div>

<div class="reyAdmin-formRow">
	<div class="reyAdmin-checkboxWrapper">
		<input id="rey-subscribe-newsletter" name="rey_subscribe_newsletter" type="checkbox" value="1" <?php echo esc_attr($rey_subscribe_newsletter) ?> />
		<label for="rey-subscribe-newsletter" class="reyAdmin-label --checkbox"><?php esc_html_e( 'Subscribe to our newsletter?', 'rey' ); ?></label>
	</div>
	<p class="reyAdmin-inputDesc"><?php esc_html_e('We hate spam too and rather than pushing annoying emails we\'re actually busy making this theme better and better, but it\'s likely sometimes we\'ll send great tips, freebies and limited offers. You can unsubscribe at anytime.', 'rey') ?></p>
</div>
