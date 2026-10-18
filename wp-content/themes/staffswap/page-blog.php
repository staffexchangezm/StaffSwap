<?php
get_header();
if ( staffswap_has_builder_content() ) : while ( have_posts() ) : the_post(); the_content(); endwhile; get_footer(); return; endif;
$category_value = $_GET['opportunity_category'] ?? '';
$search_value = $_GET['opportunity_search'] ?? '';
$profession_value = $_GET['opportunity_profession'] ?? '';
$location_value = $_GET['opportunity_location'] ?? '';
$organization_value = $_GET['opportunity_organization_type'] ?? '';
$closing_value = $_GET['opportunity_closing'] ?? '';
$sort_value = $_GET['opportunity_sort'] ?? 'newest';
$view_value = $_GET['opportunity_view'] ?? 'list';
$category_slug = is_scalar( $category_value ) ? sanitize_title( wp_unslash( $category_value ) ) : '';
$search_term = is_scalar( $search_value ) ? sanitize_text_field( wp_unslash( $search_value ) ) : '';
$profession = is_scalar( $profession_value ) ? sanitize_text_field( wp_unslash( $profession_value ) ) : '';
$location = is_scalar( $location_value ) ? sanitize_text_field( wp_unslash( $location_value ) ) : '';
$organization_type = is_scalar( $organization_value ) ? sanitize_key( wp_unslash( $organization_value ) ) : '';
$closing = is_scalar( $closing_value ) ? sanitize_key( wp_unslash( $closing_value ) ) : '';
$sort = is_scalar( $sort_value ) ? sanitize_key( wp_unslash( $sort_value ) ) : 'newest';
$view = is_scalar( $view_value ) ? sanitize_key( wp_unslash( $view_value ) ) : 'list';
$sort = in_array( $sort, array( 'newest', 'closing' ), true ) ? $sort : 'newest';
$view = in_array( $view, array( 'grid', 'list' ), true ) ? $view : 'list';
$organization_types = array( 'government' => __( 'Government', 'staffswap' ), 'ngo' => __( 'NGO', 'staffswap' ), 'private' => __( 'Private sector', 'staffswap' ), 'education' => __( 'Education', 'staffswap' ), 'healthcare' => __( 'Healthcare', 'staffswap' ) );
$categories = array_filter( array_map( 'get_category_by_slug', array( 'job-opportunities', 'internships-attachments', 'transfer-placement-updates', 'scholarships-training', 'events-deadlines', 'professional-news' ) ) );
if ( ! in_array( $category_slug, array_map( function ( $category ) { return $category->slug; }, $categories ), true ) ) { $category_slug = ''; }
$selected_category = $category_slug ? get_category_by_slug( $category_slug ) : false;
$location_options = array();
foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 250, 'fields' => 'ids', 'meta_key' => '_staffswap_opportunity_location' ) ) as $opportunity_id ) {
	$option = get_post_meta( $opportunity_id, '_staffswap_opportunity_location', true );
	if ( $option ) { $location_options[ $option ] = $option; }
}
$query_args = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 12, 'paged' => max( 1, get_query_var( 'paged', 1 ) ) );
if ( $category_slug ) { $query_args['category_name'] = $category_slug; }
if ( $search_term ) { $query_args['s'] = $search_term; }
if ( $profession ) { $query_args['meta_query'][] = array( 'key' => '_staffswap_opportunity_profession', 'value' => $profession, 'compare' => 'LIKE' ); }
if ( $location ) { $query_args['meta_query'][] = array( 'key' => '_staffswap_opportunity_location', 'value' => $location, 'compare' => '=' ); }
if ( $organization_type && isset( $organization_types[ $organization_type ] ) ) { $query_args['meta_query'][] = array( 'key' => '_staffswap_opportunity_organization_type', 'value' => $organization_type ); }
if ( 'active' === $closing ) {
	$query_args['meta_query'][] = array( 'key' => '_staffswap_opportunity_closing_date', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' );
} elseif ( 'closed' === $closing ) {
	$query_args['meta_query'][] = array( 'key' => '_staffswap_opportunity_closing_date', 'value' => current_time( 'Y-m-d' ), 'compare' => '<', 'type' => 'DATE' );
}
if ( 'closing' === $sort ) {
	$query_args['meta_key'] = '_staffswap_opportunity_closing_date';
	$query_args['orderby'] = 'meta_value';
	$query_args['order'] = 'ASC';
	$query_args['meta_query'][] = array( 'key' => '_staffswap_opportunity_closing_date', 'compare' => 'EXISTS' );
}
$updates = new WP_Query( $query_args );
$page_image = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
?>
<section class="opportunities-page">
	<header class="opportunities-hero"<?php if ( $page_image ) : ?> style="--opportunities-image:url('<?php echo esc_url( $page_image ); ?>')"<?php endif; ?>>
		<div class="opportunities-hero__copy">
			<p class="eyebrow"><?php echo esc_html__( 'STAY INFORMED, GROW YOUR CAREER', 'staffswap' ); ?></p>
			<h1><?php echo esc_html__( 'Updates & Opportunities', 'staffswap' ); ?></h1>
			<p><?php echo esc_html__( 'Discover career opportunities, internship programmes, transfer updates and professional development news across Zambia.', 'staffswap' ); ?></p>
			<form class="opportunities-search" method="get">
				<label class="screen-reader-text" for="opportunity-search"><?php echo esc_html__( 'Search opportunities', 'staffswap' ); ?></label>
				<input id="opportunity-search" type="search" name="opportunity_search" value="<?php echo esc_attr( $search_term ); ?>" placeholder="<?php echo esc_attr__( 'Search opportunities (e.g. nurse, teacher, internship, Lusaka)', 'staffswap' ); ?>">
				<button class="button button--primary" type="submit"><?php echo esc_html__( 'Search', 'staffswap' ); ?></button>
			</form>
		</div>
		<div class="opportunities-hero__image" aria-hidden="true"><?php if ( ! $page_image ) : ?><span class="opportunities-hero__person opportunities-hero__person--one"><span class="dashicons dashicons-id-alt"></span></span><span class="opportunities-hero__person opportunities-hero__person--two"><span class="dashicons dashicons-businessperson"></span></span><span class="opportunities-hero__person opportunities-hero__person--three"><span class="dashicons dashicons-welcome-learn-more"></span></span><?php endif; ?></div>
	</header>
	<nav class="opportunity-categories" aria-label="<?php echo esc_attr__( 'Opportunity categories', 'staffswap' ); ?>">
		<?php $category_icons = array( 'dashicons-portfolio', 'dashicons-welcome-learn-more', 'dashicons-randomize', 'dashicons-awards', 'dashicons-calendar-alt', 'dashicons-media-document' ); ?>
		<?php foreach ( array_values( $categories ) as $index => $category ) : ?><a class="opportunity-category<?php echo $category_slug === $category->slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'opportunity_category', $category->slug ) ); ?>"><span class="category-icon"><span class="dashicons <?php echo esc_attr( $category_icons[ $index % count( $category_icons ) ] ); ?>"></span></span><h3><?php echo esc_html( $category->name ); ?></h3><p><?php echo esc_html( sprintf( _n( '%s opportunity', '%s opportunities', (int) $category->count, 'staffswap' ), number_format_i18n( $category->count ) ) ); ?></p><span class="dashicons dashicons-arrow-right-alt2 opportunity-categories__arrow" aria-hidden="true"></span></a><?php endforeach; ?>
	</nav>
	<div class="opportunities-layout">
		<aside class="opportunities-filter">
			<div class="opportunities-filter__heading"><h2><?php echo esc_html__( 'Filter Opportunities', 'staffswap' ); ?></h2><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html__( 'Reset all', 'staffswap' ); ?></a></div>
			<details class="opportunities-filter__details" open>
				<summary><?php echo esc_html__( 'Refine results', 'staffswap' ); ?></summary>
				<form method="get">
				<div class="field"><label for="opportunity-category"><?php echo esc_html__( 'Category', 'staffswap' ); ?></label><select id="opportunity-category" name="opportunity_category"><option value=""><?php echo esc_html__( 'All categories', 'staffswap' ); ?></option><?php foreach ( $categories as $category ) : ?><option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $category_slug, $category->slug ); ?>><?php echo esc_html( $category->name ); ?></option><?php endforeach; ?></select></div>
				<div class="field"><label for="opportunity-profession"><?php echo esc_html__( 'Profession', 'staffswap' ); ?></label><input id="opportunity-profession" name="opportunity_profession" value="<?php echo esc_attr( $profession ); ?>" placeholder="<?php echo esc_attr__( 'All professions', 'staffswap' ); ?>"></div>
				<div class="field"><label for="opportunity-location"><?php echo esc_html__( 'Location', 'staffswap' ); ?></label><select id="opportunity-location" name="opportunity_location"><option value=""><?php echo esc_html__( 'All provinces', 'staffswap' ); ?></option><?php foreach ( $location_options as $option ) : ?><option value="<?php echo esc_attr( $option ); ?>" <?php selected( $location, $option ); ?>><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select></div>
				<div class="field"><label for="opportunity-organization"><?php echo esc_html__( 'Organisation Type', 'staffswap' ); ?></label><select id="opportunity-organization" name="opportunity_organization_type"><option value=""><?php echo esc_html__( 'All types', 'staffswap' ); ?></option><?php foreach ( $organization_types as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $organization_type, $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></div>
				<div class="field"><label for="opportunity-closing"><?php echo esc_html__( 'Closing Date', 'staffswap' ); ?></label><select id="opportunity-closing" name="opportunity_closing"><option value=""><?php echo esc_html__( 'Any time', 'staffswap' ); ?></option><option value="active" <?php selected( $closing, 'active' ); ?>><?php echo esc_html__( 'Still open', 'staffswap' ); ?></option><option value="closed" <?php selected( $closing, 'closed' ); ?>><?php echo esc_html__( 'Closed', 'staffswap' ); ?></option></select></div>
				<input type="hidden" name="opportunity_search" value="<?php echo esc_attr( $search_term ); ?>">
				<button class="button button--primary" type="submit"><span class="dashicons dashicons-filter"></span><?php echo esc_html__( 'Apply filters', 'staffswap' ); ?></button>
				</form>
			</details>
			<div class="opportunities-alert"><h2><?php echo esc_html__( 'Get Opportunity Alerts', 'staffswap' ); ?></h2><p><?php echo esc_html__( 'Be the first to know about new vacancies, internships and updates.', 'staffswap' ); ?></p><?php echo do_shortcode( '[staffswap_alert_signup]' ); ?></div>
		</aside>
		<div class="opportunities-results">
			<div class="opportunities-results__heading"><div><p class="eyebrow"><?php echo esc_html__( 'LATEST OPPORTUNITIES', 'staffswap' ); ?></p><h2><?php echo esc_html( $selected_category ? $selected_category->name : __( 'Latest Opportunities', 'staffswap' ) ); ?></h2><p class="muted"><?php echo esc_html( sprintf( __( 'Showing %1$d of %2$d opportunities', 'staffswap' ), min( 12, (int) $updates->found_posts ), (int) $updates->found_posts ) ); ?></p></div><form class="opportunities-sort" method="get"><?php foreach ( array( 'opportunity_search' => $search_term, 'opportunity_category' => $category_slug, 'opportunity_profession' => $profession, 'opportunity_location' => $location, 'opportunity_organization_type' => $organization_type, 'opportunity_closing' => $closing ) as $key => $value ) : if ( $value ) : ?><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"><?php endif; endforeach; ?><label for="opportunity-sort"><?php echo esc_html__( 'Sort by:', 'staffswap' ); ?></label><select id="opportunity-sort" name="opportunity_sort"><option value="newest" <?php selected( $sort, 'newest' ); ?>><?php echo esc_html__( 'Newest first', 'staffswap' ); ?></option><option value="closing" <?php selected( $sort, 'closing' ); ?>><?php echo esc_html__( 'Closing soon', 'staffswap' ); ?></option></select><input type="hidden" name="opportunity_view" value="<?php echo esc_attr( $view ); ?>"><button class="opportunity-view-button<?php echo 'grid' === $view ? ' is-active' : ''; ?>" name="opportunity_view" value="grid" aria-label="<?php echo esc_attr__( 'Grid view', 'staffswap' ); ?>"><span class="dashicons dashicons-grid-view"></span></button><button class="opportunity-view-button<?php echo 'list' === $view ? ' is-active' : ''; ?>" name="opportunity_view" value="list" aria-label="<?php echo esc_attr__( 'List view', 'staffswap' ); ?>"><span class="dashicons dashicons-list-view"></span></button></form></div>
			<div class="opportunities-cards opportunities-cards--<?php echo esc_attr( $view ); ?>">
			<?php if ( $updates->have_posts() ) : while ( $updates->have_posts() ) : $updates->the_post(); $closing_date = get_post_meta( get_the_ID(), '_staffswap_opportunity_closing_date', true ); $is_closed = $closing_date && $closing_date < current_time( 'Y-m-d' ); $post_categories = get_the_category(); $post_category_names = wp_list_pluck( $post_categories, 'name' ); ?>
				<article class="opportunity-card<?php echo $is_closed ? ' is-closed' : ''; ?>">
					<?php if ( has_post_thumbnail() ) : ?><a class="opportunity-card__image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy' ) ); ?></a><?php else : ?><span class="opportunity-card__image opportunity-card__image--fallback" aria-hidden="true"><span class="dashicons dashicons-building"></span></span><?php endif; ?>
					<div class="opportunity-card__body"><p class="eyebrow"><?php echo esc_html( $post_category_names ? implode( ', ', $post_category_names ) : __( 'Professional update', 'staffswap' ) ); ?><?php if ( $is_closed ) : ?> <span class="opportunity-card__status"><?php echo esc_html__( 'Closed', 'staffswap' ); ?></span><?php else : ?> <span class="opportunity-card__status opportunity-card__status--open"><?php echo esc_html__( 'Open', 'staffswap' ); ?></span><?php endif; ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p><div class="opportunity-card__meta"><?php if ( $location = get_post_meta( get_the_ID(), '_staffswap_opportunity_location', true ) ) : ?><span class="dashicons dashicons-location"><?php echo esc_html( $location ); ?></span><?php endif; ?><?php if ( $organization = get_post_meta( get_the_ID(), '_staffswap_opportunity_organization', true ) ) : ?><span class="dashicons dashicons-building"><?php echo esc_html( $organization ); ?></span><?php endif; ?><?php if ( $closing_date ) : ?><span class="dashicons dashicons-calendar-alt"><?php echo esc_html( sprintf( __( 'Closes %s', 'staffswap' ), $closing_date ) ); ?></span><?php endif; ?></div><div class="opportunity-card__tags"><?php if ( $profession_label = get_post_meta( get_the_ID(), '_staffswap_opportunity_profession', true ) ) : ?><span><?php echo esc_html( $profession_label ); ?></span><?php endif; ?><?php if ( $post_categories ) : ?><span><?php echo esc_html( $post_categories[0]->name ); ?></span><?php endif; ?></div></div>
					<?php $source_url = get_post_meta( get_the_ID(), '_staffswap_opportunity_source_url', true ); ?><a class="button button--outline" href="<?php echo esc_url( $source_url ?: get_permalink() ); ?>"<?php echo $source_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html__( 'View Details', 'staffswap' ); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a>
				</article>
			<?php endwhile; wp_reset_postdata(); else : ?><div class="panel"><h2><?php echo esc_html__( 'No opportunities found', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Try a different search or category. New updates will appear here when published.', 'staffswap' ); ?></p></div><?php endif; ?>
			</div>
			<?php $pagination = paginate_links( array( 'total' => max( 1, (int) $updates->max_num_pages ), 'current' => max( 1, (int) get_query_var( 'paged', 1 ) ), 'add_args' => array_filter( array( 'opportunity_search' => $search_term, 'opportunity_category' => $category_slug, 'opportunity_profession' => $profession, 'opportunity_location' => $location, 'opportunity_organization_type' => $organization_type, 'opportunity_closing' => $closing, 'opportunity_sort' => $sort, 'opportunity_view' => $view ) ) ) ); if ( $pagination ) : ?><nav class="listing-pagination" aria-label="<?php echo esc_attr__( 'Opportunity pages', 'staffswap' ); ?>"><?php echo wp_kses_post( $pagination ); ?></nav><?php endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
