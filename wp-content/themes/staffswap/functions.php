<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_setup() { add_theme_support( 'title-tag' ); add_theme_support( 'post-thumbnails' ); add_theme_support( 'custom-logo', array( 'height' => 48, 'width' => 220, 'flex-height' => true, 'flex-width' => true ) ); add_theme_support( 'align-wide' ); add_theme_support( 'editor-styles' ); add_theme_support( 'elementor' ); add_post_type_support( 'page', 'elementor' ); add_editor_style( 'style.css' ); register_nav_menus( array( 'primary' => __( 'Primary Menu', 'staffswap' ) ) ); }
add_action( 'after_setup_theme', 'staffswap_setup' );
function staffswap_load_textdomain() { load_theme_textdomain( 'staffswap', get_template_directory() . '/languages' ); }
add_action( 'after_setup_theme', 'staffswap_load_textdomain', 11 );
function staffswap_assets() { wp_enqueue_style( 'staffswap-fonts', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap', array(), null ); wp_enqueue_style( 'dashicons' ); wp_enqueue_style( 'staffswap-style', get_stylesheet_uri(), array( 'staffswap-fonts', 'dashicons' ), filemtime( get_stylesheet_directory() . '/style.css' ) ); wp_enqueue_script( 'staffswap-main', get_template_directory_uri() . '/assets/js/main.js', array(), filemtime( get_template_directory() . '/assets/js/main.js' ), true ); }
add_action( 'wp_enqueue_scripts', 'staffswap_assets' );
function staffswap_account_menu_shortcode() {
	$user = wp_get_current_user();
	ob_start(); ?>
	<div class="profile-menu" data-profile-menu>
		<button class="profile-cluster" type="button" data-profile-trigger aria-expanded="false" aria-haspopup="true">
			<span class="profile-avatar"><?php if ( is_user_logged_in() ) : ?><?php echo esc_html( strtoupper( substr( $user->display_name, 0, 1 ) ) ); ?><?php else : ?><span class="dashicons dashicons-admin-users" aria-hidden="true"></span><?php endif; ?></span>
			<span><strong><?php echo is_user_logged_in() ? esc_html( $user->display_name ) : esc_html__( 'Welcome', 'staffswap' ); ?></strong><small><?php echo is_user_logged_in() ? esc_html__( 'Open account menu', 'staffswap' ) : esc_html__( 'Sign in or register', 'staffswap' ); ?></small></span><span class="dashicons dashicons-arrow-down-alt2 profile-menu__chevron" aria-hidden="true"></span>
		</button>
		<div class="profile-menu__dropdown" data-profile-dropdown hidden>
			<?php if ( is_user_logged_in() ) : ?>
				<p class="profile-menu__label"><?php echo esc_html__( 'Your workspace', 'staffswap' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>"><span class="dashicons dashicons-dashboard" aria-hidden="true"></span><?php echo esc_html__( 'Dashboard', 'staffswap' ); ?></a><a href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php echo esc_html__( 'Create listing', 'staffswap' ); ?></a><a href="<?php echo esc_url( home_url( '/messages/' ) ); ?>"><span class="dashicons dashicons-email-alt" aria-hidden="true"></span><?php echo esc_html__( 'Messages', 'staffswap' ); ?></a><a href="<?php echo esc_url( home_url( '/offers/' ) ); ?>"><span class="dashicons dashicons-megaphone" aria-hidden="true"></span><?php echo esc_html__( 'Offers', 'staffswap' ); ?></a><a href="<?php echo esc_url( home_url( '/verification/' ) ); ?>"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php echo esc_html__( 'Verification', 'staffswap' ); ?></a><a class="profile-menu__logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><span class="dashicons dashicons-external" aria-hidden="true"></span><?php echo esc_html__( 'Log out', 'staffswap' ); ?></a>
			<?php else : ?>
				<p class="profile-menu__label"><?php echo esc_html__( 'Join the exchange network', 'staffswap' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>"><span class="dashicons dashicons-unlock" aria-hidden="true"></span><?php echo esc_html__( 'Sign in', 'staffswap' ); ?></a><a href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><span class="dashicons dashicons-id-alt" aria-hidden="true"></span><?php echo esc_html__( 'Create account', 'staffswap' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'staffswap_account_menu', 'staffswap_account_menu_shortcode' );
function staffswap_fallback_menu() { echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'staffswap' ) . '</a></li><li><a href="' . esc_url( home_url( '/swaps/' ) ) . '">' . esc_html__( 'Find Swaps', 'staffswap' ) . '</a></li><li><a href="' . esc_url( home_url( '/how-it-works/' ) ) . '">' . esc_html__( 'How It Works', 'staffswap' ) . '</a></li><li><a href="' . esc_url( home_url( '/success-stories/' ) ) . '">' . esc_html__( 'Success Stories', 'staffswap' ) . '</a></li><li><a href="' . esc_url( home_url( '/resources/' ) ) . '">' . esc_html__( 'Resources', 'staffswap' ) . '</a></li><li><a href="' . esc_url( home_url( '/blog/' ) ) . '">' . esc_html__( 'Blog', 'staffswap' ) . '</a></li><li><a href="' . esc_url( home_url( '/pricing/' ) ) . '">' . esc_html__( 'Pricing', 'staffswap' ) . '</a></li></ul>'; }

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
	$wp_customize->add_control( new WP_Customize_Cropped_Image_Control( $wp_customize, 'custom_logo', array( 'label' => __( 'Header logo', 'staffswap' ), 'section' => 'title_tagline', 'priority' => 8 ) ) );
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
function staffswap_builder_location( $location ) {
	if ( ! function_exists( 'elementor_theme_do_location' ) ) { return false; }
	ob_start();
	$rendered = elementor_theme_do_location( $location );
	$output = trim( ob_get_clean() );
	if ( $output ) { echo $output; return true; }
	return (bool) $rendered && false;
}
function staffswap_has_builder_content( $post_id = 0 ) { return (bool) get_post_meta( $post_id ?: get_the_ID(), '_elementor_data', true ) || isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ); }
function staffswap_enable_elementor_content_types() { foreach ( array( 'page', 'post', 'swap_listing', 'staff_resource' ) as $post_type ) { add_post_type_support( $post_type, 'elementor' ); } }
add_action( 'init', 'staffswap_enable_elementor_content_types', 30 );

// Success Stories were previously three testimonials hardcoded in the template; now they're an editable post type.
function staffswap_testimonial_post_type() {
	register_post_type( 'staff_testimonial', array( 'labels' => array( 'name' => 'Success Stories', 'singular_name' => 'Success Story', 'add_new_item' => 'Add Success Story' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=swap_listing', 'menu_icon' => 'dashicons-star-filled', 'supports' => array( 'title', 'editor' ) ) );
}
add_action( 'init', 'staffswap_testimonial_post_type' );

function staffswap_testimonial_meta_box() {
	add_meta_box( 'staffswap_testimonial_details', 'Story Details', 'staffswap_testimonial_meta_box_render', 'staff_testimonial', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'staffswap_testimonial_meta_box' );

function staffswap_testimonial_meta_box_render( $post ) {
	wp_nonce_field( 'staffswap_save_testimonial', 'staffswap_testimonial_nonce' );
	$role = get_post_meta( $post->ID, '_staffswap_testimonial_role', true );
	$variant = get_post_meta( $post->ID, '_staffswap_testimonial_variant', true ) ?: 'default';
	echo '<p><label for="staffswap_testimonial_role"><strong>Role &amp; location</strong></label><br><input type="text" id="staffswap_testimonial_role" name="staffswap_testimonial_role" class="widefat" value="' . esc_attr( $role ) . '" placeholder="e.g. Registered Nurse · Ndola"></p>';
	echo '<p><label for="staffswap_testimonial_variant"><strong>Accent colour</strong></label><br><select id="staffswap_testimonial_variant" name="staffswap_testimonial_variant" class="widefat"><option value="default" ' . selected( $variant, 'default', false ) . '>Green (default)</option><option value="blue" ' . selected( $variant, 'blue', false ) . '>Blue</option><option value="gold" ' . selected( $variant, 'gold', false ) . '>Gold</option></select></p>';
	echo '<p class="description">Title is the member\'s name. The editor content is the quote.</p>';
}

function staffswap_testimonial_meta_box_save( $post_id ) {
	if ( ! isset( $_POST['staffswap_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['staffswap_testimonial_nonce'] ) ), 'staffswap_save_testimonial' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	update_post_meta( $post_id, '_staffswap_testimonial_role', sanitize_text_field( wp_unslash( $_POST['staffswap_testimonial_role'] ?? '' ) ) );
	$variant = sanitize_key( wp_unslash( $_POST['staffswap_testimonial_variant'] ?? 'default' ) );
	update_post_meta( $post_id, '_staffswap_testimonial_variant', in_array( $variant, array( 'default', 'blue', 'gold' ), true ) ? $variant : 'default' );
}
add_action( 'save_post_staff_testimonial', 'staffswap_testimonial_meta_box_save' );

function staffswap_seed_testimonials() {
	if ( get_option( 'staffswap_testimonials_seeded' ) ) { return; }
	update_option( 'staffswap_testimonials_seeded', '1' );
	if ( get_posts( array( 'post_type' => 'staff_testimonial', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) { return; }
	$seed = array(
		array( 'name' => 'Lydia Mwansa', 'role' => 'Registered Nurse · Ndola', 'quote' => 'The process was simple, secure and fast. I found a placement that works better for my family.', 'variant' => 'default' ),
		array( 'name' => 'Brian Chileshe', 'role' => 'Maths Teacher · Southern Province', 'quote' => 'I could compare real institutions and speak to someone who understood the work before making a decision.', 'variant' => 'blue' ),
		array( 'name' => 'Robert Tembo', 'role' => 'Bank Officer · Kabwe', 'quote' => 'StaffExchangeHub made a complicated career move feel organised and professional from the first post.', 'variant' => 'gold' ),
	);
	foreach ( $seed as $story ) {
		$post_id = wp_insert_post( array( 'post_type' => 'staff_testimonial', 'post_title' => $story['name'], 'post_content' => $story['quote'], 'post_status' => 'publish' ) );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_staffswap_testimonial_role', $story['role'] );
			update_post_meta( $post_id, '_staffswap_testimonial_variant', $story['variant'] );
		}
	}
}
add_action( 'after_switch_theme', 'staffswap_seed_testimonials' );
add_action( 'admin_init', 'staffswap_seed_testimonials' );

function staffswap_testimonials_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 3 ), $atts, 'staffswap_success_stories' );
	$stories = get_posts( array( 'post_type' => 'staff_testimonial', 'post_status' => 'publish', 'posts_per_page' => absint( $atts['limit'] ), 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( ! $stories ) { return ''; }
	$initials = function ( $name ) { $parts = preg_split( '/\s+/', trim( $name ) ); return strtoupper( substr( $parts[0] ?? '', 0, 1 ) . substr( end( $parts ) ?: '', 0, 1 ) ); };
	ob_start(); ?><section class="stories-grid"><?php foreach ( $stories as $story ) : $variant = get_post_meta( $story->ID, '_staffswap_testimonial_variant', true ) ?: 'default'; $variant_class = 'default' === $variant ? '' : ' story-card--' . $variant; $avatar_class = 'default' === $variant ? '' : ' featured-avatar--' . $variant; ?><article class="story-card<?php echo esc_attr( $variant_class ); ?>"><span class="featured-avatar<?php echo esc_attr( $avatar_class ); ?>"><?php echo esc_html( $initials( $story->post_title ) ); ?></span><p class="story-quote">&ldquo;<?php echo esc_html( $story->post_content ); ?>&rdquo;</p><strong><?php echo esc_html( $story->post_title ); ?></strong><small><?php echo esc_html( get_post_meta( $story->ID, '_staffswap_testimonial_role', true ) ); ?></small></article><?php endforeach; ?></section><?php return ob_get_clean();
}
add_shortcode( 'staffswap_success_stories', 'staffswap_testimonials_shortcode' );

function staffswap_random_testimonial() {
	$story = get_posts( array( 'post_type' => 'staff_testimonial', 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'rand' ) );
	if ( ! $story ) { return false; }
	$story = $story[0];
	$parts = preg_split( '/\s+/', trim( $story->post_title ) );
	return array(
		'name' => $story->post_title,
		'quote' => $story->post_content,
		'role' => get_post_meta( $story->ID, '_staffswap_testimonial_role', true ),
		'initials' => strtoupper( substr( $parts[0] ?? '', 0, 1 ) . substr( end( $parts ) ?: '', 0, 1 ) ),
	);
}

function staffswap_theme_activated() { update_option( 'staffswap_show_setup', '1' ); }
add_action( 'after_switch_theme', 'staffswap_theme_activated' );

// Setup is now a tab inside Theme Options rather than its own admin page.
function staffswap_setup_menu() {}

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
		'how' => array( 'title' => 'How It Works', 'slug' => 'how-it-works', 'content' => '<h1>How StaffExchangeHub Works</h1><p>Build your profile, post a request, compare matches, connect securely, and complete your workplace exchange.</p>' ),
		'success' => array( 'title' => 'Success Stories', 'slug' => 'success-stories', 'content' => '<h1>Success Stories</h1><p>Read how professionals across Zambia have found better workplace placements.</p>' ),
		'blog' => array( 'title' => 'Blog', 'slug' => 'blog', 'content' => '<h1>StaffExchangeHub Blog</h1><p>Practical advice for workplace mobility, career growth, and professional exchange.</p>' ),
		'pricing' => array( 'title' => 'Pricing', 'slug' => 'pricing', 'content' => '<h1>Plans for every professional move</h1><p>Start with the free exchange network and choose premium visibility when you need it.</p>' ),
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
		$menu_keys = array( 'home', 'swaps', 'how', 'success', 'resources', 'blog', 'pricing' );
		$existing_ids = array(); foreach ( (array) $items as $item ) { $existing_ids[] = (int) $item->object_id; }
		foreach ( $menu_keys as $key ) { if ( ! empty( $created[ $key ] ) && ! in_array( (int) $created[ $key ], $existing_ids, true ) ) { wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $pages[ $key ]['title'], 'menu-item-object' => 'page', 'menu-item-object-id' => $created[ $key ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) ); } }
		$locations = get_theme_mod( 'nav_menu_locations', array() ); $locations['primary'] = $menu_id; set_theme_mod( 'nav_menu_locations', $locations );
	}
	update_option( 'staffswap_show_setup', '0' );
	return $created;
}

function staffswap_setup_status() {
	$menu = wp_get_nav_menu_object( 'StaffSwap Main Menu' );
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$required = array( 'home', 'swaps', 'search', 'create-swap', 'register', 'sign-in', 'my-profile', 'resources', 'blog', 'pricing' );
	$pages = array();
	foreach ( $required as $slug ) { $pages[ $slug ] = (bool) get_page_by_path( $slug ); }
	return array( 'menu' => (bool) $menu, 'menu_assigned' => $menu && ! empty( $locations['primary'] ) && (int) $locations['primary'] === (int) $menu->term_id, 'pages' => $pages, 'elementor' => defined( 'ELEMENTOR_VERSION' ), 'elementskit' => defined( 'ELEMENTSKIT_VERSION' ), 'front_page' => 'page' === get_option( 'show_on_front' ) && (bool) get_option( 'page_on_front' ) );
}

function staffswap_handle_setup() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to run StaffSwap setup.', 'staffswap' ) ); }
	check_admin_referer( 'staffswap_run_setup', 'staffswap_setup_nonce' );
	$created = staffswap_setup_pages();
	if ( is_array( $created ) ) { wp_safe_redirect( add_query_arg( array( 'page' => 'staffswap-theme-options', 'tab' => 'setup', 'staffswap_setup' => 'complete' ), admin_url( 'themes.php' ) ) ); exit; }
	wp_safe_redirect( add_query_arg( array( 'page' => 'staffswap-theme-options', 'tab' => 'setup', 'staffswap_setup' => 'failed' ), admin_url( 'themes.php' ) ) ); exit;
}
add_action( 'admin_post_staffswap_run_setup', 'staffswap_handle_setup' );

function staffswap_handle_repair() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to repair StaffSwap setup.', 'staffswap' ) ); }
	check_admin_referer( 'staffswap_repair_setup', 'staffswap_repair_nonce' );
	staffswap_setup_pages();
	wp_safe_redirect( add_query_arg( array( 'page' => 'staffswap-theme-options', 'tab' => 'setup', 'staffswap_setup' => 'repaired' ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_staffswap_repair_setup', 'staffswap_handle_repair' );

function staffswap_setup_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$notice = '';
	if ( isset( $_GET['staffswap_setup'] ) && 'complete' === sanitize_key( wp_unslash( $_GET['staffswap_setup'] ) ) ) { $notice = '<div class="notice notice-success is-dismissible"><p><strong>StaffSwap is ready.</strong> Your pages, homepage, and navigation were configured.</p></div>'; }
	if ( isset( $_GET['staffswap_setup'] ) && 'failed' === sanitize_key( wp_unslash( $_GET['staffswap_setup'] ) ) ) { $notice = '<div class="notice notice-error is-dismissible"><p>StaffSwap setup could not complete. Check your administrator permissions and try again.</p></div>'; }
	if ( isset( $_GET['staffswap_setup'] ) && 'repaired' === sanitize_key( wp_unslash( $_GET['staffswap_setup'] ) ) ) { $notice = '<div class="notice notice-success is-dismissible"><p><strong>Setup repaired.</strong> Pages, navigation, and homepage settings were checked again.</p></div>'; }
	$status = staffswap_setup_status();
	$plugins = array( 'staffswap-core/staffswap-core.php' => 'Swap marketplace', 'staffswap-resources/staffswap-resources.php' => 'Resources Centre', 'staffswap-profiles/staffswap-profiles.php' => 'Member profiles', 'staffswap-messaging/staffswap-messaging.php' => 'Private messaging' );
	if ( ! function_exists( 'is_plugin_active' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
	?><div class="wrap staffswap-setup"><div class="staffswap-setup__hero"><div><span class="staffswap-kicker">STAFFEXCHANGEHUB</span><h1>Set up your exchange network</h1><p>Build the essential pages, navigation, modules, and builder compatibility for your marketplace.</p></div><div class="staffswap-setup__mark">S</div></div><?php echo $notice; ?><div class="staffswap-setup__grid"><section class="staffswap-setup__main"><div class="staffswap-setup__card"><span class="staffswap-step">01</span><h2>Launch or repair the core experience</h2><p>Creates missing pages, sets Home as the front page, repairs the StaffSwap Main Menu, and refreshes the primary menu assignment.</p><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'staffswap_run_setup', 'staffswap_setup_nonce' ); ?><input type="hidden" name="action" value="staffswap_run_setup"><button class="button button-primary button-hero" type="submit">Run setup</button></form><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:10px"><?php wp_nonce_field( 'staffswap_repair_setup', 'staffswap_repair_nonce' ); ?><input type="hidden" name="action" value="staffswap_repair_setup"><button class="button" type="submit">Repair pages and menu</button></form></div><div class="staffswap-setup__card"><span class="staffswap-step">02</span><h2>System health</h2><p class="staffswap-check"><span class="<?php echo $status['menu_assigned'] ? 'is-ready' : ''; ?>"><?php echo $status['menu_assigned'] ? '&#10003;' : '&#9675;'; ?></span>Primary menu assigned</p><p class="staffswap-check"><span class="<?php echo $status['front_page'] ? 'is-ready' : ''; ?>"><?php echo $status['front_page'] ? '&#10003;' : '&#9675;'; ?></span>Homepage configured</p><p class="staffswap-check"><span class="<?php echo $status['elementor'] ? 'is-ready' : ''; ?>"><?php echo $status['elementor'] ? '&#10003;' : '&#9675;'; ?></span><?php echo $status['elementor'] ? 'Elementor detected' : 'Elementor not installed'; ?></p><?php if ( $status['elementskit'] ) : ?><p class="staffswap-check"><span>&#9888;</span>ElementsKit detected: check that no empty header template is assigned.</p><?php endif; ?><p><a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">Edit menu</a> <a class="button" href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>">Reading settings</a></p></div><div class="staffswap-setup__card"><span class="staffswap-step">03</span><h2>Shape your brand</h2><p>Update hero message, CTA labels, homepage stats, and primary color from the visual Customizer or StaffSwap settings.</p><a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">Open Customizer</a> <a class="button" href="<?php echo esc_url( admin_url( 'options-general.php?page=staffswap-settings' ) ); ?>">Open StaffSwap settings</a></div></section><aside class="staffswap-setup__side"><div class="staffswap-setup__card"><h2>Module checklist</h2><?php foreach ( $plugins as $plugin => $label ) : ?><p class="staffswap-check"><span class="<?php echo is_plugin_active( $plugin ) ? 'is-ready' : ''; ?>"><?php echo is_plugin_active( $plugin ) ? '&#10003;' : '&#9675;'; ?></span><?php echo esc_html( $label ); ?></p><?php endforeach; ?></div><div class="staffswap-setup__card staffswap-setup__tip"><strong>Builder compatibility</strong><p>Use Elementor Full Width or Default for the homepage. Do not assign an empty ElementsKit header, or it will replace the StaffSwap header.</p><a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Manage plugins</a></div></aside></div></div><?php
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

remove_action( 'admin_menu', 'staffswap_admin_settings_menu' );
function staffswap_theme_options_menu() {
	add_theme_page( 'StaffSwap Theme Options', 'Theme Options', 'manage_options', 'staffswap-theme-options', 'staffswap_theme_options_hub' );
}
add_action( 'admin_menu', 'staffswap_theme_options_menu' );

function staffswap_theme_options_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$defaults = array( 'site_name' => 'StaffExchangeHub', 'hero_title' => 'Swap Your Workplace. Change Your Life.', 'hero_text' => 'Connect with verified professionals across Zambia who want to swap their workplace just like you. Secure, efficient, and professional workplace mobility.', 'primary_label' => 'Create Swap Post', 'secondary_label' => 'Browse Swaps', 'stats' => '12,000+|3,200+|150+|10|20+', 'primary_color' => '#00bb7f' );
	$settings = wp_parse_args( get_option( 'staffswap_settings', array() ), $defaults );
	if ( isset( $_POST['staffswap_save_theme_options'] ) && check_admin_referer( 'staffswap_save_theme_options', 'staffswap_theme_options_nonce' ) ) {
		foreach ( $defaults as $key => $default ) { $settings[ $key ] = 'primary_color' === $key ? sanitize_hex_color( wp_unslash( $_POST[ $key ] ?? $default ) ) : sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? $default ) ); }
		update_option( 'staffswap_settings', $settings );
		echo '<div class="notice notice-success is-dismissible"><p>Theme options saved.</p></div>';
	}
	$links = array( 'StaffSwap Setup' => admin_url( 'themes.php?page=staffswap-setup' ), 'Menus' => admin_url( 'nav-menus.php' ), 'Listings' => admin_url( 'edit.php?post_type=swap_listing' ), 'Verification Queue' => admin_url( 'users.php?page=staffswap-verification-queue' ), 'Messages' => admin_url( 'edit.php?post_type=staff_message' ), 'Offers' => admin_url( 'edit.php?post_type=staffswap_offer' ), 'Plugins & Payments' => admin_url( 'plugins.php' ) );
	?><div class="wrap staffswap-theme-options"><header class="staffswap-options-hero"><div><span>STAFFEXCHANGEHUB / THEME CONTROL</span><h1>Theme Options</h1><p>Manage the public brand, homepage actions, and StaffSwap operations from one workspace.</p></div><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">View site</a></header><form method="post"><div class="staffswap-options-layout"><main><section class="staffswap-options-card"><div class="staffswap-options-heading"><div><span class="dashicons dashicons-admin-appearance"></span><h2>Brand and Homepage</h2></div><p>Changes appear across the public StaffExchangeHub experience.</p></div><div class="staffswap-options-grid"><label>Site name<input name="site_name" value="<?php echo esc_attr( $settings['site_name'] ); ?>"></label><label>Primary action<input name="primary_label" value="<?php echo esc_attr( $settings['primary_label'] ); ?>"></label><label>Secondary action<input name="secondary_label" value="<?php echo esc_attr( $settings['secondary_label'] ); ?>"></label><label>Primary action color<input name="primary_color" type="color" value="<?php echo esc_attr( $settings['primary_color'] ); ?>"></label><label class="staffswap-options-full">Homepage headline<input name="hero_title" value="<?php echo esc_attr( $settings['hero_title'] ); ?>"></label><label class="staffswap-options-full">Homepage description<textarea name="hero_text" rows="4"><?php echo esc_textarea( $settings['hero_text'] ); ?></textarea></label><label class="staffswap-options-full">Network statistics<input name="stats" value="<?php echo esc_attr( $settings['stats'] ); ?>"><small>Five values separated with the | character.</small></label></div><?php wp_nonce_field( 'staffswap_save_theme_options', 'staffswap_theme_options_nonce' ); ?><p><button type="submit" name="staffswap_save_theme_options" class="button button-primary">Save Theme Options</button></p></section></main><aside><section class="staffswap-options-card staffswap-options-card--nav"><div class="staffswap-options-heading"><div><span class="dashicons dashicons-admin-tools"></span><h2>Operations</h2></div><p>Open the administrative tools behind the member experience.</p></div><?php foreach ( $links as $label => $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a><?php endforeach; ?></section><section class="staffswap-options-status"><span class="dashicons dashicons-shield-alt"></span><div><strong>Theme control centre</strong><p>Use StaffSwap Setup to repair pages, homepage assignment, and navigation.</p></div></section></aside></div></form></div><?php
}

function staffswap_theme_options_css() {
	$settings = get_option( 'staffswap_settings', array() );
	$color = sanitize_hex_color( $settings['primary_color'] ?? '' );
	if ( $color ) { echo '<style>:root{--primary:' . esc_attr( $color ) . ';}</style>'; }
}
add_action( 'wp_head', 'staffswap_theme_options_css', 20 );

function staffswap_theme_options_styles( $hook ) {
	if ( 'appearance_page_staffswap-theme-options' !== $hook ) { return; }
	wp_enqueue_style( 'dashicons' );
	wp_add_inline_style( 'dashicons', '.staffswap-theme-options{max-width:1180px;margin-top:24px}.staffswap-options-hero{align-items:center;background:#0d2240;color:#fff;display:flex;justify-content:space-between;padding:34px 40px}.staffswap-options-hero span{color:#a4f4cf;font-size:11px;font-weight:700;letter-spacing:.1em}.staffswap-options-hero h1{color:#fff;font:700 34px/1.2 "Space Grotesk",sans-serif;margin:8px 0}.staffswap-options-hero p{color:#bedbff;margin:0}.staffswap-options-hero .button{background:#00bb7f;border-color:#00bb7f;color:#06251d;font-weight:700}.staffswap-options-layout{display:grid;grid-template-columns:minmax(0,1fr) 290px;gap:20px;margin-top:20px}.staffswap-options-card{background:#fff;border:1px solid #d9e1ec;padding:26px}.staffswap-options-heading{border-bottom:1px solid #e2e8f0;margin-bottom:22px;padding-bottom:16px}.staffswap-options-heading>div{align-items:center;display:flex;gap:9px}.staffswap-options-heading .dashicons{color:#00a875}.staffswap-options-heading h2{font:700 19px "Space Grotesk",sans-serif;margin:0}.staffswap-options-heading p{color:#526074;font-size:13px;margin:8px 0 0}.staffswap-options-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.staffswap-options-grid label{color:#0f172a;display:grid;font-weight:700;gap:7px}.staffswap-options-grid input,.staffswap-options-grid textarea{border:1px solid #cbd5e1;border-radius:4px;font:14px "DM Sans",sans-serif;padding:9px 10px;width:100%}.staffswap-options-grid input[type=color]{height:40px;padding:3px}.staffswap-options-grid small{color:#64748b;font-weight:400}.staffswap-options-full{grid-column:1/-1}.staffswap-options-card--nav a{align-items:center;border-top:1px solid #e2e8f0;color:#155dfc;display:flex;font-weight:700;justify-content:space-between;padding:12px 0;text-decoration:none}.staffswap-options-card--nav a:hover{color:#0f766e}.staffswap-options-status{align-items:flex-start;background:#ecfdf5;border:1px solid #a4f4cf;display:flex;gap:12px;margin-top:20px;padding:18px}.staffswap-options-status .dashicons{color:#0f766e}.staffswap-options-status strong{color:#0f766e}.staffswap-options-status p{color:#526074;font-size:13px;line-height:1.5;margin:5px 0 0}@media(max-width:760px){.staffswap-options-hero{align-items:flex-start;flex-direction:column;padding:26px}.staffswap-options-layout{grid-template-columns:1fr}.staffswap-options-grid{grid-template-columns:1fr}}' );
}
add_action( 'admin_enqueue_scripts', 'staffswap_theme_options_styles' );

// Single tabbed "Theme Options" page: every StaffSwap setting lives here, switched with JS tabs, no extra admin pages to click through to.
function staffswap_theme_options_hub_tabs() {
	return array(
		'brand' => array( 'label' => 'Brand & Homepage', 'icon' => 'dashicons-admin-appearance' ),
		'plans' => array( 'label' => 'Membership Plans', 'icon' => 'dashicons-tickets-alt' ),
		'payments' => array( 'label' => 'Payment Gateway', 'icon' => 'dashicons-money-alt' ),
		'sms' => array( 'label' => 'SMS Notifications', 'icon' => 'dashicons-smartphone' ),
		'ai' => array( 'label' => 'AI Drafting', 'icon' => 'dashicons-lightbulb' ),
		'setup' => array( 'label' => 'Setup & Health', 'icon' => 'dashicons-admin-tools' ),
		'links' => array( 'label' => 'Quick Links', 'icon' => 'dashicons-admin-links' ),
	);
}

function staffswap_theme_options_hub() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$tabs = staffswap_theme_options_hub_tabs();
	$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'brand';
	$active = isset( $tabs[ $requested ] ) ? $requested : 'brand';
	echo '<div class="wrap staffswap-hub"><header class="staffswap-hub__hero"><div><span>STAFFEXCHANGEHUB / CONTROL CENTRE</span><h1>Theme Options</h1><p>Every StaffSwap setting lives on this one page — switch tabs below, nothing here sends you to another screen.</p></div><a class="button" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">View site</a></header>';
	echo '<nav class="staffswap-hub__tabs">';
	foreach ( $tabs as $key => $tab ) { echo '<button type="button" class="staffswap-hub__tab' . ( $key === $active ? ' is-active' : '' ) . '" data-staffswap-tab="' . esc_attr( $key ) . '"><span class="dashicons ' . esc_attr( $tab['icon'] ) . '"></span>' . esc_html( $tab['label'] ) . '</button>'; }
	echo '</nav><div class="staffswap-hub__panels">';
	foreach ( $tabs as $key => $tab ) {
		echo '<section class="staffswap-hub__panel' . ( $key === $active ? ' is-active' : '' ) . '" data-staffswap-panel="' . esc_attr( $key ) . '">';
		if ( function_exists( 'staffswap_hub_tab_' . $key ) ) { call_user_func( 'staffswap_hub_tab_' . $key ); }
		echo '</section>';
	}
	echo '</div></div>';
}

function staffswap_hub_tab_brand() {
	$defaults = array( 'site_name' => 'StaffExchangeHub', 'hero_title' => 'Swap Your Workplace. Change Your Life.', 'hero_text' => 'Connect with verified professionals across Zambia who want to swap their workplace just like you. Secure, efficient, and professional workplace mobility.', 'primary_label' => 'Create Swap Post', 'secondary_label' => 'Browse Swaps', 'stats' => '12,000+|3,200+|150+|10|20+', 'primary_color' => '#00bb7f' );
	$settings = wp_parse_args( get_option( 'staffswap_settings', array() ), $defaults );
	if ( isset( $_POST['staffswap_save_theme_options'] ) && check_admin_referer( 'staffswap_save_theme_options', 'staffswap_theme_options_nonce' ) ) {
		foreach ( $defaults as $key => $default ) { $settings[ $key ] = 'primary_color' === $key ? sanitize_hex_color( wp_unslash( $_POST[ $key ] ?? $default ) ) : sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? $default ) ); }
		update_option( 'staffswap_settings', $settings );
		echo '<div class="notice notice-success is-dismissible"><p>Brand settings saved.</p></div>';
	}
	?>
	<form method="post">
		<div class="staffswap-hub__grid">
			<label>Site name<input name="site_name" value="<?php echo esc_attr( $settings['site_name'] ); ?>"></label>
			<label>Primary action<input name="primary_label" value="<?php echo esc_attr( $settings['primary_label'] ); ?>"></label>
			<label>Secondary action<input name="secondary_label" value="<?php echo esc_attr( $settings['secondary_label'] ); ?>"></label>
			<label>Primary action color<input name="primary_color" type="color" value="<?php echo esc_attr( $settings['primary_color'] ); ?>"></label>
			<label class="staffswap-hub__full">Homepage headline<input name="hero_title" value="<?php echo esc_attr( $settings['hero_title'] ); ?>"></label>
			<label class="staffswap-hub__full">Homepage description<textarea name="hero_text" rows="4"><?php echo esc_textarea( $settings['hero_text'] ); ?></textarea></label>
			<label class="staffswap-hub__full">Network statistics<input name="stats" value="<?php echo esc_attr( $settings['stats'] ); ?>"><small>Five values separated with the | character.</small></label>
		</div>
		<?php wp_nonce_field( 'staffswap_save_theme_options', 'staffswap_theme_options_nonce' ); ?>
		<p><button type="submit" name="staffswap_save_theme_options" class="button button-primary">Save brand settings</button></p>
	</form>
	<?php
}

function staffswap_hub_tab_plans() {
	$plan_defaults = function_exists( 'staffswap_wc_plans' ) ? staffswap_wc_plans() : array(
		'month' => array( 'title' => 'StaffSwap VIP Gold - 1 Month', 'price' => '99', 'duration' => '1 month' ),
		'quarter' => array( 'title' => 'StaffSwap VIP Gold - 3 Months', 'price' => '249', 'duration' => '3 months' ),
		'lifetime' => array( 'title' => 'StaffSwap VIP Gold - Lifetime', 'price' => '799', 'duration' => 'lifetime' ),
	);
	if ( isset( $_POST['staffswap_save_plans'] ) && check_admin_referer( 'staffswap_save_plans', 'staffswap_plans_nonce' ) ) {
		$overrides = array();
		foreach ( array_keys( $plan_defaults ) as $plan ) { $overrides[ $plan ] = array( 'title' => sanitize_text_field( wp_unslash( $_POST[ 'plan_title_' . $plan ] ?? '' ) ), 'price' => sanitize_text_field( wp_unslash( $_POST[ 'plan_price_' . $plan ] ?? '' ) ) ); }
		update_option( 'staffswap_plan_settings', $overrides );
		if ( function_exists( 'staffswap_wc_sync_plan_products' ) ) { staffswap_wc_sync_plan_products(); }
		echo '<div class="notice notice-success is-dismissible"><p>Membership plan pricing saved.</p></div>';
		if ( function_exists( 'staffswap_wc_plans' ) ) { $plan_defaults = staffswap_wc_plans(); }
	}
	if ( ! class_exists( 'WooCommerce' ) ) { echo '<p>Install and activate WooCommerce to sell StaffSwap VIP Gold memberships.</p>'; }
	?>
	<form method="post">
		<div class="staffswap-hub__plans">
			<?php foreach ( $plan_defaults as $plan => $data ) : ?>
				<div class="staffswap-hub__plan-card">
					<h3><?php echo esc_html( ucfirst( $plan ) ); ?> <small>(<?php echo esc_html( $data['duration'] ?? '' ); ?>)</small></h3>
					<label>Plan title<input name="plan_title_<?php echo esc_attr( $plan ); ?>" value="<?php echo esc_attr( $data['title'] ); ?>"></label>
					<label>Price (ZMW)<input name="plan_price_<?php echo esc_attr( $plan ); ?>" value="<?php echo esc_attr( $data['price'] ); ?>"></label>
				</div>
			<?php endforeach; ?>
		</div>
		<?php wp_nonce_field( 'staffswap_save_plans', 'staffswap_plans_nonce' ); ?>
		<p><button type="submit" name="staffswap_save_plans" class="button button-primary">Save plan pricing</button></p>
	</form>
	<?php
}

function staffswap_hub_tab_payments() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { echo '<p>Install and activate WooCommerce to configure the Lipila Mobile Money gateway.</p>'; return; }
	$settings = get_option( 'woocommerce_staffswap_lipila_settings', array() );
	$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), array( 'enabled' => 'no', 'title' => 'Mobile Money', 'description' => 'Pay securely using MTN Mobile Money, Airtel Money, or Zamtel Kwacha.', 'api_key' => '' ) );
	if ( isset( $_POST['staffswap_save_payments'] ) && check_admin_referer( 'staffswap_save_payments', 'staffswap_payments_nonce' ) ) {
		$settings['enabled'] = isset( $_POST['lipila_enabled'] ) ? 'yes' : 'no';
		$settings['title'] = sanitize_text_field( wp_unslash( $_POST['lipila_title'] ?? $settings['title'] ) );
		$settings['description'] = sanitize_textarea_field( wp_unslash( $_POST['lipila_description'] ?? $settings['description'] ) );
		$submitted_key = wp_unslash( $_POST['lipila_api_key'] ?? '' );
		if ( '' !== trim( (string) $submitted_key ) ) { $settings['api_key'] = sanitize_text_field( $submitted_key ); }
		update_option( 'woocommerce_staffswap_lipila_settings', $settings );
		echo '<div class="notice notice-success is-dismissible"><p>Payment gateway settings saved.</p></div>';
	}
	?>
	<form method="post">
		<div class="staffswap-hub__grid">
			<label class="staffswap-hub__checkbox"><input type="checkbox" name="lipila_enabled" <?php checked( 'yes', $settings['enabled'] ); ?>> Enable Lipila Mobile Money</label>
			<label>Gateway title<input name="lipila_title" value="<?php echo esc_attr( $settings['title'] ); ?>"></label>
			<label class="staffswap-hub__full">Customer description<textarea name="lipila_description" rows="3"><?php echo esc_textarea( $settings['description'] ); ?></textarea></label>
			<label class="staffswap-hub__full">Lipila secret key<input type="password" name="lipila_api_key" placeholder="<?php echo $settings['api_key'] ? 'Key saved — leave blank to keep it' : ''; ?>" autocomplete="off"><small>Stored securely; leave blank to keep the current key.</small></label>
		</div>
		<?php wp_nonce_field( 'staffswap_save_payments', 'staffswap_payments_nonce' ); ?>
		<p><button type="submit" name="staffswap_save_payments" class="button button-primary">Save payment settings</button></p>
	</form>
	<?php
}

function staffswap_hub_tab_sms() {
	$settings = get_option( 'staffswap_sms_settings', array() );
	$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), array( 'enabled' => 'no', 'api_token' => '', 'sender_id' => '' ) );
	if ( isset( $_POST['staffswap_save_sms'] ) && check_admin_referer( 'staffswap_save_sms', 'staffswap_sms_nonce' ) ) {
		$settings['enabled'] = isset( $_POST['sms_enabled'] ) ? 'yes' : 'no';
		$settings['sender_id'] = sanitize_text_field( wp_unslash( $_POST['sms_sender_id'] ?? $settings['sender_id'] ) );
		$submitted_token = wp_unslash( $_POST['sms_api_token'] ?? '' );
		if ( '' !== trim( (string) $submitted_token ) ) { $settings['api_token'] = sanitize_text_field( $submitted_token ); }
		update_option( 'staffswap_sms_settings', $settings );
		echo '<div class="notice notice-success is-dismissible"><p>SMS gateway settings saved.</p></div>';
	}
	?>
	<p>Members can opt in to SMS updates for new messages, offers, and matches from their Profile Settings once they add a mobile number.</p>
	<form method="post">
		<div class="staffswap-hub__grid">
			<label class="staffswap-hub__checkbox"><input type="checkbox" name="sms_enabled" <?php checked( 'yes', $settings['enabled'] ); ?>> Enable ExciteSMS notifications</label>
			<label>Sender ID<input name="sms_sender_id" value="<?php echo esc_attr( $settings['sender_id'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></label>
			<label class="staffswap-hub__full">ExciteSMS API token<input type="password" name="sms_api_token" placeholder="<?php echo $settings['api_token'] ? 'Token saved — leave blank to keep it' : ''; ?>" autocomplete="off"><small>Stored securely; leave blank to keep the current token.</small></label>
		</div>
		<?php wp_nonce_field( 'staffswap_save_sms', 'staffswap_sms_nonce' ); ?>
		<p><button type="submit" name="staffswap_save_sms" class="button button-primary">Save SMS settings</button></p>
	</form>
	<?php
}

function staffswap_hub_tab_ai() {
	$settings = get_option( 'staffswap_ai_settings', array() );
	$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), array( 'enabled' => 'no', 'model' => 'gpt-4o-mini', 'api_key' => '' ) );
	if ( isset( $_POST['staffswap_save_ai'] ) && check_admin_referer( 'staffswap_save_ai', 'staffswap_ai_nonce' ) ) {
		$settings['enabled'] = isset( $_POST['ai_enabled'] ) ? 'yes' : 'no';
		$settings['model'] = sanitize_text_field( wp_unslash( $_POST['ai_model'] ?? $settings['model'] ) );
		$submitted_key = wp_unslash( $_POST['ai_api_key'] ?? '' );
		if ( '' !== trim( (string) $submitted_key ) ) {
			$settings['api_key'] = sanitize_text_field( $submitted_key );
		}
		update_option( 'staffswap_ai_settings', $settings );
		echo '<div class="notice notice-success is-dismissible"><p>AI drafting settings saved.</p></div>';
	}
	?>
	<p>AI is optional. It can generate a concise summary paragraph for accepted swap documents, but the agreement itself should still be reviewed before it is used as a final business record.</p>
	<form method="post">
		<div class="staffswap-hub__grid">
			<label class="staffswap-hub__checkbox"><input type="checkbox" name="ai_enabled" <?php checked( 'yes', $settings['enabled'] ); ?>> Enable AI summary drafting</label>
			<label>Model<input name="ai_model" value="<?php echo esc_attr( $settings['model'] ); ?>" placeholder="gpt-4o-mini"></label>
			<label class="staffswap-hub__full">OpenAI API key<input type="password" name="ai_api_key" placeholder="<?php echo $settings['api_key'] ? 'Key saved — leave blank to keep it' : ''; ?>" autocomplete="off"><small>Stored securely; leave blank to keep the current key.</small></label>
		</div>
		<?php wp_nonce_field( 'staffswap_save_ai', 'staffswap_ai_nonce' ); ?>
		<p><button type="submit" name="staffswap_save_ai" class="button button-primary">Save AI settings</button></p>
	</form>
	<?php
}

function staffswap_hub_tab_setup() {
	if ( isset( $_GET['staffswap_setup'] ) ) {
		$state = sanitize_key( wp_unslash( $_GET['staffswap_setup'] ) );
		$messages = array(
			'complete' => array( 'success', 'StaffSwap is ready. Your pages, homepage, and navigation were configured.' ),
			'failed' => array( 'error', 'StaffSwap setup could not complete. Check your administrator permissions and try again.' ),
			'repaired' => array( 'success', 'Setup repaired. Pages, navigation, and homepage settings were checked again.' ),
		);
		if ( isset( $messages[ $state ] ) ) { echo '<div class="notice notice-' . esc_attr( $messages[ $state ][0] ) . ' is-dismissible"><p>' . esc_html( $messages[ $state ][1] ) . '</p></div>'; }
	}
	$status = staffswap_setup_status();
	?>
	<div class="staffswap-hub__setup">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'staffswap_run_setup', 'staffswap_setup_nonce' ); ?><input type="hidden" name="action" value="staffswap_run_setup"><button class="button button-primary" type="submit">Run setup</button></form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'staffswap_repair_setup', 'staffswap_repair_nonce' ); ?><input type="hidden" name="action" value="staffswap_repair_setup"><button class="button" type="submit">Repair pages and menu</button></form>
		<ul class="staffswap-hub__checks">
			<li class="<?php echo $status['menu_assigned'] ? 'is-ready' : ''; ?>">Primary menu assigned</li>
			<li class="<?php echo $status['front_page'] ? 'is-ready' : ''; ?>">Homepage configured</li>
			<li class="<?php echo $status['menu'] ? 'is-ready' : ''; ?>">StaffSwap Main Menu exists</li>
			<li class="<?php echo $status['elementor'] ? 'is-ready' : ''; ?>">Elementor active</li>
			<?php foreach ( $status['pages'] as $slug => $exists ) : ?>
				<li class="<?php echo $exists ? 'is-ready' : ''; ?>">Page /<?php echo esc_html( $slug ); ?>/</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

function staffswap_hub_tab_links() {
	$links = array(
		'Moderation Queue' => admin_url( 'edit.php?post_type=swap_listing&page=staffswap-listing-moderation' ),
		'Analytics' => admin_url( 'edit.php?post_type=swap_listing&page=staffswap-analytics' ),
		'Verification Queue' => admin_url( 'users.php?page=staffswap-verification-queue' ),
		'Listings' => admin_url( 'edit.php?post_type=swap_listing' ),
		'Messages' => admin_url( 'edit.php?post_type=staff_message' ),
		'Offers' => admin_url( 'edit.php?post_type=staffswap_offer' ),
		'Menus' => admin_url( 'nav-menus.php' ),
		'Plugins & Payments' => admin_url( 'plugins.php' ),
	);
	echo '<ul class="staffswap-hub__links">';
	foreach ( $links as $label => $url ) { echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '<span class="dashicons dashicons-arrow-right-alt2"></span></a></li>'; }
	echo '</ul>';
}

function staffswap_theme_options_hub_assets( $hook ) {
	if ( 'appearance_page_staffswap-theme-options' !== $hook ) { return; }
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_script( 'jquery' );
	wp_add_inline_style( 'dashicons', '.staffswap-hub{max-width:1180px;margin-top:24px}.staffswap-hub__hero{align-items:center;background:#0d2240;color:#fff;display:flex;justify-content:space-between;padding:34px 40px;border-radius:8px}.staffswap-hub__hero span{color:#a4f4cf;font-size:11px;font-weight:700;letter-spacing:.1em}.staffswap-hub__hero h1{color:#fff;font:700 32px/1.2 "Space Grotesk",sans-serif;margin:8px 0}.staffswap-hub__hero p{color:#bedbff;margin:0}.staffswap-hub__hero .button{background:#00bb7f;border-color:#00bb7f;color:#06251d;font-weight:700}.staffswap-hub__tabs{display:flex;flex-wrap:wrap;gap:8px;margin:20px 0}.staffswap-hub__tab{align-items:center;background:#fff;border:1px solid #d9e1ec;border-radius:6px;color:#334155;cursor:pointer;display:flex;font-weight:600;gap:6px;padding:10px 16px}.staffswap-hub__tab.is-active{background:#0d2240;border-color:#0d2240;color:#fff}.staffswap-hub__tab .dashicons{font-size:16px;height:16px;width:16px}.staffswap-hub__panel{background:#fff;border:1px solid #d9e1ec;border-radius:8px;display:none;padding:26px}.staffswap-hub__panel.is-active{display:block}.staffswap-hub__grid{display:grid;gap:18px;grid-template-columns:1fr 1fr}.staffswap-hub__grid label{color:#0f172a;display:grid;font-weight:700;gap:7px}.staffswap-hub__grid input,.staffswap-hub__grid textarea{border:1px solid #cbd5e1;border-radius:4px;font:14px "DM Sans",sans-serif;padding:9px 10px;width:100%}.staffswap-hub__grid input[type=color]{height:40px;padding:3px}.staffswap-hub__full{grid-column:1/-1}.staffswap-hub__checkbox{align-items:center;display:flex!important;flex-direction:row!important;font-weight:600!important;gap:8px}.staffswap-hub__plans{display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}.staffswap-hub__plan-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:18px;display:grid;gap:12px}.staffswap-hub__plan-card h3{margin:0}.staffswap-hub__plan-card label{display:grid;font-weight:600;gap:6px}.staffswap-hub__setup form{display:inline-block;margin-right:10px}.staffswap-hub__checks{border-top:1px solid #e2e8f0;display:grid;gap:0;margin-top:20px}.staffswap-hub__checks li{border-bottom:1px solid #eef2f0;color:#8a9690;padding:10px 0}.staffswap-hub__checks li.is-ready{color:#0d2240;font-weight:600}.staffswap-hub__checks li.is-ready::before{content:"\\2713";color:#00a875;margin-right:8px}.staffswap-hub__checks li:not(.is-ready)::before{content:"\\25CB";margin-right:8px}.staffswap-hub__links{display:grid;gap:0;list-style:none;margin:0;padding:0}.staffswap-hub__links a{align-items:center;border-bottom:1px solid #e2e8f0;color:#155dfc;display:flex;font-weight:700;justify-content:space-between;padding:14px 4px;text-decoration:none}.staffswap-hub__links a:hover{color:#0f766e}' );
	wp_add_inline_script( 'jquery', '(function(){document.addEventListener("DOMContentLoaded",function(){var tabs=document.querySelectorAll(".staffswap-hub__tab");var panels=document.querySelectorAll(".staffswap-hub__panel");tabs.forEach(function(tab){tab.addEventListener("click",function(){var target=tab.getAttribute("data-staffswap-tab");tabs.forEach(function(t){t.classList.toggle("is-active",t===tab);});panels.forEach(function(p){p.classList.toggle("is-active",p.getAttribute("data-staffswap-panel")===target);});if(window.history&&window.history.replaceState){var url=new URL(window.location.href);url.searchParams.set("tab",target);window.history.replaceState({},"",url);}});});});})();' );
}
add_action( 'admin_enqueue_scripts', 'staffswap_theme_options_hub_assets' );