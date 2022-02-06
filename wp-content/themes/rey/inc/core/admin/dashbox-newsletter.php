<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rey_email_address = isset($_POST['rey_email_address']) ? $_POST['rey_email_address'] : get_bloginfo('admin_email');
?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">
		<h2 class="rey-dashBox-title"><?php esc_html_e('Subscribe to newsletter', 'rey') ?></h2>
		<div class="rey-dashBox-content">

			<form class="rey-adminForm js-subscribeNewsletterForm" method="post" action="#">

				<div class="reyAdmin-formRow">
					<label for="rey-email-address" class="reyAdmin-label"><?php esc_html_e( 'Your Email Address', 'rey' ); ?></label><br>
					<input id="rey-email-address" class="reyAdmin-input" name="rey_email_address" type="text" required pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="Invalid email address." value="<?php echo sanitize_email( $rey_email_address ) ?>" />
					<p class="reyAdmin-inputDesc"><?php esc_html_e('We hate spam too and rather than pushing annoying emails we\'re actually busy making this theme better and better, but it\'s likely sometimes we\'ll send great tips, freebies and limited offers. You can unsubscribe at anytime.', 'rey') ?></p>
				</div>

				<button type="submit" class="rey-adminBtn rey-adminBtn-secondary"><?php esc_html_e( 'SUBSCRIBE', 'rey' ); ?></button>
			</form>

		</div>
	</div>
</div>
