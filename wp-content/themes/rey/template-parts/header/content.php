<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rey-siteHeader-container">
    <div class="rey-siteHeader-row">

		<?php
		/**
		 * Prints content inside header's main row
		 * @hook rey/header/row
		 */
		rey_action__header_row(); ?>

	</div>
	<!-- .rey-siteHeader-row -->

</div>
