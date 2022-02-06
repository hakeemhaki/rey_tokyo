<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( ! class_exists('ReyCore_ACF_Elementor') ):

class ReyCore_ACF_Elementor
{
	public static $widgets_dir  = '';

	public function __construct()
	{

		if( ! (class_exists('Elementor\Plugin') && is_callable( 'Elementor\Plugin::instance' )) ){
			return;
		}

		self::$widgets_dir = trailingslashit( REY_CORE_MODULE_DIR . 'elementor-acf/' );

		add_action( 'init', [$this, 'init'] );
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );
		add_filter( 'reycore/query-control/autocomplete', [$this, 'query_control_autocomplete'], 10, 2);
		add_filter( 'reycore/query-control/values', [$this, 'query_control_values'], 10, 2);
	}

	public function init()
	{

	}

	public function register_widgets( $widgets_manager ) {

		foreach ( [
			'acf-text',
			'acf-heading',
			'acf-button',
			'acf-image',
			// 'acf-icon', // field uses 2 values, icon string and library
		] as $widget ) {
			$this->register_widget( $widget, $widgets_manager );
		}
	}

	public function register_widget( $widget, $widgets_manager ) {

		$class = ucwords( str_replace( '-', ' ', $widget ) );
		$class = str_replace( ' ', '_', $class );
		$class = sprintf( 'ReyCore_Widget_%s', $class );

		// Load widget
		if( ( $file = trailingslashit( self::$widgets_dir ) . "{$widget}.php" ) && is_readable($file) ){
			require_once $file;
		}

		if ( class_exists( $class ) ) {
			// Register widget
			$widgets_manager->register_widget_type( new $class );
		}

	}

	/**
	 * Add Rey Widget Categories
	 *
	 * @since 1.0.0
	 */
	public function add_elementor_widget_categories( $elements_manager ) {

		$categories = [
			'rey-acf' => [
				'title' => __( 'REY - ACF Fields', 'rey-core' ),
				'icon' => 'fa fa-plug',
			],
		];

		foreach( $categories as $key => $data ){
			$elements_manager->add_category($key, $data);
		}
	}

	public static function get_acf_fields( $types = [] ){

		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_groups = acf_get_field_groups();
		}

		$default_types = [
			'text',
			'textarea',
			'number',
			'email',
			'wysiwyg',
			'select',
			'checkbox',
			'radio',
			'true_false',
			'oembed',
			'google_map',
			'date_picker',
			'time_picker',
			'date_time_picker',
			'color_picker',
			'image',
		];

		$options = [];

		$options_page_groups_ids = [];

		if ( function_exists( 'acf_options_page' ) ) {

			$pages = acf_options_page()->get_pages();

			foreach ( $pages as $slug => $page ) {

				$options_page_groups = acf_get_field_groups( [
					'options_page' => $slug,
				] );

				foreach ( $options_page_groups as $options_page_group ) {
					$options_page_groups_ids[ $options_page_group['key'] ] = $page['post_id'];
				}

			}
		}

		foreach ( $acf_groups as $acf_group ) {

			if ( function_exists( 'acf_get_fields' ) ) {
				if ( isset( $acf_group['ID'] ) && ! empty( $acf_group['ID'] ) ) {
					$fields = acf_get_fields( $acf_group['ID'] );
				} else {
					$fields = acf_get_fields( $acf_group );
				}
			}


			if ( ! is_array( $fields ) ) {
				continue;
			}

			foreach ( $fields as $field ) {

				if ( ! in_array( $field['type'], $types, true ) ) {
					continue;
				}

				if( array_key_exists($field['parent'], $options_page_groups_ids) ){
					$key = $field['key'] . ':' . $field['name'] . ':' . $options_page_groups_ids[$field['parent']];
				}
				else {
					$key = $field['key'] . ':' . $field['name'];
				}

				$options[ $key ] = sprintf('%s > %s', $acf_group['title'], $field['label']);

			}

			if ( empty( $options ) ) {
				continue;
			}

		}

		return $options;

	}

	function query_control_values($results, $data){

		if( ! isset($data['query_args']['type']) ){
			return $results;
		}

		if( $data['query_args']['type'] !== 'acf' ){
			return $results;
		}

		$field_types = isset($data['query_args']['field_types']) ? $data['query_args']['field_types'] : [];
		$fields = self::get_acf_fields( $field_types );

		foreach ((array) $data['values'] as $id) {
			if( isset($fields[$id]) ){
				$results[ $id ] = $fields[$id];
			}
		}

		return $results;
	}

	function query_control_autocomplete($results, $data){

		if( ! isset($data['query_args']['type']) ){
			return $results;
		}

		if( $data['query_args']['type'] !== 'acf' ){
			return $results;
		}

		$field_types = isset($data['query_args']['field_types']) ? $data['query_args']['field_types'] : [];
		$fields = self::get_acf_fields( $field_types );

		foreach( $fields as $id => $text ){
			if( strpos($id, $data['q']) !== false || strpos(strtolower($text), strtolower($data['q'])) !== false ){
				$results[] = [
					'id' 	=> $id,
					'text' 	=> $text,
				];
			}
		}

		return $results;
	}

	public static function get_field( $key ){

		$parts = explode(':', $key);
		$field = $parts[0];
		$post_id = get_queried_object_id();

		// has option page
		if( count($parts) > 2 ){
			$post_id = $parts[2];
		}

		return get_field($field, $post_id);
	}

}

new ReyCore_ACF_Elementor;

endif;
