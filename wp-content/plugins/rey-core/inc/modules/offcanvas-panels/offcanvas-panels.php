<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_OffcanvasPanels') ):

class ReyCore_OffcanvasPanels
{
	private $settings = [];

	private $offcanvas_panels = [];

	const SCRIPTS_TRANSIENT = 'reycore-offcanvas-scripts';

	const ASSET_HANDLE = 'reycore-offcanvas-panels';

	public function __construct()
	{
		if( ! class_exists('\Elementor\Plugin') ){
			return;
		}

		add_filter( 'reycore/global_sections/types', [$this, 'add_gs_support']);
		add_action( 'reycore/global_section_template/after_content', [$this, 'add_gs_notices']);
		add_action( 'elementor/element/wp-post/document_settings/before_section_end', [$this, 'gs_settings'], 20);
		add_action( 'init', [$this, 'init'] );

		// v1
		add_action( 'wp_ajax_reycore_offcanvas_panel', [$this, 'get_offcanvas_panel_content']);
		add_action( 'wp_ajax_nopriv_reycore_offcanvas_panel', [$this, 'get_offcanvas_panel_content']);

	}

	public function init()
	{
		if( ! $this->is_enabled() ){
			return;
		}

		$this->set_settings();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/elementor/btn_trigger', [ $this, 'init_content'] );
		add_action( 'reycore/elementor/header_nav/offcanvas', [ $this, 'init_content'] );
	}

	private function set_settings(){
		$this->settings = apply_filters('reycore/module/offcanvas_panels/settings', []);
	}

	public function add_gs_support( $gs ){
		$gs['offcanvas']  = __( 'Off-Canvas Panel', 'rey-core' );
		return $gs;
	}

	/**`
	 * Add Global section text notices to describe.
	 *
	 */
	public function add_gs_notices(){

		if( class_exists('ReyCore_GlobalSections') && get_post_type() === ReyCore_GlobalSections::POST_TYPE ):
			$html = '';

			$gs_type = reycore__acf_get_field('gs_type', get_the_ID(), 'generic');

			if( $gs_type === 'offcanvas' ){
				$html = '<div class="rey-pbTemplate--gs-notice elementor-edit-area">' . __('Please click on the <span class="rey-openPageSettings">Page Settings <i class="eicon-cog" aria-hidden="true"></i></span> (bottom left corner) of the screen to adjust this panel\'s settings.', 'rey-core') . '</div>';
			}

			echo $html;
		endif;

	}

	public function register_assets(){

		reyCoreAssets()->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		reyCoreAssets()->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js',
				'deps'    => ['animejs', 'simple-scrollbar', 'reycore-elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			]
		]);
	}

	public function init_content(){

		reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
		reyCoreAssets()->add_styles([self::ASSET_HANDLE, 'simple-scrollbar']);

		add_action(	'wp_footer', [$this, 'add_panels']);
	}

	/**
	 * Checks if there are published Off-canvas panel global sections
	 */
	public function is_enabled() {

		if( ! reycore__can_add_public_content() ){
			return;
		}

		if( ! class_exists('ReyCore_GlobalSections') ){
			return false;
		}

		$this->offcanvas_panels = ReyCore_GlobalSections::get_global_sections('offcanvas');

		return !empty($this->offcanvas_panels);
	}

	function panel_settings_defaults(){
		return [
			'offcanvas_position' => 'left',
			'offcanvas_close_position' => 'inside',
			'offcanvas_close_text' => '',
			'offcanvas_close_outside_rotate' => '',
			'offcanvas_transition' => '',
			'offcanvas_transition_duration' => 700,
			'offcanvas_animate_cols' => 'yes',
			'offcanvas_shift_site' => 'yes',
			'offcanvas_lazyload' => '',
		];
	}

	function add_panels(){

		$defaults = $this->panel_settings_defaults();

		foreach ($this->offcanvas_panels as $id => $gs):

			if( ! apply_filters("reycore/module/offcanvas_panels/load_panel={$id}", false) ){
				continue;
			}

			$settings = get_post_meta( $id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

			if( $settings === false ){
				continue;
			}

			if( isset($settings['offcanvas_lazyload']) && $settings['offcanvas_lazyload'] !== '' ){
				continue;
			}

			$settings = wp_parse_args($settings, $defaults);

			$this->make_markup($id, $settings);

		endforeach;
	}

	function make_markup($id, $settings){

		if( get_post_type() === ReyCore_GlobalSections::POST_TYPE ){
			return;
		}

		?>
			<div class="rey-offcanvas-wrapper --hidden"
				data-transition="<?php echo $settings['offcanvas_transition'] ?>"
				data-transition-duration="<?php echo $settings['offcanvas_transition_duration'] ?>"
				data-position="<?php echo $settings['offcanvas_position'] ?>"
				data-gs-id="<?php echo $id ?>"
				data-close-position="<?php echo $settings['offcanvas_close_position'] ?>"
				data-close-rotate="<?php echo $settings['offcanvas_close_outside_rotate'] ?>"
				data-animate-cols="<?php echo $settings['offcanvas_animate_cols'] ?>"
				data-shift="<?php echo $settings['offcanvas_shift_site'] ?>"
			>
				<div class="rey-offcanvas-contentWrapper">
					<button class="rey-offcanvas-close" aria-label="<?php esc_html_e('Close', 'rey-core') ?>" >
						<span class="rey-offcanvas-closeText"><?php echo $settings['offcanvas_close_text'] ?></span>
						<?php echo reycore__get_svg_icon(['id' => 'rey-icon-close', 'class' => 'icon-close']) ?>
					</button>
					<div class="rey-offcanvas-content">
						<?php echo ReyCore_GlobalSections::do_section( $id ); ?>
					</div>
				</div>
				<div class="rey-lineLoader"></div>
			</div>
		<?php
	}

	function get_offcanvas_panel_content(){

		if( ! (isset($_POST['gs']) && ($id = absint($_POST['gs']))) ){
			wp_send_json_error(esc_html__('Missing Global Section.', 'rey-core'));
		}

		if( ! class_exists('ReyCore_GlobalSections') ){
			wp_send_json_error(esc_html__('Elementor is disabled?', 'rey-core'));
		}

		$settings = get_post_meta( $id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

		if( $settings === false ){
			wp_send_json_error(esc_html__('Can\'t retrieve settings!', 'rey-core'));
		}

		$settings = wp_parse_args($settings, $this->panel_settings_defaults());

		if( $settings['offcanvas_lazyload'] === '' ){
			wp_send_json_error(esc_html__('Lazy loaded disabled on this panel!', 'rey-core'));
		}

		ob_start();

		$this->make_markup($id, $settings);

		$content = ob_get_clean();

		// check if GS && get_field gs is panel
		if( $content ){
			wp_send_json_success($content);
		}

		wp_send_json_error(esc_html__('Couldn\'t retrieve content.', 'rey-core'));

	}


	/**
	 * Add page settings into Elementor
	 *
	 * @since 1.7.0
	 */
	function gs_settings( $page )
	{
		if(
			class_exists('ReyCore_GlobalSections') &&
			($page_id = $page->get_id()) && $page_id != "" && ($post_type = get_post_type( $page_id )) &&
			($post_type === ReyCore_GlobalSections::POST_TYPE || $post_type === 'revision')
		) {

			if( $post_type === 'revision' && ($rev_id = wp_get_post_parent_id($page_id)) && $rev_id !== 0 ){
				$page_id = $rev_id;
			}

			$gs_type = $page->get_settings_for_display('gs_type');

			if( !$gs_type ) {
				$gs_type = reycore__acf_get_field('gs_type', $page_id, 'generic');
			}

			$page->add_control(
				'offcanvas_panel_heading',
				[
					'label' => esc_html__( 'OFFCANVAS SETTINGS', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			$panel_selector = sprintf('.rey-offcanvas-wrapper[data-gs-id="%s"]', $page_id);

			$page->add_responsive_control(
				'offcanvas_width',
				[
					'label' => esc_html__( 'Panel Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw', 'vh' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 3000,
							'step' => 1,
						],
						'vw' => [
							'min' => 10,
							'max' => 100,
						],
						'vh' => [
							'min' => 5,
							'max' => 100,
						],
					],
					'default' => [],
					'selectors' => [
						$panel_selector => '--panel-width: {{SIZE}}{{UNIT}};',
						'.elementor-editor-active, .elementor-editor-preview' => '--panel-width: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			$page->add_control(
				'offcanvas_bgcolor',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$panel_selector => '--panel-color:{{VALUE}}',
						'.elementor-editor-active, .elementor-editor-preview' => '--panel-color:{{VALUE}}',
					],
					'separator' => 'before',
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			$page->add_control(
				'offcanvas_position',
				[
					'label' => esc_html__( 'Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'left'  => esc_html__( 'Left', 'rey-core' ),
						'right'  => esc_html__( 'Right', 'rey-core' ),
						'top'  => esc_html__( 'Top', 'rey-core' ),
						'bottom'  => esc_html__( 'Bottom', 'rey-core' ),
					],
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			// -----

			$page->add_control(
				'offcanvas_close_position',
				[
					'label' => esc_html__( 'Close Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'inside',
					'options' => [
						'inside'  => esc_html__( 'Inside', 'rey-core' ),
						'outside'  => esc_html__( 'Outside', 'rey-core' ),
					],
					'separator' => 'before',
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			$page->add_control(
				'offcanvas_close_text',
				[
					'label' => esc_html__( 'Close text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Close', 'rey-core' ),
					'selectors' => [
						$panel_selector => '--close-text: "{{VALUE}}";',
					],
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			$page->add_control(
				'offcanvas_close_outside_rotate',
				[
					'label' => esc_html__( 'Rotate Button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'gs_type' =>'offcanvas',
						'offcanvas_position' => ['left', 'right'],
						'offcanvas_close_position' => 'outside',
						'offcanvas_close_text!' => '',
					]
				]
			);

			$page->add_control(
				'offcanvas_close_size',
				[
					'label' => esc_html__( 'Close Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 8,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$panel_selector => '--close-size: {{VALUE}}px',
					],
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			// -----

			$page->add_control(
				'offcanvas_transition',
				[
					'label' => esc_html__( 'Transition', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default (Slide)', 'rey-core' ),
						'slideskew'  => esc_html__( 'Slide Skew', 'rey-core' ),
						'curtain'  => esc_html__( 'Curtain', 'rey-core' ),
						'basic'  => esc_html__( 'Basic', 'rey-core' ),
					],
					'separator' => 'before',
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			$page->add_control(
				'offcanvas_transition_duration',
				[
					'label' => esc_html__( 'Transition Duration', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 700,
					'min' => 0,
					'max' => 2000,
					'step' => 10,
					'condition' => [
						'gs_type' =>'offcanvas',
					],
					'selectors' => [
						$panel_selector => '--transition-duration: {{VALUE}}ms;',
					],
				]
			);

			$page->add_control(
				'offcanvas_animate_cols',
				[
					'label' => esc_html__( 'Animate Inside', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

			$page->add_control(
				'offcanvas_shift_site',
				[
					'label' => esc_html__( 'Shift Site Content', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'gs_type' =>'offcanvas',
						'offcanvas_position' => ['left', 'right'],
					],
				]
			);

			$page->add_control(
				'offcanvas_curtain__m1_color',
				[
					'type' => \Elementor\Controls_Manager::COLOR,
					'label' => esc_html__( 'Curtain - Mask #1 Color', 'rey-core' ),
					'selectors' => [
						$panel_selector . ' .rey-offcanvas-mask.--m1' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'gs_type' =>'offcanvas',
						'offcanvas_transition' => 'curtain',
					],
				]
			);

			$page->add_control(
				'offcanvas_curtain__m2_color',
				[
					'type' => \Elementor\Controls_Manager::COLOR,
					'label' => esc_html__( 'Curtain - Mask #2 Color', 'rey-core' ),
					'selectors' => [
						$panel_selector . ' .rey-offcanvas-mask.--m2' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'gs_type' =>'offcanvas',
						'offcanvas_transition' => 'curtain',
					],
				]
			);

			$page->add_control(
				'offcanvas_lazyload',
				[
					'label' => esc_html__( 'Lazy Load Content', 'rey-core' ),
					'description' => esc_html__( 'Enabling this option will force the content to load only on demand (when button is clicked), via Ajax.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'gs_type' =>'offcanvas',
					],
				]
			);

		}
	}

}

new ReyCore_OffcanvasPanels;

endif;
