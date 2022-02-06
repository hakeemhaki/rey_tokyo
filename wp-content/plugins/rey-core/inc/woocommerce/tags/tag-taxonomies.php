<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Taxonomies') ):

class ReyCore_WooCommerce_Taxonomies
{
	public $tax_bottom_description = '';
	public $tax_bottom_gs = '';
	public $set = false;

	private static $_instance = null;

	private function __construct()
	{
		add_action('acf/init', [$this, 'add_controls']);
		add_action('wp', [$this, 'wp']);
	}

	function wp(){

		if( ! is_tax() ){
			return;
		}

		$this->set_acf_fields();

		if( $this->tax_bottom_description || $this->tax_bottom_gs ){

			// woocommerce_after_shop_loop (goes right after products)
			// woocommerce_after_main_content (goes after content)
			add_action(apply_filters('reycore/woocommerce/taxonomies/position', 'woocommerce_after_main_content'), [$this, 'output_bottom_content'], 50);

			// append class
			add_filter('rey/site_content_classes', [$this, 'content_classes']);
		}
	}

	function set_acf_fields( $queried_id = '' ){

		if( ! class_exists('ACF') ) {
			return;
		}

		$queried_object = get_queried_object();

		if( $queried_id ){
			$queried_object = get_term($queried_id);
		}

		if ( $content = get_field('tax_bottom_description', $queried_object) ){
			$this->tax_bottom_description = $content;
		}

		if ( $gs = get_field('tax_bottom_gs', $queried_object) ){
			$this->tax_bottom_gs = $gs;
		}

		$this->set = true;
	}

	function output_bottom_content( $queried_id = '' ){

		if( ! $this->set ){
			$this->set_acf_fields( $queried_id );
		}

		if( $content = $this->tax_bottom_description ){
			printf('<div class="rey-taxBottom">%s</div>', reycore__parse_text_editor($content));
		}

		if( class_exists('ReyCore_GlobalSections') && $gs = $this->tax_bottom_gs ){
			printf('<div class="rey-taxBottom">%s</div>', ReyCore_GlobalSections::do_section($gs) );
		}
	}

	function content_classes( $classes ){

		$classes['bottom_desc'] = '--bottom-desc';

		return $classes;
	}

	function add_controls(){

		acf_add_local_field_group(array(
			'key' => 'group_604909c1983b4',
			'title' => 'Extra taxonomy settings',
			'fields' => array(
				array(
					'key' => 'tax_bottom_description',
					'label' => 'Bottom Description',
					'name' => 'tax_bottom_description',
					'type' => 'wysiwyg',
					'instructions' => 'This description will be displayed in this public taxonomy\'s footer (after the items grid).',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
					'delay' => 1,
				),
				array (
					'key' => 'tax_bottom_gs',
					'label' => 'Bottom Global Section',
					'instructions' => 'This generic global section will be displayed in this public taxonomy\'s footer (after the items grid).',
					'name' => 'tax_bottom_gs',
					'type' => 'global_sections',
					'menu_order' => 0,
					'instructions' => '',
					'required' => 0,
					'gs_type' => 'generic',
				)
			),
			'location' => array(
				array(
					array(
						'param' => 'taxonomy',
						'operator' => '==',
						'value' => 'all',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return Base
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}

ReyCore_WooCommerce_Taxonomies::getInstance();

endif;
