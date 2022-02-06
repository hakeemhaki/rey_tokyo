<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action( 'customize_register', function( $wp_customize ) {

	/**
	 * The custom control class
	 */
	class Kirki_Controls_ReyHFGlobalSections extends Kirki_Control_Base {

		public $type = 'rey-hf-global-section';

		public function render_content() {

			$label = $this->label;
			$description = $this->description;

            $input_id = '_customize-input-' . $this->id;
			$name = $this->id;

			$active_value = $this->value();

			$global_sections = $this->choices['global_sections'];
			$gs_type = $this->choices['type'];

			$new_link = sprintf('<a href="%1$s" class="rey-gs-linksNew rey-gs-linkItem" title="%3$s" target="_blank">%2$s</a>',
				admin_url('post-new.php?post_type=rey-global-sections&gs_type=' . $gs_type),
				sprintf( esc_html__('+ New %s Global Section', 'rey-core') , $gs_type),
				sprintf( esc_attr__('Create a new %s global section with Elementor', 'rey-core') , ucfirst($gs_type))
			);

			$gs_button_content = esc_html__('Global Section', 'rey-core');

			echo apply_filters("reycore/customizer/pre_text/" . $this->type, '', $name); ?>

			<div class="rey-control-wrap rey-hf-gs">

				<?php if( !empty( $label ) ) : ?>
					<span class="customize-control-title rey-control-title"> <?php echo $label; ?> </span>
				<?php endif; ?>

				<?php if( !empty( $description ) ) : ?>
					<span class="customize-control-description rey-control-description"><?php echo $description; ?></span>
				<?php endif; ?>

				<div class="customize-control-content">

					<div class="rey-radio-choices">
						<?php

						foreach([
								'none' => esc_html__('Disabled', 'rey-core'),
								'default' => esc_html__('Basic', 'rey-core'),
								'gs' => $gs_button_content
							] as $k => $v ){

							printf( '<input type="radio" name="%1$s[type]" id="%1$s-%3$s" value="%3$s" %4$s><label for="%1$s-%3$s">%2$s</label>',
								esc_attr__( $input_id ),
								$v,
								$k,
								$this->is_checked($k)
							);
						} ?>
					</div>

					<?php if( !empty($global_sections) ){ ?>

						<div class="rey-gs-list <?php echo $active_value !== 'none' && $active_value !== 'default' ? '--active' : '' ?>">
							<?php
							$options = [];

							foreach ($global_sections as $k => $v){
								$options[] = sprintf('<option value="%1$d" %3$s>%2$s</option>',
									$k,
									$v,
									selected($k, $active_value, false)
								);
							}

							if( $options ){

								echo '<span class="customize-control-title rey-control-title">';
								echo reycore_customizer__title_tooltip(
									sprintf( esc_html__('Select a %s Global Section:', 'rey-core'), ucfirst($gs_type)),
									reycore__header_footer_layout_desc($gs_type),
									[ 'size'=>290, 'clickable'=>true ]
								);
								echo '</span>';

								printf('<select name="%1$s[gs_list]" id="%1$s-gs_list">%2$s</select>',
									esc_attr__( $input_id ),
									implode('', $options)
								);

							} ?>

							<div class="rey-gs-links">
								<?php
								foreach ($global_sections as $k => $v){
									printf('<a class="rey-gs-linksEdit rey-gs-linkItem %3$s" data-id="%6$s" href="%1$s" target="_blank" title="%5$s">%4$s</a>',
										admin_url('post.php?post='. $k .'&action=elementor'),
										$v,
										$k == $active_value ? '--active' : '',
										esc_html__('Edit selected', 'rey-core'),
										esc_attr__('Edit the selected global section with Elementor', 'rey-core'),
										esc_attr($k)
									);
								}
								echo $new_link;
								?>
							</div>
						</div>

					<?php } else {

						printf( '<p class="description customize-control-description">%s</p>', esc_html__('No global sections existing. Please create a new one and refresh Customizer.', 'rey-core') );

						echo $new_link;
					} ?>

					<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $active_value ); ?>" <?php $this->link(); ?> />

				</div>
			</div>

            <?php
		}

		public function is_checked( $val ){

			$not_gs = $this->value() !== 'none' && $this->value() !== 'default';

			$s = 'checked="checked"';

			if( $val == $this->value() ){
				return $s;
			}

			elseif ($not_gs){
				return $s;
			}
		}

	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-hf-global-section'] = 'Kirki_Controls_ReyHFGlobalSections';
		return $controls;
	} );
} );


class Kirki_Field_ReyHFGlobalSections extends Kirki_Field {

	protected function set_type() {
		$this->type = 'rey-hf-global-section';
	}

	/**
	 * Sets the $sanitize_callback
	 *
	 * @access protected
	 */
	// protected function set_sanitize_callback() {
	// 	$this->sanitize_callback = array( $this, 'sanitize' );
	// }

	/**
	 * Sanitizes numeric values.
	 *
	 * @access public
	 * @param integer|string $value The checkbox value.
	 * @return bool
	 */
	public function sanitize( $value = 0 ) {


		$value = filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		// Minimum & maximum value limits.
		if ( $value < $this->choices['min'] || $value > $this->choices['max'] ) {
			return max( min( $value, $this->choices['max'] ), $this->choices['min'] );
		}

		// Only multiple of steps.
		$steps = ( $value - $this->choices['min'] ) / $this->choices['step'];
		if ( ! is_int( $steps ) ) {
			$value = $this->choices['min'] + ( round( $steps ) * $this->choices['step'] );
		}
		return $value;
	}
}
