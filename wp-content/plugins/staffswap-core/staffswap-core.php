<?php
/**
 * Plugin Name: StaffSwap Core
 * Description: Listings, profiles, filters, and front-end workflows for StaffExchangeHub.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: staffswap-core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'STAFFSWAP_DB_VERSION', '1.0.0' );
function staffswap_zambia_locations() {
    return array(
        'Central Province' => array( 'Kabwe', 'Kapiri Mposhi', 'Mkushi', 'Serenje', 'Chibombo', 'Mumbwa' ),
        'Copperbelt Province' => array( 'Ndola', 'Kitwe', 'Chingola', 'Mufulira', 'Luanshya', 'Kalulushi', 'Chililabombwe' ),
        'Eastern Province' => array( 'Chipata', 'Petauke', 'Katete', 'Lundazi', 'Nyimba' ),
        'Luapula Province' => array( 'Mansa', 'Samfya', 'Kawambwa', 'Nchelenge' ),
        'Lusaka Province' => array( 'Lusaka', 'Kafue', 'Chongwe', 'Luangwa' ),
        'Muchinga Province' => array( 'Chinsali', 'Mpika', 'Isoka', 'Nakonde' ),
        'Northern Province' => array( 'Kasama', 'Mbala', 'Mpulungu', 'Luwingu' ),
        'North-Western Province' => array( 'Solwezi', 'Zambezi', 'Mwinilunga', 'Kasempa' ),
        'Southern Province' => array( 'Livingstone', 'Choma', 'Mazabuka', 'Monze', 'Kalomo' ),
        'Western Province' => array( 'Mongu', 'Senanga', 'Kaoma', 'Lukulu', 'Kalabo' ),
    );
}
function staffswap_render_location_select( $id, $name, $selected = '', $required = true ) {
    $selected = (string) $selected;
    $html = '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . '><option value="">Select province &amp; town</option>';
    foreach ( staffswap_zambia_locations() as $province => $towns ) {
        $html .= '<optgroup label="' . esc_attr( $province ) . '">';
        foreach ( $towns as $town ) {
            $value = $town . ', ' . $province;
            $html .= '<option value="' . esc_attr( $value ) . '"' . selected( $selected, $value, false ) . '>' . esc_html( $value ) . '</option>';
        }
        $html .= '</optgroup>';
    }
    $html .= '</select>';
    return $html;
}
function staffswap_db_table( $name ) { global $wpdb; return $wpdb->prefix . 'staffswap_' . sanitize_key( $name ); }
function staffswap_db_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE " . staffswap_db_table( 'matches' ) . " ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, listing_id bigint(20) unsigned NOT NULL, candidate_listing_id bigint(20) unsigned NOT NULL, score decimal(5,2) NOT NULL DEFAULT 0, status varchar(20) NOT NULL DEFAULT 'suggested', created_at datetime NOT NULL, updated_at datetime NOT NULL, PRIMARY KEY (id), UNIQUE KEY listing_pair (listing_id,candidate_listing_id), KEY listing_id (listing_id), KEY candidate_listing_id (candidate_listing_id), KEY status (status) ) $charset;
    CREATE TABLE " . staffswap_db_table( 'saved_searches' ) . " ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, user_id bigint(20) unsigned NOT NULL, name varchar(190) NOT NULL, filters longtext NOT NULL, alert_frequency varchar(20) NOT NULL DEFAULT 'weekly', last_notified_at datetime NULL, created_at datetime NOT NULL, PRIMARY KEY (id), KEY user_id (user_id), KEY alert_frequency (alert_frequency) ) $charset;
    CREATE TABLE " . staffswap_db_table( 'events' ) . " ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, user_id bigint(20) unsigned NULL, event_type varchar(50) NOT NULL, object_id bigint(20) unsigned NULL, payload longtext NULL, created_at datetime NOT NULL, PRIMARY KEY (id), KEY user_id (user_id), KEY event_type (event_type), KEY object_id (object_id), KEY created_at (created_at) ) $charset;";
    dbDelta( $sql ); update_option( 'staffswap_db_version', STAFFSWAP_DB_VERSION );
}
function staffswap_db_maybe_upgrade() { if ( get_option( 'staffswap_db_version' ) !== STAFFSWAP_DB_VERSION ) { staffswap_db_install(); } }
add_action( 'admin_init', 'staffswap_db_maybe_upgrade' );

function staffswap_register_listing() {
    register_post_type( 'swap_listing', array(
        'labels' => array( 'name' => 'Swap Listings', 'singular_name' => 'Swap Listing', 'add_new_item' => 'Add Swap Listing' ),
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-randomize',
        'supports' => array( 'title', 'editor', 'author', 'thumbnail' ), 'rewrite' => array( 'slug' => 'swap' ), 'has_archive' => true,
    ) );
}
add_action( 'init', 'staffswap_register_listing' );
function staffswap_create_pages() {
    $pages = array(
        'swaps' => array( 'title' => 'Find Swaps', 'content' => '[staffswap_listings]' ),
        'create-swap' => array( 'title' => 'Create a Swap Post', 'content' => '[staffswap_create_form]' ),
        'search' => array( 'title' => 'Search Swaps', 'content' => '[staffswap_search]' ),
        'register' => array( 'title' => 'Create Your Account', 'content' => '[staffswap_register]' ),
        'sign-in' => array( 'title' => 'Sign In', 'content' => '[staffswap_login]' ),
        'my-profile' => array( 'title' => 'My Profile', 'content' => '[staffswap_dashboard]' ),
    );
    foreach ( $pages as $slug => $page ) {
        if ( ! get_page_by_path( $slug ) ) {
            wp_insert_post( array( 'post_title' => $page['title'], 'post_name' => $slug, 'post_content' => $page['content'], 'post_status' => 'publish', 'post_type' => 'page' ) );
        }
    }
}
register_activation_hook( __FILE__, function() { staffswap_register_listing(); staffswap_db_install(); staffswap_create_pages(); flush_rewrite_rules(); } );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

function staffswap_listing_fields() { return array( 'profession' => 'Profession', 'current_location' => 'Current Location', 'current_employer' => 'Current Employer', 'desired_location' => 'Desired Location', 'desired_employer' => 'Desired Employer', 'experience' => 'Experience (years)', 'match_score' => 'Match Score', 'housing' => 'Housing available', 'nearby_towns' => 'Open to nearby towns', 'relocation_support' => 'Relocation support', 'urgent' => 'Urgent listing', 'verified' => 'Verified professional', 'swap_reason' => 'Reason for swap request' ); }
function staffswap_swap_reasons() {
    return array(
        'family_reasons' => 'Family reasons',
        'spouse_partner_relocation' => 'Spouse or partner relocation',
        'closer_to_family' => 'Moving closer to family',
        'childcare_dependent_care' => 'Childcare or dependent care',
        'education_studies' => 'Education or studies',
        'career_development' => 'Career development',
        'professional_growth' => 'Professional growth',
        'better_work_environment' => 'Better work environment',
        'change_work_environment' => 'Change of work environment',
        'closer_to_home' => 'Moving closer to home',
        'reduce_commuting_distance' => 'Reduce commuting distance',
        'lower_cost_of_living' => 'Lower cost of living',
        'housing_accommodation' => 'Housing or accommodation',
        'personal_circumstances' => 'Personal circumstances',
        'returning_home_district_province' => 'Returning to home district/province',
        'urban_location' => 'Preference for urban location',
        'rural_location' => 'Preference for rural location',
        'new_experience' => 'Seeking new experience',
        'mutual_convenience' => 'Mutual convenience',
        'other' => 'Other',
    );
}
function staffswap_add_meta_box() { add_meta_box( 'staffswap_listing_details', 'Exchange Details', 'staffswap_meta_box', 'swap_listing', 'normal', 'high' ); }
add_action( 'add_meta_boxes', 'staffswap_add_meta_box' );
function staffswap_meta_box( $post ) {
    wp_nonce_field( 'staffswap_save_listing', 'staffswap_listing_nonce' );
    echo '<div class="staffswap-admin-fields">';
    foreach ( staffswap_listing_fields() as $key => $label ) {
        $value = get_post_meta( $post->ID, '_staffswap_' . $key, true );
        if ( 'swap_reason' === $key ) {
            echo '<p><label><strong>' . esc_html( $label ) . '</strong><br><select name="staffswap_swap_reason"><option value="">Select a reason</option>';
            foreach ( staffswap_swap_reasons() as $reason_key => $reason_label ) {
                echo '<option value="' . esc_attr( $reason_key ) . '" ' . selected( $value, $reason_key, false ) . '>' . esc_html( $reason_label ) . '</option>';
            }
            echo '</select></label></p>';
            continue;
        }
        $type = in_array( $key, array( 'housing', 'nearby_towns', 'relocation_support', 'urgent', 'verified' ), true ) ? 'checkbox' : 'text';
        echo '<p><label><strong>' . esc_html( $label ) . '</strong><br><input type="' . esc_attr( $type ) . '" name="staffswap_' . esc_attr( $key ) . '" value="' . esc_attr( $type === 'checkbox' ? '1' : $value ) . '" ' . checked( $value, '1', false ) . '></label></p>';
    }
    echo '</div>';
}
function staffswap_save_meta( $post_id ) {
    if ( ! isset( $_POST['staffswap_listing_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_listing_nonce'] ) ), 'staffswap_save_listing' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
    foreach ( staffswap_listing_fields() as $key => $label ) {
        $value = isset( $_POST['staffswap_' . $key] ) ? sanitize_text_field( wp_unslash( $_POST['staffswap_' . $key] ) ) : '';
        if ( 'swap_reason' === $key && ! array_key_exists( $value, staffswap_swap_reasons() ) ) { $value = ''; }
        update_post_meta( $post_id, '_staffswap_' . $key, $value );
    }
}
add_action( 'save_post_swap_listing', 'staffswap_save_meta' );

function staffswap_normalize_match_value( $value ) {
    $value = strtolower( remove_accents( sanitize_text_field( $value ) ) );
    return trim( preg_replace( '/[^a-z0-9]+/', ' ', $value ) );
}

function staffswap_delete_listing_matches( $listing_id ) {
    global $wpdb;
    $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . staffswap_db_table( 'matches' ) . ' WHERE listing_id = %d OR candidate_listing_id = %d', $listing_id, $listing_id ) );
}

function staffswap_member_declined_matches( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    return array_map( 'absint', (array) get_user_meta( $user_id, 'staffswap_declined_match_listings', true ) );
}

function staffswap_match_score( $listing_meta, $candidate_meta ) {
    $score = 0;
    if ( $listing_meta['profession'] && $listing_meta['profession'] === $candidate_meta['profession'] ) { $score += 35; }
    if ( $listing_meta['current_location'] && $listing_meta['current_location'] === $candidate_meta['desired_location'] ) { $score += 20; }
    if ( $listing_meta['desired_location'] && $listing_meta['desired_location'] === $candidate_meta['current_location'] ) { $score += 20; }
    if ( $listing_meta['current_employer'] && $listing_meta['current_employer'] === $candidate_meta['desired_employer'] && $listing_meta['desired_employer'] === $candidate_meta['current_employer'] ) { $score += 10; }
    if ( absint( $listing_meta['experience'] ) && abs( absint( $listing_meta['experience'] ) - absint( $candidate_meta['experience'] ) ) <= 3 ) { $score += 5; }
    if ( $listing_meta['housing'] && $candidate_meta['housing'] ) { $score += 3; }
    if ( $listing_meta['nearby_towns'] || $candidate_meta['nearby_towns'] ) { $score += 3; }
    if ( $listing_meta['verified'] && $candidate_meta['verified'] ) { $score += 4; }
    return $score;
}

function staffswap_refresh_listing_matches( $listing_id ) {
    global $wpdb;
    if ( 'publish' !== get_post_status( $listing_id ) ) {
        staffswap_delete_listing_matches( $listing_id );
        return;
    }
    $listing_meta = array();
    foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer', 'desired_employer', 'experience', 'housing', 'nearby_towns', 'verified' ) as $field ) {
        $listing_meta[ $field ] = staffswap_normalize_match_value( get_post_meta( $listing_id, '_staffswap_' . $field, true ) );
    }
    if ( ! $listing_meta['current_location'] || ! $listing_meta['desired_location'] || ! $listing_meta['profession'] ) {
        staffswap_delete_listing_matches( $listing_id );
        return;
    }
    staffswap_delete_listing_matches( $listing_id );
    $candidates = get_posts( array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'post__not_in' => array( $listing_id ), 'posts_per_page' => -1, 'fields' => 'ids' ) );
    $declined_listing_ids = staffswap_member_declined_matches( get_post_field( 'post_author', $listing_id ) );
    $now = current_time( 'mysql', true );
    foreach ( $candidates as $candidate_id ) {
        if ( (int) get_post_field( 'post_author', $listing_id ) === (int) get_post_field( 'post_author', $candidate_id ) || in_array( $candidate_id, $declined_listing_ids, true ) ) {
            continue;
        }
        $candidate_meta = array();
        foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer', 'desired_employer', 'experience', 'housing', 'nearby_towns', 'verified' ) as $field ) {
            $candidate_meta[ $field ] = staffswap_normalize_match_value( get_post_meta( $candidate_id, '_staffswap_' . $field, true ) );
        }
        $score = staffswap_match_score( $listing_meta, $candidate_meta );
        if ( $score < 60 ) {
            continue;
        }
        foreach ( array( array( $listing_id, $candidate_id ), array( $candidate_id, $listing_id ) ) as $pair ) {
            if ( in_array( $pair[1], staffswap_member_declined_matches( get_post_field( 'post_author', $pair[0] ) ), true ) ) {
                continue;
            }
            $wpdb->replace( staffswap_db_table( 'matches' ), array( 'listing_id' => $pair[0], 'candidate_listing_id' => $pair[1], 'score' => $score, 'status' => 'suggested', 'created_at' => $now, 'updated_at' => $now ), array( '%d', '%d', '%f', '%s', '%s', '%s' ) );
        }
    }
}

function staffswap_handle_listing_match_status( $new_status, $old_status, $post ) {
    if ( 'swap_listing' !== $post->post_type || wp_is_post_revision( $post->ID ) ) {
        return;
    }
    if ( 'publish' === $new_status ) {
        staffswap_refresh_listing_matches( $post->ID );
    } elseif ( 'publish' === $old_status ) {
        staffswap_delete_listing_matches( $post->ID );
    }
}
add_action( 'transition_post_status', 'staffswap_handle_listing_match_status', 20, 3 );
add_action( 'save_post_swap_listing', 'staffswap_refresh_listing_matches', 20 );
add_action( 'before_delete_post', function( $post_id ) { if ( 'swap_listing' === get_post_type( $post_id ) ) { staffswap_delete_listing_matches( $post_id ); } } );

function staffswap_listing_moderation_menu() {
    add_submenu_page( 'edit.php?post_type=swap_listing', 'Listing Moderation', 'Moderation Queue', 'manage_options', 'staffswap-listing-moderation', 'staffswap_listing_moderation_screen' );
}
add_action( 'admin_menu', 'staffswap_listing_moderation_menu' );

function staffswap_listing_moderation_action() {
    if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['staffswap_listing_moderation_action'] ) ) { return; }
    $listing_id = absint( $_POST['listing_id'] ?? 0 );
    $action = sanitize_key( wp_unslash( $_POST['staffswap_listing_moderation_action'] ) );
    if ( ! $listing_id || 'swap_listing' !== get_post_type( $listing_id ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_listing_moderation_nonce'] ?? '' ) ), 'staffswap_listing_moderation_' . $listing_id ) || ! in_array( $action, array( 'approved', 'rejected', 'changes_requested' ), true ) ) { return; }
    $reason = sanitize_textarea_field( wp_unslash( $_POST['review_reason'] ?? '' ) );
    update_post_meta( $listing_id, '_staffswap_review_state', $action );
    update_post_meta( $listing_id, '_staffswap_review_reason', $reason );
    update_post_meta( $listing_id, '_staffswap_reviewed_at', current_time( 'mysql', true ) );
    update_post_meta( $listing_id, '_staffswap_reviewed_by', get_current_user_id() );
    if ( 'approved' === $action ) { wp_update_post( array( 'ID' => $listing_id, 'post_status' => 'publish' ) ); }
    staffswap_record_event( 'listing_' . $action, $listing_id, array( 'reason' => $reason, 'reviewer_id' => get_current_user_id() ), get_current_user_id() );
    wp_safe_redirect( add_query_arg( array( 'post_type' => 'swap_listing', 'page' => 'staffswap-listing-moderation', 'updated' => '1' ), admin_url( 'edit.php' ) ) );
    exit;
}
add_action( 'admin_init', 'staffswap_listing_moderation_action' );

function staffswap_listing_moderation_screen() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $listings = get_posts( array( 'post_type' => 'swap_listing', 'post_status' => 'pending', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'ASC' ) );
    ?><div class="wrap"><h1>StaffSwap Listing Moderation</h1><?php if ( isset( $_GET['updated'] ) ) : ?><div class="notice notice-success is-dismissible"><p>Listing review recorded.</p></div><?php endif; ?><p>Approve verified, complete listings. Rejections and requested changes remain private to the author until corrected.</p><table class="widefat striped"><thead><tr><th>Listing</th><th>Author</th><th>Route</th><th>Review decision</th></tr></thead><tbody><?php if ( $listings ) : foreach ( $listings as $listing ) : ?><tr><td><strong><a href="<?php echo esc_url( get_edit_post_link( $listing->ID ) ); ?>"><?php echo esc_html( $listing->post_title ); ?></a></strong><br><?php echo esc_html( get_the_date( '', $listing ) ); ?></td><td><?php echo esc_html( get_the_author_meta( 'display_name', $listing->post_author ) ); ?></td><td><?php echo esc_html( get_post_meta( $listing->ID, '_staffswap_current_location', true ) ); ?> to <?php echo esc_html( get_post_meta( $listing->ID, '_staffswap_desired_location', true ) ); ?></td><td><form method="post"><textarea name="review_reason" rows="2" placeholder="Reason for this decision"></textarea><input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing->ID ); ?>"><?php wp_nonce_field( 'staffswap_listing_moderation_' . $listing->ID, 'staffswap_listing_moderation_nonce' ); ?><p><button type="submit" class="button button-primary" name="staffswap_listing_moderation_action" value="approved">Approve</button> <button type="submit" class="button" name="staffswap_listing_moderation_action" value="changes_requested">Request changes</button> <button type="submit" class="button" name="staffswap_listing_moderation_action" value="rejected">Reject</button></p></form></td></tr><?php endforeach; else : ?><tr><td colspan="4">No pending listings are waiting for review.</td></tr><?php endif; ?></tbody></table></div><?php
}
function staffswap_listing_review_status_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'listing' => get_the_ID() ), $atts, 'staffswap_listing_review_status' );
    $listing_id = absint( $atts['listing'] );
    if ( ! is_user_logged_in() || (int) get_post_field( 'post_author', $listing_id ) !== get_current_user_id() ) { return ''; }
    $state = get_post_meta( $listing_id, '_staffswap_review_state', true );
    if ( ! in_array( $state, array( 'changes_requested', 'rejected' ), true ) ) { return ''; }
    $reason = get_post_meta( $listing_id, '_staffswap_review_reason', true );
    $message = 'changes_requested' === $state ? 'An administrator requested changes before publishing this listing.' : 'This listing was not approved.';
    return '<div class="notice"><p><strong>' . esc_html( $message ) . '</strong>' . ( $reason ? ' ' . esc_html( $reason ) : '' ) . '</p></div>';
}
add_shortcode( 'staffswap_listing_review_status', 'staffswap_listing_review_status_shortcode' );

function staffswap_matches_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '';
    }
    global $wpdb;
    $matches = $wpdb->get_results( $wpdb->prepare( 'SELECT m.listing_id, m.candidate_listing_id, m.score FROM ' . staffswap_db_table( 'matches' ) . ' m INNER JOIN ' . $wpdb->posts . ' p ON p.ID = m.listing_id WHERE p.post_author = %d AND p.post_status = %s AND m.status = %s ORDER BY m.score DESC', get_current_user_id(), 'publish', 'suggested' ) );
    ob_start(); ?>
            <section class="panel staffswap-matches" style="margin-top:16px"><h2>Reciprocal matches</h2><?php if ( $matches ) : ?><div class="listing-list"><?php foreach ( $matches as $match ) : ?><div><p><strong><?php echo esc_html( number_format_i18n( $match->score, 0 ) ); ?>% match</strong></p><?php echo staffswap_match_explanation( $match->listing_id, $match->candidate_listing_id ); ?><?php echo staffswap_listing_card( $match->candidate_listing_id ); ?><form method="post" style="margin-top:8px"><input type="hidden" name="candidate_listing_id" value="<?php echo esc_attr( $match->candidate_listing_id ); ?>"><?php wp_nonce_field( 'staffswap_decline_match_' . $match->candidate_listing_id, 'staffswap_decline_match_nonce' ); ?><button type="submit" name="staffswap_decline_match" value="1">Dismiss match</button></form></div><?php endforeach; ?></div><?php else : ?><p class="muted">Publish a swap listing to receive reciprocal matches.</p><?php endif; ?></section>
    <?php return ob_get_clean();
}
add_shortcode( 'staffswap_matches', 'staffswap_matches_shortcode' );

function staffswap_handle_match_decline() {
    if ( ! is_user_logged_in() || ! isset( $_POST['staffswap_decline_match'] ) ) { return; }
    $candidate_listing_id = absint( $_POST['candidate_listing_id'] ?? 0 );
    if ( ! $candidate_listing_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_decline_match_nonce'] ?? '' ) ), 'staffswap_decline_match_' . $candidate_listing_id ) ) { return; }
    $declined = staffswap_member_declined_matches();
    if ( ! in_array( $candidate_listing_id, $declined, true ) ) {
        $declined[] = $candidate_listing_id;
        update_user_meta( get_current_user_id(), 'staffswap_declined_match_listings', $declined );
    }
    global $wpdb;
    $wpdb->query( $wpdb->prepare( 'UPDATE ' . staffswap_db_table( 'matches' ) . ' m INNER JOIN ' . $wpdb->posts . ' p ON p.ID = m.listing_id SET m.status = %s, m.updated_at = %s WHERE p.post_author = %d AND m.candidate_listing_id = %d', 'declined', current_time( 'mysql', true ), get_current_user_id(), $candidate_listing_id ) );
}
add_action( 'init', 'staffswap_handle_match_decline' );

function staffswap_match_explanation( $source_id, $candidate_id ) {
    $fields = array( 'profession', 'current_location', 'desired_location', 'current_employer', 'desired_employer' );
    $source = array(); $candidate = array();
    foreach ( $fields as $field ) { $source[ $field ] = staffswap_normalize_match_value( get_post_meta( $source_id, '_staffswap_' . $field, true ) ); $candidate[ $field ] = staffswap_normalize_match_value( get_post_meta( $candidate_id, '_staffswap_' . $field, true ) ); }
    $reasons = array();
    if ( $source['profession'] && $source['profession'] === $candidate['profession'] ) { $reasons[] = 'Same profession'; }
    if ( $source['current_location'] && $source['current_location'] === $candidate['desired_location'] ) { $reasons[] = 'Your destination matches their current location'; }
    if ( $source['desired_location'] && $source['desired_location'] === $candidate['current_location'] ) { $reasons[] = 'Their destination matches your current location'; }
    if ( $source['current_employer'] && $source['current_employer'] === $candidate['desired_employer'] && $source['desired_employer'] === $candidate['current_employer'] ) { $reasons[] = 'Employers align in both directions'; }
    if ( ! $reasons ) { $reasons[] = 'Compatible reciprocal route'; }
    return '<div class="match-explanation" aria-label="Why this listing matches"><span class="match-explanation__title">Why it matches</span><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $reasons ) ) . '</li></ul></div>';
}

function staffswap_listing_card( $post_id ) {
    $meta = array();
    foreach ( staffswap_listing_fields() as $key => $label ) { $meta[ $key ] = get_post_meta( $post_id, '_staffswap_' . $key, true ); }
    $name = get_the_title( $post_id ); $score = $meta['match_score'] ?: '90'; $profession = $meta['profession'] ?: 'Professional';
    ob_start(); ?>
    <article class="listing-card"><div class="listing-main"><div class="person"><div class="avatar"><?php echo esc_html( strtoupper( substr( $name, 0, 1 ) ) ); ?></div><div><h3><?php echo esc_html( $name ); ?><?php if ( $meta['verified'] ) : ?> <span class="verified">&#10003; Verified</span><?php endif; ?></h3><p style="color:#005f2e;font-weight:600"><?php echo esc_html( $profession ); ?></p><p class="muted"><?php echo esc_html( $meta['experience'] ?: '-' ); ?> years experience</p></div></div><div class="swap-route"><div class="route"><small>Current</small><strong><?php echo esc_html( $meta['current_employer'] ?: 'Not specified' ); ?></strong><span><?php echo esc_html( $meta['current_location'] ?: 'Location pending' ); ?></span></div><div class="swap-icon">&#8596;</div><div class="route route--desired"><small>Desired</small><strong><?php echo esc_html( $meta['desired_employer'] ?: 'Not specified' ); ?></strong><span><?php echo esc_html( $meta['desired_location'] ?: 'Location pending' ); ?></span></div></div><div class="match"><strong><?php echo esc_html( $score ); ?>%</strong><small><?php echo (int) $score >= 94 ? 'Excellent' : 'Good'; ?> Match</small></div></div><?php if ( $meta['swap_reason'] ) : ?><p class="muted" style="margin-top:8px"><strong>Why they want to swap:</strong> <?php echo esc_html( staffswap_swap_reasons()[ $meta['swap_reason'] ] ?? $meta['swap_reason'] ); ?></p><?php endif; ?><div class="listing-meta"><div class="tags"><?php if ( $meta['verified'] ) : ?><span class="tag tag--success">Verified</span><?php endif; ?><?php if ( $meta['housing'] ) : ?><span class="tag tag--housing">Housing available</span><?php else : ?><span class="tag tag--housing">Housing not included</span><?php endif; ?><?php if ( $meta['nearby_towns'] ) : ?><span class="tag tag--nearby">Nearby towns OK</span><?php endif; ?><?php if ( $meta['relocation_support'] ) : ?><span class="tag tag--relocation">Relocation support</span><?php endif; ?><?php if ( $meta['urgent'] ) : ?><span class="tag tag--urgent">Urgent</span><?php endif; ?></div><div class="listing-actions"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">View profile</a></div></div></article>
    <?php return ob_get_clean();
}

function staffswap_listings_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'limit' => 10 ), $atts, 'staffswap_listings' );
    $args = array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'posts_per_page' => (int) $atts['limit'], 'paged' => max( 1, get_query_var( 'paged', 1 ) ) );
    $args['meta_query'] = array( 'relation' => 'AND' );
    foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer' ) as $filter ) { if ( ! empty( $_GET[ $filter ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $filter, 'value' => sanitize_text_field( wp_unslash( $_GET[ $filter ] ) ), 'compare' => 'LIKE' ); } }
    foreach ( array( 'verified', 'housing', 'urgent' ) as $flag ) { if ( ! empty( $_GET[ $flag ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $flag, 'value' => '1', 'compare' => '=' ); } }
    if ( isset( $_GET['min_experience'] ) && '' !== $_GET['min_experience'] ) { $args['meta_query'][] = array( 'key' => '_staffswap_experience', 'value' => absint( $_GET['min_experience'] ), 'type' => 'NUMERIC', 'compare' => '>=' ); }
    if ( isset( $_GET['max_experience'] ) && '' !== $_GET['max_experience'] ) { $args['meta_query'][] = array( 'key' => '_staffswap_experience', 'value' => absint( $_GET['max_experience'] ), 'type' => 'NUMERIC', 'compare' => '<=' ); }
    $query = new WP_Query( $args ); ob_start(); ?>
    <div class="marketplace"><aside class="panel"><h2>Filter listings</h2><form method="get"><div class="field"><label for="current_location">Current location</label><input id="current_location" name="current_location" value="<?php echo esc_attr( $_GET['current_location'] ?? '' ); ?>" placeholder="e.g. Lusaka"></div><div class="field"><label for="desired_location">Desired location</label><input id="desired_location" name="desired_location" value="<?php echo esc_attr( $_GET['desired_location'] ?? '' ); ?>" placeholder="e.g. Copperbelt"></div><div class="field"><label for="profession">Profession</label><input id="profession" name="profession" value="<?php echo esc_attr( $_GET['profession'] ?? '' ); ?>" placeholder="e.g. Teacher"></div><?php foreach ( array( 'verified' => 'Verified users only', 'housing' => 'Housing available', 'urgent' => 'Urgent swaps only' ) as $key => $label ) : ?><label class="check"><input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $_GET[ $key ] ) ); ?>><?php echo esc_html( $label ); ?></label><?php endforeach; ?><div class="stack" style="margin-top:20px"><input type="submit" value="Apply filters"><a class="button button--outline" style="text-align:center" href="<?php echo esc_url( get_permalink() ); ?>">Clear filters</a></div></form></aside><section><div class="page-heading"><div><p class="eyebrow">THE MARKETPLACE</p><h1>Swap Listings</h1><p class="muted"><?php echo esc_html( $query->found_posts ); ?> swap requests matching your search</p></div></div><div class="notice"><div><h2>Get better matches</h2><p class="muted">Create a swap post to connect with professionals looking for your institution.</p></div><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Create swap post</a></div><div class="listing-list"><?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); echo staffswap_listing_card( get_the_ID() ); endwhile; wp_reset_postdata(); else : ?><div class="panel"><h2>No listings found</h2><p class="muted">Try widening your filters or create the first listing for this route.</p></div><?php endif; ?></div></section></div>
    <?php return ob_get_clean();
}
add_shortcode( 'staffswap_listings', 'staffswap_listings_shortcode' );

function staffswap_advanced_listings_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'limit' => 10 ), $atts, 'staffswap_listings' );
    $args = array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'posts_per_page' => absint( $atts['limit'] ), 'paged' => max( 1, get_query_var( 'paged', 1 ) ), 'meta_query' => array( 'relation' => 'AND' ) );
    foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer' ) as $filter ) { if ( ! empty( $_GET[ $filter ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $filter, 'value' => sanitize_text_field( wp_unslash( $_GET[ $filter ] ) ), 'compare' => 'LIKE' ); } }
    foreach ( array( 'verified', 'housing', 'urgent' ) as $flag ) { if ( ! empty( $_GET[ $flag ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $flag, 'value' => '1' ); } }
    foreach ( array( 'min_experience' => '>=', 'max_experience' => '<=' ) as $field => $compare ) { if ( isset( $_GET[ $field ] ) && '' !== $_GET[ $field ] ) { $args['meta_query'][] = array( 'key' => '_staffswap_experience', 'value' => absint( $_GET[ $field ] ), 'type' => 'NUMERIC', 'compare' => $compare ); } }
    $query = new WP_Query( $args ); ob_start(); ?>
    <div class="marketplace"><aside class="panel"><h2>Filter listings</h2><form method="get"><div class="field"><label for="current_location">Current location</label><?php echo staffswap_render_location_select( 'current_location', 'current_location', $_GET['current_location'] ?? '', false ); ?></div><div class="field"><label for="desired_location">Desired location</label><?php echo staffswap_render_location_select( 'desired_location', 'desired_location', $_GET['desired_location'] ?? '', false ); ?></div><div class="field"><label for="profession">Profession</label><input id="profession" name="profession" value="<?php echo esc_attr( $_GET['profession'] ?? '' ); ?>" placeholder="e.g. Teacher"></div><div class="field"><label for="current_employer">Current employer</label><input id="current_employer" name="current_employer" value="<?php echo esc_attr( $_GET['current_employer'] ?? '' ); ?>" placeholder="e.g. General Hospital"></div><div class="filter-range"><div class="field"><label for="min_experience">Min years</label><input id="min_experience" name="min_experience" type="number" min="0" value="<?php echo esc_attr( $_GET['min_experience'] ?? '' ); ?>"></div><div class="field"><label for="max_experience">Max years</label><input id="max_experience" name="max_experience" type="number" min="0" value="<?php echo esc_attr( $_GET['max_experience'] ?? '' ); ?>"></div></div><?php foreach ( array( 'verified' => 'Verified users only', 'housing' => 'Housing available', 'urgent' => 'Urgent swaps only' ) as $key => $label ) : ?><label class="check"><input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $_GET[ $key ] ) ); ?>><?php echo esc_html( $label ); ?></label><?php endforeach; ?><div class="stack" style="margin-top:20px"><input type="submit" value="Apply filters"><a class="button button--outline" style="text-align:center" href="<?php echo esc_url( get_permalink() ); ?>">Clear filters</a></div></form></aside><section><div class="page-heading"><div><p class="eyebrow">THE MARKETPLACE</p><h1>Swap Listings</h1><p class="muted"><?php echo esc_html( $query->found_posts ); ?> swap requests matching your search</p></div></div><div class="listing-list"><?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); echo staffswap_listing_card( get_the_ID() ); endwhile; wp_reset_postdata(); else : ?><div class="empty-state"><h2>No matching swaps found</h2><p class="muted">Try widening your location, employer, or experience filters.</p></div><?php endif; ?></div></section></div>
    <?php return ob_get_clean();
}
remove_shortcode( 'staffswap_listings' );
add_shortcode( 'staffswap_listings', 'staffswap_advanced_listings_shortcode' );

function staffswap_login_shortcode() { if ( is_user_logged_in() ) { return '<div class="panel content-form"><h2>You are signed in</h2><p><a class="button button--primary" href="' . esc_url( home_url( '/my-profile/' ) ) . '">Open my profile</a></p></div>'; } ob_start(); ?><div class="panel content-form"><h1>Sign in</h1><p class="muted">Access your swap requests, saved matches, and messages.</p><?php wp_login_form( array( 'redirect' => home_url( '/my-profile/' ), 'label_username' => 'Email or username', 'label_password' => 'Password', 'label_log_in' => 'Sign in' ) ); ?><p>New here? <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create an account</a></p></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_login', 'staffswap_login_shortcode' );

function staffswap_register_wizard_shortcode() { if ( is_user_logged_in() ) { return '<div class="panel content-form"><h2>Welcome to StaffExchangeHub</h2><p>Your Zambian civil service swap profile is ready. <a href="' . esc_url( home_url( '/my-profile/' ) ) . '">Go to your dashboard</a></p></div>'; } $step = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1; $message = ''; $form_data = array(); if ( isset( $_POST['staffswap_register_step'] ) && check_admin_referer( 'staffswap_register_step_' . $step, 'staffswap_register_nonce' ) ) { $form_data = array( 'full_name' => sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) ), 'email' => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ), 'password' => (string) ( $_POST['password'] ?? '' ), 'profession' => sanitize_text_field( wp_unslash( $_POST['profession'] ?? '' ) ), 'employer' => sanitize_text_field( wp_unslash( $_POST['employer'] ?? '' ) ), 'years_service' => absint( $_POST['years_service'] ?? 0 ), 'current_location' => sanitize_text_field( wp_unslash( $_POST['current_location'] ?? '' ) ), 'desired_location' => sanitize_text_field( wp_unslash( $_POST['desired_location'] ?? '' ) ), 'staff_housing' => isset( $_POST['staff_housing'] ) ? 1 : 0, ); if ( $step === 1 ) { if ( empty( $form_data['full_name'] ) || empty( $form_data['email'] ) || empty( $form_data['password'] ) ) { $message = '<div class="notice"><p>Please complete all fields.</p></div>'; } elseif ( ! is_email( $form_data['email'] ) ) { $message = '<div class="notice"><p>Please enter a valid email address.</p></div>'; } elseif ( strlen( $form_data['password'] ) < 8 ) { $message = '<div class="notice"><p>Password must be at least 8 characters.</p></div>'; } elseif ( email_exists( $form_data['email'] ) ) { $message = '<div class="notice"><p>That email is already registered. <a href="' . esc_url( home_url( '/sign-in/' ) ) . '">Sign in</a></p></div>'; } else { wp_safe_redirect( add_query_arg( 'step', 2, home_url( '/register/' ) ) ); exit; } } elseif ( $step === 2 ) { if ( empty( $form_data['profession'] ) || empty( $form_data['employer'] ) ) { $message = '<div class="notice"><p>Please complete all professional fields.</p></div>'; } else { wp_safe_redirect( add_query_arg( 'step', 3, home_url( '/register/' ) ) ); exit; } } elseif ( $step === 3 ) { if ( empty( $form_data['current_location'] ) || empty( $form_data['desired_location'] ) ) { $message = '<div class="notice"><p>Please specify your current and desired location.</p></div>'; } else { $username = sanitize_user( explode( '@', $form_data['email'] )[0] . '_' . time() ); $user_id = wp_create_user( $username, $form_data['password'], $form_data['email'] ); if ( ! is_wp_error( $user_id ) ) { wp_update_user( array( 'ID' => $user_id, 'display_name' => $form_data['full_name'] ) ); update_user_meta( $user_id, 'staffswap_profession', $form_data['profession'] ); update_user_meta( $user_id, 'staffswap_employer', $form_data['employer'] ); update_user_meta( $user_id, 'staffswap_years_service', $form_data['years_service'] ); update_user_meta( $user_id, 'staffswap_location', $form_data['current_location'] ); update_user_meta( $user_id, 'staffswap_desired_location', $form_data['desired_location'] ); update_user_meta( $user_id, 'staffswap_staff_housing', $form_data['staff_housing'] ); update_user_meta( $user_id, 'staffswap_verified_status', 'unverified' ); staffswap_record_event( 'user_registered', $user_id, array( 'profession' => $form_data['profession'] ), $user_id ); wp_set_auth_cookie( $user_id ); wp_safe_redirect( home_url( '/my-profile/' ) ); exit; } $message = '<div class="notice"><p>Account creation failed. Please try again.</p></div>'; } } } ob_start(); ?><div class="account-page"><div class="account-intro"><p class="eyebrow">STEP ' . esc_html( $step ) . ' OF 3</p><h1><?php echo $step === 1 ? 'Account Identity' : ( $step === 2 ? 'Professional Status' : 'Relocation Goals' ); ?></h1><p class="muted"><?php echo $step === 1 ? 'Create your secure login credentials.' : ( $step === 2 ? 'Tell us about your role and institution.' : 'Where are you now, and where do you want to go?' ); ?></p></div><form method="post" class="staffswap-register-form"><?php echo $message; if ( $step === 1 ) : ?><div class="field"><label for="full_name">Official Full Name</label><input id="full_name" name="full_name" placeholder="e.g. Arthur Musonda" required></div><div class="field"><label for="email">Email Address</label><input id="email" name="email" type="email" placeholder="your.email@moh.gov.zm" required></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" minlength="8" placeholder="Minimum 8 characters" required></div><?php elseif ( $step === 2 ) : ?><div class="field"><label for="profession">Profession / Cadre</label><select id="profession" name="profession" required><option value="">Select profession</option><option value="registered_nurse">Registered Nurse</option><option value="secondary_teacher">Secondary School Teacher</option><option value="primary_teacher">Primary School Teacher</option><option value="clinical_officer">Clinical Officer</option><option value="doctor">Medical Doctor</option><option value="pharmacist">Pharmacist</option><option value="police_officer">Police Officer</option><option value="administrative_officer">Administrative Officer</option><option value="other">Other</option></select></div><div class="field"><label for="employer">Employer / Institution Type</label><select id="employer" name="employer" required><option value="">Select employer type</option><option value="government_hospital">Government Hospital</option><option value="secondary_school">Secondary School</option><option value="primary_school">Primary School</option><option value="ministry">Ministry / Headquarters</option><option value="rural_health_center">Rural Health Center</option><option value="police_station">Police Station</option><option value="local_council">Local Council</option><option value="other">Other</option></select></div><div class="field"><label for="years_service">Years of Service</label><input id="years_service" name="years_service" type="number" min="0" placeholder="e.g. 4" required></div><?php else : ?><div class="field"><label for="current_location">Current Province & Town</label><input id="current_location" name="current_location" placeholder="e.g. Lusaka, Lusaka Province" required></div><div class="field"><label for="desired_location">Desired Province & Town</label><input id="desired_location" name="desired_location" placeholder="e.g. Ndola, Copperbelt Province" required></div><label class="check"><input type="checkbox" name="staff_housing" value="1"> I have staff accommodation available to handover</label><?php endif; ?><input type="hidden" name="staffswap_register_step" value="1"><?php wp_nonce_field( 'staffswap_register_step_' . $step, 'staffswap_register_nonce' ); ?><div style="display:flex;gap:10px;margin-top:20px"><input type="submit" value="<?php echo $step === 3 ? 'Complete & Get Matched!' : 'Continue'; ?>"><a class="button button--outline" href="<?php echo $step > 1 ? esc_url( add_query_arg( 'step', $step - 1, home_url( '/register/' ) ) ) : esc_url( home_url( '/' ) ); ?>"><?php echo $step > 1 ? 'Back' : 'Cancel'; ?></a></div></form></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_register', 'staffswap_register_wizard_shortcode' );

function staffswap_register_wizard_v2_shortcode() {
    if ( is_user_logged_in() ) {
        return '<div class="panel content-form"><h2>Welcome to StaffExchangeHub</h2><p>Your Zambian civil service swap profile is ready. <a href="' . esc_url( home_url( '/my-profile/' ) ) . '">Go to your dashboard</a></p></div>';
    }

    $step = min( 3, max( 1, absint( $_GET['step'] ?? 1 ) ) );
    $token = sanitize_key( wp_unslash( $_REQUEST['staffswap_registration'] ?? '' ) );
    $state_key = $token ? 'staffswap_registration_' . $token : '';
    $form_data = $state_key ? get_transient( $state_key ) : array();
    $form_data = is_array( $form_data ) ? $form_data : array();
    $message = '';

    if ( isset( $_POST['staffswap_register_step'] ) && check_admin_referer( 'staffswap_register_step_' . $step, 'staffswap_register_nonce' ) ) {
        $form_data = array_merge( $form_data, array(
            'full_name'        => sanitize_text_field( wp_unslash( $_POST['full_name'] ?? $form_data['full_name'] ?? '' ) ),
            'email'            => sanitize_email( wp_unslash( $_POST['email'] ?? $form_data['email'] ?? '' ) ),
            'password'         => (string) ( $_POST['password'] ?? $form_data['password'] ?? '' ),
            'profession'       => sanitize_text_field( wp_unslash( $_POST['profession'] ?? $form_data['profession'] ?? '' ) ),
            'employer'         => sanitize_text_field( wp_unslash( $_POST['employer'] ?? $form_data['employer'] ?? '' ) ),
            'years_service'    => absint( $_POST['years_service'] ?? $form_data['years_service'] ?? 0 ),
            'current_location' => sanitize_text_field( wp_unslash( $_POST['current_location'] ?? $form_data['current_location'] ?? '' ) ),
            'desired_location' => sanitize_text_field( wp_unslash( $_POST['desired_location'] ?? $form_data['desired_location'] ?? '' ) ),
            'staff_housing'    => isset( $_POST['staff_housing'] ) ? 1 : (int) ( $form_data['staff_housing'] ?? 0 ),
        ) );

        if ( 1 === $step ) {
            if ( empty( $form_data['full_name'] ) || empty( $form_data['email'] ) || empty( $form_data['password'] ) ) {
                $message = '<div class="notice"><p>Please complete all fields.</p></div>';
            } elseif ( ! is_email( $form_data['email'] ) ) {
                $message = '<div class="notice"><p>Please enter a valid email address.</p></div>';
            } elseif ( strlen( $form_data['password'] ) < 8 ) {
                $message = '<div class="notice"><p>Password must be at least 8 characters.</p></div>';
            } elseif ( email_exists( $form_data['email'] ) ) {
                $message = '<div class="notice"><p>That email is already registered. <a href="' . esc_url( home_url( '/sign-in/' ) ) . '">Sign in</a></p></div>';
            }
        } elseif ( 2 === $step && ( empty( $form_data['profession'] ) || empty( $form_data['employer'] ) ) ) {
            $message = '<div class="notice"><p>Please complete all professional fields.</p></div>';
        } elseif ( 3 === $step && ( empty( $form_data['current_location'] ) || empty( $form_data['desired_location'] ) ) ) {
            $message = '<div class="notice"><p>Please specify your current and desired location.</p></div>';
        }

        if ( ! $message && $step < 3 ) {
            $token = $token ?: wp_generate_password( 24, false, false );
            set_transient( 'staffswap_registration_' . $token, $form_data, HOUR_IN_SECONDS );
            wp_safe_redirect( add_query_arg( array( 'step' => $step + 1, 'staffswap_registration' => $token ), home_url( '/register/' ) ) );
            exit;
        }

        if ( ! $message && 3 === $step ) {
            $username = sanitize_user( explode( '@', $form_data['email'] )[0] . '_' . time(), true );
            $user_id = wp_create_user( $username, $form_data['password'], $form_data['email'] );
            if ( ! is_wp_error( $user_id ) ) {
                wp_update_user( array( 'ID' => $user_id, 'display_name' => $form_data['full_name'] ) );
                foreach ( array( 'profession', 'employer', 'years_service', 'current_location', 'desired_location', 'staff_housing' ) as $field ) {
                    $meta_key = 'current_location' === $field ? 'location' : ( 'staff_housing' === $field ? 'staff_housing' : $field );
                    update_user_meta( $user_id, 'staffswap_' . $meta_key, $form_data[ $field ] );
                }
                update_user_meta( $user_id, 'staffswap_verified_status', 'unverified' );
                staffswap_record_event( 'user_registered', $user_id, array( 'profession' => $form_data['profession'] ), $user_id );
                delete_transient( 'staffswap_registration_' . $token );
                wp_set_auth_cookie( $user_id );
                wp_safe_redirect( home_url( '/my-profile/' ) );
                exit;
            }
            $message = '<div class="notice"><p>Account creation failed. Please try again.</p></div>';
        }
    }

    $route_args = array_filter( array( 'staffswap_registration' => $token ) );
    $back_url = 1 === $step ? home_url( '/' ) : add_query_arg( array_merge( $route_args, array( 'step' => $step - 1 ) ), home_url( '/register/' ) );
    ob_start(); ?>
    <div class="account-page"><div class="account-intro"><p class="eyebrow">STEP <?php echo esc_html( $step ); ?> OF 3</p><h1><?php echo esc_html( 1 === $step ? 'Account Identity' : ( 2 === $step ? 'Professional Status' : 'Relocation Goals' ) ); ?></h1></div><form method="post" class="staffswap-register-form"><?php echo $message; ?><input type="hidden" name="staffswap_registration" value="<?php echo esc_attr( $token ); ?>"><?php if ( 1 === $step ) : ?><div class="field"><label for="full_name">Official Full Name</label><input id="full_name" name="full_name" value="<?php echo esc_attr( $form_data['full_name'] ?? '' ); ?>" required></div><div class="field"><label for="email">Email Address</label><input id="email" name="email" type="email" value="<?php echo esc_attr( $form_data['email'] ?? '' ); ?>" required></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" minlength="8" required></div><?php elseif ( 2 === $step ) : ?><div class="field"><label for="profession">Profession / Cadre</label><input id="profession" name="profession" value="<?php echo esc_attr( $form_data['profession'] ?? '' ); ?>" required></div><div class="field"><label for="employer">Employer / Institution Type</label><input id="employer" name="employer" value="<?php echo esc_attr( $form_data['employer'] ?? '' ); ?>" required></div><div class="field"><label for="years_service">Years of Service</label><input id="years_service" name="years_service" type="number" min="0" value="<?php echo esc_attr( $form_data['years_service'] ?? '' ); ?>" required></div><?php else : ?><div class="field"><label for="current_location">Current Province & Town</label><?php echo staffswap_render_location_select( 'current_location', 'current_location', $form_data['current_location'] ?? '' ); ?></div><div class="field"><label for="desired_location">Desired Province & Town</label><?php echo staffswap_render_location_select( 'desired_location', 'desired_location', $form_data['desired_location'] ?? '' ); ?></div><label class="check"><input type="checkbox" name="staff_housing" value="1" <?php checked( ! empty( $form_data['staff_housing'] ) ); ?>> I have staff accommodation available to handover</label><?php endif; ?><input type="hidden" name="staffswap_register_step" value="1"><?php wp_nonce_field( 'staffswap_register_step_' . $step, 'staffswap_register_nonce' ); ?><div style="display:flex;gap:10px;margin-top:20px"><input type="submit" value="<?php echo esc_attr( 3 === $step ? 'Complete & Get Matched!' : 'Continue' ); ?>"><a class="button button--outline" href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html( $step > 1 ? 'Back' : 'Cancel' ); ?></a></div></form></div>
    <?php return ob_get_clean();
}
remove_shortcode( 'staffswap_register' );
add_shortcode( 'staffswap_register', 'staffswap_register_wizard_v2_shortcode' );

function staffswap_dashboard_shortcode() { if ( ! is_user_logged_in() ) { return '<div class="panel content-form"><h2>Sign in to view your profile</h2><a class="button button--primary" href="' . esc_url( home_url( '/sign-in/' ) ) . '">Sign in</a></div>'; } $user = wp_get_current_user(); $query = new WP_Query( array( 'post_type' => 'swap_listing', 'author' => get_current_user_id(), 'post_status' => array( 'publish', 'pending' ), 'posts_per_page' => 10 ) ); ob_start(); ?><div class="content-form"><div class="page-heading"><div><p class="eyebrow">MEMBER AREA</p><h1><?php echo esc_html( $user->display_name ); ?></h1><p class="muted">Manage your profile and swap requests.</p></div><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Create listing</a></div><div class="panel"><h2>Your swap requests</h2><?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?><p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span class="muted">(<?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?>)</span></p><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">You have not created a swap request yet.</p><?php endif; ?></div><?php echo do_shortcode( '[staffswap_matches]' ); ?></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_dashboard', 'staffswap_dashboard_shortcode' );

function staffswap_search_shortcode() { ob_start(); ?><div class="content-form"><div class="page-heading"><div><p class="eyebrow">SEARCH THE NETWORK</p><h1>Find your next workplace</h1><p class="muted">Search by profession, current location, or desired location.</p></div></div><?php echo do_shortcode( '[staffswap_listings]' ); ?><?php echo do_shortcode( '[staffswap_save_search]' ); ?><?php echo do_shortcode( '[staffswap_saved_searches]' ); ?></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_search', 'staffswap_search_shortcode' );

function staffswap_create_form_shortcode() {
    if ( ! is_user_logged_in() ) { return '<div class="panel content-form"><h2>Join the exchange network</h2><p>You need an account to publish a swap listing.</p><a class="button button--primary" href="' . esc_url( wp_registration_url() ) . '">Create an account</a></div>'; }
    if ( function_exists( 'staffswap_has_active_membership' ) && ! staffswap_has_active_membership() ) { return function_exists( 'staffswap_membership_required_notice' ) ? staffswap_membership_required_notice( 'publish a swap listing' ) : '<div class="panel"><h2>Membership required</h2><p>Activate your membership to publish a swap listing.</p></div>'; }
    if ( isset( $_POST['staffswap_create_listing'] ) && check_admin_referer( 'staffswap_create_listing', 'staffswap_create_nonce' ) ) {
        $is_verified = 'verified' === get_user_meta( get_current_user_id(), 'staffswap_verified_status', true );
        $post_id = wp_insert_post( array( 'post_type' => 'swap_listing', 'post_title' => sanitize_text_field( wp_unslash( $_POST['name'] ) ), 'post_content' => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ), 'post_status' => $is_verified ? 'publish' : 'pending', 'post_author' => get_current_user_id() ), true );
            if ( ! is_wp_error( $post_id ) ) { foreach ( staffswap_listing_fields() as $key => $label ) { if ( isset( $_POST[ $key ] ) ) { $value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) ); if ( 'swap_reason' === $key && ! array_key_exists( $value, staffswap_swap_reasons() ) ) { $value = ''; } update_post_meta( $post_id, '_staffswap_' . $key, $value ); } } update_post_meta( $post_id, '_staffswap_verified', $is_verified ? '1' : '' ); if ( $is_verified ) { staffswap_refresh_listing_matches( $post_id ); return '<div class="panel content-form"><h2>Listing published</h2><p>Your verified listing is live and the matchmaker is scanning for reciprocal routes.</p></div>'; } return '<div class="panel content-form"><h2>Listing submitted for review</h2><p>Submit verification to publish your listing and activate reciprocal matching.</p><a class="button button--primary" href="' . esc_url( home_url( '/verification/' ) ) . '">Open verification</a></div>'; }
    }
    $user = wp_get_current_user();
    $profile = array( 'name' => $user->display_name, 'profession' => get_user_meta( $user->ID, 'staffswap_profession', true ), 'current_employer' => get_user_meta( $user->ID, 'staffswap_employer', true ), 'current_location' => get_user_meta( $user->ID, 'staffswap_location', true ), 'experience' => get_user_meta( $user->ID, 'staffswap_years_service', true ), 'housing' => get_user_meta( $user->ID, 'staffswap_staff_housing', true ) );
    ob_start(); ?>
    <div class="panel content-form"><h1>Create a swap post</h1><p class="muted">Tell other professionals where you are and where you hope to go.</p><form method="post"><div class="form-grid"><div class="field"><label for="name">Your name</label><input id="name" name="name" value="<?php echo esc_attr( $profile['name'] ); ?>" required></div><div class="field"><label for="profession">Profession</label><input id="profession" name="profession" value="<?php echo esc_attr( $profile['profession'] ); ?>" required></div><div class="field"><label for="current_employer">Current employer</label><input id="current_employer" name="current_employer" value="<?php echo esc_attr( $profile['current_employer'] ); ?>" required></div><div class="field"><label for="current_location">Current location</label><?php echo staffswap_render_location_select( 'current_location', 'current_location', $profile['current_location'] ); ?></div><div class="field"><label for="desired_employer">Desired employer</label><input id="desired_employer" name="desired_employer" required></div><div class="field"><label for="desired_location">Desired location</label><?php echo staffswap_render_location_select( 'desired_location', 'desired_location', '' ); ?></div><div class="field"><label for="experience">Years of experience</label><input id="experience" name="experience" type="number" min="0" value="<?php echo esc_attr( $profile['experience'] ); ?>" required></div><div class="field"><label for="housing">Housing</label><select id="housing" name="housing"><option value="">Not included</option><option value="1" <?php selected( $profile['housing'], '1' ); ?>>Available</option></select></div><div class="field"><label for="swap_reason">Reason for swap request</label><select id="swap_reason" name="swap_reason" required><option value="">Select a reason</option><?php foreach ( staffswap_swap_reasons() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></div><div class="field full"><span class="field-label">Listing badges</span><label class="check"><input type="checkbox" name="nearby_towns" value="1">Open to nearby towns</label><label class="check"><input type="checkbox" name="relocation_support" value="1">Can assist with relocation</label></div><div class="field full"><label for="notes">Additional details</label><textarea id="notes" name="notes" rows="5"></textarea></div></div><?php wp_nonce_field( 'staffswap_create_listing', 'staffswap_create_nonce' ); ?><input type="submit" name="staffswap_create_listing" value="Submit listing"></form></div>
    <?php return ob_get_clean();
}
add_shortcode( 'staffswap_create_form', 'staffswap_create_form_shortcode' );

function staffswap_record_event( $event_type, $object_id = 0, $payload = array(), $user_id = 0 ) { global $wpdb; $wpdb->insert( staffswap_db_table( 'events' ), array( 'user_id' => $user_id ? absint( $user_id ) : ( get_current_user_id() ?: null ), 'event_type' => sanitize_key( $event_type ), 'object_id' => $object_id ? absint( $object_id ) : null, 'payload' => wp_json_encode( $payload ), 'created_at' => current_time( 'mysql', true ) ), array( '%d', '%s', '%d', '%s', '%s' ) ); }

function staffswap_analytics_menu() {
    add_submenu_page( 'edit.php?post_type=swap_listing', 'StaffSwap Analytics', 'Analytics', 'manage_options', 'staffswap-analytics', 'staffswap_analytics_screen' );
}
add_action( 'admin_menu', 'staffswap_analytics_menu' );

function staffswap_analytics_screen() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    global $wpdb;
    $listing_count = (int) wp_count_posts( 'swap_listing' )->publish;
    $match_count = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . staffswap_db_table( 'matches' ) . " WHERE status = 'suggested'" );
    $accepted_offers = post_type_exists( 'staffswap_offer' ) ? (int) ( new WP_Query( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'meta_key' => '_staffswap_offer_status', 'meta_value' => 'accepted', 'fields' => 'ids', 'posts_per_page' => -1 ) ) )->found_posts : 0;
    $new_members = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . staffswap_db_table( 'events' ) . " WHERE event_type = 'user_registered' AND created_at >= DATE_SUB( UTC_TIMESTAMP(), INTERVAL 30 DAY)" );
    $top_professions = $wpdb->get_results( "SELECT meta_value AS profession, COUNT(*) AS total FROM {$wpdb->postmeta} WHERE meta_key = '_staffswap_profession' AND meta_value <> '' GROUP BY meta_value ORDER BY total DESC LIMIT 5" );
    ?><div class="wrap"><h1>StaffSwap Analytics</h1><p>Marketplace activity for the last 30 days where noted.</p><div class="staffswap-analytics-grid"><div class="staffswap-analytics-card"><strong><?php echo esc_html( $listing_count ); ?></strong><span>Published listings</span></div><div class="staffswap-analytics-card"><strong><?php echo esc_html( $match_count ); ?></strong><span>Active match suggestions</span></div><div class="staffswap-analytics-card"><strong><?php echo esc_html( $accepted_offers ); ?></strong><span>Accepted offers</span></div><div class="staffswap-analytics-card"><strong><?php echo esc_html( $new_members ); ?></strong><span>New members, 30 days</span></div></div><h2>Top professions</h2><table class="widefat striped" style="max-width:640px"><thead><tr><th>Profession</th><th>Published listings</th></tr></thead><tbody><?php if ( $top_professions ) : foreach ( $top_professions as $profession ) : ?><tr><td><?php echo esc_html( $profession->profession ); ?></td><td><?php echo esc_html( $profession->total ); ?></td></tr><?php endforeach; else : ?><tr><td colspan="2">No published listing data yet.</td></tr><?php endif; ?></tbody></table></div><?php
}

function staffswap_analytics_admin_styles( $hook ) {
    if ( 'swap_listing_page_staffswap-analytics' !== $hook ) { return; }
    wp_add_inline_style( 'common', '.staffswap-analytics-grid{display:grid;grid-template-columns:repeat(4,minmax(150px,1fr));gap:16px;max-width:900px;margin:20px 0 28px}.staffswap-analytics-card{background:#fff;border:1px solid #dcdcde;padding:20px}.staffswap-analytics-card strong{display:block;font-size:28px;color:#135e96}.staffswap-analytics-card span{color:#50575e}@media(max-width:782px){.staffswap-analytics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}' );
}
add_action( 'admin_enqueue_scripts', 'staffswap_analytics_admin_styles' );

function staffswap_save_search_shortcode() { if ( ! is_user_logged_in() ) { return ''; } if ( isset( $_POST['staffswap_save_search'] ) && check_admin_referer( 'staffswap_save_search', 'staffswap_saved_search_nonce' ) ) { global $wpdb; $filters = array(); foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer', 'verified', 'housing', 'urgent', 'min_experience', 'max_experience' ) as $key ) { if ( ! empty( $_REQUEST[ $key ] ) ) { $filters[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); } } $frequency = sanitize_key( wp_unslash( $_POST['alert_frequency'] ?? 'weekly' ) ); if ( ! in_array( $frequency, array( 'daily', 'weekly' ), true ) ) { $frequency = 'weekly'; } $wpdb->insert( staffswap_db_table( 'saved_searches' ), array( 'user_id' => get_current_user_id(), 'name' => sanitize_text_field( wp_unslash( $_POST['search_name'] ?? 'My swap search' ) ), 'filters' => wp_json_encode( $filters ), 'alert_frequency' => $frequency, 'created_at' => current_time( 'mysql', true ) ), array( '%d', '%s', '%s', '%s', '%s' ) ); staffswap_record_event( 'saved_search_created', 0, $filters ); } ob_start(); ?><form method="post" class="staffswap-save-search"><input name="search_name" placeholder="Name this search" aria-label="Search name" required><select name="alert_frequency" aria-label="Alert frequency"><option value="weekly">Weekly alerts</option><option value="daily">Daily alerts</option></select><input type="submit" name="staffswap_save_search" value="Save search"><?php wp_nonce_field( 'staffswap_save_search', 'staffswap_saved_search_nonce' ); ?></form><?php return ob_get_clean(); }
add_shortcode( 'staffswap_save_search', 'staffswap_save_search_shortcode' );

function staffswap_saved_searches_shortcode() {
    if ( ! is_user_logged_in() ) { return ''; }
    global $wpdb;
    $searches = $wpdb->get_results( $wpdb->prepare( 'SELECT id, name, filters, alert_frequency FROM ' . staffswap_db_table( 'saved_searches' ) . ' WHERE user_id = %d ORDER BY created_at DESC', get_current_user_id() ) );
    if ( ! $searches ) { return ''; }
    ob_start(); ?><section class="panel" style="margin-top:16px"><h2>Saved searches</h2><?php foreach ( $searches as $search ) : $filters = json_decode( $search->filters, true ); $url = add_query_arg( is_array( $filters ) ? $filters : array(), home_url( '/search/' ) ); ?><p><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $search->name ); ?></a> <span class="muted"><?php echo esc_html( ucfirst( $search->alert_frequency ) ); ?> alerts</span> <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'staffswap_delete_search' => $search->id ), home_url( '/search/' ) ), 'staffswap_delete_search_' . $search->id ) ); ?>">Delete</a></p><?php endforeach; ?></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_saved_searches', 'staffswap_saved_searches_shortcode' );

function staffswap_delete_saved_search() {
    if ( ! is_user_logged_in() || empty( $_GET['staffswap_delete_search'] ) ) { return; }
    $search_id = absint( $_GET['staffswap_delete_search'] );
    if ( ! $search_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'staffswap_delete_search_' . $search_id ) ) { return; }
    global $wpdb;
    $wpdb->delete( staffswap_db_table( 'saved_searches' ), array( 'id' => $search_id, 'user_id' => get_current_user_id() ), array( '%d', '%d' ) );
    wp_safe_redirect( remove_query_arg( array( 'staffswap_delete_search', '_wpnonce' ) ) );
    exit;
}
add_action( 'init', 'staffswap_delete_saved_search' );

function staffswap_send_saved_search_alerts() {
    global $wpdb;
    $searches = $wpdb->get_results( 'SELECT * FROM ' . staffswap_db_table( 'saved_searches' ) );
    foreach ( $searches as $search ) {
        $last_alert = $search->last_notified_at ? strtotime( $search->last_notified_at . ' UTC' ) : 0;
        $interval = 'daily' === $search->alert_frequency ? DAY_IN_SECONDS : WEEK_IN_SECONDS;
        if ( $last_alert && time() - $last_alert < $interval ) { continue; }
        $filters = json_decode( $search->filters, true );
        $args = array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'posts_per_page' => 1, 'date_query' => $last_alert ? array( array( 'after' => gmdate( 'Y-m-d H:i:s', $last_alert ), 'inclusive' => false ) ) : array(), 'meta_query' => array( 'relation' => 'AND' ) );
        foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer' ) as $key ) { if ( ! empty( $filters[ $key ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $key, 'value' => $filters[ $key ], 'compare' => 'LIKE' ); } }
        foreach ( array( 'verified', 'housing', 'urgent' ) as $key ) { if ( ! empty( $filters[ $key ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $key, 'value' => '1' ); } }
        foreach ( array( 'min_experience' => '>=', 'max_experience' => '<=' ) as $key => $compare ) { if ( isset( $filters[ $key ] ) && '' !== $filters[ $key ] ) { $args['meta_query'][] = array( 'key' => '_staffswap_experience', 'value' => absint( $filters[ $key ] ), 'type' => 'NUMERIC', 'compare' => $compare ); } }
        $results = new WP_Query( $args );
        $user = get_userdata( $search->user_id );
        if ( $results->have_posts() && $user && $user->user_email ) { wp_mail( $user->user_email, 'New StaffSwap listings: ' . $search->name, 'New swap listings match your saved search. View them at ' . add_query_arg( is_array( $filters ) ? $filters : array(), home_url( '/search/' ) ) ); }
        $wpdb->update( staffswap_db_table( 'saved_searches' ), array( 'last_notified_at' => current_time( 'mysql', true ) ), array( 'id' => $search->id ), array( '%s' ), array( '%d' ) );
    }
}
add_action( 'staffswap_saved_search_alerts', 'staffswap_send_saved_search_alerts' );
add_action( 'init', function() { if ( ! wp_next_scheduled( 'staffswap_saved_search_alerts' ) ) { wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'staffswap_saved_search_alerts' ); } } );

function staffswap_saved_listing_ids() { return is_user_logged_in() ? array_map( 'absint', (array) get_user_meta( get_current_user_id(), 'staffswap_saved_listings', true ) ) : array(); }
function staffswap_toggle_saved_listing() { if ( ! is_user_logged_in() ) { wp_safe_redirect( wp_login_url( wp_get_referer() ?: home_url( '/' ) ) ); exit; } $listing_id = absint( $_GET['listing_id'] ?? 0 ); if ( ! $listing_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'staffswap_save_listing_' . $listing_id ) ) { wp_die( 'Invalid save request.' ); } $saved = staffswap_saved_listing_ids(); if ( in_array( $listing_id, $saved, true ) ) { $saved = array_values( array_diff( $saved, array( $listing_id ) ) ); } else { $saved[] = $listing_id; } update_user_meta( get_current_user_id(), 'staffswap_saved_listings', $saved ); wp_safe_redirect( wp_get_referer() ?: get_permalink( $listing_id ) ); exit; }
add_action( 'init', function() { if ( isset( $_GET['staffswap_toggle_saved'] ) ) { staffswap_toggle_saved_listing(); } } );
function staffswap_saved_shortcode() { if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Sign in to view saved listings.</p></div>'; } $ids = staffswap_saved_listing_ids(); if ( ! $ids ) { return '<div class="panel"><h2>No saved listings yet</h2><p class="muted">Save a promising match from the marketplace and it will appear here.</p></div>'; } $query = new WP_Query( array( 'post_type' => 'swap_listing', 'post__in' => $ids, 'posts_per_page' => -1, 'orderby' => 'post__in' ) ); ob_start(); ?><div class="listing-list"><?php while ( $query->have_posts() ) : $query->the_post(); echo staffswap_listing_card( get_the_ID() ); endwhile; wp_reset_postdata(); ?></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_saved_listings', 'staffswap_saved_shortcode' );
function staffswap_save_button_shortcode( $atts ) { $atts = shortcode_atts( array( 'id' => get_the_ID(), 'label' => 'Save listing' ), $atts, 'staffswap_save_button' ); $listing_id = absint( $atts['id'] ); if ( ! $listing_id || ! is_user_logged_in() ) { return ''; } $saved = in_array( $listing_id, staffswap_saved_listing_ids(), true ); $url = wp_nonce_url( add_query_arg( array( 'staffswap_toggle_saved' => '1', 'listing_id' => $listing_id ), home_url( '/' ) ), 'staffswap_save_listing_' . $listing_id ); return '<a class="button button--outline staffswap-save-button" href="' . esc_url( $url ) . '">' . esc_html( $saved ? 'Saved listing' : $atts['label'] ) . '</a>'; }
add_shortcode( 'staffswap_save_button', 'staffswap_save_button_shortcode' );

function staffswap_planner_page() {
    if ( ! get_page_by_path( 'relocation-planner' ) ) {
        wp_insert_post( array( 'post_title' => 'Relocation Planner', 'post_name' => 'relocation-planner', 'post_content' => '[staffswap_relocation_planner]', 'post_status' => 'publish', 'post_type' => 'page' ) );
    }
}
register_activation_hook( __FILE__, 'staffswap_planner_page' );
add_action( 'admin_init', 'staffswap_planner_page' );

function staffswap_relocation_planner_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<div class="panel"><p>Please sign in to use your relocation planner.</p></div>';
    }
    $phases = array(
        'ministry_approvals' => array( 'Official mutual transfer letter co-signed', 'PEO / PHD approval minute received', 'Public Service Commission approval letter issued' ),
        'administrative_clearance' => array( 'Station internal clearance form signed', 'Last Pay Certificate requested from Payroll' ),
        'logistics_housing' => array( 'Institutional housing handover inspection completed', 'Transportation and packing arranged', 'Utility bills cleared' ),
        'station_reporting' => array( 'Report to new district office or superintendent', 'Introduction to department head and duty roster' ),
    );
    $user_id = get_current_user_id();
    if ( isset( $_POST['staffswap_save_planner'] ) && check_admin_referer( 'staffswap_save_planner', 'staffswap_planner_nonce' ) ) {
        $completed = array_map( 'sanitize_key', (array) wp_unslash( $_POST['planner_completed'] ?? array() ) );
        update_user_meta( $user_id, 'staffswap_relocation_planner', $completed );
    }
    $completed = (array) get_user_meta( $user_id, 'staffswap_relocation_planner', true );
    ob_start(); ?><section class="content-form"><div class="page-heading"><div><p class="eyebrow">YOUR TRANSFER PLAN</p><h1>Relocation &amp; handover planner</h1></div></div><form method="post" class="panel"><?php foreach ( $phases as $phase => $tasks ) : ?><section style="margin-bottom:24px"><h2><?php echo esc_html( ucwords( str_replace( '_', ' ', $phase ) ) ); ?></h2><?php foreach ( $tasks as $index => $task ) : $key = $phase . '_' . $index; ?><label class="check"><input type="checkbox" name="planner_completed[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $completed, true ) ); ?>> <?php echo esc_html( $task ); ?></label><?php endforeach; ?></section><?php endforeach; wp_nonce_field( 'staffswap_save_planner', 'staffswap_planner_nonce' ); ?><input type="submit" name="staffswap_save_planner" value="Save progress"></form></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_relocation_planner', 'staffswap_relocation_planner_shortcode' );

function staffswap_documents_page() {
    if ( ! get_page_by_path( 'official-letters' ) ) {
        wp_insert_post( array( 'post_title' => 'Official Transfer Letters', 'post_name' => 'official-letters', 'post_content' => '[staffswap_transfer_letter]', 'post_status' => 'publish', 'post_type' => 'page' ) );
    }
}
register_activation_hook( __FILE__, 'staffswap_documents_page' );
add_action( 'admin_init', 'staffswap_documents_page' );

function staffswap_accepted_offers_for_user( $user_id ) {
    if ( ! post_type_exists( 'staffswap_offer' ) ) {
        return array();
    }
    $offers = get_posts( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
    return array_filter( $offers, function( $offer ) use ( $user_id ) {
        return 'accepted' === get_post_meta( $offer->ID, '_staffswap_offer_status', true ) && ( (int) $offer->post_author === $user_id || (int) get_post_meta( $offer->ID, '_staffswap_offer_recipient', true ) === $user_id );
    } );
}

function staffswap_transfer_letter_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<div class="panel"><p>Please sign in to generate transfer letters.</p></div>';
    }
    $user_id = get_current_user_id();
    $offers = staffswap_accepted_offers_for_user( $user_id );
    if ( ! $offers ) {
        return '<div class="panel"><h2>Official transfer letters</h2><p class="muted">A letter becomes available after a formal swap offer is accepted.</p></div>';
    }
    $selected_offer_id = absint( $_POST['offer_id'] ?? $_GET['offer_id'] ?? $offers[0]->ID );
    $offer = get_post( $selected_offer_id );
    if ( ! $offer || ! in_array( $offer, $offers, true ) ) {
        $offer = $offers[0];
        $selected_offer_id = $offer->ID;
    }
    $authority = sanitize_text_field( wp_unslash( $_POST['authority'] ?? 'Permanent Secretary' ) );
    $justification = sanitize_textarea_field( wp_unslash( $_POST['justification'] ?? 'mutual relocation and continuity of public service delivery' ) );
    $listing_id = (int) get_post_meta( $selected_offer_id, '_staffswap_offer_listing', true );
    $listing = get_post( $listing_id );
    $partner_id = (int) $offer->post_author === $user_id ? (int) get_post_meta( $selected_offer_id, '_staffswap_offer_recipient', true ) : (int) $offer->post_author;
    $user = get_userdata( $user_id );
    $partner = get_userdata( $partner_id );
    ob_start(); ?><section class="content-form"><div class="page-heading"><div><p class="eyebrow">DOCUMENT HUB</p><h1>Official transfer letter</h1></div></div><form method="post" class="panel" style="margin-bottom:24px"><div class="field"><label for="offer_id">Accepted offer</label><select id="offer_id" name="offer_id"><?php foreach ( $offers as $available_offer ) : ?><option value="<?php echo esc_attr( $available_offer->ID ); ?>" <?php selected( $available_offer->ID, $selected_offer_id ); ?>><?php echo esc_html( get_the_title( $available_offer ) ); ?></option><?php endforeach; ?></select></div><div class="field"><label for="authority">Administrative authority</label><select id="authority" name="authority"><option <?php selected( $authority, 'Permanent Secretary' ); ?>>Permanent Secretary</option><option <?php selected( $authority, 'Provincial Health Director (PHD)' ); ?>>Provincial Health Director (PHD)</option><option <?php selected( $authority, 'Provincial Education Officer (PEO)' ); ?>>Provincial Education Officer (PEO)</option><option <?php selected( $authority, 'District Education Board Secretary (DEBS)' ); ?>>District Education Board Secretary (DEBS)</option></select></div><div class="field"><label for="justification">Primary justification</label><textarea id="justification" name="justification" rows="3"><?php echo esc_textarea( $justification ); ?></textarea></div><input type="submit" value="Generate letter"></form><article class="panel staffswap-letter"><p><?php echo esc_html( wp_date( get_option( 'date_format' ) ) ); ?></p><p>To: <?php echo esc_html( $authority ); ?></p><p><strong>RE: REQUEST FOR MUTUAL TRANSFER</strong></p><p>I, <?php echo esc_html( $user->display_name ); ?> (Man-Number: <?php echo esc_html( get_user_meta( $user_id, 'staffswap_man_number', true ) ?: 'Not provided' ); ?>), respectfully request a mutual transfer with <?php echo esc_html( $partner ? $partner->display_name : 'the confirmed exchange officer' ); ?>.</p><p>The proposed transfer is between <?php echo esc_html( get_user_meta( $user_id, 'staffswap_location', true ) ?: 'my current station' ); ?> and <?php echo esc_html( $listing ? get_post_meta( $listing_id, '_staffswap_current_location', true ) : 'the partner station' ); ?>, effective <?php echo esc_html( get_post_meta( $selected_offer_id, '_staffswap_offer_effective_date', true ) ); ?>, subject to all required approvals.</p><p>This request is made on the basis of <?php echo esc_html( $justification ); ?>. Both officers confirm their mutual agreement and will comply with applicable ministry and commission procedures.</p><p>Yours faithfully,</p><p>____________________________<br><?php echo esc_html( $user->display_name ); ?></p><p>____________________________<br><?php echo esc_html( $partner ? $partner->display_name : '' ); ?></p></article><p><button type="button" class="button button--primary" onclick="window.print()">Print / Save as PDF</button></p></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_transfer_letter', 'staffswap_transfer_letter_shortcode' );

function staffswap_member_dashboard_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<div class="panel content-form"><h2>Sign in to view your workspace</h2><a class="button button--primary" href="' . esc_url( home_url( '/sign-in/' ) ) . '">Sign in</a></div>';
    }
    global $wpdb;
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $listings = new WP_Query( array( 'post_type' => 'swap_listing', 'author' => $user_id, 'post_status' => array( 'publish', 'pending' ), 'posts_per_page' => 10 ) );
    $match_count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . staffswap_db_table( 'matches' ) . ' m INNER JOIN ' . $wpdb->posts . ' p ON p.ID = m.listing_id WHERE p.post_author = %d AND m.status = %s', $user_id, 'suggested' ) );
    $offer_count = 0;
    if ( post_type_exists( 'staffswap_offer' ) ) {
        $offer_count = (int) ( new WP_Query( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'meta_key' => '_staffswap_offer_recipient', 'meta_value' => $user_id, 'meta_query' => array( array( 'key' => '_staffswap_offer_status', 'value' => 'proposed' ) ), 'fields' => 'ids', 'posts_per_page' => -1 ) ) )->found_posts;
    }
    $verification = get_user_meta( $user_id, 'staffswap_verified_status', true ) ?: 'unverified';
    ob_start(); ?>
    <div class="member-workspace"><header class="member-workspace__header"><div><p class="eyebrow">MEMBER WORKSPACE</p><h1><?php echo esc_html( $user->display_name ); ?></h1><p class="muted"><?php echo esc_html( get_user_meta( $user_id, 'staffswap_profession', true ) ?: 'Complete your professional profile' ); ?> · <?php echo esc_html( get_user_meta( $user_id, 'staffswap_location', true ) ?: 'Location pending' ); ?></p></div><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Publish Direct Swap Listing</a></header><nav class="member-workspace__nav" aria-label="Member workspace"><a href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>">Dashboard</a><a href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>">Search Swaps</a><a href="<?php echo esc_url( home_url( '/messages/' ) ); ?>">Messages</a><a href="<?php echo esc_url( home_url( '/offers/' ) ); ?>">Offers</a><a href="<?php echo esc_url( home_url( '/verification/' ) ); ?>">Verification</a><a href="<?php echo esc_url( home_url( '/relocation-planner/' ) ); ?>">Planner</a><a href="<?php echo esc_url( home_url( '/official-letters/' ) ); ?>">Documents</a></nav><section class="member-metrics"><article><span>Active listings</span><strong><?php echo esc_html( $listings->found_posts ); ?></strong><small>Published or in review</small></article><article><span>Reciprocal matches</span><strong><?php echo esc_html( $match_count ); ?></strong><small>Routes ready to compare</small></article><article><span>Incoming offers</span><strong><?php echo esc_html( $offer_count ); ?></strong><small>Awaiting your response</small></article><article><span>Verification</span><strong><?php echo esc_html( ucfirst( $verification ) ); ?></strong><small>Profile trust status</small></article></section><div class="member-workspace__grid"><section class="panel"><div class="section-heading"><div><p class="eyebrow">YOUR LISTINGS</p><h2>Active swap requests</h2></div><a class="text-link" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Create listing</a></div><?php if ( $listings->have_posts() ) : ?><div class="member-listings"><?php while ( $listings->have_posts() ) : $listings->the_post(); ?><article><div><strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong><span><?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?></span></div><a href="<?php the_permalink(); ?>">Manage</a></article><?php endwhile; wp_reset_postdata(); ?></div><?php else : ?><p class="muted">No active listings yet. Publish your route to activate the matchmaker.</p><?php endif; ?></section><aside class="panel member-workspace__next"><p class="eyebrow">NEXT ACTION</p><?php if ( 'verified' !== $verification ) : ?><h2>Verify your profile</h2><p class="muted">Submit your NRC and payslip to build trust with prospective swap partners.</p><a class="button button--outline" href="<?php echo esc_url( home_url( '/verification/' ) ); ?>">Open verification</a><?php elseif ( $offer_count ) : ?><h2>Review a formal offer</h2><p class="muted">A colleague is waiting for your decision on a proposed swap.</p><a class="button button--outline" href="<?php echo esc_url( home_url( '/offers/' ) ); ?>">Review offers</a><?php else : ?><h2>Find your reciprocal route</h2><p class="muted">Publish a listing and compare route-compatible public service colleagues.</p><a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>">Search swaps</a><?php endif; ?></aside></div><?php echo do_shortcode( '[staffswap_matches]' ); ?></div>
    <?php return ob_get_clean();
}
remove_shortcode( 'staffswap_dashboard' );
add_shortcode( 'staffswap_dashboard', 'staffswap_member_dashboard_shortcode' );

function staffswap_profile_workspace_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<div class="panel content-form"><h2>Sign in to view your workspace</h2><a class="button button--primary" href="' . esc_url( home_url( '/sign-in/' ) ) . '">Sign in</a></div>';
    }
    $user = wp_get_current_user();
    $tab = sanitize_key( wp_unslash( $_GET['profile_tab'] ?? 'dashboard' ) );
    $tabs = array( 'dashboard', 'search', 'messages', 'offers', 'verification', 'planner', 'documents' );
    if ( ! in_array( $tab, $tabs, true ) ) { $tab = 'dashboard'; }
    $content = '';
    if ( 'search' === $tab ) { $content = do_shortcode( '[staffswap_search]' ); }
    elseif ( 'messages' === $tab ) { $content = do_shortcode( '[staffswap_inbox]' ); }
    elseif ( 'offers' === $tab ) { $content = do_shortcode( '[staffswap_offers]' ); }
    elseif ( 'verification' === $tab ) { $content = do_shortcode( '[staffswap_verification]' ); }
    elseif ( 'planner' === $tab ) { $content = do_shortcode( '[staffswap_relocation_planner]' ); }
    elseif ( 'documents' === $tab ) { $content = do_shortcode( '[staffswap_transfer_letter]' ); }
    else {
        global $wpdb;
        $query = new WP_Query( array( 'post_type' => 'swap_listing', 'author' => get_current_user_id(), 'post_status' => array( 'publish', 'pending' ), 'posts_per_page' => 10 ) );
        $match_count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . staffswap_db_table( 'matches' ) . ' m INNER JOIN ' . $wpdb->posts . ' p ON p.ID = m.listing_id WHERE p.post_author = %d AND m.status = %s', get_current_user_id(), 'suggested' ) );
        $offer_count = post_type_exists( 'staffswap_offer' ) ? (int) ( new WP_Query( array( 'post_type' => 'staffswap_offer', 'post_status' => 'publish', 'meta_key' => '_staffswap_offer_recipient', 'meta_value' => get_current_user_id(), 'meta_query' => array( array( 'key' => '_staffswap_offer_status', 'value' => 'proposed' ) ), 'fields' => 'ids', 'posts_per_page' => -1 ) ) )->found_posts : 0;
        $verification = get_user_meta( get_current_user_id(), 'staffswap_verified_status', true ) ?: 'unverified';
        ob_start(); ?><section class="member-metrics"><article><span>Active listings</span><strong><?php echo esc_html( $query->found_posts ); ?></strong><small>Published or in review</small></article><article><span>Reciprocal matches</span><strong><?php echo esc_html( $match_count ); ?></strong><small>Routes ready to compare</small></article><article><span>Incoming offers</span><strong><?php echo esc_html( $offer_count ); ?></strong><small>Awaiting your response</small></article><article><span>Verification</span><strong><?php echo esc_html( ucfirst( $verification ) ); ?></strong><small>Profile trust status</small></article></section><section class="panel"><div class="section-heading"><div><p class="eyebrow">YOUR LISTINGS</p><h2>Active swap requests</h2></div><a class="text-link" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Create listing</a></div><?php if ( $query->have_posts() ) : ?><div class="member-listings"><?php while ( $query->have_posts() ) : $query->the_post(); ?><article><div><strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong><span><?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?></span></div><a href="<?php the_permalink(); ?>">Manage</a></article><?php endwhile; wp_reset_postdata(); ?></div><?php else : ?><p class="muted">No active listings yet. Publish your route to activate the matchmaker.</p><?php endif; ?></section><?php $content = ob_get_clean();
    }
    $labels = array( 'dashboard' => 'Dashboard', 'search' => 'Search Swaps', 'messages' => 'Messages', 'offers' => 'Offers', 'verification' => 'Verification', 'planner' => 'Planner', 'documents' => 'Documents' );
    ob_start(); ?><div class="member-workspace member-workspace--tabs"><header class="member-workspace__header"><div><p class="eyebrow">MEMBER WORKSPACE</p><h1><?php echo esc_html( $user->display_name ); ?></h1><p class="muted"><?php echo esc_html( get_user_meta( $user->ID, 'staffswap_profession', true ) ?: 'Complete your professional profile' ); ?> · <?php echo esc_html( get_user_meta( $user->ID, 'staffswap_location', true ) ?: 'Location pending' ); ?></p></div><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Publish Direct Swap Listing</a></header><nav class="member-workspace__nav" aria-label="Member workspace"><?php foreach ( $labels as $key => $label ) : ?><a href="<?php echo esc_url( add_query_arg( 'profile_tab', $key, home_url( '/my-profile/' ) ) ); ?>" class="<?php echo $tab === $key ? 'is-active' : ''; ?>" aria-current="<?php echo $tab === $key ? 'page' : 'false'; ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?></nav><div class="member-workspace__pane"><?php if ( 'dashboard' === $tab && shortcode_exists( 'staffswap_profile_completion' ) ) { echo do_shortcode( '[staffswap_profile_completion]' ); } ?><?php echo $content; ?></div></div><?php return ob_get_clean();
}
remove_shortcode( 'staffswap_dashboard' );
add_shortcode( 'staffswap_dashboard', 'staffswap_profile_workspace_shortcode' );
