<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( class_exists('WooCommerce') && !class_exists('ReyCore_Wc_ProductsCategoriesAttributes') ):

class ReyCore_Wc_ProductsCategoriesAttributes
{
	const ASSET_HANDLE = 'reycore-product-catattr-widget';

	public function __construct()
	{

		include_once REY_CORE_MODULE_DIR . basename(__DIR__) . '/widget.php';
		include_once REY_CORE_MODULE_DIR . basename(__DIR__) . '/walker.php';

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
	}

	public function register_assets(){

		reyCoreAssets()->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		reyCoreAssets()->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => REY_CORE_MODULE_URI . basename(__DIR__) . '/script.js',
				'deps'    => ['rey-script', 'reycore-scripts', 'reycore-woocommerce', 'simple-scrollbar'],
				'version'   => REY_CORE_VERSION,
			]
		]);
	}

}

new ReyCore_Wc_ProductsCategoriesAttributes;

endif;
