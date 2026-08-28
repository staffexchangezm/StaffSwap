<?php get_header(); if ( staffswap_builder_location( 'single' ) ) { get_footer(); return; } while ( have_posts() ) : the_post(); ?>
<article class="single-article"><p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p><h1><?php the_title(); ?></h1><div class="single-article__body"><?php the_content(); ?></div><p><a class="button button--outline" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">&larr; <?php echo esc_html__( 'Back to Blog', 'staffswap' ); ?></a></p></article>
<?php endwhile; get_footer(); ?>
