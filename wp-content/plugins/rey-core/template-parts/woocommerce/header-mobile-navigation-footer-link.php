<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
?>
<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" class="rey-mobileNav--footerItem">
	<?php
		if(! is_user_logged_in() ){
			esc_html_e('Connect to your account', 'rey-core');
		}
		else {
			esc_html_e('Your account', 'rey-core');
		}
	?>
	<?php echo reycore__get_svg_icon(['id' => 'rey-icon-user']) ?>
</a>

<?php if( is_user_logged_in() ): ?>
	<a href="<?php echo esc_url( wp_logout_url( reycore__get_current_url() ) ); ?>" class="rey-mobileNav--footerItem">
		<?php
			esc_html_e('Log Out', 'rey-core');
		?>
	</a>
<?php endif; ?>
