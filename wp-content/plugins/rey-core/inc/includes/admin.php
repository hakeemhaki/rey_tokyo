<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_init', function(){

	if( ! class_exists('ACF') ){
		return false;
	}

	if( reycore__acf_get_field('rey_widgets_blocks_layout', REY_CORE_THEME_NAME) === true ){
		return;
	}

	// Disables the block editor from managing widgets in the Gutenberg plugin.
	add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
	// Disables the block editor from managing widgets.
	add_filter( 'use_widgets_block_editor', '__return_false' );
});

if(!function_exists('reycore__admin_bar_css')):

function reycore__admin_bar_css() {

	if ( is_admin_bar_showing() ) { ?>

	   <style type="text/css">
			#wpadminbar .rey-abQuickMenu .rey-abQuickMenu-logo {
				width: 45px;
				height: inherit;
				line-height: inherit;
				display: flex;
			}
			#wpadminbar .rey-abQuickMenu .rey-abQuickMenu-logo img {
				display: block;
				width: 100%;
				max-width: 32px;
				opacity: .5;
				margin: 5px auto 0;
			}
			.rey-abQuickMenu:hover .rey-abQuickMenu-logo img {
				opacity: .7;
			}
			#wpadminbar .rey-abQuickMenu .rey-abQuickMenu-top {
				margin-top: 10px;
			}
			#wpadminbar .rey-abQuickMenu .rey-abQuickMenu-notices {
				margin-top: 15px;
			}
	   </style>

		<?php
		wp_enqueue_style('reycore-frontend-admin');
	}
}
endif;
add_action( 'admin_head', 'reycore__admin_bar_css' );
add_action( 'wp_head', 'reycore__admin_bar_css' );


add_action( 'admin_menu', function(){
	if( apply_filters('reycore/admin_menu', true) ){
		return;
	}
	remove_menu_page( reycore__get_dashboard_page_id() );
});


if(!function_exists('reycore__admin_bar_links')):
	/**
	 * Admin bar links
	 *
	 * @since 1.4.0
	 **/
	function reycore__admin_bar_links( $admin_bar ) {

		if( ! current_user_can('administrator') ){
			return;
		}

		if( ! apply_filters('reycore/admin_bar_menu', true) ){
			return;
		}

		if( !($dashboard_id = reycore__get_dashboard_page_id()) ){
			return;
		}

		$nodes['main'] = [
			'title'  => apply_filters('reycore/admin_bar_menu/html', sprintf('<span class="rey-abQuickMenu-logo"><img src="%1$s" alt="%2$s"><span class="screen-reader-text">%2$s</span></span>', REY_CORE_URI  . 'assets/images/logo-simple-white.svg', esc_html__( 'Rey - Quick Menu', 'rey-core' ))),
			'href'  => add_query_arg([
				'page' => $dashboard_id
				], admin_url( 'admin.php' )
			),
			'meta_title' => esc_html__( 'Rey - Quick Menu', 'rey-core' ),
			'class' => 'rey-abQuickMenu'
		];

		$nodes['dashboard'] = [
			'title'  => esc_html__( 'Dashboard', 'rey-core' ),
			'href'  => add_query_arg([
				'page' => $dashboard_id
				], admin_url( 'admin.php' )
			),
			'top' => true,
		];

		if( class_exists('ReyCore_GlobalSections') ):
			$nodes['gs'] = [
				'title'  => esc_html__( 'Global Sections', 'rey-core' ),
				'href'  => add_query_arg([
					'post_type' => ReyCore_GlobalSections::POST_TYPE
					], admin_url( 'edit.php' )
				),
			];
		endif;

		$nodes['settings'] = [
			'title'  => esc_html__( 'Theme Settings', 'rey-core' ),
			'href'  => add_query_arg([
				'page' => REY_CORE_THEME_NAME . '-settings',
				], admin_url( 'admin.php' )
			),
			'meta_title' => esc_html__( 'Generic theme settings.', 'rey-core' ),
		];

		$customize_url = urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );

		$nodes['customize'] = [
			'title'  => esc_html__( 'Customize Options', 'rey-core' ),
			'href'  => add_query_arg([
				'url' => $customize_url,
				], admin_url( 'customize.php' )
			),
			'meta_title' => esc_html__( 'Customize Options', 'rey-core' ),
			'top' => true,
			'nodes' => [
				[
					'title' => esc_html__( 'General settings', 'rey-core' ),
					'href'  => add_query_arg([
						'url' => $customize_url,
						'autofocus[panel]' => 'general_options'
						], admin_url( 'customize.php' )
					),
				],
				[
					'title' => esc_html__( 'Header', 'rey-core' ),
					'href'  => add_query_arg([
						'url' => $customize_url,
						'autofocus[panel]' => 'header_options'
						], admin_url( 'customize.php' )
					),
					'meta_title' => esc_html__( 'Customize the site header.', 'rey-core' ),
					'top' => true
				],
				[
					'title' => esc_html__( 'Footer', 'rey-core' ),
					'href'  => add_query_arg([
						'url' => $customize_url,
						'autofocus[panel]' => 'footer_options'
						], admin_url( 'customize.php' )
					),
					'meta_title' => esc_html__( 'Customize the footer.', 'rey-core' ),
				],
				[
					'title' => esc_html__( 'Page Cover', 'rey-core' ),
					'href'  => add_query_arg([
						'url' => $customize_url,
						'autofocus[panel]' => 'cover_panel'
						], admin_url( 'customize.php' )
					),
					'meta_title' => esc_html__( 'Customize the page covers (page headers).', 'rey-core' ),
				],
				[
					'title' => esc_html__( 'Blog', 'rey-core' ),
					'href'  => add_query_arg([
						'url' => $customize_url,
						'autofocus[panel]' => 'blog_options'
						], admin_url( 'customize.php' )
					),
					'meta_title' => esc_html__( 'Customize the site blog.', 'rey-core' ),
				],

			]
		];

		if( class_exists('WooCommerce') ):

			$nodes['customize']['nodes'][] = [
				'title' => esc_html__( 'Woo > Catalog > Layout', 'rey-core' ),
				'href'  => add_query_arg([
					'url' => $customize_url,
					'autofocus[section]' => 'woocommerce_product_catalog'
					], admin_url( 'customize.php' )
				),
				'meta_title' => esc_html__( 'Customize the looks of the product catalog (eg: categories pages)', 'rey-core' ),
				'top' => true,
			];

			$nodes['customize']['nodes'][] = [
					'title' => esc_html__( 'Woo > Catalog > Components', 'rey-core' ),
					'href'  => add_query_arg([
						'url' => $customize_url,
						'autofocus[section]' => 'woocommerce_product_catalog_components'
						], admin_url( 'customize.php' )
					),
					'meta_title' => esc_html__( 'Enable or disable components in product catalog (categories).', 'rey-core' ),
				];

			$nodes['customize']['nodes'][] = [
				'title'  => esc_html__( 'Woo > Catalog > Misc.', 'rey-core' ),
				'href'  => add_query_arg([
					'url' => $customize_url,
					'autofocus[section]' => 'woocommerce_product_catalog_misc'
					], admin_url( 'customize.php' )
				),
				'meta_title' => esc_html__( 'Various options', 'rey-core' ),
			];

			$nodes['customize']['nodes'][] = [
				'title'  => esc_html__( 'Woo > Product Page > Layout', 'rey-core' ),
				'href'  => add_query_arg([
					'url' => $customize_url,
					'autofocus[section]' => 'shop_product_section_layout'
					], admin_url( 'customize.php' )
				),
				'meta_title' => esc_html__( 'Customize the looks of the product page.', 'rey-core' ),
				'top' => true,
			];

			$nodes['customize']['nodes'][] = [
				'title'  => esc_html__( 'Woo > Product Page > Components', 'rey-core' ),
				'href'  => add_query_arg([
					'url' => $customize_url,
					'autofocus[section]' => 'shop_product_section_components'
					], admin_url( 'customize.php' )
				),
				'meta_title' => esc_html__( 'Enable or disable components in product page.', 'rey-core' ),
			];

			$nodes['customize']['nodes'][] = [
				'title'  => esc_html__( 'Woo > Product Page > Content', 'rey-core' ),
				'href'  => add_query_arg([
					'url' => $customize_url,
					'autofocus[section]' => 'shop_product_section_content'
					], admin_url( 'customize.php' )
				),
				'meta_title' => esc_html__( 'Customize the product page content.', 'rey-core' ),
			];

			$nodes['customize']['nodes'][] = [
				'title'  => esc_html__( 'Woo > Product Images', 'rey-core' ),
				'href'  => add_query_arg([
					'url' => $customize_url,
					'autofocus[section]' => 'woocommerce_product_images'
					], admin_url( 'customize.php' )
				),
				'meta_title' => esc_html__( 'WooCommerce catalog & page product images', 'rey-core' ),
				'top' => true,
			];

		endif;

		$nodes['css'] = [
			'title'  => esc_html__( 'Custom CSS', 'rey-core' ),
			'href'  => add_query_arg([
				'url' => $customize_url,
				'autofocus[section]' => 'custom_css'
				], admin_url( 'customize.php' )
			),
			'meta_title' => esc_html__( 'Add additional CSS.', 'rey-core' ),
		];

		if ( current_user_can( 'administrator' ) && function_exists('reycore__css_cache_is_enabled') && reycore__css_cache_is_enabled() ) {
			$nodes['refresh_css'] = [
				'title'  => esc_html__( 'Refresh dynamic CSS', 'rey-core' ),
				'href'  => '#',
				'meta_title' => esc_html__( 'Refresh the dynamic CSS generated in Customizer.', 'rey-core' ),
				'class' => 'qm-refresh-css',
			];
		}

		if ( current_user_can( 'administrator' ) && function_exists('reyCoreAssets')) {
			if( ($asset_settings = reyCoreAssets()->settings) && ! $asset_settings['load_natural_css'] ){
				if( $asset_settings['store_data'] || $asset_settings['save_css'] || $asset_settings['save_js'] ){

					$href = '?clear_assets=2';
					$id = get_queried_object_id();

					if( is_singular() || is_home() || is_front_page() || (class_exists('WooCommerce') && is_shop()) ){
						$href .= '&type=post&id='. $id;
					}
					else if(is_tax() || is_archive()){
						$href .= '&type=term&id='. $id;
					}

					$nodes['refresh_assets'] = [
						'title'  => esc_html__( 'Refresh Assets', 'rey-core' ),
						'href'  => $href,
						'meta_title' => esc_html__( 'Refresh the combined and minified assets.', 'rey-core' ),
						'class' => 'qm-refresh-assets',
					];

				}
			}
		}

		$nodes['help'] = [
			'title'  => esc_html__( 'Help KB', 'rey-core' ),
			'href'  => 'https://support.reytheme.com/',
			'meta_title' => esc_html__( 'Get help online.', 'rey-core' ),
			'new' => true,
			'top' => true,
			'nodes' => [
				[
					'title' => esc_html__( 'Getting started', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/getting-started/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Settings', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/settings/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Elementor', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/elementor/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'WooCommerce', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/woocommerce/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Global Sections', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/global-sections/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Customization FAQ\'s', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/customization-faqs/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Demos FAQ\'s', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/demos-faq/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Rey Modules', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/rey-modules/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Troubleshooting', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kbtopic/troubleshooting/',
					'new' => true,
				],
			]
		];

		$nodes['faq'] = [
			'title'  => esc_html__( 'FAQ', 'rey-core' ),
			'href'  => 'https://support.reytheme.com/',
			'meta_title' => esc_html__( 'Frequently asked questions', 'rey-core' ),
			'new' => true,
			'nodes' => [
				[
					'title' => esc_html__( 'How to work faster with Rey?', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kb/design-work-faster-with-rey/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'What are Global Sections?', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kb/what-exactly-are-global-sections/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'How to add custom CSS?', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kb/how-to-add-custom-css/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'How to get started with Elementor?', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kb/getting-started-with-elementor/',
					'new' => true,
				],
				[
					'title' => esc_html__( 'Optimising your website’s speed', 'rey-core' ),
					'href'  => 'https://support.reytheme.com/kb/optimising-your-websites-speed/',
					'new' => true,
				],
			]
		];

		$nodes = apply_filters('reycore/admin_bar_menu/nodes', $nodes);

		foreach($nodes as $i => $node){

			$admin_bar->add_node(
				[
					'id'     => $i === 'main' ? $dashboard_id : $dashboard_id . $i,
					'title'  => $node['title'],
					'href'  => $node['href'],
					'parent' => $i === 'main' ? '' : $dashboard_id,
					'meta'   => [
						'title' => isset($node['meta_title']) ? $node['meta_title'] : '',
						'target' => isset($node['new']) ? '_blank' : '',
						'class' => (isset($node['top']) ? 'rey-abQuickMenu-top' : '') . ' ' . (isset($node['class']) ? $node['class'] : ''),
					],
				]
			);

			if( isset($node['nodes']) ){
				foreach ($node['nodes'] as $k => $subnode) {
					$admin_bar->add_node(
						[
							'id'     => $dashboard_id . $i . $k,
							'title'  => $subnode['title'],
							'href'  => $subnode['href'],
							'parent' => $dashboard_id . $i,
							'meta'   => [
								'title' => isset($subnode['meta_title']) ? $subnode['meta_title'] : '',
								'target' => isset($subnode['new']) ? '_blank' : '',
								'class' => (isset($subnode['top']) ? 'rey-abQuickMenu-top' : '') . ' ' . (isset($subnode['class']) ? $subnode['class'] : ''),
							],
						]
					);
				}
			}

		}
	}
endif;

add_action( 'admin_bar_menu', 'reycore__admin_bar_links', 200 );
