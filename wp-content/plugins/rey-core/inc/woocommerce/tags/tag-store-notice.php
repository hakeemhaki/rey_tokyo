<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_StoreNotice') ):

class ReyCore_WooCommerce_StoreNotice
{

	public function __construct() {
		add_action('wp', [$this, 'init']);
	}

	function init(){
		if( is_store_notice_showing() ){
			if( function_exists('reyAssets') ){
				reyAssets()->add_styles('rey-wc-general');
			}
			add_filter('woocommerce_demo_store', [$this, 'notice_markup'], 20, 2);
		}
	}

	public function notice_markup( $html, $notice ){

		$notice_id = md5( $notice );

		$style = 'style="display:block;"';

		// is hidden
		if( isset($_COOKIE['store_notice' . $notice_id]) && $_COOKIE['store_notice' . $notice_id] === 'hidden' ){
			$style = 'style="display:none;"';
		}

		$notice_start = '<div class="woocommerce-store-notice demo_store" data-notice-id="' . esc_attr( $notice_id ) . '" '. $style .'>';
		$notice_end = '</div>';

		$close_text = esc_html__( 'Dismiss', 'rey-core' );
		$close_style = get_theme_mod('woocommerce_store_notice_close_style', 'default');

		if( $close_style !== 'default' ){
			$close_text = reycore__get_svg_icon(['id' => 'rey-icon-close']);
		}

		$notice_close = sprintf('<a href="#" class="woocommerce-store-notice__dismiss-link --%s">%s</a>', $close_style, $close_text);

		reyAssets()->add_styles('rey-wc-general');

		return sprintf( '%3$s <div class="woocommerce-store-notice-content">%1$s %2$s</div> %4$s',
			wp_kses_post( $notice ),
			$notice_close,
			$notice_start,
			$notice_end
		);

	}

}

new ReyCore_WooCommerce_StoreNotice;

endif;
