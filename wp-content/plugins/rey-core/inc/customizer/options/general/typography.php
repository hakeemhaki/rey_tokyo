<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$section = 'typography_general';

ReyCoreKirki::add_section($section, array(
	'title'          => esc_attr__('Typography', 'rey-core'),
	'panel'			=> 'general_options'
));

/**
 * Default
 */
$rey_attributes_selectors = [
	'h1' => 'h1, .h1, .rey-pageTitle, .rey-postItem-catText',
	'h2' => 'h2, .h2',
	'h3' => 'h3, .h3',
	'h4' => 'h4, .h4',
	'h5' => 'h5, .h5',
	'h6' => 'h6, .h6',
];

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_primary',
	'label'       => esc_html__('PRIMARY FONT', 'rey-core'),
	'section'     => $section,
	'default'     => [
		'font-family' => '',
	],
	'load_choices' => 'exclude',
	'input_attrs' => [
		'data-should-collapse' => 'no'
	]
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_secondary',
	'label'       => esc_html__('SECONDARY FONT', 'rey-core'),
	'section'     => $section,
	'default'     => [
		'font-family' => '',
	],
	'load_choices' => 'exclude',
	'input_attrs' => [
		'data-should-collapse' => 'no'
	]
));


/* ------------------------------------ Typo settings ------------------------------------ */

reycore_customizer__title([
	'title'   => esc_html__('Typography settings', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'border'  => 'top',
	'upper'   => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'typography_inherit_elementor',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x('Inherit Typography from Elementor Kit', 'Customizer control title', 'rey-core'),
		sprintf( _x('If enabled, the Typography settings will be inherited from Elementor Site Settings. <a href="%s" target="_blank">Learn more</a>.', 'Customizer control description', 'rey-core'), 'https://elementor.com/help/global-layout-settings/'),
		[ 'size'=>290, 'clickable'=>true ]
	),
	'section'     => $section,
	'default'     => false,
] );

/**
 * Body Font
 */
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_body',
	'label'       => esc_attr__('Site typography', 'rey-core'),
	'description' => __('Site typography settings.', 'rey-core'),
	'section'     => $section,
	'default'     => array(
		'font-family' => 'var(--primary-ff)',
		'font-size'   => '',
		'line-height' => '',
		'variant' => '',
		'font-weight' => '',
	),
	'output'      => [
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper',
			'property' => 'font-family',
			'context'  => [ 'editor' ],
		],
	],
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));


/**
 * Headings
 */

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_heading1',
	'label'       => esc_attr__('Heading 1', 'rey-core'),
	'section'     => $section,
	'transport' => 'auto',
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
			'element' => $rey_attributes_selectors['h1'],
		),
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper h1, .edit-post-visual-editor.editor-styles-wrapper .h1',
			'property' => 'font-family',
			'context'  => [ 'editor' ],
		],
	),
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_heading2',
	'label'       => esc_attr__('Heading 2', 'rey-core'),
	'section'     => $section,
	// 'priority'    => 60,
	'transport' => 'auto',
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
			'element' => $rey_attributes_selectors['h2'],
		),
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper h2, .edit-post-visual-editor.editor-styles-wrapper .h2',
			'property' => 'font-family',
			'context'  => [ 'editor' ],
		],
	),
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_heading3',
	'label'       => esc_attr__('Heading 3', 'rey-core'),
	'section'     => $section,
	// 'priority'    => 70,
	'transport' => 'auto',
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
			'element' => $rey_attributes_selectors['h3'],
		),
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper h3, .edit-post-visual-editor.editor-styles-wrapper .h3',
			'property' => 'font-family',
			'context'  => [ 'editor' ],
		],
	),
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_heading4',
	'label'       => esc_attr__('Heading 4', 'rey-core'),
	'section'     => $section,
	// 'priority'    => 80,
	'transport' => 'auto',
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
			'element' => $rey_attributes_selectors['h4'],
		),
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper h4, .edit-post-visual-editor.editor-styles-wrapper .h4',
			'property' => 'font-family',
			'context'  => [ 'editor' ],
		],
	),
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_heading5',
	'label'       => esc_attr__('Heading 5', 'rey-core'),
	'section'     => $section,
	// 'priority'    => 90,
	'transport' => 'auto',
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
			'element' => $rey_attributes_selectors['h5'],
		),
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper h5, .edit-post-visual-editor.editor-styles-wrapper .h5',
			'property' => 'font-family',
			'context'  => [ 'editor' ],
		],
	),
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_heading6',
	'label'       => esc_attr__('Heading 6', 'rey-core'),
	'section'     => $section,
	// 'priority'    => 100,
	'transport' => 'auto',
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
			'element' => $rey_attributes_selectors['h6'],
		),
		[
			'element'  => '.edit-post-visual-editor.editor-styles-wrapper h6, .edit-post-visual-editor.editor-styles-wrapper .h6',
			'property' => 'font-family',
			'context'  => [ 'editor' ],
		],
	),
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_page_title',
	'label'       => esc_attr__('Page Titles', 'rey-core'),
	'section'     => $section,
	'transport' => 'auto',
	'default'     => array(
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'text-transform' => '',
		'variant' => '',
		'font-weight' => '',
	),
	'output' => [
		[
			'element' => '.rey-pageTitle',
		]
	],
	'load_choices' => true,
	'responsive' => true,
	'active_callback' => [
		[
			'setting'  => 'typography_inherit_elementor',
			'operator' => '!=',
			'value'    => true,
			],
	],
));




reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-general-settings/#typography-settings',
	'section' => $section
]);
