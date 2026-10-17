</main>
<?php if ( ! staffswap_builder_location( 'footer' ) ) : ?>
<footer class="site-footer">
	<div class="site-footer__inner">
		<div><div class="footer-brand"><?php echo esc_html( staffswap_brand_settings()['site_name'] ); ?></div><p><?php echo esc_html( staffswap_brand_settings()['footer_text'] ); ?></p></div>
		<?php foreach ( staffswap_footer_columns() as $column ) : ?>
		<div>
			<?php if ( '' !== $column['title'] ) : ?><h3><?php echo esc_html( $column['title'] ); ?></h3><?php endif; ?>
			<?php foreach ( $column['items'] as $item ) : ?><p><?php if ( $item['url'] ) : ?><a href="<?php echo esc_url( $item['url'], array( 'http', 'https', 'mailto', 'tel' ) ); ?>"><?php echo esc_html( $item['text'] ); ?></a><?php else : echo esc_html( $item['text'] ); endif; ?></p><?php endforeach; ?>
		</div>
		<?php endforeach; ?>
		<div class="footer-bottom">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html__( 'StaffExchangeHub. All rights reserved.', 'staffswap' ); ?></div>
	</div>
</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
