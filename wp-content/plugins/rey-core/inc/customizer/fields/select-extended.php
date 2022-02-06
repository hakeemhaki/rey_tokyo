<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReySelectExtended extends Kirki_Control_Base {

		public $type = 'kirki-select';

		public $placeholder = false;

		public $multiple = 1;

		public $query_args;

		/*
		'query_args' => [
			'type' => 'posts',
			'post_type' => 'product',
			'meta' => [
				'meta_key' => '',
				'meta_value' => ''
			]
		],

		'query_args' => [
			'type' => 'terms',
			'taxonomy' => 'product_cat',
			// 'taxonomy' => 'all_attributes',
			'field' => 'slug',
		],
		*/

		/**
		 * Constructor.
		 * Supplied `$args` override class property defaults.
		 * If `$args['settings']` is not defined, use the $id as the setting ID.
		 *
		 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
		 * @param string               $id      Control ID.
		 * @param array                $args    {@see WP_Customize_Control::__construct}.
		 */
		public function __construct( $manager, $id, $args = [] ) {

			parent::__construct( $manager, $id, $args );

			if ( empty( $args['query_args'] ) || ! is_array( $args['query_args'] ) ) {
				$args['query_args'] = [];
			}

			$this->query_args = $args['query_args'];
			$this->multiple = isset($args['multiple']) && absint($args['multiple']) > 1 ? $args['multiple'] : 1;

		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @see WP_Customize_Control::to_json()
		 */
		public function to_json() {

			// Get the basics from the parent class.
			parent::to_json();

			$this->json['query_args'] = $this->query_args;

			$this->json['multiple']    = $this->multiple;

			$this->json['placeholder'] = $this->placeholder;
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['kirki-select'] = 'Kirki_Controls_ReySelectExtended';
		return $controls;
	}, 20 );


} );
