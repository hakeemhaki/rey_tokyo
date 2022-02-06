<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('WPCF7') && !class_exists('ReyCore_Widget_Cf7')):

	/**
	 *
	 * Elementor widget.
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Widget_Cf7 extends \Elementor\Widget_Base {

		public function get_name() {
			return 'reycore-cf7';
		}

		public function get_title() {
			return __( 'Contact Form', 'rey-core' );
		}

		public function get_icon() {
			return ' eicon-mail';
		}

		public function get_categories() {
			return [ 'rey-theme' ];
		}

		public function get_keywords() {
			return [ 'mail', 'contact', 'form' ];
		}

		public function get_custom_help_url() {
			return 'https://support.reytheme.com/kb/rey-elements/#contact-form';
		}

		public function on_export($element)
		{
			unset(
				$element['settings']['form_id']
			);

			return $element;
		}

		/**
		 * Register widget controls.
		 *
		 * Adds different input fields to allow the user to change and customize the widget settings.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_settings',
				[
					'label' => __( 'Settings', 'rey-core' ),
				]
			);

			$forms = apply_filters('reycore/cf7/forms', []);

			if( !empty($forms) ){

				$note = sprintf(__( 'Create contact forms in <a href="%s" target="_blank">Contact Form 7</a> plugin. Here\'s <a href="%s" target="_blank">an article</a> where you can find generic HTML code to add into the Contact Form, to style it', 'rey-core' ), admin_url('admin.php?page=wpcf7'), 'https://support.reytheme.com/kb/how-to-style-contact-form-7-forms-html/');

				$this->add_control(
					'important_note',
					[
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'raw' => $note,
						'content_classes' => 'elementor-descriptor',
					]
				);
			}

			// form id
			$this->add_control(
				'form_id',
				[
					'label' => __( 'Select form', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $forms,
				]
			);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_style',
				[
					'label' => __( 'Style', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'form_style',
				[
					'label' => __( 'Form Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'basic',
					'options' => [
						'' => '- None -',
						'basic' => esc_html__('Basic', 'rey-core'),
					],
				]
			);



			$this->end_controls_section();
		}

		/**
		 * Render form widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function render() {

			$settings = $this->get_settings_for_display();

			$this->add_render_attribute( 'wrapper', 'class', 'rey-element' );
			$this->add_render_attribute( 'wrapper', 'class', 'rey-cf7' );

			?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<?php if( function_exists('wpcf7_contact_form') && $form_id = $settings['form_id'] ){
					if ( $contact_form = wpcf7_contact_form($form_id) ) {

						if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
							wpcf7_enqueue_scripts();
						}

						if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
							wpcf7_enqueue_styles();
						}

						echo $contact_form->form_html([
							'html_class' => 'rey-cf7--' . $settings['form_style']
						]);
					}
				} ?>
			</div>
			<?php
		}

		/**
		 * Render widget output in the editor.
		 *
		 * Written as a Backbone JavaScript template and used to generate the live preview.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function content_template() {}
	}
endif;
