<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Menu__Custom_Items') ):

	class ReyCore_Widget_Menu__Custom_Items extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'custom-items';
		}

		public function get_title() {
			return __( 'Custom Items', 'rey-core' );
		}

		protected function _register_controls_actions() {
			parent::_register_controls_actions();

			add_action( 'elementor/element/reycore-menu/section_settings/before_section_end', [ $this, 'register_items_controls' ] );
		}

		public function register_items_controls( $element ){

			$items = new \Elementor\Repeater();

			$items->add_control(
				'title',
				[
					'label'       => __( 'Title', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
				]
			);

			$items->add_control(
				'link',
				[
					'label' => __( 'Link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
					'default' => [
						'url' => '#',
					],
				]
			);

			$items->add_control(
				'extra_content',
				[
					'label' => esc_html__( 'Extra content', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'None', 'rey-core' ),
						'image'  => esc_html__( 'Image', 'rey-core' ),
						'icon'  => esc_html__( 'Icon', 'rey-core' ),
					],
				]
			);

			$items->add_control(
				'image',
				[
				   'label' => esc_html__( 'Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [],
					'conditions' => [
						'terms' => [
							[
								'name' => 'extra_content',
								'operator' => '==',
								'value' => 'image',
							],
						],
					],
				]
			);

			$items->add_control(
				'icon',
				[
					'label' => __( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'default' => [
						'value' => 'fas fa-plus',
						'library' => 'fa-solid',
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'extra_content',
								'operator' => '==',
								'value' => 'icon',
							],
						],
					],
				]
			);

			$element->add_control(
				'custom_items',
				[
					'label' => __( 'Items', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $items->get_controls(),
					'default' => [
						[
							'title' => __( 'Link #1', 'rey-core' ),
							'button_url' => [
								'url' => '#',
							],
						],
						[
							'title' => __( 'Link #2', 'rey-core' ),
							'button_url' => [
								'url' => '#',
							],
						],
					],
					'condition' => [
						'_skin' => 'custom-items',
					],
				]
			);

		}


		public function render_menu($settings)
		{
			if( !empty($settings['custom_items']) ){

				echo '<div class="reyEl-menu-navWrapper">';

					printf('<ul class="reyEl-menu-nav rey-navEl --menuHover-%s">', $settings['hover_style']);

					foreach ($settings['custom_items'] as $i => $item) {

						if( ! isset($item['link']['url']) ){
							continue;
						}

						$is_active = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" === $item['link']['url'];

						$attributes = '';

						if( $item['link']['nofollow'] ){
							$attributes .= ' rel="nofollow"';
						}

						$extra_content = '';

						if( $settings['icons_visibility'] === 'yes' && isset($item['extra_content']) && $setting__extra_content = $item['extra_content']){

							if( $setting__extra_content === 'icon' && isset($item['icon']) && ($icon = $item['icon']) ){

								ob_start();
								\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true', 'class' => 'rey-customIcon' ] );
								$extra_content = ob_get_clean();

								$attributes .= ' data-has-icon';
							}
							elseif( $setting__extra_content === 'image' && isset($item['image']) && ($image = $item['image']) ){

								$extra_content = reycore__get_attachment_image( [
									'image' => $image,
									'size' => 'thumbnail',
									'attributes' => [
										'class' => 'rey-customIcon'
									]
								] );

								$attributes .= ' data-has-icon';
							}
						}

						printf(
							'<li class="menu-item %4$s"><a class="" href="%2$s" target="%3$s" %5$s>%6$s<span>%1$s</span></a></li>',
							$item['title'],
							$item['link']['url'],
							($item['link']['is_external'] ? "_blank" : "_self"),
							($is_active ? 'current-menu-item' : ''),
							$attributes,
							$extra_content
						);
					}

					echo '</ul>';
				echo '</div>';
			}
		}

		public function render() {

			reyCoreAssets()->add_styles(['reycore-widget-menu-styles']);

			$settings = $this->parent->get_settings_for_display();

			$this->parent->render_start($settings);
			$this->parent->render_title($settings);
			$this->render_menu($settings);
			$this->parent->render_end();
		}
	}
endif;
