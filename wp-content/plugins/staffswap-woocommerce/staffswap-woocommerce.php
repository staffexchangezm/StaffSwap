<?php
/**
 * Plugin Name: StaffSwap WooCommerce Bridge
 * Description: Optional WooCommerce integration for StaffSwap Plus upgrades, hosted Lenco card/mobile-money payments, and premium visibility.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_has_active_membership( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	if ( ! $user_id || '1' !== get_user_meta( $user_id, 'staffswap_plus_active', true ) ) { return false; }
	$expires_at = get_user_meta( $user_id, 'staffswap_vip_expires_at', true );
	if ( $expires_at && strtotime( $expires_at . ' UTC' ) < time() ) {
		update_user_meta( $user_id, 'staffswap_plus_active', '' );
		return false;
	}
	return true;
}
function staffswap_membership_required_notice( $action = 'use this feature' ) { return '<div class="panel membership-required"><p class="eyebrow">STAFFSWAP PLUS</p><h2>Membership required</h2><p>You need an active membership to ' . esc_html( $action ) . '.</p><a class="button button--primary" href="' . esc_url( home_url( '/pricing/' ) ) . '">View membership plans</a></div>'; }
function staffswap_wc_plans() {
	$plans = array( 'month' => array( 'title' => 'StaffSwap VIP Gold - 1 Month', 'price' => '99', 'sku' => 'STAFFSWAP-VIP-1M', 'duration' => '1 month' ), 'quarter' => array( 'title' => 'StaffSwap VIP Gold - 3 Months', 'price' => '249', 'sku' => 'STAFFSWAP-VIP-3M', 'duration' => '3 months' ), 'lifetime' => array( 'title' => 'StaffSwap VIP Gold - Lifetime', 'price' => '799', 'sku' => 'STAFFSWAP-VIP-LIFE', 'duration' => 'lifetime' ) );
	$overrides = get_option( 'staffswap_plan_settings', array() );
	foreach ( $plans as $plan => $data ) {
		if ( ! empty( $overrides[ $plan ]['title'] ) ) { $plans[ $plan ]['title'] = $overrides[ $plan ]['title']; }
		if ( isset( $overrides[ $plan ]['price'] ) && '' !== $overrides[ $plan ]['price'] ) { $plans[ $plan ]['price'] = $overrides[ $plan ]['price']; }
	}
	return $plans;
}
// Pushes edited plan titles/prices from Theme Options onto the already-created WooCommerce products.
function staffswap_wc_sync_plan_products() {
	if ( ! class_exists( 'WooCommerce' ) ) { return; }
	foreach ( staffswap_wc_plans() as $plan => $data ) {
		$product_id = (int) get_option( 'staffswap_vip_product_' . $plan, 0 );
		if ( $product_id && 'publish' === get_post_status( $product_id ) ) {
			wp_update_post( array( 'ID' => $product_id, 'post_title' => $data['title'] ) );
			update_post_meta( $product_id, '_regular_price', $data['price'] );
			update_post_meta( $product_id, '_price', $data['price'] );
		}
	}
}
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
	if ( ! is_user_logged_in() ) {
		return '<a class="button button--outline" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Sign in to choose this plan</a>';
	}
	if ( staffswap_has_active_membership() && $plan === get_user_meta( get_current_user_id(), 'staffswap_vip_plan', true ) ) {
		$expires_at = get_user_meta( get_current_user_id(), 'staffswap_vip_expires_at', true );
		return '<span class="button button--outline" aria-disabled="true">Current plan</span>' . ( $expires_at ? '<p class="muted" style="margin-top:8px;font-size:12px;">Renews ' . esc_html( date_i18n( 'j M Y', strtotime( $expires_at ) ) ) . '</p>' : '<p class="muted" style="margin-top:8px;font-size:12px;">Lifetime access</p>' );
	}
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
function staffswap_wc_payment_complete( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) { return; }
	$user_id = (int) $order->get_user_id();
	$plan_durations = array( 'month' => '+1 month', 'quarter' => '+3 months', 'lifetime' => '' );
	if ( $user_id && $order->get_items() ) { foreach ( $order->get_items() as $item ) { foreach ( array_keys( staffswap_wc_plans() ) as $plan ) { if ( (int) $item->get_product_id() === (int) get_option( 'staffswap_vip_product_' . $plan ) ) { update_user_meta( $user_id, 'staffswap_plus_active', '1' ); update_user_meta( $user_id, 'staffswap_vip_plan', $plan ); $duration = $plan_durations[ $plan ] ?? ''; update_user_meta( $user_id, 'staffswap_vip_expires_at', $duration ? gmdate( 'Y-m-d H:i:s', strtotime( $duration, current_time( 'timestamp', true ) ) ) : '' ); delete_user_meta( $user_id, 'staffswap_vip_renewal_notified' ); } } } }
	if ( $user_id && $order->get_items() ) { foreach ( $order->get_items() as $item ) { foreach ( array_keys( staffswap_wc_plans() ) as $plan ) { if ( (int) $item->get_product_id() === (int) get_option( 'staffswap_vip_product_' . $plan ) ) { update_user_meta( $user_id, 'staffswap_plus_active', '1' ); update_user_meta( $user_id, 'staffswap_vip_plan', $plan ); delete_transient( 'staffswap_plus_fallback_' . $user_id ); } } } }
}
add_action( 'woocommerce_payment_complete', 'staffswap_wc_payment_complete' );
// Some gateways (bank transfer, manual admin completion, COD) never call $order->payment_complete(),
// so also activate the plan on the order status transitions that indicate the order was paid.
add_action( 'woocommerce_order_status_processing', 'staffswap_wc_payment_complete' );
add_action( 'woocommerce_order_status_completed', 'staffswap_wc_payment_complete' );

function staffswap_wc_membership_cron_schedule() {
	if ( ! wp_next_scheduled( 'staffswap_membership_check' ) ) { wp_schedule_event( time(), 'daily', 'staffswap_membership_check' ); }
}
add_action( 'wp', 'staffswap_wc_membership_cron_schedule' );
function staffswap_wc_membership_cron_clear() { wp_clear_scheduled_hook( 'staffswap_membership_check' ); }
register_deactivation_hook( __FILE__, 'staffswap_wc_membership_cron_clear' );

// Emails a renewal reminder a few days before expiry, then deactivates and notifies once a plan actually lapses.
function staffswap_wc_membership_daily_check() {
	if ( ! function_exists( 'staffswap_notify_user' ) ) { return; }
	global $wpdb;
	$soon = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'staffswap_vip_expires_at' AND meta_value <> '' AND meta_value BETWEEN %s AND %s", gmdate( 'Y-m-d H:i:s' ), gmdate( 'Y-m-d H:i:s', strtotime( '+3 days' ) ) ) );
	foreach ( $soon as $user_id ) {
		if ( '1' !== get_user_meta( $user_id, 'staffswap_plus_active', true ) || get_user_meta( $user_id, 'staffswap_vip_renewal_notified', true ) ) { continue; }
		staffswap_notify_user( $user_id, 'Your VIP Gold membership is expiring soon', 'Your StaffSwap VIP Gold membership expires on ' . get_user_meta( $user_id, 'staffswap_vip_expires_at', true ) . ' UTC. Renew now to keep uninterrupted access to messaging and formal swap offers.' . "\n\n" . 'Renew here: ' . home_url( '/pricing/' ) );
		update_user_meta( $user_id, 'staffswap_vip_renewal_notified', '1' );
	}
	$active = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'staffswap_plus_active' AND meta_value = '1'" );
	foreach ( $active as $user_id ) {
		$expires_at = get_user_meta( $user_id, 'staffswap_vip_expires_at', true );
		if ( $expires_at && strtotime( $expires_at . ' UTC' ) < time() ) {
			update_user_meta( $user_id, 'staffswap_plus_active', '' );
			delete_user_meta( $user_id, 'staffswap_vip_renewal_notified' );
			staffswap_notify_user( $user_id, 'Your VIP Gold membership has expired', 'Your StaffSwap VIP Gold membership has expired. Renew to regain access to messaging and formal swap offers.' . "\n\n" . 'Renew here: ' . home_url( '/pricing/' ) );
		}
	}
}
add_action( 'staffswap_membership_check', 'staffswap_wc_membership_daily_check' );
function staffswap_wc_admin_notice() { if ( current_user_can( 'manage_options' ) && ! class_exists( 'WooCommerce' ) ) { echo '<div class="notice notice-info"><p><strong>StaffSwap WooCommerce Bridge:</strong> Install WooCommerce to enable premium upgrade checkout. The core marketplace remains fully available without it.</p></div>'; } }
add_action( 'admin_notices', 'staffswap_wc_admin_notice' );

function staffswap_lenco_gateway_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	class StaffSwap_Lenco_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			$this->id = 'staffswap_lenco'; $this->method_title = 'Lenco Payments'; $this->method_description = 'Card and Zambian mobile-money collections through Lenco.'; $this->has_fields = false;
			$this->init_form_fields(); $this->init_settings(); $this->title = $this->get_option( 'title', 'Mobile Money' ); $this->description = $this->get_option( 'description', 'Pay securely using MTN Mobile Money, Airtel Money, or Zamtel Kwacha.' ); $this->api_key = trim( (string) $this->get_option( 'api_key' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		public function init_form_fields() { $this->form_fields = array( 'enabled' => array( 'title' => 'Enable', 'type' => 'checkbox', 'label' => 'Enable Lenco Payments', 'default' => 'no' ), 'title' => array( 'title' => 'Title', 'type' => 'text', 'default' => 'Card or Mobile Money' ), 'description' => array( 'title' => 'Description', 'type' => 'textarea', 'default' => 'Pay securely by card or Zambian mobile money.' ), 'public_key' => array( 'title' => 'Lenco public key', 'type' => 'text', 'description' => 'Used by the hosted payment widget. This is safe to expose in checkout.' ), 'api_key' => array( 'title' => 'Lenco API token', 'type' => 'password', 'description' => 'Keep this secret. Server requests use the Authorization: Bearer header.' ) ); }
		public function payment_fields() { if ( $this->description ) { echo wpautop( wp_kses_post( $this->description ) ); } }
		public function validate_fields() { return true; }
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$public_key = trim( (string) $this->get_option( 'public_key' ) );
			if ( ! $this->api_key || ! $public_key ) { wc_add_notice( 'Lenco Payments is not configured. Please contact support.', 'error' ); return array( 'result' => 'failure' ); }
			$reference = 'STAFFSWAP-' . $order->get_order_number() . '-' . wp_generate_password( 8, false, false );
			$order->update_meta_data( '_staffswap_lenco_reference', $reference ); $order->update_meta_data( '_staffswap_lenco_status', 'pending' ); $order->save(); $order->update_status( 'on-hold', 'Awaiting Lenco card or mobile-money authorization.' ); wc_reduce_stock_levels( $order_id ); WC()->cart->empty_cart();
			return array( 'result' => 'success', 'redirect' => add_query_arg( array( 'staffswap_lenco_pay' => $order_id, 'key' => $order->get_order_key() ), home_url( '/' ) ) );
		}
	}
}
add_action( 'plugins_loaded', 'staffswap_lenco_gateway_init', 20 );
function staffswap_lenco_add_gateway( $gateways ) { $gateways[] = 'StaffSwap_Lenco_Gateway'; return $gateways; }
add_filter( 'woocommerce_payment_gateways', 'staffswap_lenco_add_gateway' );

function staffswap_lenco_settings() {
	$settings = get_option( 'woocommerce_staffswap_lenco_settings', array() );
	return is_array( $settings ) ? $settings : array();
}

function staffswap_lenco_fetch_status( $reference, $api_key ) {
	if ( ! $reference || ! $api_key ) { return false; }
	$response = wp_remote_get( 'https://api.lenco.co/access/v2/collections/status/' . rawurlencode( $reference ), array( 'timeout' => 20, 'headers' => array( 'accept' => 'application/json', 'Authorization' => 'Bearer ' . $api_key ) ) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { return false; }
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return is_array( $body['data'] ?? null ) ? $body['data'] : false;
}

function staffswap_lenco_update_order_from_status( $order, $data, $source = 'status check' ) {
	if ( ! $order || ! is_array( $data ) ) { return false; }
	$status = strtolower( sanitize_text_field( $data['status'] ?? '' ) );
	$reference = sanitize_text_field( $data['reference'] ?? $order->get_meta( '_staffswap_lenco_reference' ) );
	if ( $reference && $reference !== $order->get_meta( '_staffswap_lenco_reference' ) ) { return false; }
	if ( $status ) { $order->update_meta_data( '_staffswap_lenco_status', $status ); $order->save(); }
	if ( 'successful' === $status && ! $order->is_paid() ) { $order->payment_complete( $reference ); $order->add_order_note( 'Lenco payment confirmed by ' . $source . '.' ); return true; }
	if ( 'failed' === $status && ! $order->is_paid() ) { $order->update_status( 'failed', 'Lenco payment was not completed.' ); return true; }
	return false;
}

function staffswap_lenco_payment_page() {
	$order_id = absint( $_GET['staffswap_lenco_pay'] ?? 0 );
	$order_key = wc_clean( wp_unslash( $_GET['key'] ?? '' ) );
	if ( ! $order_id ) { return; }
	$order = wc_get_order( $order_id );
	$settings = staffswap_lenco_settings();
	$public_key = trim( (string) ( $settings['public_key'] ?? '' ) );
	if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) || 'staffswap_lenco' !== $order->get_payment_method() || ! $public_key ) { status_header( 404 ); exit; }
	$reference = $order->get_meta( '_staffswap_lenco_reference' );
	$return_url = $order->get_checkout_order_received_url();
	$customer = array_filter( array( 'firstName' => $order->get_billing_first_name(), 'lastName' => $order->get_billing_last_name(), 'phone' => $order->get_billing_phone() ) );
	$verify_url = add_query_arg( array( 'wc-api' => 'staffswap_lenco_verify', 'order_id' => $order_id, 'key' => $order_key ), home_url( '/' ) );
	$config = array( 'key' => $public_key, 'reference' => $reference, 'email' => $order->get_billing_email(), 'amount' => (float) $order->get_total(), 'currency' => $order->get_currency(), 'channels' => array( 'card', 'mobile-money' ), 'customer' => $customer );
	$html = '<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Complete payment</title></head><body><p>Opening secure Lenco payment...</p><script src="https://pay.lenco.co/js/v1/inline.js"></script><script>window.addEventListener("load",function(){LencoPay.getPaid(Object.assign(' . wp_json_encode( $config ) . ',{onSuccess:function(response){window.location.href=' . wp_json_encode( $verify_url ) . ' + "&reference=" + encodeURIComponent(response.reference);},onClose:function(){window.location.href=' . wp_json_encode( $return_url ) . ';},onConfirmationPending:function(){window.location.href=' . wp_json_encode( $return_url ) . ';}}));});</script></body></html>';
	wp_die( $html, 'StaffSwap Lenco Payment' );
}
add_action( 'template_redirect', 'staffswap_lenco_payment_page' );

function staffswap_lenco_verify() {
	$order = wc_get_order( absint( $_REQUEST['order_id'] ?? 0 ) );
	$order_key = wc_clean( wp_unslash( $_REQUEST['key'] ?? '' ) );
	$reference = sanitize_text_field( wp_unslash( $_REQUEST['reference'] ?? '' ) );
	if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) || 'staffswap_lenco' !== $order->get_payment_method() || ! $reference || $reference !== $order->get_meta( '_staffswap_lenco_reference' ) ) { wp_send_json_error( array( 'message' => 'Invalid payment verification request.' ), 400 ); }
	$data = staffswap_lenco_fetch_status( $reference, trim( (string) ( staffswap_lenco_settings()['api_key'] ?? '' ) ) );
	if ( ! $data ) { wp_send_json_error( array( 'message' => 'Payment status could not be verified yet.' ), 502 ); }
	staffswap_lenco_update_order_from_status( $order, $data, 'verification' );
	wp_send_json_success( array( 'status' => sanitize_key( $data['status'] ?? '' ), 'redirect' => $order->get_checkout_order_received_url() ) );
}
add_action( 'woocommerce_api_staffswap_lenco_verify', 'staffswap_lenco_verify' );

function staffswap_lenco_callback() {
	$raw_body = file_get_contents( 'php://input' );
	$api_key = trim( (string) ( get_option( 'woocommerce_staffswap_lenco_settings', array() )['api_key'] ?? '' ) );
	$signature = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_LENCO_SIGNATURE'] ?? '' ) );
	$expected = $api_key ? hash_hmac( 'sha512', $raw_body, hash( 'sha256', $api_key ) ) : '';
	if ( ! $api_key || ! $signature || ! hash_equals( $expected, $signature ) ) { status_header( 401 ); exit; }
	$payload = json_decode( $raw_body, true );
	$reference = sanitize_text_field( $payload['data']['reference'] ?? '' );
	$orders = $reference ? wc_get_orders( array( 'limit' => 1, 'meta_key' => '_staffswap_lenco_reference', 'meta_value' => $reference ) ) : array();
	$order = $orders ? $orders[0] : false;
	if ( ! $order ) { status_header( 400 ); exit; }
	staffswap_lenco_update_order_from_status( $order, $payload['data'] ?? array(), 'webhook' );
	status_header( 200 ); echo 'OK'; exit;
}
add_action( 'woocommerce_api_staffswap_lenco_callback', 'staffswap_lenco_callback' );

function staffswap_lenco_check_order_status( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->is_paid() || 'staffswap_lenco' !== $order->get_payment_method() ) { return; }
	$reference = $order->get_meta( '_staffswap_lenco_reference' );
	$data = staffswap_lenco_fetch_status( $reference, trim( (string) ( staffswap_lenco_settings()['api_key'] ?? '' ) ) );
	if ( $data ) { staffswap_lenco_update_order_from_status( $order, $data ); }
}
add_action( 'woocommerce_thankyou_staffswap_lenco', 'staffswap_lenco_check_order_status' );

function staffswap_lenco_cron_schedules( $schedules ) {
	$schedules['staffswap_every_thirty_minutes'] = array( 'interval' => 30 * MINUTE_IN_SECONDS, 'display' => 'Every 30 minutes' );
	return $schedules;
}
add_filter( 'cron_schedules', 'staffswap_lenco_cron_schedules' );
function staffswap_lenco_schedule_status_checks() {
	if ( ! wp_next_scheduled( 'staffswap_lenco_status_check' ) ) { wp_schedule_event( time() + 30 * MINUTE_IN_SECONDS, 'staffswap_every_thirty_minutes', 'staffswap_lenco_status_check' ); }
}
add_action( 'wp', 'staffswap_lenco_schedule_status_checks' );
function staffswap_lenco_status_check_cron() {
	$orders = wc_get_orders( array( 'limit' => 50, 'status' => array( 'pending', 'on-hold' ), 'payment_method' => 'staffswap_lenco', 'meta_key' => '_staffswap_lenco_reference', 'meta_compare' => 'EXISTS' ) );
	foreach ( $orders as $order ) { staffswap_lenco_check_order_status( $order->get_id() ); }
}
add_action( 'staffswap_lenco_status_check', 'staffswap_lenco_status_check_cron' );
