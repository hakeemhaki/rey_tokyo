<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Instagram__Shuffle') ):

	class ReyCore_Widget_Instagram__Shuffle extends \Elementor\Skin_Base
	{
		public $_settings = [];

		public function get_id() {
			return 'shuffle';
		}

		public function get_title() {
			return __( 'Shuffle', 'rey-core' );
		}

		public function rey_get_script_depends() {
			return [ 'masonry', 'imagesloaded', 'scroll-out', 'reycore-widget-instagram-scripts' ];
		}

		public function render_items(){

			if( isset($this->parent->_items['items']) && !empty($this->parent->_items['items']) ){

				$anim_class =  !\Elementor\Plugin::$instance->editor->is_edit_mode() ? 'rey-elInsta-item--animated': '';

				if( 'yes' === $this->parent->_settings['enable_box'] ){

					// box url
					if( !empty($this->parent->_settings['box_url']) ){
						$box_url = $this->parent->_settings['box_url'];
					}
					else {
						$box_url = 'https://www.instagram.com/' . $this->parent->_items['username'];
					}
					// box text
					if( !empty($this->parent->_settings['box_text']) ){
						$box_text = $this->parent->_settings['box_text'];
					}
					else {
						$box_text = $this->parent->_items['username'];
					}

					$hide_mobile = $this->parent->_settings['hide_box_mobile'] === 'yes' ? '--hide-mobile' : '';

					$shuffle_item = '<div class="rey-elInsta-item rey-gapItem rey-elInsta-shuffleItem '. $anim_class .' '. $hide_mobile .'">';
						$shuffle_item .= '<div>';
						$shuffle_item .= '<a href="'. $box_url .'" rel="noreferrer" class="rey-instaItem-link" target="_blank"><span>'.$box_text.'</span></a>';
						$shuffle_item .= '</div>';
					$shuffle_item .= '</div>';
				}

				foreach ($this->parent->_items['items'] as $key => $item) {

					$link = $this->parent->get_url($item);

					if( ($key + 1) == $this->parent->_settings['box_position'] ) {
						echo $shuffle_item;
					}

					echo '<div class="rey-elInsta-item rey-gapItem '. $anim_class .'">';
						echo '<a href="'. $link['url'] .'" class="rey-instaItem-link" title="'. $item['image-caption'] .'" '. $link['attr'] .'>';
							printf( '<img src="%s" alt="%s" class="rey-instaItem-img">',
								$item['image-url'],
								$item['image-caption']
							);
						echo '</a>';
					echo '</div>';
				}

			}
		}

		public function render() {

			$this->parent->_settings = $this->parent->get_settings_for_display();

			if( $this->parent->lazy_start() ){
				return;
			}

			reyCoreAssets()->add_styles(['reycore-widget-instagram-styles']);
			reyCoreAssets()->add_scripts( $this->rey_get_script_depends() );

			$this->parent->query_items();
			$this->parent->render_start();
			$this->render_items();
			$this->parent->render_end();

			$this->parent->lazy_end();

		}
	}
endif;
