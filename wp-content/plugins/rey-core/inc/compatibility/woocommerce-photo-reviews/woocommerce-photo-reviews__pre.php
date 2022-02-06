<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyCore_Compatibility__WooPhotoReviews__Pre') ):

	class ReyCore_Compatibility__WooPhotoReviews__Pre
	{
		private $settings = [];

		public function __construct()
		{
			add_filter('_wcpr_nkt_setting', [$this, 'set_defaults'], 20);
		}

		function set_defaults( $params ){

			if( get_theme_mod('wpr__use_defaults', false) ){
				return $params;
			}

			$params['photo']['grid_item_bg'] = '';
			$params['photo']['grid_item_border_color'] = '';
			$params['photo']['comment_text_color'] = '';
			$params['photo']['star_color'] = get_theme_mod('star_rating_color', '#ff4545');
			$params['photo']['enable_box_shadow'] = false;

			// $params['photo']['display'] = 2;
			// $params['photo']['image_popup'] = 'lightbox';
			// $params['photo']['image_popup'] = 'below_thumb';

			$params['photo']['verified'] = 'badge';
			$params['photo']['verified_text'] = 'Verified owner';
			$params['photo']['verified_badge'] = 'woocommerce-photo-reviews-badge-tick-4';
			$params['photo']['verified_color'] = '#000';

			return $params;
		}

	}

	new ReyCore_Compatibility__WooPhotoReviews__Pre();
endif;
