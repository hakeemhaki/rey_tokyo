<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Element_Common') ):
    /**
	 * Common Overrides and customizations
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Element_Common {

		private $transient_data = [];
		private $prevent_save = [];

		const COMMON_TRANSIENT = 'rey_elementor_common_';

		private static $cache = true;

		function __construct(){

			add_action( 'elementor/element/common/_section_style/before_section_end', [$this, 'common_advanced']);
			add_action( 'elementor/element/common/section_effects/before_section_end', [$this, 'common_effects'], 10, 2);
			add_action( 'elementor/element/common/_section_position/before_section_end', [$this, 'common_position'], 10, 2);

			if( reyCoreElementor()->animations_enabled() ){
				add_action( 'elementor/frontend/before_render', [$this, 'common_before_render'], 10);
				if( self::$cache ){
					add_action( 'elementor/frontend/before_get_builder_content', [$this, 'get_transient_data']);
					add_action( 'elementor/frontend/get_builder_content', [$this, 'set_transient_data']);
					add_action( 'elementor/editor/after_save', [$this, 'remove_transient_data'], 10, 2 );
				}
			}
		}

		function get_post_id(){

			$post_id = 0;

			if( isset($GLOBALS['elem_post_id']) ){
				$post_id = $GLOBALS['elem_post_id'];
			}

			return $post_id;
		}

		function remove_transient_data($post_id, $editor_data){
			delete_transient( self::COMMON_TRANSIENT . $post_id );
		}

		function get_transient_data(){

			$post_id = $this->get_post_id();
			if( false !== ($data = get_transient( self::COMMON_TRANSIENT . $post_id )) ){
				$this->transient_data[$post_id] = $data;
				$this->prevent_save[$post_id] = true;
				return;
			}
		}

		function set_transient_data(){
			if( $post_id = $this->get_post_id() ){
				if( isset($this->transient_data[$post_id]) && ! isset($this->prevent_save[$post_id]) ){
					set_transient( self::COMMON_TRANSIENT . $post_id, $this->transient_data[$post_id], WEEK_IN_SECONDS );
				}
			}
		}

		/**
		 * Render some attributes before rendering.
		 *
		 * @since 1.0.0
		 **/
		function common_before_render( $element )
		{

			if( $element->get_type() !== 'widget' ){
				return;
			}

			$el_id = $element->get_id();

			if( self::$cache &&
				($post_id = $this->get_post_id()) &&
				isset($this->transient_data[$post_id][$el_id]) && $this->transient_data[$post_id][$el_id] === false ){
				return;
			}

			// error_log(var_export( ':: will run checks ' . $post_id . '_' . $el_id,1));

			if( ! (
					($settings = $element->get_data('settings')) &&
					isset($settings['rey_animation_overrides']) &&
					$settings['rey_animation_overrides'] !== ''
				)
			){
				if( self::$cache ){
					$this->transient_data[$post_id][$el_id] = false;
				}
				return;
			}

			$config = [];

			if( isset($settings['rey_animation_type_reveal_direction']) && !empty($settings['rey_animation_type_reveal_direction']) ) {
				$config['reveal_direction'] = esc_attr( $settings['rey_animation_type_reveal_direction']);
			}

			if( isset($settings['rey_animation_type__reveal_bg_color']) && !empty($settings['rey_animation_type__reveal_bg_color']) ) {
				$config['reveal_bg'] = esc_attr( $settings['rey_animation_type__reveal_bg_color']);
			}

			if( isset($settings['rey_animation_delay']) && !empty($settings['rey_animation_delay']) ) {
				$config['delay'] = esc_attr( $settings['rey_animation_delay'] );
			}

			if( isset($settings['rey_animation_duration']) && $settings['rey_animation_duration'] != 'inherit' ) {
				$config['duration'] = esc_attr( $settings['rey_animation_duration'] );
			}

			$element->add_render_attribute( '_wrapper', 'data-rey-anim-config', wp_json_encode($config) );

			reyCoreAssets()->add_scripts('reycore-elementor-entrance-animations');
			reyCoreAssets()->add_styles('reycore-elementor-entrance-animations');

			if( self::$cache ){
				$this->transient_data[$post_id][$el_id] = true;
			}

		}


		/**
		 * Tweak the CSS classes field.
		 */
		function common_advanced( $stack )
		{
			$stack->add_responsive_control(
				'rey_el_order',
				[
					'label' => __( 'Order', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Default -', 'rey-core' ),
						'-1'  => esc_html__( 'First', 'rey-core' ),
						'1'  => esc_html__( '1', 'rey-core' ),
						'2'  => esc_html__( '2', 'rey-core' ),
						'3'  => esc_html__( '3', 'rey-core' ),
						'4'  => esc_html__( '4', 'rey-core' ),
						'5'  => esc_html__( '5', 'rey-core' ),
						'6'  => esc_html__( '6', 'rey-core' ),
						'7'  => esc_html__( '7', 'rey-core' ),
						'8'  => esc_html__( '8', 'rey-core' ),
						'9'  => esc_html__( '9', 'rey-core' ),
						'10'  => esc_html__( '10', 'rey-core' ),
						'999'  => esc_html__( 'Last', 'rey-core' ),
					],
					'selectors' => [
						'{{WRAPPER}}' => 'order: {{VALUE}};',
					],
				]
			);

			// get args
			$css_classes = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), '_css_classes' );
			$css_classes['label_block'] = true;
			$stack->update_control( '_css_classes', $css_classes );

			$stack->add_control(
				'rey_utility_classes',
				[
					'label' => esc_html__( 'Utility Classes', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- None -', 'rey-core' ),
						'm-auto--top'  => esc_html__( 'Margin Top - Auto', 'rey-core' ),
						'm-auto--right'  => esc_html__( 'Margin Right - Auto', 'rey-core' ),
						'm-auto--bottom'  => esc_html__( 'Margin Bottom - Auto', 'rey-core' ),
						'm-auto--left'  => esc_html__( 'Margin Left - Auto', 'rey-core' ),
					],
					'prefix_class' => ''
				]
			);
		}


		/**
		 * Add custom settings into Elementor's "Motion Effects" Section
		 *
		 * @since 1.0.0
		 */
		function common_effects( $element, $args )
		{

			if( reyCoreElementor()->animations_enabled() ):

				$element->add_control(
					'rey_animation_overrides',
					[
						'label' => __( 'Animation Overrides', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
					]
				);

				$element->add_control(
					'rey_animation_duration',
					[
						'label' => __( 'Animation Duration', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'inherit',
						'options' => [
							'inherit' => __( 'Inherit', 'rey-core' ),
							'slow' => __( 'Slow', 'rey-core' ),
							'' => __( 'Normal', 'rey-core' ),
							'fast' => __( 'Fast', 'rey-core' ),
						],
						'render_type' => 'none',
						'condition' => [
							'rey_animation_overrides!' => [''],
						],
					]
				);

				$element->add_control(
					'rey_animation_delay',
					[
						'label' => __( 'Animation Delay', 'rey-core' ) . ' (ms)',
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'step' => 100,
						'condition' => [
							'rey_animation_overrides!' => [''],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_reveal_title',
					[
					'label' => __( 'REVEAL SETTINGS', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							// 'rey_animation_type' => ['reveal'],
							'rey_animation_overrides!' => [''],
						],
					]
				);

				$element->add_control(
					'rey_animation_type_reveal_direction',
					[
						'label' => __( 'Reveal Direction', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							''  => __( 'Inherit', 'rey-core' ),
							'left'  => __( 'Left', 'rey-core' ),
							'top'  => __( 'Top', 'rey-core' ),
						],
						'condition' => [
							// 'rey_animation_type' => ['reveal'],
							'rey_animation_overrides!' => [''],
						],
						'render_type' => 'none',
					]
				);

				$element->add_control(
					'rey_animation_type__reveal_bg_color',
					[
						'label' => __( 'Reveal Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'condition' => [
							// 'rey_animation_type' => ['reveal'],
							'rey_animation_overrides!' => [''],
						],
						'render_type' => 'none',
					]
				);
			endif;
		}


		/**
		 * Add custom settings into Elementor's "Custom Position" Section
		 *
		 * @since 1.0.0
		 */
		function common_position( $element, $args )
		{

			$element->start_injection( [
				'of' => '_element_vertical_align_mobile',
			] );

			$element->add_responsive_control(
				'rey_inline_horizontal_align',
				[
					'label'           => __( 'Horizontal Align', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type'            => \Elementor\Controls_Manager::CHOOSE,
					// 'label_block'     => true,
					'toggle'          => true,
					'default'         => '',
					'options'         => [
						'left'           => [
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-h-align-left',
						],
						'stretch'        => [
							'title'         => __( 'Stretch', 'rey-core' ),
							'icon'          => 'eicon-h-align-stretch',
						],
						'right'          => [
							'title'         => __( 'Right', 'rey-core' ),
							'icon'          => 'eicon-h-align-right',
						],
					],
					'devices'         => [ 'desktop', 'tablet', 'mobile' ],
					'condition'       => [
						'_element_width' => ['auto', 'initial'],
						'_position'      => '',
					],
					'prefix_class'    => 'rey-widget-inline-%s-',
				]
			);

			$element->add_responsive_control(
				'rey_inline_flex_grow',
				[
					'label'           => __( 'Flex Grow', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type'            => \Elementor\Controls_Manager::SELECT,
					'label_block'     => false,
					'default'         => '',
					'options'         => [
						'' => __( 'Unset', 'rey-core' ),
						'1' => __( 'Yes', 'rey-core' ),
						'0' => __( 'No', 'rey-core' ),
					],
					'devices'         => [ 'desktop', 'tablet', 'mobile' ],
					'condition'       => [
						'_element_width' => ['auto', 'initial'],
						'_position'      => '',
					],
					'selectors' => [
						'{{WRAPPER}}' => '-webkit-box-flex: {{VALUE}}; -ms-flex-positive: {{VALUE}}; flex-grow: {{VALUE}}',
					],
					// 'description' => esc_html__('Specifies how much the item will grow relative to the rest of the flexible items inside the same container.', 'rey-core'),
				]
			);

			$element->add_responsive_control(
				'rey_inline_flex_shrink',
				[
					'label'           => __( 'Flex Shrink', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type'            => \Elementor\Controls_Manager::SELECT,
					'label_block'     => false,
					'default'         => '',
					'options'         => [
						'' => __( 'Unset', 'rey-core' ),
						'1' => __( 'Yes', 'rey-core' ),
						'0' => __( 'No', 'rey-core' ),
					],
					'devices'         => [ 'desktop', 'tablet', 'mobile' ],
					'condition'       => [
						'_element_width' => ['auto', 'initial'],
						'_position'      => '',
					],
					'selectors' => [
						'{{WRAPPER}}' => '-ms-flex-negative: {{VALUE}}; flex-shrink: {{VALUE}}',
					],
					'separator' => 'after'
					// 'description' => esc_html__('Specifies how the item will shrink relative to the rest of the flexible items inside the same container.', 'rey-core'),
				]
			);

			$element->end_injection();

			$element->add_control(
				'rey_position_mobile',
				[
					'label' => esc_html__( 'Static position on mobiles', 'rey-core' ) . reyCoreElementor::getReyBadge(),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'_position!' => '',
					],
					'prefix_class' => 'rey-default-position-',
				]
			);
		}


	}
endif;
