<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$section = 'blog_post_options';


ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Blog Posts', 'rey-core'),
	'priority'       => 4,
	'panel'			=> 'blog_options'
));

reycore_customizer__title([
	'title'   => esc_html__('Layout', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'border'  => 'top',
	'upper'   => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'post_width',
	'label'    => reycore_customizer__title_tooltip(
		__('Post width', 'rey-core'),
		__('Select the post width style.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'c',
    'choices'     => array(
        'c' => esc_attr__('Compact', 'rey-core'),
        'e' => esc_attr__('Expanded', 'rey-core')
    ),
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'custom_post_width',
	'label'       => esc_html__( 'Post Width', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		// 'min'  => 100,
		'max'  => 1920,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'post_width',
			'operator' => '==',
			'value'    => 'c',
		],
	],
	'transport'   => 'auto',
	'output'      		=> [
		[
			'element'  => ':root',
			'property' => '--post-width',
			'units' 	=> 'px',
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Extra settings', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'wide_size',
	'label'       => esc_html__( '"Wide" alignment Size', 'rey-core' ) . ' (vw)',
	'section'     => $section,
	'default'     => 25,
	'choices'     => [
		'min'  => 0,
		'max'  => 80,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'post_width',
			'operator' => '==',
			'value'    => 'c',
		],
	],
	'transport'   => 'auto',
	'output'      => [
		[
			'element'  => ':root',
			'property' => '--post-align-wide-size',
			'units' 	=> 'vw',
		],
	],
	'rey_group_end' => true
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'blog_post__comments_btn',
	'label'    => reycore_customizer__title_tooltip(
		__('Comments - Show large button', 'rey-core'),
		__('If enabled, the comments section will be hidden and a large outline button will be shown instead to toggle open the comments or join the conversation.', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'blog_post__comments_expanded',
	'label'       => esc_html__( 'Start expanded', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'blog_post__comments_btn',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Extra settings', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'blog_post__comments_btn_style',
	'label'       => esc_html__( 'Button style', 'rey-core' ),
	'section'     => $section,
	'default'     => 'secondary-outline',
	'choices'     => [
		'secondary-outline' => esc_html__( 'Outline', 'rey-core' ),
		'primary' => esc_html__( 'Filled', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'blog_post__comments_btn',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'blog_post__links',
	'label'       => esc_html__( 'Links style', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Underline', 'rey-core' ),
		'clean' => esc_html__( 'Clean', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'blog_post__links_color',
	'label'       => esc_html__( 'Links Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output' => [
		[
			'element'  => ':root',
			'property' => '--post-content-links-color',
		]
	],
	'rey_group_start' => [
		'label' => esc_html__('Extra settings', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'blog_post__links_color_hover',
	'label'       => esc_html__( 'Links Hover Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output' => [
		[
			'element'  => ':root',
			'property' => '--post-content-links-hover-color',
		]
	],
	'rey_group_end' => true
] );

// Meta
reycore_customizer__title([
	'title'   => esc_html__('Meta', 'rey-core'),
	'section' => $section,
	'size'    => 'md',
	'border'  => 'top',
	'upper'   => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_date_visibility',
	'label'    => reycore_customizer__title_tooltip(
		__('Date', 'rey-core'),
		__('Display the post date?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_comment_visibility',
	'label'    => reycore_customizer__title_tooltip(
		__('Comments', 'rey-core'),
		__('Enable comments number?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_categories_visibility',
	'label'    => reycore_customizer__title_tooltip(
		__('Categories', 'rey-core'),
		__('Enable categories?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_author_visibility',
	'label'    => reycore_customizer__title_tooltip(
		__('Author', 'rey-core'),
		__('Enable author?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_read_visibility',
	'label'    => reycore_customizer__title_tooltip(
		__('Read duration', 'rey-core'),
		__('Enable read duration?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));
ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_author_box',
	'label'    => reycore_customizer__title_tooltip(
		__('Author Box', 'rey-core'),
		__('Enable author box?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_tags',
	'label'    => reycore_customizer__title_tooltip(
		__('Tags', 'rey-core'),
		__('Enable post tags?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_navigation',
	'label'    => reycore_customizer__title_tooltip(
		__('Navigation', 'rey-core'),
		__('Enable post navigation?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_thumbnail_visibility',
	'label'    => reycore_customizer__title_tooltip(
		__('Display Featured Image', 'rey-core'),
		__('Enable thumbnail or other media?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'post_thumbnail_image_size',
	'label'    => reycore_customizer__title_tooltip(
		__('Thumb Size', 'rey-core'),
		__('Select the featured image intrinsic size.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => reyCoreHelper()::get_all_image_sizes(),
	'active_callback' => [
		[
			'setting'  => 'post_thumbnail_visibility',
			'operator' => '!=',
			'value'    => '',
			],
	],
	'rey_group_start' => [
		'label' => esc_html__('Extra settings', 'rey-core')
	],
	'rey_group_end' => true
] );

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_cat_text_visibility',
	'label'    => reycore_customizer__title_tooltip(
		__('Large Category Text', 'rey-core'),
		__('Enable the big category text behind the post title?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'post_share',
	'label'    => reycore_customizer__title_tooltip(
		__('Social Sharing links', 'rey-core'),
		__('Enable Sharing links in the post footer?', 'rey-core')
	),
	'section'     => $section,
	'default'     => '1',
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'post_share_style',
	'label'       => esc_html__( 'Sharing Icons style', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Default (Colored)', 'rey-core' ),
		'round_c' => esc_html__( 'Colored Rounded', 'rey-core' ),
		'minimal' => esc_html__( 'Minimal', 'rey-core' ),
		'round_m' => esc_html__( 'Minimal Rounded', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'post_share',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Extra settings', 'rey-core')
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'post_share_icons_list',
	'label'       => esc_html__( 'Social Sharing Items', 'rey-core' ),
	'section'     => $section,
	'default'     => ['facebook-f', 'twitter', 'linkedin', 'pinterest-p', 'mail'],
	'multiple'    => 15,
	'choices'     => reycore__social_icons_list_select2('share'),
	'active_callback' => [
		[
			'setting'  => 'post_share',
			'operator' => '==',
			'value'    => true,
		],
	],
	'input_attrs' => [
		'data-control-class' => '--block-label',
	],
	'rey_group_end' => true
] );

reycore_customizer__title([
	'title'       => esc_html__('Typography', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_blog_post_title',
	'label'       => esc_attr__('Post Title', 'rey-core'),
	'section'     => $section,
	'show_variants' => true,
	'default'     => array(
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'text-transform' => '',
		'font-weight' => '',
		'variant' => '',
		'color' => '',
	),
	'output' => array(
		array(
			'element' => '.single-post .rey-postTitle',
		),
	),
	'load_choices' => true,
	'responsive' => true,

));


ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'typography',
	'settings'    => 'typography_blog_post_content',
	'label'       => esc_attr__('Post Content', 'rey-core'),
	'section'     => $section,
	'default'     => array(
		'font-family'      => '',
		'font-size'      => '',
		'line-height'    => '',
		'letter-spacing' => '',
		'font-weight' => '',
		'variant' => '',
		'color' => '',
	),
	'output' => array(
		array(
			'element' => '.single-post .rey-postContent, .single-post .rey-postContent a',
		),
	),
	'load_choices' => true,
	'responsive' => true,
));

reycore_customizer__title([
	'title'       => esc_html__('Misc.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'select',
	'settings'    => 'blog_post_sidebar',
	'label'    => reycore_customizer__title_tooltip(
		__('Sidebar', 'rey-core'),
		__('Select the placement of sidebar or disable it. Default is right.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 'disabled',
    'choices'     => [
        'inherit' => esc_attr__('Inherit', 'rey-core'),
        'left' => esc_attr__('Left', 'rey-core'),
        'right' => esc_attr__('Right', 'rey-core'),
        'disabled' => esc_attr__('Disabled', 'rey-core'),
	],
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'blog_teasers',
	'label'       => esc_html__('Blog Teasers', 'rey-core'),
	'description' => __('Assign blocks into posts.', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Blog teaser', 'rey-core'),
		'type'  => 'field',
		'field' => 'type',
	],
	'button_label' => esc_html__('New teaser', 'rey-core'),
	'default'      => [],
	'fields'       => [

		'position' => [
			'type'        => 'select',
			'label'       => esc_html__('Position in page', 'rey-core'),
			'choices'     => [
				'' => esc_html__('- Select -', 'rey-core'),
				'0%' => esc_html__('Top', 'rey-core'),
				'25%' => esc_html__('In content - 25%', 'rey-core'),
				'50%' => esc_html__('In content - 50%', 'rey-core'),
				'75%' => esc_html__('In content - 75%', 'rey-core'),
				'100%' => esc_html__('Bottom', 'rey-core'),
			],
		],

		'align' => [
			'type'        => 'select',
			'label'       => esc_html__('Align', 'rey-core'),
			'choices'     => [
				'' => esc_html__('- Select -', 'rey-core'),
				'left' => esc_html__('Left', 'rey-core'),
				'center' => esc_html__('Center', 'rey-core'),
				'right' => esc_html__('Right', 'rey-core'),
			],
		],

		'offset_align' => [
			'type'        => 'select',
			'label'       => esc_html__('Offset Align', 'rey-core'),
			'choices'     => [
				'' => esc_html__('- Select -', 'rey-core'),
				'semi' => esc_html__('Semi-offset', 'rey-core'),
				'full' => esc_html__('Full-offset', 'rey-core'),
			],
			'condition' => [
				[
					'setting'  => 'align',
					'operator' => '!=',
					'value'    => 'center',
				],
			],
		],

		'heading' => [
			'type'        => 'text',
			'label'       => esc_html__('Heading text', 'rey-core'),
		],

		'width' => [
			'type'        => 'number',
			'label'       => esc_html__('Block Width', 'rey-core'),
			'condition' => [
				[
					'setting'  => 'align',
					'operator' => '!=',
					'value'    => 'center',
				],
			],
		],

		'type' => [
			'type'        => 'select',
			'label'       => esc_html__('Block Type', 'rey-core'),
			'choices'     => [
				'' => esc_html__('- Select -', 'rey-core'),
				'related' => esc_html__('Related posts', 'rey-core'),
				'single' => esc_html__('Single post', 'rey-core'),
				'global_section' => esc_html__('Global section', 'rey-core'),
			],
		],

		'global_section' => [
			'type'        => 'select',
			'label'       => esc_html__('Select Global Section', 'rey-core'),
			'choices'     => class_exists('ReyCore_GlobalSections') ? ReyCore_GlobalSections::get_global_sections('generic', ['' => '- Select -']) : [],
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => '==',
					'value'    => 'global_section',
				],
			],
		],

		'manual_posts' => [
			'type'        => 'select',
			'label'       => esc_html__('Choose Post', 'rey-core'),
			'query_args' => [
				'type' => 'posts',
				'post_type' => 'post',
			],
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => '==',
					'value'    => 'single',
				],
			],
		],

		'related_limit' => [
			'type'    => 'number',
			'label'   => esc_html__('Posts Limit', 'rey-core'),
			'default' => 3,
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => '==',
					'value'    => 'related',
				],
			],
		],

		'show_image' => [
			'type'    => 'select',
			'label'   => esc_html__('Show Image', 'rey-core'),
			'default' => '',
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => 'in',
					'value'    => ['related', 'single'],
				],
			],
			'choices'     => [
				'' => esc_html__('- Default -', 'rey-core'),
				'yes' => esc_html__('Yes', 'rey-core'),
				'no' => esc_html__('No', 'rey-core'),
			],
		],

		'image_alignment' => [
			'type'    => 'select',
			'label'   => esc_html__('Image alignment', 'rey-core'),
			'default' => '',
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => 'in',
					'value'    => ['related', 'single'],
				],
				[
					'setting'  => 'show_image',
					'operator' => '!=',
					'value'    => 'no',
				],
			],
			'choices'     => [
				'' => esc_html__('- Default -', 'rey-core'),
				'top' => esc_html__('Top', 'rey-core'),
				'left' => esc_html__('Left', 'rey-core'),
				'right' => esc_html__('Right', 'rey-core'),
			],
		],

		'show_date' => [
			'type'    => 'select',
			'label'   => esc_html__('Show date', 'rey-core'),
			'default' => '',
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => 'in',
					'value'    => ['related', 'single'],
				],
			],
			'choices'     => [
				'' => esc_html__('- Default -', 'rey-core'),
				'yes' => esc_html__('Yes', 'rey-core'),
				'no' => esc_html__('No', 'rey-core'),
			],
		],

		'show_categories' => [
			'type'    => 'select',
			'label'   => esc_html__('Show categories', 'rey-core'),
			'default' => '',
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => 'in',
					'value'    => ['related', 'single'],
				],
			],
			'choices'     => [
				'' => esc_html__('- Default -', 'rey-core'),
				'yes' => esc_html__('Yes', 'rey-core'),
				'no' => esc_html__('No', 'rey-core'),
			],
		],

		'numerotation' => [
			'type'    => 'select',
			'label'   => esc_html__('Show numerotation', 'rey-core'),
			'default' => '',
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => 'in',
					'value'    => ['related', 'single'],
				],
			],
			'choices'     => [
				'' => esc_html__('- Default -', 'rey-core'),
				'yes' => esc_html__('Yes', 'rey-core'),
				'no' => esc_html__('No', 'rey-core'),
			],
		],

		'columns' => [
			'type'    => 'number',
			'label'   => esc_html__('Columns', 'rey-core'),
			'default' => '',
			'choices' => [
				'min' => 1,
				'max' => 5,
				'step' => 1,
			],
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => '==',
					'value'    => 'related',
				],
			],
		],

		'title_size' => [
			'type'    => 'select',
			'label'   => esc_html__('Title size', 'rey-core'),
			'default' => '',
			'condition' => [
				[
					'setting'  => 'type',
					'operator' => 'in',
					'value'    => ['related', 'single'],
				],
			],
			'choices'     => [
				'' => esc_html__('- Default -', 'rey-core'),
				'xxs' => esc_html__('XXS', 'rey-core'),
				'xs' => esc_html__('XS', 'rey-core'),
				'sm' => esc_html__('SM', 'rey-core'),
				'md' => esc_html__('MD', 'rey-core'),
				'lg' => esc_html__('LG', 'rey-core'),
			],
		],

	],
] );


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-blog-settings/#blog-post',
	'section' => $section
]);
