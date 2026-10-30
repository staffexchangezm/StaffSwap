<?php
get_header();
if ( staffswap_has_builder_content() ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
	get_footer();
	return;
endif;
while ( have_posts() ) : the_post();
?>
<section class="page-hero page-hero--compact"><p class="eyebrow">STAFFEXCHANGEHUB</p><h1><?php the_title(); ?></h1></section><article class="content-form page-content-card"><div><?php the_content(); ?></div></article>
<?php endwhile; get_footer(); ?>