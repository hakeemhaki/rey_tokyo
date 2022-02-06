<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_GlobalSections_Visibility') ):

class ReyCore_GlobalSections_Visibility
{
	protected $date_format = 'd/m/Y g:i a';

	const OPTION = 'rey_elementor_gs_hide';

	public $opts = [];

	public function __construct()
	{
		add_action( 'acf/init', [$this, 'add_fields'] );
		add_action( 'acf/save_post', [$this, 'refresh_opt'], 20 );
		add_filter( 'reycore/elementor/gs_id', [$this, 'hide_gs'], 99);
	}

	function maybe_hide_gs( $gs_id ){

		if( isset($this->opts[ $gs_id ]) ){
			return $this->opts[ $gs_id ];
		}

		$hide = [];
		$date_now = strtotime('now');

		if( $start_date = get_field('start_date', $gs_id) ){

			if( $startMakeFormat = DateTime::createFromFormat($this->date_format, $start_date) ):

				$hide['start_date'] = true;

				$start_timestamp = $startMakeFormat->getTimestamp();

				if( $date_now > $start_timestamp ){
					$hide['start_date'] = false;
				}
			endif;

		}

		if( $end_date = get_field('end_date', $gs_id) ){

			if( $endMakeFormat = DateTime::createFromFormat($this->date_format, $end_date) ):

				$hide['end_date'] = true;
				$end_timestamp = $endMakeFormat->getTimestamp();

				if( $date_now < $end_timestamp ){
					$hide['end_date'] = false;
				}
			endif;
		}

		if( $status = get_field('show_per_login_status', $gs_id) ){

			$hide['status'] = true;
			$logged_in = is_user_logged_in();

			if( $logged_in && 'logged' === $status ){
				$hide['status'] = false;
			}

			else if( ! $logged_in && 'logged_out' === $status ){
				$hide['status'] = false;
			}

		}

		$should_hide = in_array(true, $hide, true);

		$this->opts[ $gs_id ] = $should_hide;

		update_option(self::OPTION, $this->opts);

		return $should_hide;
	}

	function hide_gs( $gs_id ){

		if( ! class_exists('ACF') ){
			return $gs_id;
		}

		if( $this->maybe_hide_gs( $gs_id ) ){
			return false;
		}

		return $gs_id;
	}

	function refresh_opt( $gs_id ){

		$opt = get_option(self::OPTION, []);

		$opt[ $gs_id ] = $this->maybe_hide_gs( $gs_id );

		update_option(self::OPTION, $opt);
	}

	function add_fields(){

		$this->opts = get_option(self::OPTION, []);

		if( function_exists('acf_add_local_field_group') ):

			acf_add_local_field_group(array(
				'key' => 'group_5f058db5d6559',
				'title' => 'Global Section Visibility',
				'fields' => array(
					array(
						'key' => 'field_5f058df1520b4',
						'label' => 'Start Date',
						'name' => 'start_date',
						'type' => 'date_time_picker',
						'instructions' => 'Automatically show this global section when this date has started.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'display_format' => $this->date_format,
						'return_format' => $this->date_format,
						'first_day' => 1,
					),
					array(
						'key' => 'field_5f058e27520b5',
						'label' => 'End Date',
						'name' => 'end_date',
						'type' => 'date_time_picker',
						'instructions' => 'Automatically hide this global section after this date.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'display_format' => $this->date_format,
						'return_format' => $this->date_format,
						'first_day' => 1,
					),
					array(
						'key' => 'field_5f058ec8e3781',
						'label' => 'Show per login status',
						'name' => 'show_per_login_status',
						'type' => 'select',
						'instructions' => 'Select if you want to show this section to a specific group of users',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'logged' => 'Logged-in users',
							'logged_out' => 'Logged-out users (guests)',
						),
						'default_value' => array(
						),
						'allow_null' => 1,
						'multiple' => 0,
						'ui' => 0,
						'return_format' => 'value',
						'ajax' => 0,
						'placeholder' => '',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'rey-global-sections',
						),
					),
				),
				'menu_order' => 10,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));

		endif;
	}

}

new ReyCore_GlobalSections_Visibility;

endif;
