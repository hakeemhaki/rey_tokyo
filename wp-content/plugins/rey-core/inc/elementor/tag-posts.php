<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('ReyCore_Elementor_Posts')):

	class ReyCore_Elementor_Posts
	{

		public $_settings = [];
		public $_args = [];

		/**
		 * ReyCore_Elementor_Posts constructor.
		 */
		function __construct( $args = [], $settings = [] )
		{

			$this->_settings = $settings;
			$this->_args = $args;

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
				'yes' !== $this->_settings['add_pagination'] &&
				! wp_doing_ajax() &&
				! ( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() ) ){

				$qid = isset($GLOBALS['global_section_id']) ? $GLOBALS['global_section_id'] : get_queried_object_id();

				$config = [
					'element_id' => $this->_args['el_instance']->get_id(),
					'skin' => $this->_settings['_skin'],
					'trigger' => $this->_settings['lazy_load_trigger'] ? $this->_settings['lazy_load_trigger'] : 'scroll',
					'qid' => apply_filters('reycore/elementor/posts/lazy_load_qid', $qid),
					'options' => apply_filters('reycore/elementor/posts/lazy_load_options', [])
				];

				if( 'click' === $this->_settings['lazy_load_trigger'] ){
					$config['trigger__click'] = $this->_settings['lazy_load_click_trigger'];
				}

				$this->_args['el_instance']->add_render_attribute( '_wrapper', 'data-lazy-load', wp_json_encode( $config ) );

				if( $this->_settings['carousel'] !== '' ){
					$per_row = $this->_settings['slides_to_show'];
					$per_row_tablet = $this->_settings['slides_to_show_tablet'];
					$per_row_mobile = $this->_settings['slides_to_show_mobile'];
				}
				else {
					$per_row = $this->_settings['per_row'];
					$per_row_tablet = $this->_settings['per_row_tablet'];
					$per_row_mobile = $this->_settings['per_row_mobile'];
				}

				printf('<div class="__placeholders %4$s" style="--cols: %1$d; --cols-tablet: %2$d; --cols-mobile: %3$d;">',
					absint($per_row),
					absint($per_row_tablet),
					absint($per_row_mobile),
					( isset($this->_args['placeholder_class']) ? $this->_args['placeholder_class'] : '' ) );

					$count = $this->_settings['carousel'] === '' ? $this->_settings['posts_per_page'] : $per_row;

					for( $i = 0; $i < absint($count); $i++ ){
						echo '<div class="__placeholder-item"><div class="__placeholder-thumb"></div><div class="__placeholder-title"></div><div class="__placeholder-subtitle"></div></div>';
					}

				echo '</div>';

				$scripts = ['reycore-elementor-elem-lazy-load', 'reycore-widget-basic-post-grid-scripts'];

				if( 'scroll' === $this->_settings['lazy_load_trigger'] ){
					$scripts[] = 'scroll-out';
				}

				if( ! empty($scripts) ){
					reyCoreAssets()->add_scripts($scripts);
				}

				do_action('reycore/elementor/posts/lazy_load_assets', $this->_settings);

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

	}

endif;
