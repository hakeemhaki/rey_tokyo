<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// Show ACF menu in WP Dashboard
add_filter('acf/settings/show_admin', function(){
	return reycore__acf_get_field('acf_fields_panel', REY_CORE_THEME_NAME) !== false;
});

add_action('acf/init', function(){
	acf_update_setting('show_updates', false);
});

if(!function_exists('reycore_acf__is_exporting')):
	/**
	 * Check if is exporting
	 *
	 * @since 1.6.10
	 **/
	function reycore_acf__is_exporting()
	{
		if( isset($_REQUEST['page']) && $_REQUEST['page'] === 'acf-tools' && isset($_REQUEST['tool']) && $_REQUEST['tool'] === 'export' ){
			return true;
		}
		if( isset($_REQUEST['page']) && $_REQUEST['page'] === 'acf-tools' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'download' ){
			return true;
		}
		return false;
	}
endif;


if(!function_exists('reycore_acf__is_editing_group')):
	/**
	 * Check if editing group
	 *
	 * @since 1.6.10
	 **/
	function reycore_acf__is_editing_group()
	{
		return (get_post_type() === 'acf-field-group' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit') ||
			(isset($_REQUEST['page']) && $_REQUEST['page'] === 'acf-tools');
	}
endif;


if(!function_exists('reycore_acf__prevent_export_dynamic_field')):
	/**
	 * Check if editing group
	 *
	 * @since 1.7.3
	 **/
	function reycore_acf__prevent_export_dynamic_field() {
		return reycore_acf__is_exporting() || reycore_acf__is_editing_group();
	}
endif;



add_action('acf/init', function(){

	/**
	 * Adds settings page
	 */
	$opts = [
		'page_title'     => __( 'Settings', 'rey-core' ),
		'menu_title'	 => __( 'Settings', 'rey-core' ),
		'menu_slug' 	 => REY_CORE_THEME_NAME . '-settings',
		'capability'	 => 'manage_options',
		'post_id'        => REY_CORE_THEME_NAME,
		'update_button'	 => __('Save Options', 'rey-core'),
	];

	if( $dashboard_page_id = reycore__get_dashboard_page_id() ){
		$opts['parent_slug'] = $dashboard_page_id;
	}

	if( ! function_exists('acf_add_options_page') ){
		if( apply_filters('reycore/acf/can_show_lite_notice', true) ){
          add_action('admin_notices', function (){
              _e('<div class="notice notice-error is-dismissible"><p>You\'re using Advanced Custom Fields Lite but Rey Core already has the PRO version built-in. Please head over to Plugins and <strong>delete Advanced Custom Fields plugin</strong>.</p></div>', 'rey-core');
          });
        }
		return;
	}

	acf_add_options_page($opts);

	add_action('admin_notices', function (){

		$current = get_current_screen();

		if ( $current->id == 'rey-theme_page_rey-settings' ) {
			printf(
				__('<div class="notice notice-info is-dismissible"><p>To customize the theme, head over to <a href="%s" target="_blank">Customizer</a>. To learn more about these options, please visit <a href="%s" target="_blank">Rey\'s Documentation</a>.</p></div>', 'rey-core'),
				add_query_arg( [
					'url' => get_site_url()
				], admin_url( 'customize.php' ) ),
				'https://support.reytheme.com/kb/theme-settings-backend/'
			);
		}
	});

});


if(!function_exists('reycore__acf_redirect_url')):
	/**
	 * Redirect if enabled in post/page settings
	 *
	 * @since 1.0.0
	 */
	function reycore__acf_redirect_url(){

		if ( !is_admin() && reycore__acf_get_field('general_redirect') && reycore__acf_get_field('redirect_url') ) {
			wp_redirect( reycore__acf_get_field('redirect_url') );
			exit;
		}

	}
endif;
add_action('template_redirect', 'reycore__acf_redirect_url', 10);


if(!function_exists('reycore__acf_remove_titles')):
	/**
	 * Removes title if it's disabled in post/page options
	 *
	 * @since 1.0.0
	 **/
	function reycore__acf_remove_titles( $status )
	{
		if( reycore__acf_get_field('title_display') && reycore__acf_get_field('title_display') == 'hide' ) {
			$status = false;
		}

		return $status;
	}
endif;
add_filter('rey/add_titles', 'reycore__acf_remove_titles');


if(!function_exists('reycore__acf_remove_product_archive_titles')):
	/**
	 * Removes title if it's disabled in product archive's ACF settings
	 *
	 * @since 1.0.0
	 **/
	function reycore__acf_remove_product_archive_titles()
	{
		if( class_exists('WooCommerce') && (is_product_category() || is_shop()) && reycore__acf_get_field('title_display') && reycore__acf_get_field('title_display') == 'hide' ){
			// remove title
			add_filter( 'woocommerce_show_page_title', '__return_false', 10 );
			// remove breadcrumb too
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
			// remove description
			remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
			remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		}
	}
endif;
add_action('wp', 'reycore__acf_remove_product_archive_titles');


/**
 * Populate ACF header layouts choices
 *
 * @since 1.0.0
 */
add_filter('acf/prepare_field/name=header_layout_type', function ( $field ) {

	$styles = reycore__get_header_styles();

	if( isset($field['choices']) && is_array($styles) ) {
		$field['choices'] = $field['choices'] + $styles;
	}

	$field['placeholder'] = esc_html__('Inherit option (from Customizer > Header > General)', 'rey-core');
	$field['instructions'] = reycore__header_footer_layout_desc( 'header', true );

    return $field;
});


/**
 * Populate ACF header layouts choices
 *
 * @since 1.0.0
 */
add_filter('acf/prepare_field/name=footer_layout_type', function ( $field ) {

	$styles = reycore__get_footer_styles();

	if( isset($field['choices']) && is_array($styles) ) {
		$field['choices'] = $field['choices'] + $styles;
	}

	$field['instructions'] = reycore__header_footer_layout_desc( 'footer', true );
    return $field;
});

/**
 * Populate ACF global section types
 *
 * @since 1.0.0
 */
add_filter( 'acf/prepare_field/name=gs_type', function ( $field ) {
	if( class_exists('ReyCore_GlobalSections') && isset($field['choices']) ) {
		$field['choices'] = ReyCore_GlobalSections::get_global_section_types();
		if( isset($_REQUEST['gs_type']) && array_key_exists($_REQUEST['gs_type'], $field['choices'] ) ){
			$field['value'] = $_REQUEST['gs_type'];
		}
	}
	return $field;
});

if(!function_exists('reycore_acf__populate_sticky_lists')):
	/**
	 * Populate Sticky global sections options in acf lists
	*
	* @since 1.6.x
	**/
	function reycore_acf__populate_sticky_lists($field)
	{
		if( class_exists('ReyCore_GlobalSections') && isset($field['choices']) ) {
			if( ($gs = ReyCore_GlobalSections::get_global_sections(['generic','header'])) && is_array($gs) ){
				$field['choices'] = $field['choices'] + $gs + ['none' => esc_html__('- Disabled -', 'rey-core')];
			}
		}
		return $field;
	}
	add_filter('acf/prepare_field/name=top_sticky_gs', 'reycore_acf__populate_sticky_lists');
	add_filter('acf/prepare_field/name=bottom_sticky_gs', 'reycore_acf__populate_sticky_lists');
endif;


if(!function_exists('reycore_acf__rename_placeholder')):
/**
 * Renames placeholder for some options
 *
 * @since 1.3.5
 **/
function reycore_acf__rename_placeholder( $field )
{
	$field['placeholder'] = esc_html__('Inherit', 'rey-core');
	return $field;
}
endif;
add_filter('acf/prepare_field/name=header_layout_type', 'reycore_acf__rename_placeholder');
add_filter('acf/prepare_field/name=header_position', 'reycore_acf__rename_placeholder');
add_filter('acf/prepare_field/name=header_fixed_overlap', 'reycore_acf__rename_placeholder');
add_filter('acf/prepare_field/name=footer_layout_type', 'reycore_acf__rename_placeholder');
add_filter('acf/prepare_field/name=title_display', 'reycore_acf__rename_placeholder');
add_filter('acf/prepare_field/name=page_cover', 'reycore_acf__rename_placeholder');


/**
 * Populate ACF adobe fonts list in theme settings
 *
 * @since 1.0.0
 */
add_action('acf/render_field', function ( $field ) {

	if( function_exists('rey__maybe_disable_obj_cache') ){
		rey__maybe_disable_obj_cache();
	}

	if( $field['_name'] == 'adobe_fonts_project_id' && $adobe_fonts = get_transient( 'rey_adobe_fonts' ) ){
		foreach( $adobe_fonts as $key => $font ) {
			printf( __('<p><strong>%s</strong> font. Weights <strong>%s</strong>. Use <em>"font-family: %s;"</em> in CSS. </p>', 'rey-core'),
				$font['family'],
				implode(', ', $font['font_variants']),
				$font['font_name']
			);
		}
	}
});


/**
 * Populate ACF google fonts list in theme settings
 *
 * @since 1.0.0
 */
add_filter('acf/prepare_field/name=preload_google_fonts', function ( $field ) {

	$fonts = [];

	if( is_callable('Kirki_Fonts::get_google_fonts') ){
		$fonts = Kirki_Fonts::get_google_fonts();
		$fonts = array_keys($fonts);
		$fonts = array_combine($fonts, $fonts);
	}

	if( isset($field['sub_fields'][0]['choices']) ) {
		$field['sub_fields'][0]['choices'] = ['' => esc_html('- Select -', 'rey-core')] + $fonts;
	}

    return $field;
});


/**
 * Register Header/Footer List field
 *
 * @since 1.0.0
 */
add_action('acf/include_field_types', function( $version = false ){

	if( !is_admin() ){
		return;
	}

	if( is_customize_preview() ){
		return;
	}

	// support empty $version
	if ( ! $version ) {
		$version = 5;
	}

	include_once REY_CORE_DIR . '/inc/acf/acf-custom-fields/acf-extended-color-picker-v5.php';
	include_once REY_CORE_DIR . '/inc/acf/acf-custom-fields/acf-global-sections.php';
});

/**
 * FIlter ACF RGBA color field's assets URL
 */
add_filter('acf/extended_color_picker/url', function(){
	return REY_CORE_URI . 'inc/acf/acf-custom-fields/';
}, 100);


/**
 * Set description for Global Sections - Type selector.
 *
 * @since 1.0.0
 **/
function reycore__get_gs_type_info()
{

	$header_link_query['autofocus[control]'] = 'header_layout_type';
	$header_link = add_query_arg( $header_link_query, admin_url( 'customize.php' ) );

	$generic_link_query['autofocus[control]'] = 'global_sections';
	$generic_link = add_query_arg( $generic_link_query, admin_url( 'customize.php' ) );

	return sprintf(
		__('Select type of global section.<br> <strong>Headers & Footers</strong> need to be manually assigned in Customizer\'s <a href="%s" target="_blank" title="Will open in a new tab">%s</a> panel, or for specific pages - into the page (or taxonomy) custom options. <br> <strong>Generic sections</strong> can be assigned to different page positions, either in Customizer\'s <a href="%s" target="_blank">%s</a> (for site-wide usage), or for specific pages - into the page (or taxonomy) custom options. <a href="%s" target="_blank">Learn More</a>.', 'rey-core'),
		esc_url( $header_link ),
		esc_html__('Header options', 'rey-core'),
		esc_url( $generic_link ),
		esc_html__('Global Sections', 'rey-core'),
		'https://support.reytheme.com/kbtopic/global-sections/'
	);
}

/**
 * Populate ACF's description Global Sections - Type option
 *
 * @since 1.0.0
 */
add_filter('acf/prepare_field/name=gs_type', function ( $field ) {
	if( isset($field['instructions']) ) {
		$field['instructions'] = reycore__get_gs_type_info();
	}
    return $field;
});


/**
 * Populate ACF's Remove Global Sections, if there are
 * assignments in Customizer's > Global Sections
 *
 * @since 1.0.0
 */
add_filter('acf/prepare_field/name=remove_global_sections', function ( $field ) {

	$sections = get_theme_mod('global_sections', false);

	// Don't bother to display field
	// if it's empty
	if( empty($sections) ) {
		return false;
	}

	if( isset($field['choices']) && is_array($sections) && !empty($sections) ) {
		foreach ($sections as $k => $s) {
			// dumb solution to keep id
			$field['choices'][$s['id'] . '__' . $s['hook']] = get_the_title($s['id']) . ' ( ' . $s['hook'] . ' )';
		}
	}
    return $field;
});


/**
 * Populate ACF's Global Sections list
 *
 * @since 1.0.0
 */
add_filter('acf/prepare_field/name=global_sections_id', function ( $field ) {

	$generic_sections_items = ReyCore_GlobalSections::get_global_sections('generic');

	if( isset($field['choices']) && is_array($generic_sections_items) && !empty($generic_sections_items) ) {
		$field['choices'] = $field['choices'] + $generic_sections_items;
	}

    return $field;
});


/**
 * Populate ACF page cover choices
 *
 * @since 1.0.0
 */
add_filter('acf/prepare_field/name=page_cover', function ( $field ) {

	$styles = ReyCore_GlobalSections::get_global_sections('cover', [
		'no'  => esc_attr__( 'Disabled', 'rey-core' )
	]);

	if( isset($field['choices']) && is_array($styles) ) {
		$field['choices'] = $field['choices'] + $styles;
	}

    return $field;
});


/**
 * Populate ACF's supported post types for Singular Settings
 *
 * @since 1.6.6
 */
if(!function_exists('reycore_acf__default_supported_singular_post_types')):
	function reycore_acf__default_supported_singular_post_types(){
		return [
			'post_type' => [ 'post', 'page', 'product', 'rey-templates' ],
			'taxonomy' => [ 'category', 'product_cat', 'product_tag' ],
		];
	}
endif;

add_filter('acf/prepare_field/name=singular_settings_support_roles', function ( $field ) {

	if( function_exists('get_editable_roles') ){

		$roles = get_editable_roles();

		$field['choices']['all'] = esc_html__('All', 'rey-core');

		foreach ($roles as $key => $value) {
			$field['choices'][$key] = $value['name'];
		}

	}

    return $field;
});

add_filter('acf/prepare_field/name=singular_settings_support', function ( $field ) {
	if( $new_choices = reycore__get_post_types_list() ){
		$field['choices'] = $new_choices;
	}
    return $field;
});

add_filter('acf/load_field/name=singular_settings_support', function ( $field ) {
	$default_post_types = reycore_acf__default_supported_singular_post_types();
	$field['default_value'] = $default_post_types['post_type'];
    return $field;
});

add_filter('acf/update_value/name=singular_settings_support', function ( $value, $post_id, $field ) {
	$default_post_types = reycore_acf__default_supported_singular_post_types();
    return array_unique(array_merge($value, $default_post_types['post_type']));
}, 10, 3);

// Adds singular group to other post type
add_filter('acf/load_field_group', function($field_group){

	if( reycore_acf__prevent_export_dynamic_field() ){
		return $field_group;
	}

	if( ! (isset($field_group['key']) && $field_group['key'] === 'group_5c4ad0bd35b33') ){
		return $field_group;
	}

	if( ($post_types = reycore__acf_get_field('singular_settings_support', REY_CORE_THEME_NAME)) && is_array($post_types) ){

		$default_post_types = reycore_acf__default_supported_singular_post_types();

		foreach ($post_types as $key => $value) {

			if( in_array($value, $default_post_types['post_type'], true) ){
				continue;
			}

			$field_group['location'][] = [
				[
					'param' => 'post_type',
					'operator' => '==',
					'value' => $value,
				]
			];
		}
	}

	if( function_exists('wc_get_attribute_taxonomies') ):

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach ( $attribute_taxonomies as $attribute ) {

			$is_public = (bool) $attribute->attribute_public;

			if( !$is_public ){
				continue;
			}

			$field_group['location'][] = [
				[
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => wc_attribute_taxonomy_name($attribute->attribute_name),
				]
			];
		}
	endif;

	return $field_group;
});


add_filter('acf/load_field_groups', function($field_groups){

	if( reycore_acf__prevent_export_dynamic_field() ){
		return $field_groups;
	}

	foreach ($field_groups as $key => $field_group) {
		if( ! (isset($field_group['key']) && $field_group['key'] === 'group_5c4ad0bd35b33') ){
			continue;
		}

		if( !($role = reycore__acf_get_field('singular_settings_support_roles', REY_CORE_THEME_NAME)) ){
			continue;
		}

		if( $role === 'all' ){
			continue;
		}

		if( ! current_user_can($role) ){
			$field_groups[$key]['active'] = false;
		}
	}

	return $field_groups;
}, 20);


/**
 * Populate ACF's Global Sections list in Product page
 *
 * @since 1.0.0
 */
function reycore_acf__product_page_options_global_lists( $field ) {

	$generic_sections_items = ReyCore_GlobalSections::get_global_sections('generic', [
		'none'  => esc_attr__( 'Disabled', 'rey-core' )
	]);

	if( isset($field['choices']) && is_array($generic_sections_items) && !empty($generic_sections_items) ) {
		$field['choices'] = $field['choices'] + $generic_sections_items;
	}

    return $field;
}
add_filter('acf/prepare_field/name=product_content_after_summary', 'reycore_acf__product_page_options_global_lists');
add_filter('acf/prepare_field/name=product_content_after_content', 'reycore_acf__product_page_options_global_lists');


if(!function_exists('reycore__acf_to_elementor')):
	/**
	 * Push ACF fields to elementor meta
	 *
	 * @since 1.0.0
	 */
	function reycore__acf_to_elementor( $post_id ) {

		if( !class_exists('\Elementor\Core\Settings\Page\Manager') ){
			return;
		}

		$elementor_meta = get_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

		if ( ! $elementor_meta ) {
			$elementor_meta = [];
		}

		$post_type = get_post_type($post_id);

		// Title Display
		$elementor_meta['hide_title'] = reycore__acf_get_field('title_display', $post_id, '')  == 'hide' ? 'yes' : '';

		if ( class_exists('ReyCore_GlobalSections') && $post_type === ReyCore_GlobalSections::POST_TYPE ) {
			$gs_type = sanitize_text_field(reycore__acf_get_field('gs_type', $post_id, '') );

			if( $gs_type === '' ){
				$gs_type = 'generic';
			}

			$elementor_meta['gs_type'] =  $gs_type;
		}

		// Body Class
		$elementor_meta['rey_body_class'] = sanitize_text_field( reycore__acf_get_field('rey_body_class', $post_id, '') );

		update_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, $elementor_meta );
	}
endif;
add_action('acf/save_post', 'reycore__acf_to_elementor', 20);


if(!function_exists('reycore__acf_image_to_elementor_image')):
	/**
	 * ACF Image ID to ELementor Media control ID & URL
	 *
	 * @since 1.0.0
	 **/
	function reycore__acf_image_to_elementor_image( $image_id )
	{
		$url = '';

		if( $image_id ){
			$url_array = wp_get_attachment_image_src( absint( $image_id ), 'full' );
			if( isset($url_array[0]) ){
				$url = $url_array[0];
			}
		}

		return [
			'url' => $url,
			'id' => $image_id ? absint( $image_id ) : ''
		];
	}
endif;


if(!function_exists('reycore__acf_to_customizer')):
	/**
	 * Push ACF fields to Customizer's options
	 *
	 * @since 1.0.0
	 */
	function reycore__acf_to_customizer( $post_id )
	{
		// Shop Page
		if( class_exists('WooCommerce') && wc_get_page_id('shop') == $post_id ){
			set_theme_mod('cover__shop_page', reycore__acf_get_field('page_cover', $post_id, '') );
		}
		// Blog page
		if( get_option( 'page_for_posts' ) == $post_id ){
			set_theme_mod('cover__blog_home', reycore__acf_get_field('page_cover', $post_id, '') );
		}
	}
endif;
add_action('acf/save_post', 'reycore__acf_to_customizer', 20);


/**
 * Fix metabox position in product pages
 */
add_filter('acf/input/meta_box_priority', function($priority, $field_group){

	// adjust Singular settings position in page
	// On product page, needs to be super low
	if( get_post_type() === 'product' && isset($field_group['key']) ) {
		if( $field_group['key'] === 'group_5c4ad0bd35b33' || $field_group['key'] === 'group_5d4ff536a2684' ){
			$priority = 'low';
		}
	}

	return $priority;
}, 10, 2);


if(!function_exists('reycore__acf_responsive_handlers')):
	/**
	 * Add responsive handlers
	 *
	 * @since 1.3.5
	 **/
	function reycore__acf_responsive_handlers()
	{ ?>
		<script type="text/html" id="tmpl-rey-acf-responsive-handler">
			<div class="rey-acf-responsiveHandlers">
				<span data-breakpoint="desktop"></span>
				<span data-breakpoint="tablet"></span>
				<span data-breakpoint="mobile"></span>
			</div>
		</script>
		<?php
	}
endif;
add_action( 'admin_footer', 'reycore__acf_responsive_handlers' );



if(!function_exists('reycore_acf__page_overrides_admin_menu')):
	function reycore_acf__page_overrides_admin_menu( $nodes ) {

		$notice_item = [];

		$overrides = [
			[
				'title' => esc_html__('Header Layout:', 'rey-core'),
				'page' => reycore__acf_get_field('header_layout_type'),
				'default' => get_theme_mod('header_layout_type'),
			],
			[
				'title' => esc_html__('Header Position:', 'rey-core'),
				'page' => reycore__acf_get_field('header_position'),
				'default' => get_theme_mod('header_position'),
			],
			[
				'title' => esc_html__('Header Color:', 'rey-core'),
				'page' => reycore__acf_get_field('header_text_color'),
				'default' => get_theme_mod('header_text_color'),
			],
			[
				'title' => esc_html__('Page Cover:', 'rey-core'),
				'page' => reycore__acf_get_field('page_cover'),
				'default' => get_theme_mod('page_cover'),
			],
		];

		foreach ($overrides as $key => $value) {
			if( $value['page'] == '' || $value['page'] === $value['default']  ){
				continue;
			}
			$notice_item[] = sprintf('<li>%s %s (%s %s)</li>', $value['title'], $value['page'], esc_html__('default:', 'rey-core'), $value['default']);
		}

		if( !is_admin() && !empty($notice_item) ):
			$nodes['notices'] = [
				'title'  => esc_html__('Page overrides:', 'rey-core'),
				'href'  => '',
				'top'  => true,
				'nodes'  => [
					[
						'title'  => sprintf('<div class="rey-abQuickMenu-notices"><ul>%s</ul></div>', implode('', $notice_item)),
						'href'  => '',
					]
				],
			];
		endif;

		return $nodes;
	}
endif;
// add_filter( 'reycore/admin_bar_menu/nodes', 'reycore_acf__page_overrides_admin_menu' );

/**
 * Add exclude assets option
 * @since 2.0.0
 */
add_filter('acf/prepare_field/name=perf__css_exclude', function ( $field ) {

	$styles = function_exists('reyCoreAssets') ? reyCoreAssets()->get_excludes_choices( false ) : [];

	if( isset($field['choices']) && is_array($styles) ) {
		$field['choices'] = $field['choices'] + $styles;
	}

    return $field;
});
