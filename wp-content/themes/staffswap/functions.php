<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_setup() { add_theme_support( 'title-tag' ); add_theme_support( 'post-thumbnails' ); register_nav_menus( array( 'primary' => __( 'Primary Menu', 'staffswap' ) ) ); }
add_action( 'after_setup_theme', 'staffswap_setup' );
function staffswap_assets() { wp_enqueue_style( 'staffswap-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap', array(), null ); wp_enqueue_style( 'staffswap-style', get_stylesheet_uri(), array( 'staffswap-fonts' ), '1.0.0' ); wp_enqueue_script( 'staffswap-main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.0', true ); }
add_action( 'wp_enqueue_scripts', 'staffswap_assets' );
function staffswap_fallback_menu() { echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li><li><a href="' . esc_url( home_url( '/swaps/' ) ) . '">Find Swaps</a></li><li><a href="' . esc_url( home_url( '/search/' ) ) . '">How It Works</a></li><li><a href="' . esc_url( home_url( '/swaps/' ) ) . '">Success Stories</a></li><li><a href="' . esc_url( home_url( '/resources/' ) ) . '">Resources</a></li><li><a href="' . esc_url( home_url( '/pricing/' ) ) . '">Pricing</a></li></ul>'; }

function staffswap_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'staffswap_home', array( 'title' => __( 'StaffSwap Homepage', 'staffswap' ), 'priority' => 30 ) );
	$settings = array(
		'staffswap_hero_title' => array( 'label' => 'Hero title', 'default' => 'Swap Your Workplace. Change Your Life.' ),
		'staffswap_hero_text' => array( 'label' => 'Hero description', 'default' => 'Connect with verified professionals across Zambia who want to swap their workplace just like you. Secure, efficient, and professional workplace mobility.' ),
		'staffswap_hero_primary_label' => array( 'label' => 'Primary button label', 'default' => 'Create Swap Post' ),
		'staffswap_hero_secondary_label' => array( 'label' => 'Secondary button label', 'default' => 'Browse Swaps' ),
		'staffswap_stats_text' => array( 'label' => 'Homepage stats', 'default' => '12,000+|3,200+|150+|10|20+' ),
	);
	foreach ( $settings as $id => $setting ) {
		$wp_customize->add_setting( $id, array( 'default' => $setting['default'], 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp_customize->add_control( $id, array( 'label' => $setting['label'], 'section' => 'staffswap_home', 'type' => 'textarea' ) );
	}
	$wp_customize->add_setting( 'staffswap_primary_color', array( 'default' => '#005f2e', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'staffswap_primary_color', array( 'label' => 'Primary action color', 'section' => 'staffswap_home' ) ) );
}
add_action( 'customize_register', 'staffswap_customize_register' );

function staffswap_customizer_css() {
	$color = get_theme_mod( 'staffswap_primary_color', '#005f2e' );
	echo '<style>:root{--primary:' . esc_attr( $color ) . ';}</style>';
}
add_action( 'wp_head', 'staffswap_customizer_css' );

function staffswap_home_setting( $key, $default ) {
	$settings = get_option( 'staffswap_settings', array() );
	if ( ! empty( $settings[ $key ] ) ) { return $settings[ $key ]; }
	return get_theme_mod( 'staffswap_' . $key, $default );
}
function staffswap_theme_activated() { update_option( 'staffswap_show_setup', '1' ); }
add_action( 'after_switch_theme', 'staffswap_theme_activated' );

function staffswap_setup_menu() { add_theme_page( 'StaffSwap Setup', 'StaffSwap Setup', 'manage_options', 'staffswap-setup', 'staffswap_setup_screen' ); }
add_action( 'admin_menu', 'staffswap_setup_menu' );

function staffswap_setup_pages() {
	$pages = array(
		'home' => array( 'title' => 'Home', 'slug' => 'home', 'content' => '' ),
		'swaps' => array( 'title' => 'Find Swaps', 'slug' => 'swaps', 'content' => '[staffswap_listings]' ),
		'search' => array( 'title' => 'Search Swaps', 'slug' => 'search', 'content' => '[staffswap_search]' ),
		'create' => array( 'title' => 'Create a Swap Post', 'slug' => 'create-swap', 'content' => '[staffswap_create_form]' ),
		'register' => array( 'title' => 'Create Your Account', 'slug' => 'register', 'content' => '[staffswap_register]' ),
		'login' => array( 'title' => 'Sign In', 'slug' => 'sign-in', 'content' => '[staffswap_login]' ),
		'profile' => array( 'title' => 'My Profile', 'slug' => 'my-profile', 'content' => '[staffswap_dashboard]' ),
		'resources' => array( 'title' => 'Resources Centre', 'slug' => 'resources', 'content' => '[staffswap_resources]' ),
	);
	$created = array();
	foreach ( $pages as $key => $page ) {
		$existing = get_page_by_path( $page['slug'] );
		$created[ $key ] = $existing ? $existing->ID : 0;
		if ( ! $existing ) {
			$created[ $key ] = wp_insert_post( array( 'post_title' => $page['title'], 'post_name' => $page['slug'], 'post_content' => $page['content'], 'post_status' => 'publish', 'post_type' => 'page' ) );
		}
	}
	if ( ! empty( $created['home'] ) ) { update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $created['home'] ); }
	$menu = wp_get_nav_menu_object( 'StaffSwap Main Menu' );
	$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'StaffSwap Main Menu' );
	if ( ! is_wp_error( $menu_id ) ) {
		$items = wp_get_nav_menu_items( $menu_id );
		if ( empty( $items ) ) { foreach ( array( 'home', 'swaps', 'search', 'resources' ) as $key ) { if ( ! empty( $created[ $key ] ) ) { wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $pages[ $key ]['title'], 'menu-item-object' => 'page', 'menu-item-object-id' => $created[ $key ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) ); } } }
		$locations = get_theme_mod( 'nav_menu_locations', array() ); $locations['primary'] = $menu_id; set_theme_mod( 'nav_menu_locations', $locations );
	}
	update_option( 'staffswap_show_setup', '0' );
	return $created;
}

function staffswap_setup_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$notice = '';
	if ( isset( $_POST['staffswap_run_setup'] ) && check_admin_referer( 'staffswap_run_setup', 'staffswap_setup_nonce' ) ) { staffswap_setup_pages(); $notice = '<div class="notice notice-success is-dismissible"><p><strong>StaffSwap is ready.</strong> Your pages, homepage, and navigation were configured.</p></div>'; }
	$plugins = array( 'staffswap-core/staffswap-core.php' => 'Swap marketplace', 'staffswap-resources/staffswap-resources.php' => 'Resources Centre', 'staffswap-profiles/staffswap-profiles.php' => 'Member profiles', 'staffswap-messaging/staffswap-messaging.php' => 'Private messaging' );
	if ( ! function_exists( 'is_plugin_active' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
	?><div class="wrap staffswap-setup"><div class="staffswap-setup__hero"><div><span class="staffswap-kicker">STAFFEXCHANGEHUB</span><h1>Set up your exchange network</h1><p>Build the essential pages and navigation for your professional workplace marketplace.</p></div><div class="staffswap-setup__mark">S</div></div><?php echo $notice; ?><div class="staffswap-setup__grid"><section class="staffswap-setup__main"><div class="staffswap-setup__card"><span class="staffswap-step">01</span><h2>Launch the core experience</h2><p>Creates the homepage, swap search, registration, sign-in, profile, create-post, and resources pages. It also sets Home as the front page and adds a polished main menu.</p><form method="post"><?php wp_nonce_field( 'staffswap_run_setup', 'staffswap_setup_nonce' ); ?><button class="button button-primary button-hero" type="submit" name="staffswap_run_setup">Run setup</button></form></div><div class="staffswap-setup__card"><span class="staffswap-step">02</span><h2>Shape your brand</h2><p>Update your hero message, CTA labels, homepage stats, and primary color without touching code.</p><a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">Open Customizer</a></div></section><aside class="staffswap-setup__side"><div class="staffswap-setup__card"><h2>Module checklist</h2><?php foreach ( $plugins as $plugin => $label ) : ?><p class="staffswap-check"><span class="<?php echo is_plugin_active( $plugin ) ? 'is-ready' : ''; ?>"><?php echo is_plugin_active( $plugin ) ? '&#10003;' : '&#9675;'; ?></span><?php echo esc_html( $label ); ?></p><?php endforeach; ?></div><div class="staffswap-setup__card staffswap-setup__tip"><strong>Recommended next step</strong><p>Activate StaffSwap Core first, then add Resources, Profiles, and Messaging as your site grows.</p><a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Manage plugins</a></div></aside></div></div><?php
}

function staffswap_setup_admin_styles( $hook ) {
	if ( 'appearance_page_staffswap-setup' !== $hook ) { return; }
	wp_enqueue_style( 'dashicons' );
	wp_add_inline_style( 'dashicons', '.staffswap-setup{max-width:1120px;margin-top:24px}.staffswap-setup__hero{background:#061f3d;color:#fff;border-radius:14px;padding:38px 42px;display:flex;justify-content:space-between;align-items:center}.staffswap-kicker{color:#9aebb0;font-size:11px;font-weight:700;letter-spacing:.12em}.staffswap-setup h1{color:#fff;font:800 34px/1.2 Manrope,sans-serif;margin:10px 0}.staffswap-setup__hero p{color:#d8e5f6;font-size:15px;margin:0}.staffswap-setup__mark{width:70px;height:70px;border:2px solid #9aebb0;border-radius:50%;display:grid;place-items:center;color:#9aebb0;font:800 36px Manrope}.staffswap-setup__grid{display:grid;grid-template-columns:1fr 330px;gap:20px;margin-top:20px}.staffswap-setup__main,.staffswap-setup__side{display:grid;gap:20px;align-content:start}.staffswap-setup__card{background:#fff;border:1px solid #d9e1dc;border-radius:12px;padding:26px;box-shadow:0 3px 12px rgba(6,31,61,.05)}.staffswap-setup__card h2{font:700 20px Manrope;margin:8px 0}.staffswap-setup__card p{color:#526052;font-size:14px;line-height:1.6}.staffswap-step{color:#005f2e;font:800 12px Manrope;letter-spacing:.1em}.staffswap-check{display:flex;gap:10px;align-items:center;border-top:1px solid #eef2f0;padding:12px 0;margin:0!important}.staffswap-check span{color:#8a9690;font-size:18px}.staffswap-check span.is-ready{color:#005f2e}.staffswap-tip{background:#eaf8ef;border-color:#9aebb0}.staffswap-tip strong{color:#005f2e}.staffswap-tip a{font-weight:600;color:#005f2e}@media(max-width:700px){.staffswap-setup__hero{padding:28px;}.staffswap-setup__mark{display:none}.staffswap-setup__grid{grid-template-columns:1fr}}' );
}
add_action( 'admin_enqueue_scripts', 'staffswap_setup_admin_styles' );

function staffswap_admin_settings_menu() { add_options_page( 'StaffSwap Settings', 'StaffSwap', 'manage_options', 'staffswap-settings', 'staffswap_admin_settings_screen' ); }
add_action( 'admin_menu', 'staffswap_admin_settings_menu' );
function staffswap_admin_settings_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$defaults = array( 'site_name' => 'StaffExchangeHub', 'hero_title' => 'Swap Your Workplace. Change Your Life.', 'hero_text' => 'Connect with verified professionals across Zambia who want to swap their workplace just like you. Secure, efficient, and professional workplace mobility.', 'primary_label' => 'Create Swap Post', 'secondary_label' => 'Browse Swaps', 'stats' => '12,000+|3,200+|150+|10|20+' );
	$settings = wp_parse_args( get_option( 'staffswap_settings', array() ), $defaults );
	if ( isset( $_POST['staffswap_save_settings'] ) && check_admin_referer( 'staffswap_save_settings', 'staffswap_settings_nonce' ) ) { foreach ( $defaults as $key => $default ) { $settings[ $key ] = sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? $default ) ); } update_option( 'staffswap_settings', $settings ); echo '<div class="notice notice-success is-dismissible"><p><strong>Settings saved.</strong> Your StaffSwap brand content is updated.</p></div>'; }
	?><div class="wrap staffswap-admin-settings"><div class="staffswap-settings-hero"><span class="staffswap-kicker">STAFFEXCHANGEHUB / CONTROL CENTRE</span><h1>StaffSwap settings</h1><p>Keep the marketplace voice, actions, and network numbers aligned from one place.</p></div><form method="post"><div class="staffswap-settings-layout"><main class="staffswap-settings-card"><h2>Homepage content</h2><p class="description">These values power the public landing page. You can still use Appearance > Customize for live visual previews.</p><div class="staffswap-settings-field"><label for="site_name">Site name</label><input class="regular-text" id="site_name" name="site_name" value="<?php echo esc_attr( $settings['site_name'] ); ?>"></div><div class="staffswap-settings-field"><label for="hero_title">Hero title</label><input class="large-text" id="hero_title" name="hero_title" value="<?php echo esc_attr( $settings['hero_title'] ); ?>"></div><div class="staffswap-settings-field"><label for="hero_text">Hero description</label><textarea class="large-text" id="hero_text" name="hero_text" rows="4"><?php echo esc_textarea( $settings['hero_text'] ); ?></textarea></div><div class="staffswap-settings-row"><div class="staffswap-settings-field"><label for="primary_label">Primary action</label><input class="regular-text" id="primary_label" name="primary_label" value="<?php echo esc_attr( $settings['primary_label'] ); ?>"></div><div class="staffswap-settings-field"><label for="secondary_label">Secondary action</label><input class="regular-text" id="secondary_label" name="secondary_label" value="<?php echo esc_attr( $settings['secondary_label'] ); ?>"></div></div><div class="staffswap-settings-field"><label for="stats">Network statistics</label><input class="large-text" id="stats" name="stats" value="<?php echo esc_attr( $settings['stats'] ); ?>"><p class="description">Use five values separated by the | character.</p></div><?php wp_nonce_field( 'staffswap_save_settings', 'staffswap_settings_nonce' ); ?><p><button type="submit" name="staffswap_save_settings" class="button button-primary button-hero">Save StaffSwap settings</button></p></main><aside class="staffswap-settings-side"><div class="staffswap-settings-card"><h2>Quick links</h2><a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">Open visual Customizer</a><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=staffswap-setup' ) ); ?>">Open setup guide</a><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">View live homepage</a></div><div class="staffswap-settings-card staffswap-settings-tip"><strong>Content tip</strong><p>Keep your headline focused on the career move. Use the description to explain trust, locations, and the professionals you serve.</p></div></aside></div></form></div><?php
}
function staffswap_admin_settings_styles( $hook ) {
	if ( 'settings_page_staffswap-settings' !== $hook ) { return; }
	wp_enqueue_style( 'dashicons' );
	wp_add_inline_style( 'dashicons', '.staffswap-admin-settings{max-width:1120px;margin-top:24px}.staffswap-settings-hero{background:#061f3d;color:#fff;border-radius:14px;padding:34px 40px;margin-bottom:20px}.staffswap-settings-hero h1{color:#fff;font:800 34px/1.2 Manrope,sans-serif;margin:10px 0}.staffswap-settings-hero p{color:#d8e5f6;font-size:15px;margin:0}.staffswap-settings-layout{display:grid;grid-template-columns:1fr 300px;gap:20px}.staffswap-settings-card{background:#fff;border:1px solid #d9e1dc;border-radius:12px;padding:26px;box-shadow:0 3px 12px rgba(6,31,61,.05)}.staffswap-settings-card h2{font:700 20px Manrope;margin:0 0 8px}.staffswap-settings-field{margin:22px 0}.staffswap-settings-field label{display:block;font-weight:700;margin-bottom:7px}.staffswap-settings-field input,.staffswap-settings-field textarea{border-color:#becabc;border-radius:7px;padding:9px 11px}.staffswap-settings-row{display:grid;grid-template-columns:1fr 1fr;gap:18px}.staffswap-settings-side{display:grid;gap:20px;align-content:start}.staffswap-settings-side .button{display:block;margin-top:10px;text-align:center}.staffswap-settings-tip{background:#eaf8ef;border-color:#9aebb0}.staffswap-settings-tip strong{color:#005f2e}@media(max-width:700px){.staffswap-settings-layout{grid-template-columns:1fr}.staffswap-settings-row{grid-template-columns:1fr}}' );
}
add_action( 'admin_enqueue_scripts', 'staffswap_admin_settings_styles' );