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

function staffswap_listing_fields() { return array( 'profession' => 'Profession', 'current_location' => 'Current Location', 'current_employer' => 'Current Employer', 'desired_location' => 'Desired Location', 'desired_employer' => 'Desired Employer', 'experience' => 'Experience (years)', 'match_score' => 'Match Score', 'housing' => 'Housing available', 'urgent' => 'Urgent listing', 'verified' => 'Verified professional' ); }
function staffswap_add_meta_box() { add_meta_box( 'staffswap_listing_details', 'Exchange Details', 'staffswap_meta_box', 'swap_listing', 'normal', 'high' ); }
add_action( 'add_meta_boxes', 'staffswap_add_meta_box' );
function staffswap_meta_box( $post ) {
    wp_nonce_field( 'staffswap_save_listing', 'staffswap_listing_nonce' );
    echo '<div class="staffswap-admin-fields">';
    foreach ( staffswap_listing_fields() as $key => $label ) {
        $value = get_post_meta( $post->ID, '_staffswap_' . $key, true );
        $type = in_array( $key, array( 'housing', 'urgent', 'verified' ), true ) ? 'checkbox' : 'text';
        echo '<p><label><strong>' . esc_html( $label ) . '</strong><br><input type="' . esc_attr( $type ) . '" name="staffswap_' . esc_attr( $key ) . '" value="' . esc_attr( $type === 'checkbox' ? '1' : $value ) . '" ' . checked( $value, '1', false ) . '></label></p>';
    }
    echo '</div>';
}
function staffswap_save_meta( $post_id ) {
    if ( ! isset( $_POST['staffswap_listing_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_listing_nonce'] ) ), 'staffswap_save_listing' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
    foreach ( staffswap_listing_fields() as $key => $label ) {
        $value = isset( $_POST['staffswap_' . $key] ) ? sanitize_text_field( wp_unslash( $_POST['staffswap_' . $key] ) ) : '';
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

function staffswap_refresh_listing_matches( $listing_id ) {
    global $wpdb;
    if ( 'publish' !== get_post_status( $listing_id ) ) {
        staffswap_delete_listing_matches( $listing_id );
        return;
    }
    $listing_meta = array();
    foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer', 'desired_employer' ) as $field ) {
        $listing_meta[ $field ] = staffswap_normalize_match_value( get_post_meta( $listing_id, '_staffswap_' . $field, true ) );
    }
    if ( ! $listing_meta['current_location'] || ! $listing_meta['desired_location'] || ! $listing_meta['profession'] ) {
        staffswap_delete_listing_matches( $listing_id );
        return;
    }
    staffswap_delete_listing_matches( $listing_id );
    $candidates = get_posts( array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'post__not_in' => array( $listing_id ), 'posts_per_page' => -1, 'fields' => 'ids' ) );
    $now = current_time( 'mysql', true );
    foreach ( $candidates as $candidate_id ) {
        $candidate_meta = array();
        foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer', 'desired_employer' ) as $field ) {
            $candidate_meta[ $field ] = staffswap_normalize_match_value( get_post_meta( $candidate_id, '_staffswap_' . $field, true ) );
        }
        if ( $listing_meta['current_location'] !== $candidate_meta['desired_location'] || $listing_meta['desired_location'] !== $candidate_meta['current_location'] || $listing_meta['profession'] !== $candidate_meta['profession'] ) {
            continue;
        }
        $employer_match = $listing_meta['current_employer'] === $candidate_meta['desired_employer'] && $listing_meta['desired_employer'] === $candidate_meta['current_employer'];
        $score = $employer_match ? 100 : 90;
        foreach ( array( array( $listing_id, $candidate_id ), array( $candidate_id, $listing_id ) ) as $pair ) {
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

function staffswap_matches_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '';
    }
    global $wpdb;
    $matches = $wpdb->get_results( $wpdb->prepare( 'SELECT m.candidate_listing_id, m.score FROM ' . staffswap_db_table( 'matches' ) . ' m INNER JOIN ' . $wpdb->posts . ' p ON p.ID = m.listing_id WHERE p.post_author = %d AND p.post_status = %s AND m.status = %s ORDER BY m.score DESC', get_current_user_id(), 'publish', 'suggested' ) );
    ob_start(); ?>
    <section class="panel" style="margin-top:16px"><h2>Reciprocal matches</h2><?php if ( $matches ) : ?><div class="listing-list"><?php foreach ( $matches as $match ) : ?><div><p><strong><?php echo esc_html( number_format_i18n( $match->score, 0 ) ); ?>% match</strong></p><?php echo staffswap_listing_card( $match->candidate_listing_id ); ?></div><?php endforeach; ?></div><?php else : ?><p class="muted">Publish a swap listing to receive reciprocal matches.</p><?php endif; ?></section>
    <?php return ob_get_clean();
}
add_shortcode( 'staffswap_matches', 'staffswap_matches_shortcode' );

function staffswap_listing_card( $post_id ) {
    $meta = array();
    foreach ( staffswap_listing_fields() as $key => $label ) { $meta[ $key ] = get_post_meta( $post_id, '_staffswap_' . $key, true ); }
    $name = get_the_title( $post_id ); $score = $meta['match_score'] ?: '90'; $profession = $meta['profession'] ?: 'Professional';
    ob_start(); ?>
    <article class="listing-card"><div class="listing-main"><div class="person"><div class="avatar"><?php echo esc_html( strtoupper( substr( $name, 0, 1 ) ) ); ?></div><div><h3><?php echo esc_html( $name ); ?><?php if ( $meta['verified'] ) : ?> <span class="verified">&#10003; Verified</span><?php endif; ?></h3><p style="color:#005f2e;font-weight:600"><?php echo esc_html( $profession ); ?></p><p class="muted"><?php echo esc_html( $meta['experience'] ?: '-' ); ?> years experience</p></div></div><div class="swap-route"><div class="route"><small>Current</small><strong><?php echo esc_html( $meta['current_employer'] ?: 'Not specified' ); ?></strong><span><?php echo esc_html( $meta['current_location'] ?: 'Location pending' ); ?></span></div><div class="swap-icon">&#8596;</div><div class="route route--desired"><small>Desired</small><strong><?php echo esc_html( $meta['desired_employer'] ?: 'Not specified' ); ?></strong><span><?php echo esc_html( $meta['desired_location'] ?: 'Location pending' ); ?></span></div></div><div class="match"><strong><?php echo esc_html( $score ); ?>%</strong><small><?php echo (int) $score >= 94 ? 'Excellent' : 'Good'; ?> Match</small></div></div><div class="listing-meta"><div class="tags"><?php if ( $meta['verified'] ) : ?><span class="tag tag--success">Verified</span><?php endif; ?><?php if ( $meta['urgent'] ) : ?><span class="tag tag--urgent">Urgent</span><?php endif; ?><span class="tag"><?php echo $meta['housing'] ? 'Housing available' : 'Housing not included'; ?></span></div><div class="listing-actions"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">View profile</a></div></div></article>
    <?php return ob_get_clean();
}

function staffswap_listings_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'limit' => 10 ), $atts, 'staffswap_listings' );
    $args = array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'posts_per_page' => (int) $atts['limit'], 'paged' => max( 1, get_query_var( 'paged', 1 ) ) );
    foreach ( array( 'profession', 'current_location', 'desired_location', 'current_employer' ) as $filter ) { if ( ! empty( $_GET[ $filter ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $filter, 'value' => sanitize_text_field( wp_unslash( $_GET[ $filter ] ) ), 'compare' => 'LIKE' ); } }
    foreach ( array( 'verified', 'housing', 'urgent' ) as $flag ) { if ( ! empty( $_GET[ $flag ] ) ) { $args['meta_query'][] = array( 'key' => '_staffswap_' . $flag, 'value' => '1' ); } }
    $query = new WP_Query( $args ); ob_start(); ?>
    <div class="marketplace"><aside class="panel"><h2>Filter listings</h2><form method="get"><div class="field"><label for="current_location">Current location</label><input id="current_location" name="current_location" value="<?php echo esc_attr( $_GET['current_location'] ?? '' ); ?>" placeholder="e.g. Lusaka"></div><div class="field"><label for="desired_location">Desired location</label><input id="desired_location" name="desired_location" value="<?php echo esc_attr( $_GET['desired_location'] ?? '' ); ?>" placeholder="e.g. Copperbelt"></div><div class="field"><label for="profession">Profession</label><input id="profession" name="profession" value="<?php echo esc_attr( $_GET['profession'] ?? '' ); ?>" placeholder="e.g. Teacher"></div><?php foreach ( array( 'verified' => 'Verified users only', 'housing' => 'Housing available', 'urgent' => 'Urgent swaps only' ) as $key => $label ) : ?><label class="check"><input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $_GET[ $key ] ) ); ?>><?php echo esc_html( $label ); ?></label><?php endforeach; ?><div class="stack" style="margin-top:20px"><input type="submit" value="Apply filters"><a class="button button--outline" style="text-align:center" href="<?php echo esc_url( get_permalink() ); ?>">Clear filters</a></div></form></aside><section><div class="page-heading"><div><p class="eyebrow">THE MARKETPLACE</p><h1>Swap Listings</h1><p class="muted"><?php echo esc_html( $query->found_posts ); ?> swap requests matching your search</p></div></div><div class="notice"><div><h2>Get better matches</h2><p class="muted">Create a swap post to connect with professionals looking for your institution.</p></div><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Create swap post</a></div><div class="listing-list"><?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); echo staffswap_listing_card( get_the_ID() ); endwhile; wp_reset_postdata(); else : ?><div class="panel"><h2>No listings found</h2><p class="muted">Try widening your filters or create the first listing for this route.</p></div><?php endif; ?></div></section></div>
    <?php return ob_get_clean();
}
add_shortcode( 'staffswap_listings', 'staffswap_listings_shortcode' );

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
    <div class="account-page"><div class="account-intro"><p class="eyebrow">STEP <?php echo esc_html( $step ); ?> OF 3</p><h1><?php echo esc_html( 1 === $step ? 'Account Identity' : ( 2 === $step ? 'Professional Status' : 'Relocation Goals' ) ); ?></h1></div><form method="post" class="staffswap-register-form"><?php echo $message; ?><input type="hidden" name="staffswap_registration" value="<?php echo esc_attr( $token ); ?>"><?php if ( 1 === $step ) : ?><div class="field"><label for="full_name">Official Full Name</label><input id="full_name" name="full_name" value="<?php echo esc_attr( $form_data['full_name'] ?? '' ); ?>" required></div><div class="field"><label for="email">Email Address</label><input id="email" name="email" type="email" value="<?php echo esc_attr( $form_data['email'] ?? '' ); ?>" required></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" minlength="8" required></div><?php elseif ( 2 === $step ) : ?><div class="field"><label for="profession">Profession / Cadre</label><input id="profession" name="profession" value="<?php echo esc_attr( $form_data['profession'] ?? '' ); ?>" required></div><div class="field"><label for="employer">Employer / Institution Type</label><input id="employer" name="employer" value="<?php echo esc_attr( $form_data['employer'] ?? '' ); ?>" required></div><div class="field"><label for="years_service">Years of Service</label><input id="years_service" name="years_service" type="number" min="0" value="<?php echo esc_attr( $form_data['years_service'] ?? '' ); ?>" required></div><?php else : ?><div class="field"><label for="current_location">Current Province & Town</label><input id="current_location" name="current_location" value="<?php echo esc_attr( $form_data['current_location'] ?? '' ); ?>" required></div><div class="field"><label for="desired_location">Desired Province & Town</label><input id="desired_location" name="desired_location" value="<?php echo esc_attr( $form_data['desired_location'] ?? '' ); ?>" required></div><label class="check"><input type="checkbox" name="staff_housing" value="1" <?php checked( ! empty( $form_data['staff_housing'] ) ); ?>> I have staff accommodation available to handover</label><?php endif; ?><input type="hidden" name="staffswap_register_step" value="1"><?php wp_nonce_field( 'staffswap_register_step_' . $step, 'staffswap_register_nonce' ); ?><div style="display:flex;gap:10px;margin-top:20px"><input type="submit" value="<?php echo esc_attr( 3 === $step ? 'Complete & Get Matched!' : 'Continue' ); ?>"><a class="button button--outline" href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html( $step > 1 ? 'Back' : 'Cancel' ); ?></a></div></form></div>
    <?php return ob_get_clean();
}
remove_shortcode( 'staffswap_register' );
add_shortcode( 'staffswap_register', 'staffswap_register_wizard_v2_shortcode' );

function staffswap_dashboard_shortcode() { if ( ! is_user_logged_in() ) { return '<div class="panel content-form"><h2>Sign in to view your profile</h2><a class="button button--primary" href="' . esc_url( home_url( '/sign-in/' ) ) . '">Sign in</a></div>'; } $user = wp_get_current_user(); $query = new WP_Query( array( 'post_type' => 'swap_listing', 'author' => get_current_user_id(), 'post_status' => array( 'publish', 'pending' ), 'posts_per_page' => 10 ) ); ob_start(); ?><div class="content-form"><div class="page-heading"><div><p class="eyebrow">MEMBER AREA</p><h1><?php echo esc_html( $user->display_name ); ?></h1><p class="muted">Manage your profile and swap requests.</p></div><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>">Create listing</a></div><div class="panel"><h2>Your swap requests</h2><?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?><p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span class="muted">(<?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?>)</span></p><?php endwhile; wp_reset_postdata(); else : ?><p class="muted">You have not created a swap request yet.</p><?php endif; ?></div><?php echo do_shortcode( '[staffswap_matches]' ); ?></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_dashboard', 'staffswap_dashboard_shortcode' );

function staffswap_search_shortcode() { ob_start(); ?><div class="content-form"><div class="page-heading"><div><p class="eyebrow">SEARCH THE NETWORK</p><h1>Find your next workplace</h1><p class="muted">Search by profession, current location, or desired location.</p></div></div><?php echo do_shortcode( '[staffswap_listings]' ); ?></div><?php return ob_get_clean(); }
add_shortcode( 'staffswap_search', 'staffswap_search_shortcode' );

function staffswap_create_form_shortcode() {
    if ( ! is_user_logged_in() ) { return '<div class="panel content-form"><h2>Join the exchange network</h2><p>You need an account to publish a swap listing.</p><a class="button button--primary" href="' . esc_url( wp_registration_url() ) . '">Create an account</a></div>'; }
    if ( isset( $_POST['staffswap_create_listing'] ) && check_admin_referer( 'staffswap_create_listing', 'staffswap_create_nonce' ) ) {
        $post_id = wp_insert_post( array( 'post_type' => 'swap_listing', 'post_title' => sanitize_text_field( wp_unslash( $_POST['name'] ) ), 'post_content' => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ), 'post_status' => 'pending', 'post_author' => get_current_user_id() ), true );
        if ( ! is_wp_error( $post_id ) ) { foreach ( staffswap_listing_fields() as $key => $label ) { if ( isset( $_POST[ $key ] ) ) { update_post_meta( $post_id, '_staffswap_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ); } } return '<div class="panel content-form"><h2>Listing submitted for review</h2><p>We will verify the details before publishing your swap request.</p></div>'; }
    }
    $user = wp_get_current_user();
    $profile = array( 'name' => $user->display_name, 'profession' => get_user_meta( $user->ID, 'staffswap_profession', true ), 'current_employer' => get_user_meta( $user->ID, 'staffswap_employer', true ), 'current_location' => get_user_meta( $user->ID, 'staffswap_location', true ), 'experience' => get_user_meta( $user->ID, 'staffswap_years_service', true ), 'housing' => get_user_meta( $user->ID, 'staffswap_staff_housing', true ) );
    ob_start(); ?>
    <div class="panel content-form"><h1>Create a swap post</h1><p class="muted">Tell other professionals where you are and where you hope to go.</p><form method="post"><div class="form-grid"><div class="field"><label for="name">Your name</label><input id="name" name="name" value="<?php echo esc_attr( $profile['name'] ); ?>" required></div><div class="field"><label for="profession">Profession</label><input id="profession" name="profession" value="<?php echo esc_attr( $profile['profession'] ); ?>" required></div><div class="field"><label for="current_employer">Current employer</label><input id="current_employer" name="current_employer" value="<?php echo esc_attr( $profile['current_employer'] ); ?>" required></div><div class="field"><label for="current_location">Current location</label><input id="current_location" name="current_location" value="<?php echo esc_attr( $profile['current_location'] ); ?>" required></div><div class="field"><label for="desired_employer">Desired employer</label><input id="desired_employer" name="desired_employer" required></div><div class="field"><label for="desired_location">Desired location</label><input id="desired_location" name="desired_location" required></div><div class="field"><label for="experience">Years of experience</label><input id="experience" name="experience" type="number" min="0" value="<?php echo esc_attr( $profile['experience'] ); ?>" required></div><div class="field"><label for="housing">Housing</label><select id="housing" name="housing"><option value="">Not included</option><option value="1" <?php selected( $profile['housing'], '1' ); ?>>Available</option></select></div><div class="field full"><label for="notes">Additional details</label><textarea id="notes" name="notes" rows="5"></textarea></div></div><?php wp_nonce_field( 'staffswap_create_listing', 'staffswap_create_nonce' ); ?><input type="submit" name="staffswap_create_listing" value="Submit listing"></form></div>
    <?php return ob_get_clean();
}
add_shortcode( 'staffswap_create_form', 'staffswap_create_form_shortcode' );

function staffswap_record_event( $event_type, $object_id = 0, $payload = array(), $user_id = 0 ) { global $wpdb; $wpdb->insert( staffswap_db_table( 'events' ), array( 'user_id' => $user_id ? absint( $user_id ) : ( get_current_user_id() ?: null ), 'event_type' => sanitize_key( $event_type ), 'object_id' => $object_id ? absint( $object_id ) : null, 'payload' => wp_json_encode( $payload ), 'created_at' => current_time( 'mysql', true ) ), array( '%d', '%s', '%d', '%s', '%s' ) ); }
function staffswap_save_search_shortcode() { if ( ! is_user_logged_in() ) { return ''; } if ( isset( $_POST['staffswap_save_search'] ) && check_admin_referer( 'staffswap_save_search', 'staffswap_saved_search_nonce' ) ) { global $wpdb; $filters = array(); foreach ( array( 'profession', 'current_location', 'desired_location', 'verified', 'housing', 'urgent' ) as $key ) { if ( ! empty( $_REQUEST[ $key ] ) ) { $filters[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); } } $wpdb->insert( staffswap_db_table( 'saved_searches' ), array( 'user_id' => get_current_user_id(), 'name' => sanitize_text_field( wp_unslash( $_POST['search_name'] ?? 'My swap search' ) ), 'filters' => wp_json_encode( $filters ), 'alert_frequency' => 'weekly', 'created_at' => current_time( 'mysql', true ) ), array( '%d', '%s', '%s', '%s', '%s' ) ); staffswap_record_event( 'saved_search_created', 0, $filters ); } ob_start(); ?><form method="post" class="staffswap-save-search"><input name="search_name" placeholder="Name this search" aria-label="Search name"><input type="submit" name="staffswap_save_search" value="Save search"><?php wp_nonce_field( 'staffswap_save_search', 'staffswap_saved_search_nonce' ); ?></form><?php return ob_get_clean(); }
add_shortcode( 'staffswap_save_search', 'staffswap_save_search_shortcode' );

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