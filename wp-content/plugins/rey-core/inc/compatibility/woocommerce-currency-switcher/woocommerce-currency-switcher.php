<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( class_exists('WooCommerce') && !class_exists('ReyCore_Compatibility__WoocommerceCurrencySwitcher') ):

	class ReyCore_Compatibility__WoocommerceCurrencySwitcher
	{
		private static $_instance = null;

		private $settings = [];

		private $data = [];

		const ASSET_HANDLE = 'reycore-woocs';

		private $supported = [
			'WOOCS_STARTER' => 'get_woocs_data',
			'WOOMULTI_CURRENCY_F' => 'get_woomulticurrency_data',
			'WOOMULTI_CURRENCY' => 'get_woomulticurrency_premium_data',
			'WC_Aelia_CurrencySwitcher' => 'get_aelia_data',
			'woocommerce_wpml' => 'get_wcml_data',
		];

		// https://wordpress.org/plugins/woo-multi-currency/
		// https://wordpress.org/plugins/woocommerce-currency-switcher/

		private $plugin = false;

		private function __construct()
		{
			foreach ($this->supported as $class => $function) {
				if( class_exists( $class ) ){
					$this->plugin = $function;
				}
			}

			if( ! $this->plugin ){
				return;
			}

			add_action( 'init', [ $this, 'init' ] );
			add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'rey/header/row', [$this, 'header'], 70);
			add_action( 'rey/mobile_nav/footer', [$this, 'mobile'], 20);
		}

		public function init(){

			if( wp_doing_cron() || wp_doing_ajax() ){
				return;
			}

			$this->settings = apply_filters('reycore/woocs/params', [
				'symbol' => 'yes', // no / first
				'always_show_caret' => false,
				'layout' => 'dropdown'
			]);

			$this->{$this->plugin}();
		}

		public function get_woocs_data(){

			if( !isset($GLOBALS['WOOCS']) ){
				return;
			}

			$woocs = $GLOBALS['WOOCS'];

			$this->data = [
				'type' => 'woocs',
				'all_currencies' => apply_filters('woocs_currency_manipulation_before_show', $woocs->get_currencies()),
				'current' => $woocs->current_currency,
				'shortcode' => '[woocs style="no"]'
			];

		}

		public function get_woomulticurrency_data(){

			$settings = WOOMULTI_CURRENCY_F_Data::get_ins();

			$currencies       = $settings->get_list_currencies();
			$links            = $settings->get_links();

			$all_currencies = [];

			foreach($currencies as $key => $currency){
				$all_currencies[$key] = [
					'type' => 'woomulticurrency',
					'name' => $key,
					'symbol' => get_woocommerce_currency_symbol( $key ),
					'link' => $links[$key]
				];
			}

			$this->data = [
				'all_currencies' => $all_currencies,
				'current' => $settings->get_current_currency()
			];

		}

		public function get_woomulticurrency_premium_data(){

			$settings = WOOMULTI_CURRENCY_Data::get_ins();

			$currencies       = $settings->get_list_currencies();
			$links            = $settings->get_links();

			$all_currencies = [];

			foreach($currencies as $key => $currency){
				$all_currencies[$key] = [
					'type' => 'woomulticurrency',
					'name' => $key,
					'symbol' => get_woocommerce_currency_symbol( $key ),
					'link' => $links[$key]
				];
			}

			$this->data = [
				'type' => 'woomulticurrency',
				'all_currencies' => $all_currencies,
				'current' => $settings->get_current_currency()
			];

		}

		public function get_aelia_data(){

			$currencies = [];

			$settings_controller = WC_Aelia_CurrencySwitcher::settings();
			$enabled_currencies = $settings_controller->get_enabled_currencies();
			$exchange_rates = $settings_controller->get_exchange_rates();
			$woocommerce_currencies = get_woocommerce_currencies();

			foreach($exchange_rates as $currency => $fx_rate) {

				// Display only Currencies supported by WooCommerce
				$currency_name = !empty($woocommerce_currencies[$currency]) ? $woocommerce_currencies[$currency] : false;

				if(!empty($currency_name)) {

					// Skip currencies that are not enabled
					if(!in_array($currency, $enabled_currencies)) {
						continue;
					}

					// Display only currencies with a valid Exchange Rate
					if($fx_rate > 0) {
						$currencies[$currency] = $currency_name;
					}
				}
			}

			// aelia_cs_currency
			$all_currencies = [];

			foreach($currencies as $key => $currency){
				$all_currencies[$key] = [
					'name' => $key,
					'symbol' => get_woocommerce_currency_symbol( $key ),
					'link' => '#'
					// 'link' => add_query_arg('aelia_cs_currency', $key)
				];
			}

			// aelia_customer_country
			$this->data = [
				'type' => 'aelia',
				'all_currencies' => $all_currencies,
				'current' => WC_Aelia_CurrencySwitcher::instance()->get_selected_currency()
			];

		}

		public function header( $custom_settings = [] ){

			if( empty($this->data) ){
				return;
			}

			$settings = wp_parse_args($custom_settings, $this->settings);

			$classes = [
				'rey-woocurrency',
				'rey-woocurrency--'. $settings['layout'],
				'--symbol-' . $settings['symbol']
			];

			if( $settings['always_show_caret'] ){
				$classes[] = '--always-show-caret';
			}

			if( isset($settings['show_mobile']) && $settings['show_mobile'] ){
				$classes[] = '--show-mobile';
			}

			if( isset($this->data['type']) ){
				$classes[] = '--type-' . $this->data['type'];
			}

			if( $settings['layout'] === 'dropdown' ){

				$classes[] = 'rey-headerIcon';
				$classes[] = 'rey-headerDropSwitcher';
				$classes[] = 'rey-header-dropPanel';

				reyCoreAssets()->add_styles('rey-header-drop-panel');
				reyCoreAssets()->add_scripts('rey-drop-panel');
			}

			$html = sprintf('<div class="%s">', implode(' ', $classes));

			if( isset($this->data['shortcode']) && !empty($this->data['shortcode']) ){
				$html .= do_shortcode($this->data['shortcode']);
			}

			if( $settings['layout'] === 'dropdown' ){

				$html .= sprintf('<button class="btn rey-headerIcon-btn rey-header-dropPanel-btn"><span class="rey-woocurrency-name">%s</span><span class="rey-woocurrency-symbol">%s</span></button>', $this->data['current'], $this->data['all_currencies'][$this->data['current']]['symbol']);

				$html .= '<div class="rey-header-dropPanel-content">';
			}

				$html .= '<ul>';

				foreach ($this->data['all_currencies'] as $key => $currency) {
					$html .= sprintf( '<li class="%3$s"><a href="%5$s" data-currency="%4$s" class="rey-woocurrency-item" data-type="%6$s"><span class="rey-woocurrency-name">%1$s</span><span class="rey-woocurrency-symbol">%2$s</span></a></li>',
						$currency['name'],
						$currency['symbol'],
						$this->data['current'] === $key ? '--active' : '',
						$key,
						isset( $currency['link'] ) ? esc_url($currency['link']) : '#',
						isset($this->data['type']) ? esc_attr($this->data['type']) : ''
					);
				}
				$html .= '</ul>';

			if( $settings['layout'] === 'dropdown' ){
				$html .= '</div>';
			}

			$html .= '</div>';

			echo apply_filters('reycore/woocs/header_html', $html, $this->data);
		}

		function mobile(){

			if( empty($this->data) ){
				return;
			}

			$html = '<ul class="rey-woocurrencyMobile rey-mobileNav--footerItem rey-dropSwitcher-mobile">';
			$html .= '<li class="rey-dropSwitcher-mobileTitle">'. esc_html_x('CURRENCY:', 'Currency switcher title in Mobile panel.', 'rey-core') .'</li>';
			foreach ($this->data['all_currencies'] as $key => $currency) {
				$html .= sprintf( '<li class="%2$s"><a href="%4$s" data-currency="%3$s" class="rey-woocurrency-item" data-type="%5$s"><span class="rey-woocurrency-name">%1$s</span></a></li>',
					$currency['name'],
					$this->data['current'] === $key ? '--active' : '',
					$key,
					isset( $currency['link'] ) ? esc_url($currency['link']) : '#',
					isset($this->data['type']) ? esc_attr($this->data['type']) : ''
				);
			}
			$html .= '</ul>';

			echo apply_filters('reycore/woocs/header_mobile_html', $html, $this->data);
		}

		function aelia_country(){
			global $woocommerce;

			if( ! class_exists('Aelia_WC_TaxDisplayByCountry_RequirementsChecks') ){
				return;
			}

			$active = WC_Aelia_CurrencySwitcher::instance()->get_customer_country();
			$countries = $woocommerce->countries->get_allowed_countries();

			echo '<div class="rey-woocurrency --aelia-countries">';
				echo '<label class="btn btn-line">';

					if( isset($countries[$active]) ){
						printf('<span>%s</span>', $countries[$active] );
					}

					echo '<select class="aelia-countries" name="' . Aelia\WC\CurrencySwitcher\Definitions::ARG_CUSTOMER_COUNTRY . '">';
						foreach($countries as $country_code => $country_name) {
							printf(
								'<option value="%1$s" %3$s>%2$s</option>',
								$country_code,
								$country_name,
								selected($country_code, $active, false)
							);
						}
					echo '</select>';
				echo '</label>';
			echo '</div>';
		}

		public function enqueue_scripts(){
			reyCoreAssets()->add_scripts(self::ASSET_HANDLE);
			reyCoreAssets()->add_styles(self::ASSET_HANDLE);
		}

		public function register_scripts(){
            wp_register_style( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/style.css', [], REY_CORE_VERSION );
            wp_register_script( self::ASSET_HANDLE, REY_CORE_COMPATIBILITY_URI . basename(__DIR__) . '/script.js', ['jquery'], REY_CORE_VERSION, true );
		}

		public function get_wcml_data(){

			if(is_admin()){
				return;
			}

			global $woocommerce_wpml;

			if( ! $woocommerce_wpml ){
				return;
			}

			if( ! $woocommerce_wpml->multi_currency ){
				return;
			}

			$wcml_settings = $woocommerce_wpml->get_settings();
			$currencies       = $wcml_settings['currency_options'];
			$all_currencies = [];

			foreach($currencies as $key => $currency){
				$all_currencies[$key] = [
					'type' => 'wcml',
					'name' => $key,
					'symbol' => get_woocommerce_currency_symbol( $key ),
					// 'link' => $links[$key]
				];
			}

			$this->data = [
				'type' => 'wcml',
				'all_currencies' => $all_currencies,
				'current' => $woocommerce_wpml->multi_currency->get_client_currency()
			];

			wp_enqueue_script( 'wcml-mc-scripts' );
		}

		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_Compatibility__WoocommerceCurrencySwitcher::getInstance();
endif;
