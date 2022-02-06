<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists( 'WP_Store_locator' ) && !class_exists('ReyCore_Compatibility__WpStoreLocator') ):
	/**
	 * Wp Store Locator Plugin Compatibility
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Compatibility__WpStoreLocator
	{
		private static $_instance = null;

		const ASSET_HANDLE = 'reycore-wpsl-styles';

		private function __construct()
		{
			add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'wp', [ $this, 'determine_dealer_button_location' ] );
			add_action( 'reycore/customizer/init', [ $this, 'add_customizer_options' ] );

		}

		public function enqueue_scripts(){
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
		}

		public function register_scripts(){
            wp_register_style( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', ['wpsl-styles'], REY_CORE_VERSION );
		}

		function add_customizer_options(){

			$section = 'woocommerce_wpsl_settings';

			ReyCoreKirki::add_section($section, array(
				'title'          => esc_html__('Plugin: WP Store Locator', 'rey-core'),
				'priority'       => 150,
				'panel'			=> 'woocommerce'
			));

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'toggle',
				'settings'    => 'wpsl_enable_button',
				'label'       => esc_html__( 'Enable Button in Product Page', 'rey-core' ),
				'description' => esc_html__( 'Enable or disable a "Find Dealer" button in the Product page.', 'rey-core' ),
				'section'     => $section,
				'default'     => get_field('wpsl_enable_button', REY_CORE_THEME_NAME),
			] );

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'text',
				'settings'    => 'wpsl_button_text',
				'label'       => esc_html__( 'Button Text', 'rey-core' ),
				'section'     => $section,
				'default'     => get_field('wpsl_button_text', REY_CORE_THEME_NAME),
				'input_attrs'     => [
					'placeholder' => esc_html__('eg: FIND A DEALER', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting'  => 'wpsl_enable_button',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$btn_url = '';
			if( ($acf_btn_url = get_field('wpsl_button_url', REY_CORE_THEME_NAME)) && isset($acf_btn_url['url']) ){
				$btn_url = $acf_btn_url['url'];
			}

			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'text',
				'settings'    => 'wpsl_button_url',
				'label'       => esc_html__( 'Button URL', 'rey-core' ),
				'section'     => $section,
				'default'     => $btn_url,
				'active_callback' => [
					[
						'setting'  => 'wpsl_enable_button',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

		}

		function determine_dealer_button_location(){
			$priority = 25;

			if(get_theme_mod('single_skin', 'default') === 'compact' ){
				$priority = 26;
			}

			add_action( 'woocommerce_single_product_summary', [ $this, 'add_dealer_button' ], $priority );
		}

		public function add_dealer_button(){
			if( class_exists('ACF') && get_theme_mod('wpsl_enable_button', get_field('wpsl_enable_button', REY_CORE_THEME_NAME)) ):

				$link_attributes = '';

				$btn_text = get_theme_mod('wpsl_button_text', get_field('wpsl_button_text', REY_CORE_THEME_NAME));

				$btn_url = '';
				if( ($acf_btn_url = get_field('wpsl_button_url', REY_CORE_THEME_NAME)) && isset($acf_btn_url['url']) ){
					$btn_url = $acf_btn_url['url'];
				}

				$url = get_theme_mod('wpsl_button_url', $btn_url);

				if( $url ){
					$link_attributes .= "href='{$url}'";
					$link_attributes .= "title='{$btn_text}'";
				}

				ob_start();
				?>

				<div class="rey-wpStoreLocator">
					<a <?php echo $link_attributes; ?> class="rey-wpsl-btn btn btn-primary">
						<i class="fa fa-map-marker" aria-hidden="true"></i>
						<span><?php echo $btn_text ?></span>
					</a>
				</div>

				<?php
				return apply_filters('reycore/wp_store_locator/button', ob_get_contents());

			endif;
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__WpStoreLocator::getInstance();
endif;
