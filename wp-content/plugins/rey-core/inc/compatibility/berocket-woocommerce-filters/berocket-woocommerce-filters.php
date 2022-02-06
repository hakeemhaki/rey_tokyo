<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( defined('BeRocket_AJAX_filters_version') && !class_exists('ReyCore_Compatibility__BeRocketWooCommerceFilters') ):

	class ReyCore_Compatibility__BeRocketWooCommerceFilters
	{
		public function __construct() {
			add_action('wp_footer', [$this, 'compatibility_ajax_load_more'], 20);
		}

		public function compatibility_ajax_load_more($files) {
			?>
			<script>
				(function($){
					$(document).on('berocket_ajax_filtering_end', function(e){
						if( typeof $.reyCore !== 'undefined' ){
							$(document).trigger('rey/product/loaded', [$('.rey-siteMain ul.products li.product')]);
						}
					})
				})(jQuery);
			</script>
			<?php
		}

	}

	new ReyCore_Compatibility__BeRocketWooCommerceFilters;
endif;
