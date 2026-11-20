<?php
/**
 * Plugin Name: StaffSwap Traffic Insights
 * Description: Privacy-conscious traffic reports for StaffSwap, including page views, referral domains, and device summaries.
 * Version: 1.1.0
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
	$average_views = (int) round( $total_views / $days );
	$top_pages = $wpdb->get_results( $wpdb->prepare( "SELECT page_path, SUM(views) AS total FROM $pages_table WHERE visit_date >= %s GROUP BY page_path ORDER BY total DESC LIMIT 10", $start_date ) );
	$top_sources = $wpdb->get_results( $wpdb->prepare( "SELECT source_host, SUM(views) AS total FROM $sources_table WHERE visit_date >= %s GROUP BY source_host ORDER BY total DESC LIMIT 10", $start_date ) );
	$device_totals = $wpdb->get_results( $wpdb->prepare( "SELECT device_type, SUM(views) AS total FROM $devices_table WHERE visit_date >= %s GROUP BY device_type ORDER BY total DESC", $start_date ) );
	$daily_totals = $wpdb->get_results( $wpdb->prepare( "SELECT visit_date, SUM(views) AS total FROM $pages_table WHERE visit_date >= %s GROUP BY visit_date ORDER BY visit_date ASC", $start_date ), OBJECT_K );
	$admin_page = post_type_exists( 'swap_listing' ) ? 'edit.php?post_type=swap_listing' : 'tools.php';
	$max_daily_views = 1;
	foreach ( $daily_totals as $daily_total ) {
		$max_daily_views = max( $max_daily_views, (int) $daily_total->total );
	}
	$daily_series = array();
	$chart_points = array();
	$chart_labels = array();
	$chart_width = 900;
	$chart_left = 48;
	$chart_right = 880;
	$chart_top = 24;
	$chart_bottom = 222;
	for ( $offset = $days - 1; $offset >= 0; $offset-- ) {
		$display_date = $today->modify( '-' . $offset . ' days' );
		$date = $display_date->format( 'Y-m-d' );
		$views = isset( $daily_totals[ $date ] ) ? (int) $daily_totals[ $date ]->total : 0;
		$daily_series[] = array( 'date' => $display_date, 'views' => $views );
	}
	foreach ( $daily_series as $index => $day ) {
		$x = $chart_left + ( count( $daily_series ) > 1 ? ( $index / ( count( $daily_series ) - 1 ) ) * ( $chart_right - $chart_left ) : ( $chart_right - $chart_left ) / 2 );
		$y = $chart_bottom - ( $day['views'] / $max_daily_views ) * ( $chart_bottom - $chart_top );
		$chart_points[] = array( 'x' => round( $x, 1 ), 'y' => round( $y, 1 ), 'day' => $day );
		if ( 0 === $index || $index === count( $daily_series ) - 1 || 0 === $index % max( 1, (int) ceil( $days / 5 ) ) ) {
			$chart_labels[] = array( 'x' => round( $x, 1 ), 'date' => $day['date'] );
		}
	}
	$line_path = '';
	foreach ( $chart_points as $index => $point ) {
		$line_path .= ( 0 === $index ? 'M ' : ' L ' ) . $point['x'] . ' ' . $point['y'];
	}
	$first_point = reset( $chart_points );
	$last_point = end( $chart_points );
	$area_path = $line_path . ' L ' . $last_point['x'] . ' ' . $chart_bottom . ' L ' . $first_point['x'] . ' ' . $chart_bottom . ' Z';
	$device_total = 0;
	$device_counts = array( 'mobile' => 0, 'desktop' => 0 );
	foreach ( $device_totals as $device ) {
		if ( isset( $device_counts[ $device->device_type ] ) ) {
			$device_counts[ $device->device_type ] = (int) $device->total;
		}
		$device_total += (int) $device->total;
	}
	$mobile_share = $device_total ? (int) round( ( $device_counts['mobile'] / $device_total ) * 100 ) : 0;
	$source_max = ! empty( $top_sources ) ? max( 1, (int) $top_sources[0]->total ) : 1;
	?>
	<div class="wrap staffswap-traffic">
		<header class="staffswap-traffic-header">
			<div><p class="staffswap-traffic-eyebrow">STAFFSWAP / ANALYTICS</p><h1>Traffic overview</h1><p>Understand how people discover and use your marketplace.</p></div>
			<form method="get" action="<?php echo esc_url( admin_url( $admin_page ) ); ?>" class="staffswap-traffic-filter">
				<input type="hidden" name="page" value="staffswap-traffic">
				<label class="screen-reader-text" for="staffswap-traffic-period">Report period</label>
				<select id="staffswap-traffic-period" name="period">
					<option value="7" <?php selected( $days, 7 ); ?>>Last 7 days</option>
					<option value="30" <?php selected( $days, 30 ); ?>>Last 30 days</option>
					<option value="90" <?php selected( $days, 90 ); ?>>Last 90 days</option>
				</select>
				<button class="button button-primary">Update report</button>
			</form>
		</header>
		<div class="staffswap-traffic-cards">
			<div class="staffswap-traffic-card"><span class="staffswap-traffic-card-icon staffswap-traffic-icon-blue"><span class="dashicons dashicons-chart-area" aria-hidden="true"></span></span><span class="staffswap-traffic-card-label">Page views</span><strong><?php echo esc_html( number_format_i18n( $total_views ) ); ?></strong><small>Across the selected period</small></div>
			<div class="staffswap-traffic-card"><span class="staffswap-traffic-card-icon staffswap-traffic-icon-violet"><span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span></span><span class="staffswap-traffic-card-label">Today's views</span><strong><?php echo esc_html( number_format_i18n( $today_views ) ); ?></strong><small>Since midnight</small></div>
			<div class="staffswap-traffic-card"><span class="staffswap-traffic-card-icon staffswap-traffic-icon-green"><span class="dashicons dashicons-chart-line" aria-hidden="true"></span></span><span class="staffswap-traffic-card-label">Daily average</span><strong><?php echo esc_html( number_format_i18n( $average_views ) ); ?></strong><small>Views per day</small></div>
			<div class="staffswap-traffic-card"><span class="staffswap-traffic-card-icon staffswap-traffic-icon-amber"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span></span><span class="staffswap-traffic-card-label">Pages viewed</span><strong><?php echo esc_html( number_format_i18n( $tracked_pages ) ); ?></strong><small>Unique page paths</small></div>
		</div>
		<div class="staffswap-traffic-dashboard">
			<section class="staffswap-traffic-panel staffswap-traffic-trend">
				<div class="staffswap-traffic-panel-heading"><div><h2>Traffic trend</h2><p>Daily page views over the last <?php echo esc_html( $days ); ?> days</p></div><span class="staffswap-traffic-live"><span></span> All traffic</span></div>
				<div class="staffswap-traffic-chart-wrap">
					<svg class="staffswap-traffic-chart" viewBox="0 0 <?php echo esc_attr( $chart_width ); ?> 280" role="img" aria-labelledby="staffswap-traffic-chart-title staffswap-traffic-chart-desc" preserveAspectRatio="none">
						<title id="staffswap-traffic-chart-title">Daily page views</title>
						<desc id="staffswap-traffic-chart-desc">Page views by day for the selected reporting period.</desc>
						<defs><linearGradient id="staffswap-traffic-gradient" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#6473df" stop-opacity=".24" /><stop offset="100%" stop-color="#6473df" stop-opacity="0" /></linearGradient></defs>
						<?php for ( $gridline = 0; $gridline <= 4; $gridline++ ) : $grid_y = $chart_top + ( $gridline / 4 ) * ( $chart_bottom - $chart_top ); ?>
							<line x1="<?php echo esc_attr( $chart_left ); ?>" y1="<?php echo esc_attr( $grid_y ); ?>" x2="<?php echo esc_attr( $chart_right ); ?>" y2="<?php echo esc_attr( $grid_y ); ?>" class="staffswap-traffic-gridline" />
							<text x="0" y="<?php echo esc_attr( $grid_y + 4 ); ?>" class="staffswap-traffic-axis-label"><?php echo esc_html( number_format_i18n( (int) round( $max_daily_views * ( 1 - $gridline / 4 ) ) ) ); ?></text>
						<?php endfor; ?>
						<path d="<?php echo esc_attr( $area_path ); ?>" class="staffswap-traffic-area" />
						<path d="<?php echo esc_attr( $line_path ); ?>" class="staffswap-traffic-line" />
						<?php foreach ( $chart_points as $point ) : ?>
							<circle cx="<?php echo esc_attr( $point['x'] ); ?>" cy="<?php echo esc_attr( $point['y'] ); ?>" r="3.5" class="staffswap-traffic-point"><title><?php echo esc_html( $point['day']['date']->format( get_option( 'date_format' ) ) . ': ' . number_format_i18n( $point['day']['views'] ) . ' views' ); ?></title></circle>
						<?php endforeach; ?>
						<?php foreach ( $chart_labels as $label ) : ?>
							<text x="<?php echo esc_attr( $label['x'] ); ?>" y="260" text-anchor="middle" class="staffswap-traffic-axis-label"><?php echo esc_html( $label['date']->format( 'M j' ) ); ?></text>
						<?php endforeach; ?>
					</svg>
				</div>
			</section>
			<section class="staffswap-traffic-panel staffswap-traffic-devices">
				<div class="staffswap-traffic-panel-heading"><div><h2>Devices</h2><p>Visits by device type</p></div></div>
				<div class="staffswap-traffic-device-content">
					<div class="staffswap-traffic-donut" role="img" aria-label="<?php echo esc_attr( $mobile_share . '% mobile and ' . ( 100 - $mobile_share ) . '% desktop' ); ?>" style="--mobile-share:<?php echo esc_attr( $mobile_share ); ?>%">
						<div><strong><?php echo esc_html( $device_total ? number_format_i18n( $device_total ) : '0' ); ?></strong><span>views</span></div>
					</div>
					<div class="staffswap-traffic-device-legend">
						<div><span><i class="staffswap-traffic-legend-mobile"></i>Mobile</span><strong><?php echo esc_html( number_format_i18n( $device_counts['mobile'] ) ); ?><small><?php echo esc_html( $device_total ? round( ( $device_counts['mobile'] / $device_total ) * 100 ) : 0 ); ?>%</small></strong></div>
						<div><span><i class="staffswap-traffic-legend-desktop"></i>Desktop</span><strong><?php echo esc_html( number_format_i18n( $device_counts['desktop'] ) ); ?><small><?php echo esc_html( $device_total ? round( ( $device_counts['desktop'] / $device_total ) * 100 ) : 0 ); ?>%</small></strong></div>
					</div>
				</div>
			</section>
			<section class="staffswap-traffic-panel">
				<div class="staffswap-traffic-panel-heading"><div><h2>Top pages</h2><p>Most viewed paths in this period</p></div></div>
				<?php if ( $top_pages ) : ?>
					<div class="staffswap-traffic-ranking">
						<?php $page_max = max( 1, (int) $top_pages[0]->total ); foreach ( $top_pages as $index => $page ) : $page_width = (int) round( ( (int) $page->total / $page_max ) * 100 ); ?>
							<div class="staffswap-traffic-rank-row"><div class="staffswap-traffic-rank-meta"><span class="staffswap-traffic-rank-number"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><code><?php echo esc_html( $page->page_path ); ?></code><strong><?php echo esc_html( number_format_i18n( $page->total ) ); ?></strong></div><div class="staffswap-traffic-rank-track"><span style="width:<?php echo esc_attr( $page_width ); ?>%"></span></div></div>
						<?php endforeach; ?>
					</div>
				<?php else : ?><p class="staffswap-traffic-empty">No traffic recorded in this period yet.</p><?php endif; ?>
			</section>
			<section class="staffswap-traffic-panel">
				<div class="staffswap-traffic-panel-heading"><div><h2>Traffic sources</h2><p>Where your visitors come from</p></div></div>
				<?php if ( $top_sources ) : ?>
					<div class="staffswap-traffic-ranking">
						<?php foreach ( $top_sources as $source ) : $source_width = (int) round( ( (int) $source->total / $source_max ) * 100 ); ?>
							<div class="staffswap-traffic-rank-row"><div class="staffswap-traffic-rank-meta"><span class="staffswap-traffic-source-icon"><span class="dashicons <?php echo esc_attr( 'direct' === $source->source_host ? 'dashicons-admin-site' : 'dashicons-external' ); ?>" aria-hidden="true"></span></span><span class="staffswap-traffic-source-name"><?php echo esc_html( 'direct' === $source->source_host ? 'Direct / unknown' : $source->source_host ); ?></span><strong><?php echo esc_html( number_format_i18n( $source->total ) ); ?></strong></div><div class="staffswap-traffic-rank-track"><span class="staffswap-traffic-rank-source" style="width:<?php echo esc_attr( $source_width ); ?>%"></span></div></div>
						<?php endforeach; ?>
					</div>
				<?php else : ?><p class="staffswap-traffic-empty">No referral data recorded in this period yet.</p><?php endif; ?>
			</section>
		</div>
		<p class="staffswap-traffic-privacy"><span class="dashicons dashicons-shield" aria-hidden="true"></span> Privacy-first reporting: aggregate views only. No cookies, IP addresses, full referrer URLs, or user-agent strings are stored. Data is retained for 13 months.</p>
	</div>
	<?php
}

function staffswap_traffic_admin_styles( $hook ) {
	if ( ! in_array( $hook, array( 'swap_listing_page_staffswap-traffic', 'tools_page_staffswap-traffic' ), true ) ) {
		return;
	}
	wp_enqueue_style( 'staffswap-traffic-admin', plugin_dir_url( __FILE__ ) . 'assets/css/traffic-admin.css', array( 'common' ), '1.1.0' );
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
