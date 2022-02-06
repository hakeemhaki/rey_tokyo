<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Basic_Post_Grid__Basic2') ):

	class ReyCore_Widget_Basic_Post_Grid__Basic2 extends \Elementor\Skin_Base
	{
		private $posts_archive = null;

		public function get_id() {
			return 'basic2';
		}

		public function get_title() {
			return __( 'Compact', 'rey-core' );
		}

		/**
		 * Render meta
		 *
		 * @since 1.0.0
		 **/
		public function render_meta()
		{

			if( 'yes' === $this->parent->_settings['meta_author'] || 'yes' === $this->parent->_settings['meta_comments'] ): ?>
				<div class="rey-postInfo">
				<?php
					if( 'yes' === $this->parent->_settings['meta_author'] ){
						if( function_exists('rey__posted_by') ){
							rey__posted_by();
						}
					}
					if( 'yes' === $this->parent->_settings['meta_comments'] ){
						if( function_exists('rey__comment_count') ){
							rey__comment_count();
						}
					}
					if( function_exists('rey__edit_link') ){
						rey__edit_link();
					}
				?>
				</div>
			<?php endif;
		}

		public function render() {

			$this->parent->_settings = $this->parent->get_settings_for_display();

			if( class_exists('ReyCore_Elementor_Posts') ){
				$this->posts_archive = new ReyCore_Elementor_Posts( [
					'el_instance' => $this->parent
				], $this->parent->_settings );
			}

			if( $this->posts_archive && $this->posts_archive->lazy_start() ){
				return;
			}

			reyCoreAssets()->add_styles('reycore-widget-basic-post-grid-styles');

			$this->parent->query_posts();

			if ( ! $this->parent->_query->found_posts ) {
				return;
			}

			$this->parent->render_start();

			while ( $this->parent->_query->have_posts() ) : $this->parent->_query->the_post(); ?>
			<div class="reyEl-bPostGrid-item <?php echo $this->parent->get_classes(); ?>">
				<?php
					$this->parent->render_thumbnail();

					$this->render_meta();

					echo '<div class="basic2-postMeta">';

						if( 'yes' === $this->parent->_settings['meta_date'] ){
							echo sprintf(
								'<span class="rey-entryDate"><time datetime="%1$s">%2$s</time></span>',
								esc_attr(get_the_date(DATE_W3C)),
								esc_html(get_the_date('m / y'))
							);
						}

						$this->parent->render_title();

					echo '</div>';

					$this->parent->render_excerpt();
					$this->parent->render_footer();
				?>
			</div>
			<?php endwhile;
			wp_reset_postdata();

			$this->parent->render_end();

			if( $this->posts_archive ){
				$this->posts_archive->lazy_end();
			}
		}

	}
endif;
