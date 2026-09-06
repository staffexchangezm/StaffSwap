<?php
get_header();
if ( staffswap_builder_location( 'single' ) ) { get_footer(); return; }
while ( have_posts() ) : the_post(); ?>
<article class="content-form"><p class="eyebrow"><?php echo esc_html__( 'SWAP PROFILE', 'staffswap' ); ?></p><h1><?php the_title(); ?></h1><?php echo do_shortcode( '[staffswap_listing_review_status]' ); ?><div class="panel" style="margin-top:24px"><?php echo function_exists( 'staffswap_listing_card' ) ? staffswap_listing_card( get_the_ID() ) : ''; ?><?php if ( get_the_content() ) : ?><div style="margin-top:20px"><?php the_content(); ?></div><?php endif; ?></div><?php echo do_shortcode( '[staffswap_contact]' ); ?><?php echo do_shortcode( '[staffswap_offer_form]' ); ?><?php
	$staffswap_profession = get_post_meta( get_the_ID(), '_staffswap_profession', true );
	if ( $staffswap_profession && function_exists( 'staffswap_listing_card' ) ) {
		$staffswap_similar = new WP_Query( array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'post__not_in' => array( get_the_ID() ), 'posts_per_page' => 3, 'meta_key' => '_staffswap_profession', 'meta_value' => $staffswap_profession ) );
		if ( $staffswap_similar->have_posts() ) : ?><section style="margin-top:32px"><div class="page-heading"><div><p class="eyebrow"><?php echo esc_html__( 'MORE LIKE THIS', 'staffswap' ); ?></p><h2><?php echo esc_html__( 'Similar swap listings', 'staffswap' ); ?></h2></div></div><div class="listing-list"><?php while ( $staffswap_similar->have_posts() ) : $staffswap_similar->the_post(); echo staffswap_listing_card( get_the_ID() ); endwhile; wp_reset_postdata(); ?></div></section><?php endif;
	}
?><p style="margin-top:24px"><a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>">&larr; <?php echo esc_html__( 'Back to listings', 'staffswap' ); ?></a></p></article>
<?php endwhile; get_footer(); ?>
