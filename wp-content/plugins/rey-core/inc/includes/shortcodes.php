<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!function_exists('reycore_shortcode__can_ship')):
	/**
	 * Can Ship shortcode. Will check if shipping is supported for visitors.
	 *
	 * [can_ship text="Yes, we ship to %s!" no_text=""]
	 *
	 * @since 1.0.0
	 **/
	function reycore_shortcode__can_ship($atts)
	{
		if( class_exists('WooCommerce') && class_exists('WC_Geolocation') && is_callable('WC') ){

			// Bail if localhost
			if( in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ){
				return esc_html__('Can\'t geolocate localhost.', 'rey-core');
			}

			$geolocation = WC_Geolocation::geolocate_ip();
			$country_list = WC()->countries->get_shipping_countries();
			if( !is_null($geolocation) && !empty($geolocation) && isset($geolocation['country']) ) {
				if( isset($country_list[$geolocation['country']]) && ($supported_country = $country_list[$geolocation['country']]) ) {
					if( isset($atts['text']) && !empty($atts['text']) ){
						$html = '<span class="rey-canShip">'. str_replace('%s', '<span>%s</span>', sanitize_text_field($atts['text'])) .'</span>';
						return sprintf( $html, $country_list[$geolocation['country']] );
					}
				}
				else {
					if( isset($atts['no_text']) && !empty($atts['no_text']) ){
						$html = '<span class="rey-canShip">'. str_replace('%s', '<span>%s</span>', sanitize_text_field($atts['no_text'])) .'</span>';
						return sprintf( $html, WC()->countries->countries[ $geolocation['country'] ] );
					}
				}
			}
		}
		return false;
	}
endif;
add_shortcode('can_ship', 'reycore_shortcode__can_ship');


if(!function_exists('reycore_shortcode__site_info')):
	/**
	 * Display site info through shortcodes.
	 * show = name / email / url
	 *
	 * @since 1.0.0
	 **/
	function reycore_shortcode__site_info($atts)
	{
		$content = '';
		if( isset($atts['show']) && $show = $atts['show'] ){
			switch ($show):
				case"name":
					$content = get_bloginfo( 'name' );
					break;
				case"email":
					$content = get_bloginfo( 'admin_email' );
					break;
				case"url":
					$content = sprintf('<a href="%1$s">%1%s</a>', get_bloginfo( 'url' ));
					break;
			endswitch;
		}
		return $content;
	}
endif;
add_shortcode('site_info', 'reycore_shortcode__site_info');


if(!function_exists('reycore_shortcode__enqueue_asset')):
	/**
	 * Enqueue a script or style;
	 *
	 * @since 1.9.7
	 **/
	function reycore_shortcode__enqueue_asset($atts)
	{
		$content = '';

		if( isset($atts['type']) && ($type = $atts['type']) && isset($atts['name']) && ($name = $atts['name']) ){

			if( $type === 'style' ){
				wp_enqueue_style($name);
			}
			else if( $type === 'script' ){
				wp_enqueue_script($name);
			}

		}

		return $content;
	}
endif;
add_shortcode('enqueue_asset', 'reycore_shortcode__enqueue_asset');


if(!function_exists('reycore_shortcode__attribute_link')):
	/**
	 * Get URL of a taxonomy.
	 * [attribute_link taxonomy="pa_brand"]
	 *
	 * @since 1.9.7
	 **/
	function reycore_shortcode__attribute_link($atts)
	{
		$content = '';

		if( !(isset($atts['taxonomy']) && ($taxonomy = $atts['taxonomy']) && taxonomy_exists($taxonomy)) ){
			return $content;
		}

		if( !($product = wc_get_product()) ){
			return $content;
		}

		$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );

		if( ! isset($attributes[ $taxonomy ]) ){
			return $content;
		}

		if( ! ( isset($attributes[ $taxonomy ]['options']) && ($options = $attributes[ $taxonomy ]['options'] ) && !empty($options) ) ){
			return $content;
		}

		if( ($term_link = get_term_link( $options[0], $taxonomy )) && is_string($term_link) ){
			$content = $term_link;
		}
		else {
			if( class_exists('WooCommerce') ){
				$term_obj = get_term_by( 'term_taxonomy_id', $options[0], $taxonomy );
				if( isset($term_obj->slug) ){
					$content = sprintf( '%1$s?filter_%2$s=%3$s', get_permalink( wc_get_page_id( 'shop' ) ), wc_attribute_taxonomy_slug($taxonomy), $term_obj->slug );
				}
			}
		}

		return esc_url( $content );
	}
endif;
add_shortcode('attribute_link', 'reycore_shortcode__attribute_link');


if(!function_exists('reycore_shortcode__product_page')):
	function reycore_shortcode__product_page($atts){
		$content = '';

		if( ! (isset($atts['id']) && $id = $atts['id']) ){
			return '';
		}

		if( is_admin() ){
			return '';
		}

		// weird error in Gutenberg editor
		if( isset($_REQUEST['_locale']) && $_REQUEST['_locale'] === 'user') {
			return '';
		}

		ob_start();

		do_action('reycore/woocommerce/product_page/scripts');

		if( isset($atts['only_summary']) && 'true' === reycore__clean($atts['only_summary']) ){
			remove_all_actions( 'woocommerce_after_single_product_summary' );
		}

		echo do_shortcode(sprintf('[product_page id="%d"]', $id));

		$content = ob_get_clean();

		$search = '<div class="woocommerce">';
		$replace_with = '<div class="woocommerce single-skin--' . get_theme_mod('single_skin', 'default') . '">';

		return str_replace($search, $replace_with, $content);
	}
endif;
add_shortcode('rey_product_page', 'reycore_shortcode__product_page');
