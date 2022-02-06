<?php
/**
 * The plugin page view - the "settings" page of the plugin.
 *
 * @package ocdi
 */

// namespace OCDI;

$predefined_themes = self::$ocdi->import_files;

if ( ! empty( self::$ocdi->import_files ) && isset( $_GET['import-mode'] ) && 'manual' === $_GET['import-mode'] ) {
	$predefined_themes = array();
} ?>

<div class="ocdi  wrap ">

	<h1 class="ocdi__title"><?php esc_html_e( 'Import Demos', 'rey-core' ); ?></h1>

	<?php

	// Display warrning if PHP safe mode is enabled, since we wont be able to change the max_execution_time.
	if ( ini_get( 'safe_mode' ) ) {
		printf(
			esc_html__( '%sWarning: your server is using %sPHP safe mode%s. This means that you might experience server timeout errors.%s', 'rey-core' ),
			'<div class="notice  notice-warning  is-dismissible"><p>',
			'<strong>',
			'</strong>',
			'</p></div>'
		);
	}

	?>
	<div class="rey-ocdi__intro-text">

		<p><?php esc_html_e( 'When you import the data, the following things might happen:', 'rey-core' ); ?></p>

		<ul>
			<li><?php _e( 'Please click on the Import button only once and wait, <strong>it can take a couple of minutes</strong>.', 'rey-core' ); ?></li>
			<li><?php esc_html_e( 'No existing posts, pages, categories, images, custom post types or any other data will be deleted or modified.', 'rey-core' ); ?></li>
			<li><?php esc_html_e( 'Posts, pages, images, widgets, menus and other theme settings will get imported.', 'rey-core' ); ?></li>
			<li><?php esc_html_e( 'It\'s highly recommended to import on a clean WordPress instance.', 'rey-core' ); ?></li>
			<li>
				<?php _e( 'Having troubles? Try adjusting <a href="#" class="js-rey-demos-adjust-settings">these settings</a>:', 'rey-core' ); ?>
				<div class="rey-demosImportSettings --hidden">
					<div class="rey-demosImportSetting-item">
						<div class="rey-toggleSwitch">
							<span class="rey-spinnerIcon"></span>
							<div class="rey-toggleSwitch-box">
								<input id="rey-toggSwitchreycore-thumbsregen" type="checkbox" value="1" <?php checked(ReyCore_Demos::getInstance()->check_ocdi_setting('thumbnails')) ?> class="js-toggle-ocdi-settings" data-setting="thumbnails">
								<label for="rey-toggSwitchreycore-thumbsregen">
									<span class="rey-toggleSwitch-label"><?php esc_html_e('Enable Thumbnail Regeneration', 'rey-core') ?></span>
									<span class="rey-toggleSwitch-label-desc"><?php esc_html_e('While importing, smaller versions of images are being generated, which takes up a lot of server memory. So by default it\'s disabled to avoid getting a 500 internal server error and to speed up importing.', 'rey-core') ?></span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</li>
		</ul>

		<div class="intro-text__buttons">

			<a href="#" class="button button-primary js-rey-refresh-demos"><?php esc_html_e('Refresh Demos List', 'rey-core') ?></a>
			<a href="https://support.reytheme.com/kb/importing-demos/" target="_blank" class="button button-secondary intro-text__buttons-help"><?php esc_html_e('Need Help?', 'rey-core') ?></a>

			<?php if ( ! empty( self::$ocdi->import_files ) ) : ?>
				<?php if ( empty( $_GET['import-mode'] ) || 'manual' !== $_GET['import-mode'] ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::MENU_SLUG, 'import-mode' => 'manual' ), admin_url( 'admin.php' ) ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Switch to manual import', 'rey-core' ); ?></a>
				<?php else : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::MENU_SLUG ), admin_url( 'admin.php' ) ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Switch to theme predefined imports', 'rey-core' ); ?></a>
				<?php endif; ?>
			<?php endif; ?>

		</div>
	</div>

	<?php do_action('reycore/ocdi/after_buttons'); ?>

	<?php if ( empty( self::$ocdi->import_files ) ) : ?>
		<div class="notice  notice-info  is-dismissible">
			<p><?php esc_html_e( 'There are no predefined import files available in this theme. Please upload the import files manually!', 'rey-core' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( empty( $predefined_themes ) ) : ?>

		<div class="ocdi__file-upload-container">
			<h2><?php esc_html_e( 'Manual demo files upload', 'rey-core' ); ?></h2>
			<ul class="ul-square">
				<!-- <li><?php _e( 'Please download the demo data by selecting it from {{dropdown}}.', 'rey-core' ); ?></li> -->
				<li><?php printf( __( 'Please request the demo data in our <a href="%s" target="_blank">Support site</a>. In the message, please specify which demo you want to import.', 'rey-core' ), 'https://support.reytheme.com/new/' ); ?></li>
				<li><?php printf( __( 'Before importing, please access "<a href="%s" target="_blank">Appearance > Install Plugins</a>" and make sure plugins are installed & enabled.', 'rey-core' ), admin_url('admin.php?page=rey-install-required-plugins') ); ?></li>
			</ul>

			<div class="ocdi__file-upload">
				<h3><label for="content-file-upload"><?php esc_html_e( 'Choose a XML file for content import:', 'rey-core' ); ?></label></h3>
				<input id="ocdi__content-file-upload" type="file" name="content-file-upload">
			</div>

			<div class="ocdi__file-upload">
				<h3><label for="widget-file-upload"><?php esc_html_e( 'Choose a WIE or JSON file for widget import:', 'rey-core' ); ?></label></h3>
				<input id="ocdi__widget-file-upload" type="file" name="widget-file-upload">
			</div>

			<div class="ocdi__file-upload">
				<h3><label for="customizer-file-upload"><?php esc_html_e( 'Choose a DAT file for customizer import:', 'rey-core' ); ?></label></h3>
				<input id="ocdi__customizer-file-upload" type="file" name="customizer-file-upload">
			</div>

			<div class="ocdi__file-upload">
				<h3><label for="rey-file-upload"><?php esc_html_e( 'Choose a JSON file for Rey\'s Configuration:', 'rey-core' ); ?></label></h3>
				<input id="ocdi__rey-file-upload" type="file" name="rey-file-upload">
			</div>

		</div>

		<p class="ocdi__button-container">
			<button class="ocdi__button  button  button-hero  button-primary  js-ocdi-import-data"><?php esc_html_e( 'Import Demo Data', 'rey-core' ); ?></button>
		</p>

	<?php elseif ( 1 === count( $predefined_themes ) ) : ?>

		<div class="ocdi__demo-import-notice  js-ocdi-demo-import-notice"><?php
			if ( is_array( $predefined_themes ) && ! empty( $predefined_themes[0]['import_notice'] ) ) {
				echo wp_kses_post( $predefined_themes[0]['import_notice'] );
			}
		?></div>

		<p class="ocdi__button-container">
			<button class="ocdi__button  button  button-hero  button-primary  js-ocdi-import-data"><?php esc_html_e( 'Import Demo Data', 'rey-core' ); ?></button>
		</p>

	<?php else : ?>

		<!-- OCDI grid layout -->
		<div class="ocdi__gl  js-ocdi-gl">
		<?php
			// Prepare navigation data.
			$categories = OCDI\Helpers::get_all_demo_import_categories( $predefined_themes );
		?>
			<?php if ( ! empty( $categories ) ) : ?>
				<div class="ocdi__gl-header  js-ocdi-gl-header">
					<nav class="ocdi__gl-navigation">
						<ul>
							<li class="active"><a href="#all" class="ocdi__gl-navigation-link  js-ocdi-nav-link"><?php esc_html_e( 'All', 'rey-core' ); ?></a></li>
							<?php foreach ( $categories as $key => $name ) : ?>
								<li><a href="#<?php echo esc_attr( $key ); ?>" class="ocdi__gl-navigation-link  js-ocdi-nav-link"><?php echo esc_html( $name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
					<div clas="ocdi__gl-search">
						<input type="search" class="ocdi__gl-search-input  js-ocdi-gl-search" name="ocdi-gl-search" value="" placeholder="<?php esc_html_e( 'Search demos...', 'rey-core' ); ?>">
					</div>
				</div>
			<?php endif; ?>
			<div class="ocdi__gl-item-container  wp-clearfix  js-ocdi-gl-item-container">
				<?php foreach ( $predefined_themes as $index => $import_file ) : ?>
					<?php
						// Prepare import item display data.
						$img_src = isset( $import_file['import_preview_image_url'] ) ? $import_file['import_preview_image_url'] : '';
						// Default to the theme screenshot, if a custom preview image is not defined.
						if ( empty( $img_src ) ) {
							$theme = wp_get_theme();
							$img_src = $theme->get_screenshot();
						}

						$is_public = isset($import_file['visibility']) && $import_file['visibility']  === 'public';
					?>
					<div class="ocdi__gl-item js-ocdi-gl-item <?php echo !$is_public ? '--private':''; ?>" data-categories="<?php echo esc_attr( OCDI\Helpers::get_demo_import_item_categories( $import_file ) ); ?>" data-name="<?php echo esc_attr( strtolower( $import_file['import_file_name'] ) ); ?>">
						<div class="ocdi__gl-item-image-container">
							<?php if ( ! empty( $img_src ) ) : ?>
								<div class="ocdi__gl-item-imageLoader"></div>
								<img class="ocdi__gl-item-image" data-src="<?php echo esc_url( $img_src ) ?>">
							<?php else : ?>
								<div class="ocdi__gl-item-image  ocdi__gl-item-image--no-image"><?php esc_html_e( 'No preview image.', 'rey-core' ); ?></div>
							<?php endif; ?>
						</div>
						<div class="ocdi__gl-item-footer<?php echo ! empty( $import_file['preview_url'] ) ? '  ocdi__gl-item-footer--with-preview' : ''; ?>">
							<h4 class="ocdi__gl-item-title" title="<?php echo esc_attr( $import_file['import_file_name'] ); ?>"><?php echo esc_html( $import_file['import_file_name'] ); ?></h4>
							<button class="ocdi__gl-item-button  button  button-primary  js-ocdi-gl-import-data" value="<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Import', 'rey-core' ); ?></button>
							<?php if ( ! empty( $import_file['preview_url'] ) ) : ?>
								<a class="ocdi__gl-item-button  button" href="<?php echo esc_url( $import_file['preview_url'] ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'rey-core' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div id="js-ocdi-modal-content"></div>

	<?php endif; ?>

	<p class="ocdi__ajax-loader  js-ocdi-ajax-loader">
		<span class="spinner"></span> <?php esc_html_e( 'Importing content, please wait!', 'rey-core' ); ?>
	</p>

	<div class="ocdi__response  js-ocdi-ajax-response"></div>
</div>
