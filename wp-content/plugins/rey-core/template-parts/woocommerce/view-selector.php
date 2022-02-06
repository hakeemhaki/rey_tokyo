<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Get variations
$options = array_filter( array_map( 'absint', explode(',', trim( get_theme_mod('loop_view_switcher_options', '2, 3, 4') ) ) ) );

?>
<div class="rey-viewSelector">
	<span class="rey-viewSelector__label"><?php esc_html_e('VIEW', 'rey-core') ?></span>
	<ul>
		<?php
			foreach ($options as $key => $value) {
				if( $value > 1 && $value <= 6 ){
					printf( '<li data-count="%1$s" class="%2$s">%1$s</li>',
						$value,
						$value == reycore_wc_get_columns('desktop') ? 'is-active': ''
					);
				}
			}
		?>
	</ul>
</div>
