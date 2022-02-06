<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( !class_exists('ReyCore_WooCommerce_Refunds') ):

	class ReyCore_WooCommerce_Refunds
	{

		public function __construct() {
			add_action('init', [$this, 'init']);
		}

		function init(){

			if( ! $this->is_enabled() ){
				return;
			}

			$this->settings = apply_filters('reycore/woocommerce/returns', [
				'subject' => esc_html__('Return request from %s', 'rey-core'),
				'heading' => esc_html__('Return request', 'rey-core'),
				'email' => get_bloginfo('admin_email'),
				'order_text' => '{{ID}} ( {{DATE}} / {{TOTAL}}{{CURRENCY}} )',
				'error_not_sent' => esc_html__('Something went wrong and the request hasn\'t been sent. Please retry later!', 'rey-core'),
				'success_msg' => esc_html__('Return request sent successfully.', 'rey-core'),
				'endpoint' => 'refund-request'
			]);

			add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
			add_action( 'wp_enqueue_scripts', [$this, 'load_scripts']);
			add_rewrite_endpoint( $this->settings['endpoint'], EP_ROOT | EP_PAGES );
			add_filter( 'query_vars', [$this, 'set_query_vars'], 0 );
			add_filter( 'woocommerce_account_menu_items', [$this, 'set_menu_item'] );
			add_action( "woocommerce_account_{$this->settings['endpoint']}_endpoint", [$this, 'add_content'] );
			add_action( 'wp_ajax_rey_refund_request_order_products', [$this, 'refund_request_order_products']);
			add_action( 'wp_ajax_rey_refund_request_submit', [$this, 'refund_request_submit']);
		}

		public function register_assets(){

			reyCoreAssets()->register_asset('scripts', [
				'reycore-wc-refunds' => [
					'src'     => REY_CORE_URI . 'assets/js/woocommerce/refunds.js',
					'deps'    => ['reycore-woocommerce'],
					'version'   => REY_CORE_VERSION,
				],
			]);

		}

		public function add_content() {

			if( ! is_user_logged_in() ){
				printf('<p>%s</p>', esc_html__('Please login to show form.', 'rey-core') );
				return;
			}

			echo '<div class="rey-refundsPage">';

				printf('<h2 class="rey-refundsPage-title">%s</h2>', get_theme_mod('refunds__page_title', esc_html__('Request Return', 'rey-core')) );
				printf('<div class="rey-refundsPage-before">%s</div>', wpautop(do_shortcode(get_theme_mod('refunds__content', ''))) );

				$orders = $this->get_orders();

				if( ! empty($orders) ){
					$this->get_form();
				}
				else {
					printf('<p>%s</p>', esc_html__('No orders yet.', 'rey-core'));
				}

			echo '</div>';
		}

		public function get_orders(){
			return wc_get_orders([
				'customer_id' => get_current_user_id(),
				'return' => 'ids',
			]);
		}

		public function get_form(){
			?>
			<div class="rey-refundsPage-orders">

				<form action="" class="woocommerce-form" method="post">

					<div class="rey-refundsPage-response --empty"></div>

					<p class="form-row">
						<label for="rey_refund__orders"><?php esc_html_e('Select order', 'rey-core') ?> <span class="required">*</span></label>
						<select name="rey_refund__orders" id="rey_refund__orders" required>
							<option value=""><?php esc_html_e('-- Select --', 'rey-core') ?></option>
							<?php
							$orders = wc_get_orders([
								'customer_id' => get_current_user_id(),
							]);
							foreach ($orders as $order) {

								$data = sprintf('%s ( %s / %s%s )',
									sprintf( esc_html__( 'Order #%d', 'rey-core' ), $order->get_id() ),
									wc_format_datetime($order->get_date_created()),
									$order->get_total(),
									get_woocommerce_currency_symbol()
								);

								$data = $this->settings['order_text'];

								$data = str_replace( '{{ID}}', sprintf( esc_html__( 'Order #%d', 'rey-core' ), $order->get_id() ), $data);
								$data = str_replace( '{{DATE}}', wc_format_datetime($order->get_date_created()), $data);
								$data = str_replace( '{{TOTAL}}', $order->get_total(), $data);
								$data = str_replace( '{{CURRENCY}}', get_woocommerce_currency_symbol(), $data);

								printf('<option value="%d">%s</option>', $order->get_id(), $data);
							} ?>
						</select>
					</p>

					<p class="form-row --hidden">
						<label for="rey_refund__order_items"><?php esc_html_e('Select item from order', 'rey-core') ?> <span class="required">*</span></label>
						<select name="rey_refund__order_items[]" id="rey_refund__order_items" class="__products-items" multiple required></select>
						<small><em><?php esc_html_e('Hold Ctrl/Cmd to select multiple items.', 'rey-core') ?></em></small>
					</p>

					<p class="form-row">
						<label for="rey_refund__observation"><?php esc_html_e('Reason and observations', 'rey-core') ?> <span class="required">*</span></label>
						<textarea name="rey_refund__observation" id="rey_refund__observation" class="__reasons" required></textarea>
					</p>

					<p class="form-row">
						<button type="submit" class="btn btn-primary"><?php esc_html_e('Send Request', 'rey-core') ?></button>
					</p>

				</form>
			</div>

			<?php
		}

		function refund_request_order_products(){

			if ( ! check_ajax_referer( 'rey_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if( !(isset($_REQUEST['order']) && $order_id = absint($_REQUEST['order'])) ){
				wp_send_json_error( esc_html__('Order id not provided!', 'rey-core') );
			}

			$order = wc_get_order($order_id);
			$data = [];

			foreach ( $order->get_items() as $item_id => $item ) {
				$name = $item->get_name();
				$data[$name] = $name . '( ' . ($item->get_total() + $item->get_subtotal_tax()) . get_woocommerce_currency_symbol() . ' )';
			}

			wp_send_json_success($data);

		}

		function refund_request_submit(){

			if ( ! check_ajax_referer( 'rey_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			$fields = [];

			$default_fields = [
				'rey_refund__orders' => [
					'error' => esc_html__('Order id not provided!', 'rey-core')
				],
				'rey_refund__order_items' => [
					'error' => esc_html__('No product items selected!', 'rey-core')
				],
				'rey_refund__observation' => [
					'error' => esc_html__('No reason provided!', 'rey-core')
				],
			];

			$errors = [];

			foreach ($default_fields as $key => $value) {

				if( ! isset($_REQUEST[$key]) ){
					$errors[] = $this->make_notice($value['error']);
					continue;
				}

				if( ! ($fields[$key] = reycore__clean($_REQUEST[$key])) ){
					$errors[] = $this->make_notice($value['error']);
					continue;
				}
			}

			if( ! empty($errors) ){
				wp_send_json_success([
					'errors' => $errors
				]);
			}

			$message[] = sprintf( '<strong>%s:</strong> <a href="%s">#%s</a>',
				esc_html_x('Order', 'Refunds form mail title', 'rey-core'),
				admin_url( sprintf('post.php?post=%d&action=edit', $fields['rey_refund__orders']) ),
				$fields['rey_refund__orders']
			);

			$products = [];

			$order = wc_get_order($fields['rey_refund__orders']);

			foreach ( $order->get_items() as $item_id => $item ) {

				$name = $item->get_name();

				foreach ($fields['rey_refund__order_items'] as $value) {
					if( $name == $value ){
						$products[] = $name . ' ( ' . ($item->get_total() + $item->get_subtotal_tax()) . get_woocommerce_currency_symbol() . ' )';
					}
				}
			}

			if( !empty($products) ){
				$message[] = '<strong>' . esc_html_x('Products:', 'Refunds form mail title', 'rey-core') . '</strong><br>' . implode('<br>', $products);
			}

			$message[] = '<strong>' . esc_html_x('Reason:', 'Refunds form mail title', 'rey-core') . '</strong><br>' . $fields['rey_refund__observation'];

			if( ($msg = implode('<br>', $message)) && $this->send_email_woocommerce_style( $msg ) ){
				wp_send_json_success( $this->make_notice($this->settings['success_msg'], 'rey-icon-check') );
			}
			else {
				wp_send_json_success([
					'errors' => [
						$this->make_notice($this->settings['error_not_sent'])
					]
				]);
			}
		}

		function send_email_woocommerce_style($message) {

			$user = wp_get_current_user();

			$name = $user->user_login;
			if( $user->first_name && $user->last_name ) {
				$name = " {$user->first_name} {$user->last_name}";
			}
			elseif( $user->first_name ) {
				$name = " {$user->first_name}";
			}

			// @email - Email address of the reciever
			$email = $this->settings['email'];

			// @subject - Subject of the email
			$subject = sprintf($this->settings['subject'], $name);

			// @heading - Heading to place inside of the woocommerce template
			$heading = $this->settings['heading'];

			// Get woocommerce mailer from instance
			$mailer = WC()->mailer();

			// Wrap message using woocommerce html email template
			$wrapped_message = $mailer->wrap_message($heading, $message);

			// Create new WC_Email instance
			$wc_email = new WC_Email;

			// Style the wrapped message with woocommerce inline styles
			$html_message = $wc_email->style_inline($wrapped_message);

			$headers = [
				"Content-Type: text/html; charset=UTF-8",
				"Reply-to: {$name} <{$user->user_email}>"
			];

			// Send the email using wordpress mail function
			return wp_mail( $email, $subject, $html_message, $headers );
		}

		function make_notice($message, $icon = 'rey-icon-close'){
			return '<p class="__msg">' . reycore__get_svg_icon(['id' => $icon]) . '<span>' . $message . '</span></p>';
		}

		public function is_enabled(){
			return get_theme_mod('refunds__enable', false);
		}

		public function set_query_vars( $vars ) {

			$vars[] = $this->settings['endpoint'];

			return $vars;
		}

		public function set_menu_item( $items ) {

			$afterIndex = 4;

			$rr = [
				$this->settings['endpoint'] => get_theme_mod('refunds__menu_text', esc_html__('Request Return', 'rey-core'))
			];

			return array_merge( array_slice( $items, 0, $afterIndex + 1 ), $rr, array_slice( $items, $afterIndex + 1 ));
		}

		function load_scripts(){

			global $wp_query;

			if( ! isset($wp_query->query[$this->settings['endpoint']]) ){
				return;
			}

			reyCoreAssets()->add_scripts('reycore-wc-refunds');

		}
	}

	new ReyCore_WooCommerce_Refunds;

endif;
