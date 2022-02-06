<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">
		<h2 class="rey-dashBox-title">
			<span><?php esc_html_e('Versions Status', 'rey') ?></span>
		</h2>
		<div class="rey-dashBox-content">
			<table class="rey-systemStatus rey-versionsStatus">
				<?php do_action('rey/dashboard/box/versions'); ?>
			</table>
		</div>
	</div>
</div>
