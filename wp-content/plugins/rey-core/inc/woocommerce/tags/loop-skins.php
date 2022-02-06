<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_LoopSkins') ):

	class ReyCore_WooCommerce_LoopSkins
	{
		private static $_instance = null;

		private $_skins = [];

		private function __construct()
		{
			add_filter( 'reycore/woocommerce/loop/get_skins', [$this, 'get_all_skins']);
			add_action( 'init', [$this, 'register_skins'], 5 );
		}

		function get_all_skins(){
			return $this->_skins;
		}

		function register_skins(){
			do_action( 'reycore/woocommerce/loop/register_skin' );
		}

		function add_skin( $skin = [] ){
			$this->_skins = array_merge($this->_skins, (array) $skin);
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_WooCommerce_LoopSkins
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	function reyCoreLoopSkins(){
		return ReyCore_WooCommerce_LoopSkins::getInstance();
	}

	reyCoreLoopSkins();

endif;
