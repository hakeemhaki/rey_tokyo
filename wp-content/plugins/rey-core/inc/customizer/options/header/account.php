<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Header > Account
 */

$section = 'header_account_options';

ReyCoreKirki::add_section($section, array(
    'title'          => esc_attr__('Account', 'rey-core'),
	'priority'       => 50,
	'panel'			 => 'header_options'
));

$default_header_conditions = [
	'setting'  => 'header_layout_type',
	'operator' => '==',
	'value'    => 'default',
];

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_enable_account',
	'label'       => esc_html__( 'Enable My Account panel?', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		$default_header_conditions,
	],
] );

$account__default_header_conditions = [
	'setting'  => 'header_enable_account',
	'operator' => '==',
	'value'    => true,
];

reycore_customizer__title([
	'title'       => esc_html__('Button settings', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'bottom',
	'upper'       => true,
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
	],
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_account_type',
	'label'       => esc_html__( 'Type of button', 'rey-core' ),
	'section'     => $section,
	'default'     => 'text',
	'choices'     => [
		'text' => esc_html__( 'Text', 'rey-core' ),
		'icon' => esc_html__( 'Icon', 'rey-core' ),
		'both_before' => esc_html__( 'Text & Icon Before', 'rey-core' ),
		'both_after' => esc_html__( 'Text & Icon After', 'rey-core' ),
	],
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'header_account_text',
	'label'    => esc_html__( 'Button text label', 'rey-core' ),
	'section'  => $section,
	'default'  => esc_html__( 'ACCOUNT', 'rey-core' ),
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account_type',
			'operator' => 'in',
			'value'    => ['text', 'both_before', 'both_after'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'header_account_text_logged_in',
	'label'    => reycore_customizer__title_tooltip(
		__('Button text label (Logged in)', 'rey-core'),
		__('Optional. Text to display when user is logged in.', 'rey-core')
	),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account_type',
			'operator' => 'in',
			'value'    => ['text', 'both_before', 'both_after'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_account_icon_type',
	'label'       => esc_html__( 'Icon Type', 'rey-core' ),
	'section'     => $section,
	'default'     => 'rey-icon-user',
	// 'priority'    => 40,
	'choices'     => [
		'rey-icon-user' => esc_html__( 'User Icon', 'rey-core' ),
		'reycore-icon-heart' => esc_html__( 'Heart Icon (Requires Rey-Core plugin)', 'rey-core' ),
	],
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account_type',
			'operator' => 'in',
			'value'    => ['icon', 'both_before', 'both_after'],
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_account_mobile',
	'label'       => esc_html__( 'Hide on Mobiles?', 'rey-core' ),
	'section'     => $section,
	'default'     => false,
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '==',
			'value'    => 'default',
		],
	],
] );

reycore_customizer__title([
	'title'       => esc_html__('Drop panel settings', 'rey-core'),
	'section'     => $section,
	'size'        => 'md',
	'border'      => 'bottom',
	'upper'       => true,
]);

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_account__panel_display',
	'label'       => esc_html__( 'Display as', 'rey-core' ),
	'section'     => $section,
	'default'     => 'drop',
	'choices'     => [
		'drop' => esc_html__( 'Drop Down', 'rey-core' ),
		'offcanvas' => esc_html__( 'Off-canvas panel', 'rey-core' ),
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_account__enable_forms',
	'label'       => esc_html__( 'Enable Forms/Account menu', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_account__enable_ajax',
	'label'       => esc_html__( 'Enable Forms Ajax', 'rey-core' ),
	'section'     => $section,
	'default'     => apply_filters('reycore/header/account/ajax_forms', true),
	'active_callback' => [
		[
			'setting'  => 'header_account__enable_forms',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'select',
	'settings'    => 'header_account_redirect_type',
	'label'       => esc_html__( 'Login/Register action on success', 'rey-core' ),
	'section'     => $section,
	'default'     => 'load_menu',
	'choices'     => [
		'load_menu' => esc_html__( 'Show "My Account Menu"', 'rey-core' ),
		'refresh' => esc_html__( 'Refresh same page', 'rey-core' ),
		'myaccount' => esc_html__( 'Default (My Account)', 'rey-core' ),
		'url' => esc_html__( 'Go to custom URL', 'rey-core' ),
	],
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account__enable_forms',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'     => 'text',
	'settings' => 'header_account_redirect_url',
	'label'    => reycore_customizer__title_tooltip(
		__('Redirect URL', 'rey-core'),
		__('Add here the URL where you want the visitor to redirect after login or register.', 'rey-core')
	),
	'section'  => $section,
	'default'  => '',
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account_redirect_type',
			'operator' => '==',
			'value'    => 'url',
		],
		[
			'setting'  => 'header_account__enable_forms',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_account__close_on_scroll',
	'label'    => reycore_customizer__title_tooltip(
		__('Close on scroll', 'rey-core'),
		__('Will close the drop panel when scrolling outside the panel, in the page.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		[
			'setting'  => 'header_account__panel_display',
			'operator' => '==',
			'value'    => 'drop',
		],
	],
] );

reycore_customizer__separator([
	'section' => $section,
	'id'      => 'a2',
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'repeater',
	'settings'    => 'header_account_menu_items',
	'label'       => esc_html__('Menu - Extra Items', 'rey-core'),
	'description' => sprintf(__('Add extra menu items to the My Account menu. To hide default menu items, please access <a href="%s" target="_blank">WooCommerce Account endpoints</a> and empty the fields you don\'t want to display', 'rey-core'), admin_url('admin.php?page=wc-settings&tab=advanced') ),
	'section'     => $section,
	'row_label' => [
		'value' => esc_html__('Menu item', 'rey-core'),
		'type'  => 'field',
		'field' => 'text',
	],
	'button_label' => esc_html__('New menu item', 'rey-core'),
	'default'      => [],
	'fields' => [
		'text' => [
			'type'        => 'text',
			'label'       => esc_html__('Text', 'rey-core'),
		],
		'url' => [
			'type'        => 'text',
			'label'       => esc_html__('URL', 'rey-core'),
		],
		'target' => [
			'type'        => 'select',
			'label'       => esc_html__('Target', 'rey-core'),
			'default'     => '',
			'choices'     => [
				'' => esc_html__('Same window', 'rey-core'),
				'_blank' => esc_html__('New window', 'rey-core')
			],
		],
	],
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account__enable_forms',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_account_menu_items__glue',
	'label'    => reycore_customizer__title_tooltip(
		__('Merge navigations', 'rey-core'),
		__('This will force the 2 naviations to merge.', 'rey-core')
	),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account__enable_forms',
			'operator' => '==',
			'value'    => true,
		],
	],
] );

reycore_customizer__separator([
	'section' => $section,
	'id'      => 'a1',
]);


ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_account_wishlist',
	'label'       => esc_html__( 'Enable Wishlist', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
	],
] );

ReyCoreKirki::add_field( 'rey_core_kirki', [
	'type'        => 'toggle',
	'settings'    => 'header_account_wishlist_counter',
	'label'       => esc_html__( 'Wishlist Counter', 'rey-core' ),
	'section'     => $section,
	'default'     => true,
	'active_callback' => [
		// [
		// 	'setting'  => 'header_enable_account',
		// 	'operator' => '==',
		// 	'value'    => true,
		// ],
		[
			'setting'  => 'header_account_wishlist',
			'operator' => '==',
			'value'    => true,
		],
	],
	'rey_group_start' => [
		'label' => esc_html__('Wishlist settings', 'rey-core')
	],
	'rey_group_end' => true
] );


reycore_customizer__notice([
	'section'     => $section,
	'default'     => __('In case these options doesn\'t seem to work, please check if you\'re using a Header Global Section and make sure the "Header - Account" element doesn\'t have the Override settings option enabled eg: <a href="https://d.pr/i/rbTmOb" target="_blank">https://d.pr/i/rbTmOb</a>.', 'rey-core'),
	'active_callback' => [
		[
			'setting'  => 'header_layout_type',
			'operator' => '!=',
			'value'    => 'default',
		],
	],
] );


reycore_customizer__help_link([
	'url' => 'https://support.reytheme.com/kb/customizer-header-settings/#account',
	'section' => $section
]);
