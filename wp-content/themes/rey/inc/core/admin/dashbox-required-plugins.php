<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include __DIR__ . '/tpl-required-plugins.php';

if( empty($plugins_to_install) ){
	return;
} ?>

<div class="rey-dashBox">
	<div class="rey-dashBox-inner">

		<h2 class="rey-dashBox-title"><?php esc_html_e('Install Required Plugins', 'rey') ?></h2>

		<div class="rey-dashBox-content">

			<p><?php esc_html_e('Rey Theme needs the following plugins to be installed & active:', 'rey') ?></p>

			<?php echo rey__kses_post_with_svg( $plugins_output ); ?>

			<div class="dashBox-reqPlugins-buttons">

				<button class="rey-adminBtn rey-adminBtn-secondary js-dashBox-installRequired <?php echo empty($plugins_to_install) ? 'rey-adminBtn--disabled' : ''; ?>"><?php esc_html_e('INSTALL / ACTIVATE PLUGINS', 'rey') ?></button>

				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'reycore-demo-import' ), admin_url( 'admin.php' ) ) ); ?>" class="rey-importDemos rey-adminBtn rey-adminBtn-line <?php echo empty($plugins_to_install) ? '--visible' : ''; ?> <?php echo !$is_registerd ? '--not-registered':''; ?>"><?php esc_html_e('IMPORT DEMOS', 'rey') ?></a>

			</div>

		</div>
	</div>
</div>
