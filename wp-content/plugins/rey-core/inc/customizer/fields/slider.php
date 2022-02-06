<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


add_action( 'customize_register', function( $wp_customize ) {

	class Kirki_Controls_Rey_Slider extends Kirki_Control_Base {

		public $type = 'rey-slider';

		public function to_json() {

			parent::to_json();

			$this->json['choices'] = wp_parse_args(
				$this->json['choices'],
				array(
					'min'    => '0',
					'max'    => '100',
					'step'   => '1',
					'suffix' => '',
				)
			);
		}

		/**
		 * An Underscore (JS) template for this control's content (but not its container).
		 *
		 * Class variables for this control class are available in the `data` JS object;
		 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
		 *
		 * @see WP_Customize_Control::print_template()
		 *
		 * @access protected
		 */
		protected function content_template() {
			?>
			<div class="rey-control-wrap">

				<# if ( data.label ) { #><label for="{{data.id}}"><span class="customize-control-title">{{{ data.label }}}</span></label><# } #>

				<div class="customize-control-content">
					<input {{{ data.inputAttrs }}} type="range" min="{{ data.choices['min'] }}" max="{{ data.choices['max'] }}" step="{{ data.choices['step'] }}" value="{{ data.value }}" {{{ data.link }}} />
					<span class="value">
						<input {{{ data.inputAttrs }}} type="text"/>
						<span class="suffix">{{ data.choices['suffix'] }}</span>
					</span>
				</div>

				<# if ( data.description ) { #><span class="description customize-control-description">{{{ data.description }}}</span><# } #>
			</div>
			<?php
		}
	}

	add_filter( 'kirki_control_types', function( $controls ) {
		$controls['rey-slider'] = 'Kirki_Controls_Rey_Slider';
		return $controls;
	} );

	$wp_customize->register_control_type( 'Kirki_Controls_Rey_Slider' );

} );


class Kirki_Field_Rey_Slider extends Kirki_Field {

	protected function set_type() {
		$this->type = 'rey-slider';
	}

	/**
	 * Sets the $sanitize_callback
	 *
	 * @access protected
	 */
	protected function set_sanitize_callback() {
		$this->sanitize_callback = array( $this, 'sanitize' );
	}

	/**
	 * Sets the $choices
	 *
	 * @access protected
	 */
	protected function set_choices() {
		$this->choices = wp_parse_args(
			$this->choices,
			array(
				'min'  => -999999999,
				'max'  => 999999999,
				'step' => 1,
			)
		);
		// Make sure min, max & step are all numeric.
		$this->choices['min']  = filter_var( $this->choices['min'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$this->choices['max']  = filter_var( $this->choices['max'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$this->choices['step'] = filter_var( $this->choices['step'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	}

	/**
	 * Sanitizes numeric values.
	 *
	 * @access public
	 * @param integer|string $value The checkbox value.
	 * @return bool
	 */
	public function sanitize( $value = 0 ) {
		$this->set_choices();

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
