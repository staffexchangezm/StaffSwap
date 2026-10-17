<?php get_header(); if ( staffswap_builder_location( 'archive' ) ) { get_footer(); return; } ?>
<section class="opportunities-page">
    <div class="opportunities-hero">
        <div class="opportunities-hero__copy">
            <p class="eyebrow"><?php echo esc_html__( 'STAY INFORMED, GROW YOUR CAREER', 'staffswap' ); ?></p>
            <h1><?php echo esc_html__( 'Updates & Opportunities', 'staffswap' ); ?></h1>
            <p><?php echo esc_html__( 'Discover the latest job opportunities, internships, transfer updates and professional development news across Zambia.', 'staffswap' ); ?></p>
            <form class="opportunities-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                <input type="text" name="s" placeholder="Search opportunities (e.g. nursing, teacher, internship, Lusaka)">
                <button type="submit" class="button button--primary">Search</button>
            </form>
        </div>
        <div class="opportunities-hero__image" aria-hidden="true">
            <div class="opportunities-hero__person">
                <span class="opportunities-hero__avatar">S</span>
            </div>
        </div>
    </div>

    <div class="opportunity-categories">
        <article class="opportunity-category"><span class="category-icon">✦</span><h3>Job Vacancies</h3><p>Latest employment opportunities</p></article>
        <article class="opportunity-category"><span class="category-icon">A</span><h3>Internships &amp; Attachments</h3><p>Student and graduate programmes</p></article>
        <article class="opportunity-category"><span class="category-icon">⇄</span><h3>Transfer &amp; Placement Updates</h3><p>Institutional news and updates</p></article>
        <article class="opportunity-category"><span class="category-icon">◌</span><h3>Scholarships &amp; Training</h3><p>Courses and development opportunities</p></article>
        <article class="opportunity-category"><span class="category-icon">▣</span><h3>Events &amp; Deadlines</h3><p>Workshops, recruitment and events</p></article>
    </div>

    <div class="opportunities-layout">
        <aside class="opportunities-filter panel">
            <div class="filter-header">
                <h3>Filter Opportunities</h3>
                <a href="<?php echo esc_url( home_url( '/updates/' ) ); ?>">Reset</a>
            </div>
            <label>Category<select><option>All categories</option></select></label>
            <label>Profession<select><option>All professions</option></select></label>
            <label>Location<select><option>All provinces</option></select></label>
            <label>All districts<select><option>All districts</option></select></label>
            <label>Organisation Type<select><option>All types</option></select></label>
            <label>Closing Date<select><option>Any time</option></select></label>
            <button type="button" class="button button--primary">Apply filters</button>
            <div class="subscribe-box">
                <h4>Get Opportunity Alerts</h4>
                <p>Be the first to know about new job vacancies, internships and transfer updates.</p>
                <input type="email" value="" placeholder="Enter your email address">
                <button type="button" class="button button--primary">Subscribe</button>
                <small>No spam. Unsubscribe anytime.</small>
            </div>
        </aside>

        <div class="opportunities-listing">
            <div class="listing-toolbar">
                <h3>Latest Opportunities</h3>
                <div class="listing-toolbar__right">
                    <span>Showing 12 of 128 opportunities</span>
                    <button type="button" class="button button--outline button--small">Sort by: Newest first</button>
                    <button type="button" class="button button--outline button--small">List</button>
                </div>
            </div>
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <article class="opportunity-item">
                    <div class="opportunity-item__logo"><?php echo esc_html( strtoupper( substr( get_the_title(), 0, 2 ) ) ); ?></div>
                    <div class="opportunity-item__content">
                        <div class="opportunity-item__header">
                            <div>
                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                <p><?php echo esc_html( get_the_date() ); ?></p>
                            </div>
                            <span class="opportunity-item__status">Active</span>
                        </div>
                        <div class="opportunity-item__meta">
                            <span>Job Vacancy</span>
                            <span>Teacher</span>
                            <span>Lusaka</span>
                            <span>Full Time</span>
                        </div>
                        <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
                    </div>
                    <a class="button button--outline" href="<?php the_permalink(); ?>">View Details</a>
                </article>
            <?php endwhile; else : ?>
                <article class="panel"><h3><?php echo esc_html__( 'New updates are on the way.', 'staffswap' ); ?></h3><p class="muted"><?php echo esc_html__( 'Check back soon for fresh opportunities and professional updates.', 'staffswap' ); ?></p></article>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php get_footer(); ?>
