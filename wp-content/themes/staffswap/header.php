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
	$staffswap_unread_messages = is_user_logged_in() && function_exists( 'staffswap_unread_message_count' ) ? staffswap_unread_message_count() : 0;
	$staffswap_pending_offers = is_user_logged_in() && function_exists( 'staffswap_pending_offer_count' ) ? staffswap_pending_offer_count() : 0;
	$staffswap_menu = wp_nav_menu( array( 'theme_location' => 'primary', 'fallback_cb' => false, 'container' => false, 'echo' => false ) );
	?>
	<header class="site-header site-header--brand">
		<div class="site-header__inner">
			<a class="brand brand--light" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( staffswap_home_setting( 'site_name', 'StaffExchangeHub' ) ); ?>"><?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?><?php the_custom_logo(); ?><?php else : ?><?php echo esc_html( staffswap_home_setting( 'site_name', 'StaffExchangeHub' ) ); ?><?php endif; ?></a>
			<button class="menu-toggle" type="button" data-mobile-menu aria-label="<?php echo esc_attr__( 'Toggle menu', 'staffswap' ); ?>"><?php echo esc_html__( 'Menu', 'staffswap' ); ?></button>
			<nav class="primary-nav" aria-label="<?php echo esc_attr__( 'Primary navigation', 'staffswap' ); ?>"><?php if ( ! empty( trim( $staffswap_menu ) ) ) { echo $staffswap_menu; } else { staffswap_fallback_menu(); } ?></nav>
			<div class="header-actions">
				<a class="header-icon" href="<?php echo esc_url( home_url( '/offers/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Offers, %d pending', 'staffswap' ), $staffswap_pending_offers ) ); ?>"><span class="dashicons dashicons-bell" aria-hidden="true"></span><?php if ( $staffswap_pending_offers ) : ?><b><?php echo esc_html( $staffswap_pending_offers ); ?></b><?php endif; ?></a>
				<a class="header-icon" href="<?php echo esc_url( home_url( '/messages/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Messages, %d unread', 'staffswap' ), $staffswap_unread_messages ) ); ?>"><span class="dashicons dashicons-email-alt" aria-hidden="true"></span><?php if ( $staffswap_unread_messages ) : ?><b><?php echo esc_html( $staffswap_unread_messages ); ?></b><?php endif; ?></a>
				<div class="profile-menu" data-profile-menu>
					<button class="profile-cluster" type="button" data-profile-trigger aria-expanded="false" aria-haspopup="true">
						<span class="profile-avatar"><?php if ( is_user_logged_in() ) : ?><?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?><?php else : ?><span class="dashicons dashicons-admin-users" aria-hidden="true"></span><?php endif; ?></span>
						<span><strong><?php echo is_user_logged_in() ? esc_html( $current_user->display_name ) : esc_html__( 'Welcome', 'staffswap' ); ?></strong><small><?php echo is_user_logged_in() ? esc_html__( 'Open account menu', 'staffswap' ) : esc_html__( 'Sign in or register', 'staffswap' ); ?></small></span><span class="dashicons dashicons-arrow-down-alt2 profile-menu__chevron" aria-hidden="true"></span>
					</button>
					<div class="profile-menu__dropdown" data-profile-dropdown hidden>
						<?php if ( is_user_logged_in() ) : ?>
							<p class="profile-menu__label"><?php echo esc_html__( 'Your workspace', 'staffswap' ); ?></p>
							<a href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>"><span class="dashicons dashicons-dashboard" aria-hidden="true"></span><?php echo esc_html__( 'Dashboard', 'staffswap' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php echo esc_html__( 'Create listing', 'staffswap' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/messages/' ) ); ?>"><span class="dashicons dashicons-email-alt" aria-hidden="true"></span><?php echo esc_html__( 'Messages', 'staffswap' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/offers/' ) ); ?>"><span class="dashicons dashicons-megaphone" aria-hidden="true"></span><?php echo esc_html__( 'Offers', 'staffswap' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/verification/' ) ); ?>"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php echo esc_html__( 'Verification', 'staffswap' ); ?></a>
							<a class="profile-menu__logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><span class="dashicons dashicons-external" aria-hidden="true"></span><?php echo esc_html__( 'Log out', 'staffswap' ); ?></a>
						<?php else : ?>
							<p class="profile-menu__label"><?php echo esc_html__( 'Join the exchange network', 'staffswap' ); ?></p>
							<a href="<?php echo esc_url( home_url( '/sign-in/' ) ); ?>"><span class="dashicons dashicons-unlock" aria-hidden="true"></span><?php echo esc_html__( 'Sign in', 'staffswap' ); ?></a>
							<a href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><span class="dashicons dashicons-id-alt" aria-hidden="true"></span><?php echo esc_html__( 'Create account', 'staffswap' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</header>
<?php endif; ?>
<main id="content" class="site-main">
