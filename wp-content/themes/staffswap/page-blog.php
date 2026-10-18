<?php
get_header();
if ( staffswap_has_builder_content() ) : while ( have_posts() ) : the_post(); the_content(); endwhile; get_footer(); return; endif;
$category_value = $_GET['opportunity_category'] ?? '';
$search_value = $_GET['opportunity_search'] ?? '';
$category_slug = is_scalar( $category_value ) ? sanitize_title( wp_unslash( $category_value ) ) : '';
$search_term = is_scalar( $search_value ) ? sanitize_text_field( wp_unslash( $search_value ) ) : '';
$categories = array_filter( array_map( 'get_category_by_slug', array( 'job-opportunities', 'internships-attachments', 'transfer-placement-updates' ) ) );
if ( ! in_array( $category_slug, array_map( function ( $category ) { return $category->slug; }, $categories ), true ) ) { $category_slug = ''; }
$selected_category = $category_slug ? get_category_by_slug( $category_slug ) : false;
$query_args = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 8, 'paged' => max( 1, get_query_var( 'paged', 1 ) ) );
if ( $category_slug ) { $query_args['category_name'] = $category_slug; }
if ( $search_term ) { $query_args['s'] = $search_term; }
$updates = new WP_Query( $query_args );
?>
<section class="opportunities-page">
	<header class="opportunities-hero">
		<div class="opportunities-hero__copy">
			<p class="eyebrow"><?php echo esc_html__( 'STAY INFORMED, GROW YOUR CAREER', 'staffswap' ); ?></p>
			<h1><?php echo esc_html__( 'Updates & Opportunities', 'staffswap' ); ?></h1>
			<p><?php echo esc_html__( 'Discover career opportunities, internship programmes, transfer updates and professional development news across Zambia.', 'staffswap' ); ?></p>
			<form class="opportunities-search" method="get">
				<label class="screen-reader-text" for="opportunity-search"><?php echo esc_html__( 'Search opportunities', 'staffswap' ); ?></label>
				<input id="opportunity-search" type="search" name="opportunity_search" value="<?php echo esc_attr( $search_term ); ?>" placeholder="<?php echo esc_attr__( 'Search opportunities, professions or locations', 'staffswap' ); ?>">
				<button class="button button--primary" type="submit"><?php echo esc_html__( 'Search', 'staffswap' ); ?></button>
			</form>
		</div>
		<div class="opportunities-hero__image" aria-hidden="true"><span class="dashicons dashicons-welcome-learn-more"></span></div>
	</header>
	<nav class="opportunity-categories" aria-label="<?php echo esc_attr__( 'Opportunity categories', 'staffswap' ); ?>">
		<a class="<?php echo $category_slug ? '' : 'is-active'; ?>" href="<?php echo esc_url( remove_query_arg( 'opportunity_category' ) ); ?>"><?php echo esc_html__( 'All updates', 'staffswap' ); ?></a>
		<?php foreach ( $categories as $category ) : ?><a class="<?php echo $category_slug === $category->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'opportunity_category', $category->slug ) ); ?>"><?php echo esc_html( $category->name ); ?></a><?php endforeach; ?>
	</nav>
	<div class="opportunities-layout">
		<aside class="opportunities-filter"><h2><?php echo esc_html__( 'Browse categories', 'staffswap' ); ?></h2><?php foreach ( $categories as $category ) : ?><a href="<?php echo esc_url( add_query_arg( 'opportunity_category', $category->slug ) ); ?>"><?php echo esc_html( $category->name ); ?></a><?php endforeach; ?></aside>
		<div class="opportunities-results">
			<div class="page-heading"><div><p class="eyebrow"><?php echo esc_html__( 'LATEST OPPORTUNITIES', 'staffswap' ); ?></p><h2><?php echo esc_html( $selected_category ? $selected_category->name : __( 'Latest Opportunities', 'staffswap' ) ); ?></h2><p class="muted"><?php echo esc_html( sprintf( __( 'Showing %d updates', 'staffswap' ), (int) $updates->found_posts ) ); ?></p></div></div>
			<?php if ( $updates->have_posts() ) : while ( $updates->have_posts() ) : $updates->the_post(); $closing_date = get_post_meta( get_the_ID(), '_staffswap_opportunity_closing_date', true ); $is_closed = $closing_date && $closing_date < current_time( 'Y-m-d' ); $post_categories = get_the_category(); $post_category_names = wp_list_pluck( $post_categories, 'name' ); ?>
				<article class="opportunity-card<?php echo $is_closed ? ' is-closed' : ''; ?>">
					<div class="opportunity-card__body"><p class="eyebrow"><?php echo esc_html( $post_category_names ? implode( ', ', $post_category_names ) : __( 'Professional update', 'staffswap' ) ); ?><?php if ( $is_closed ) : ?> <span class="opportunity-card__status"><?php echo esc_html__( 'Closed', 'staffswap' ); ?></span><?php endif; ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p><div class="opportunity-card__meta"><span><?php echo esc_html( get_the_date() ); ?></span><?php if ( $organization = get_post_meta( get_the_ID(), '_staffswap_opportunity_organization', true ) ) : ?><span><?php echo esc_html( $organization ); ?></span><?php endif; ?><?php if ( $location = get_post_meta( get_the_ID(), '_staffswap_opportunity_location', true ) ) : ?><span><?php echo esc_html( $location ); ?></span><?php endif; ?><?php if ( $closing_date ) : ?><span><?php echo esc_html( sprintf( __( 'Closes %s', 'staffswap' ), $closing_date ) ); ?></span><?php endif; ?></div></div>
					<?php $source_url = get_post_meta( get_the_ID(), '_staffswap_opportunity_source_url', true ); if ( $source_url ) : ?><a class="button button--outline" href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Original source', 'staffswap' ); ?></a><?php endif; ?>
				</article>
			<?php endwhile; wp_reset_postdata(); else : ?><div class="panel"><h2><?php echo esc_html__( 'No opportunities found', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Try a different search or category. New updates will appear here when published.', 'staffswap' ); ?></p></div><?php endif; ?>
			<?php $pagination = paginate_links( array( 'total' => max( 1, (int) $updates->max_num_pages ), 'current' => max( 1, (int) get_query_var( 'paged', 1 ) ) ) ); if ( $pagination ) : ?><nav class="listing-pagination" aria-label="<?php echo esc_attr__( 'Opportunity pages', 'staffswap' ); ?>"><?php echo wp_kses_post( $pagination ); ?></nav><?php endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
