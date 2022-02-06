<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCoreElementor_TemplateLibrary') ):

class ReyCoreElementor_TemplateLibrary
{
	const LIBRARY_DB_DATA = 'rey_library_data';
	const LIBRARY_DB_INSTALLED = 'rey_library_installed';

	public function __construct()
	{
		$this->init_hooks();
	}

	/**
	 * Initialize Hooks
	 *
	 * @since 1.0.0
	 */
	public function init_hooks()
	{
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_scripts'] );
		add_action( 'elementor/preview/enqueue_styles', [$this, 'enqueue_preview_styles']);
		add_action( 'wp_ajax_add_remove_favorites', [ $this, 'add_remove_favorites'] );
		add_action( 'elementor/init', [$this, 'register_templates_source'] );
		add_action( 'elementor/editor/footer', [$this, 'html_templates'] );
	}

	/**
	 * Load Editor JS
	 *
	 * @since 1.0.0
	 */
	public function editor_scripts() {
		wp_enqueue_style( 'reycore-template-library-style', REY_CORE_URI . 'assets/css/template-library.css', [], REY_CORE_VERSION );
		wp_enqueue_script( 'reycore-template-library-script', REY_CORE_URI . 'assets/js/elementor-editor/template-library.js', ['wp-util', 'masonry', 'imagesloaded'], REY_CORE_VERSION, true );
		wp_localize_script('reycore-template-library-script', 'reyTemplatesLibraryParams', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'rey_atf_nonce' ),
			'logo' => reycore__get_svg_icon(['id'=>'rey-icon-logo']),
			'internal_data' => $this->get_internal_data(),
			'api_url' => $this->get_api_url(),
			'per_load' => 12,
			'strings' => [
				'searching' => esc_html__('Searching for', 'rey-core'),
				'browse' => esc_html__('Browse Library', 'rey-core'),
				'an_error_occurred' => esc_html__( 'Error(s) occurred', 'rey-core' ),
				'downloading' => esc_html__( 'DOWNLOADING & IMPORTING..', 'rey-core' ),
			],
			'req_strings' => [
				'01' => __('<strong>Header</strong> is not included because it needs manual assigment.', 'rey-core'),
				'02' => __('<strong>Footer</strong> is not included because it needs manual assigment.', 'rey-core'),
				'03' => __('<strong>Header & Footer</strong> are not included because they need manual assigment.', 'rey-core'),
				'04' => __('<strong>Products</strong> are not included.', 'rey-core'),
				'05' => __('<strong>Categories</strong> are not included.', 'rey-core'),
				'06' => __('<strong>Menu navigation</strong> is not included. Must be manually selected.', 'rey-core'),
				'07' => sprintf(
					__('<strong>Contact Form</strong> is not included. Must be created separately in <a href="%s" target="_blank">CF7 plugin</a>.', 'rey-core'),
					admin_url('admin.php?page=wpcf7')
				),
				'08' => sprintf(
					__('<strong>Mailchimp form</strong> is not included. Must be created separately in <a href="%s" target="_blank">MailChimp plugin</a>.', 'rey-core'),
					admin_url('admin.php?page=mailchimp-for-wp')
				),
				'09' => sprintf(
					__('<strong>Instagram images</strong> are not included. You need to configure <a href="%s" target="_blank">Instagram plugin</a> manually.', 'rey-core'),
					admin_url('options-general.php?page=wpzoom-instagram-widget')
				),
				'10' => __('<strong>Blog posts</strong> are not included.', 'rey-core'),
				'11' => sprintf(
					__('<strong>Maps / Store locator plugin</strong> is not included. It\'s actually based on <strong>WP Store Locator</strong> plugin. You need to <a href="%s" target="_blank">access plugins</a> and install/activate and configure manually.', 'rey-core'),
					admin_url('admin.php?page=rey-install-required-plugins')
				),
			],
		]);
	}

	/**
	 * Enqueue ReyCore's Elementor Preview CSS
	 */
	public function enqueue_preview_styles() {
		wp_enqueue_style( 'reycore-template-library-button', REY_CORE_URI . 'assets/css/template-library-button.css', [], REY_CORE_VERSION );
	}

	function get_api_url(){
		if( class_exists('ReyTheme_API') ){
			return esc_url( ReyTheme_API::API_FILES_URL );
		}
		return false;
	}

	function register_templates_source(){
		require_once REY_CORE_DIR . 'inc/elementor/template-library/source.php';
		\Elementor\Plugin::instance()->templates_manager->register_source( 'ReyCoreElementor_Source' );
	}

	function get_internal_data(){

		$data = get_site_option(self::LIBRARY_DB_DATA, [
			'favorites'=> [],
			'installed'=> []
		]);

		return function_exists('rey__clean') ? array_map('rey__clean', $data) : $data;
	}

	function add_remove_favorites(){

		if ( ! check_ajax_referer( 'rey_atf_nonce', 'security', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		if ( ! current_user_can('administrator') ) {
			wp_send_json_error( esc_html__('Operation not allowed!', 'rey-core') );
		}

		if( function_exists('rey__maybe_disable_obj_cache') ){
			rey__maybe_disable_obj_cache();
		}

		if( isset($_POST['slug']) && $slug = sanitize_text_field($_POST['slug']) ){

			$data = $this->get_internal_data();

			$favorites = $data['favorites'];

			if( in_array($slug, $favorites) ) {
				$favorites = array_values(array_filter($favorites, function($v) use ($slug) {
					return $v !== $slug;
				}));
			}
			else {
				$favorites[] = $slug;
			}

			$data['favorites'] = array_unique( $favorites );

			update_site_option(self::LIBRARY_DB_DATA, (array) $data);

			wp_send_json_success( $data['favorites'] );
		}

		wp_send_json_error( esc_html__('Something went wrong', 'rey-core') );
	}

	/**
	 * Templates Modal Markup
	 *
	 * @since 1.0.0
	 */
	function html_templates()
	{ ?>
		<script type="text/html" id="tmpl-elementor-rey-templates-modal">
			<div class="rey-tpModal" data-columns="4">

				<div class="tpModal-headerMain">
					<div class="tpModal-headerLogo --active">
						<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-logo']) ?>
					</div>
					<nav class="tpModal-headerNav">
						<!-- <h3><?php esc_html_e('SELECT CATEGORY:', 'rey-core') ?></h3> -->
					</nav>
					<div class="tpModal-headerTools">
						<div class="headerTools-view tpModal-list">
							<div class="tpModal-listTitle">
								<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-grid']); ?>
								<span><?php esc_html_e('SWITCH VIEW', 'rey-core') ?></span>
							</div>
							<select class="js-headerBar-changeView">
								<option value="2">2 per row</option>
								<option value="3">3 per row</option>
								<option value="4" selected>4 per row</option>
								<option value="5">5 per row</option>
								<option value="6">6 per row</option>
								<option value="7">7 per row</option>
							</select>
						</div>
						<a class="headerTools-installed" data-title="<?php esc_attr_e('Downloaded templates', 'rey-core') ?>">
							<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-downloaded']); ?>
							<span><?php esc_html_e('INSTALLED', 'rey-core') ?></span>
						</a>
						<a class="headerTools-favorites" data-count="0">
							<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-favorites']); ?>
							<span><?php esc_html_e('FAVORITES', 'rey-core') ?></span>
						</a>
						<a href="#" class="tpModal-headerSync" data-template-tooltip="<?php esc_html_e('SYNC LIBRARY', 'rey-core') ?>">
							<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-sync']); ?>
							<span class="elementor-screen-only"><?php esc_html_e('Sync Library', 'rey-core') ?></span>
						</a>
						<a href="https://support.reytheme.com/kb/template-library-faqs/" target="_blank" class="tpModal-headerHelp" data-template-tooltip="<?php esc_html_e('NEED HELP?', 'rey-core') ?>">
							<?php echo reycore__get_svg_icon(['id'=>'rey-icon-help']); ?>
							<span class="elementor-screen-only"><?php esc_html_e('Click to read documentation.', 'rey-core') ?></span>
						</a>
					</div>
					<a href="#" class="tpModal-headerClose js-tpModal-headerClose">
						<i class="eicon-close" aria-hidden="true" title="<?php esc_attr_e('Close', 'rey-core') ?>"></i>
						<span class="elementor-screen-only"><?php esc_html_e('Close', 'rey-core') ?></span>
					</a>
				</div>

				<div class="tpModal-headerBar">
					<h2 class="headerBar-title"><?php esc_html_e('Browse Library', 'rey-core') ?></h2>
					<div class="headerBar-sort tpModal-list">
						<div class="tpModal-listTitle">
							<span>SORT BY </span>
							<span class="headerBar-sortWhich">Newest first</span>
							<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']) ?>
						</div>
						<select class="js-headerBar-sort">
							<option value="newest">Newest first</option>
							<option value="month">Popular this month</option>
							<option value="alltime">Popular all time</option>
						</select>
					</div>
					<div class="headerBar-search">
						<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-search']); ?>
						<input type="search" placeholder="Type to search the library .." />
					</div>
				</div>

				<div class="tpModal-content js-tpModal-content">

				</div>

				<div class="js-tpModal-loading tpModal-loading --visible"><div class="tpModal-loadingLine"></div></div>
			</div>
		</script>

		<script type="text/html" id="tmpl-elementor-rey-templates-modal-itemholder">

			<div class="tpModal-contentInner">
				<div class="tpModal-itemLoader">
					<div class="tpModal-loadingLine"></div>
				</div>
			</div>

		</script>

		<script type="text/html" id="tmpl-elementor-rey-templates-modal-item">


				<# Object.keys(data.items).forEach(function(item, i) { #>
					<div class="tpModal-item" data-keywords="{{{data.items[item].k}}}">
						<div class="tpModal-itemInner">
							<a href="{{{data.items[item].url}}}" target="_blank" class="tpModal-itemThumbnail">
								<img src="{{{data.items[item].preview_img}}}" alt="{{{data.items[item].name}}}" />
								<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-external-link']); ?>
							</a>
							<#
								var isInstalled = typeof data.internal_data['installed'] !== 'undefined' && data.internal_data['installed'].indexOf(item) !== -1 ? '--active' : '';
								var installText = JSON.stringify({
									'switcher_class': '--active',
									'text': '<?php esc_html_e('DOWNLOAD & IMPORT TEMPLATE', 'rey-core') ?>',
									'active_text': '<?php esc_html_e('INSERT TEMPLATE', 'rey-core') ?>'
								});
							#>
							<a href="#" class="tpModal-itemAction tpModal-itemInsert {{{isInstalled}}}" data-slug="{{{item}}}" data-sku="{{{data.items[item].sku}}}" data-template-tooltip='{{{installText}}}'>
								<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-downloaded']); ?>
							</a>
							<#
								var isActive = typeof data.internal_data['favorites'] !== 'undefined' && data.internal_data['favorites'].indexOf(item) !== -1 ? '--active' : '';
								var favText = JSON.stringify({
									'switcher_class': '--active',
									'text': '<?php esc_html_e('ADD TO FAVORITES', 'rey-core') ?>',
									'active_text': '<?php esc_html_e('REMOVE FROM FAVORITES', 'rey-core') ?>'
								});
							#>
							<a href="#" class="tpModal-itemAction tpModal-itemFav {{{isActive}}}" data-slug="{{{item}}}" data-template-tooltip='{{{favText}}}'>
								<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-heart']); ?>
								<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-heart-filled']); ?>
							</a>
							<h4 class="tpModal-itemName"><span>{{data.items[item].name}}</span><span class="tpModal-itemSku">{{data.items[item].sku}}</span> </h4>
						</div>
					</div>
				<# }); #>


		</script>

		<script type="text/html" id="tmpl-elementor-rey-templates-modal-nav">
			<ul class="headerNav-list">
			<# Object.keys(data).forEach(function(nav, index) { #>
			<# var hasSubmenus = typeof data[nav] === 'object' && Object.keys(data[nav]).length; #>
				<li class="__cat-{{{nav}}}">
					<a href="#" class="" data-category="{{{nav}}}">
						<span>{{nav}}</span>
						<# if( hasSubmenus ){ #>
							<?php echo reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']) ?>
						<# } #>
					</a>
					<# if( hasSubmenus ){ #>
						<div class="sub-categories">
							<# Object.keys(data[nav]).forEach(function(subNav, subNavIndex) { #>
								<a href="#" data-category="{{{subNav}}}">{{data[nav][subNav]}}</a>
							<# }); #>
						</div>
					<# } #>
				</li>
			<# }); #>
			</ul>
		</script>

		<script type="text/html" id="tmpl-elementor-rey-templates-modal-empty">
			<div class="tpModal-emptyContent">
				<p><?php esc_html_e('Empty list.', 'rey-core') ?></p>
			</div>
		</script>
		<?php
	}

}

new ReyCoreElementor_TemplateLibrary;

endif;
