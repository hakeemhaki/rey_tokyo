<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('Header_Footer_Elementor') && !class_exists('ReyCore_Compatibility__HFE') ):

	class ReyCore_Compatibility__HFE
	{
		public function __construct()
		{
			add_action('reycore/ocdi/after_buttons', [$this, 'add_notice']);
			add_action('admin_notices', [$this, 'site_notice']);
		}

		public function site_notice() {
			printf('<div class="notice error"><p>%s</p></div>', __('Please disable "<strong>Elementor - Header, Footer & Blocks</strong>" plugin because it\'s not compatible with <strong>REY</strong> and will cause problems. Also Rey already has Header & Footer global sections, so this plugin is redundant.', 'rey-core') );
		}

		public function add_notice() {
			printf('<div class="rey-adminNotice --error">%s</div>', __('Please disable "<strong>Elementor - Header, Footer & Blocks</strong>" plugin because it\'s not compatible with Rey and will cause problems.', 'rey-core') );
		}
	}

	new ReyCore_Compatibility__HFE;
endif;
