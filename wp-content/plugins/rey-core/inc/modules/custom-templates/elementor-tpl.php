<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $post;

$rt = reyTemplates();
$active_template = $rt->get_active_template();
$edit_preview_mode = $rt->elementor->is_edit_mode() || isset($_REQUEST[$rt::POST_TYPE]);
$tid = !empty($active_template) ? $active_template['id'] : ($edit_preview_mode ? $post->ID : '');
$settings = $rt->elementor->get_settings( $tid );

if( $settings['grid'] === 'elementor' ){
	add_filter( 'reycore/elementor/load_grid', '__return_false', 100);
}

get_header( $settings['type'] === 'canvas' ? 'canvas' : '' );

	if( ! $settings['type'] ){
		rey_action__before_site_container();
	}

		$template_type = get_field( 'template_type', $tid );

		do_action('reycore/templates/tpl/before_render', $template_type, $active_template, $edit_preview_mode);

		// Edit mode (or Preview)
		if( $edit_preview_mode  ){

			$is_woocommerce = in_array($template_type, ['product', 'product-archive'], true);

			// create wrapper for woocommerce and product/product-archive
			if( $is_woocommerce ){
				echo '<div class="woocommerce">';
			}

				echo '<div id="rey-template" class="rey-template rey-template--edit-mode '. $template_type .'">';

					the_content();

				echo '</div>';

			if( $is_woocommerce ){
				echo '</div>';
			}

		}

		// Template mode in Frontend
		else if( !empty($active_template) ) {

			$css_classes[] = sprintf('rey-template rey-template--frontend rey-template-%d %s', $tid, $template_type);

			if( is_singular() ){
				$css_classes = array_merge( $css_classes, get_post_class() );
			} ?>

			<div id="rey-template" class="<?php echo esc_attr( implode( ' ', $css_classes) ); ?>" >
				<?php

				if( is_singular() ){
					$rt->elementor::$instance->db->switch_to_query([ 'p' => get_the_ID(), 'post_type' => get_post_type() ]);
				}

				echo $rt->elementor::$instance->frontend->get_builder_content_for_display( $tid );

				if( is_singular() ){
					$rt->elementor::$instance->db->restore_current_query();
				}

				?>
			</div>

			<?php
		}

		else {
			echo 'Missing template.';
		}

		do_action('reycore/templates/tpl/after_render', $template_type, $active_template, $edit_preview_mode);

	if( ! $settings['type'] ){
		rey_action__after_site_container();
	}

get_footer( $settings['type'] === 'canvas' ? 'canvas' : '' );
