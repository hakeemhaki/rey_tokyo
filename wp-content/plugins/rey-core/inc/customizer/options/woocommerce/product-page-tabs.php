<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = 'shop_product_section_tabs';

/**
 * PRODUCT PAGE - tabs settings
 */

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Product Page - Tabs/Blocks', 'rey-core'),
	'priority'       => 15,
	'panel'			=> 'woocommerce'
));


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'product_content_layout',
	'label'       => esc_html__( 'Tabs Layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'blocks',
	'rey_preset' => 'page',
	'choices'     => [
		'blocks' => esc_html__( 'As Blocks', 'rey-core' ),
		'tabs' => esc_html__( 'Tabs', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_content_tabs_disable_titles',
	'label'       => esc_html__( 'Disable titles inside Tabs', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'tabs',
		],
	],
] );


reycore_customizer__title([
	'title'       => esc_html__('Built-in Tabs (Blocks)', 'rey-core'),
	'description' => esc_html__('Customize the built-in tabs/blocks.', 'rey-core'),
	'section'     => $section,
	'size'        => 'lg',
	'border'      => 'bottom',
	'upper'       => true,
]);

/* ------------------------------------ DESCRPTION ------------------------------------ */


reycore_customizer__title([
	'title'       => esc_html__('Description', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'none',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'product_tab_description',
    'label'       => esc_html__('Enable Description', 'rey-core'),
	'section'     => $section,
	'default'     => true,
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_content_blocks_desc_toggle',
	'label'       => esc_html__( 'Toggle long description', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'product_content_blocks_title',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Description Title', 'rey-core'),
		__('If you want to completely hide the title, please add "0".', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Description', 'rey-core'),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_description_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 10,
    'choices'     => [
		'min'  => 10,
		'max'  => 200,
		'step' => 5,
	],
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'product_content_blocks_desc_stretch',
	'label'       => esc_html__( 'Stretch Description Block', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'blocks',
		],
	],
] );

/* ------------------------------------ INFORMATION ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Information', 'rey-core'),
	// 'description' => esc_html__('You can add a block of custom content right after the product summary.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field('rey_core_kirki', array(
	'type'        => 'toggle',
	'settings'    => 'product_info',
    'label'       => esc_html__('Custom Information', 'rey-core'),
    'description' => __('Select if you want to add a tab with text content. You can override or disable per product.', 'rey-core'),
	'section'     => $section,
	'default'     => '',
));

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'single__product_info_title',
	'label'       => esc_html__( 'Title', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Information', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'product_info',
			'operator' => '!=',
			'value'    => '',
		],
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'tabs',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_custom_info_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 15,
    'choices'     => [
		'min'  => 10,
		'max'  => 200,
		'step' => 5,
	],
	'active_callback' => [
		[
			'setting'  => 'product_info',
			'operator' => '!=',
			'value'    => '',
		],
	],
]);

ReyCoreKirki::add_field('rey_core_kirki', [
	'type'        => 'editor',
	'settings'    => 'product_info_content',
	'label'       => esc_html__( 'Add Content', 'rey-core' ),
	'section'     => $section,
	'default'     => '',
	'active_callback' => [
		[
			'setting'  => 'product_info',
			'operator' => '!=',
			'value'    => '',
		],
	],
	'partial_refresh'    => [
		'product_info_content' => [
			'selector'        => '.rey-wcPanel--information',
			'render_callback' => function() {
				return get_theme_mod('product_info_content', '');
			},
		],
	],
] );


/* ------------------------------------ Specs ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('Additional Info. / SPECIFICATIONS', 'rey-core'),
	'description' => __('Content inside Additional Information / Specifications block/tab.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_specifications_block',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Enable tab/block', 'rey-core'),
		__('Select the visibility of Specifications (Additional Information) block/tab', 'rey-core')
	),
	'section'     => $section,
	'default'     => true
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'text',
	'settings'    => 'single_specifications_title',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Title', 'rey-core'),
		__('If you want to completely hide the title, please add "0".', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'input_attrs'     => [
		'placeholder' => esc_html__('eg: Specifications', 'rey-core'),
	],
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_specifications_block_dimensions',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Enable Dimensions Info', 'rey-core'),
		__('Select the visibility of Weight/Dimensions rows.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single_specifications_position',
	'label'       => reycore_customizer__title_tooltip(
		esc_html__('Spec. Position', 'rey-core'),
		__('Select if you want to move the Specifications block in product summary.', 'rey-core')
	),
	'section'     => $section,
	'default'     => '',
	'choices'     => [
		'' => esc_html__( 'Default (as block/tab)', 'rey-core' ),
		'29' => esc_html__( 'After short description', 'rey-core' ),
		'39' => esc_html__( 'After Add to cart button', 'rey-core' ),
		'49' => esc_html__( 'After Meta', 'rey-core' ),
		'499' => esc_html__( 'Last one', 'rey-core' ),
	],
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_specs_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 20,
    'choices'     => [
		'min'  => 10,
		'max'  => 200,
		'step' => 5,
	],
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'woocommerce_product_page_attr_desc',
	'label'       => esc_html__( 'Show attributes descriptions', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'custom',
	'settings'    => 'single_specs_customize_desc',
	'section'     => $section,
	'default'     => sprintf(__('Here\'s a <a href="%s" target="_blank">quick article</a> on how to add or remove rows inside.', 'rey-core'), 'https://support.reytheme.com/kb/customize-the-attributes-inside-specifications-additional-information/'),
	'active_callback' => [
		[
			'setting'  => 'single_specifications_block',
			'operator' => '==',
			'value'    => true,
		],
	],
] );


/* ------------------------------------ Review ------------------------------------ */

reycore_customizer__title([
    'title'       => esc_html__('REVIEWS', 'rey-core'),
	'description' => esc_html__('Content inside reviews block/tab.', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'top',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-color',
	'settings'    => 'star_rating_color',
	'label'       => esc_html__( 'Stars Color', 'rey-core' ),
	'section'     => $section,
	'default'     => '#ff4545',
	'choices'     => [
		'alpha' => true,
	],
	'output'      		=> [
		[
			'element'  		=> ':root',
			'property' 		=> '--star-rating-color',
		],
	],
	'transport' => 'auto'
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_reviews_start_opened',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x('Start Opened', 'Customizer control label', 'rey-core'),
		_x('By default the review block is hidden and can be opened clicking the large Reviews button. If this option is enabled though, the reviews block will always load opened first.', 'Customizer control description', 'rey-core')
	),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'blocks',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_reviews_ajax',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x('Ajax load reviews', 'Customizer control label', 'rey-core'),
		_x('This option will make the reviews to load dynamically on demand.', 'Customizer control description', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_reviews_info',
	'label'       => reycore_customizer__title_tooltip(
		esc_html_x('Rating Infographic', 'Customizer control label', 'rey-core'),
		_x('Will show an infographic summary of the reviews and ratings.', 'Customizer control description', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_reviews_avatar',
	'label'       => esc_html_x('Show avatars?', 'Customizer control label', 'rey-core'),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'single_reviews_layout',
			'operator' => '!=',
			'value'    => 'minimal',
			],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single_reviews_layout',
	'label'       => esc_html__( 'Reviews layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'default',
	'choices'     => [
		'default' => esc_html__( 'Default', 'rey-core' ),
		'minimal' => esc_html__( 'Minimal', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single_tabs__reviews_outside',
	'label'       => esc_html__( 'Make Reviews tab as block', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'product_content_layout',
			'operator' => '==',
			'value'    => 'tabs',
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'rey-number',
	'settings'    => 'single_reviews_priority',
    'label'       => reycore_customizer__title_tooltip(
		esc_html__('Priority', 'rey-core'),
		__('Choose the order of the blocks/tabs.', 'rey-core')
	),
	'section'     => $section,
	'default'     => 30,
    'choices'     => [
		'min'  => 5,
		'max'  => 200,
		'step' => 5,
	],
]);

/* ------------------------------------ CUSTOM TABS ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Custom Tabs', 'rey-core'),
	'description' => esc_html__('Add extra tabs. Content will be edited in product page settings.', 'rey-core'),
	'section'     => $section,
	'size'        => 'lg',
	'border'      => 'bottom',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'single__custom_tabs',
	'label'       => esc_html__('Add Custom tabs', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Tab', 'rey-core'),
		'type'  => 'field',
		'field' => 'text',
	],
	'button_label' => esc_html__('New Tab', 'rey-core'),
	'default'      => [],
	'fields' => [
		'text' => [
			'type'        => 'text',
			'label'       => esc_html__('Title', 'rey-core'),
			'default'       => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Tab title', 'rey-core'),
			],
		],
		'priority' => [
			'type'        => 'text',
			'label'       => esc_html__('Priority', 'rey-core'),
			'default'       => 40,
		],
		'content' => [
			'type'        => 'textarea',
			'label'       => esc_html__('Default Content', 'rey-core'),
			'default'       => '',
		],
	],
] );

/* ------------------------------------ CUSTOM TABS ------------------------------------ */

reycore_customizer__title([
	'title'       => esc_html__('Summary Accordion', 'rey-core'),
	'description' => esc_html__('Display some of the product tabs in the Summary content, as an accordion (or tabs layout). *Note: Requires refresh if you\'ve just added custom tabs.', 'rey-core'),
	'section'     => $section,
	'size'        => 'lg',
	'border'      => 'bottom',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single__accordion_layout',
	'label'       => esc_html__( 'Layout', 'rey-core' ),
	'section'     => $section,
	'default'     => 'acc',
	'choices'     => [
		'acc' => esc_html__( 'Accordions', 'rey-core' ),
		'tabs' => esc_html__( 'Tabs', 'rey-core' ),
	],
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'single__accordion_position',
	'label'       => esc_html__( 'Position', 'rey-core' ),
	'section'     => $section,
	'default'     => '39',
	'choices'     => [
		'39' => esc_html__( 'After Add to cart', 'rey-core' ),
		'45' => esc_html__( 'After Product Meta', 'rey-core' ),
		'100' => esc_html__( 'Summary End', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'single__accordion_first_active',
	'label'       => esc_html__( 'First start opened', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
] );


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'single__accordion_items',
	'label'       => esc_html__('Add Accordion Items', 'rey-core'),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Item', 'rey-core'),
		'type'  => 'field',
		'field' => 'item',
	],
	'button_label' => esc_html__('New Item', 'rey-core'),
	'default'      => [],
	'fields' => [
		'item' => [
			'type'        => 'select',
			'label'       => esc_html__('Select item', 'rey-core'),
			'default'     => '',
			'choices'     => call_user_func( function() {

				$items = [
					'' => esc_html__('- Select -', 'rey-core'),
					'description' => esc_html__('Description', 'rey-core'),
					'short_desc' => esc_html__('Short Description', 'rey-core'),
					'information' => esc_html__('Information', 'rey-core'),
					'additional_information' => esc_html__('Additional Info / Specs.', 'rey-core'),
					'reviews' => esc_html__('Reviews', 'rey-core'),
				];

				$custom_tabs = get_theme_mod('single__custom_tabs', []);

				foreach ($custom_tabs as $key => $value) {
					$items[ 'custom_tab_' . $key] = $value['text'];
				}

				return $items;
			}),
		],

		'title' => [
			'type'        => 'text',
			'label'       => esc_html__('Custom Title', 'rey-core'),
			'default'       => '',
		]
	],
] );
