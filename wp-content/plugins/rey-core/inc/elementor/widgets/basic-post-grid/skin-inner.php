<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Basic_Post_Grid__Inner') ):

	class ReyCore_Widget_Basic_Post_Grid__Inner extends \Elementor\Skin_Base
	{
		private $posts_archive = null;

		public function get_id() {
			return 'inner';
		}

		public function get_title() {
			return __( 'Inner Content', 'rey-core' );
		}

		public function render() {

			$this->parent->_settings = $this->parent->get_settings_for_display();

			if( class_exists('ReyCore_Elementor_Posts') ){
				$this->posts_archive = new ReyCore_Elementor_Posts( [
					'el_instance' => $this->parent,
					'placeholder_class' => '--no-titles'
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
				<div class="reyEl-bPostGrid-innerWrapper">

					<?php $this->parent->render_thumbnail(); ?>

					<div class="reyEl-bPostGrid-inner">

						<?php $this->parent->render_meta(); ?>

						<div class="reyEl-bpost-contentWrap">
							<?php
							$this->parent->render_title();
							$this->parent->render_excerpt(); ?>
						</div>

						<?php $this->parent->render_footer(); ?>

					</div>
				</div>
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
