<?php
/**
 * Plugin Name: StaffSwap WooCommerce Bridge
 * Description: Optional WooCommerce integration for StaffSwap Plus upgrades and premium visibility.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_has_active_membership( $user_id = 0 ) { $user_id = $user_id ? absint( $user_id ) : get_current_user_id(); return (bool) ( $user_id && '1' === get_user_meta( $user_id, 'staffswap_plus_active', true ) ); }
function staffswap_membership_required_notice( $action = 'use this feature' ) { return '<div class="panel membership-required"><p class="eyebrow">STAFFSWAP PLUS</p><h2>Membership required</h2><p>You need an active membership to ' . esc_html( $action ) . '.</p><a class="button button--primary" href="' . esc_url( home_url( '/pricing/' ) ) . '">View membership plans</a></div>'; }
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
function staffswap_wc_plan_price( $plan = 'month' ) {
	$plans = staffswap_wc_plans();
	if ( empty( $plans[ $plan ] ) ) { return ''; }
	$product_id = staffswap_wc_product( $plan );
	$product = $product_id && function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : false;
	if ( $product && '' !== $product->get_price() ) { return wc_price( $product->get_price() ); }
	return 'ZMW ' . esc_html( $plans[ $plan ]['price'] );
}
function staffswap_wc_plan_price_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'plan' => 'month' ), $atts, 'staffswap_plan_price' );
	return staffswap_wc_plan_price( sanitize_key( $atts['plan'] ) );
}
add_shortcode( 'staffswap_plan_price', 'staffswap_wc_plan_price_shortcode' );
function staffswap_wc_sync_pricing_content( $content ) {
	if ( is_admin() || ! is_page( 'pricing' ) || ! class_exists( 'WooCommerce' ) ) { return $content; }
	$plans = array_keys( staffswap_wc_plans() );
	$price_index = 0;
	$content = preg_replace_callback( '/(?:ZK|ZMW|K)\s?[0-9][0-9,.]*/i', function ( $matches ) use ( $plans, &$price_index ) {
		if ( ! isset( $plans[ $price_index ] ) ) { return $matches[0]; }
		$price = staffswap_wc_plan_price( $plans[ $price_index ] );
		$price_index++;
		return wp_strip_all_tags( html_entity_decode( $price ) );
	}, $content );
	$button_index = 0;
	$content = preg_replace_callback( '/<a\b([^>]*)>(\s*Choose VIP Gold\s*)<\/a>/i', function ( $matches ) use ( $plans, &$button_index ) {
		if ( ! isset( $plans[ $button_index ] ) ) { return $matches[0]; }
		$product_id = staffswap_wc_product( $plans[ $button_index ] );
		$button_index++;
		if ( ! $product_id || ! function_exists( 'wc_get_checkout_url' ) ) { return $matches[0]; }
		$url = add_query_arg( array( 'add-to-cart' => $product_id ), wc_get_checkout_url() );
		$attributes = preg_replace( '/\s+href=(["\']).*?\1/i', '', $matches[1] );
		return '<a' . $attributes . ' href="' . esc_url( $url ) . '">' . $matches[2] . '</a>';
	}, $content );
	return $content;
}
add_filter( 'the_content', 'staffswap_wc_sync_pricing_content', 99 );
function staffswap_wc_membership_plan_for_order( $order ) {
	if ( ! $order || ! $order->get_items() ) { return ''; }
	foreach ( $order->get_items() as $item ) {
		foreach ( array_keys( staffswap_wc_plans() ) as $plan ) {
			if ( (int) $item->get_product_id() === (int) get_option( 'staffswap_vip_product_' . $plan ) ) {
				return $plan;
			}
		}
	}
	return '';
}
function staffswap_wc_membership_user_for_order( $order ) {
	if ( ! $order ) { return 0; }
	$user_id = (int) $order->get_user_id();
	if ( $user_id ) { return $user_id; }
	$email = sanitize_email( $order->get_billing_email() );
	if ( ! $email ) { return 0; }
	$user = get_user_by( 'email', $email );
	return ( $user && ! empty( $user->ID ) ) ? (int) $user->ID : 0;
}
function staffswap_wc_activate_membership_from_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->is_paid() ) { return; }
	$plan = staffswap_wc_membership_plan_for_order( $order );
	if ( ! $plan ) { return; }
	$user_id = staffswap_wc_membership_user_for_order( $order );
	if ( ! $user_id ) { return; }
	$already_activated = '1' === $order->get_meta( '_staffswap_membership_activated', true );
	$activated_user_id = (int) $order->get_meta( '_staffswap_membership_activated_user', true );
	$activated_plan = sanitize_key( $order->get_meta( '_staffswap_membership_activated_plan', true ) );
	if ( $activated_user_id === $user_id && $activated_plan === $plan ) { return; }
	if ( $already_activated ) { return; }
	update_user_meta( $user_id, 'staffswap_plus_active', '1' );
	update_user_meta( $user_id, 'staffswap_vip_plan', $plan );
	$order->update_meta_data( '_staffswap_membership_activated', '1' );
	$order->update_meta_data( '_staffswap_membership_activated_user', $user_id );
	$order->update_meta_data( '_staffswap_membership_activated_plan', $plan );
	$order->save();
}
add_action( 'woocommerce_payment_complete', 'staffswap_wc_activate_membership_from_order' );
add_action( 'woocommerce_order_status_processing', 'staffswap_wc_activate_membership_from_order' );
add_action( 'woocommerce_order_status_completed', 'staffswap_wc_activate_membership_from_order' );
function staffswap_wc_admin_notice() { if ( current_user_can( 'manage_options' ) && ! class_exists( 'WooCommerce' ) ) { echo '<div class="notice notice-info"><p><strong>StaffSwap WooCommerce Bridge:</strong> Install WooCommerce to enable premium upgrade checkout. The core marketplace remains fully available without it.</p></div>'; } }
add_action( 'admin_notices', 'staffswap_wc_admin_notice' );

function staffswap_lipila_gateway_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	class StaffSwap_Lipila_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			$this->id = 'staffswap_lipila'; $this->method_title = 'Lipila Mobile Money'; $this->method_description = 'MTN Mobile Money, Airtel Money, and Zamtel Kwacha collections via Lipila.'; $this->has_fields = true;
			$this->init_form_fields(); $this->init_settings(); $this->title = $this->get_option( 'title', 'Mobile Money' ); $this->description = $this->get_option( 'description', 'Pay securely using MTN Mobile Money, Airtel Money, or Zamtel Kwacha.' ); $this->api_key = trim( (string) $this->get_option( 'api_key' ) );
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
			$response = wp_remote_post( 'https://api.lipila.dev/api/v1/collections/mobile-money', array( 'timeout' => 45, 'headers' => array( 'accept' => 'application/json', 'content-type' => 'application/json', 'x-api-key' => $this->api_key ), 'body' => wp_json_encode( array( 'referenceId' => $reference, 'amount' => (float) $order->get_total(), 'narration' => 'StaffSwap VIP Gold order #' . $order->get_order_number(), 'accountNumber' => $phone, 'currency' => $order->get_currency(), 'email' => $order->get_billing_email(), 'referenceData' => (string) $order_id, 'callbackUrl' => $callback ) ) ) );
			if ( is_wp_error( $response ) ) { $order->add_order_note( 'Lipila request failed: ' . $response->get_error_message() ); wc_add_notice( 'We could not reach the Mobile Money service. Please try again.', 'error' ); return array( 'result' => 'failure' ); }
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 > $response_code || 299 < $response_code ) { $body_text = wp_remote_retrieve_body( $response ); $order->add_order_note( 'Lipila request rejected (HTTP ' . absint( $response_code ) . '): ' . sanitize_text_field( wp_trim_words( $body_text, 30 ) ) ); wc_add_notice( 401 === $response_code ? 'Mobile Money authentication failed. Please contact support.' : 'We could not start the Mobile Money request. Please try again.', 'error' ); return array( 'result' => 'failure' ); }
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

function staffswap_lipila_check_order_status( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->is_paid() || 'staffswap_lipila' !== $order->get_payment_method() ) { return; }
	$reference = $order->get_meta( '_staffswap_lipila_reference' );
	$settings = get_option( 'woocommerce_staffswap_lipila_settings', array() );
	$api_key = $settings['api_key'] ?? '';
	if ( ! $reference || ! $api_key ) { return; }
	$response = wp_remote_get( add_query_arg( 'referenceId', rawurlencode( $reference ), 'https://api.lipila.dev/api/v1/collections/check-status' ), array( 'timeout' => 20, 'headers' => array( 'accept' => 'application/json', 'x-api-key' => $api_key ) ) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { return; }
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $body['status'] ) && 'successful' === strtolower( sanitize_text_field( $body['status'] ) ) ) { $order->payment_complete( $reference ); $order->add_order_note( 'Lipila Mobile Money payment confirmed by status check.' ); }
}
add_action( 'woocommerce_thankyou_staffswap_lipila', 'staffswap_lipila_check_order_status' );
