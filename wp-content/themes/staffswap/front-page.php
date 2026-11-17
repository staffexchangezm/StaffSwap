<?php
get_header();
$elementor_content = get_post_meta( get_the_ID(), '_elementor_data', true );
$elementor_preview = isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() );
$hero_title = staffswap_home_setting( 'hero_title', 'Workplace mobility, made simple' );
$hero_text = staffswap_home_setting( 'hero_text', 'Connect with professionals across Zambia looking to exchange work locations. Search available opportunities, create your swap request and explore relocation possibilities.' );
$hero_image_id = absint( get_theme_mod( 'staffswap_hero_image', 0 ) );
$hero_image = $hero_image_id ? wp_get_attachment_image( $hero_image_id, 'large', false, array( 'class' => 'swap-hero-image', 'loading' => 'eager', 'decoding' => 'async' ) ) : '';
$how_it_works_image_id = absint( get_theme_mod( 'staffswap_how_it_works_image', 0 ) );
$how_it_works_image = $how_it_works_image_id ? wp_get_attachment_image( $how_it_works_image_id, 'medium_large', false, array( 'class' => 'homepage-section-image', 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
$opportunities_image_id = absint( get_theme_mod( 'staffswap_opportunities_image', 0 ) );
$opportunities_image = $opportunities_image_id ? wp_get_attachment_image( $opportunities_image_id, 'medium', false, array( 'class' => 'homepage-section-image', 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
$resources_image_id = absint( get_theme_mod( 'staffswap_resources_image', 0 ) );
$resources_image = $resources_image_id ? wp_get_attachment_image( $resources_image_id, 'medium_large', false, array( 'class' => 'resource-promo__image', 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
$profession_count = function_exists( 'staffswap_profession_options' ) ? count( staffswap_profession_options() ) : 10;
$benefits = array(
    array( 'Search by profession', 'Browse listings by your role, location and preferred move.' ),
    array( 'Review potential matches', 'Compare current and desired provinces before you connect.' ),
    array( 'Protect your privacy', 'Keep your details private while exploring exchanges.' ),
);
?>
<?php if ( ! empty( $elementor_content ) || $elementor_preview ) : ?><div id="content" class="staffswap-elementor-content"><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div><?php get_footer(); return; endif; ?>
<section class="landing-hero">
    <div class="landing-hero__frame">
        <div class="landing-hero__inner">
            <div class="landing-hero__copy">
                <p class="eyebrow">WORKPLACE MOBILITY, MADE SIMPLE</p>
                <h1>Swap Your Workplace. Change Your Life.</h1>
                <p><?php echo esc_html( $hero_text ); ?></p>
                <div class="hero-actions">
                    <?php if ( is_user_logged_in() ) : ?>
                        <a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Post a Swap', 'staffswap' ); ?></a>
                        <a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'Find Swaps', 'staffswap' ); ?></a>
                    <?php else : ?>
                        <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html( get_theme_mod( 'staffswap_hero_primary_label', 'Sign Up Free' ) ); ?></a>
                        <a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html( get_theme_mod( 'staffswap_hero_secondary_label', 'Find Swaps' ) ); ?></a>
                    <?php endif; ?>
                </div>
                <div class="benefits-row">
                    <?php foreach ( $benefits as $benefit ) : ?>
                        <div class="benefit-item"><span class="benefit-icon">✓</span><strong><?php echo esc_html( $benefit[0] ); ?></strong></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="swap-visual<?php echo $hero_image ? ' swap-visual--image' : ''; ?>" <?php echo $hero_image ? '' : 'aria-label="Swap illustration"'; ?>>
                <?php if ( $hero_image ) : ?>
                    <?php echo $hero_image; // WordPress generates the escaped responsive image markup. ?>
                <?php else : ?>
                    <div class="swap-node swap-node--from">
                        <div class="swap-avatar">LM</div>
                        <div class="swap-node__meta">
                            <strong>Lusaka</strong>
                            <small>Current workplace</small>
                        </div>
                    </div>
                    <div class="swap-connector"><b>&harr;</b><small>Current → Desired</small></div>
                    <div class="swap-node swap-node--to">
                        <div class="swap-avatar swap-avatar--blue">BM</div>
                        <div class="swap-node__meta">
                            <strong>Ndola</strong>
                            <small>Preferred location</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <form class="quick-search panel" method="get" action="<?php echo esc_url( home_url( '/search/' ) ); ?>">
            <div class="field"><label for="hero-current"><?php echo esc_html__( 'Current Location', 'staffswap' ); ?></label><?php echo function_exists( 'staffswap_render_location_select' ) ? staffswap_render_location_select( 'hero-current', 'current_location', '', false ) : '<select id="hero-current" name="current_location"><option value="">' . esc_html__( 'Any location', 'staffswap' ) . '</option></select>'; ?></div>
            <div class="field"><label for="hero-desired"><?php echo esc_html__( 'Desired Location', 'staffswap' ); ?></label><?php echo function_exists( 'staffswap_render_location_select' ) ? staffswap_render_location_select( 'hero-desired', 'desired_location', '', false ) : '<select id="hero-desired" name="desired_location"><option value="">' . esc_html__( 'Any location', 'staffswap' ) . '</option></select>'; ?></div>
            <div class="field"><label for="hero-profession"><?php echo esc_html__( 'Profession', 'staffswap' ); ?></label><select id="hero-profession" name="profession"><option value=""><?php echo esc_html__( 'All professions', 'staffswap' ); ?></option><?php if ( function_exists( 'staffswap_profession_options' ) ) : foreach ( staffswap_profession_options() as $profession_option ) : ?><option value="<?php echo esc_attr( $profession_option ); ?>"><?php echo esc_html( $profession_option ); ?></option><?php endforeach; endif; ?></select></div>
            <div class="field"><label for="hero-employer"><?php echo esc_html__( 'Employer Type', 'staffswap' ); ?></label><select id="hero-employer" name="employer_type"><option value=""><?php echo esc_html__( 'All types', 'staffswap' ); ?></option><?php if ( function_exists( 'staffswap_employer_types' ) ) : foreach ( staffswap_employer_types() as $type => $label ) : ?><option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; endif; ?></select></div>
            <button type="submit" class="quick-search__submit">Find Matching Swaps</button>
        </form>

    </div>
</section>
<section class="how-it-works">
    <div class="how-it-works__intro<?php echo $how_it_works_image ? ' has-section-image' : ''; ?>">
        <div class="section-heading section-heading--center">
            <p class="eyebrow">A CLEARER WAY FORWARD</p>
            <h2>How StaffExchangeHub Works</h2>
            <p class="muted">A clear path from creating your profile to arranging a potential exchange.</p>
        </div>
        <?php if ( $how_it_works_image ) : ?>
            <div class="how-it-works__image"><?php echo $how_it_works_image; // WordPress generates the escaped responsive image markup. ?></div>
        <?php endif; ?>
    </div>
    <div class="steps">
        <?php $steps = array(
            array( '01', 'Create Your Free Profile', 'Register and add your profession, current workplace and location.', 'dashicons-admin-users' ),
            array( '02', 'Post Your Swap Request', 'Share where you are, where you want to move and what flexibility you have.', 'dashicons-edit' ),
            array( '03', 'Discover Potential Matches', 'Compare compatible listings and identify reciprocal exchange opportunities.', 'dashicons-search' ),
            array( '04', 'Connect and Discuss', 'Express interest, chat securely and confirm whether both parties are interested.', 'dashicons-format-chat' ),
            array( '05', 'Arrange Your Exchange', 'Follow employer or institution approval procedures before finalising any move.', 'dashicons-yes-alt' )
        ); foreach ( $steps as $step ) : ?>
            <div class="step">
                <span class="step__number"><?php echo esc_html( $step[0] ); ?></span>
                <span class="dashicons <?php echo esc_attr( $step[3] ); ?>" aria-hidden="true"></span>
                <h3><?php echo esc_html( $step[1] ); ?></h3>
                <p><?php echo esc_html( $step[2] ); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="cta-strip">
        <p>Transfers still require approval from the relevant employer or institution.</p>
        <?php if ( is_user_logged_in() ) : ?>
            <a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Post a Swap', 'staffswap' ); ?></a>
        <?php else : ?>
            <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html__( 'Create Free Account', 'staffswap' ); ?></a>
        <?php endif; ?>
    </div>
</section>
<section class="featured-section">
    <div class="section-heading section-heading--center featured-section__heading<?php echo $opportunities_image ? ' has-section-image' : ''; ?>">
        <div>
            <p class="eyebrow"><?php echo esc_html__( 'Latest opportunities', 'staffswap' ); ?></p>
            <h2><?php echo esc_html__( 'Latest Workplace Swap Opportunities', 'staffswap' ); ?></h2>
            <p class="muted"><?php echo esc_html__( 'Explore recent swap requests from professionals looking to relocate across Zambia.', 'staffswap' ); ?></p>
        </div>
        <?php if ( $opportunities_image ) : ?>
            <div class="featured-section__image"><?php echo $opportunities_image; // WordPress generates the escaped responsive image markup. ?></div>
        <?php endif; ?>
        <a class="text-link" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'View all listings', 'staffswap' ); ?> &rarr;</a>
    </div>
    <div class="featured-carousel" data-featured-carousel role="region" aria-roledescription="carousel" aria-label="<?php echo esc_attr__( 'Latest swap opportunities', 'staffswap' ); ?>">
        <div class="featured-carousel__viewport" data-featured-carousel-viewport>
            <div class="featured-grid" data-featured-carousel-track>
                <?php if ( function_exists( 'staffswap_listing_card' ) ) : $featured = new WP_Query( array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'posts_per_page' => 6 ) ); if ( $featured->have_posts() ) : while ( $featured->have_posts() ) : $featured->the_post(); echo staffswap_listing_card( get_the_ID() ); endwhile; wp_reset_postdata(); else : ?><div class="panel"><h2><?php echo esc_html__( 'No swap requests published yet', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Be the first to post a workplace swap request and appear here.', 'staffswap' ); ?></p><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Create a swap post', 'staffswap' ); ?></a></div><?php endif; else : ?><div class="panel"><h2><?php echo esc_html__( 'Featured swaps are coming soon', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Activate StaffSwap Core and publish your first listing.', 'staffswap' ); ?></p></div><?php endif; ?>
            </div>
        </div>
        <div class="featured-carousel__controls" data-featured-carousel-controls hidden>
            <button type="button" data-featured-carousel-prev aria-label="<?php echo esc_attr__( 'Previous swap opportunities', 'staffswap' ); ?>">&larr; <?php echo esc_html__( 'Previous', 'staffswap' ); ?></button>
            <button type="button" data-featured-carousel-pause aria-pressed="false"><?php echo esc_html__( 'Pause rotation', 'staffswap' ); ?></button>
            <button type="button" data-featured-carousel-next aria-label="<?php echo esc_attr__( 'Next swap opportunities', 'staffswap' ); ?>"><?php echo esc_html__( 'Next', 'staffswap' ); ?> &rarr;</button>
        </div>
    </div>
</section>
<section class="badge-explainer" aria-labelledby="badge-explainer-title">
    <div class="badge-explainer__intro">
        <p class="eyebrow">Clear signals</p>
        <h2 id="badge-explainer-title">What do these badges mean?</h2>
    </div>
    <div class="badge-explainer__items">
        <div class="badge-explainer__item badge-explainer__item--verified"><span class="dashicons dashicons-shield" aria-hidden="true"></span><div><strong>Verified</strong><p>Member identity and listing details reviewed by StaffExchangeHub.</p></div></div>
        <div class="badge-explainer__item badge-explainer__item--housing"><span class="dashicons dashicons-admin-home" aria-hidden="true"></span><div><strong>Housing available / not included</strong><p>Shows whether staff housing is part of the move.</p></div></div>
        <div class="badge-explainer__item badge-explainer__item--nearby"><span class="dashicons dashicons-location" aria-hidden="true"></span><div><strong>Nearby towns OK</strong><p>Open to suitable nearby towns or districts.</p></div></div>
        <div class="badge-explainer__item badge-explainer__item--relocation"><span class="dashicons dashicons-car" aria-hidden="true"></span><div><strong>Relocation support</strong><p>Willing to assist with moving logistics.</p></div></div>
    </div>
</section>
<section class="stats-band" aria-label="<?php echo esc_attr__( 'Platform highlights', 'staffswap' ); ?>">
    <div class="stats-grid">
        <div class="stats-item"><span class="dashicons dashicons-location" aria-hidden="true"></span><div><strong>10</strong><span><?php echo esc_html__( 'Zambian Provinces', 'staffswap' ); ?></span></div></div>
        <div class="stats-item"><span class="dashicons dashicons-portfolio" aria-hidden="true"></span><div><strong><?php echo esc_html( number_format_i18n( $profession_count ) ); ?></strong><span><?php echo esc_html__( 'Profession Options', 'staffswap' ); ?></span></div></div>
        <div class="stats-item"><span class="dashicons dashicons-randomize" aria-hidden="true"></span><div><strong><?php echo esc_html__( '2-Way', 'staffswap' ); ?></strong><span><?php echo esc_html__( 'Swap Connections', 'staffswap' ); ?></span></div></div>
        <div class="stats-item"><span class="dashicons dashicons-groups" aria-hidden="true"></span><div><strong><?php echo esc_html__( 'Free', 'staffswap' ); ?></strong><span><?php echo esc_html__( 'Registration', 'staffswap' ); ?></span></div></div>
    </div>
</section>
<section class="landing-bottom">
    <article class="panel resource-promo">
        <div class="resource-promo__content">
            <p class="eyebrow">LEARN AND GROW</p>
            <h2>Explore Our Resources Centre</h2>
            <p>Access career guides, CV templates, internship resources and professional development materials.</p>
            <a class="button button--outline" href="<?php echo esc_url( home_url( '/resources/' ) ); ?>">Explore Resources</a>
        </div>
        <?php if ( $resources_image ) : ?>
            <div class="resource-promo__visual"><?php echo $resources_image; // WordPress generates the escaped responsive image markup. ?></div>
        <?php else : ?>
            <div class="resource-promo__visual resource-promo__visual--fallback" aria-hidden="true"><span class="dashicons dashicons-book-alt"></span><span class="resource-promo__spark resource-promo__spark--one"></span><span class="resource-promo__spark resource-promo__spark--two"></span></div>
        <?php endif; ?>
    </article>
    <div class="panel alert-panel">
        <p class="eyebrow">NEVER MISS A POTENTIAL SWAP</p>
        <h2>Never Miss a Potential Swap</h2>
        <p>Tell us your profession and preferred province to receive relevant updates.</p>
        <?php echo do_shortcode( '[staffswap_alert_signup]' ); ?>
    </div>
</section>
<section class="home-pricing">
    <div class="section-heading">
        <div><p class="eyebrow"><?php echo esc_html__( 'MEMBERSHIP & PRICING', 'staffswap' ); ?></p><h2><?php echo esc_html__( 'Start free. Upgrade when you need member features.', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Compare the current access and choose what suits your search.', 'staffswap' ); ?></p></div>
        <a class="text-link" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php echo esc_html__( 'Compare all plans', 'staffswap' ); ?> &rarr;</a>
    </div>
    <div class="home-pricing__grid">
        <article><span class="dashicons dashicons-admin-users" aria-hidden="true"></span><p class="eyebrow">Basic</p><h3>K0 <small>/ free</small></h3><p><?php echo esc_html__( 'Create a profile, browse listings and access free resources.', 'staffswap' ); ?></p></article>
        <article class="home-pricing__featured"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span><p class="eyebrow">VIP Gold</p><h3><?php echo shortcode_exists( 'staffswap_plan_price' ) ? wp_kses_post( do_shortcode( '[staffswap_plan_price plan="month"]' ) ) : 'K99'; ?> <small>/ 30 days</small></h3><p><?php echo esc_html__( 'One-time access to member messaging and formal swap offers.', 'staffswap' ); ?></p></article>
        <article><span class="dashicons dashicons-awards" aria-hidden="true"></span><p class="eyebrow">VIP Lifetime</p><h3><?php echo shortcode_exists( 'staffswap_plan_price' ) ? wp_kses_post( do_shortcode( '[staffswap_plan_price plan="lifetime"]' ) ) : 'K199'; ?> <small>/ once</small></h3><p><?php echo esc_html__( 'One-time access to core VIP features while the platform operates.', 'staffswap' ); ?></p></article>
    </div>
</section>
<section class="final-cta">
    <div>
        <p class="eyebrow">READY TO FIND YOUR WORKPLACE SWAP?</p>
        <h2><?php echo is_user_logged_in() ? esc_html__( 'Post your preferred location and start exploring potential matches.', 'staffswap' ) : esc_html__( 'Create your free account, post your preferred location and start exploring potential matches.', 'staffswap' ); ?></h2>
    </div>
    <?php if ( is_user_logged_in() ) : ?>
        <a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Post a Swap', 'staffswap' ); ?></a>
    <?php else : ?>
        <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html__( 'Create Free Account', 'staffswap' ); ?></a>
    <?php endif; ?>
</section>
<?php get_footer(); ?>
