<?php
/**
 * Plugin Name: StaffSwap Profiles
 * Description: Member profile fields and profile display shortcode.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_profile_fields( $user ) { ?><h2>StaffExchangeHub Professional Profile</h2><table class="form-table"><tr><th><label for="staffswap_man_number">Employee Man-Number</label></th><td><input class="regular-text" name="staffswap_man_number" id="staffswap_man_number" placeholder="e.g. MH/00123" value="<?php echo esc_attr( get_user_meta( $user->ID, 'staffswap_man_number', true ) ); ?>"></td></tr><tr><th><label for="staffswap_profession">Profession / Cadre</label></th><td><select name="staffswap_profession" id="staffswap_profession"><option value="">Select profession</option><option value="registered_nurse" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'registered_nurse' ); ?>>Registered Nurse</option><option value="secondary_teacher" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'secondary_teacher' ); ?>>Secondary School Teacher</option><option value="primary_teacher" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'primary_teacher' ); ?>>Primary School Teacher</option><option value="clinical_officer" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'clinical_officer' ); ?>>Clinical Officer</option><option value="doctor" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'doctor' ); ?>>Medical Doctor</option><option value="pharmacist" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'pharmacist' ); ?>>Pharmacist</option><option value="police_officer" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'police_officer' ); ?>>Police Officer</option><option value="administrative_officer" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'administrative_officer' ); ?>>Administrative Officer</option><option value="other" <?php selected( get_user_meta( $user->ID, 'staffswap_profession', true ), 'other' ); ?>>Other</option></select></td></tr><tr><th><label for="staffswap_salary_scale">Salary Scale</label></th><td><select name="staffswap_salary_scale" id="staffswap_salary_scale"><option value="">Select salary scale</option><option value="a" <?php selected( get_user_meta( $user->ID, 'staffswap_salary_scale', true ), 'a' ); ?>>A</option><option value="b" <?php selected( get_user_meta( $user->ID, 'staffswap_salary_scale', true ), 'b' ); ?>>B</option><option value="c" <?php selected( get_user_meta( $user->ID, 'staffswap_salary_scale', true ), 'c' ); ?>>C</option><option value="g" <?php selected( get_user_meta( $user->ID, 'staffswap_salary_scale', true ), 'g' ); ?>>G</option><option value="j" <?php selected( get_user_meta( $user->ID, 'staffswap_salary_scale', true ), 'j' ); ?>>J</option><option value="l" <?php selected( get_user_meta( $user->ID, 'staffswap_salary_scale', true ), 'l' ); ?>>L</option></select></td></tr><tr><th><label for="staffswap_location">Current Province & Town</label></th><td><input class="regular-text" name="staffswap_location" id="staffswap_location" placeholder="e.g. Lusaka, Lusaka Province" value="<?php echo esc_attr( get_user_meta( $user->ID, 'staffswap_location', true ) ); ?>"></td></tr><tr><th><label for="staffswap_desired_location">Desired Province & Town</label></th><td><input class="regular-text" name="staffswap_desired_location" id="staffswap_desired_location" placeholder="e.g. Ndola, Copperbelt" value="<?php echo esc_attr( get_user_meta( $user->ID, 'staffswap_desired_location', true ) ); ?>"></td></tr><tr><th><label for="staffswap_employer">Current Employer / Institution</label></th><td><input class="regular-text" name="staffswap_employer" id="staffswap_employer" placeholder="e.g. Levy Mwanawasa University Teaching Hospital" value="<?php echo esc_attr( get_user_meta( $user->ID, 'staffswap_employer', true ) ); ?>"></td></tr><tr><th><label for="staffswap_years_service">Years of Service</label></th><td><input class="regular-text" name="staffswap_years_service" id="staffswap_years_service" type="number" min="0" value="<?php echo esc_attr( get_user_meta( $user->ID, 'staffswap_years_service', true ) ); ?>"></td></tr><tr><th><label for="staffswap_staff_housing">Staff Accommodation</label></th><td><label><input type="checkbox" name="staffswap_staff_housing" value="1" <?php checked( get_user_meta( $user->ID, 'staffswap_staff_housing', true ), '1' ); ?>> Staff quarters available for handover</label></td></tr><tr><th><label for="staffswap_professional_license">Professional License Number</label></th><td><input class="regular-text" name="staffswap_professional_license" id="staffswap_professional_license" placeholder="e.g. NMCZ/RN/12345" value="<?php echo esc_attr( get_user_meta( $user->ID, 'staffswap_professional_license', true ) ); ?>"></td></tr><tr><th><label for="staffswap_verified_status">Verification Status</label></th><td><select name="staffswap_verified_status" id="staffswap_verified_status"><option value="unverified" <?php selected( get_user_meta( $user->ID, 'staffswap_verified_status', true ) ?: 'unverified', 'unverified' ); ?>>Unverified</option><option value="pending" <?php selected( get_user_meta( $user->ID, 'staffswap_verified_status', true ), 'pending' ); ?>>Pending Review</option><option value="verified" <?php selected( get_user_meta( $user->ID, 'staffswap_verified_status', true ), 'verified' ); ?>>Verified Civil Servant</option></select></td></tr></table><?php }
add_action( 'show_user_profile', 'staffswap_profile_fields' ); add_action( 'edit_user_profile', 'staffswap_profile_fields' );
function staffswap_save_profile_fields( $user_id ) { if ( ! current_user_can( 'edit_user', $user_id ) ) { return; } $fields = array( 'man_number', 'profession', 'salary_scale', 'location', 'desired_location', 'employer', 'years_service', 'professional_license', 'verified_status' ); foreach ( $fields as $field ) { if ( isset( $_POST['staffswap_' . $field] ) ) { $value = 'staffswap_' . $field === 'staffswap_staff_housing' ? ( isset( $_POST['staffswap_staff_housing'] ) ? '1' : '' ) : sanitize_text_field( wp_unslash( $_POST['staffswap_' . $field] ) ); update_user_meta( $user_id, 'staffswap_' . $field, $value ); } } if ( isset( $_POST['staffswap_staff_housing'] ) ) { update_user_meta( $user_id, 'staffswap_staff_housing', '1' ); } else { update_user_meta( $user_id, 'staffswap_staff_housing', '' ); } }
add_action( 'personal_options_update', 'staffswap_save_profile_fields' ); add_action( 'edit_user_profile_update', 'staffswap_save_profile_fields' );
function staffswap_profile_shortcode( $atts ) { $atts = shortcode_atts( array( 'user' => get_current_user_id() ), $atts, 'staffswap_profile' ); $user = get_userdata( (int) $atts['user'] ); if ( ! $user ) { return '<div class="panel">Profile not found.</div>'; } $listing_count = ( new WP_Query( array( 'post_type' => 'swap_listing', 'author' => $user->ID, 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 1 ) ) )->found_posts; ob_start(); ?><article class="profile-surface"><div class="profile-cover"></div><div class="profile-surface__body"><div class="profile-surface__heading"><div class="profile-avatar profile-avatar--large"><?php echo esc_html( strtoupper( substr( $user->display_name, 0, 1 ) ) ); ?></div><div><p class="eyebrow">STAFFSWAP MEMBER</p><h1><?php echo esc_html( $user->display_name ); ?></h1><p class="muted"><?php echo esc_html( get_user_meta( $user->ID, 'staffswap_profession', true ) ?: 'Professional member' ); ?> <span class="verified">&#10003; Verified profile</span></p></div><?php if ( get_current_user_id() === $user->ID ) : ?><a class="button button--outline" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">Edit profile</a><?php endif; ?></div><div class="profile-facts"><div><span>Current location</span><strong><?php echo esc_html( get_user_meta( $user->ID, 'staffswap_location', true ) ?: 'Not provided' ); ?></strong></div><div><span>Employer</span><strong><?php echo esc_html( get_user_meta( $user->ID, 'staffswap_employer', true ) ?: 'Not provided' ); ?></strong></div><div><span>Published swaps</span><strong><?php echo esc_html( $listing_count ); ?></strong></div></div></div></article><?php return ob_get_clean(); }
add_shortcode( 'staffswap_profile', 'staffswap_profile_shortcode' );

remove_action( 'personal_options_update', 'staffswap_save_profile_fields' );
remove_action( 'edit_user_profile_update', 'staffswap_save_profile_fields' );
function staffswap_save_profile_fields_secure( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	foreach ( array( 'man_number', 'profession', 'salary_scale', 'location', 'desired_location', 'employer', 'years_service', 'professional_license' ) as $field ) {
		if ( isset( $_POST['staffswap_' . $field] ) ) {
			update_user_meta( $user_id, 'staffswap_' . $field, sanitize_text_field( wp_unslash( $_POST['staffswap_' . $field] ) ) );
		}
	}
	update_user_meta( $user_id, 'staffswap_staff_housing', isset( $_POST['staffswap_staff_housing'] ) ? '1' : '' );
	if ( current_user_can( 'manage_options' ) && isset( $_POST['staffswap_verified_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_POST['staffswap_verified_status'] ) );
		if ( in_array( $status, array( 'unverified', 'pending', 'verified' ), true ) ) {
			update_user_meta( $user_id, 'staffswap_verified_status', $status );
		}
	}
}
add_action( 'personal_options_update', 'staffswap_save_profile_fields_secure' );
add_action( 'edit_user_profile_update', 'staffswap_save_profile_fields_secure' );

function staffswap_verification_page() {
	if ( ! get_page_by_path( 'verification' ) ) {
		wp_insert_post( array( 'post_title' => 'Verification', 'post_name' => 'verification', 'post_content' => '[staffswap_verification]', 'post_status' => 'publish', 'post_type' => 'page' ) );
	}
}
register_activation_hook( __FILE__, 'staffswap_verification_page' );
add_action( 'admin_init', 'staffswap_verification_page' );

function staffswap_private_document_directory() {
	return trailingslashit( dirname( ABSPATH ) ) . 'staffswap-private-documents';
}

function staffswap_private_document_url( $user_id, $document ) {
	return wp_nonce_url( add_query_arg( array( 'action' => 'staffswap_verification_document', 'user_id' => absint( $user_id ), 'document' => sanitize_key( $document ) ), admin_url( 'admin-post.php' ) ), 'staffswap_verification_document_' . absint( $user_id ) . '_' . sanitize_key( $document ) );
}

function staffswap_download_private_document() {
	if ( ! is_user_logged_in() ) { wp_die( 'You are not allowed to view this document.', 403 ); }
	$user_id = absint( $_GET['user_id'] ?? 0 );
	$document = sanitize_key( wp_unslash( $_GET['document'] ?? '' ) );
	if ( ! $user_id || ! in_array( $document, array( 'nrc_document', 'payslip_document', 'license_document' ), true ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'staffswap_verification_document_' . $user_id . '_' . $document ) || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) { wp_die( 'You are not allowed to view this document.', 403 ); }
	$file = get_user_meta( $user_id, 'staffswap_' . $document, true );
	if ( ! is_array( $file ) || empty( $file['path'] ) || ! is_readable( $file['path'] ) ) { wp_die( 'Document not found.', 404 ); }
	nocache_headers();
	header( 'Content-Type: ' . ( $file['type'] ?? 'application/octet-stream' ) );
	header( 'Content-Length: ' . filesize( $file['path'] ) );
	header( 'Content-Disposition: inline; filename="' . sanitize_file_name( $file['name'] ?? basename( $file['path'] ) ) . '"' );
	readfile( $file['path'] );
	exit;
}
add_action( 'admin_post_staffswap_verification_document', 'staffswap_download_private_document' );

function staffswap_verification_shortcode() {
	if ( ! is_user_logged_in() ) {
		return '<div class="panel"><p>Please sign in to submit verification documents.</p></div>';
	}
	$user_id = get_current_user_id();
	$notice = '';
	if ( isset( $_POST['staffswap_submit_verification'] ) && check_admin_referer( 'staffswap_submit_verification', 'staffswap_verification_nonce' ) ) {
		$uploads = array( 'nrc_document', 'payslip_document', 'license_document' );
		$uploaded = 0;
		$directory = staffswap_private_document_directory();
		if ( ! wp_mkdir_p( $directory ) || ! is_writable( $directory ) ) {
			$notice = '<div class="notice"><p>Verification upload storage is unavailable. Please contact support.</p></div>';
		}
		foreach ( $uploads as $field ) {
			if ( $notice ) { break; }
			if ( empty( $_FILES[ $field ]['name'] ) ) {
				continue;
			}
			$file = $_FILES[ $field ];
			$type = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf' ) );
			if ( UPLOAD_ERR_OK === (int) $file['error'] && ! empty( $type['type'] ) && (int) $file['size'] <= 5 * MB_IN_BYTES && is_uploaded_file( $file['tmp_name'] ) ) {
				$path = trailingslashit( $directory ) . wp_generate_uuid4() . '.' . $type['ext'];
				if ( move_uploaded_file( $file['tmp_name'], $path ) ) {
					update_user_meta( $user_id, 'staffswap_' . $field, array( 'path' => $path, 'name' => sanitize_file_name( $file['name'] ), 'type' => $type['type'] ) );
					$uploaded++;
				}
			}
		}
		if ( ! $notice && get_user_meta( $user_id, 'staffswap_nrc_document', true ) && get_user_meta( $user_id, 'staffswap_payslip_document', true ) ) {
			update_user_meta( $user_id, 'staffswap_verified_status', 'pending' );
			$notice = '<div class="notice"><p>Your documents have been submitted for review.</p></div>';
		} elseif ( ! $notice ) {
			$notice = '<div class="notice"><p>Please upload both your NRC and a recent payslip. We accept PDF, JPG, and PNG files.</p></div>';
		}
	}
	$status = get_user_meta( $user_id, 'staffswap_verified_status', true ) ?: 'unverified';
	ob_start(); echo $notice; ?><section class="panel content-form"><h1>Verification</h1><p class="muted">Status: <strong><?php echo esc_html( ucfirst( $status ) ); ?></strong></p><form method="post" enctype="multipart/form-data"><div class="field"><label for="nrc_document">National Registration Card (NRC)</label><input id="nrc_document" name="nrc_document" type="file" accept=".jpg,.jpeg,.png,.pdf" required></div><div class="field"><label for="payslip_document">Recent ministry payslip</label><input id="payslip_document" name="payslip_document" type="file" accept=".jpg,.jpeg,.png,.pdf" required></div><div class="field"><label for="license_document">Professional practicing license (optional)</label><input id="license_document" name="license_document" type="file" accept=".jpg,.jpeg,.png,.pdf"></div><?php wp_nonce_field( 'staffswap_submit_verification', 'staffswap_verification_nonce' ); ?><input type="submit" name="staffswap_submit_verification" value="Submit for verification"></form></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_verification', 'staffswap_verification_shortcode' );

function staffswap_verification_admin_menu() {
	add_users_page( 'Verification Queue', 'Verification Queue', 'manage_options', 'staffswap-verification-queue', 'staffswap_verification_queue_screen' );
}
add_action( 'admin_menu', 'staffswap_verification_admin_menu' );

function staffswap_verification_queue_action() {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['staffswap_verification_action'] ) ) {
		return;
	}
	$user_id = absint( $_POST['user_id'] ?? 0 );
	$action = sanitize_key( wp_unslash( $_POST['staffswap_verification_action'] ) );
	if ( ! $user_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_verification_queue_nonce'] ?? '' ) ), 'staffswap_verification_queue_' . $user_id ) || ! in_array( $action, array( 'verified', 'unverified' ), true ) ) {
		return;
	}
	update_user_meta( $user_id, 'staffswap_verified_status', $action );
	wp_safe_redirect( add_query_arg( array( 'page' => 'staffswap-verification-queue', 'updated' => '1' ), admin_url( 'users.php' ) ) );
	exit;
}
add_action( 'admin_init', 'staffswap_verification_queue_action' );

function staffswap_verification_queue_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$users = get_users( array( 'meta_key' => 'staffswap_verified_status', 'meta_value' => 'pending', 'orderby' => 'registered', 'order' => 'ASC' ) );
	?><div class="wrap"><h1>StaffSwap Verification Queue</h1><?php if ( isset( $_GET['updated'] ) ) : ?><div class="notice notice-success is-dismissible"><p>Verification status updated.</p></div><?php endif; ?><p>Review NRC and payslip documents before approving a member profile.</p><table class="widefat striped"><thead><tr><th>Member</th><th>Profession</th><th>NRC</th><th>Payslip</th><th>License</th><th>Action</th></tr></thead><tbody><?php if ( $users ) : foreach ( $users as $user ) : ?><tr><td><strong><?php echo esc_html( $user->display_name ); ?></strong><br><a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>"><?php echo esc_html( $user->user_email ); ?></a></td><td><?php echo esc_html( get_user_meta( $user->ID, 'staffswap_profession', true ) ?: 'Not provided' ); ?></td><?php foreach ( array( 'nrc_document', 'payslip_document', 'license_document' ) as $document ) : $file = get_user_meta( $user->ID, 'staffswap_' . $document, true ); ?><td><?php if ( is_array( $file ) && ! empty( $file['path'] ) ) : ?><a href="<?php echo esc_url( staffswap_private_document_url( $user->ID, $document ) ); ?>" target="_blank" rel="noopener">View document</a><?php else : ?>-<?php endif; ?></td><?php endforeach; ?><td><form method="post"><input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>"><?php wp_nonce_field( 'staffswap_verification_queue_' . $user->ID, 'staffswap_verification_queue_nonce' ); ?><button type="submit" class="button button-primary" name="staffswap_verification_action" value="verified">Approve</button> <button type="submit" class="button" name="staffswap_verification_action" value="unverified">Reject</button></form></td></tr><?php endforeach; else : ?><tr><td colspan="6">No verification submissions are waiting for review.</td></tr><?php endif; ?></tbody></table></div><?php
}

function staffswap_profile_settings_page() {
	if ( ! get_page_by_path( 'profile-settings' ) ) {
		wp_insert_post( array( 'post_title' => 'Profile Settings', 'post_name' => 'profile-settings', 'post_content' => '[staffswap_profile_settings]', 'post_status' => 'publish', 'post_type' => 'page' ) );
	}
}
register_activation_hook( __FILE__, 'staffswap_profile_settings_page' );
add_action( 'admin_init', 'staffswap_profile_settings_page' );

function staffswap_profile_settings_shortcode() {
	if ( ! is_user_logged_in() ) { return '<div class="panel"><p>Please sign in to update your profile.</p></div>'; }
	$user_id = get_current_user_id();
	$user = wp_get_current_user();
	$fields = array( 'man_number' => 'Employee Man-Number', 'profession' => 'Profession / Cadre', 'salary_scale' => 'Salary Scale', 'employer' => 'Employer / Institution', 'years_service' => 'Years of Service', 'location' => 'Current Province & Town', 'desired_location' => 'Desired Province & Town', 'professional_license' => 'Professional License Number' );
	$notice = '';
	if ( isset( $_POST['staffswap_save_frontend_profile'] ) && check_admin_referer( 'staffswap_save_frontend_profile', 'staffswap_profile_settings_nonce' ) ) {
		$display_name = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
		if ( $display_name ) { wp_update_user( array( 'ID' => $user_id, 'display_name' => $display_name ) ); }
		foreach ( $fields as $key => $label ) { update_user_meta( $user_id, 'staffswap_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ) ); }
		update_user_meta( $user_id, 'staffswap_staff_housing', isset( $_POST['staff_housing'] ) ? '1' : '' );
		$notice = '<div class="notice"><p>Profile settings saved. Your future listings and matches will use these details.</p></div>';
		$user = wp_get_current_user();
	}
	ob_start(); echo $notice; ?><section class="content-form"><div class="page-heading"><div><p class="eyebrow">MEMBER PROFILE</p><h1>Profile settings</h1><p class="muted">Keep your professional and relocation details current.</p></div></div><form method="post" class="panel"><div class="form-grid"><div class="field"><label for="display_name">Official full name</label><input id="display_name" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required></div><?php foreach ( $fields as $key => $label ) : ?><div class="field"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label><input id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_user_meta( $user_id, 'staffswap_' . $key, true ) ); ?>" <?php echo 'years_service' === $key ? 'type="number" min="0"' : ''; ?>></div><?php endforeach; ?><label class="check full"><input type="checkbox" name="staff_housing" value="1" <?php checked( get_user_meta( $user_id, 'staffswap_staff_housing', true ), '1' ); ?>> Staff accommodation is available for handover</label></div><?php wp_nonce_field( 'staffswap_save_frontend_profile', 'staffswap_profile_settings_nonce' ); ?><input type="submit" name="staffswap_save_frontend_profile" value="Save profile settings"></form></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_profile_settings', 'staffswap_profile_settings_shortcode' );

function staffswap_profile_completion_shortcode() {
	if ( ! is_user_logged_in() ) { return ''; }
	$fields = array( 'man_number' => 'Employee Man-Number', 'profession' => 'Profession / Cadre', 'employer' => 'Employer / Institution', 'years_service' => 'Years of Service', 'location' => 'Current Province & Town', 'desired_location' => 'Desired Province & Town', 'professional_license' => 'Professional License Number' );
	$missing = array();
	foreach ( $fields as $key => $label ) {
		if ( ! get_user_meta( get_current_user_id(), 'staffswap_' . $key, true ) ) { $missing[] = $label; }
	}
	$percentage = (int) round( ( count( $fields ) - count( $missing ) ) / count( $fields ) * 100 );
	ob_start(); ?><section class="panel" style="margin-bottom:16px"><h2>Profile completion: <?php echo esc_html( $percentage ); ?>%</h2><?php if ( $missing ) : ?><p class="muted">Add <?php echo esc_html( implode( ', ', $missing ) ); ?> to improve your listing and match quality.</p><a class="button button--outline" href="<?php echo esc_url( home_url( '/profile-settings/' ) ); ?>">Complete profile</a><?php else : ?><p class="muted">Your professional profile is complete.</p><?php endif; ?></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_profile_completion', 'staffswap_profile_completion_shortcode' );