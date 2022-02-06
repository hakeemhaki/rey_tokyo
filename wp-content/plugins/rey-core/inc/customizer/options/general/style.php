<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'style_options';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Style Settings', 'rey-core'),
	'priority'       => 5,
	'panel'			=> 'general_options'
));

reycore__kirki_custom_bg_group([
	'settings'    => 'style_bg_image',
	'label'       => __('Background image', 'rey-core'),
	'description' => __('Change the site background.', 'rey-core'),
	'section'     => $section,
	// 'priority'   => 30,
	'supports' => [
		'color', 'image', 'repeat', 'attachment', 'size', 'position'
	],
	'output_element' => ':root',
	'color' => [
		'default' => '#ffffff',
		'output_property' => '--body-bg-color',
	],
	'image' => [
		'output_property' => '--body-bg-image',
	],
	'repeat' => [
		'output_property' => '--body-bg-repeat',
	],
	'attachment' => [
		'output_property' => '--body-bg-attachment',
	],
	'size' => [
		'output_property' => '--body-bg-size',
	],
	'positionx' => [
		'output_property' => '--body-bg-posx',
	],
	'positiony' => [
		'output_property' => '--body-bg-posy',
	],
]);


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'rey-color',
	'settings'    => 'style_text_color',
	'label'       => reycore_customizer__title_tooltip(
		__('Text Color', 'rey-core'),
		__('Change the site text color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--body-color',
		],
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper',
			'property' => '--body-color',
			'context'  => [ 'editor' ],
		],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'rey-color',
	'settings'    => 'style_link_color',
	'label'       => reycore_customizer__title_tooltip(
		__('Link Color', 'rey-core'),
		__('Change the links color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--link-color',
		],
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper',
			'property' => '--link-color',
			'context'  => [ 'editor' ],
		],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'rey-color',
	'settings'    => 'style_link_color_hover',
	'label'       => reycore_customizer__title_tooltip(
		__('Link Color Hover', 'rey-core'),
		__('Change the links hover color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--link-color-hover',
		],
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper',
			'property' => '--link-color-hover',
			'context'  => [ 'editor' ],
		],
	],
));

/* ------------------------------------ ACCENT COLOR ------------------------------------ */

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'style_accent_color',
	'label'       => reycore_customizer__title_tooltip(
		__('Accent Color', 'rey-core'),
		__('Change the accent color. Some elements are using this color, such as primary buttons.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '#212529',
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'style_accent_color_hover',
	'label'       => reycore_customizer__title_tooltip(
		__('Accent Hover Color', 'rey-core'),
		__('Change the hover accent color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'style_accent_color_text',
	'label'       => reycore_customizer__title_tooltip(
		__('Accent Text Color', 'rey-core'),
		__('Change the text accent color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'style_accent_color_text_hover',
	'label'       => reycore_customizer__title_tooltip(
		__('Accent Text Hover Color', 'rey-core'),
		__('Change the text hover accent color.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'slider',
	'settings'    => 'style_neutral_hue',
	'label'       => esc_html__( 'Neutral Colors Hue', 'rey-core' ),
	'section'     => $section,
	'default'     => 210,
	'choices'     => [
		'min'  => 0,
		'max'  => 360,
		'step' => 1,
	],
	'transport' => 'auto',
	'input_attrs' => [
		'data-control-class' => '--hue-slider',
	],
	'output'      => [
		[
			'element'  		=> ':root',
			'property' 		=> '--neutral-hue',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'style_neutrals_theme',
	'label'       => esc_html__( 'Neutral Colors Theme', 'rey-core' ),
	'section'     => $section,
	'default'     => 'light',
	'choices'     => [
		'light'  => esc_html__('Light', 'rey-core'),
		'dark'  => esc_html__('Dark', 'rey-core'),
	],
] );

/*

reycore_customizer__title([
	'title'   => esc_html__('Site overlays', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'upper'   => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'site_overlay__enable',
	'label'       => esc_html__( 'Enable site overlay', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'site_overlay__bg',
	'label'       => esc_html__( 'Overlay Background Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'active_callback' => [
		[
			'setting'  => 'site_overlay__enable',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'image',
	'settings'    => 'site_overlay__cursor',
	'label'       => esc_html__( 'Overlay Cursor', 'rey-core' ),
	'description' => esc_html__( 'You can change the overlay cursor image.', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'save_as' => 'id',
	],
	'active_callback' => [
		[
			'setting'  => 'site_overlay__enable',
			'operator' => '==',
			'value'    => true,
			],
	],
] );

*/

reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#style-settings',
	'section' => $section
]);
