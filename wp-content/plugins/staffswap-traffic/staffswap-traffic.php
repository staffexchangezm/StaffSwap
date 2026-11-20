<?php
/**
 * Plugin Name: StaffSwap Traffic Insights
 * Description: Privacy-conscious traffic reports for StaffSwap, including page views, referral domains, and device summaries.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: staffswap-traffic
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'STAFFSWAP_TRAFFIC_DB_VERSION', '1.0.0' );

function staffswap_traffic_table( $name ) {
	global $wpdb;
	return $wpdb->prefix . 'staffswap_traffic_' . sanitize_key( $name );
}

function staffswap_traffic_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE " . staffswap_traffic_table( 'pages' ) . " (
		visit_date date NOT NULL,
		page_path varchar(191) NOT NULL,
		views bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (visit_date,page_path)
	) $charset;
	CREATE TABLE " . staffswap_traffic_table( 'sources' ) . " (
		visit_date date NOT NULL,
		source_host varchar(191) NOT NULL,
		views bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (visit_date,source_host)
	) $charset;
	CREATE TABLE " . staffswap_traffic_table( 'devices' ) . " (
		visit_date date NOT NULL,
		device_type varchar(20) NOT NULL,
		views bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (visit_date,device_type)
	) $charset;";
	dbDelta( $sql );
	update_option( 'staffswap_traffic_db_version', STAFFSWAP_TRAFFIC_DB_VERSION );
}
function staffswap_traffic_maybe_upgrade() {
	if ( get_option( 'staffswap_traffic_db_version' ) !== STAFFSWAP_TRAFFIC_DB_VERSION ) {
		staffswap_traffic_install();
	}
}
add_action( 'admin_init', 'staffswap_traffic_maybe_upgrade' );

function staffswap_traffic_activate() {
	staffswap_traffic_install();
	if ( ! wp_next_scheduled( 'staffswap_traffic_cleanup' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'staffswap_traffic_cleanup' );
	}
}
register_activation_hook( __FILE__, 'staffswap_traffic_activate' );

function staffswap_traffic_deactivate() {
	$timestamp = wp_next_scheduled( 'staffswap_traffic_cleanup' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'staffswap_traffic_cleanup' );
	}
}
register_deactivation_hook( __FILE__, 'staffswap_traffic_deactivate' );

function staffswap_traffic_cleanup() {
	global $wpdb;
	$cutoff = ( new DateTimeImmutable( 'today', wp_timezone() ) )->modify( '-13 months' )->format( 'Y-m-d' );
	foreach ( array( 'pages', 'sources', 'devices' ) as $table_name ) {
		$table = staffswap_traffic_table( $table_name );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE visit_date < %s", $cutoff ) );
	}
}
add_action( 'staffswap_traffic_cleanup', 'staffswap_traffic_cleanup' );

function staffswap_traffic_should_track() {
	if ( is_admin() || is_feed() || is_robots() || is_trackback() || is_404() || is_preview() || current_user_can( 'manage_options' ) ) {
		return false;
	}
	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return false;
	}
	if ( function_exists( 'post_password_required' ) && post_password_required() ) {
		return false;
	}
	$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
	if ( preg_match( '/bot|crawler|spider|slurp|headless|preview|monitoring|uptime/i', $user_agent ) ) {
		return false;
	}
	return true;
}

function staffswap_traffic_page_path() {
	$path = '';
	$object_id = get_queried_object_id();
	if ( $object_id ) {
		$permalink = get_permalink( $object_id );
		if ( $permalink ) {
			$path = wp_make_link_relative( $permalink );
		}
	}
	if ( '' === $path && isset( $_SERVER['REQUEST_URI'] ) ) {
		$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] );
		$path = wp_parse_url( $request_uri, PHP_URL_PATH );
	}
	$path = is_string( $path ) ? rawurldecode( $path ) : '';
	$path = '/' . trim( sanitize_text_field( $path ), '/' );
	if ( '/' === $path ) {
		return '/';
	}
	return substr( $path, 0, 191 );
}

function staffswap_traffic_source_host() {
	if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
		return 'direct';
	}
	$referer = wp_unslash( $_SERVER['HTTP_REFERER'] );
	$host = wp_parse_url( $referer, PHP_URL_HOST );
	if ( ! is_string( $host ) || '' === $host ) {
		return 'direct';
	}
	$host = strtolower( sanitize_text_field( $host ) );
	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( $site_host && strtolower( $site_host ) === $host ) {
		return 'direct';
	}
	if ( ! preg_match( '/^[a-z0-9.-]+$/', $host ) ) {
		return 'other';
	}
	return substr( $host, 0, 191 );
}

function staffswap_traffic_increment( $table_name, $dimension_column, $dimension_value ) {
	global $wpdb;
	$table = staffswap_traffic_table( $table_name );
	$date = current_time( 'Y-m-d' );
	$sql = "INSERT INTO $table (visit_date, $dimension_column, views) VALUES (%s, %s, 1)
		ON DUPLICATE KEY UPDATE views = views + 1";
	$result = $wpdb->query( $wpdb->prepare( $sql, $date, $dimension_value ) );
	if ( false === $result ) {
		error_log( 'StaffSwap Traffic Insights could not save an aggregate view.' );
	}
}

function staffswap_traffic_record_view() {
	if ( ! staffswap_traffic_should_track() ) {
		return;
	}
	staffswap_traffic_increment( 'pages', 'page_path', staffswap_traffic_page_path() );
	staffswap_traffic_increment( 'sources', 'source_host', staffswap_traffic_source_host() );
	staffswap_traffic_increment( 'devices', 'device_type', wp_is_mobile() ? 'mobile' : 'desktop' );
}
add_action( 'wp', 'staffswap_traffic_record_view' );

function staffswap_traffic_admin_menu() {
	$parent = post_type_exists( 'swap_listing' ) ? 'edit.php?post_type=swap_listing' : 'tools.php';
	add_submenu_page( $parent, 'StaffSwap Traffic Insights', 'Traffic Insights', 'manage_options', 'staffswap-traffic', 'staffswap_traffic_admin_screen' );
}
add_action( 'admin_menu', 'staffswap_traffic_admin_menu' );

function staffswap_traffic_admin_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$allowed_periods = array( 7, 30, 90 );
	$requested_period = isset( $_GET['period'] ) ? absint( wp_unslash( $_GET['period'] ) ) : 30;
	$days = in_array( $requested_period, $allowed_periods, true ) ? $requested_period : 30;
	$today = new DateTimeImmutable( 'today', wp_timezone() );
	$start_date = $today->modify( '-' . ( $days - 1 ) . ' days' )->format( 'Y-m-d' );
	$pages_table = staffswap_traffic_table( 'pages' );
	$sources_table = staffswap_traffic_table( 'sources' );
	$devices_table = staffswap_traffic_table( 'devices' );

	$total_views = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(views), 0) FROM $pages_table WHERE visit_date >= %s", $start_date ) );
	$today_views = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(views), 0) FROM $pages_table WHERE visit_date = %s", current_time( 'Y-m-d' ) ) );
	$tracked_pages = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT page_path) FROM $pages_table WHERE visit_date >= %s", $start_date ) );
	$top_pages = $wpdb->get_results( $wpdb->prepare( "SELECT page_path, SUM(views) AS total FROM $pages_table WHERE visit_date >= %s GROUP BY page_path ORDER BY total DESC LIMIT 10", $start_date ) );
	$top_sources = $wpdb->get_results( $wpdb->prepare( "SELECT source_host, SUM(views) AS total FROM $sources_table WHERE visit_date >= %s GROUP BY source_host ORDER BY total DESC LIMIT 10", $start_date ) );
	$device_totals = $wpdb->get_results( $wpdb->prepare( "SELECT device_type, SUM(views) AS total FROM $devices_table WHERE visit_date >= %s GROUP BY device_type ORDER BY total DESC", $start_date ) );
	$daily_totals = $wpdb->get_results( $wpdb->prepare( "SELECT visit_date, SUM(views) AS total FROM $pages_table WHERE visit_date >= %s GROUP BY visit_date ORDER BY visit_date ASC", $start_date ), OBJECT_K );
	$admin_page = post_type_exists( 'swap_listing' ) ? 'edit.php?post_type=swap_listing' : 'tools.php';
	$max_daily_views = 1;
	foreach ( $daily_totals as $daily_total ) {
		$max_daily_views = max( $max_daily_views, (int) $daily_total->total );
	}
	?>
	<div class="wrap staffswap-traffic">
		<h1>StaffSwap Traffic Insights</h1>
		<p>Aggregate page views from non-administrator visits. IP addresses, cookies, full referrer URLs, and user-agent strings are not stored. Data is retained for 13 months.</p>
		<form method="get" action="<?php echo esc_url( admin_url( $admin_page ) ); ?>" class="staffswap-traffic-filter">
			<input type="hidden" name="page" value="staffswap-traffic">
			<label for="staffswap-traffic-period">Report period</label>
			<select id="staffswap-traffic-period" name="period">
				<option value="7" <?php selected( $days, 7 ); ?>>Last 7 days</option>
				<option value="30" <?php selected( $days, 30 ); ?>>Last 30 days</option>
				<option value="90" <?php selected( $days, 90 ); ?>>Last 90 days</option>
			</select>
			<button class="button">Apply</button>
		</form>
		<div class="staffswap-traffic-cards">
			<div><strong><?php echo esc_html( number_format_i18n( $total_views ) ); ?></strong><span>Page views in selected period</span></div>
			<div><strong><?php echo esc_html( number_format_i18n( $today_views ) ); ?></strong><span>Page views today</span></div>
			<div><strong><?php echo esc_html( number_format_i18n( $tracked_pages ) ); ?></strong><span>Pages with recorded views</span></div>
		</div>
		<div class="staffswap-traffic-grid">
			<section>
				<h2>Daily page views</h2>
				<table class="widefat striped"><thead><tr><th>Date</th><th>Page views</th><th class="staffswap-traffic-chart-heading">Trend</th></tr></thead><tbody>
				<?php for ( $offset = $days - 1; $offset >= 0; $offset-- ) :
					$display_date = $today->modify( '-' . $offset . ' days' );
					$date = $display_date->format( 'Y-m-d' );
					$views = isset( $daily_totals[ $date ] ) ? (int) $daily_totals[ $date ]->total : 0;
					$width = $views ? max( 2, (int) round( ( $views / $max_daily_views ) * 100 ) ) : 0;
					?>
					<tr><td><?php echo esc_html( $display_date->format( get_option( 'date_format' ) ) ); ?></td><td><?php echo esc_html( number_format_i18n( $views ) ); ?></td><td><span class="staffswap-traffic-bar" style="width:<?php echo esc_attr( $width ); ?>%" aria-label="<?php echo esc_attr( number_format_i18n( $views ) . ' page views' ); ?>"></span></td></tr>
				<?php endfor; ?>
				</tbody></table>
			</section>
			<section>
				<h2>Top pages</h2>
				<table class="widefat striped"><thead><tr><th>Page path</th><th>Views</th></tr></thead><tbody>
				<?php if ( $top_pages ) : foreach ( $top_pages as $page ) : ?>
					<tr><td><code><?php echo esc_html( $page->page_path ); ?></code></td><td><?php echo esc_html( number_format_i18n( $page->total ) ); ?></td></tr>
				<?php endforeach; else : ?><tr><td colspan="2">No traffic data for this period yet.</td></tr><?php endif; ?>
				</tbody></table>
			</section>
			<section>
				<h2>Referral domains</h2>
				<table class="widefat striped"><thead><tr><th>Source</th><th>Views</th></tr></thead><tbody>
				<?php if ( $top_sources ) : foreach ( $top_sources as $source ) : ?>
					<tr><td><?php echo esc_html( 'direct' === $source->source_host ? 'Direct / unknown' : $source->source_host ); ?></td><td><?php echo esc_html( number_format_i18n( $source->total ) ); ?></td></tr>
				<?php endforeach; else : ?><tr><td colspan="2">No referral data for this period yet.</td></tr><?php endif; ?>
				</tbody></table>
			</section>
			<section>
				<h2>Device summary</h2>
				<table class="widefat striped"><thead><tr><th>Device</th><th>Views</th></tr></thead><tbody>
				<?php if ( $device_totals ) : foreach ( $device_totals as $device ) : ?>
					<tr><td><?php echo esc_html( ucfirst( $device->device_type ) ); ?></td><td><?php echo esc_html( number_format_i18n( $device->total ) ); ?></td></tr>
				<?php endforeach; else : ?><tr><td colspan="2">No device data for this period yet.</td></tr><?php endif; ?>
				</tbody></table>
			</section>
		</div>
	</div>
	<?php
}

function staffswap_traffic_admin_styles( $hook ) {
	if ( ! in_array( $hook, array( 'swap_listing_page_staffswap-traffic', 'tools_page_staffswap-traffic' ), true ) ) {
		return;
	}
	wp_add_inline_style( 'common', '.staffswap-traffic-filter{display:flex;align-items:center;gap:8px;margin:18px 0}.staffswap-traffic-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin:20px 0 28px}.staffswap-traffic-cards>div{background:#fff;border:1px solid #dcdcde;padding:20px}.staffswap-traffic-cards strong{display:block;font-size:28px;color:#135e96}.staffswap-traffic-cards span{color:#50575e}.staffswap-traffic-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:24px;max-width:1400px}.staffswap-traffic-grid h2{margin-bottom:10px}.staffswap-traffic-chart-heading{width:35%}.staffswap-traffic-bar{display:block;min-width:0;height:12px;background:#2271b1;border-radius:2px}.staffswap-traffic code{overflow-wrap:anywhere}@media(max-width:782px){.staffswap-traffic-filter{align-items:flex-start;flex-wrap:wrap}.staffswap-traffic-grid{grid-template-columns:1fr}}' );
}
add_action( 'admin_enqueue_scripts', 'staffswap_traffic_admin_styles' );

function staffswap_traffic_privacy_policy_content() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}
	$content = '<p>StaffSwap Traffic Insights records aggregate daily page-view totals, page paths, external referral domains, and whether visits were from mobile or desktop devices. It excludes administrator visits and filters common automated requests. It does not set cookies or store IP addresses, full referrer URLs, user-agent strings, or individual visitor profiles. Aggregate traffic data is retained for 13 months.</p>';
	wp_add_privacy_policy_content( 'StaffSwap Traffic Insights', wp_kses_post( $content ) );
}
add_action( 'admin_init', 'staffswap_traffic_privacy_policy_content' );
