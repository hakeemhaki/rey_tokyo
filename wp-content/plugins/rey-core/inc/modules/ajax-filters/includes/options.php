<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if(!function_exists('reycore_ajaxfilters__load_options')):
/**
 * Load options
 *
 * @since 1.5.0
 **/
function reycore_ajaxfilters__load_options()
{

	if( !class_exists('ReyCoreKirki') ){
		return;
	}

	$section = 'woocommerce_ajaxfilters';

	ReyCoreKirki::add_section($section, array(
		'title'          => esc_html__('Ajax Filters', 'rey-core'),
		'priority'       => 20,
		'panel'			=> 'woocommerce'
	));

	// ReyCoreKirki::add_field( 'rey_core_kirki', [
	// 	'type'        => 'toggle',
	// 	'settings'    => 'ajaxfilter_enable',
	// 	'label'       => esc_html__( 'Enable Filters', 'rey-core' ),
	// 	'description' => esc_html__( 'This option will enables the Filters module in Rey.', 'rey-core' ),
	// 	'section'     => $section,
	// 	'default'     => true,
	// ] );

	reycore_customizer__title([
		'description' => sprintf(__('To enable filter panels, simply drag and drop any Rey Ajax Filter widget into the <a href="%s" target="_blank">Widgets</a> page, in any of the Shop Sidebar / Filter Panel / Filter Top Bar areas. Read <a href="%s" target="_blank">more here</a>.', 'rey-core'), admin_url('widgets.php') , 'https://support.reytheme.com/kb/add-filters-in-woocommerce/ ') . '<br>',
		'section'     => $section,
		'size'        => 'sm',
		'border'      => 'none',
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'ajaxfilter_animation_type',
		'label'       => esc_html__( 'Animation type', 'rey-core' ),
		'section'     => $section,
		'default'     => 'default',
		'priority'    => 10,
		'choices'     => [
			'default' => esc_html__( 'Fade & Scroll to top', 'rey-core' ),
			'subtle' => esc_html__( 'Suble fade', 'rey-core' ),
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_scroll_to_top',
		'label'       => esc_html__( 'Scroll to top', 'rey-core' ),
		'description' => esc_html__( 'Enable if to enable scroll to top after updating shop loop.', 'rey-core' ),
		'section'     => $section,
		'default'     => true,
		'active_callback' => [
			[
				'setting'  => 'ajaxfilter_animation_type',
				'operator' => '==',
				'value'    => 'default',
			],
		],
		'rey_group_start' => [
			'label' => esc_html__('Fade / Scroll options', 'rey-core')
		]
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'ajaxfilter_scroll_to_top_from',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__( 'Scroll to top target', 'rey-core' ),
			esc_html__( 'Offset when scrolling to top.', 'rey-core' )
		),
		'section'     => $section,
		'default'     => 'grid',
		'choices'     => [
			'grid'  => esc_html_x('Products Grid', 'Customizer control label', 'rey-core'),
			'top'  => esc_html_x('Page top', 'Customizer control label', 'rey-core'),
		],
		'active_callback' => [
			[
				'setting'  => 'ajaxfilter_animation_type',
				'operator' => '==',
				'value'    => 'default',
			],
			[
				'setting'  => 'ajaxfilter_scroll_to_top',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'rey-number',
		'settings'    => 'ajaxfilter_scroll_to_top_offset',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__( 'Scroll to top offset', 'rey-core' ),
			esc_html__( 'Offset when scrolling to top.', 'rey-core' )
		),
		'section'     => $section,
		'default'     => 100,
		'choices'     => [
			'min'  => 0,
			'max'  => 200,
			'step' => 1,
		],
		'active_callback' => [
			[
				'setting'  => 'ajaxfilter_animation_type',
				'operator' => '==',
				'value'    => 'default',
			],
			[
				'setting'  => 'ajaxfilter_scroll_to_top',
				'operator' => '==',
				'value'    => true,
			],
			[
				'setting'  => 'ajaxfilter_scroll_to_top_from',
				'operator' => '==',
				'value'    => 'grid',
			],
		],
		'rey_group_end' => true
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'ajaxfilter_active_position',
		'label'       => esc_html__( 'Active filters position', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'choices'     => [
			'' => esc_html__( 'None (manually added)', 'rey-core' ),
			'above_grid' => esc_html__( 'Above product grid', 'rey-core' ),
			'above_header' => esc_html__( 'Above grid header', 'rey-core' ),
		],
	] );

	ReyCoreKirki::add_field('rey_core_kirki', [
		'type'     => 'text',
		'settings' => 'ajaxfilter_active_clear_text',
		'label'       => esc_html__( 'Reset text', 'rey-core' ),
		'section'  => $section,
		'default'  => esc_html__('Clear all', 'rey-core'),
		'active_callback' => [
			[
				'setting'  => 'ajaxfilter_active_position',
				'operator' => '!=',
				'value'    => '',
			],
		],
		'input_attrs' => [
			'placeholder' => 'eg: Clear all'
		],
		'rey_group_start' => [
			'label' => esc_html__('Extra options', 'rey-core')
		],
		'rey_group_end' => true
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_apply_filter',
		'label'       => esc_html__( '"Apply Filters" button', 'rey-core' ),
		'description' => esc_html__( 'Enable if you want to append an Apply Filter button to submit the filters.', 'rey-core' ),
		'section'     => $section,
		'default'     => false,
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'text',
		'settings'    => 'ajaxfilter_apply_filter_text',
		'label'       => esc_html__( 'Button Text', 'rey-core' ),
		'section'     => $section,
		'default'     => esc_html__('Apply filters', 'rey-core'),
		'active_callback' => [
			[
				'setting'  => 'ajaxfilter_apply_filter',
				'operator' => '==',
				'value'    => true,
			],
		],
		'rey_group_start' => [
			'label' => esc_html__('Button options', 'rey-core')
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_apply_filter_live',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__( 'Show live results', 'rey-core' ),
			esc_html__( 'Will show products results count inside the button.', 'rey-core' )
		),
		'section'     => $section,
		'default'     => false,
		'active_callback' => [
			[
				'setting'  => 'ajaxfilter_apply_filter',
				'operator' => '==',
				'value'    => true,
			],
		],
		'rey_group_end' => true
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_product_sorting',
		'label'       => esc_html__( 'Ajax Sorting', 'rey-core' ),
		'description'       => esc_html__( 'Enable if you want to sort your products via ajax.', 'rey-core' ),
		'section'     => $section,
		'default'     => true,
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_transients',
		'label'       => esc_html__( 'Enable Transients', 'rey-core' ),
		'description' => esc_html__( 'Transients improve query performance by temporarily caching them.', 'rey-core' ),
		'section'     => $section,
		'default'     => true,
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'rey-button',
		'settings'    => 'ajaxfilter_clear_transient',
		'label'       => esc_html__( 'Clear Transients', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'choices'     => [
			'text' => esc_html__('Clear', 'rey-core'),
			'action' => 'ajaxfilter_clear_transient',
		],
		'active_callback' => [
			[
				'setting'  => 'ajaxfilter_transients',
				'operator' => '==',
				'value'    => true,
			],
		],
	] );

	reycore_customizer__title([
		'title'   => esc_html__('SIDEBARS', 'rey-core'),
		'section' => $section,
		'size'    => 'md',
		'upper'   => true,
	]);

	$available_sidebars = [
		'' => esc_html__('- Select -', 'rey-core')
	];

	if( reycore_wc__check_filter_panel() ){
		$available_sidebars['filters-sidebar'] = esc_html__( 'Filters Offcanvas Panel', 'rey-core' );
	}

	if( reycore_wc__check_shop_sidebar() ){
		$available_sidebars['shop-sidebar'] = esc_html__( 'Shop Sidebar', 'rey-core' );
	}

	if( reycore_wc__check_filter_sidebar_top() ){
		$available_sidebars['filters-top-sidebar'] = esc_html__( 'Top Filters Bar', 'rey-core' );
	}

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'ajaxfilter_mobile_button_opens',
		'label'       => esc_html__( 'Mobile Filter opens:', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'choices'     => $available_sidebars,
	] );

	reycore_customizer__title([
		'title'   => esc_html__('SIDEBAR: SHOP', 'rey-core'),
		'description' => esc_html__('Customize the shop sidebar.', 'rey-core'),
		'section' => $section,
		'size'    => 'md',
		'upper'   => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_shop_sidebar_mobile_offcanvas',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__( '"Filters" button on mobile', 'rey-core' ),
			esc_html__( 'On mobiles, this option will transform the shop sidebar into an off-canvas panel and add a Filter button. The option won\'t work though if the Filter Panel sidebar has widgets inside. Make sure to select the "Mobile Filter opens" option.', 'rey-core' )
		),
		'section'     => $section,
		'default'     => true,
	] );

	reycore_customizer__title([
		'title'   => esc_html__('SIDEBAR: FILTER PANEL (OFFCANVAS)', 'rey-core'),
		'description' => esc_html__('Customize the off-canvas Filter panel.', 'rey-core'),
		'section' => $section,
		'size'    => 'md',
		'upper'   => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'ajaxfilter_panel_btn_pos',
		'label'       => esc_html__( 'Button Position', 'rey-core' ),
		'section'     => $section,
		'default'     => 'right',
		'choices'     => [
			'left' => esc_html__( 'Left', 'rey-core' ),
			'right' => esc_html__( 'Right', 'rey-core' ),
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'select',
		'settings'    => 'ajaxfilter_panel_pos',
		'label'       => esc_html__( 'Panel Position', 'rey-core' ),
		'section'     => $section,
		'default'     => 'right',
		'choices'     => [
			'left' => esc_html__( 'Left', 'rey-core' ),
			'right' => esc_html__( 'Right', 'rey-core' ),
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'slider',
		'settings'    => 'ajaxfilter_panel_size',
		'label'       => esc_html__( 'Filter Panel width on mobile (%)', 'rey-core' ),
		'section'     => $section,
		'default'     => 100,
		'choices'     => [
			'min'  => 40,
			'max'  => 100,
			'step' => 1,
		],
		'output' => [
			[
				'media_query'	=> '@media (max-width: 1024px)',
				'element' => '.rey-filterPanel-wrapper.rey-sidePanel',
				'property' => 'width',
				'units' => '%',
			],
		],
	] );

	reycore_customizer__title([
		'title'   => esc_html__('SIDEBAR: TOP BAR', 'rey-core'),
		'description' => esc_html__('Customize the top bar filter sidebar.', 'rey-core'),
		'section' => $section,
		'size'    => 'md',
		'upper'   => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_topbar_buttons',
		'label'       => esc_html__( 'Enable "Filters" label/button', 'rey-core' ),
		'section'     => $section,
		'default'     => true,
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilter_topbar_sticky',
		'label'       => esc_html__( 'Enable sticky on scroll', 'rey-core' ),
		'section'     => $section,
		'default'     => false,
	] );

	reycore_customizer__title([
		'title'   => esc_html__('Typography', 'rey-core'),
		'section' => $section,
		'size'    => 'md',
		'upper'   => true,
	]);

	ReyCoreKirki::add_field('rey_core_kirki', [
		'type'        => 'typography',
		'settings'    => 'ajaxfilter_nav_typo',
		'label'       => esc_attr__('Nav. items typography', 'rey-core'),
		'section'     => $section,
		'default'     => [
			'font-family'      => '',
			'font-size'      => '',
			'line-height'    => '',
			'letter-spacing' => '',
			'font-weight' => '',
			'text-transform' => '',
			'variant' => ''
		],
		'output' => [
			[
				'element' => '.reyajfilter-layered-nav li a',
			],
		],
		'load_choices' => true,
		'transport' => 'auto',
		'responsive' => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'rey-color',
		'settings'    => 'ajaxfilter_nav_item_color',
		'label'       => esc_html__( 'Nav. items color', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'choices'     => [
			'alpha' => true,
		],
		'output' => [
			[
				'element' => '.reyajfilter-layered-nav li a',
				'property' => 'color',
			],
		],
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'rey-color',
		'settings'    => 'ajaxfilter_nav_item_hover_color',
		'label'       => esc_html__( 'Nav. items hover color', 'rey-core' ),
		'section'     => $section,
		'default'     => '',
		'choices'     => [
			'alpha' => true,
		],
		'output' => [
			[
				'element'  => '.reyajfilter-layered-nav li a:hover, .reyajfilter-layered-nav li.chosen a',
				'property' => 'color',
			],
		],
	] );


	// Advanced
	reycore_customizer__title([
		'title'   => esc_html__('Advanced settings', 'rey-core'),
		'section' => $section,
		'size'    => 'lg',
		'upper'   => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilters_exclude_outofstock_variables',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__( 'Fix Out of stock Variables', 'rey-core' ),
			esc_html__( 'By default WooCommerce cannot filter out of stock variable products. This option fixes it, but adds extra queries and might end up making the filters slower.', 'rey-core' )
		),
		'section'     => $section,
		'default'     => false,
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilters__hide_active_tax_filter',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__( 'Show active taxonomy?', 'rey-core' ),
			esc_html__( 'When you access a taxonomy page (product category, archive, etc), the current taxonomy term will be shown in the Active filters block.', 'rey-core' )
		),
		'section'     => $section,
		'default'     => false,
	] );

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'toggle',
		'settings'    => 'ajaxfilters__multiple_all',
		'label'       => reycore_customizer__title_tooltip(
			esc_html__( 'Multiple filters all?', 'rey-core' ),
			esc_html__( 'When enabling multiple filters, by default only current category siblings are available for being selected. Enabling this will allow to select any item from any category or subcategory.', 'rey-core' )
		),
		'section'     => $section,
		'default'     => false,
	] );

	reycore_customizer__title([
		'title'   => esc_html__('Taxonomies', 'rey-core'),
		'section' => $section,
		'size'    => 'md',
		'upper'   => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'repeater',
		'settings'    => 'ajaxfilters_taxonomies',
		'label'       => esc_html__('Register taxonomies', 'rey-core'),
		'description' => __('.Register taxonomies to use the Ajax Filter Taxonomies Widget.', 'rey-core'),
		'section'     => $section,
		'row_label' => [
			'type' => 'text',
			'value' => esc_html__('Taxonomy', 'rey-core'),
		],
		'button_label' => esc_html__('New Taxonomy', 'rey-core'),
		'default'      => [],
		'fields' => [
			'id' => [
				'type'        => 'text',
				'label'       => esc_html__('Taxonomy', 'rey-core'),
				'default' => esc_html__('product_some_tax', 'rey-core'),
			],
			'name' => [
				'type'        => 'text',
				'label'       => esc_html__('Taxonomy Name', 'rey-core'),
				'default' => esc_html__('Some Tax', 'rey-core'),
			],
		],
	] );

	reycore_customizer__title([
		'title'   => esc_html__('Meta Queries (Custom Fields)', 'rey-core'),
		'section' => $section,
		'size'    => 'md',
		'upper'   => true,
	]);

	ReyCoreKirki::add_field( 'rey_core_kirki', [
		'type'        => 'repeater',
		'settings'    => 'ajaxfilters_meta_queries',
		'label'       => esc_html__('Register Meta Query', 'rey-core'),
		'description' => __('Register a meta query to use with the Ajax Filter - Meta Widget.', 'rey-core'),
		'section'     => $section,
		'row_label' => [
			'value' => esc_html__('Meta query', 'rey-core'),
			'type'  => 'field',
			'field' => 'title',
		],
		'button_label' => esc_html__('New Meta Query', 'rey-core'),
		'default'      => [],
		'fields' => [
			'title' => [
				'type'        => 'text',
				'label'       => esc_html__('Label', 'rey-core'),
				'default' => '',
			],
			'key' => [
				'type'        => 'text',
				'label'       => esc_html__('Key', 'rey-core'),
				'default' => '',
			],
			'operator' => [
				'type'        => 'select',
				'label'       => esc_html__('Operator', 'rey-core'),
				'default' => '==',
				'choices' => reycore__get_operators()
			],
			'value' => [
				'type'        => 'text',
				'label'       => esc_html__('Value', 'rey-core'),
				'default' => '',
			],
		],
	] );

}
endif;

add_action('reycore/customizer/init', 'reycore_ajaxfilters__load_options');
