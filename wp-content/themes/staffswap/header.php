<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$staffswap_elementor_header = staffswap_builder_location( 'header' );
if ( ! $staffswap_elementor_header ) :
	$current_user = wp_get_current_user();
	$staffswap_menu = wp_nav_menu( array( 'theme_location' => 'primary', 'fallback_cb' => false, 'container' => false, 'echo' => false ) );
	?>
	<header class="site-header site-header--brand">
		<div class="site-header__inner">
			<a class="brand brand--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">StaffExchangeHub</a>
			<button class="menu-toggle" type="button" data-mobile-menu aria-label="<?php echo esc_attr__( 'Toggle menu', 'staffswap' ); ?>"><?php echo esc_html__( 'Menu', 'staffswap' ); ?></button>
			<nav class="primary-nav" aria-label="<?php echo esc_attr__( 'Primary navigation', 'staffswap' ); ?>"><?php if ( ! empty( trim( $staffswap_menu ) ) ) { echo $staffswap_menu; } else { staffswap_fallback_menu(); } ?></nav>
			<div class="header-actions">
				<a class="header-icon" href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>" aria-label="<?php echo esc_attr__( 'Notifications', 'staffswap' ); ?>"><span aria-hidden="true">&#128276;</span><b>3</b></a>
				<a class="header-icon" href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>" aria-label="<?php echo esc_attr__( 'Messages', 'staffswap' ); ?>"><span aria-hidden="true">&#128172;</span></a>
				<?php if ( is_user_logged_in() ) : ?><a class="profile-cluster" href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>"><span class="profile-avatar"><?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?></span><span><strong><?php echo esc_html( $current_user->display_name ); ?></strong><small><?php echo esc_html__( 'View my profile', 'staffswap' ); ?></small></span></a><?php else : ?><a class="profile-cluster" href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>"><span class="profile-avatar">&#128100;</span><span><strong><?php echo esc_html__( 'Welcome', 'staffswap' ); ?></strong><small><?php echo esc_html__( 'Sign in to your profile', 'staffswap' ); ?></small></span></a><?php endif; ?>
			</div>
		</div>
	</header>
<?php endif; ?>
<main id="content" class="site-main">
