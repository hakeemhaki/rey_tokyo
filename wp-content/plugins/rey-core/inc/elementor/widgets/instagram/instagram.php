<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('Wpzoom_Instagram_Widget_API') && !class_exists('ReyCore_Widget_Instagram') ):

/**
 *
 * Elementor widget.
 *
 * @since 1.0.0
 */
class ReyCore_Widget_Instagram extends \Elementor\Widget_Base {

	public $_items = [];
	public $_errors = [];
	public $_settings = [];

	public function get_name() {
		return 'reycore-instagram';
	}

	public function get_title() {
		return __( 'Instagram', 'rey-core' );
	}

	public function get_icon() {
		return 'fa fa-instagram';
	}

	public function get_categories() {
		return [ 'rey-theme' ];
	}

	public function rey_get_script_depends() {
		return [ 'masonry', 'imagesloaded', 'scroll-out', 'reycore-widget-instagram-scripts' ];
	}

	protected function _register_skins() {
		$this->add_skin( new ReyCore_Widget_Instagram__Shuffle( $this ) );
	}

	public function get_custom_help_url() {
		return 'https://support.reytheme.com/kb/rey-elements/#instagram';
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
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
			]
		);

		if ( empty( $this->_items ) ) {
			/* translators: 1: Instagram Settings Page link */
			$html = sprintf( __( 'No Instagram posts found. If you have just installed or updated this plugin, please go to the <a href="%s" target="_blank">Settings page</a> and <strong>connect</strong> it with your Instagram account.', 'rey-core' ), admin_url('options-general.php?page=wpzoom-instagram-widget') );
			$content_classes = 'elementor-panel-alert elementor-panel-alert-danger';
		} else {
			$html = __( 'Your account is connected.', 'rey-core' );
			$content_classes = 'elementor-descriptor';
		}

		$this->add_control(
			'connect_msg',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => $html,
				'content_classes' => $content_classes,
			]
		);

		$this->add_control(
			'limit',
			[
				'label' => __( 'Limit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 20,
				'step' => 1,
			]
		);

		$this->add_control(
			'per_row',
			[
				'label' => __( 'Items per row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 7,
				'step' => 1,
			]
		);

		$this->add_control(
			'img_size',
			[
				'label' => __( 'Image Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'low_resolution',
				'options' => [
					'thumbnail'  => __( 'Thumbnail ( 150x150px )', 'rey-core' ),
					'low_resolution'  => __( 'Low Resolution ( 320x320px )', 'rey-core' ),
					'standard_resolution'  => __( 'Standard Resolution ( 640x640px )', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'spacing',
			[
				'label' => __( 'Spacing Gap', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'rey-core' ),
					'no' => __( 'No Spacing', 'rey-core' ),
					'narrow' => __( 'Narrow', 'rey-core' ),
					'extended' => __( 'Extended', 'rey-core' ),
					'wide' => __( 'Wide', 'rey-core' ),
					'wider' => __( 'Wider', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link To', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'insta',
				'options' => [
					'insta'  => __( 'Instagram Page', 'rey-core' ),
					'image'  => __( 'Image Lightbox', 'rey-core' ),
					'url'  => __( 'Caption URL', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'caption_url_target',
			[
				'label' => __( 'Caption URL Target', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '_self',
				'options' => [
					'_self'  => __( 'Same window', 'rey-core' ),
					'_blank'  => __( 'New window', 'rey-core' ),
				],
				'condition' => [
					'link' => ['url'],
				],
			]
		);

		if ( empty( $this->_items ) ) {

			$this->add_control(
				'demo_items',
				[
					'label' => esc_html__( 'Items JSON', 'rey-core' ),
					'description' => esc_html__( 'Mostly used for demo purposes. This control is used when you don\'t have an Instagram account connected', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
					'placeholder' => '{ .. }',
				]
			);
		}

		$this->add_control(
			'delay_init',
			[
				'label' => esc_html__( 'Delay Init', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 30000,
				'step' => 50,
			]
		);

		$this->add_control(
			'lazy_load',
			[
				'label' => esc_html__( 'Lazy Load', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
			] );

		$this->add_control(
			'lazy_load_trigger',
			[
				'label' => esc_html__( 'Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'scroll',
				'options' => [
					'scroll'  => esc_html__( 'On Scroll', 'rey-core' ),
					'click'  => esc_html__( 'On Click', 'rey-core' ),
					'mega-menu'  => esc_html__( 'On Mega Menu display', 'rey-core' ),
				],
				'condition' => [
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_click_trigger',
			[
				'label' => esc_html__( 'Click Selector', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: .custom-unique-selector', 'rey-core' ),
				'condition' => [
					'lazy_load!' => '',
					'lazy_load_trigger' => 'click',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'top_spacing',
			[
				'label'       => esc_html__( 'Top-Spacing Items', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 2, 3, 4',
				'description' => __( 'Adds a top-spacing margin for specific items. Add item index number separated by comma.', 'rey-core' ),
				'condition' => [
					'_skin' => [''],
				],
			]
		);

		// Shuffled
		$this->add_control(
			'enable_box',
			[
				'label' => __( 'Display Username Box', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_text',
			[
				'label'       => __( 'Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '', 'rey-core' ),
				'description' => __( 'Leave empty for username.', 'rey-core' ),
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_url',
			[
				'label'       => __( 'URL', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '', 'rey-core' ),
				'description' => __( 'Leave empty for your Instagram profile.', 'rey-core' ),
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_position',
			[
				'label' => __( 'Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 20,
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta-shuffleItem a' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'box_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-elInsta-shuffleItem a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->add_control(
			'hide_box_mobile',
			[
				'label' => __( 'Hide Username Box on Mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => ['shuffle'],
				],
			]
		);

		$this->end_controls_section();
	}

	public function image_sizes(){
		return [
			'thumbnail' => 150,
			'low_resolution' => 320,
			'standard_resolution' => 640,
		];
	}

	/**
	 * WPZoom Instagram Plugin's `set_images_to_transient` is protected
	 */
	public function wpzoom__set_images_to_transient( $attachment_id, $media_id, $uploaderClass ){

		$wpZoomInstance = $uploaderClass::getInstance();
		$transient = $wpZoomInstance->get_api_transient();

		if ( ! empty( $transient->data ) ) {
			foreach ( $transient->data as $key => $item ) {
				if ( $item->id === $media_id ) {
					$thumbnail                         = wp_get_attachment_image_src( $attachment_id, $uploaderClass::get_image_size_name( 'thumbnail' ) );
					$low_resolution                    = wp_get_attachment_image_src( $attachment_id, $uploaderClass::get_image_size_name( 'low_resolution' ) );
					$standard_resolution               = wp_get_attachment_image_src( $attachment_id, $uploaderClass::get_image_size_name( 'standard_resolution' ) );
					$item->images->thumbnail->url      = ! empty( $thumbnail ) ? $thumbnail[0] : '';
					$item->images->low_resolution->url = ! empty( $low_resolution ) ? $low_resolution[0] : '';;
					$item->images->standard_resolution->url = ! empty( $standard_resolution ) ? $standard_resolution[0] : '';;

					$transient->data[ $key ] = $item;
				}
			}

			$wpZoomInstance->set_api_transient( $transient );
		}

	}

	/**
	 * WPZoom Instagram Plugin's `get_best_size` is protected
	 */
	public function wpzoom__get_best_size($desired_width, $image_resolution = 'default_algorithm' ) {

		$size = 'thumbnail';

		$sizes = $this->image_sizes();

		$diff = PHP_INT_MAX;

		if ( array_key_exists( $image_resolution, $sizes ) ) {
			return $image_resolution;
		}

		foreach ( $sizes as $key => $value ) {
			if ( abs( $desired_width - $value ) < $diff ) {
				$size = $key;
				$diff = abs( $desired_width - $value );
			}
		}

		return $size;
	}

	public function query_items() {

		$img_sizes = $this->image_sizes();
		$api = Wpzoom_Instagram_Widget_API::getInstance();

		$def_items = [];

		if( isset($this->_settings['demo_items']) && !empty($this->_settings['demo_items']) && ( $settings_demo_items = json_decode($this->_settings['demo_items'], true) ) ){
			$def_items = $settings_demo_items;
		}

		// used for demos
		$this->_items = apply_filters('reycore/elementor/instagram/data', $def_items, $this->get_id() );

		if( isset($this->_items['items']) ){
			$this->_items['items'] = array_slice($this->_items['items'], 0, $this->_settings['limit']);
		}

		if( $api->is_configured() ) {

			$args = [
				'image-limit' => $this->_settings['limit'],
				'image-width' => $img_sizes[$this->_settings['img_size']],
				'image-resolution' => $this->_settings['img_size'],
			];

			$insta_items = $api->get_items($args);

			if( $insta_items ){

				$wpzoom_uploaderClass = 'WPZOOM_Instagram_Image_Uploader';
				$wpzoom_uploader = $wpzoom_uploaderClass::getInstance();

				if( isset($insta_items['items']) && class_exists($wpzoom_uploaderClass) ){

					$wpzoom_media_metakey_name = 'wpzoom_instagram_media_id';
					$wpzoom_post_status_name = 'wpzoom-hidden';

					foreach ($insta_items['items'] as $key => $item) {

						if( $item['image-url'] === false ){

							$media_url = $wpzoom_uploaderClass::get_media_url_by_id( $item['image-id'] );

							$query = new WP_Query( [
								'post_type'      => 'attachment',
								'posts_per_page' => 1,
								'post_status'    => $wpzoom_post_status_name,
								'meta_query'     => [
									[
										'key'   => $wpzoom_media_metakey_name,
										'value' => $item['image-id'],
									],
								],
							] );

							if ( $query->have_posts() ) {
								$post          = array_shift( $query->posts );
								$attachment_id = $post->ID;
							} else {
								$attachment_id = $wpzoom_uploaderClass::upload_image( $media_url, $item['image-id'] );
							}

							$this->wpzoom__set_images_to_transient( $attachment_id, $item['image-id'], $wpzoom_uploaderClass );

							if ( ! is_wp_error( $attachment_id ) ) {

								$media_size = $this->wpzoom__get_best_size( $args['image-width'], $args['image-resolution'] );

								$image_src = wp_get_attachment_image_src( $attachment_id, $wpzoom_uploaderClass::get_image_size_name( $media_size ) );

								$item['image-url'] = ! empty( $image_src ) ? $image_src[0] : $media_url;

								$this->_items['items'][$key] = $item;
							}

						}
						else {
							$this->_items['items'][$key] = $item;
						}
					}
				}

				if( isset($insta_items['username']) ){
					$this->_items['username'] = $insta_items['username'];
				}

			}
			else {
				$this->_errors = $api->errors->get_error_messages();
			}
		}

	}

	/**
	 * Output errors if widget is misconfigured and current user can manage options (plugin settings).
	 *
	 * @return void
	 */
	protected function display_errors($errors = []) {

		if ( current_user_can( 'edit_theme_options' ) ) {
			?>
			<p class="text-center">
				<?php _e( 'Instagram Widget misconfigured, check plugin &amp; widget settings.', 'rey-core' ); ?>
			</p>

            <?php if ( ! empty( $errors ) ): ?>
                <ul>
					<?php foreach ( $errors as $error ): ?>
                        <li class="text-center"><?php echo $error; ?></li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
		<?php
		} else {
			echo "&#8230;";
		}
	}

	public function get_insta_items() {
		return $this->_items;
	}

	public function get_url($item = []) {
		if( empty($item) ) {
			return;
		}

		// Default Instagram URL
		$url = [
			'url' => $item['link'],
			'attr' => 'target="_blank"'
		];

		// Instagram IMAGE
		if( 'image' == $this->_settings['link'] ){
			$url = [
				'url' => $item['image-url'],
				'attr' => 'data-elementor-open-lightbox="yes"'
			];
		}

		// Instagram Caption URL
		// gets first link, if not, get default
		elseif( 'url' == $this->_settings['link'] ){
			$matches = [];

			$regex = '/https?\:\/\/[^\" ]+/i';

			if( isset($item['image-caption']) && ($caption = $item['image-caption']) ){
				preg_match($regex, $caption, $matches);
			}

			if( !empty($matches) ){
				$url = [
					'url' => $matches[0],
					'attr' => 'data-caption-url target="'. $this->_settings['caption_url_target'] .'"'
				];
			}
		}

		return $url;
	}

	public function render_items(){

		if( isset($this->_items['items']) && !empty($this->_items['items']) ){

			$top_spacing = array_map( 'trim', explode( ',', $this->_settings['top_spacing'] ) );
			// $anim_class =  !\Elementor\Plugin::$instance->editor->is_edit_mode() ? 'rey-elInsta-item--animated': '';
			$anim_class =  '';

			foreach ($this->_items['items'] as $key => $item) {

				$link = $this->get_url($item);

				echo '<div class="rey-elInsta-item rey-gapItem '. ( in_array( ($key + 1), $top_spacing) ? '--spaced' : '' ) . $anim_class .'">';
					echo '<a href="'. $link['url'] .'" rel="noreferrer" class="rey-instaItem-link" title="'. $item['image-caption'] .'" '. $link['attr'] .'>';

						printf( '<img src="%s" alt="%s" class="rey-instaItem-img">',
							$item['image-url'],
							$item['image-caption']
						);
					echo '</a>';
				echo '</div>';
			}
		}
	}

	public function render_start(){

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-elInsta clearfix',
			'rey-elInsta--skin-' . ($this->_settings['_skin'] ? $this->_settings['_skin'] : 'default'),
			'rey-gap--' . $this->_settings['spacing']
		] );

		$this->add_render_attribute( 'wrapper', 'data-per-row', $this->_settings['per_row'] );
		$this->add_render_attribute( 'wrapper', 'data-delay', $this->_settings['delay_init'] );
		$this->add_render_attribute( 'wrapper', 'data-image-size', $this->_settings['img_size'] );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php
		if ( empty( $this->_items ) ) {
			/* translators: 1: Instagram Settings Page link */
			printf(
				__( '<p class="text-center">No Instagram posts found. If you have just installed or updated this plugin, please go to the <a href="%s" target="_blank">Settings page</a> and <strong>connect</strong> it with your Instagram account.</p>', 'rey-core' ),
				admin_url('options-general.php?page=wpzoom-instagram-widget')
			);

			$this->display_errors($this->_errors);
		}

	}

	public function render_end(){
		?>
		</div>
		<?php
	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();

		if( $this->lazy_start() ){
			return;
		}

		reyCoreAssets()->add_styles(['reycore-widget-instagram-styles']);
		reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

		$this->query_items();
		$this->render_start();
		$this->render_items();
		$this->render_end();

		$this->lazy_end();
	}

	function lazy_start(){

		$is_ajax_request = (isset($_REQUEST['action']) && 'reycore_element_lazy' === reycore__clean($_REQUEST['action']));

		if( $is_ajax_request ){
			reyCoreAssets()->collect_start();
		}

		if( ! isset($this->_settings['lazy_load']) ){
			return;
		}

		// Initial Load (not Ajax)
		if( '' !== $this->_settings['lazy_load'] &&
			! wp_doing_ajax() &&
			! ( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) ){

			$qid = isset($GLOBALS['global_section_id']) ? $GLOBALS['global_section_id'] : get_queried_object_id();

			$config = [
				'element_id' => $this->get_id(),
				'skin' => $this->_settings['_skin'],
				'trigger' => $this->_settings['lazy_load_trigger'] ? $this->_settings['lazy_load_trigger'] : 'scroll',
				'qid' => apply_filters('reycore/elementor/instagram/lazy_load_qid', $qid),
				'options' => apply_filters('reycore/elementor/instagram/lazy_load_options', [])
			];

			if( 'click' === $this->_settings['lazy_load_trigger'] ){
				$config['trigger__click'] = $this->_settings['lazy_load_click_trigger'];
			}

			$this->add_render_attribute( '_wrapper', 'data-lazy-load', wp_json_encode( $config ) );

			$per_row = $this->_settings['per_row'];

			echo '<div class="__lazy-loader"></div>';

			$scripts = ['reycore-elementor-elem-lazy-load', 'reycore-widget-instagram-scripts'];

			if( 'scroll' === $this->_settings['lazy_load_trigger'] ){
				$scripts[] = 'scroll-out';
			}

			if( ! empty($scripts) ){
				reyCoreAssets()->add_scripts($scripts);
			}

			do_action('reycore/elementor/instagram/lazy_load_assets', $this->_settings);

			return true;
		}

		return false;
	}

	function lazy_end(){

		if( ! (isset($_REQUEST['action']) && 'reycore_element_lazy' === reycore__clean($_REQUEST['action'])) ){
			return;
		}

		$collected_assets = reyCoreAssets()->collect_end(true);

		if( !empty($collected_assets) ){
			printf( "<div data-assets='%s'></div>", wp_json_encode($collected_assets) );
		}

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
