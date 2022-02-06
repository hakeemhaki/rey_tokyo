<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('kirki_telemetry', '__return_false');

add_filter('kirki_googlefonts_transient_time', function(){
	return MONTH_IN_SECONDS;
}, 10);

function reycore__kirki_init() {

	// Force Kirki's Google fonts variants to load all
    if (class_exists('Kirki_Fonts_Google')) {
		Kirki_Fonts_Google::$force_load_all_variants = reycore__acf_get_field('kirki_font_variants', REY_CORE_THEME_NAME);
	}
}
add_action('init', 'reycore__kirki_init');


if(!function_exists('reycore_customizer__add_field')):
	/**
	 * Add fields
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__add_field( $args = [] ){

		if( empty($args) ){
			return;
		}

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
endif;

if(!function_exists('reycore__background_attachment_choices')):
	/**
	 * Background attachment choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_attachment_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'scroll' => esc_html__('Scroll', 'rey-core'),
			'fixed' => esc_html__('Fixed', 'rey-core'),
			'local' => esc_html__('Local', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__background_size_choices')):
	/**
	 * Background size choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_size_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'auto' => esc_html__('Auto', 'rey-core'),
			'contain' => esc_html__('Contain', 'rey-core'),
			'cover' => esc_html__('Cover', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__background_repeat_choices')):
	/**
	 * Background repeat choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_repeat_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'repeat' => esc_html__('Repeat', 'rey-core'),
			'no-repeat' => esc_html__('No Repeat', 'rey-core'),
			'repeat-x' => esc_html__('Repeat Horizontally', 'rey-core'),
			'repeat-y' => esc_html__('Repeat Vertically', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;

if(!function_exists('reycore__background_clip_choices')):
	/**
	 * Background clip choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_clip_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'border-box' => esc_html__('Border Box', 'rey-core'),
			'padding-box' => esc_html__('Padding Box', 'rey-core'),
			'content-box' => esc_html__('Content Box', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__background_blend_choices')):
	/**
	 * Background blend mode choices
	 *
	 * @since 1.0.0
	 **/
	function reycore__background_blend_choices()
	{
		return [
			'' => esc_html__('Default', 'rey-core'),
			'normal' => esc_html__('Normal', 'rey-core'),
			'multiply' => esc_html__('Multiply', 'rey-core'),
			'screen' => esc_html__('Screen', 'rey-core'),
			'overlay' => esc_html__('Overlay', 'rey-core'),
			'darken' => esc_html__('Darken', 'rey-core'),
			'lighten' => esc_html__('Lighten', 'rey-core'),
			'color-dodge' => esc_html__('Color Dodge', 'rey-core'),
			'saturation' => esc_html__('Saturation', 'rey-core'),
			'color-burn' => esc_html__('Color burn', 'rey-core'),
			'hard-light' => esc_html__('Hard light', 'rey-core'),
			'soft-light' => esc_html__('Soft light', 'rey-core'),
			'difference' => esc_html__('Difference', 'rey-core'),
			'exclusion' => esc_html__('Exclusion', 'rey-core'),
			'hue' => esc_html__('Hue', 'rey-core'),
			'color' => esc_html__('Color', 'rey-core'),
			'luminosity' => esc_html__('Luminosity', 'rey-core'),
			'initial' => esc_html__('Initial', 'rey-core'),
			'inherit' => esc_html__('Inherit', 'rey-core'),
		];
	}
endif;


if(!function_exists('reycore__kirki_custom_bg_group')):
	/**
	 * Custom Background option group.
	 *
	 * @since 1.0.0
	 */
	function reycore__kirki_custom_bg_group($args = []){

		$defaults = [
			'settings' => 'bg_option',
			'section' => 'bg_section',
			'label' => esc_html__('Background', 'rey-core'),
			'description' => esc_html__('Change background settings.', 'rey-core'),
			'output_element' => '',
			'active_callback' => [],
			'color' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-color',
				'active_callback' => [],
			],
			'image' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-image',
				'active_callback' => [],
			],
			'repeat' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-repeat',
				'active_callback' => [],
			],
			'attachment' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-attachment',
				'active_callback' => [],
			],
			'size' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-size',
				'active_callback' => [],
			],
			'positionx' => [
				'default' => '50%',
				'output_element' => '',
				'output_property' => 'background-position-x',
				'active_callback' => [],
			],
			'positiony' => [
				'default' => '50%',
				'output_element' => '',
				'output_property' => 'background-position-y',
				'active_callback' => [],
			],
			'blend' => [
				'default' => '',
				'output_element' => '',
				'output_property' => 'background-blend-mode',
				'active_callback' => [],
			],
		];

		$args = reycore__wp_parse_args($args, $defaults);

		$has_image = [
			'setting'  => $args['settings']. '_img',
			'operator' => '!=',
			'value'    => '',
		];

		$active_callback[] = $has_image;

		if( $args['active_callback'] ){
			$active_callback[] = array_merge($active_callback, $args['active_callback']);
		}

		$priority = isset($args['priority']) ? $args['priority'] : '';

		reycore_customizer__title([
			'title'       => $args['label'],
			'description' => $args['description'],
			'section'     => $args['section'],
			'size'        => 'xs',
			'border'      => 'none',
			'upper'       => true,
			'priority'    => $priority,
		]);

		/**
		 * IMAGE
		 */
		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'image',
			'settings'    => $args['settings']. '_img',
			'section'     => $args['section'],
			'default'     => $args['image']['default'],
			'priority'   => $priority,
			// 'transport'   => 'auto',
			'output'      => [
				[
					'element' => !empty( $args['image']['output_element'] ) ? $args['image']['output_element'] : $args['output_element'],
					'property' => $args['image']['output_property'],
					'value_pattern' => 'url($)'
				]
			],
			'active_callback' => $args['active_callback']
		] );

		/**
		 * REPEAT
		 */
		if( in_array('repeat', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_repeat',
				'label'       => __( 'Background Repeat', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['image']['default'],
				'choices'     => reycore__background_repeat_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['repeat']['output_element'] ) ? $args['repeat']['output_element'] : $args['output_element'],
						'property' => $args['repeat']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * ATTACHMENT
		 */
		if( in_array('attachment', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_attachment',
				'label'       => __( 'Background Attachment', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['attachment']['default'],
				'choices'     => reycore__background_attachment_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['attachment']['output_element'] ) ? $args['attachment']['output_element'] : $args['output_element'],
						'property' => $args['attachment']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * SIZE
		 */
		if( in_array('size', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_size',
				'label'       => __( 'Background Size', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['size']['default'],
				'choices'     => reycore__background_size_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['size']['output_element'] ) ? $args['size']['output_element'] : $args['output_element'],
						'property' => $args['size']['output_property'],
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * POSITION
		 */
		if( in_array('position', $args['supports']) ):
			/**
			 * POSITION X
			 */
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'text',
				'settings'    => $args['settings']. '_positionx',
				'label'       => __( 'Background Horizontal Position ', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['positionx']['default'],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['positionx']['output_element'] ) ? $args['positionx']['output_element'] : $args['output_element'],
						'property' => $args['positionx']['output_property']
					]
				],
				'active_callback' => $active_callback
			] );

			/**
			 * POSITION Y
			 */
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'text',
				'settings'    => $args['settings']. '_positiony',
				'label'       => __( 'Background Vertical Position ', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['positiony']['default'],
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['positiony']['output_element'] ) ? $args['positiony']['output_element'] : $args['output_element'],
						'property' => $args['positiony']['output_property']
					]
				],
				'active_callback' => $active_callback
			] );
		endif;

		/**
		 * COLOR
		 */
		if( in_array('color', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'rey-color',
				'settings'    => $args['settings']. '_color',
				'label'       => __( 'Background Color', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['color']['default'],
				'transport'   => 'auto',
				'priority'   => $priority,
				'choices'     => [
					'alpha' => true,
				],
				'output'      => [
					[
						'element' => !empty( $args['color']['output_element'] ) ? $args['color']['output_element'] : $args['output_element'],
						'property' => $args['color']['output_property'],
					]
				],
				'active_callback' => $args['active_callback']
			] );
		endif;


		/**
		 * BLEND
		 */
		if( in_array('blend', $args['supports']) ):
			ReyCoreKirki::add_field( 'rey_core_kirki', [
				'type'        => 'select',
				'settings'    => $args['settings']. '_blend',
				'label'       => __( 'Background Blend', 'rey-core' ),
				'section'     => $args['section'],
				'default'     => $args['blend']['default'],
				'choices'     => reycore__background_blend_choices(),
				'priority'   => $priority,
				'transport'   => 'auto',
				'output'      => [
					[
						'element' => !empty( $args['blend']['output_element'] ) ? $args['blend']['output_element'] : $args['output_element'],
						'property' => $args['blend']['output_property'],
					]
				],
				'active_callback' => $args['active_callback']
			] );
		endif;

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'        => 'custom',
			'settings'    => $args['settings']. '_end',
			'section'     => $args['section'],
			'priority'    => $priority,
			'default'     => '<hr>',
		] );

	}
endif;

if(!function_exists('reycore__customizer_to_acf')):
	/**
	 * Push customizer option into ACF
	 *
	 * @since 1.0.0
	 */
	function reycore__customizer_to_acf( $wp_customizer )
	{
		// Sync shop page cover with post's option
		if( class_exists('ACF') && class_exists('WooCommerce') && wc_get_page_id('shop') !== -1 ){
			update_field('page_cover', get_theme_mod('cover__shop_page'), wc_get_page_id('shop') );
		}
		// Sync blog page cover with post's option
		if( class_exists('ACF') && get_option( 'page_for_posts' ) ){
			update_field('page_cover', get_theme_mod('cover__blog_home'), get_option( 'page_for_posts' ) );
		}
	}
endif;
add_action('customize_save_after', 'reycore__customizer_to_acf', 20);


if(!function_exists('reycore_customizer__print_js')):
	/**
	 * Print JS script in Customizer Preview
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__print_js()
	{ ?>
		<script type="text/javascript">
			(function ( api ) {

				var lsName = "rey-active-view-selector-" + <?php echo is_multisite() ? absint( get_current_blog_id() ) : 0 ?>,
					removeLs = function(){
						localStorage.removeItem( lsName );
					};

				api.bind( "ready", function () {
					removeLs();
				});

				api.bind( "saved", function () {
					removeLs();
				});

				api( 'woocommerce_catalog_columns', function( value ) {
					value.bind( function( to ) {
						removeLs();
					} );
				} );
			})( wp.customize );
		</script>
		<?php
	}
endif;
add_action( 'customize_controls_print_scripts', 'reycore_customizer__print_js', 30 );


if(!function_exists('reycore_customizer__print_css')):
	/**
	 * Print CSS styles in Customizer Preview
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__print_css()
	{ ?>
		<style type="text/css">
			@media screen and (min-width: 1667px){
				.wp-full-overlay.expanded {
					margin-left: 345px;
				}
				.rtl .wp-full-overlay.expanded {
					margin-right: 345px;
					margin-left: 0;
				}
			}
			.wp-full-overlay-sidebar {
				width: 345px;
			}
			.preview-mobile .wp-full-overlay-main {
				margin: auto 0 auto -180px;
				width: 360px;
				height: 640px;
			}
			.rtl .preview-mobile .wp-full-overlay-main {
				margin-right: -180px;
				margin-left: auto;
			}
			.preview-tablet .wp-full-overlay-main {
				margin: auto 0 auto -384px;
				width: 768px;
			}
			.rtl .preview-tablet .wp-full-overlay-main {
				margin-right: -384px;
				margin-left: auto;
			}
		</style>
		<?php
	}
endif;
add_action( 'customize_controls_print_styles', 'reycore_customizer__print_css' );


if(!function_exists('reycore_customizer__help_link')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__help_link( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'url' => '',
			'priority' => 500,
			'section'     => '',
			'end' => ''
		]);

		$content = '<hr class="--separator --separator-top2x"><h2 class="rey-helpTitle">' . esc_html__('Need help?', 'rey-core') . '</h2>';
		$content .= '<p>'. sprintf( __('Read more about <a href="%s" target="_blank">these options from this panel</a>.', 'rey-core'), $args['url']) . '</p>' . $args['end'];

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'     => 'custom',
			'settings' => 'chelp_' . $args['section'],
			'section'  => $args['section'],
			'priority' => $args['priority'],
			'default'  => $content,
		] );
	}
endif;


if(!function_exists('reycore_customizer__title')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__title( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'title'       => '',
			'description' => '',
			'default'     => '',
			'section'     => '',
			'settings'     => '',
			'type'        => 'custom',
			'size'        => 'md',
			'border'      => 'bottom',
			'border_size' => '1px',
			'style_attr'  => '',
			'color'       => 'inherit',
			'upper'       => false,
		]);

		$classes[] = '--fz-' . $args['size'];
		$classes[] = '--border-' . $args['border'];
		$classes[] = '--color-' . $args['color'];

		if( $args['upper'] ){
			$classes[] = '--upper';
		}

		$content = sprintf('<div class="rey-customizerTitle-wrapper %s" style="%s">', implode(' ', $classes), $args['style_attr']);
		$content .= sprintf('<h2 class="rey-customizerTitle">%s</h2>', $args['title']);
		if( $args['description'] !== '' ){
			$content .= sprintf('<div class="description">%s</div>', $args['description']);
		}
		$content .= '</div>';

		if( $args['default'] === '' ){
			$args['default'] = $content;
		}

		if( $args['settings'] === '' ){
			$args['settings'] = sprintf('title_%s_%s', $args['section'], str_replace('-','_', sanitize_title($args['title'])) );
		}
		$args['description'] = '';

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
endif;


if(!function_exists('reycore_customizer__separator')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__separator( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'section'     => '',
			'id'     => '',
		]);

		ReyCoreKirki::add_field( 'rey_core_kirki', [
			'type'     => 'custom',
			'settings' => 'separator_' . $args['section'] . '_' . $args['id'],
			'section'  => $args['section'],
			'default'  => '<hr class="--separator-simple"/>',
		] );
	}
endif;


if(!function_exists('reycore_customizer__notice')):
	/**
	 * Display Help links in Customizer's panels
	 *
	 * @since 1.0.0
	 */
	function reycore_customizer__notice( $args = [] ){

		$args = reycore__wp_parse_args($args, [
			'type'        => 'custom',
			'default'     => '',
			'section'     => '',
			'notice_type'     => 'warning',
		]);

		if( $args['default'] === '' ){
			return;
		}

		$text = $args['default'];
		$slug = substr( strip_tags($text), 0, 15);

		$args['default'] = sprintf( '<p class="rey-cstInfo --%2$s">%1$s</p>', $text, $args['notice_type'] );
		$args['settings'] = sprintf('notice_%s_%s', $args['section'], str_replace('-','_', sanitize_title($slug)) );

		reycore_customizer__add_field( $args );
	}
endif;


if(!function_exists('reycore_customizer__title_tooltip')):
	/**
	 * Shows title with help tip
	 *
	 * @since 1.4.2
	 **/
	function reycore_customizer__title_tooltip( $title, $help = '', $args = [] )
	{
		$args = wp_parse_args($args, [
			'tip' => '?',
			'style' => 'qmark',
			'size' => '',
			'clickable' => false
		]);

		return sprintf('<div class="rey-csTitleHelp-popWrapper">
				<span class="rey-csTitleHelp-title">%1$s</span>
				<div class="rey-csTitleHelp-pop --pop-%4$s">
					<span class="rey-csTitleHelp-label">%2$s</span>
					<span class="rey-csTitleHelp-content %6$s" style="%5$s">%3$s</span>
				</div>
			</div>',
			$title,
			$args['tip'],
			$help,
			esc_attr($args['style']),
			!empty($args['size']) ? sprintf('min-width: %spx;', absint($args['size'])) : '',
			$args['clickable'] ? '' : '--pevn'
		);
	}
endif;


add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_Group_Start_Control extends Kirki_Control_Base {
		public $type = 'rey_group_start';
		public function render_content() {
			if( !empty($this->label) ){
				printf('<h4>%s</h4>', $this->label);
			}
		}
	}
	class Kirki_Controls_Group_End_Control extends Kirki_Control_Base {
		public $type = 'rey_group_end';
		public function render_content() {}
	}
	// Register our custom control with Kirki
	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey_group_start'] = 'Kirki_Controls_Group_Start_Control';
		$controls['rey_group_end'] = 'Kirki_Controls_Group_End_Control';
		return $controls;
	} );

} );

if(!function_exists('reycore__customizer_group_start')):
/**
 * Add field group start automatically
 *
 * @since 1.3.0
 **/
function reycore__customizer_group_start($setting, $args)
{
	if( isset($args['rey_group_start']) ){

		if( $args['rey_group_start'] === true ){
			unset($args['label']);
		}

		$args = reycore__wp_parse_args($args['rey_group_start'], $args);

		$args['type'] = 'rey_group_start';
		$args['settings'] = $args['settings'] . '__start';

		if( isset($args['rey_group_start']['active_callback']) ){
			$args['active_callback'] = $args['rey_group_start']['active_callback'];
		}

		unset($args['description']);
		unset($args['tooltip']);
		unset($args['rey_group_start']);
		unset($args['rey_group_end']);
		unset($args['responsive']);
		unset($args['output']);

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
}
endif;

add_action('reycore/kirki_fields/before_field', 'reycore__customizer_group_start', 10, 2);


if(!function_exists('reycore__customizer_group_end')):
/**
 * Add field group end automatically
 *
 * @since 1.3.0
 **/
function reycore__customizer_group_end($setting, $args)
{
	if( isset($args['rey_group_end']) ){

		$args = reycore__wp_parse_args($args['rey_group_end'], $args);

		$args['type'] = 'rey_group_end';
		$args['settings'] = $args['settings'] . '__end';

		unset($args['rey_group_start']);
		unset($args['rey_group_end']);
		unset($args['label']);
		unset($args['description']);
		unset($args['tooltip']);
		unset($args['active_callback']);
		unset($args['output']);
		unset($args['responsive']);

		ReyCoreKirki::add_field( 'rey_core_kirki', $args );
	}
}
endif;

add_action('reycore/kirki_fields/after_field', 'reycore__customizer_group_end', 10, 2);


if(!function_exists('reycore__customizer_responsive')):
	/**
	 * Add responsive fields
	 *
	 * @since 1.3.5
	 **/
	function reycore__customizer_responsive($setting, $args)
	{
		if( isset($args['responsive']) && $args['responsive'] === true ){

			$setting = $args['settings'];

			add_action( 'customize_render_control_' . $setting, function( $customizer ) {
				$customizer->json['is_responsive'] = 'desktop';
			} );

			// avoid loophole
			unset($args['rey_group_start']);
			unset($args['rey_group_end']);
			unset($args['responsive']);

			$breakpoints = [
				'tablet', 'mobile'
			];

			$mq = [
				'tablet'	=> '@media (min-width: 768px) and (max-width: 1025px)',
				'mobile'	=> '@media (max-width: 767px)',
			];

			foreach($breakpoints as $breakpoint){

				add_action("reycore/kirki_fields/after_field=" . $setting , function() use ($args, $breakpoint, $setting, $mq){

					// assign media queries
					if( isset($args['output']) ){
						foreach($args['output'] as $i => $rule){
							$args['output'][$i]['media_query'] = $mq[$breakpoint];
						}
					}

					// create setting name per device
					$args['settings'] = sprintf( '%s_%s', $setting, $breakpoint);

					// pass JSON attribute
					add_action( 'customize_render_control_' . $args['settings'], function( $customizer ) use ($breakpoint) {
						$customizer->json['is_responsive'] = $breakpoint;
					} );

					// render new device fields
					ReyCoreKirki::add_field( 'rey_core_kirki', $args );
				});

			}
		}
	}
endif;
add_action('reycore/kirki_fields/before_field', 'reycore__customizer_responsive', 10, 2);


if(!function_exists('reycore__customizer_responsive_handlers')):
	/**
	 * Add responsive handlers
	 *
	 * @since 1.3.5
	 **/
	function reycore__customizer_responsive_handlers()
	{ ?>

		<script type="text/html" id="tmpl-rey-customizer-responsive-handler">
			<div class="rey-cst-responsiveHandlers">
				<span data-breakpoint="desktop"><i class="dashicons dashicons-desktop"></i></span>
				<span data-breakpoint="tablet"><i class="dashicons dashicons-tablet"></i></span>
				<span data-breakpoint="mobile"><i class="dashicons dashicons-smartphone"></i></span>
			</div>
		</script>

		<script type="text/html" id="tmpl-rey-customizer-typo-handler">
			<div class="rey-cstTypo-wrapper">
				<span class="rey-cstTypo-btn">
					<span class="dashicons dashicons-edit"></span>
					<span class="rey-cstTypo-ff">{{ data.ff }}</span>
					<span class="rey-cstTypo-fz">{{ data.fz }}</span>
					<span class="rey-cstTypo-fw">{{ data.fw }}</span>
				</span>
			</div>
		</script>
		<?php
	}
endif;
add_action( 'customize_controls_print_scripts', 'reycore__customizer_responsive_handlers' );


if(!function_exists('reycore_customizer__wc_taxonomies')):
	/**
	 * Get WooCommerce taxonomies
	 *
	 * @since 1.6.x
	 **/
	function reycore_customizer__wc_taxonomies( $args = [] )
	{
		$args = wp_parse_args($args, [
			'exclude' => []
		]);

		$wc_taxonomy_attributes = [
			'product_cat' => esc_html__( 'Product Catagories', 'rey-core' ),
			'product_tag' => esc_html__( 'Product Tags', 'rey-core' ),
		];

		if( !function_exists('wc_get_attribute_taxonomies') ){
			return $wc_taxonomy_attributes;
		}

		foreach( wc_get_attribute_taxonomies() as $attribute ) {
			$attribute_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
			$wc_taxonomy_attributes[$attribute_name] = $attribute->attribute_label;
		}

		if( !empty($args['exclude']) ){
			foreach ($args['exclude'] as $to_exclude) {
				unset($wc_taxonomy_attributes[$to_exclude]);
			}
		}

		return $wc_taxonomy_attributes;
	}
endif;


if(!function_exists('reycore_customizer__presets')):
/**
 * Presets
 *
 * @since 1.9.4
 **/
function reycore_customizer__presets()
{
	return [
		'london' => [
			'title' => esc_html__( 'London Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'fullscreen',
					'single_skin_default_flip' => false,
					'summary_size' => 45,
					'product_page_summary_fixed' => true,
					'product_gallery_layout' => 'cascade',
					'product_page_gallery_zoom' => true,
					'single_skin_fullscreen_stretch_gallery' => true,
					'single_skin_cascade_bullets' => true,
				],
				'catalog' => [],
			],
		],
		'valencia' => [
			'title' => esc_html__( 'Valencia Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'compact',
					'single_skin_default_flip' => false,
					'summary_size' => 36,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'grid',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'amsterdam' => [
			'title' => esc_html__( 'Amsterdam Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'fullscreen',
					'single_skin_default_flip' => false,
					'summary_size' => 40,
					'product_page_summary_fixed' => true,
					'product_gallery_layout' => 'vertical',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'newyork' => [
			'title' => esc_html__( 'New York Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'default',
					'single_skin_default_flip' => false,
					'summary_size' => 40,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'grid',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'tokyo' => [
			'title' => esc_html__( 'Tokyo Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'default',
					'single_skin_default_flip' => false,
					'summary_size' => 44,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'vertical',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'beijing' => [
			'title' => esc_html__( 'Beijing Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'default',
					'single_skin_default_flip' => false,
					'summary_size' => 44,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'horizontal',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'milano' => [
			'title' => esc_html__( 'Milano Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'default',
					'single_skin_default_flip' => false,
					'summary_size' => 38,
					'product_page_summary_fixed' => true,
					'product_gallery_layout' => 'cascade-scattered',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'melbourne' => [
			'title' => esc_html__( 'Melbourne Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'default',
					'single_skin_default_flip' => false,
					'summary_size' => 45,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'vertical',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'paris' => [
			'title' => esc_html__( 'Paris Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'fullscreen',
					'single_skin_default_flip' => false,
					'summary_size' => 40,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'vertical',
					'product_page_gallery_zoom' => true,
				],
				'catalog' => [],
			],
		],
		'stockholm' => [
			'title' => esc_html__( 'Stockholm Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'compact',
					'single_skin_default_flip' => false,
					'summary_size' => 36,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'cascade',
					'product_page_gallery_zoom' => true,
					'product_content_layout' => 'blocks',
				],
				'catalog' => [],
			],
		],
		'frankfurt' => [
			'title' => esc_html__( 'Frankfurt Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'default',
					'single_skin_default_flip' => false,
					'summary_size' => 45,
					'summary_padding' => 50,
					'summary_bg_color' => '#ebf5fb',
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'vertical',
					'product_page_gallery_zoom' => true,
					'product_content_layout' => 'blocks',
				],
				'catalog' => [],
			],
		],
		'athens' => [
			'title' => esc_html__( 'Athens Demo', 'rey-core' ),
			'settings' => [
				'page' => [
					'single_skin' => 'default',
					'single_skin_default_flip' => false,
					'summary_size' => 36,
					'summary_padding' => 0,
					'product_page_summary_fixed' => false,
					'product_gallery_layout' => 'vertical',
					'product_page_gallery_zoom' => true,
					'product_content_layout' => 'blocks',
				],

			],
		],
	];
}
endif;

if(!function_exists('reycore_customizer__get_settings')):
	function reycore_customizer__get_settings( $theme_name ){

		if ( false === ($mods = get_option( "theme_mods_$theme_name" )) ) {
			$mods = get_option( "mods_$theme_name" ); // Deprecated location.
			if ( is_admin() && false !== $mods ) {
				update_option( "theme_mods_$theme_slug", $mods );
				delete_option( "mods_$theme_name" );
			}
		}

		$data = [
			'template' => $theme_name,
			'mods'     => $mods,
			'options'  => []
		];

		global $wp_customize;

		if( $wp_customize ):

			// Get options from the Customizer API.
			$settings = $wp_customize->settings();

			foreach ( $settings as $key => $setting ) {

				if ( 'option' == $setting->type ) {

					// Don't save widget data.
					if ( 'widget_' === substr( strtolower( $key ), 0, 7 ) ) {
						continue;
					}

					// Don't save sidebar data.
					if ( 'sidebars_' === substr( strtolower( $key ), 0, 9 ) ) {
						continue;
					}

					// Don't save core options.
					if ( in_array( $key, [
						'blogname',
						'blogdescription',
						'show_on_front',
						'page_on_front',
						'page_for_posts',
					] ) ) {
						continue;
					}

					$data['options'][ $key ] = $setting->value();
				}
			}

		endif;

		// Plugin developers can specify additional option keys to export.
		$option_keys = apply_filters( 'reycore/transfer_mods_option_keys', [] );

		foreach ( $option_keys as $option_key ) {
			$data['options'][ $option_key ] = get_option( $option_key );
		}

		if( function_exists( 'wp_get_custom_css_post' ) ) {
			$data['wp_css'] = wp_get_custom_css( $theme_name );
		}

		return $data;
	}
endif;


if(!function_exists('reycore_customizer__transfer_theme_settings')):
/**
 * Copy settings from child to parent & viceversa
 *
 * @since 2.0.5
 **/
function reycore_customizer__transfer_theme_settings()
{
	if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
		wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
	}

	if( ! isset($_POST['type']) ){
		wp_send_json_error( 'No type set!' );
	}

	if( ! ($type = reycore__clean($_POST['type'])) ){
		wp_send_json_error( 'No type set!' );
	}

	$success = false;

	if( $type === 'parent' ){
		$theme_name = REY_CORE_THEME_NAME;
	}
	else if( $type === 'child' ){
		$theme_name = 'rey-child';
	}

	if( ($theme = wp_get_theme($theme_name)) && isset($theme['errors']) && !empty($theme['errors']) ){
		wp_send_json_error( 'Couldn\'t find theme' );
	}

	$data = reycore_customizer__get_settings( $theme_name );

	global $wp_customize;

	// Import custom options.
	if ( class_exists('CEI_Option') && isset( $data['options'] ) ) {

		foreach ( $data['options'] as $option_key => $option_value ) {

			$option = new CEI_Option( $wp_customize, $option_key, array(
				'default'		=> '',
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options'
			) );

			$option->import( $option_value );
		}
	}

	// If wp_css is set then import it.
	if( function_exists( 'wp_update_custom_css_post' ) && isset( $data['wp_css'] ) && '' !== $data['wp_css'] ) {
		wp_update_custom_css_post( $data['wp_css'] );
	}

	if( isset($data['mods']) && ! empty($data['mods']) ){

		if( $wp_customize ){
			// Call the customize_save action.
			do_action( 'customize_save', $wp_customize );
		}

		// Loop through the mods.
		foreach ( $data['mods'] as $key => $val ) {

			if( $wp_customize ){
				// Call the customize_save_ dynamic action.
				do_action( 'customize_save_' . $key, $wp_customize );
			}

			// Save the mod.
			set_theme_mod( $key, $val );
		}

		if( $wp_customize ){
			// Call the customize_save_after action.
			do_action( 'customize_save_after', $wp_customize );
		}
	}

	wp_send_json_success();

}
add_action('wp_ajax_rey_transfer_mods', 'reycore_customizer__transfer_theme_settings');
endif;
