<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include __DIR__ . '/tpl-required-plugins.php'; ?>

<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">

<div class="rey-adminPage-wrapper rey-wizard-wrapper wrap">

	<a href="#" class="rey-adminBtn rey-wizard-skipWizard js-skipWizard"><?php esc_html_e('DISABLE WIZARD', 'rey') ?></a>

	<div class="rey-wizard-wrapperInner">

		<div class="rWizard-step rWizard-step--1 <?php echo ( ($is_registerd) ? '--registered' : '' ) ?>">

			<?php if( !$is_registerd ): ?>
				<?php echo rey__get_svg_icon(['id'=>'rey-icon-logo', 'class'=>'rWizard-logo']) ?>
				<h1><?php esc_html_e('Let\'s get started', 'rey') ?></h1>
				<p class="rWizard-stepIntro"><?php esc_html_e('First things first, let’s register your copy of Rey Theme to enable theme updates, importing demos, installing and updating premium plugins.', 'rey') ?></p>

				<form class="rey-adminForm js-rWizard-registrationForm" method="post" action="#">

					<?php include __DIR__ . '/tpl-register-form.php'; ?>

					<div class="rWizard-buttons">
						<button type="submit" class="rey-adminBtn rey-adminBtn-primary "><?php esc_html_e('NEXT', 'rey') ?></button>
						<a href="#" class="rey-adminBtn rey-adminBtn-line js-skipRegistration">
							<?php esc_html_e('SKIP REGISTRATION', 'rey') ?>
							<div class="rey-tooltip rey-tooltip--top rey-tooltip--large">
								<?php _e('<strong>Are you sure?</strong> Skipping registration will result in certain features to be disabled( such as importing demos, installing premium plugins or even theme updates).', 'rey') ?>
							</div>
						</a>
					</div>
				</form>

				<div class="rWizard-stepsIndicators">
					<div class="rWizard-stepsIndicator--inactive">1</div>
					<div class="rWizard-stepsIndicator--active">1</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="rWizard-step rWizard-step--2 <?php echo esc_attr( ($is_registerd && empty($plugins_to_install)) ? '--plugins-installed': '' ) ?>">

			<?php echo rey__get_svg_icon(['id'=>'rey-icon-logo', 'class'=>'rWizard-logo']) ?>
			<h1><?php esc_html_e('Install required plugins', 'rey') ?></h1>
			<p class="rWizard-stepIntro"><?php esc_html_e('Rey theme requires and recommends the following plugins to be installed and active. Rey can run without these plugins however most options and features won’t be available.', 'rey') ?></p>

			<div class="reyAdmin-reqPlugins js-reyAdmin-reqPlugins">
				<?php echo rey__kses_post_with_svg( $plugins_output ); ?>
			</div>

			<div class="rWizard-installChild">
				<span class="rey-spinnerIcon"></span>
				<div class="rWizard-installChild-box">
					<input id="wizardInstallChild" type="checkbox" checked>
					<label for="wizardInstallChild"></label>
				</div>
				<h4><?php esc_html_e('Install Child Theme?', 'rey') ?> <a href="https://woocommerce.com/posts/why-child-themes-matter/#" target="_target"><?php esc_html_e('See why its recommended', 'rey') ?></a>.</h4>
			</div>

			<div class="rWizard-buttons">
				<a href="#" class="rey-adminBtn rey-adminBtn-primary js-rWizard-btnStep-2 "><?php esc_html_e('INSTALL & NEXT', 'rey') ?></a>

				<?php if( !ReyTheme_Base::get_purchase_code() ): ?>
				<a href="#" class="rey-adminBtn rey-adminBtn-line js-backRegistration">
					<?php esc_html_e('BACK TO REGISTRATION', 'rey') ?>
				</a>
				<?php endif; ?>

			</div>

			<div class="rWizard-stepsIndicators">
				<div class="rWizard-stepsIndicator--inactive">2</div>
				<div class="rWizard-stepsIndicator--active">2</div>
			</div>
		</div>

		<div class="rWizard-step rWizard-step--3 ">
			<?php echo rey__get_svg_icon(['id'=>'rey-icon-logo', 'class'=>'rWizard-logo']) ?>
			<h1><?php esc_html_e('Done!', 'rey') ?></h1>

			<p class="rWizard-stepIntro">
				<?php
					$clean_query_args = [
						'post_type' => 'page',
					];
					$clean_url = 'post-new.php';

					if( class_exists('Elementor\Plugin') ){
						$clean_query_args['action'] = 'elementor_new_post';
						$clean_query_args['_wpnonce'] = wp_create_nonce( 'elementor_action_new_post' );
						$clean_url = 'edit.php';
					}

					echo rey__wp_kses( sprintf( __( 'You can now install demo content (pre-defined site) or <a href="%s">start clean</a> from scratch.', 'rey' ),  esc_url( add_query_arg( $clean_query_args, admin_url( $clean_url ) ) ) ) );
				?>
			</p>

			<p class="rWizard-stepIntro--skipped"><?php echo rey__wp_kses( sprintf( __('<strong>Importing demos is disabled</strong> because you have skipped registration. If you want to register Rey and import demos, simply head over to <a href="%s">Rey Dashboard</a> and register there. You can now start building your website from scratch.', 'rey') , esc_url( add_query_arg( ['page' => self::DASHBOARD_PAGE_ID ], admin_url('admin.php') ) ) ) ); ?></p>

			<div class="rWizard-buttons">
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'reycore-demo-import' ), admin_url( 'admin.php' ) ) ); ?>" class="rey-adminBtn rey-adminBtn-primary"><?php esc_html_e('IMPORT DEMOS', 'rey') ?></a>
				<a href="https://support.reytheme.com/" target="_blank" class="rey-adminBtn rey-adminBtn-line"><?php esc_html_e('ACCESS DOCUMENTATION', 'rey') ?></a>
			</div>

			<div class="rWizard-stepsIndicators">
				<div class="rWizard-stepsIndicator--inactive">3</div>
				<div class="rWizard-stepsIndicator--active">3</div>
			</div>
		</div>

	</div>
</div>

<?php echo rey__get_svg_icon(['id'=>'rey-icon-logo', 'class'=>'rey-wizard-bottomLogo']) ?>
