<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class ReyCoreElementor_Widget_Assets
{
	private static $_instance = null;
	private $reycore_elementor = null;
	private $scripts = [];
	private $styles = [];
	private $added_elements = [];

	const PREFIX = 'reycore-';

	private function __construct()
	{
		$this->reycore_elementor = reyCoreElementor();

		add_action('init', [$this, 'get_scripts_and_styles']);

		add_action( 'reycore/assets/register_scripts', [$this, 'register_widgets_assets']);

		add_action( 'elementor/element/before_parse_css', [$this, 'force_newly_added_elements_into_header'], 0, 2 );
		add_action( 'elementor/element/parse_css', [$this, 'add_section_custom_css_to_post_file'], 10, 2 );

		add_action( 'reycore/elementor/global_section/ajax_load', [$this, 'load_ajax_assets'], 10, 2 );
	}

	/**
	 * Get all script.js and style.css files
	 *
	 * @since 1.0.0
	 */
	public function get_scripts_and_styles(){

		$disabled_elements = class_exists('ReyCore_WidgetsManager') ? ReyCore_WidgetsManager::get_disabled_elements() : [];

		foreach ( $this->reycore_elementor->widgets as $widget ) {

			// don't load disabled elements
			if( in_array(self::PREFIX . $widget, $disabled_elements, true) ){
				continue;
			}

			$assets_folder = trailingslashit('assets');

			$dir_path = reycore__clean( trailingslashit($this->reycore_elementor->widgets_dir . $widget) . $assets_folder );
			$uri = reycore__clean( trailingslashit(REY_CORE_URI . $this->reycore_elementor->widgets_folder . $widget) . $assets_folder );

			// script
			if( is_readable($dir_path . 'script.js') ){
				$this->scripts[$widget] = $uri . 'script.js';
			}

			if( is_readable($dir_path . 'style.css') ){
				$suffix = '';
				if( is_readable($dir_path . 'style-rtl.css') && is_rtl() ) {
					$suffix = '-rtl';
				}
				$this->styles[ $widget ]['uri'] = $uri . 'style'.$suffix.'.css';
				$this->styles[ $widget ]['dir'] = $dir_path . 'style'.$suffix.'.css';
				$this->styles[ $widget ]['widget_name'] = "reycore-widget-{$widget}-styles";
			}
		}
	}


	/**
	 * Register elements widgets assets
	 *
	 * @since 2.0.0
	 */
	public function register_widgets_assets(){

		$styles = [];

		foreach ( $this->styles as $widget => $style ) {
			$styles[ $style['widget_name'] ] = [
				'src'     => $style['uri'],
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			];
		}

		reyCoreAssets()->register_asset('styles', $styles);

		$scripts = [];

		foreach ( $this->scripts as $widget => $script ) {
			$scripts[ "reycore-widget-{$widget}-scripts" ] = [
				'src'     => $script,
				'deps'    => ['elementor-frontend', 'reycore-elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];
		}

		reyCoreAssets()->register_asset('scripts', $scripts);
	}

	/**
	 * Force newly added elements to insert into header
	 * after page was saved in Elementor.
	 *
	 * @since 2.0.0
	 */
	public function force_newly_added_elements_into_header( $post_css, $element ){

		if ( $post_css instanceof Elementor\Core\DynamicTags\Dynamic_CSS ) {
			return;
		}

		$el_name = $element->get_unique_name();

		// check if widget is used in this particular post.
		if( strpos($el_name, self::PREFIX) === false ){
			return;
		}

		$el_name_short = str_replace(self::PREFIX, '', $el_name);

		if( isset($this->styles[ $el_name_short ]) && $stylesheet = $this->styles[ $el_name_short ] ){

			$post_id = $post_css->get_post_id();

			if( ! isset($this->added_elements[$post_id]) ){
				$this->added_elements[$post_id] = [];
			}

			if( ! in_array($el_name, $this->added_elements[$post_id]) ){
				reyCoreAssets()->add_styles($stylesheet['widget_name']);
				$this->added_elements[$post_id][] = $el_name;
			}
		}
	}

	public function add_section_custom_css_to_post_file( $post_css, $element ){

		if ( $post_css instanceof Elementor\Core\DynamicTags\Dynamic_CSS ) {
			return;
		}

		if( 'section' !== $element->get_type() ){
			return;
		}

		$rey_custom_css = $element->get_settings('rey_custom_css');

		if( ! ($css = trim( $rey_custom_css )) ) {
			return;
		}

		$css = str_replace( 'SECTION-ID', $post_css->get_element_unique_selector( $element ), $css );

		$post_css->get_stylesheet()->add_raw_css( $css );
	}

	public function load_ajax_assets( $post_id, $instance ){

		$document = $instance->documents->get( $post_id );

		if ( $document ) {
			$data = $document->get_elements_data();
		}

		if ( empty( $data ) ) {
			return;
		}

		$_a = [];

		$assets = $this;

		$instance->db->iterate_data( $data, function( $element ) use ($assets, &$_a) {

			if ( empty( $element['widgetType'] ) ){
				return;
			}

			if( ! (strpos($element['widgetType'], $assets::PREFIX) !== false) ){
				return;
			}

			$name = str_replace($assets::PREFIX, '', $element['widgetType']);

			if( isset($assets->styles[$name]) ){
				$_a['styles'][$name] = $assets->styles[$name]['uri'];
			}

			if( isset($assets->scripts[$name]) ){
				$_a['scripts'][$name] = $assets->scripts[$name];
			}

		});

		if( !empty($_a) ){
			printf( "<div data-assets='%s'></div>", wp_json_encode($_a) );
		}
	}

	public function get_scripts(){
		return $this->scripts;
	}


	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCoreElementor_Widget_Assets
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}

ReyCoreElementor_Widget_Assets::getInstance();
