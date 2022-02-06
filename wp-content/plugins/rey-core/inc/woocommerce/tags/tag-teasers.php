<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Teasers') ):

	class ReyCore_WooCommerce_Teasers
	{

		public function __construct() {
			add_action( 'wp', [$this, 'init']);
		}

		function init(){

			if( ! apply_filters('reycore/woocommerce/catalog/teasers/enable', is_main_query()) ){
				return;
			}

			$this->settings = apply_filters('reycore/woocommerce/catalog/teasers/settings', [
				'count_previous_indexes' => true
			]);

			$loop_teaser = get_theme_mod('loop_teasers', []);

			if( empty($loop_teaser) ){
				return;
			}

			add_action( 'elementor/theme/before_do_archive', [$this, 'epro_location_before']);

			foreach ($loop_teaser as $key => $teaser) {

				if( !(isset($teaser['gs']) && ($gs = $teaser['gs'])) ){
					continue;
				}

				$should_display_shop_page = is_shop() && $teaser['shop_page'] === 'yes';
				$should_display_tags_page = is_product_tag() && isset($teaser['tags_page']) && $teaser['tags_page'] === 'yes';
				$should_display_category_page = is_product_category( $teaser['categories'] );

				if( ! apply_filters('reycore/woocommerce/catalog/teasers/display_check', $should_display_shop_page || $should_display_category_page || $should_display_tags_page , $teaser ) ){
					continue;
				}

				$cols = wc_get_loop_prop('columns');
				$position = $teaser['position'] ? $teaser['position'] : 'start';
				$size = $teaser['size'] ? $teaser['size'] : 2;
				$row = $teaser['row'] ? $teaser['row'] : 1;

				if( $position === 'start' ){
					$index = ($row * $cols) - $cols;
				}
				elseif( $position === 'end' ){
					$index = ($row * $cols) - ($cols - $size);
				}

				$prev_indexes = 0;

				if( $this->settings['count_previous_indexes'] && isset($GLOBALS['reycore_teasers']) ){
					if( $prev_indexes = wp_list_pluck($GLOBALS['reycore_teasers'], 'size') ){
						$prev_indexes = array_sum($prev_indexes) - 1;
					}
				}

				$GLOBALS['reycore_teasers'][$key] = [
					'index' => $index - $prev_indexes,
					'repeat' => $teaser['repeat'] === 'yes',
					'gs' => $gs,
					'size' => $size,
				];

				add_action( 'reycore/woocommerce/content_product/before', [$this, '_before'], 10);
			}
		}

		function _before( $product ){

			if( ! (isset($GLOBALS['reycore_teasers']) && ($teasers = $GLOBALS['reycore_teasers'])) ){
				return;
			}

			if( apply_filters('reycore/woocommerce/teasers/epro_shop_page_fix', false) ){
				$GLOBALS['wp_query']->next_post();
			}

			foreach ($teasers as $key => $teaser) {

				if( ! (isset($GLOBALS['wp_query']) && $GLOBALS['wp_query']->current_post === $teaser['index']) ){
					continue;
				}

				if( ! $teaser['repeat'] && wc_get_loop_prop('current_page') > 1 ){
					continue;
				}

				if( isset($teaser['gs']) && ($gs = $teaser['gs']) ){

					if( ! class_exists('ReyCore_GlobalSections') ){
						continue;
					}

					$gs_html = ReyCore_GlobalSections::do_section( $gs, true );

					if( ! $gs_html ){
						continue;
					}

					echo '<li ';

						wc_product_class( '--teaser' );

						if( $teaser['size'] > 1 ){

							if( ($layout_cols = absint(wc_get_loop_prop('columns'))) && $layout_cols < $teaser['size'] ){
								$teaser['size'] = absint($layout_cols);
							}

							printf( ' data-colspan="%d" ', $teaser['size']);
						}

					echo '>';

						echo $gs_html;

					echo '</li>';

				}

			}

			reyCoreAssets()->add_styles('rey-wc-tag-stretch');
			reyCoreAssets()->add_scripts('reycore-wc-loop-stretch');
		}

		function epro_location_before( $instance ){

			if( class_exists('WooCommerce') && is_shop() ){
				add_filter('reycore/woocommerce/teasers/epro_shop_page_fix', '__return_true');
			}
		}
	}

	new ReyCore_WooCommerce_Teasers;
endif;
