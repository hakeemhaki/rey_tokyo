<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if( !class_exists('ReyAjaxFilters_MigrateWcapf') ):

class ReyAjaxFilters_MigrateWcapf
{
	private static $_instance = null;

	private function __construct()
	{
		if( !class_exists('WCAPF') ){
			return;
		}

		add_action( 'admin_notices', [$this, 'show_notices'] );
		add_action( 'wp_ajax_rey_ajaxfilters_migrate', [$this, 'migrate_wcapf'] );
		add_action( 'wp_ajax_rey_ajaxfilters_deactivate_wcapf', [$this, 'deactivate_wcapf'] );
	}

	/**
	 * Display notices
	 *
	 * @since 1.0.0
	 */
	function show_notices()
	{
		echo '<div class="notice notice-error">';
		echo __( '<p>Since Rey 1.5.0 update, <strong>"WC Ajax Filters" plugin is deprecated</strong>. Proceed to migrate the old widgets into the new ones and deactivate this plugin? It will only take a couple of moments.</p> ', 'rey-core' );
		echo sprintf('<p><a href="#" class="js-reyajaxfilters-migrate button button-primary">%s</a></p>', esc_html__('Yes, proceed!', 'rey-core') );
		echo '</div>';
	}

	function migrate_wcapf(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		$errors = [];

		$wcapf_widgets = [
			'wcapf-category-filter'  => 'reyajfilter-category-filter',
			'wcapf-attribute-filter' => 'reyajfilter-attribute-filter',
			'wcapf-price-filter'     => 'reyajfilter-price-filter',
			'wcapf-active-filters'   => 'reyajfilter-active-filters',
		];

		$rey_sidebars = [
			'shop-sidebar',
			'filters-sidebar',
			'filters-top-sidebar'
		];

		$sidebars_widgets = wp_get_sidebars_widgets();

		foreach( $sidebars_widgets as $sidebar_name => $widgets_instances_in_sidebar ){

			if( !in_array($sidebar_name, $rey_sidebars) ){
				continue;
			}

			if( empty($widgets_instances_in_sidebar) ){
				continue;
			}

			foreach( $wcapf_widgets as $wcapf_widget => $rey_widget ){

				$this_wcapf_widget_instances = get_option( 'widget_' . $wcapf_widget );

				// special old option
				if ( $wcapf_widget === 'wcapf-attribute-filter' ) {
					foreach( $this_wcapf_widget_instances as $wid => $wcapf_attribute_filter_instance ){
						if( isset($wcapf_attribute_filter_instance['rey_display_type']) &&
							($wcapf_attribute_filter_instance['rey_display_type'] === 'color' || $wcapf_attribute_filter_instance['rey_display_type'] === 'button')
						){
							$this_wcapf_widget_instances[$wid]['display_type'] = $wcapf_attribute_filter_instance['rey_display_type'];
						}
					}
				}

				foreach( $this_wcapf_widget_instances as $id => $settings ){

					$instance_key = array_search( $wcapf_widget . '-' . $id, $widgets_instances_in_sidebar );

					if ($instance_key === false) {
						continue;
					}

					$sidebars_widgets[ $sidebar_name ][] = $rey_widget . '-' . $id;
				}

				update_option('sidebars_widgets', $sidebars_widgets);
				update_option('widget_' . $rey_widget, $this_wcapf_widget_instances);

			}
		}

		wp_send_json_success();
	}

	public function deactivate_wcapf(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		deactivate_plugins( 'wc-ajax-product-filter/wcapf.php');
		unset( $_GET['activate'], $_GET['plugin_status'], $_GET['activate-multi'] );
		wp_send_json_success();
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyAjaxFilters_MigrateWcapf
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}
endif;

ReyAjaxFilters_MigrateWcapf::getInstance();
