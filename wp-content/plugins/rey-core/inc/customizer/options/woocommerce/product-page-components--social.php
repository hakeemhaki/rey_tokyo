<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_components';


/* ------------------------------------ Social Sharing ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('Social Sharing', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
	'style_attr'  => '--border-size: 3px;',
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'product_share',
    'label'       => esc_html__('Share', 'rey-core'),
    'description' => __('Select the visibility of share icons.', 'rey-core'),
	'section'     => $section,
	'default'     => '1',
    'choices'     => array(
        '1' => esc_attr__('Show', 'rey-core'),
        '2' => esc_attr__('Hide', 'rey-core')
	),
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'product_share_icons',
	'label'       => esc_html__('Social Sharing Icons', 'rey-core'),
	'description' => __('Customize the social icons.', 'rey-core'),
	'section'     => $section,
	'row_label' => array(
		'type' => 'text',
		'value' => esc_html__('Social Icon', 'rey-core'),
		'field' => 'social_icon',
	),
	'button_label' => esc_html__('New Social Icon', 'rey-core'),
	'default'      => [
		[
			'social_icon' => 'twitter'
		],
		[
			'social_icon' => 'facebook-f'
		],
		[
			'social_icon' => 'linkedin'
		],
		[
			'social_icon' => 'pinterest-p'
		],
		[
			'social_icon' => 'mail'
		],
		[
			'social_icon' => 'copy'
		],
	],
	'fields' => array(
		'social_icon' => array(
			'type'        => 'select',
			'label'       => esc_html__('Social Icon', 'rey-core'),
			'choices'     => reycore__social_icons_list_select2('share'),
		),
	),
	'active_callback' => [
		[
			'setting'  => 'product_share',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Icons options', 'rey-core')
	]
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_share_icons_colored',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__( 'Colored icons', 'rey-core' ),
		esc_html__( 'Enable coloring the icons', 'rey-core' )
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_share',
			'operator' => '==',
			'value'    => '1',
		],
	],
	'rey_group_end' => true
] );
