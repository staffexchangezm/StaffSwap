<?php
/**
 * Plugin Name: StaffSwap Messaging
 * Description: Private member-to-member conversation requests for swap listings.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
// Branded HTML wrapper for member notification emails, using the Theme Options brand color.
function staffswap_email_html_template( $subject, $message ) {
	$site_name = get_bloginfo( 'name' );
	$settings = get_option( 'staffswap_settings', array() );
	$color = ! empty( $settings['primary_color'] ) ? sanitize_hex_color( $settings['primary_color'] ) : '#00bb7f';
	$body = wpautop( make_clickable( esc_html( $message ) ) );
	return '<!doctype html><html><body style="margin:0;padding:0;background:#f4f6f5;font-family:Arial,Helvetica,sans-serif;">'
		. '<div style="max-width:560px;margin:0 auto;padding:24px;">'
		. '<div style="background:#0d2240;border-radius:10px 10px 0 0;padding:24px 28px;">'
		. '<span style="color:' . esc_attr( $color ) . ';font-size:11px;font-weight:700;letter-spacing:.1em;">' . esc_html( strtoupper( $site_name ) ) . '</span>'
		. '<h1 style="color:#fff;font-size:20px;margin:8px 0 0;">' . esc_html( $subject ) . '</h1>'
		. '</div>'
		. '<div style="background:#fff;border:1px solid #e2e8f0;border-top:0;border-radius:0 0 10px 10px;padding:28px;color:#0f172a;font-size:14px;line-height:1.7;">'
		. $body
		. '</div>'
		. '<p style="color:#94a3b8;font-size:12px;text-align:center;margin-top:16px;">' . esc_html( $site_name ) . ' &middot; <a style="color:#94a3b8;" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( home_url( '/' ) ) . '</a></p>'
		. '</div></body></html>';
}
// Central email/SMS helper so members can be notified without leaving the site to check for updates.
function staffswap_notify_user( $user_id, $subject, $message, $notification_type = 'general' ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) { return; }
	$user = get_userdata( $user_id );
	if ( ! $user ) { return; }
	if ( is_email( $user->user_email ) && '1' !== get_user_meta( $user_id, 'staffswap_notifications_disabled', true ) ) {
		$site_name = get_bloginfo( 'name' );
		$set_html_type = function () { return 'text/html'; };
		add_filter( 'wp_mail_content_type', $set_html_type );
		$mail_sent = wp_mail( $user->user_email, '[' . $site_name . '] ' . $subject, staffswap_email_html_template( $subject, $message ) );
		remove_filter( 'wp_mail_content_type', $set_html_type );
		if ( ! $mail_sent ) { error_log( 'StaffSwap notification email failed for user ' . $user_id . '.' ); }
	}
	$sms_settings = get_option( 'staffswap_sms_settings', array() );
	$sms_settings = is_array( $sms_settings ) ? $sms_settings : array();
	$type_settings = isset( $sms_settings['notifications'] ) && is_array( $sms_settings['notifications'] ) ? $sms_settings['notifications'] : array();
	$type_enabled = ! array_key_exists( $notification_type, $type_settings ) || 'yes' === $type_settings[ $notification_type ];
	if ( function_exists( 'staffswap_send_sms' ) && '1' === get_user_meta( $user_id, 'staffswap_sms_notifications_enabled', true ) && $type_enabled ) {
		$phone = get_user_meta( $user_id, 'staffswap_phone', true );
		if ( $phone ) {
			$sms_message = function_exists( 'staffswap_sms_template' ) ? staffswap_sms_template( $notification_type, $subject, $message, $user_id ) : $subject . ': ' . $message;
			staffswap_send_sms( $phone, mb_substr( wp_strip_all_tags( $sms_message ), 0, 300 ) );
		}
	}
}
function staffswap_message_post_type() { register_post_type( 'staff_message', array( 'labels' => array( 'name' => 'Messages', 'singular_name' => 'Message' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=swap_listing', 'supports' => array( 'title', 'editor', 'author' ), 'capability_type' => 'post' ) ); }
add_action( 'init', 'staffswap_message_post_type' );
function staffswap_create_messages_page() { if ( ! get_page_by_path( 'messages' ) ) { wp_insert_post( array( 'post_title' => 'Messages', 'post_name' => 'messages', 'post_content' => '[staffswap_inbox]', 'post_status' => 'publish', 'post_type' => 'page' ) ); } }
register_activation_hook( __FILE__, function() { staffswap_message_post_type(); staffswap_create_messages_page(); flush_rewrite_rules(); } ); register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
function staffswap_contact_form_shortcode( $atts ) { $atts = shortcode_atts( array( 'listing' => get_the_ID() ), $atts, 'staffswap_contact' ); if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to contact this member.</p><a class="button button--primary" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Sign in</a></div>'; } $notice = ''; if ( isset( $_POST['staffswap_send_message'] ) && check_admin_referer( 'staffswap_send_message', 'staffswap_message_nonce' ) ) { $listing = get_post( (int) $atts['listing'] ); $message_text = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ); if ( $listing && 'swap_listing' === $listing->post_type && 'publish' === $listing->post_status && (int) $listing->post_author !== get_current_user_id() && $message_text ) { $message_id = wp_insert_post( array( 'post_type' => 'staff_message', 'post_title' => 'Message about: ' . $listing->post_title, 'post_content' => $message_text, 'post_status' => 'publish', 'post_author' => get_current_user_id() ), true ); if ( ! is_wp_error( $message_id ) ) { update_post_meta( $message_id, '_staffswap_recipient', (int) $listing->post_author ); update_post_meta( $message_id, '_staffswap_listing', (int) $listing->ID ); update_post_meta( $message_id, '_staffswap_read', '0' ); staffswap_notify_user( (int) $listing->post_author, 'New message about ' . $listing->post_title, wp_get_current_user()->display_name . ' sent you a message about your listing "' . $listing->post_title . '":' . "\n\n" . $message_text . "\n\n" . 'Reply here: ' . home_url( '/messages/' ), 'message' ); $notice = '<div class="notice"><div><h2>Message sent</h2><p class="muted">Your message has been sent to the listing owner.</p></div></div>'; } } } ob_start(); echo $notice; ?><div class="panel"><h2>Contact this professional</h2><form method="post"><div class="field"><label for="message">Your message</label><textarea id="message" name="message" rows="5" required placeholder="Introduce yourself and explain why this swap could work..."></textarea></div><?php wp_nonce_field( 'staffswap_send_message', 'staffswap_message_nonce' ); ?><input type="submit" name="staffswap_send_message" value="Send message"></form></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_contact', 'staffswap_contact_form_shortcode' );

function staffswap_inbox_shortcode() { if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to view your messages.</p></div>'; } $user_id = get_current_user_id(); $received = new WP_Query( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'posts_per_page' => 30, 'meta_key' => '_staffswap_recipient', 'meta_value' => $user_id ) ); $sent = new WP_Query( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'author' => $user_id, 'posts_per_page' => 30 ) ); ob_start(); ?><div class="message-inbox"><div class="page-heading"><div><p class="eyebrow">PRIVATE CONVERSATIONS</p><h1>Your messages</h1><p class="muted">Connect with potential exchange partners before you make a move.</p></div></div><section class="panel"><h2>Received</h2><?php if ( $received->have_posts() ) : while ( $received->have_posts() ) : $received->the_post(); ?><article class="message-row"><strong><?php the_title(); ?></strong><p><?php echo esc_html( wp_trim_words( get_the_content(), 22 ) ); ?></p><small class="muted">From <?php echo esc_html( get_the_author() ); ?></small></article><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">No received messages yet.</p><?php endif; ?></section><section class="panel" style="margin-top:16px"><h2>Sent</h2><?php if ( $sent->have_posts() ) : while ( $sent->have_posts() ) : $sent->the_post(); ?><article class="message-row"><strong><?php the_title(); ?></strong><p><?php echo esc_html( wp_trim_words( get_the_content(), 22 ) ); ?></p><small class="muted">Sent <?php echo esc_html( get_the_date() ); ?></small></article><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">No sent messages yet.</p><?php endif; ?></section></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_inbox', 'staffswap_inbox_shortcode' );

function staffswap_unread_message_count( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	if ( ! $user_id ) { return 0; }

	$query = new WP_Query( array(
		'post_type' => 'staff_message',
		'post_status' => 'publish',
		'fields' => 'ids',
		'posts_per_page' => 1,
		'no_found_rows' => false,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query' => array(
			array( 'key' => '_staffswap_recipient', 'value' => $user_id, 'compare' => '=' ),
			array( 'key' => '_staffswap_read', 'value' => '0', 'compare' => '=' ),
		),
	) );

	return (int) $query->found_posts;
}

function staffswap_pending_offer_count( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	if ( ! $user_id || ! post_type_exists( 'staffswap_offer' ) ) { return 0; }
	return (int) ( new WP_Query( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => '_staffswap_offer_recipient', 'value' => $user_id ), array( 'key' => '_staffswap_offer_status', 'value' => 'proposed' ) ) ) ) )->found_posts;
}

function staffswap_secure_inbox_shortcode() {
	if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to view your messages.</p></div>'; }
	$user_id = get_current_user_id();
	if ( isset( $_POST['staffswap_mark_message_read'] ) && check_admin_referer( 'staffswap_mark_message_' . absint( $_POST['message_id'] ?? 0 ), 'staffswap_message_read_nonce' ) ) {
		$message_id = absint( $_POST['message_id'] );
		if ( (int) get_post_meta( $message_id, '_staffswap_recipient', true ) === $user_id ) { update_post_meta( $message_id, '_staffswap_read', '1' ); }
	}
	if ( isset( $_POST['staffswap_send_reply'] ) && check_admin_referer( 'staffswap_reply_' . absint( $_POST['listing_id'] ?? 0 ), 'staffswap_reply_nonce' ) ) {
		$listing_id = absint( $_POST['listing_id'] ?? 0 ); $recipient = absint( $_POST['recipient_id'] ?? 0 ); $reply = sanitize_textarea_field( wp_unslash( $_POST['reply'] ?? '' ) );
		$conversation = get_posts( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'author' => $recipient, 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( 'relation' => 'AND', array( 'key' => '_staffswap_recipient', 'value' => $user_id ), array( 'key' => '_staffswap_listing', 'value' => $listing_id ) ) ) );
		if ( $listing_id && $recipient && $reply && $conversation && get_post_type( $listing_id ) === 'swap_listing' && $recipient !== $user_id ) { $reply_id = wp_insert_post( array( 'post_type' => 'staff_message', 'post_title' => 'Message about: ' . get_the_title( $listing_id ), 'post_content' => $reply, 'post_status' => 'publish', 'post_author' => $user_id ) ); if ( $reply_id ) { update_post_meta( $reply_id, '_staffswap_recipient', $recipient ); update_post_meta( $reply_id, '_staffswap_listing', $listing_id ); update_post_meta( $reply_id, '_staffswap_read', '0' ); staffswap_notify_user( $recipient, 'New reply about ' . get_the_title( $listing_id ), wp_get_current_user()->display_name . ' replied to your conversation about "' . get_the_title( $listing_id ) . '":' . "\n\n" . $reply . "\n\n" . 'View it here: ' . home_url( '/messages/' ), 'message' ); } }
	}
	$received = get_posts( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => array( array( 'key' => '_staffswap_recipient', 'value' => $user_id ) ) ) );
	$groups = array(); foreach ( $received as $message ) { $listing_id = absint( get_post_meta( $message->ID, '_staffswap_listing', true ) ); $groups[ $listing_id ?: $message->ID ][] = $message; }
	ob_start(); ?><div class="message-inbox"><div class="page-heading"><div><p class="eyebrow">PRIVATE CONVERSATIONS</p><h1>Your messages</h1><p class="muted">Connect with potential exchange partners before you make a move.</p></div></div><?php if ( $groups ) : ?><div class="message-conversations"><?php foreach ( $groups as $listing_id => $messages ) : $latest = $messages[0]; $sender_id = (int) $latest->post_author; $unread = false; foreach ( $messages as $message ) { if ( '0' === get_post_meta( $message->ID, '_staffswap_read', true ) ) { $unread = true; break; } } ?><section class="panel message-conversation <?php echo $unread ? 'is-unread' : ''; ?>"><header><div><p class="eyebrow"><?php echo $listing_id && get_post( $listing_id ) ? esc_html( get_the_title( $listing_id ) ) : 'General conversation'; ?></p><h2><?php echo esc_html( get_the_author_meta( 'display_name', $sender_id ) ); ?></h2></div><span class="message-count"><?php echo esc_html( count( $messages ) ); ?> messages</span></header><?php foreach ( $messages as $message ) : ?><article class="message-row <?php echo '0' === get_post_meta( $message->ID, '_staffswap_read', true ) ? 'is-unread' : ''; ?>"><strong><?php echo esc_html( get_the_author_meta( 'display_name', $message->post_author ) ); ?></strong><p><?php echo esc_html( get_the_content( null, false, $message ) ); ?></p><small class="muted"><?php echo esc_html( get_the_date( '', $message ) ); ?></small><?php if ( '0' === get_post_meta( $message->ID, '_staffswap_read', true ) ) : ?><form method="post" class="message-read-form"><input type="hidden" name="message_id" value="<?php echo esc_attr( $message->ID ); ?>"><?php wp_nonce_field( 'staffswap_mark_message_' . $message->ID, 'staffswap_message_read_nonce' ); ?><button type="submit" name="staffswap_mark_message_read" class="button button--outline">Mark as read</button></form><?php endif; ?></article><?php endforeach; ?><?php if ( $listing_id && $sender_id ) : ?><form method="post" class="message-reply"><label for="reply-<?php echo esc_attr( $listing_id ); ?>">Reply</label><textarea id="reply-<?php echo esc_attr( $listing_id ); ?>" name="reply" rows="2" required></textarea><input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing_id ); ?>"><input type="hidden" name="recipient_id" value="<?php echo esc_attr( $sender_id ); ?>"><?php wp_nonce_field( 'staffswap_reply_' . $listing_id, 'staffswap_reply_nonce' ); ?><button type="submit" name="staffswap_send_reply" class="button button--primary">Send reply</button></form><?php endif; ?></section><?php endforeach; ?></div><?php else : ?><section class="panel"><p class="muted">No received messages yet.</p></section><?php endif; ?></div><?php return ob_get_clean();
}
function staffswap_document_post_type() {
	register_post_type( 'staffswap_document', array( 'labels' => array( 'name' => 'Vault Documents', 'singular_name' => 'Vault Document' ), 'public' => false, 'show_ui' => false, 'supports' => array( 'title', 'author' ) ) );
}
add_action( 'init', 'staffswap_document_post_type' );

function staffswap_vault_directory() {
	$directory = trailingslashit( dirname( ABSPATH ) ) . 'staffswap-private-documents';
	return wp_mkdir_p( $directory ) && is_writable( $directory ) ? $directory : '';
}

function staffswap_create_document_vault_page() {
	if ( ! get_page_by_path( 'document-vault' ) ) {
		wp_insert_post( array( 'post_title' => 'Document Vault', 'post_name' => 'document-vault', 'post_content' => '[staffswap_document_vault]', 'post_status' => 'publish', 'post_type' => 'page' ) );
	}
}
add_action( 'init', 'staffswap_create_document_vault_page', 20 );

function staffswap_vault_document_url( $document_id ) {
	$document_id = absint( $document_id );
	return add_query_arg( array( 'action' => 'staffswap_download_vault_document', 'document_id' => $document_id, '_wpnonce' => wp_create_nonce( 'staffswap_vault_document_' . $document_id ) ), admin_url( 'admin-post.php' ) );
}

function staffswap_vault_store_upload( $owner_id, $file ) {
	$allowed_types = array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' );
	if ( empty( $file['name'] ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) || (int) $file['size'] > 10 * MB_IN_BYTES ) {
		return new WP_Error( 'staffswap_invalid_upload', 'Choose a valid document smaller than 10 MB.' );
	}
	$type = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed_types );
	if ( empty( $type['type'] ) || empty( $type['ext'] ) ) {
		return new WP_Error( 'staffswap_invalid_type', 'Use a PDF, JPG, PNG, or DOCX document.' );
	}
	$directory = staffswap_vault_directory();
	if ( ! $directory ) { return new WP_Error( 'staffswap_storage_unavailable', 'Private document storage is unavailable.' ); }
	$path = trailingslashit( $directory ) . wp_generate_uuid4() . '.' . sanitize_file_name( $type['ext'] );
	if ( ! move_uploaded_file( $file['tmp_name'], $path ) ) { return new WP_Error( 'staffswap_upload_failed', 'The document could not be saved.' ); }
	$document_id = wp_insert_post( array( 'post_type' => 'staffswap_document', 'post_status' => 'publish', 'post_title' => sanitize_file_name( $file['name'] ), 'post_author' => absint( $owner_id ) ), true );
	if ( is_wp_error( $document_id ) ) { wp_delete_file( $path ); return $document_id; }
	update_post_meta( $document_id, '_staffswap_document_path', $path );
	update_post_meta( $document_id, '_staffswap_document_name', sanitize_file_name( $file['name'] ) );
	update_post_meta( $document_id, '_staffswap_document_type', $type['type'] );
	update_post_meta( $document_id, '_staffswap_document_shared_with', array() );
	return $document_id;
}

function staffswap_vault_saved_copy_id( $document_id, $user_id ) {
	$copies = get_posts( array( 'post_type' => 'staffswap_document', 'post_status' => 'publish', 'author' => absint( $user_id ), 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_staffswap_source_document', 'value' => absint( $document_id ) ) ) ) );
	return $copies ? absint( $copies[0] ) : 0;
}

function staffswap_vault_copy_shared_document( $document_id, $user_id ) {
	$source = get_post( absint( $document_id ) );
	$shared_with = array_map( 'absint', (array) get_post_meta( $document_id, '_staffswap_document_shared_with', true ) );
	if ( ! $source || 'staffswap_document' !== $source->post_type || ! in_array( absint( $user_id ), $shared_with, true ) ) { return new WP_Error( 'staffswap_document_forbidden', 'This document is not shared with your account.' ); }
	if ( staffswap_vault_saved_copy_id( $document_id, $user_id ) ) { return true; }
	$source_path = get_post_meta( $document_id, '_staffswap_document_path', true );
	$directory = staffswap_vault_directory();
	if ( ! $directory || ! is_readable( $source_path ) ) { return new WP_Error( 'staffswap_document_missing', 'The shared document could not be found.' ); }
	$extension = pathinfo( $source_path, PATHINFO_EXTENSION );
	$copy_path = trailingslashit( $directory ) . wp_generate_uuid4() . '.' . sanitize_file_name( $extension );
	if ( ! copy( $source_path, $copy_path ) ) { return new WP_Error( 'staffswap_document_copy_failed', 'The document could not be saved to your vault.' ); }
	$copy_id = wp_insert_post( array( 'post_type' => 'staffswap_document', 'post_status' => 'publish', 'post_title' => get_the_title( $document_id ), 'post_author' => absint( $user_id ) ), true );
	if ( is_wp_error( $copy_id ) ) { wp_delete_file( $copy_path ); return $copy_id; }
	update_post_meta( $copy_id, '_staffswap_document_path', $copy_path );
	update_post_meta( $copy_id, '_staffswap_document_name', get_post_meta( $document_id, '_staffswap_document_name', true ) );
	update_post_meta( $copy_id, '_staffswap_document_type', get_post_meta( $document_id, '_staffswap_document_type', true ) );
	update_post_meta( $copy_id, '_staffswap_document_shared_with', array() );
	update_post_meta( $copy_id, '_staffswap_source_document', absint( $document_id ) );
	return true;
}

function staffswap_download_vault_document() {
	if ( ! is_user_logged_in() ) { wp_die( 'You are not allowed to view this document.', 403 ); }
	$document_id = absint( $_GET['document_id'] ?? 0 );
	$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
	$document = get_post( $document_id );
	if ( ! $document_id || ! wp_verify_nonce( $nonce, 'staffswap_vault_document_' . $document_id ) || ! $document || 'staffswap_document' !== $document->post_type ) { wp_die( 'You are not allowed to view this document.', 403 ); }
	$shared_with = array_map( 'absint', (array) get_post_meta( $document_id, '_staffswap_document_shared_with', true ) );
	if ( (int) $document->post_author !== get_current_user_id() && ! in_array( get_current_user_id(), $shared_with, true ) && ! current_user_can( 'manage_options' ) ) { wp_die( 'You are not allowed to view this document.', 403 ); }
	$path = get_post_meta( $document_id, '_staffswap_document_path', true );
	if ( ! $path || ! is_readable( $path ) ) { wp_die( 'Document not found.', 404 ); }
	nocache_headers();
	header( 'Content-Type: ' . sanitize_mime_type( get_post_meta( $document_id, '_staffswap_document_type', true ) ?: 'application/octet-stream' ) );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( get_post_meta( $document_id, '_staffswap_document_name', true ) ?: basename( $path ) ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
}
add_action( 'admin_post_staffswap_download_vault_document', 'staffswap_download_vault_document' );

function staffswap_document_vault_shortcode() {
	if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to view your document vault.</p></div>'; }
	$user_id = get_current_user_id();
	$notice = '';
	if ( isset( $_POST['staffswap_vault_upload'] ) && check_admin_referer( 'staffswap_vault_upload', 'staffswap_vault_nonce' ) ) {
		$document_id = staffswap_vault_store_upload( $user_id, $_FILES['staffswap_vault_file'] ?? array() );
		$notice = is_wp_error( $document_id ) ? '<div class="notice"><p>' . esc_html( $document_id->get_error_message() ) . '</p></div>' : '<div class="notice"><p>Document saved to your vault.</p></div>';
	}
	$documents = get_posts( array( 'post_type' => 'staffswap_document', 'post_status' => 'publish', 'author' => $user_id, 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC' ) );
	ob_start(); echo $notice; ?>
	<div class="document-vault"><div class="page-heading"><div><p class="eyebrow">PRIVATE STORAGE</p><h1>Document Vault</h1></div></div>
		<section class="panel vault-upload"><h2>Add a document</h2><form method="post" enctype="multipart/form-data"><label for="staffswap-vault-file">Choose file</label><input id="staffswap-vault-file" type="file" name="staffswap_vault_file" accept=".pdf,.jpg,.jpeg,.png,.docx" required><?php wp_nonce_field( 'staffswap_vault_upload', 'staffswap_vault_nonce' ); ?><button type="submit" name="staffswap_vault_upload" class="button button--primary">Save to vault</button></form></section>
		<section class="vault-documents"><h2>Your documents <span><?php echo esc_html( count( $documents ) ); ?></span></h2><?php if ( $documents ) : ?><ul><?php foreach ( $documents as $document ) : $path = get_post_meta( $document->ID, '_staffswap_document_path', true ); ?><li><div><strong><?php echo esc_html( get_post_meta( $document->ID, '_staffswap_document_name', true ) ?: get_the_title( $document ) ); ?></strong><small><?php echo esc_html( get_the_date( '', $document ) ); ?><?php echo is_readable( $path ) ? ' · ' . esc_html( size_format( filesize( $path ) ) ) : ''; ?></small></div><a class="button button--outline" href="<?php echo esc_url( staffswap_vault_document_url( $document->ID ) ); ?>">Download</a></li><?php endforeach; ?></ul><?php else : ?><p class="muted">Your vault is empty.</p><?php endif; ?></section>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'staffswap_document_vault', 'staffswap_document_vault_shortcode' );

function staffswap_complete_inbox_shortcode() {
	if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to view your messages.</p></div>'; }
	$user_id = get_current_user_id();
	$notice = '';
	if ( isset( $_POST['staffswap_mark_message_read'] ) && check_admin_referer( 'staffswap_mark_message_' . absint( $_POST['message_id'] ?? 0 ), 'staffswap_message_read_nonce' ) ) {
		$message_id = absint( $_POST['message_id'] );
		if ( (int) get_post_meta( $message_id, '_staffswap_recipient', true ) === $user_id ) { update_post_meta( $message_id, '_staffswap_read', '1' ); }
	}
	if ( isset( $_POST['staffswap_save_shared_document'] ) ) {
		$document_id = absint( $_POST['document_id'] ?? 0 );
		if ( $document_id && check_admin_referer( 'staffswap_save_shared_document_' . $document_id, 'staffswap_save_document_nonce' ) ) {
			$saved = staffswap_vault_copy_shared_document( $document_id, $user_id );
			$notice = is_wp_error( $saved ) ? '<div class="notice"><p>' . esc_html( $saved->get_error_message() ) . '</p></div>' : '<div class="notice"><p>Document saved to your vault.</p></div>';
		}
	}
	if ( isset( $_POST['staffswap_send_reply'] ) && check_admin_referer( 'staffswap_reply_' . absint( $_POST['listing_id'] ?? 0 ), 'staffswap_reply_nonce' ) ) {
		$listing_id = absint( $_POST['listing_id'] ?? 0 );
		$recipient = absint( $_POST['recipient_id'] ?? 0 );
		$reply = sanitize_textarea_field( wp_unslash( $_POST['reply'] ?? '' ) );
		$document_id = absint( $_POST['vault_document_id'] ?? 0 );
		$has_upload = ! empty( $_FILES['staffswap_attachment']['name'] );
		$conversation_args = array(
			'post_type' => 'staff_message',
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_query' => array(
				array( 'key' => '_staffswap_listing', 'value' => $listing_id ),
			),
		);
		$received_conversation = get_posts( array_merge( $conversation_args, array(
			'author' => $recipient,
			'meta_query' => array(
				'relation' => 'AND',
				array( 'key' => '_staffswap_listing', 'value' => $listing_id ),
				array( 'key' => '_staffswap_recipient', 'value' => $user_id ),
			),
		) ) );
		$sent_conversation = get_posts( array_merge( $conversation_args, array(
			'author' => $user_id,
			'meta_query' => array(
				'relation' => 'AND',
				array( 'key' => '_staffswap_listing', 'value' => $listing_id ),
				array( 'key' => '_staffswap_recipient', 'value' => $recipient ),
			),
		) ) );
		$authorized_thread = $listing_id && $recipient && ( $received_conversation || $sent_conversation ) && 'swap_listing' === get_post_type( $listing_id ) && $recipient !== $user_id;
		if ( $authorized_thread && ( $reply || $document_id || $has_upload ) ) {
			if ( $has_upload ) { $document_id = staffswap_vault_store_upload( $user_id, $_FILES['staffswap_attachment'] ); }
			if ( is_wp_error( $document_id ) ) {
				$notice = '<div class="notice"><p>' . esc_html( $document_id->get_error_message() ) . '</p></div>';
			} elseif ( $document_id && ( 'staffswap_document' !== get_post_type( $document_id ) || (int) get_post_field( 'post_author', $document_id ) !== $user_id ) ) {
				$document_id = 0;
				$notice = '<div class="notice"><p>Choose a document from your own vault.</p></div>';
			} else {
				$document_name = $document_id ? get_post_meta( $document_id, '_staffswap_document_name', true ) : '';
				$message_body = $reply ?: 'Shared a document: ' . $document_name;
				$reply_id = wp_insert_post( array( 'post_type' => 'staff_message', 'post_title' => 'Message about: ' . get_the_title( $listing_id ), 'post_content' => $message_body, 'post_status' => 'publish', 'post_author' => $user_id ), true );
				if ( ! is_wp_error( $reply_id ) ) {
					update_post_meta( $reply_id, '_staffswap_recipient', $recipient );
					update_post_meta( $reply_id, '_staffswap_listing', $listing_id );
					update_post_meta( $reply_id, '_staffswap_read', '0' );
					if ( $document_id ) {
						update_post_meta( $reply_id, '_staffswap_message_document', $document_id );
						$shared_with = array_map( 'absint', (array) get_post_meta( $document_id, '_staffswap_document_shared_with', true ) );
						$shared_with[] = $recipient;
						update_post_meta( $document_id, '_staffswap_document_shared_with', array_values( array_unique( $shared_with ) ) );
					}
					staffswap_notify_user( $recipient, 'New reply about ' . get_the_title( $listing_id ), wp_get_current_user()->display_name . ' replied to your conversation about "' . get_the_title( $listing_id ) . '":' . "\n\n" . $message_body . "\n\n" . 'View it here: ' . home_url( '/messages/' ), 'message' );
				} else {
					$notice = '<div class="notice"><p>Your message could not be sent.</p></div>';
				}
			}
		} elseif ( ! $authorized_thread ) {
			$notice = '<div class="notice"><p>This conversation is no longer available.</p></div>';
		} else {
			$notice = '<div class="notice"><p>Write a message or attach a document.</p></div>';
		}
	}
	$received = get_posts( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => array( array( 'key' => '_staffswap_recipient', 'value' => $user_id ) ) ) );
	$sent = get_posts( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'author' => $user_id, 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC' ) );
	$messages_by_id = array();
	foreach ( array_merge( $received, $sent ) as $message ) { $messages_by_id[ $message->ID ] = $message; }
	$messages = array_values( $messages_by_id );
	usort( $messages, function( $left, $right ) { return strcmp( $right->post_date, $left->post_date ); } );
	$groups = array();
	foreach ( $messages as $message ) {
		$listing_id = absint( get_post_meta( $message->ID, '_staffswap_listing', true ) );
		$author_id = (int) $message->post_author;
		$recipient_id = absint( get_post_meta( $message->ID, '_staffswap_recipient', true ) );
		$participant_id = $author_id === $user_id ? $recipient_id : $author_id;
		if ( ! $participant_id || $participant_id === $user_id ) { continue; }
		$group_id = $listing_id ? $listing_id . ':' . $participant_id : 'general:' . $participant_id;
		if ( ! isset( $groups[ $group_id ] ) ) { $groups[ $group_id ] = array( 'listing_id' => $listing_id, 'participant_id' => $participant_id, 'messages' => array() ); }
		$groups[ $group_id ]['messages'][] = $message;
	}
	$vault_documents = get_posts( array( 'post_type' => 'staffswap_document', 'post_status' => 'publish', 'author' => $user_id, 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC' ) );
	ob_start(); echo $notice; ?>
	<div class="message-inbox">
		<div class="page-heading"><div><p class="eyebrow">PRIVATE CONVERSATIONS</p><h1>Your messages</h1><p class="muted">Connect with potential exchange partners before you make a move.</p></div><a class="button button--outline" href="<?php echo esc_url( home_url( '/document-vault/' ) ); ?>">Document Vault</a></div>
		<?php if ( $groups ) : ?>
			<div class="message-conversations">
				<?php foreach ( $groups as $group ) : $listing_id = $group['listing_id']; $participant_id = $group['participant_id']; $thread_messages = $group['messages']; $unread = false; foreach ( $thread_messages as $thread_message ) { if ( (int) get_post_meta( $thread_message->ID, '_staffswap_recipient', true ) === $user_id && '0' === get_post_meta( $thread_message->ID, '_staffswap_read', true ) ) { $unread = true; break; } } $reply_form_id = 'reply-' . $listing_id . '-' . $participant_id; ?>
					<section class="panel message-conversation <?php echo $unread ? 'is-unread' : ''; ?>">
						<header><div><p class="eyebrow"><?php echo $listing_id && get_post( $listing_id ) ? esc_html( get_the_title( $listing_id ) ) : 'General conversation'; ?></p><h2><?php echo esc_html( get_the_author_meta( 'display_name', $participant_id ) ); ?></h2></div><span class="message-count"><?php echo esc_html( count( $thread_messages ) ); ?> messages</span></header>
						<?php foreach ( $thread_messages as $message ) : $is_own = (int) $message->post_author === $user_id; $is_unread = ! $is_own && '0' === get_post_meta( $message->ID, '_staffswap_read', true ); $document_id = absint( get_post_meta( $message->ID, '_staffswap_message_document', true ) ); $attached_document = $document_id ? get_post( $document_id ) : false; $shared_with = $document_id ? array_map( 'absint', (array) get_post_meta( $document_id, '_staffswap_document_shared_with', true ) ) : array(); $can_access_document = $attached_document && 'staffswap_document' === $attached_document->post_type && ( (int) $attached_document->post_author === $user_id || in_array( $user_id, $shared_with, true ) ); ?>
							<article class="message-row <?php echo $is_own ? 'is-own' : ''; ?> <?php echo $is_unread ? 'is-unread' : ''; ?>"><strong><?php echo esc_html( get_the_author_meta( 'display_name', $message->post_author ) ); ?></strong><p><?php echo esc_html( get_the_content( null, false, $message ) ); ?></p><small class="muted"><?php echo esc_html( get_the_date( '', $message ) ); ?></small>
								<?php if ( $can_access_document ) : ?><div class="message-attachment"><a href="<?php echo esc_url( staffswap_vault_document_url( $document_id ) ); ?>"><?php echo esc_html( get_post_meta( $document_id, '_staffswap_document_name', true ) ?: get_the_title( $document_id ) ); ?></a><?php if ( (int) $attached_document->post_author !== $user_id ) : ?><?php if ( staffswap_vault_saved_copy_id( $document_id, $user_id ) ) : ?><span>Saved to your vault</span><?php else : ?><form method="post"><?php wp_nonce_field( 'staffswap_save_shared_document_' . $document_id, 'staffswap_save_document_nonce' ); ?><input type="hidden" name="document_id" value="<?php echo esc_attr( $document_id ); ?>"><button type="submit" name="staffswap_save_shared_document" class="button button--outline">Save to my vault</button></form><?php endif; ?><?php endif; ?></div><?php endif; ?>
								<?php if ( $is_unread ) : ?><form method="post" class="message-read-form"><input type="hidden" name="message_id" value="<?php echo esc_attr( $message->ID ); ?>"><?php wp_nonce_field( 'staffswap_mark_message_' . $message->ID, 'staffswap_message_read_nonce' ); ?><button type="submit" name="staffswap_mark_message_read" class="button button--outline">Mark as read</button></form><?php endif; ?>
							</article>
						<?php endforeach; ?>
						<?php if ( $listing_id ) : ?><form method="post" enctype="multipart/form-data" class="message-reply"><label for="<?php echo esc_attr( $reply_form_id ); ?>">Reply</label><textarea id="<?php echo esc_attr( $reply_form_id ); ?>" name="reply" rows="2"></textarea><label for="attachment-<?php echo esc_attr( $reply_form_id ); ?>">Attach a document</label><input id="attachment-<?php echo esc_attr( $reply_form_id ); ?>" type="file" name="staffswap_attachment" accept=".pdf,.jpg,.jpeg,.png,.docx"><label for="vault-document-<?php echo esc_attr( $reply_form_id ); ?>">Or share from your vault</label><select id="vault-document-<?php echo esc_attr( $reply_form_id ); ?>" name="vault_document_id"><option value="">No document</option><?php foreach ( $vault_documents as $vault_document ) : ?><option value="<?php echo esc_attr( $vault_document->ID ); ?>"><?php echo esc_html( get_post_meta( $vault_document->ID, '_staffswap_document_name', true ) ?: get_the_title( $vault_document ) ); ?></option><?php endforeach; ?></select><input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing_id ); ?>"><input type="hidden" name="recipient_id" value="<?php echo esc_attr( $participant_id ); ?>"><?php wp_nonce_field( 'staffswap_reply_' . $listing_id, 'staffswap_reply_nonce' ); ?><button type="submit" name="staffswap_send_reply" class="button button--primary">Send</button></form><?php endif; ?>
					</section>
				<?php endforeach; ?>
			</div>
		<?php else : ?><section class="panel"><p class="muted">No conversations yet.</p></section><?php endif; ?>
	</div>
	<?php return ob_get_clean();
}
remove_shortcode( 'staffswap_inbox' );
add_shortcode( 'staffswap_inbox', 'staffswap_complete_inbox_shortcode' );

function staffswap_offer_post_type() {
	register_post_type( 'staffswap_offer', array( 'labels' => array( 'name' => 'Swap Offers', 'singular_name' => 'Swap Offer' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=swap_listing', 'supports' => array( 'title', 'editor', 'author' ), 'capability_type' => 'post' ) );
}
add_action( 'init', 'staffswap_offer_post_type' );

function staffswap_offer_document_directory() {
	$directory = trailingslashit( dirname( ABSPATH ) ) . 'staffswap-private-documents';
	if ( ! wp_mkdir_p( $directory ) ) {
		return '';
	}
	return $directory;
}

function staffswap_offer_document_url( $offer_id ) {
	$offer_id = absint( $offer_id );
	if ( '3' !== get_post_meta( $offer_id, '_staffswap_offer_document_version', true ) ) {
		staffswap_generate_offer_agreement_document( $offer_id );
	}
	$path = get_post_meta( $offer_id, '_staffswap_offer_agreement_file', true );
	if ( ! $offer_id || empty( $path ) || ! is_readable( $path ) ) {
		return '';
	}
	$nonce = wp_create_nonce( 'staffswap_offer_document_' . $offer_id );
	return add_query_arg( array( 'action' => 'staffswap_download_offer_document', 'offer_id' => $offer_id, 'file' => rawurlencode( basename( $path ) ), '_wpnonce' => $nonce ), admin_url( 'admin-post.php' ) );
}

function staffswap_offer_letter_url( $offer_id, $user_id = 0 ) {
	$offer_id = absint( $offer_id );
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	$offer = get_post( $offer_id );
	if ( ! $offer || 'staffswap_offer' !== $offer->post_type || ! in_array( staffswap_offer_current_status( $offer_id ), array( 'accepted', 'completed' ), true ) ) {
		return '';
	}
	$party = (int) $offer->post_author === $user_id ? 'sender' : ( (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true ) === $user_id ? 'recipient' : '' );
	if ( $party && '4' !== get_post_meta( $offer_id, '_staffswap_offer_letters_version', true ) ) {
		staffswap_generate_offer_letters( $offer_id );
	}
	$path = $party ? get_post_meta( $offer_id, '_staffswap_offer_' . $party . '_letter_file', true ) : '';
	if ( ! $path || ! is_readable( $path ) ) {
		return '';
	}
	$nonce = wp_create_nonce( 'staffswap_offer_document_' . $offer_id );
	return add_query_arg( array( 'action' => 'staffswap_download_offer_document', 'offer_id' => $offer_id, 'document' => $party . '_letter', 'file' => rawurlencode( basename( $path ) ), '_wpnonce' => $nonce ), admin_url( 'admin-post.php' ) );
}

function staffswap_offer_letter_link( $offer_id, $user_id = 0 ) {
	$url = staffswap_offer_letter_url( $offer_id, $user_id );
	return $url ? '<p><a class="button button--outline" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">View / print your transfer request letter</a></p>' : '';
}

function staffswap_offer_listing_details( $listing_id ) {
	$listing_id = absint( $listing_id );
	$listing = get_post( $listing_id );
	if ( ! $listing || 'swap_listing' !== $listing->post_type ) {
		return array();
	}
	return array(
		'id' => $listing_id,
		'name' => get_the_author_meta( 'display_name', (int) $listing->post_author ) ?: 'Staff member',
		'profession' => get_post_meta( $listing_id, '_staffswap_profession', true ) ?: 'Not specified',
		'current_employer' => get_post_meta( $listing_id, '_staffswap_current_employer', true ) ?: 'Not specified',
		'desired_employer' => get_post_meta( $listing_id, '_staffswap_desired_employer', true ) ?: 'Not specified',
		'current_location' => get_post_meta( $listing_id, '_staffswap_current_location', true ) ?: 'Not specified',
		'desired_location' => get_post_meta( $listing_id, '_staffswap_desired_location', true ) ?: 'Not specified',
	);
}

function staffswap_offer_sender_listing_id( $offer_id ) {
	$offer_id = absint( $offer_id );
	$offer = get_post( $offer_id );
	if ( ! $offer || 'staffswap_offer' !== $offer->post_type ) {
		return 0;
	}
	$listing_id = absint( get_post_meta( $offer_id, '_staffswap_offer_sender_listing', true ) );
	$listing = $listing_id ? get_post( $listing_id ) : false;
	if ( $listing && 'swap_listing' === $listing->post_type && 'publish' === $listing->post_status && (int) $listing->post_author === (int) $offer->post_author ) {
		return $listing_id;
	}

	$recipient_listing_id = absint( get_post_meta( $offer_id, '_staffswap_offer_listing', true ) );
	$recipient_listing = staffswap_offer_listing_details( $recipient_listing_id );
	if ( ! $recipient_listing ) {
		return 0;
	}
	$candidates = get_posts( array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'author' => (int) $offer->post_author, 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC' ) );
	$best_id = 0;
	$best_score = -1;
	foreach ( $candidates as $candidate_id ) {
		$candidate = staffswap_offer_listing_details( $candidate_id );
		if ( ! $candidate ) { continue; }
		$score = 0;
		if ( $candidate['current_location'] === $recipient_listing['desired_location'] ) { $score += 2; }
		if ( $candidate['desired_location'] === $recipient_listing['current_location'] ) { $score += 2; }
		if ( $candidate['profession'] === $recipient_listing['profession'] ) { $score += 1; }
		if ( $score > $best_score ) {
			$best_id = (int) $candidate_id;
			$best_score = $score;
		}
	}
	if ( $best_id ) {
		update_post_meta( $offer_id, '_staffswap_offer_sender_listing', $best_id );
	}
	return $best_id;
}

function staffswap_generate_ai_swap_summary( $data ) {
	$settings = get_option( 'staffswap_ai_settings', array() );
	$api_key = trim( (string) ( $settings['api_key'] ?? '' ) );
	if ( 'yes' !== ( $settings['enabled'] ?? '' ) || empty( $api_key ) ) {
		return '';
	}

	$prompt = sprintf(
		"Draft a concise summary for a staff swap agreement between %s and %s. Include the recipient listing's current and desired locations, profession, effective date, and any notes about housing or logistics.\n\nRecipient listing current location: %s\nRecipient listing desired location: %s\nProfession: %s\nEffective date: %s\nHousing: %s\nNotes: %s",
		wp_strip_all_tags( $data['sender_name'] ?? '' ),
		wp_strip_all_tags( $data['recipient_name'] ?? '' ),
		wp_strip_all_tags( $data['listing_current_location'] ?? '' ),
		wp_strip_all_tags( $data['listing_desired_location'] ?? '' ),
		wp_strip_all_tags( $data['profession'] ?? '' ),
		wp_strip_all_tags( $data['effective_date'] ?? '' ),
		wp_strip_all_tags( $data['housing'] ?? 'Not specified' ),
		wp_strip_all_tags( $data['notes'] ?? 'No additional notes.' )
	);

	$response = wp_remote_post(
		'https://api.openai.com/v1/chat/completions',
		array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json',
			),
			'body' => wp_json_encode( array(
				'model' => ! empty( $settings['model'] ) ? $settings['model'] : 'gpt-4o-mini',
				'messages' => array(
					array(
						'role' => 'system',
						'content' => 'You are drafting a short, neutral summary for a staff swap agreement. Keep it concise and factual. Do not invent legal obligations not in the provided data.',
					),
					array(
						'role' => 'user',
						'content' => $prompt,
					),
				),
				'temperature' => 0.2,
			) ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return '';
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return '';
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['choices'][0]['message']['content'] ) ) {
		return '';
	}

	return wp_kses_post( $body['choices'][0]['message']['content'] );
}

function staffswap_offer_document_shell( $title, $reference, $body ) {
	$css = '@page{size:A4;margin:22mm}.sender-block{text-align:right;line-height:1.5;margin:0 0 6px;color:#14324c}.date-line{text-align:right;color:#5a6b7b;margin:0 0 26px}.addressee{margin:0 0 22px;line-height:1.55}.salutation{margin-top:22px}.subject{font-weight:700;color:#14324c;text-decoration:underline;text-align:left;margin:18px 0 2px;letter-spacing:.02em}.subject-sub{text-align:left;color:#5a6b7b;font-size:13px;margin:0 0 16px}.closing{margin-top:26px}.cc{text-align:left;font-size:12.5px;color:#4a5b6b;margin-top:34px;line-height:1.6}*{box-sizing:border-box}body{margin:0;background:#e9edf0;color:#1f2a36;font:15px/1.75 Georgia,"Times New Roman",serif}.page{max-width:794px;margin:28px auto;background:#fff;padding:56px 72px;box-shadow:0 10px 30px #10243a1f}.letterhead{display:flex;justify-content:space-between;align-items:flex-end;border-bottom:2px solid #14324c;padding-bottom:12px;margin-bottom:32px;font:12px Arial,sans-serif;color:#5a6b7b}.letterhead b{font:700 18px Georgia,serif;color:#14324c;display:block}h1{font:700 19px/1.4 Georgia,serif;color:#14324c;margin:8px 0 6px}h2{font:700 15px Georgia,serif;color:#14324c;margin:28px 0 4px;border-bottom:1px solid #d9e1e8;padding-bottom:3px}p{margin:10px 0;text-align:justify}.lead,.meta{color:#5a6b7b;font-size:13px;text-align:left}.sigs{display:flex;gap:48px;margin-top:56px}.sig{flex:1;border-top:1px solid #4a5b6b;padding-top:6px;font-size:14px;line-height:1.5}.sig small{color:#5a6b7b;font:11px Arial,sans-serif}.note{margin-top:36px;padding-top:10px;border-top:1px solid #d9e1e8;color:#7a8794;font:11px/1.5 Arial,sans-serif;text-align:left}@media print{body{background:#fff}.page{margin:0;padding:0;box-shadow:none;max-width:none}}@media(max-width:640px){.page{margin:0;padding:28px 20px}.sigs{flex-direction:column;gap:36px}}';
	return '<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html( $title ) . '</title><style>' . $css . '</style></head><body><main class="page"><header class="letterhead"><span><b>StaffSwap</b>StaffExchangeHub</span><span>Ref. ' . esc_html( $reference ) . '</span></header>' . $body . '<p class="note">Generated ' . esc_html( current_time( 'mysql' ) ) . ' · Private document for the parties named · Print or save as PDF for your records.</p></main></body></html>';
}

function staffswap_generate_offer_agreement_document( $offer_id ) {
	$offer_id = absint( $offer_id );
	$offer = get_post( $offer_id );
	if ( ! $offer || 'staffswap_offer' !== $offer->post_type ) {
		return false;
	}

	$directory = staffswap_offer_document_directory();
	if ( empty( $directory ) ) {
		return false;
	}

	$recipient_listing_id = absint( get_post_meta( $offer_id, '_staffswap_offer_listing', true ) );
	$sender_listing_id = staffswap_offer_sender_listing_id( $offer_id );
	$recipient = staffswap_offer_listing_details( $recipient_listing_id );
	$sender = staffswap_offer_listing_details( $sender_listing_id );
	if ( ! $recipient || ! $sender ) {
		return false;
	}
	$is_accepted = in_array( staffswap_offer_current_status( $offer_id ), array( 'accepted', 'completed' ), true );
	$document_title = $is_accepted ? 'StaffSwap Swap Agreement' : 'StaffSwap Offer Summary';
	$recipient_id = absint( get_post_meta( $offer_id, '_staffswap_offer_recipient', true ) );
	$sender_name = $sender['name'];
	$recipient_name = $recipient['name'];
	$housing = get_post_meta( $offer_id, '_staffswap_offer_housing', true ) ?: 'Not specified';
	$effective_date = get_post_meta( $offer_id, '_staffswap_offer_effective_date', true ) ?: 'Not specified';
	$notes = $offer->post_content ?: 'No additional notes provided.';
	$ai_summary = $is_accepted ? staffswap_generate_ai_swap_summary( array(
		'sender_name' => $sender_name,
		'recipient_name' => $recipient_name,
		'listing_current_location' => $recipient['current_location'],
		'listing_desired_location' => $recipient['desired_location'],
		'profession' => $recipient['profession'],
		'effective_date' => $effective_date,
		'housing' => $housing,
		'notes' => $notes,
	) ) : '';
	$status_label = $is_accepted ? 'Accepted' : 'Proposed';
	$p = function( $text ) { return '<p>' . $text . '</p>'; };
	$b = function( $text ) { return '<strong>' . esc_html( $text ) . '</strong>'; };
	$body = '<h1>' . esc_html( $is_accepted ? 'Reciprocal Staff Exchange Agreement' : 'Reciprocal Staff Exchange Proposal' ) . '</h1>'
		. '<p class="lead">Reference SS-' . esc_html( $offer_id ) . ' &nbsp;·&nbsp; Status: ' . esc_html( $status_label ) . ' &nbsp;·&nbsp; Prepared ' . esc_html( current_time( 'F j, Y' ) ) . '</p>'
		. '<h2>1. Parties</h2>'
		. $p( 'This ' . ( $is_accepted ? 'agreement' : 'proposal' ) . ' is made between ' . $b( $sender_name ) . ', ' . esc_html( $sender['profession'] ) . ' currently serving at ' . $b( $sender['current_employer'] ) . ' in ' . $b( $sender['current_location'] ) . ' (the “First Party”), and ' . $b( $recipient_name ) . ', ' . esc_html( $recipient['profession'] ) . ' currently serving at ' . $b( $recipient['current_employer'] ) . ' in ' . $b( $recipient['current_location'] ) . ' (the “Second Party”).' )
		. '<h2>2. Purpose</h2>'
		. $p( 'The parties wish to exchange duty stations on a reciprocal basis. The First Party seeks to relocate to ' . $b( $sender['desired_location'] ) . ( 'Not specified' !== $sender['desired_employer'] ? ' and to serve with ' . $b( $sender['desired_employer'] ) : '' ) . '. The Second Party seeks to relocate to ' . $b( $recipient['desired_location'] ) . ( 'Not specified' !== $recipient['desired_employer'] ? ' and to serve with ' . $b( $recipient['desired_employer'] ) : '' ) . '. Each party’s requested posting corresponds to the other party’s current posting.' )
		. '<h2>3. Proposed Effective Date</h2>'
		. $p( 'The exchange is proposed to take effect on ' . $b( $effective_date ) . ', subject to the approvals described in section 6.' )
		. '<h2>4. Housing and Logistics</h2>'
		. $p( 'Housing arrangement: ' . esc_html( $housing ) . '. Each party remains responsible for their own relocation, accommodation and related costs unless otherwise agreed in writing.' )
		. '<h2>5. Additional Notes</h2>'
		. $p( nl2br( esc_html( $notes ) ) )
		. ( $ai_summary ? '<h2>Summary</h2>' . $p( wp_kses_post( nl2br( $ai_summary ) ) ) : '' )
		. '<h2>6. Employer Approval</h2>'
		. $p( 'This exchange is conditional on written approval from the competent authority of each employer. Neither party may leave their current post or assume the other’s post until that approval has been granted. StaffSwap facilitates introductions and records the parties’ intentions; it is not an employer and does not authorize transfers.' )
		. '<h2>7. ' . ( $is_accepted ? 'Acceptance' : 'Status of this Document' ) . '</h2>'
		. $p( $is_accepted ? 'By accepting this offer in StaffSwap, both parties confirm that the information above is accurate and that they intend to pursue the exchange in good faith. Please sign below for your records.' : 'This is a proposal only. It becomes an agreement in StaffSwap when the Second Party accepts it, and does not bind either party until then.' )
		. ( $is_accepted ? '<div class="sigs"><div class="sig">' . esc_html( $sender_name ) . '<br><small>First Party — signature and date</small></div><div class="sig">' . esc_html( $recipient_name ) . '<br><small>Second Party — signature and date</small></div></div>' : '' );
	$html = staffswap_offer_document_shell( $document_title, 'SS-' . $offer_id, $body );

	$file_name = 'swap-offer-' . $offer_id . '.html';
	$file_path = trailingslashit( $directory ) . $file_name;
	if ( false === file_put_contents( $file_path, $html ) ) {
		return false;
	}

	update_post_meta( $offer_id, '_staffswap_offer_agreement_file', $file_path );
	update_post_meta( $offer_id, '_staffswap_offer_agreement_status', 'generated' );
	update_post_meta( $offer_id, '_staffswap_offer_agreement_updated_at', current_time( 'mysql', true ) );
	update_post_meta( $offer_id, '_staffswap_offer_document_version', '3' );

	return $file_path;
}

function staffswap_generate_offer_letters( $offer_id ) {
	$offer_id = absint( $offer_id );
	$offer = get_post( $offer_id );
	if ( ! $offer || 'staffswap_offer' !== $offer->post_type || ! in_array( staffswap_offer_current_status( $offer_id ), array( 'accepted', 'completed' ), true ) ) {
		return false;
	}
	$directory = staffswap_offer_document_directory();
	$recipient = staffswap_offer_listing_details( absint( get_post_meta( $offer_id, '_staffswap_offer_listing', true ) ) );
	$sender = staffswap_offer_listing_details( staffswap_offer_sender_listing_id( $offer_id ) );
	if ( ! $directory || ! $recipient || ! $sender ) {
		return false;
	}

	$effective_date = get_post_meta( $offer_id, '_staffswap_offer_effective_date', true ) ?: 'To be agreed';
	$generated_at = current_time( 'mysql' );
	$parties = array(
		'sender' => array( 'member' => $sender, 'counterpart' => $recipient ),
		'recipient' => array( 'member' => $recipient, 'counterpart' => $sender ),
	);
	$generated = array();
	foreach ( $parties as $party => $details ) {
		$member = $details['member'];
		$counterpart = $details['counterpart'];
		$desired_employer = 'Not specified' !== $member['desired_employer'] ? $member['desired_employer'] : '';
		$body = '<div class="sender-block"><strong>' . esc_html( $member['name'] ) . '</strong><br>' . esc_html( $member['profession'] ) . '<br>' . esc_html( $member['current_employer'] ) . '<br>' . esc_html( $member['current_location'] ) . '</div>'
			. '<p class="date-line">' . esc_html( current_time( 'j F Y' ) ) . '</p>'
			. '<div class="addressee">The Human Resources Manager<br>(Attention: Authorized Approving Officer)<br><strong>' . esc_html( $member['current_employer'] ) . '</strong><br>' . esc_html( $member['current_location'] ) . '</div>'
			. '<p class="salutation">Dear Sir/Madam,</p>'
			. '<p class="subject">RE: REQUEST FOR RECIPROCAL STAFF EXCHANGE (TRANSFER) FROM ' . esc_html( strtoupper( $member['current_location'] ) ) . ' TO ' . esc_html( strtoupper( $member['desired_location'] ) ) . '</p>'
			. '<p class="subject-sub">Our reference: SS-' . esc_html( $offer_id ) . '</p>'
			. '<p>I write to respectfully request your consideration and approval of a reciprocal exchange of duty stations. I am currently serving as <strong>' . esc_html( $member['profession'] ) . '</strong> at <strong>' . esc_html( $member['current_employer'] ) . '</strong> in <strong>' . esc_html( $member['current_location'] ) . '</strong>.</p>'
			. '<p>For personal and family reasons, I wish to be posted to <strong>' . esc_html( $member['desired_location'] ) . '</strong>' . ( $desired_employer ? ', preferably with <strong>' . esc_html( $desired_employer ) . '</strong>' : '' ) . '. I have identified an officer who is willing to make the reverse move: <strong>' . esc_html( $counterpart['name'] ) . '</strong>, a <strong>' . esc_html( $counterpart['profession'] ) . '</strong> currently serving at <strong>' . esc_html( $counterpart['current_employer'] ) . '</strong> in <strong>' . esc_html( $counterpart['current_location'] ) . '</strong>, who wishes to move to my present station. Both of us have agreed in principle to the exchange.</p>'
			. '<p>This arrangement is to the mutual benefit of the service: each station would continue to be served by an officer of the same profession, so no vacancy would be created and no additional recruitment or cost to the employer would arise. We propose that the exchange take effect on <strong>' . esc_html( $effective_date ) . '</strong>, or on any later date that better suits the operational requirements of the employers concerned.</p>'
			. '<p>I understand that the exchange may proceed only with the written approval of both employers. I undertake to comply with all applicable procedures, to complete a proper handover of my duties, and to meet any conditions you may set. I would be grateful if you would review this request and advise me of the steps required and any supporting documents you need from me.</p>'
			. '<p>Thank you for your kind attention and consideration. I look forward to your favourable response.</p>'
			. '<p class="closing">Yours faithfully,</p>'
			. '<div class="sigs"><div class="sig"><strong>' . esc_html( $member['name'] ) . '</strong><br><small>' . esc_html( $member['profession'] ) . ', ' . esc_html( $member['current_employer'] ) . '</small><br><small>Signature and date</small></div></div>'
			. '<p class="cc"><strong>Enclosure:</strong> Exchange counterpart details — ' . esc_html( $counterpart['name'] ) . ', ' . esc_html( $counterpart['current_employer'] ) . ', ' . esc_html( $counterpart['current_location'] ) . '<br><strong>Copy to:</strong> Receiving employer — ' . esc_html( $counterpart['current_employer'] ) . '</p>'
			. '<p class="note">Draft prepared through StaffSwap from member-submitted information. It has not been issued, verified or approved by any employer and does not authorize a transfer. Please review and edit before submitting.</p>';
		$html = staffswap_offer_document_shell( 'Request for staff exchange', 'SS-' . $offer_id, $body );
		$file_path = trailingslashit( $directory ) . 'swap-transfer-request-' . $offer_id . '-' . $party . '.html';
		if ( false === file_put_contents( $file_path, $html ) ) {
			error_log( 'StaffSwap transfer letter generation failed for offer ' . $offer_id . ' (' . $party . ').' );
			return false;
		}
		update_post_meta( $offer_id, '_staffswap_offer_' . $party . '_letter_file', $file_path );
		$generated[] = $file_path;
	}
	if ( 2 !== count( $generated ) ) { return false; }
	update_post_meta( $offer_id, '_staffswap_offer_letters_version', '4' );
	return true;
}

function staffswap_download_offer_document() {
	if ( ! is_user_logged_in() ) {
		wp_die( 'You are not allowed to view this document.', 403 );
	}

	$offer_id = absint( $_GET['offer_id'] ?? 0 );
	$document = sanitize_key( wp_unslash( $_GET['document'] ?? 'agreement' ) );
	$file_name = sanitize_file_name( wp_unslash( $_GET['file'] ?? '' ) );
	$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
	if ( ! $offer_id || ! $file_name || ! wp_verify_nonce( $nonce, 'staffswap_offer_document_' . $offer_id ) ) {
		wp_die( 'You are not allowed to view this document.', 403 );
	}

	$offer = get_post( $offer_id );
	if ( ! $offer || 'staffswap_offer' !== $offer->post_type ) {
		wp_die( 'Document not found.', 404 );
	}

	$current_user = get_current_user_id();
	$recipient_id = (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true );
	$sender_id = (int) $offer->post_author;
	if ( $current_user !== $sender_id && $current_user !== $recipient_id && ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You are not allowed to view this document.', 403 );
	}

	if ( 'sender_letter' === $document ) {
		if ( $current_user !== $sender_id || ! in_array( staffswap_offer_current_status( $offer_id ), array( 'accepted', 'completed' ), true ) ) { wp_die( 'You are not allowed to view this document.', 403 ); }
		$stored_path = get_post_meta( $offer_id, '_staffswap_offer_sender_letter_file', true );
	} elseif ( 'recipient_letter' === $document ) {
		if ( $current_user !== $recipient_id || ! in_array( staffswap_offer_current_status( $offer_id ), array( 'accepted', 'completed' ), true ) ) { wp_die( 'You are not allowed to view this document.', 403 ); }
		$stored_path = get_post_meta( $offer_id, '_staffswap_offer_recipient_letter_file', true );
	} elseif ( 'agreement' === $document ) {
		$stored_path = get_post_meta( $offer_id, '_staffswap_offer_agreement_file', true );
	} else {
		wp_die( 'Document not found.', 404 );
	}
	if ( empty( $stored_path ) || ! is_readable( $stored_path ) || basename( $stored_path ) !== $file_name ) {
		wp_die( 'Document not found.', 404 );
	}

	nocache_headers();
	http_response_code( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Content-Disposition: inline; filename="' . sanitize_file_name( basename( $stored_path ) ) . '"' );
	header( 'Content-Length: ' . filesize( $stored_path ) );
	readfile( $stored_path );
	exit;
}
add_action( 'admin_post_staffswap_download_offer_document', 'staffswap_download_offer_document' );

// Keeps an auditable history of every status change, shown to members and useful for admin dispute review.
function staffswap_offer_log_status( $offer_id, $status, $user_id = 0, $reason = '' ) {
	$log = (array) get_post_meta( $offer_id, '_staffswap_offer_status_log', true );
	$log[] = array( 'status' => $status, 'at' => current_time( 'mysql', true ), 'by' => $user_id ?: get_current_user_id(), 'reason' => $reason );
	update_post_meta( $offer_id, '_staffswap_offer_status_log', $log );
}

// Custom admin columns so reviewers can see offer status/route/parties without opening each one.
function staffswap_offer_admin_columns( $columns ) {
	$columns = array_slice( $columns, 0, 2, true ) + array( 'staffswap_status' => 'Status', 'staffswap_route' => 'Route', 'staffswap_parties' => 'Sender / Recipient', 'staffswap_dates' => 'Effective / Expires' ) + array_slice( $columns, 2, null, true );
	return $columns;
}
add_filter( 'manage_staffswap_offer_posts_columns', 'staffswap_offer_admin_columns' );

function staffswap_offer_admin_column_content( $column, $post_id ) {
	if ( 'staffswap_status' === $column ) { echo esc_html( ucfirst( staffswap_offer_current_status( $post_id ) ) ); }
	if ( 'staffswap_route' === $column ) { $listing_id = (int) get_post_meta( $post_id, '_staffswap_offer_listing', true ); echo $listing_id ? esc_html( get_the_title( $listing_id ) ) : '—'; }
	if ( 'staffswap_parties' === $column ) { echo esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) ) . ' &rarr; ' . esc_html( get_the_author_meta( 'display_name', (int) get_post_meta( $post_id, '_staffswap_offer_recipient', true ) ) ); }
	if ( 'staffswap_dates' === $column ) { echo esc_html( get_post_meta( $post_id, '_staffswap_offer_effective_date', true ) ) . ' / ' . esc_html( get_post_meta( $post_id, '_staffswap_offer_expires_at', true ) ); }
}
add_action( 'manage_staffswap_offer_posts_custom_column', 'staffswap_offer_admin_column_content', 10, 2 );

function staffswap_offer_admin_status_filter() {
	global $typenow;
	if ( 'staffswap_offer' !== $typenow ) { return; }
	$statuses = array( 'proposed', 'accepted', 'declined', 'countered', 'withdrawn', 'completed', 'cancelled', 'expired' );
	$selected = sanitize_key( wp_unslash( $_GET['staffswap_offer_status'] ?? '' ) );
	echo '<select name="staffswap_offer_status"><option value="">All statuses</option>';
	foreach ( $statuses as $status ) { echo '<option value="' . esc_attr( $status ) . '" ' . selected( $selected, $status, false ) . '>' . esc_html( ucfirst( $status ) ) . '</option>'; }
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'staffswap_offer_admin_status_filter' );

function staffswap_offer_admin_status_filter_query( $query ) {
	global $pagenow, $typenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow || 'staffswap_offer' !== $typenow || empty( $_GET['staffswap_offer_status'] ) ) { return; }
	$query->set( 'meta_key', '_staffswap_offer_status' );
	$query->set( 'meta_value', sanitize_key( wp_unslash( $_GET['staffswap_offer_status'] ) ) );
}
add_action( 'pre_get_posts', 'staffswap_offer_admin_status_filter_query' );

function staffswap_offer_form_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'listing' => get_the_ID() ), $atts, 'staffswap_offer_form' );
	$listing = get_post( absint( $atts['listing'] ) );
	if ( ! $listing || 'swap_listing' !== $listing->post_type || ! is_user_logged_in() || (int) $listing->post_author === get_current_user_id() ) {
		return '';
	}
	$sender_listings = get_posts( array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'author' => get_current_user_id(), 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );
	$notice = '';
	if ( isset( $_POST['staffswap_send_offer'] ) && check_admin_referer( 'staffswap_send_offer_' . $listing->ID, 'staffswap_offer_nonce' ) ) {
		$sender_listing_id = absint( $_POST['sender_listing'] ?? 0 );
		$sender_listing = get_post( $sender_listing_id );
		$effective_date = sanitize_text_field( wp_unslash( $_POST['effective_date'] ?? '' ) );
		if ( ! $sender_listing || 'swap_listing' !== $sender_listing->post_type || 'publish' !== $sender_listing->post_status || (int) $sender_listing->post_author !== get_current_user_id() ) {
			$notice = '<div class="notice"><p>Select one of your own published swap listings so the offer includes your current and requested details.</p></div>';
		} elseif ( ! $effective_date ) {
			$notice = '<div class="notice"><p>Please provide the proposed effective date.</p></div>';
		} else {
			$offer_id = wp_insert_post( array( 'post_type' => 'staffswap_offer', 'post_title' => 'Swap offer: ' . $listing->post_title, 'post_content' => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ), 'post_status' => 'publish', 'post_author' => get_current_user_id() ), true );
			if ( ! is_wp_error( $offer_id ) ) {
				update_post_meta( $offer_id, '_staffswap_offer_listing', $listing->ID );
				update_post_meta( $offer_id, '_staffswap_offer_sender_listing', $sender_listing->ID );
				update_post_meta( $offer_id, '_staffswap_offer_recipient', $listing->post_author );
				update_post_meta( $offer_id, '_staffswap_offer_effective_date', $effective_date );
				$expires_at = sanitize_text_field( wp_unslash( $_POST['expires_at'] ?? '' ) );
				update_post_meta( $offer_id, '_staffswap_offer_expires_at', $expires_at && strtotime( $expires_at ) >= strtotime( 'today' ) ? $expires_at : gmdate( 'Y-m-d', strtotime( '+14 days' ) ) );
				update_post_meta( $offer_id, '_staffswap_offer_housing', isset( $_POST['housing_handover'] ) ? 'handover' : 'independent' );
				update_post_meta( $offer_id, '_staffswap_offer_status', 'proposed' );
				staffswap_offer_log_status( $offer_id, 'proposed' );
				if ( ! staffswap_generate_offer_agreement_document( $offer_id ) ) {
					error_log( 'StaffSwap offer document generation failed for offer ' . absint( $offer_id ) . '.' );
					$notice = '<div class="notice"><p>Offer sent, but its document could not be generated. You can retry from the Offers page.</p></div>';
				} else {
					$notice = '<div class="notice"><p>Swap offer sent. Its document is available to both parties in Offers.</p></div>';
				}
				staffswap_notify_user( (int) $listing->post_author, 'New swap offer for ' . $listing->post_title, wp_get_current_user()->display_name . ' sent a formal swap offer for your listing "' . $listing->post_title . '" with a proposed effective date of ' . $effective_date . '.' . "\n\n" . 'Review it here: ' . home_url( '/offers/' ), 'offer' );
			}
		}
	}
	ob_start(); echo $notice; ?><section class="panel" style="margin-top:16px"><h2>Send a formal swap offer</h2><?php if ( ! $sender_listings ) : ?><p class="muted">Publish one of your own swap listings before sending an offer. The listing will be included with the other member’s details in the offer documents.</p><a class="button button--outline" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Create a swap listing</a><?php else : ?><form method="post"><div class="field"><label for="sender_listing">Your swap listing</label><select id="sender_listing" name="sender_listing" required><option value="">Select the listing you want to exchange</option><?php foreach ( $sender_listings as $sender_listing_option ) : ?><option value="<?php echo esc_attr( $sender_listing_option->ID ); ?>" <?php selected( absint( $_POST['sender_listing'] ?? 0 ), $sender_listing_option->ID ); ?>><?php echo esc_html( get_the_title( $sender_listing_option ) . ' — ' . get_post_meta( $sender_listing_option->ID, '_staffswap_current_location', true ) . ' to ' . get_post_meta( $sender_listing_option->ID, '_staffswap_desired_location', true ) ); ?></option><?php endforeach; ?></select><small class="muted">Both your listing and the listing you are responding to will appear in the offer.</small></div><div class="field"><label for="effective_date">Proposed effective date</label><input id="effective_date" name="effective_date" type="date" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_POST['effective_date'] ?? '' ) ) ); ?>" required></div><div class="field"><label for="expires_at">Offer expires on</label><input id="expires_at" name="expires_at" type="date" min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>"></div><label class="check"><input name="housing_handover" type="checkbox" value="1"> Include staff housing handover</label><div class="field"><label for="offer_notes">Notes or contingencies</label><textarea id="offer_notes" name="notes" rows="3"><?php echo esc_textarea( wp_unslash( $_POST['notes'] ?? '' ) ); ?></textarea></div><?php wp_nonce_field( 'staffswap_send_offer_' . $listing->ID, 'staffswap_offer_nonce' ); ?><input type="submit" name="staffswap_send_offer" value="Submit offer"></form><?php endif; ?></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_offer_form', 'staffswap_offer_form_shortcode' );

function staffswap_offer_current_status( $offer_id ) {
	$status = get_post_meta( $offer_id, '_staffswap_offer_status', true );
	$expires_at = get_post_meta( $offer_id, '_staffswap_offer_expires_at', true );
	if ( 'proposed' === $status && $expires_at && strtotime( $expires_at . ' 23:59:59 UTC' ) < time() ) {
		update_post_meta( $offer_id, '_staffswap_offer_status', 'expired' );
		return 'expired';
	}
	return $status ?: 'proposed';
}

function staffswap_offer_action() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( isset( $_POST['staffswap_generate_agreement'] ) ) {
		$offer_id = absint( $_POST['offer_id'] ?? 0 );
		if ( $offer_id && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_offer_action_nonce'] ?? '' ) ), 'staffswap_offer_action_' . $offer_id ) ) {
			$offer = get_post( $offer_id );
			if ( $offer && 'staffswap_offer' === $offer->post_type && ( (int) $offer->post_author === get_current_user_id() || (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true ) === get_current_user_id() || current_user_can( 'manage_options' ) ) ) {
				if ( ! staffswap_generate_offer_agreement_document( $offer_id ) ) {
					error_log( 'StaffSwap offer document generation failed for offer ' . $offer_id . '.' );
				}
				if ( in_array( staffswap_offer_current_status( $offer_id ), array( 'accepted', 'completed' ), true ) ) {
					staffswap_generate_offer_letters( $offer_id );
				}
			}
		}
		return;
	}

	if ( ! isset( $_POST['staffswap_offer_action'] ) ) {
		return;
	}
	if ( function_exists( 'staffswap_has_active_membership' ) && ! staffswap_has_active_membership() ) {
		return;
	}
	$offer_id = absint( $_POST['offer_id'] ?? 0 );
	$action = sanitize_key( wp_unslash( $_POST['staffswap_offer_action'] ) );
	if ( ! $offer_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_offer_action_nonce'] ?? '' ) ), 'staffswap_offer_action_' . $offer_id ) || ! in_array( $action, array( 'accepted', 'declined', 'countered', 'withdrawn', 'completed', 'cancelled' ), true ) ) {
		return;
	}
	$offer = get_post( $offer_id );
	$is_recipient = (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true ) === get_current_user_id();
	$is_sender = $offer && (int) $offer->post_author === get_current_user_id();
	$status = staffswap_offer_current_status( $offer_id );
	$can_close = in_array( $action, array( 'completed', 'cancelled' ), true ) && 'accepted' === $status && ( $is_sender || $is_recipient );
	$can_withdraw = 'withdrawn' === $action && 'proposed' === $status && $is_sender;
	$can_respond = in_array( $action, array( 'accepted', 'declined', 'countered' ), true ) && 'proposed' === $status && $is_recipient;
	if ( ! $offer || ! ( $can_close || $can_withdraw || $can_respond ) ) {
		return;
	}
	update_post_meta( $offer_id, '_staffswap_offer_status', $action );
	$reason = sanitize_textarea_field( wp_unslash( $_POST['status_reason'] ?? '' ) );
	staffswap_offer_log_status( $offer_id, $action, get_current_user_id(), $reason );
	$listing_id = (int) get_post_meta( $offer_id, '_staffswap_offer_listing', true );
	$other_party = $is_recipient ? (int) $offer->post_author : (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true );
	if ( $other_party ) { staffswap_notify_user( $other_party, 'Swap offer update: ' . get_the_title( $listing_id ), 'Your swap offer for "' . get_the_title( $listing_id ) . '" is now marked as ' . $action . '.' . ( $reason ? ' Note: ' . $reason : '' ) . "\n\n" . 'View it here: ' . home_url( '/offers/' ), 'offer' ); }
	if ( in_array( $action, array( 'accepted', 'completed' ), true ) ) {
		$admin_email = get_option( 'admin_email' );
		if ( $admin_email ) { wp_mail( $admin_email, '[' . get_bloginfo( 'name' ) . '] Swap offer ' . $action, 'A swap offer for "' . get_the_title( $listing_id ) . '" between ' . get_the_author_meta( 'display_name', $offer->post_author ) . ' and ' . get_the_author_meta( 'display_name', (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true ) ) . ' is now ' . $action . '. Review it in Swap Offers.' ); }
		if ( ! staffswap_generate_offer_agreement_document( $offer_id ) ) {
			error_log( 'StaffSwap offer agreement generation failed for offer ' . $offer_id . '.' );
		}
		if ( ! staffswap_generate_offer_letters( $offer_id ) ) {
			error_log( 'StaffSwap transfer letter generation failed for offer ' . $offer_id . '.' );
		}
	}
	if ( 'accepted' === $action && function_exists( 'staffswap_db_table' ) ) {
		global $wpdb;
		$author_listing_ids = get_posts( array( 'post_type' => 'swap_listing', 'post_author' => get_current_user_id(), 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1 ) );
		foreach ( $author_listing_ids as $author_listing_id ) {
			$wpdb->update( staffswap_db_table( 'matches' ), array( 'status' => 'locked', 'updated_at' => current_time( 'mysql', true ) ), array( 'listing_id' => $author_listing_id, 'candidate_listing_id' => $listing_id ), array( '%s', '%s' ), array( '%d', '%d' ) );
			$wpdb->update( staffswap_db_table( 'matches' ), array( 'status' => 'locked', 'updated_at' => current_time( 'mysql', true ) ), array( 'listing_id' => $listing_id, 'candidate_listing_id' => $author_listing_id ), array( '%s', '%s' ), array( '%d', '%d' ) );
		}
	}
	if ( 'countered' === $action ) {
		$counter_recipient_listing_id = staffswap_offer_sender_listing_id( $offer_id );
		if ( ! $counter_recipient_listing_id ) {
			error_log( 'StaffSwap counter-offer was not created because the original sender has no published swap listing. Offer ' . $offer_id . '.' );
			return;
		}
		$counter_sender_listing_id = absint( get_post_meta( $offer_id, '_staffswap_offer_listing', true ) );
		$counter_id = wp_insert_post( array( 'post_type' => 'staffswap_offer', 'post_title' => 'Counter-offer: ' . get_the_title( $listing_id ), 'post_content' => sanitize_textarea_field( wp_unslash( $_POST['counter_notes'] ?? '' ) ), 'post_status' => 'publish', 'post_author' => get_current_user_id() ) );
		if ( $counter_id ) {
			update_post_meta( $counter_id, '_staffswap_offer_listing', $counter_recipient_listing_id );
			update_post_meta( $counter_id, '_staffswap_offer_sender_listing', $counter_sender_listing_id );
			update_post_meta( $counter_id, '_staffswap_offer_recipient', get_post_field( 'post_author', $counter_recipient_listing_id ) );
			update_post_meta( $counter_id, '_staffswap_offer_effective_date', sanitize_text_field( wp_unslash( $_POST['counter_effective_date'] ?? '' ) ) );
			update_post_meta( $counter_id, '_staffswap_offer_expires_at', gmdate( 'Y-m-d', strtotime( '+14 days' ) ) );
			update_post_meta( $counter_id, '_staffswap_offer_parent', $offer_id );
			update_post_meta( $counter_id, '_staffswap_offer_status', 'proposed' );
			staffswap_offer_log_status( $counter_id, 'proposed' );
			if ( ! staffswap_generate_offer_agreement_document( $counter_id ) ) {
				error_log( 'StaffSwap counter-offer document generation failed for offer ' . absint( $counter_id ) . '.' );
			}
			staffswap_notify_user( (int) get_post_field( 'post_author', $offer_id ), 'Counter-offer received for ' . get_the_title( $listing_id ), 'You received a counter-offer for "' . get_the_title( $listing_id ) . '". Review it here: ' . home_url( '/offers/' ), 'offer' );
		}
	}
	if ( function_exists( 'staffswap_record_event' ) ) { staffswap_record_event( 'offer_' . $action, $offer_id, array( 'listing_id' => $listing_id ), get_current_user_id() ); }
}
add_action( 'init', 'staffswap_offer_action' );

// Proactively expires overdue proposed offers and lets both parties know, instead of relying on someone opening the page.
function staffswap_offer_expiry_sweep() {
	$expired = get_posts( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => array( 'relation' => 'AND', array( 'key' => '_staffswap_offer_status', 'value' => 'proposed' ), array( 'key' => '_staffswap_offer_expires_at', 'value' => gmdate( 'Y-m-d' ), 'compare' => '<', 'type' => 'DATE' ) ) ) );
	foreach ( $expired as $offer_id ) {
		update_post_meta( $offer_id, '_staffswap_offer_status', 'expired' );
		staffswap_offer_log_status( $offer_id, 'expired' );
		$listing_id = (int) get_post_meta( $offer_id, '_staffswap_offer_listing', true );
		if ( function_exists( 'staffswap_notify_user' ) ) {
			staffswap_notify_user( (int) get_post_field( 'post_author', $offer_id ), 'Swap offer expired', 'Your swap offer for "' . get_the_title( $listing_id ) . '" expired without a response.' . "\n\n" . 'View it here: ' . home_url( '/offers/' ), 'offer' );
			staffswap_notify_user( (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true ), 'Swap offer expired', 'A swap offer for "' . get_the_title( $listing_id ) . '" expired without a response.' . "\n\n" . 'View it here: ' . home_url( '/offers/' ), 'offer' );
		}
	}
}
add_action( 'staffswap_saved_search_alerts', 'staffswap_offer_expiry_sweep' );

function staffswap_offers_shortcode() {
	if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to view offers.</p></div>'; }
	$offers = new WP_Query( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'posts_per_page' => 30, 'meta_key' => '_staffswap_offer_recipient', 'meta_value' => get_current_user_id() ) );
	ob_start(); ?><section class="content-form"><div class="page-heading"><div><p class="eyebrow">FORMAL AGREEMENTS</p><h1>Offers &amp; swaps</h1></div></div><div class="panel"><h2>Incoming offers</h2><?php if ( $offers->have_posts() ) : while ( $offers->have_posts() ) : $offers->the_post(); $offer_id = get_the_ID(); $status = get_post_meta( $offer_id, '_staffswap_offer_status', true ); ?><article class="message-row"><strong><?php the_title(); ?></strong><p>Effective date: <?php echo esc_html( get_post_meta( $offer_id, '_staffswap_offer_effective_date', true ) ); ?>. Housing: <?php echo esc_html( get_post_meta( $offer_id, '_staffswap_offer_housing', true ) ); ?>.</p><p><?php echo esc_html( get_the_content() ); ?></p><p>Status: <strong><?php echo esc_html( ucfirst( $status ) ); ?></strong></p><?php if ( 'proposed' === $status ) : ?><form method="post"><input type="hidden" name="offer_id" value="<?php echo esc_attr( $offer_id ); ?>"><input type="date" name="counter_effective_date" aria-label="Counter-offer effective date"><textarea name="counter_notes" rows="2" placeholder="Counter-offer notes"></textarea><?php wp_nonce_field( 'staffswap_offer_action_' . $offer_id, 'staffswap_offer_action_nonce' ); ?><button type="submit" name="staffswap_offer_action" value="accepted">Accept</button> <button type="submit" name="staffswap_offer_action" value="declined">Decline</button> <button type="submit" name="staffswap_offer_action" value="countered">Counter-offer</button></form><?php endif; ?></article><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">No incoming offers yet.</p><?php endif; ?></div></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_offers', 'staffswap_offers_shortcode' );

function staffswap_offer_item( $offer, $can_respond = false ) {
	$offer_id = $offer->ID;
	$status = staffswap_offer_current_status( $offer_id );
	$expires_at = get_post_meta( $offer_id, '_staffswap_offer_expires_at', true );
	$parent_id = absint( get_post_meta( $offer_id, '_staffswap_offer_parent', true ) );
	$effective = get_post_meta( $offer_id, '_staffswap_offer_effective_date', true ) ?: 'Not set';
	$housing = get_post_meta( $offer_id, '_staffswap_offer_housing', true ) ?: 'Not specified';
	$sender = staffswap_offer_listing_details( staffswap_offer_sender_listing_id( $offer_id ) );
	$recipient = staffswap_offer_listing_details( absint( get_post_meta( $offer_id, '_staffswap_offer_listing', true ) ) );
	$document_url = staffswap_offer_document_url( $offer_id );
	$letter_url = staffswap_offer_letter_url( $offer_id, get_current_user_id() );
	$status_log = array_filter( (array) get_post_meta( $offer_id, '_staffswap_offer_status_log', true ) );
	$nonce = wp_nonce_field( 'staffswap_offer_action_' . $offer_id, 'staffswap_offer_action_nonce', true, false );
	$hidden = '<input type="hidden" name="offer_id" value="' . esc_attr( $offer_id ) . '">';
	ob_start(); ?>
	<article class="offer-card offer-card--<?php echo esc_attr( $status ); ?>">
		<header class="offer-card__head">
			<div><p class="offer-card__ref">SS-<?php echo esc_html( $offer_id ); ?></p><h3><?php echo esc_html( get_the_title( $offer_id ) ); ?></h3></div>
			<span class="offer-status offer-status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucfirst( $status ) ); ?></span>
		</header>
		<?php if ( $sender && $recipient ) : ?>
			<div class="offer-route">
				<div class="offer-route__party"><small><?php echo esc_html( $sender['name'] ); ?></small><strong><?php echo esc_html( $sender['current_location'] ); ?></strong><span><?php echo esc_html( $sender['current_employer'] ); ?></span></div>
				<span class="offer-route__swap" aria-hidden="true">&#8644;</span>
				<div class="offer-route__party"><small><?php echo esc_html( $recipient['name'] ); ?></small><strong><?php echo esc_html( $recipient['current_location'] ); ?></strong><span><?php echo esc_html( $recipient['current_employer'] ); ?></span></div>
			</div>
		<?php endif; ?>
		<dl class="offer-facts">
			<div><dt>Effective</dt><dd><?php echo esc_html( $effective ); ?></dd></div>
			<div><dt>Housing</dt><dd><?php echo esc_html( $housing ); ?></dd></div>
			<?php if ( $expires_at ) : ?><div><dt>Expires</dt><dd><?php echo esc_html( $expires_at ); ?></dd></div><?php endif; ?>
		</dl>
		<?php if ( $parent_id ) : ?><p class="muted offer-card__note">Counter-offer to: <?php echo esc_html( get_the_title( $parent_id ) ); ?></p><?php endif; ?>
		<?php if ( '' !== trim( $offer->post_content ) ) : ?><p class="offer-card__message"><?php echo esc_html( $offer->post_content ); ?></p><?php endif; ?>
		<div class="offer-docs">
			<?php if ( $document_url ) : ?><a class="button button--outline" href="<?php echo esc_url( $document_url ); ?>" target="_blank" rel="noopener">View offer document</a>
			<?php else : ?><form method="post"><?php echo $hidden . $nonce; ?><button type="submit" name="staffswap_generate_agreement" value="1">Generate offer document</button></form><?php endif; ?>
			<?php if ( $letter_url ) : ?><a class="button button--outline" href="<?php echo esc_url( $letter_url ); ?>" target="_blank" rel="noopener">Your transfer letter</a><?php endif; ?>
		</div>
		<?php if ( $status_log ) : ?>
			<details class="offer-history"><summary>History (<?php echo esc_html( count( $status_log ) ); ?>)</summary>
			<ul class="offer-timeline"><?php foreach ( $status_log as $entry ) : ?><li><strong><?php echo esc_html( ucfirst( $entry['status'] ?? '' ) ); ?></strong> <span class="muted"><?php echo esc_html( mysql2date( 'j M Y, g:ia', $entry['at'] ?? '' ) ); ?></span><?php if ( ! empty( $entry['reason'] ) ) : ?><br><span class="muted"><?php echo esc_html( $entry['reason'] ); ?></span><?php endif; ?></li><?php endforeach; ?></ul></details>
		<?php endif; ?>
		<?php if ( 'proposed' === $status && $can_respond ) : ?>
			<form method="post" class="offer-actions"><?php echo $hidden . $nonce; ?>
				<div class="offer-actions__buttons"><button type="submit" name="staffswap_offer_action" value="accepted">Accept</button><button type="submit" name="staffswap_offer_action" value="declined" class="button--ghost">Decline</button></div>
				<details class="offer-actions__more"><summary>Counter-offer or add a reason</summary>
					<input type="date" name="counter_effective_date" aria-label="Counter-offer effective date">
					<textarea name="counter_notes" rows="2" placeholder="Counter-offer notes"></textarea>
					<textarea name="status_reason" rows="2" placeholder="Reason if declining (optional)"></textarea>
					<button type="submit" name="staffswap_offer_action" value="countered" class="button--ghost">Send counter-offer</button>
				</details>
			</form>
		<?php elseif ( 'proposed' === $status ) : ?>
			<form method="post" class="offer-actions"><?php echo $hidden . $nonce; ?>
				<details class="offer-actions__more"><summary>Withdraw this offer</summary><textarea name="status_reason" rows="2" placeholder="Reason for withdrawing (optional)"></textarea><button type="submit" name="staffswap_offer_action" value="withdrawn" class="button--ghost">Withdraw offer</button></details>
			</form>
		<?php elseif ( 'accepted' === $status ) : ?>
			<form method="post" class="offer-actions"><?php echo $hidden . $nonce; ?>
				<textarea name="status_reason" rows="2" placeholder="Note (optional)"></textarea>
				<div class="offer-actions__buttons"><button type="submit" name="staffswap_offer_action" value="completed">Mark swap completed</button><button type="submit" name="staffswap_offer_action" value="cancelled" class="button--ghost">Cancel agreement</button></div>
			</form>
		<?php endif; ?>
	</article>
	<?php return ob_get_clean();
}

function staffswap_offers_workspace_shortcode() {
	if ( ! is_user_logged_in() ) {
		return '<div class="panel"><p>Please sign in to view offers.</p></div>';
	}
	$user_id = get_current_user_id();
	$incoming = get_posts( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'posts_per_page' => 30, 'meta_key' => '_staffswap_offer_recipient', 'meta_value' => $user_id, 'orderby' => 'date', 'order' => 'DESC' ) );
	$sent = get_posts( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'posts_per_page' => 30, 'author' => $user_id, 'orderby' => 'date', 'order' => 'DESC' ) );
	ob_start();
	?>
	<section class="content-form">
		<div class="page-heading"><div><p class="eyebrow">FORMAL AGREEMENTS</p><h1>Offers &amp; swaps</h1></div></div>
		<div class="offers-summary"><span><b><?php echo esc_html( count( $incoming ) ); ?></b> incoming</span><span><b><?php echo esc_html( count( $sent ) ); ?></b> sent</span></div>
		<div class="panel offers-panel">
			<h2>Incoming offers</h2>
			<?php if ( $incoming ) : foreach ( $incoming as $offer ) : ?>
				<?php echo staffswap_offer_item( $offer, true ); ?>
			<?php endforeach; else : ?>
				<p class="muted">No incoming offers yet.</p>
			<?php endif; ?>
		</div>
		<div class="panel offers-panel">
			<h2>Sent offers</h2>
			<?php if ( $sent ) : foreach ( $sent as $offer ) : ?>
				<?php echo staffswap_offer_item( $offer ); ?>
			<?php endforeach; else : ?>
				<p class="muted">You have not sent an offer yet.</p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
remove_shortcode( 'staffswap_offers' );
add_shortcode( 'staffswap_offers', 'staffswap_offers_workspace_shortcode' );

function staffswap_offers_page() {
	if ( ! get_page_by_path( 'offers' ) ) {
		wp_insert_post( array( 'post_title' => 'Offers & Swaps', 'post_name' => 'offers', 'post_content' => '[staffswap_offers]', 'post_status' => 'publish', 'post_type' => 'page' ) );
	}
}
register_activation_hook( __FILE__, 'staffswap_offers_page' );
add_action( 'admin_init', 'staffswap_offers_page' );

function staffswap_membership_gate_shortcode( $shortcode, $atts, $action ) {
	if ( is_user_logged_in() && function_exists( 'staffswap_has_active_membership' ) && ! staffswap_has_active_membership() ) {
		return function_exists( 'staffswap_membership_required_notice' ) ? staffswap_membership_required_notice( $action ) : '<div class="panel"><h2>Membership required</h2><p>Activate your membership to ' . esc_html( $action ) . '.</p></div>';
	}
	return 'staffswap_contact' === $shortcode ? staffswap_contact_form_shortcode( $atts ) : staffswap_offer_form_shortcode( $atts );
}

function staffswap_gated_contact_shortcode( $atts ) { return staffswap_membership_gate_shortcode( 'staffswap_contact', $atts, 'contact a swap partner' ); }
function staffswap_gated_offer_form_shortcode( $atts ) { return staffswap_membership_gate_shortcode( 'staffswap_offer_form', $atts, 'send a formal swap offer' ); }
remove_shortcode( 'staffswap_contact' );
add_shortcode( 'staffswap_contact', 'staffswap_gated_contact_shortcode' );
remove_shortcode( 'staffswap_offer_form' );
add_shortcode( 'staffswap_offer_form', 'staffswap_gated_offer_form_shortcode' );