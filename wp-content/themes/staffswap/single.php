<?php
get_header();
if ( staffswap_builder_location( 'single' ) ) {
	get_footer();
	return;
}
while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	$categories = get_the_category( $post_id );
	$organization = get_post_meta( $post_id, '_staffswap_opportunity_organization', true );
	$location = get_post_meta( $post_id, '_staffswap_opportunity_location', true );
	$closing_date = get_post_meta( $post_id, '_staffswap_opportunity_closing_date', true );
	$source_url = get_post_meta( $post_id, '_staffswap_opportunity_source_url', true );
	$is_closed = $closing_date && $closing_date < current_time( 'Y-m-d' );
	?>
	<article <?php post_class( 'single-article' ); ?>>
		<header class="single-article__header">
			<a class="single-article__back" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><span aria-hidden="true">&larr;</span> <?php echo esc_html__( 'Updates & Opportunities', 'staffswap' ); ?></a>
			<div class="single-article__eyebrow">
				<?php if ( $categories ) : ?><span><?php echo esc_html( $categories[0]->name ); ?></span><?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<?php if ( $closing_date ) : ?><span class="single-article__status<?php echo $is_closed ? ' is-closed' : ''; ?>"><?php echo $is_closed ? esc_html__( 'Closed', 'staffswap' ) : esc_html__( 'Open', 'staffswap' ); ?></span><?php endif; ?>
			</div>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="single-article__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<?php if ( $organization || $location || $closing_date ) : ?>
				<div class="single-article__facts">
					<?php if ( $organization ) : ?><span><span class="dashicons dashicons-building" aria-hidden="true"></span><?php echo esc_html( $organization ); ?></span><?php endif; ?>
					<?php if ( $location ) : ?><span><span class="dashicons dashicons-location" aria-hidden="true"></span><?php echo esc_html( $location ); ?></span><?php endif; ?>
					<?php if ( $closing_date ) : ?><span><span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span><?php echo esc_html( sprintf( __( 'Closes %s', 'staffswap' ), $closing_date ) ); ?></span><?php endif; ?>
				</div>
			<?php endif; ?>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="single-article__featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'decoding' => 'async' ) ); ?></figure>
		<?php endif; ?>
		<div class="single-article__body"><?php the_content(); ?></div>
		<?php if ( $source_url ) : ?><p class="single-article__source"><a class="button button--primary" href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Visit original opportunity', 'staffswap' ); ?><span class="dashicons dashicons-external" aria-hidden="true"></span></a></p><?php endif; ?>
		<div class="single-article__share"><span class="muted"><?php echo esc_html__( 'Share this update:', 'staffswap' ); ?></span><a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( get_the_title() . ' ' . get_permalink() ) ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a><a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener noreferrer">Facebook</a><a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . rawurlencode( get_permalink() ) . '&text=' . rawurlencode( get_the_title() ) ); ?>" target="_blank" rel="noopener noreferrer">X</a><a href="<?php echo esc_url( 'mailto:?subject=' . rawurlencode( get_the_title() ) . '&body=' . rawurlencode( get_permalink() ) ); ?>">Email</a></div>
		<?php
		$related = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'post__not_in' => array( $post_id ), 'posts_per_page' => 3, 'category__in' => wp_get_post_categories( $post_id ) ) );
		if ( $related->have_posts() ) :
			?>
			<section class="single-article__related">
				<div class="page-heading"><div><p class="eyebrow"><?php echo esc_html__( 'KEEP READING', 'staffswap' ); ?></p><h2><?php echo esc_html__( 'Related articles', 'staffswap' ); ?></h2></div></div>
				<div class="article-grid">
					<?php while ( $related->have_posts() ) : $related->the_post(); ?>
						<article class="article-card">
							<?php if ( has_post_thumbnail() ) : ?><a class="article-card__image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) ); ?></a><?php endif; ?>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
						</article>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
			</section>
		<?php endif; ?>
		<p class="single-article__back-bottom"><a class="button button--outline" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">&larr; <?php echo esc_html__( 'Back to Updates & Opportunities', 'staffswap' ); ?></a></p>
	</article>
	<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
<?php endwhile; ?>
<?php get_footer(); ?>
