<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('ReyCore_Widget_Product_Grid__Mini') ):

	class ReyCore_Widget_Product_Grid__Mini extends \Elementor\Skin_Base
	{

		public function get_id() {
			return 'mini';
		}

		public function get_title() {
			return __( 'Mini Grid', 'rey-core' );
		}

		public function loop_start( $product_archive )
		{
			reyCoreAssets()->add_scripts( $this->parent->rey_get_script_depends() );

			wc_set_loop_prop( 'loop', 0 );

			$parent_classes = $product_archive->get_css_classes();
			unset( $parent_classes['grid_layout'] );

			$classes = [
				'--prevent-metro', // make sure it does not have thumbnail slideshow
				'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
				'--prevent-scattered', // make sure scattered is not applied
				'--prevent-masonry', // make sure masonry is not applied
			];

			$classes['columns'] = 'columns-' . wc_get_loop_prop('columns');

			$cols_per_tablet = isset($this->_settings['per_row_tablet'] ) && $this->_settings['per_row_tablet'] ? $this->_settings['per_row_tablet'] : reycore_wc_get_columns('tablet');
			$classes['columns-tablet'] = 'columns-tablet-' . absint( $cols_per_tablet );

			$classes['columns-mobile'] = 'columns-mobile-1';

			printf('<ul class="products %s">', implode(' ', array_merge( $classes, $parent_classes ) ) );
		}

		public function loop_end(){
			echo '</ul>';
		}

		function disable_animations(){
			if( $this->_settings['entry_animation'] !== 'yes' ){
				wc_set_loop_prop( 'entry_animation', false );
			}
		}

		public function render_products( $products )
		{
			if( isset($GLOBALS['post']) ) {
				$original_post = $GLOBALS['post'];
			}

			if ( wc_get_loop_prop( 'total' ) ) {

				$entry_animation = wc_get_loop_prop( 'entry_animation' );

				if( $this->_settings['entry_animation'] !== 'yes' ){
					wc_set_loop_prop( 'entry_animation', false );
				}

				foreach ( $products->ids as $product_id ) {
					$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
					setup_postdata( $GLOBALS['post'] );
					// Render product template.
					// wc_get_template_part( 'content', 'product' );
					$this->render_product( $GLOBALS['post'] );
				}

				wc_set_loop_prop( 'entry_animation', $entry_animation );

			}

			if( isset($original_post) ) {
				$GLOBALS['post'] = $original_post; // WPCS: override ok.
			}
		}

		public function render_product( $product ){

			?>
			<li <?php wc_product_class( '', $product ); ?>>

				<?php if( $this->_settings['hide_thumbnails'] !== 'yes' ): ?>
				<div class="rey-mini-img rey-productThumbnail">
					<?php
						woocommerce_template_loop_product_link_open();
						woocommerce_template_loop_product_thumbnail();
						woocommerce_template_loop_product_link_close();
					?>
				</div>
				<?php endif; ?>

				<div class="rey-mini-content">
					<?php

						if( $this->_settings['hide_brands'] !== 'yes' && class_exists('ReyCore_WooCommerce_Loop') ):
							ReyCore_WooCommerce_Loop::getInstance()->component_brands();
						endif;

						if( $this->_settings['hide_category'] !== 'yes' && class_exists('ReyCore_WooCommerce_Loop') ):
							ReyCore_WooCommerce_Loop::getInstance()->component_product_category();
						endif;

						echo sprintf(
							'<h2 class="%s"><a href="%s">%s</a></h2>',
							esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ),
							esc_url(get_the_permalink()),
							get_the_title()
						);

						if( $this->_settings['hide_ratings'] !== 'yes' ):
							woocommerce_template_loop_rating();
						endif;

						if( $this->_settings['hide_prices'] !== 'yes' ):
							woocommerce_template_loop_price();
						endif;

						if( $this->_settings['hide_add_to_cart'] !== 'yes' ):
							woocommerce_template_loop_add_to_cart([
								'wrap_button' => false,
								'supports_qty' => false,
							]);
						endif;
					?>
				</div>
			</li>
		<?php
		}

		function __change_classes( $classes ){
			unset($classes['extra-media']);
			unset($classes['rey_skin']);
			return $classes;
		}

		/**
		 * Render widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function render() {

			reyCoreAssets()->add_styles(['reycore-general']);
			reyCoreAssets()->add_scripts( ['reycore-woocommerce', 'reycore-widget-product-grid-scripts'] );

			if( ! class_exists('ReyCore_WooCommerce_ProductArchive') ){
				return;
			}

			$args = [
				'name'        => 'product_grid_element',
				'filter_name' => 'product_grid',
				'main_class'  => 'reyEl-productGrid',
				'el_instance' => $this->parent,
				'placeholder_class' => '--side-thumb'
			];

			$this->_settings = $this->parent->get_settings_for_display();

			$product_archive = new ReyCore_WooCommerce_ProductArchive( $args, $this->_settings );

			if( $product_archive->lazy_start() ){
				return;
			}

			reyCoreAssets()->add_styles(['reycore-widget-product-grid-styles']);

			if ( $product_archive->get_query_results() ) {

				$product_archive->render_start();

					$this->loop_start($product_archive);

						add_filter( 'post_class', [$this, '__change_classes'], 20 );

						$this->render_products( $product_archive->_products );

						remove_filter( 'post_class', [$this, '__change_classes'], 20 );

					$this->loop_end();

				$product_archive->render_end();
			}
			else {
				wc_get_template( 'loop/no-products-found.php' );
			}

			$product_archive->lazy_end();
		}

	}
endif;
