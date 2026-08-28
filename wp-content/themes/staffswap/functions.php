<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function staffswap_setup() { add_theme_support( 'title-tag' ); add_theme_support( 'post-thumbnails' ); register_nav_menus( array( 'primary' => __( 'Primary Menu', 'staffswap' ) ) ); }
add_action( 'after_setup_theme', 'staffswap_setup' );
function staffswap_assets() { wp_enqueue_style( 'staffswap-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap', array(), null ); wp_enqueue_style( 'staffswap-style', get_stylesheet_uri(), array( 'staffswap-fonts' ), '1.0.0' ); }
add_action( 'wp_enqueue_scripts', 'staffswap_assets' );
function staffswap_fallback_menu() { echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li><li><a href="' . esc_url( home_url( '/swaps/' ) ) . '">Find Swaps</a></li><li><a href="' . esc_url( home_url( '/pricing/' ) ) . '">Pricing</a></li></ul>'; }