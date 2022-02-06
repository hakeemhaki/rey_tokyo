<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('\Elementor\Skin_Base') && !class_exists('ReyCore_ImageCarouselLinks_Skin') ):
	/**
	 * Image Carousel with Links
	 *
	 * @since 1.0.0
	 */
	class ReyCore_ImageCarouselLinks_Skin extends \Elementor\Skin_Base {

		public function __construct( \Elementor\Widget_Base $parent ) {
			parent::__construct( $parent );
		}

		public function get_id() {
			return 'carousel_links';
		}

		public function get_title() {
			return __( 'Carousel with links', 'rey-core' );
		}

		public function render() {

			$settings = $this->parent->get_settings_for_display();

			if ( empty( $settings['rey_items'] ) ) {
				return;
			}

			$slides = [];

			foreach ( $settings['rey_items'] as $index => $item ) {

				$image_url = \Elementor\Group_Control_Image_Size::get_attachment_image_src( $item['image']['id'], 'thumbnail', $settings );

				$image_html = '<img class="swiper-slide-image" src="' . esc_attr( $image_url ) . '" alt="' . esc_attr( \Elementor\Control_Media::get_image_alt( $item['image'] ) ) . '" />';

				$link_tag = '';

				$link = $item['link'];

				if ( $link ) {
					$link_key = 'link_' . $index;

					$this->parent->add_link_attributes( $link_key, $link );

					$link_tag = '<a ' . $this->parent->get_render_attribute_string( $link_key ) . '>';
				}

				$image_caption = $this->__get_image_caption( $item['image'] );

				$slide_html = '<div class="swiper-slide">' . $link_tag . '<figure class="swiper-slide-inner">' . $image_html;

				if ( ! empty( $image_caption ) ) {
					$slide_html .= '<figcaption class="elementor-image-carousel-caption">' . $image_caption . '</figcaption>';
				}

				$slide_html .= '</figure>';

				if ( $link ) {
					$slide_html .= '</a>';
				}

				$slide_html .= '</div>';

				$slides[] = $slide_html;
			}

			if ( empty( $slides ) ) {
				return;
			}

			$this->parent->add_render_attribute( [
				'carousel' => [
					'class' => 'elementor-image-carousel swiper-wrapper',
				],
				'carousel-wrapper' => [
					'class' => 'elementor-image-carousel-wrapper swiper-container',
					'dir' => $settings['direction'],
				],
			] );

			$show_dots = ( in_array( $settings['navigation'], [ 'dots', 'both' ] ) );
			$show_arrows = ( in_array( $settings['navigation'], [ 'arrows', 'both' ] ) );

			if ( 'yes' === $settings['image_stretch'] ) {
				$this->parent->add_render_attribute( 'carousel', 'class', 'swiper-image-stretch' );
			}

			$slides_count = count( $slides );

			?>
			<div <?php echo $this->parent->get_render_attribute_string( 'carousel-wrapper' ); ?>>
				<div <?php echo $this->parent->get_render_attribute_string( 'carousel' ); ?>>
					<?php echo implode( '', $slides ); ?>
				</div>
				<?php if ( 1 < $slides_count ) : ?>
					<?php if ( $show_dots ) : ?>
						<div class="swiper-pagination"></div>
					<?php endif; ?>
					<?php if ( $show_arrows ) : ?>
						<div class="elementor-swiper-button elementor-swiper-button-prev">
							<i class="eicon-chevron-left" aria-hidden="true"></i>
							<span class="elementor-screen-only"><?php _e( 'Previous', 'elementor' ); ?></span>
						</div>
						<div class="elementor-swiper-button elementor-swiper-button-next">
							<i class="eicon-chevron-right" aria-hidden="true"></i>
							<span class="elementor-screen-only"><?php _e( 'Next', 'elementor' ); ?></span>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php
			reyCoreAssets()->add_scripts('reycore-elementor-elem-carousel-links');
		}


		/**
		 * Retrieve image carousel caption.
		 *
		 * @since 1.2.0
		 * @access public
		 *
		 * @param array $attachment
		 *
		 * @return string The caption of the image.
		 */
		public function __get_image_caption( $attachment ) {
			$caption_type = $this->parent->get_settings_for_display( 'caption_type' );

			if ( empty( $caption_type ) ) {
				return '';
			}

			$attachment_post = get_post( $attachment['id'] );

			if ( 'caption' === $caption_type ) {
				return $attachment_post->post_excerpt;
			}

			if ( 'title' === $caption_type ) {
				return $attachment_post->post_title;
			}

			return $attachment_post->post_content;
		}

	}
endif;
