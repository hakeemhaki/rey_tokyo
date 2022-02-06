<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('FacetWP') && !class_exists('ReyCore_Compatibility__FacetWP') ):

	class ReyCore_Compatibility__FacetWP
	{
		public function __construct() {
			add_action('wp_footer', [$this, 'scripts'], 20);
		}

		public function scripts($files) {
			?>
			<script>
				(function($){
					$(document).on('facetwp-loaded', function(e){
						$(document).trigger('reycore/ajaxfilters/finished', [$('.rey-siteMain ul.products ')]);
					})
				})(jQuery);
			</script>
			<?php
		}

	}

	new ReyCore_Compatibility__FacetWP;
endif;
