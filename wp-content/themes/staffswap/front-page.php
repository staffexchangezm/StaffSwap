<?php
get_header();
$elementor_content = get_post_meta( get_the_ID(), '_elementor_data', true );
$elementor_preview = isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() );
$hero_title = staffswap_home_setting( 'hero_title', 'Workplace mobility, made simple' );
$hero_text = staffswap_home_setting( 'hero_text', 'Connect with professionals across Zambia looking to exchange work locations. Search available opportunities, create your swap request and explore relocation possibilities.' );
$benefits = array(
    array( 'Search by profession', 'Browse listings by your role, location and preferred move.' ),
    array( 'Review potential matches', 'Compare current and desired provinces before you connect.' ),
    array( 'Protect your privacy', 'Keep your details private while exploring exchanges.' ),
);
?>
<?php if ( ! empty( $elementor_content ) || $elementor_preview ) : ?><div id="content" class="staffswap-elementor-content"><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div><?php get_footer(); return; endif; ?>
<section class="landing-hero">
    <div class="landing-hero__copy">
        <p class="eyebrow">WORKPLACE MOBILITY, MADE SIMPLE</p>
        <h1><?php echo esc_html( $hero_title ); ?></h1>
        <p><?php echo esc_html( $hero_text ); ?></p>
        <div class="hero-actions">
            <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html( get_theme_mod( 'staffswap_hero_primary_label', 'Sign Up Free' ) ); ?></a>
            <a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html( get_theme_mod( 'staffswap_hero_secondary_label', 'Find Swaps' ) ); ?></a>
        </div>
        <div class="benefits-row">
            <?php foreach ( $benefits as $benefit ) : ?>
                <div class="benefit-item"><span class="benefit-icon">✓</span><strong><?php echo esc_html( $benefit[0] ); ?></strong></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="swap-visual">
        <div class="swap-node swap-node--from">
            <div class="swap-avatar">LM</div>
            <strong>Lusaka</strong>
            <small>Current workplace</small>
        </div>
        <div class="swap-connector"><b>&harr;</b><small>Current → Desired</small></div>
        <div class="swap-node swap-node--to">
            <div class="swap-avatar swap-avatar--blue">BM</div>
            <strong>Ndola</strong>
            <small>Preferred location</small>
        </div>
    </div>
</section>
<form class="quick-search panel" method="get" action="<?php echo esc_url( home_url( '/search/' ) ); ?>">
    <div class="field"><label for="hero-current">Current Location</label><input id="hero-current" name="current_location" placeholder="Select province"></div>
    <div class="field"><label for="hero-desired">Desired Location</label><input id="hero-desired" name="desired_location" placeholder="Select province"></div>
    <div class="field"><label for="hero-profession">Profession</label><input id="hero-profession" name="profession" placeholder="Select profession"></div>
    <div class="field"><label for="hero-employer">Employer Type</label><input id="hero-employer" name="current_employer" placeholder="All types"></div>
    <input type="submit" value="Search Swaps">
</form>
<section class="how-it-works">
    <div class="section-heading section-heading--center">
        <p class="eyebrow">A CLEARER WAY FORWARD</p>
        <h2>How StaffExchangeHub Works</h2>
    </div>
    <div class="steps">
        <?php $steps = array(
            array( '01', 'Create Your Free Profile', 'Register and add your profession, current workplace and location.' ),
            array( '02', 'Post Your Swap Request', 'Share where you are, where you want to move and what flexibility you have.' ),
            array( '03', 'Discover Potential Matches', 'Compare compatible listings and identify reciprocal exchange opportunities.' ),
            array( '04', 'Connect and Discuss', 'Express interest, chat securely and confirm whether both parties are interested.' ),
            array( '05', 'Arrange Your Exchange', 'Follow employer or institution approval procedures before finalising any move.' )
        ); foreach ( $steps as $step ) : ?>
            <div class="step">
                <span><?php echo esc_html( $step[0] ); ?></span>
                <h3><?php echo esc_html( $step[1] ); ?></h3>
                <p><?php echo esc_html( $step[2] ); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="cta-strip">
        <p>Transfers still require approval from the relevant employer or institution.</p>
        <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create Free Account</a>
    </div>
</section>
<section class="featured-section">
    <div class="section-heading section-heading--center">
        <div>
            <p class="eyebrow"><?php echo esc_html__( 'Latest opportunities', 'staffswap' ); ?></p>
            <h2><?php echo esc_html__( 'Latest Workplace Swap Opportunities', 'staffswap' ); ?></h2>
            <p class="muted"><?php echo esc_html__( 'Explore recent swap requests from professionals looking to relocate across Zambia.', 'staffswap' ); ?></p>
        </div>
        <a class="text-link" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'View all listings', 'staffswap' ); ?> &rarr;</a>
    </div>
    <div class="featured-grid">
        <?php if ( function_exists( 'staffswap_listing_card' ) ) : $featured = new WP_Query( array( 'post_type' => 'swap_listing', 'post_status' => 'publish', 'posts_per_page' => 3 ) ); if ( $featured->have_posts() ) : while ( $featured->have_posts() ) : $featured->the_post(); echo staffswap_listing_card( get_the_ID() ); endwhile; wp_reset_postdata(); else : ?><div class="panel"><h2><?php echo esc_html__( 'No swap requests published yet', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Be the first to post a workplace swap request and appear here.', 'staffswap' ); ?></p><a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Create a swap post', 'staffswap' ); ?></a></div><?php endif; else : ?><div class="panel"><h2><?php echo esc_html__( 'Featured swaps are coming soon', 'staffswap' ); ?></h2><p class="muted"><?php echo esc_html__( 'Activate StaffSwap Core and publish your first listing.', 'staffswap' ); ?></p></div><?php endif; ?>
    </div>
</section>
<section class="badge-explainer" aria-labelledby="badge-explainer-title">
    <div class="badge-explainer__intro">
        <p class="eyebrow">Clear signals</p>
        <h2 id="badge-explainer-title">What do these badges mean?</h2>
    </div>
    <div class="badge-explainer__items">
        <div class="badge-explainer__item badge-explainer__item--verified"><span class="dashicons dashicons-shield" aria-hidden="true"></span><div><strong>Verified</strong><p>Identity and listing details have been checked.</p></div></div>
        <div class="badge-explainer__item badge-explainer__item--housing"><span class="dashicons dashicons-admin-home" aria-hidden="true"></span><div><strong>Housing available / not included</strong><p>Shows whether staff housing is part of the move.</p></div></div>
        <div class="badge-explainer__item badge-explainer__item--nearby"><span class="dashicons dashicons-location" aria-hidden="true"></span><div><strong>Nearby towns OK</strong><p>Open to suitable nearby towns or districts.</p></div></div>
        <div class="badge-explainer__item badge-explainer__item--relocation"><span class="dashicons dashicons-car" aria-hidden="true"></span><div><strong>Relocation support</strong><p>Willing to assist with moving logistics.</p></div></div>
    </div>
</section>
<section class="landing-bottom">
    <article class="panel resource-promo">
        <p class="eyebrow">LEARN AND GROW</p>
        <h2>Explore Our Resources Centre</h2>
        <p>Access career guides, CV templates, internship resources and professional development materials.</p>
        <a class="button button--outline" href="<?php echo esc_url( home_url( '/resources/' ) ); ?>">Explore Resources</a>
    </article>
    <div class="panel alert-panel">
        <p class="eyebrow">NEVER MISS A POTENTIAL SWAP</p>
        <h2>Get Swap Alerts</h2>
        <p>Tell us your profession and preferred province to receive relevant updates.</p>
        <?php echo do_shortcode( '[staffswap_alert_signup]' ); ?>
    </div>
</section>
<section class="final-cta">
    <div>
        <p class="eyebrow">READY TO FIND YOUR WORKPLACE SWAP?</p>
        <h2>Create your free account, post your preferred location and start exploring potential matches.</h2>
    </div>
    <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create Free Account</a>
</section>
<?php get_footer(); ?>
