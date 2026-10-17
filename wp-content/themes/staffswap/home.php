<?php get_header(); if ( staffswap_builder_location( 'archive' ) ) { get_footer(); return; } ?>
<section class="page-hero page-hero--compact">
    <p class="eyebrow"><?php echo esc_html__( 'FROM THE NETWORK', 'staffswap' ); ?></p>
    <h1><?php echo esc_html__( 'Updates & Opportunities', 'staffswap' ); ?></h1>
    <p><?php echo esc_html__( 'Discover the latest career opportunities, internships, transfer updates and professional development news across Zambia.', 'staffswap' ); ?></p>
</section>
<section class="article-grid"><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?><article class="article-card"><p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p><a class="text-link" href="<?php the_permalink(); ?>"><?php echo esc_html__( 'Read article', 'staffswap' ); ?> &rarr;</a></article><?php endwhile; else : ?><div class="panel"><h2><?php echo esc_html__( 'New updates are on the way.', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Check back soon for fresh opportunities and professional updates.', 'staffswap' ); ?></p></div><?php endif; ?></section><?php get_footer(); ?>
