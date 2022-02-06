<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_GridList') ):

class ReyCore_Wc_GridList
{
	private $settings = [];

	public function __construct()
	{
		return;

		add_action( 'reycore/kirki_fields/after_field=loop_grid_layout', [ $this, 'add_customizer_options' ] );
		add_filter( 'reycore/kirki_fields/field=loop_grid_layout', [$this, 'add_grid_types'], 20);
		add_filter( 'reycore/kirki_fields/field=loop_gap_size', [$this, 'remove_grid_gap'], 20);
		add_action( 'wp', [$this, 'init'] );
	}

	public function init()
	{
		$this->get_grid_type();

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 20 );

		// force short desc
		// force default product skin
		// force compare / wishlist positions

		$this->settings = apply_filters('reycore/module/grid_list', [
			'force_short_description' => true
		]);
	}

	public function enqueue_scripts(){

	}

	public function script_params($params)
	{
		return $params;
	}

	function add_grid_types($field){
		$field['choices']['list'] = esc_html__( 'List', 'rey-core' );
		$field['choices']['list_full'] = esc_html__( 'List (product summary)', 'rey-core' );
		return $field;
	}

	function remove_grid_gap($field){

		$field['active_callback'] = [
			[
				'setting'  => 'loop_grid_layout',
				'operator' => '!=',
				'value'    => 'list',
			],
			[
				'setting'  => 'loop_grid_layout',
				'operator' => '!=',
				'value'    => 'list_full',
			],
		];

		return $field;
	}

	function add_customizer_options($field_args){

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'custom',
			'settings'    => 'loop_grid_layout__list_notice',
			'section'     => $field_args['section'],
			'default'     => esc_html__('All "Products per row" settings will be overriden in List grid layout.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'loop_grid_layout',
					'operator' => 'in',
					'value'    => ['list', 'list_full'],
				],
			],
		] );

	}

	public function get_grid_type(){
		$this->type = get_theme_mod('loop_grid_layout', 'default');
	}

	public function is_enabled() {
		return in_array($this->type, ['list', 'list_full'], true);
	}

}

new ReyCore_Wc_GridList;

endif;
