<?php
/**
 * Plugin Name: StaffSwap Messaging
 * Description: Private member-to-member conversation requests for swap listings.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_message_post_type() { register_post_type( 'staff_message', array( 'labels' => array( 'name' => 'Messages', 'singular_name' => 'Message' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=swap_listing', 'supports' => array( 'title', 'editor', 'author' ), 'capability_type' => 'post' ) ); }
add_action( 'init', 'staffswap_message_post_type' );
function staffswap_create_messages_page() { if ( ! get_page_by_path( 'messages' ) ) { wp_insert_post( array( 'post_title' => 'Messages', 'post_name' => 'messages', 'post_content' => '[staffswap_inbox]', 'post_status' => 'publish', 'post_type' => 'page' ) ); } }
register_activation_hook( __FILE__, function() { staffswap_message_post_type(); staffswap_create_messages_page(); flush_rewrite_rules(); } ); register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
function staffswap_contact_form_shortcode( $atts ) { $atts = shortcode_atts( array( 'listing' => get_the_ID() ), $atts, 'staffswap_contact' ); if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to contact this member.</p><a class="button button--primary" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Sign in</a></div>'; } $notice = ''; if ( isset( $_POST['staffswap_send_message'] ) && check_admin_referer( 'staffswap_send_message', 'staffswap_message_nonce' ) ) { $listing = get_post( (int) $atts['listing'] ); $message_text = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ); if ( $listing && 'swap_listing' === $listing->post_type && 'publish' === $listing->post_status && (int) $listing->post_author !== get_current_user_id() && $message_text ) { $message_id = wp_insert_post( array( 'post_type' => 'staff_message', 'post_title' => 'Message about: ' . $listing->post_title, 'post_content' => $message_text, 'post_status' => 'publish', 'post_author' => get_current_user_id() ), true ); if ( ! is_wp_error( $message_id ) ) { update_post_meta( $message_id, '_staffswap_recipient', (int) $listing->post_author ); update_post_meta( $message_id, '_staffswap_listing', (int) $listing->ID ); update_post_meta( $message_id, '_staffswap_read', '0' ); $notice = '<div class="notice"><div><h2>Message sent</h2><p class="muted">Your message has been sent to the listing owner.</p></div></div>'; } } } ob_start(); echo $notice; ?><div class="panel"><h2>Contact this professional</h2><form method="post"><div class="field"><label for="message">Your message</label><textarea id="message" name="message" rows="5" required placeholder="Introduce yourself and explain why this swap could work..."></textarea></div><?php wp_nonce_field( 'staffswap_send_message', 'staffswap_message_nonce' ); ?><input type="submit" name="staffswap_send_message" value="Send message"></form></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_contact', 'staffswap_contact_form_shortcode' );

function staffswap_inbox_shortcode() { if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to view your messages.</p></div>'; } $user_id = get_current_user_id(); $received = new WP_Query( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'posts_per_page' => 30, 'meta_key' => '_staffswap_recipient', 'meta_value' => $user_id ) ); $sent = new WP_Query( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'author' => $user_id, 'posts_per_page' => 30 ) ); ob_start(); ?><div class="message-inbox"><div class="page-heading"><div><p class="eyebrow">PRIVATE CONVERSATIONS</p><h1>Your messages</h1><p class="muted">Connect with potential exchange partners before you make a move.</p></div></div><section class="panel"><h2>Received</h2><?php if ( $received->have_posts() ) : while ( $received->have_posts() ) : $received->the_post(); ?><article class="message-row"><strong><?php the_title(); ?></strong><p><?php echo esc_html( wp_trim_words( get_the_content(), 22 ) ); ?></p><small class="muted">From <?php echo esc_html( get_the_author() ); ?></small></article><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">No received messages yet.</p><?php endif; ?></section><section class="panel" style="margin-top:16px"><h2>Sent</h2><?php if ( $sent->have_posts() ) : while ( $sent->have_posts() ) : $sent->the_post(); ?><article class="message-row"><strong><?php the_title(); ?></strong><p><?php echo esc_html( wp_trim_words( get_the_content(), 22 ) ); ?></p><small class="muted">Sent <?php echo esc_html( get_the_date() ); ?></small></article><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">No sent messages yet.</p><?php endif; ?></section></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_inbox', 'staffswap_inbox_shortcode' );

function staffswap_unread_message_count( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
	if ( ! $user_id ) { return 0; }
	return count( get_posts( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_query' => array( array( 'key' => '_staffswap_recipient', 'value' => $user_id, 'compare' => '=' ), array( 'key' => '_staffswap_read', 'value' => '0', 'compare' => '=' ) ) ) ) );
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
		if ( $listing_id && $recipient && $reply && get_post_type( $listing_id ) === 'swap_listing' && $recipient !== $user_id ) { $reply_id = wp_insert_post( array( 'post_type' => 'staff_message', 'post_title' => 'Message about: ' . get_the_title( $listing_id ), 'post_content' => $reply, 'post_status' => 'publish', 'post_author' => $user_id ) ); if ( $reply_id ) { update_post_meta( $reply_id, '_staffswap_recipient', $recipient ); update_post_meta( $reply_id, '_staffswap_listing', $listing_id ); update_post_meta( $reply_id, '_staffswap_read', '0' ); } }
	}
	$received = get_posts( array( 'post_type' => 'staff_message', 'post_status' => 'publish', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => array( array( 'key' => '_staffswap_recipient', 'value' => $user_id ) ) ) );
	$groups = array(); foreach ( $received as $message ) { $listing_id = absint( get_post_meta( $message->ID, '_staffswap_listing', true ) ); $groups[ $listing_id ?: $message->ID ][] = $message; }
	ob_start(); ?><div class="message-inbox"><div class="page-heading"><div><p class="eyebrow">PRIVATE CONVERSATIONS</p><h1>Your messages</h1><p class="muted">Connect with potential exchange partners before you make a move.</p></div></div><?php if ( $groups ) : ?><div class="message-conversations"><?php foreach ( $groups as $listing_id => $messages ) : $latest = $messages[0]; $sender_id = (int) $latest->post_author; $unread = false; foreach ( $messages as $message ) { if ( '0' === get_post_meta( $message->ID, '_staffswap_read', true ) ) { $unread = true; break; } } ?><section class="panel message-conversation <?php echo $unread ? 'is-unread' : ''; ?>"><header><div><p class="eyebrow"><?php echo $listing_id && get_post( $listing_id ) ? esc_html( get_the_title( $listing_id ) ) : 'General conversation'; ?></p><h2><?php echo esc_html( get_the_author_meta( 'display_name', $sender_id ) ); ?></h2></div><span class="message-count"><?php echo esc_html( count( $messages ) ); ?> messages</span></header><?php foreach ( $messages as $message ) : ?><article class="message-row <?php echo '0' === get_post_meta( $message->ID, '_staffswap_read', true ) ? 'is-unread' : ''; ?>"><strong><?php echo esc_html( get_the_author_meta( 'display_name', $message->post_author ) ); ?></strong><p><?php echo esc_html( get_the_content( null, false, $message ) ); ?></p><small class="muted"><?php echo esc_html( get_the_date( '', $message ) ); ?></small><?php if ( '0' === get_post_meta( $message->ID, '_staffswap_read', true ) ) : ?><form method="post" class="message-read-form"><input type="hidden" name="message_id" value="<?php echo esc_attr( $message->ID ); ?>"><?php wp_nonce_field( 'staffswap_mark_message_' . $message->ID, 'staffswap_message_read_nonce' ); ?><button type="submit" name="staffswap_mark_message_read" class="button button--outline">Mark as read</button></form><?php endif; ?></article><?php endforeach; ?><?php if ( $listing_id && $sender_id ) : ?><form method="post" class="message-reply"><label for="reply-<?php echo esc_attr( $listing_id ); ?>">Reply</label><textarea id="reply-<?php echo esc_attr( $listing_id ); ?>" name="reply" rows="2" required></textarea><input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing_id ); ?>"><input type="hidden" name="recipient_id" value="<?php echo esc_attr( $sender_id ); ?>"><?php wp_nonce_field( 'staffswap_reply_' . $listing_id, 'staffswap_reply_nonce' ); ?><button type="submit" name="staffswap_send_reply" class="button button--primary">Send reply</button></form><?php endif; ?></section><?php endforeach; ?></div><?php else : ?><section class="panel"><p class="muted">No received messages yet.</p></section><?php endif; ?></div><?php return ob_get_clean();
}
remove_shortcode( 'staffswap_inbox' );
add_shortcode( 'staffswap_inbox', 'staffswap_secure_inbox_shortcode' );

function staffswap_offer_post_type() {
	register_post_type( 'staffswap_offer', array( 'labels' => array( 'name' => 'Swap Offers', 'singular_name' => 'Swap Offer' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=swap_listing', 'supports' => array( 'title', 'editor', 'author' ), 'capability_type' => 'post' ) );
}
add_action( 'init', 'staffswap_offer_post_type' );

function staffswap_offer_form_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'listing' => get_the_ID() ), $atts, 'staffswap_offer_form' );
	$listing = get_post( absint( $atts['listing'] ) );
	if ( ! $listing || 'swap_listing' !== $listing->post_type || ! is_user_logged_in() || (int) $listing->post_author === get_current_user_id() ) {
		return '';
	}
	$notice = '';
	if ( isset( $_POST['staffswap_send_offer'] ) && check_admin_referer( 'staffswap_send_offer_' . $listing->ID, 'staffswap_offer_nonce' ) ) {
		$effective_date = sanitize_text_field( wp_unslash( $_POST['effective_date'] ?? '' ) );
		if ( ! $effective_date ) {
			$notice = '<div class="notice"><p>Please provide the proposed effective date.</p></div>';
		} else {
			$offer_id = wp_insert_post( array( 'post_type' => 'staffswap_offer', 'post_title' => 'Swap offer: ' . $listing->post_title, 'post_content' => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ), 'post_status' => 'publish', 'post_author' => get_current_user_id() ), true );
			if ( ! is_wp_error( $offer_id ) ) {
				update_post_meta( $offer_id, '_staffswap_offer_listing', $listing->ID );
				update_post_meta( $offer_id, '_staffswap_offer_recipient', $listing->post_author );
				update_post_meta( $offer_id, '_staffswap_offer_effective_date', $effective_date );
				update_post_meta( $offer_id, '_staffswap_offer_housing', isset( $_POST['housing_handover'] ) ? 'handover' : 'independent' );
				update_post_meta( $offer_id, '_staffswap_offer_status', 'proposed' );
				$notice = '<div class="notice"><p>Swap offer sent. You can track its status in Offers.</p></div>';
			}
		}
	}
	ob_start(); echo $notice; ?><section class="panel" style="margin-top:16px"><h2>Send a formal swap offer</h2><form method="post"><div class="field"><label for="effective_date">Proposed effective date</label><input id="effective_date" name="effective_date" type="date" required></div><label class="check"><input name="housing_handover" type="checkbox" value="1"> Include staff housing handover</label><div class="field"><label for="offer_notes">Notes or contingencies</label><textarea id="offer_notes" name="notes" rows="3"></textarea></div><?php wp_nonce_field( 'staffswap_send_offer_' . $listing->ID, 'staffswap_offer_nonce' ); ?><input type="submit" name="staffswap_send_offer" value="Submit offer"></form></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_offer_form', 'staffswap_offer_form_shortcode' );

function staffswap_offer_action() {
	if ( ! is_user_logged_in() || ! isset( $_POST['staffswap_offer_action'] ) ) {
		return;
	}
	$offer_id = absint( $_POST['offer_id'] ?? 0 );
	$action = sanitize_key( wp_unslash( $_POST['staffswap_offer_action'] ) );
	if ( ! $offer_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_offer_action_nonce'] ?? '' ) ), 'staffswap_offer_action_' . $offer_id ) || ! in_array( $action, array( 'accepted', 'declined', 'countered' ), true ) ) {
		return;
	}
	if ( (int) get_post_meta( $offer_id, '_staffswap_offer_recipient', true ) !== get_current_user_id() || 'proposed' !== get_post_meta( $offer_id, '_staffswap_offer_status', true ) ) {
		return;
	}
	update_post_meta( $offer_id, '_staffswap_offer_status', $action );
	$listing_id = (int) get_post_meta( $offer_id, '_staffswap_offer_listing', true );
	if ( 'accepted' === $action && function_exists( 'staffswap_db_table' ) ) {
		global $wpdb;
		$author_listing_ids = get_posts( array( 'post_type' => 'swap_listing', 'post_author' => get_current_user_id(), 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1 ) );
		foreach ( $author_listing_ids as $author_listing_id ) {
			$wpdb->update( staffswap_db_table( 'matches' ), array( 'status' => 'locked', 'updated_at' => current_time( 'mysql', true ) ), array( 'listing_id' => $author_listing_id, 'candidate_listing_id' => $listing_id ), array( '%s', '%s' ), array( '%d', '%d' ) );
			$wpdb->update( staffswap_db_table( 'matches' ), array( 'status' => 'locked', 'updated_at' => current_time( 'mysql', true ) ), array( 'listing_id' => $listing_id, 'candidate_listing_id' => $author_listing_id ), array( '%s', '%s' ), array( '%d', '%d' ) );
		}
	}
	if ( 'countered' === $action ) {
		$counter_id = wp_insert_post( array( 'post_type' => 'staffswap_offer', 'post_title' => 'Counter-offer: ' . get_the_title( $listing_id ), 'post_content' => sanitize_textarea_field( wp_unslash( $_POST['counter_notes'] ?? '' ) ), 'post_status' => 'publish', 'post_author' => get_current_user_id() ) );
		if ( $counter_id ) {
			update_post_meta( $counter_id, '_staffswap_offer_listing', $listing_id );
			update_post_meta( $counter_id, '_staffswap_offer_recipient', get_post_field( 'post_author', $offer_id ) );
			update_post_meta( $counter_id, '_staffswap_offer_effective_date', sanitize_text_field( wp_unslash( $_POST['counter_effective_date'] ?? '' ) ) );
			update_post_meta( $counter_id, '_staffswap_offer_status', 'proposed' );
		}
	}
}
add_action( 'init', 'staffswap_offer_action' );

function staffswap_offers_shortcode() {
	if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to view offers.</p></div>'; }
	$offers = new WP_Query( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'posts_per_page' => 30, 'meta_key' => '_staffswap_offer_recipient', 'meta_value' => get_current_user_id() ) );
	ob_start(); ?><section class="content-form"><div class="page-heading"><div><p class="eyebrow">FORMAL AGREEMENTS</p><h1>Offers &amp; swaps</h1></div></div><div class="panel"><h2>Incoming offers</h2><?php if ( $offers->have_posts() ) : while ( $offers->have_posts() ) : $offers->the_post(); $offer_id = get_the_ID(); $status = get_post_meta( $offer_id, '_staffswap_offer_status', true ); ?><article class="message-row"><strong><?php the_title(); ?></strong><p>Effective date: <?php echo esc_html( get_post_meta( $offer_id, '_staffswap_offer_effective_date', true ) ); ?>. Housing: <?php echo esc_html( get_post_meta( $offer_id, '_staffswap_offer_housing', true ) ); ?>.</p><p><?php echo esc_html( get_the_content() ); ?></p><p>Status: <strong><?php echo esc_html( ucfirst( $status ) ); ?></strong></p><?php if ( 'proposed' === $status ) : ?><form method="post"><input type="hidden" name="offer_id" value="<?php echo esc_attr( $offer_id ); ?>"><input type="date" name="counter_effective_date" aria-label="Counter-offer effective date"><textarea name="counter_notes" rows="2" placeholder="Counter-offer notes"></textarea><?php wp_nonce_field( 'staffswap_offer_action_' . $offer_id, 'staffswap_offer_action_nonce' ); ?><button type="submit" name="staffswap_offer_action" value="accepted">Accept</button> <button type="submit" name="staffswap_offer_action" value="declined">Decline</button> <button type="submit" name="staffswap_offer_action" value="countered">Counter-offer</button></form><?php endif; ?></article><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">No incoming offers yet.</p><?php endif; ?></div></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_offers', 'staffswap_offers_shortcode' );

function staffswap_offers_page() {
	if ( ! get_page_by_path( 'offers' ) ) {
		wp_insert_post( array( 'post_title' => 'Offers & Swaps', 'post_name' => 'offers', 'post_content' => '[staffswap_offers]', 'post_status' => 'publish', 'post_type' => 'page' ) );
	}
}
register_activation_hook( __FILE__, 'staffswap_offers_page' );
add_action( 'admin_init', 'staffswap_offers_page' );