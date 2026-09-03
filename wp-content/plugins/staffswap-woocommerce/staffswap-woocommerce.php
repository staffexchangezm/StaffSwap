<?php
/**
 * Plugin Name: StaffSwap WooCommerce Bridge
 * Description: Optional WooCommerce integration for StaffSwap Plus upgrades and premium visibility.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_wc_plans() { return array( 'month' => array( 'title' => 'StaffSwap VIP Gold - 1 Month', 'price' => '99', 'sku' => 'STAFFSWAP-VIP-1M', 'duration' => '1 month' ), 'quarter' => array( 'title' => 'StaffSwap VIP Gold - 3 Months', 'price' => '249', 'sku' => 'STAFFSWAP-VIP-3M', 'duration' => '3 months' ), 'lifetime' => array( 'title' => 'StaffSwap VIP Gold - Lifetime', 'price' => '799', 'sku' => 'STAFFSWAP-VIP-LIFE', 'duration' => 'lifetime' ) ); }
function staffswap_wc_product( $plan = 'month' ) {
	if ( ! class_exists( 'WooCommerce' ) ) { return 0; }
	$plans = staffswap_wc_plans();
	if ( empty( $plans[ $plan ] ) ) { return 0; }
	$product_id = (int) get_option( 'staffswap_vip_product_' . $plan, 0 );
	if ( $product_id && 'publish' === get_post_status( $product_id ) ) { return $product_id; }
	$product = $plans[ $plan ];
	$product_id = wp_insert_post( array( 'post_title' => $product['title'], 'post_content' => 'VIP Gold membership with priority visibility, direct contact access, and official transfer letters for ' . $product['duration'] . '.', 'post_status' => 'publish', 'post_type' => 'product' ) );
	if ( is_wp_error( $product_id ) ) { return 0; }
	update_post_meta( $product_id, '_regular_price', $product['price'] ); update_post_meta( $product_id, '_price', $product['price'] ); update_post_meta( $product_id, '_virtual', 'yes' ); update_post_meta( $product_id, '_sold_individually', 'yes' ); update_post_meta( $product_id, '_sku', $product['sku'] ); update_option( 'staffswap_vip_product_' . $plan, $product_id );
	return $product_id;
}
function staffswap_wc_activate() { if ( class_exists( 'WooCommerce' ) ) { foreach ( array_keys( staffswap_wc_plans() ) as $plan ) { staffswap_wc_product( $plan ); } } }
register_activation_hook( __FILE__, 'staffswap_wc_activate' );
function staffswap_wc_upgrade_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'plan' => 'month' ), $atts, 'staffswap_upgrade' );
	if ( ! class_exists( 'WooCommerce' ) ) { return '<div class="panel"><h2>StaffSwap Plus</h2><p>Install WooCommerce to enable premium upgrades.</p></div>'; }
	$plans = staffswap_wc_plans(); $plan = sanitize_key( $atts['plan'] ); $product_id = staffswap_wc_product( $plan );
	if ( ! $product_id ) { return '<div class="panel"><p>Premium upgrades are temporarily unavailable.</p></div>'; }
	$url = function_exists( 'wc_get_checkout_url' ) ? add_query_arg( array( 'add-to-cart' => $product_id ), wc_get_checkout_url() ) : get_permalink( $product_id );
	return '<a class="button button--primary" href="' . esc_url( $url ) . '">Choose VIP Gold</a>';
}
add_shortcode( 'staffswap_upgrade', 'staffswap_wc_upgrade_shortcode' );
function staffswap_wc_payment_complete( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) { return; }
	$user_id = (int) $order->get_user_id();
	if ( $user_id && $order->get_items() ) { foreach ( $order->get_items() as $item ) { foreach ( array_keys( staffswap_wc_plans() ) as $plan ) { if ( (int) $item->get_product_id() === (int) get_option( 'staffswap_vip_product_' . $plan ) ) { update_user_meta( $user_id, 'staffswap_plus_active', '1' ); update_user_meta( $user_id, 'staffswap_vip_plan', $plan ); } } } }
}
add_action( 'woocommerce_payment_complete', 'staffswap_wc_payment_complete' );
function staffswap_wc_admin_notice() { if ( current_user_can( 'manage_options' ) && ! class_exists( 'WooCommerce' ) ) { echo '<div class="notice notice-info"><p><strong>StaffSwap WooCommerce Bridge:</strong> Install WooCommerce to enable premium upgrade checkout. The core marketplace remains fully available without it.</p></div>'; } }
add_action( 'admin_notices', 'staffswap_wc_admin_notice' );

function staffswap_lipila_gateway_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	class StaffSwap_Lipila_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			$this->id = 'staffswap_lipila'; $this->method_title = 'Lipila Mobile Money'; $this->method_description = 'MTN Mobile Money, Airtel Money, and Zamtel Kwacha collections via Lipila.'; $this->has_fields = true;
			$this->init_form_fields(); $this->init_settings(); $this->title = $this->get_option( 'title', 'Mobile Money' ); $this->description = $this->get_option( 'description', 'Pay securely using MTN Mobile Money, Airtel Money, or Zamtel Kwacha.' ); $this->api_key = $this->get_option( 'api_key' );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		public function init_form_fields() { $this->form_fields = array( 'enabled' => array( 'title' => 'Enable', 'type' => 'checkbox', 'label' => 'Enable Lipila Mobile Money', 'default' => 'no' ), 'title' => array( 'title' => 'Title', 'type' => 'text', 'default' => 'Mobile Money' ), 'description' => array( 'title' => 'Description', 'type' => 'textarea', 'default' => 'Pay securely using MTN Mobile Money, Airtel Money, or Zamtel Kwacha.' ), 'api_key' => array( 'title' => 'Lipila Secret Key', 'type' => 'password', 'description' => 'Get this from your Lipila Blaze dashboard. It is sent as the x-api-key header.' ) ); }
		public function payment_fields() { if ( $this->description ) { echo wpautop( wp_kses_post( $this->description ) ); } ?><p class="form-row form-row-wide"><label for="staffswap_lipila_phone">Mobile number <span class="required">*</span></label><input id="staffswap_lipila_phone" name="staffswap_lipila_phone" type="tel" placeholder="26097XXXXXXX" required></p><?php }
		public function validate_fields() { $phone = preg_replace( '/\D+/', '', wc_clean( wp_unslash( $_POST['staffswap_lipila_phone'] ?? '' ) ) ); if ( ! preg_match( '/^260[0-9]{9}$/', $phone ) ) { wc_add_notice( 'Enter a valid Zambian mobile number in the format 26097XXXXXXX.', 'error' ); return false; } return true; }
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $this->api_key ) { wc_add_notice( 'Mobile Money is not configured. Please contact support.', 'error' ); return array( 'result' => 'failure' ); }
			$phone = preg_replace( '/\D+/', '', wc_clean( wp_unslash( $_POST['staffswap_lipila_phone'] ?? '' ) ) );
			$reference = 'STAFFSWAP-' . $order->get_order_number() . '-' . wp_generate_password( 8, false, false );
			$callback = add_query_arg( array( 'wc-api' => 'staffswap_lipila_callback', 'order_id' => $order_id, 'order_key' => $order->get_order_key() ), home_url( '/' ) );
			$response = wp_remote_post( 'https://api.lipila.dev/api/v1/collections/mobile-money', array( 'timeout' => 45, 'headers' => array( 'accept' => 'application/json', 'content-type' => 'application/json', 'x-api-key' => $this->api_key, 'callbackUrl' => $callback ), 'body' => wp_json_encode( array( 'referenceId' => $reference, 'amount' => (float) $order->get_total(), 'narration' => 'StaffSwap VIP Gold order #' . $order->get_order_number(), 'accountNumber' => $phone, 'currency' => $order->get_currency(), 'email' => $order->get_billing_email(), 'referenceData' => (string) $order_id ) ) ) );
			if ( is_wp_error( $response ) || 200 > wp_remote_retrieve_response_code( $response ) || 299 < wp_remote_retrieve_response_code( $response ) ) { wc_add_notice( 'We could not start the Mobile Money request. Please try again.', 'error' ); return array( 'result' => 'failure' ); }
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $body['referenceId'] ) ) { wc_add_notice( 'Lipila did not return a payment reference. Please try again.', 'error' ); return array( 'result' => 'failure' ); }
			$order->update_meta_data( '_staffswap_lipila_reference', sanitize_text_field( $body['referenceId'] ) ); $order->update_meta_data( '_staffswap_lipila_identifier', sanitize_text_field( $body['identifier'] ?? '' ) ); $order->save(); $order->update_status( 'on-hold', 'Awaiting Lipila Mobile Money authorization.' ); wc_reduce_stock_levels( $order_id ); WC()->cart->empty_cart();
			return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
		}
	}
}
add_action( 'plugins_loaded', 'staffswap_lipila_gateway_init', 20 );
function staffswap_lipila_add_gateway( $gateways ) { $gateways[] = 'StaffSwap_Lipila_Gateway'; return $gateways; }
add_filter( 'woocommerce_payment_gateways', 'staffswap_lipila_add_gateway' );

function staffswap_lipila_callback() {
	$payload = json_decode( file_get_contents( 'php://input' ), true );
	$reference = sanitize_text_field( $payload['referenceId'] ?? $_REQUEST['referenceId'] ?? '' );
	$status = strtolower( sanitize_text_field( $payload['status'] ?? $_REQUEST['status'] ?? '' ) );
	$order_id = absint( $_REQUEST['order_id'] ?? 0 );
	$order_key = wc_clean( wp_unslash( $_REQUEST['order_key'] ?? '' ) );
	$order = $order_id ? wc_get_order( $order_id ) : false;
	if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) || ! $reference || $reference !== $order->get_meta( '_staffswap_lipila_reference' ) ) { status_header( 400 ); exit; }
	if ( in_array( $status, array( 'successful', 'success', 'completed' ), true ) && ! $order->is_paid() ) { $order->payment_complete( $reference ); $order->add_order_note( 'Lipila Mobile Money payment confirmed.' ); }
	elseif ( in_array( $status, array( 'failed', 'cancelled', 'canceled' ), true ) ) { $order->update_status( 'failed', 'Lipila Mobile Money payment was not completed.' ); }
	status_header( 200 ); echo 'OK'; exit;
}
add_action( 'woocommerce_api_staffswap_lipila_callback', 'staffswap_lipila_callback' );
