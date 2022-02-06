<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Header > Navigation
 */

 $section = 'header_nav_options';

// Nav
ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Navigation', 'rey-core'),
	'priority'       => 40,
	'panel'			 => 'header_options'
));

reycore_customizer__title([
	'description' => sprintf(__('Want more options? Great! Make sure to use a Global Section Header, and inside it make use of <strong>Header - Navigation</strong> element.', 'rey-core'), admin_url('admin.php?page=rey-install-required-plugins')) . '<br><br>',
	'section'     => $section,
	'size'        => 'sm',
	'border'      => 'none',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_nav_hover_delays',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Hover Delays', 'rey-core' ),
		__( 'This option enables a subtle delay for submenus effects when hovering menu items.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_nav_hover_overlay',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Hover Overlay', 'rey-core' ),
		__( 'This option enables the overlay to be shown behind submenus when hovering menu items.', 'rey-core' )
	),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_nav_submenus_shadow',
	'label'       => esc_html__( 'Submenu shadow level', 'rey-core' ),
	'section'     => $section,
	'default'     => '1',
	'choices'     => [
		'0' => esc_html__( 'Disabled', 'rey-core' ),
		'1' => esc_html__( 'Level 1', 'rey-core' ),
		'2' => esc_html__( 'Level 2', 'rey-core' ),
		'3' => esc_html__( 'Level 3', 'rey-core' ),
		'4' => esc_html__( 'Level 4', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'header_nav_items_spacing',
	'label'       => esc_html__( 'Horizontal Spacing (rem)', 'rey-core' ),
	'section'     => $section,
	'default'     => 1,
	'transport'   => 'auto',
	'choices'     => [
		'min'  => 0.1,
		'max'  => 100,
		'step' => 0.1,
	],
	'output'      		=> [
		[
			'media_query'	=> '@media (min-width: 1025px)',
			'element'  		=> ':root',
			'property' 		=> '--header-nav-x-spacing',
			'units'    		=> 'rem',
		],
	],
] );

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_menu_lvl_1',
	'label'       => esc_html__('Main Menu Typography (1st level)', 'rey-core'),
	'section'     => $section,
	'default'     => array(
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'text-transform' => '',
		'variant' => '',
		'font-weight' => '',
	),
	'output' => array(
		array(
			'element' => '.rey-mainMenu.rey-mainMenu--desktop > .menu-item.depth--0 > a',
		),
	),
	'load_choices' => true,
));

reycore_customizer__title([
	'title'   => esc_html__('Mobile Navigation', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'upper'   => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'nav_breakpoint',
	'label'       => esc_html__( 'Breakpoint for mobile navigation', 'rey-core' ),
	'section'     => $section,
	'default'     => 1024,
	'choices'     => [
		'min'  => 768,
		'max'  => 5000,
		'step' => 1,
	],
] );


$get_all_menus = reyCoreHelper()->get_all_menus();

if( !is_array($get_all_menus) ){
	$get_all_menus = [];
}

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'mobile_menu',
	'label'       => esc_html__( 'Mobile menu source', 'rey-core' ),
	'description' => esc_html__( 'By default, the main menu is used for mobile menu as well, however if you want to have different structure, please select another navigation', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => ['' => esc_html__('- Select -', 'rey-core')] + $get_all_menus,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'mobile_panel_bg_color',
	'label'       => esc_html__( 'Mobile Panel Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-nav-mobile-panel-bg-color',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'mobile_panel_text_color',
	'label'       => esc_html__( 'Mobile Panel Text Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--header-nav-mobile-panel-text-color',
		],
	],
] );

reycore_customizer__notice([
	'section'     => $section,
	'default'     => __('In case these options doesn\'t seem to work, please check if you\'re using a Header Global Section and make sure the "Header - Navigation" element doesn\'t have the Override settings option enabled eg: <a href="https://d.pr/i/cjQx1X" target="_blank">https://d.pr/i/cjQx1X</a>.', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'default',
		],
	]
] );


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-header-settings/#navigation',
	'section' => $section
]);
