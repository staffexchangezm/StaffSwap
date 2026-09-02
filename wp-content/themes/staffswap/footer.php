</main>
<?php if ( ! staffswap_builder_location( 'footer' ) ) : ?>
<footer class="site-footer">
	<div class="site-footer__inner">
		<div><div class="footer-brand">StaffExchangeHub</div><p><?php echo esc_html__( 'The professional exchange marketplace helping individuals and institutions find workplace swaps across Zambia.', 'staffswap' ); ?></p></div>
		<div><h3><?php echo esc_html__( 'Platform', 'staffswap' ); ?></h3><p><a href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'Browse Swaps', 'staffswap' ); ?></a></p><p><a href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Create Listing', 'staffswap' ); ?></a></p></div>
		<div><h3><?php echo esc_html__( 'Resources', 'staffswap' ); ?></h3><p><a href="<?php echo esc_url( home_url( '/resources/' ) ); ?>"><?php echo esc_html__( 'Career Advice', 'staffswap' ); ?></a></p><p><a href="<?php echo esc_url( home_url( '/help/' ) ); ?>"><?php echo esc_html__( 'Help Center', 'staffswap' ); ?></a></p></div>
		<div><h3><?php echo esc_html__( 'Legal', 'staffswap' ); ?></h3><p><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php echo esc_html__( 'Privacy Policy', 'staffswap' ); ?></a></p><p><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php echo esc_html__( 'Terms of Service', 'staffswap' ); ?></a></p></div>
		<div class="footer-bottom">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html__( 'StaffExchangeHub. All rights reserved.', 'staffswap' ); ?></div>
	</div>
</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
