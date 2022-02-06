<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if(!class_exists('ReyCore_Widget_Global_Section')):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Global_Section extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {

		if ( $data && isset($data['settings']) && $settings = $data['settings'] ) {
			if( isset($settings['section_type']) && $section = $settings['section_type'] ){
				$this->load_css($section);
			}
		}

		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'reycore-global-section';
	}

	public function get_title() {
		return __( 'Global Section', 'rey-core' );
	}

	public function get_icon() {
		return 'eicon-section';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['generic'],
            $element['settings']['header'],
            $element['settings']['cover'],
            $element['settings']['footer'],
            $element['settings']['megamenu']
		);

        return $element;
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#global-section';
	}

	/**
	 * Load Sections custom CSS to prevent FOUC
	 *
	 * @since 1.0.0
	 */
	private function load_css( $section = false ) {

		if ( $section && class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css_file = new \Elementor\Core\Files\CSS\Post( $section );

			if( !empty($css_file) ){
				$css_file->enqueue();
			}
		}
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

		// Section type
		$this->add_control(
			'section_type',
			[
				'label' => __( 'Global Section Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'generic',
				'options' => [
					'generic'  => __( 'Generic Section', 'rey-core' ),
					'header'  => __( 'Header', 'rey-core' ),
					'cover'  => __( 'Page Cover', 'rey-core' ),
					'footer'  => __( 'Footer', 'rey-core' ),
					'megamenu'  => __( 'Mega Menu', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'generic',
			[
				'label' => __( 'Generic Sections', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ReyCore_GlobalSections::get_global_sections('generic', [
					'' => __('- Select -', 'rey-core')
				]),
				'condition' => [
					'section_type' => ['generic'],
				],
			]
		);

		$this->add_control(
			'cover',
			[
				'label' => __( 'Cover Sections', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ReyCore_GlobalSections::get_global_sections('cover', [
					'' => __('- Select -', 'rey-core')
				]),
				'condition' => [
					'section_type' => ['cover'],
				],
			]
		);

		$this->add_control(
			'header',
			[
				'label' => __( 'Header Sections', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ReyCore_GlobalSections::get_global_sections('header', [
					'' => __('- Select -', 'rey-core')
				]),
				'condition' => [
					'section_type' => ['header'],
				],
			]
		);

		$this->add_control(
			'footer',
			[
				'label' => __( 'Footer Sections', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ReyCore_GlobalSections::get_global_sections('footer', [
					'' => __('- Select -', 'rey-core')
				]),
				'condition' => [
					'section_type' => ['footer'],
				],
			]
		);

		$this->add_control(
			'megamenu',
			[
				'label' => __( 'Mega-Menu Sections', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ReyCore_GlobalSections::get_global_sections('megamenu', [
					'' => __('- Select -', 'rey-core')
				]),
				'condition' => [
					'section_type' => ['megamenu'],
				],
			]
		);


		$this->end_controls_section();

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'rey-element reyEl-gs' );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php
				if( $settings['section_type'] && $section = $settings[$settings['section_type']] ){
					echo ReyCore_GlobalSections::do_section($section);
				}
			?>
		</div>
		<!-- .reyEl-gs -->
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
