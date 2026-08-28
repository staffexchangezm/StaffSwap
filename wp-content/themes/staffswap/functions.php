<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_setup() { add_theme_support( 'title-tag' ); add_theme_support( 'post-thumbnails' ); register_nav_menus( array( 'primary' => __( 'Primary Menu', 'staffswap' ) ) ); }
add_action( 'after_setup_theme', 'staffswap_setup' );
function staffswap_assets() { wp_enqueue_style( 'staffswap-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap', array(), null ); wp_enqueue_style( 'staffswap-style', get_stylesheet_uri(), array( 'staffswap-fonts' ), '1.0.0' ); wp_enqueue_script( 'staffswap-main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.0', true ); }
add_action( 'wp_enqueue_scripts', 'staffswap_assets' );
function staffswap_fallback_menu() { echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li><li><a href="' . esc_url( home_url( '/swaps/' ) ) . '">Find Swaps</a></li><li><a href="' . esc_url( home_url( '/pricing/' ) ) . '">Pricing</a></li></ul>'; }

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