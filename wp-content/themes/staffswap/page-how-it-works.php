<?php get_header(); if ( staffswap_has_builder_content() ) : while ( have_posts() ) : the_post(); the_content(); endwhile; get_footer(); return; endif; ?>
<section class="page-hero">
    <p class="eyebrow"><?php echo esc_html__( 'HOW STAFFEXCHANGEHUB WORKS', 'staffswap' ); ?></p>
    <h1><?php echo esc_html__( 'How StaffExchangeHub Works', 'staffswap' ); ?></h1>
    <p><?php echo esc_html__( 'StaffExchangeHub helps professionals find potential workplace exchanges by matching profession, current location and desired destination.', 'staffswap' ); ?></p>
</section>
<section class="process-grid">
    <?php $steps = array(
        array( '01', 'Create Your Free Profile', 'Register and provide your profession, current workplace and location.' ),
        array( '02', 'Post Your Swap Request', 'Tell the network where you work now, where you want to move and any flexible destinations.' ),
        array( '03', 'Discover Potential Matches', 'Search listings and identify professionals with compatible exchange preferences.' ),
        array( '04', 'Connect and Discuss', 'Express interest, communicate securely and confirm whether both parties are interested.' ),
        array( '05', 'Seek Approval and Arrange Your Move', 'Follow the required employer or institutional procedures before finalising any transfer.' )
    ); foreach ( $steps as $step ) : ?>
        <article class="process-card">
            <span><?php echo esc_html( $step[0] ); ?></span>
            <h2><?php echo esc_html( $step[1] ); ?></h2>
            <p><?php echo esc_html( $step[2] ); ?></p>
        </article>
    <?php endforeach; ?>
</section>
<section class="example-section">
    <div class="section-heading">
        <p class="eyebrow">REAL-WORLD EXAMPLE</p>
        <h2>See How a Workplace Swap Could Work</h2>
    </div>
    <div class="swap-example">
        <div class="swap-example__card">
            <span class="swap-example__label">Current</span>
            <strong>Registered Nurse</strong>
            <p>Lusaka General Hospital</p>
            <small>Lusaka, Lusaka Province</small>
        </div>
        <div class="swap-example__arrow">↔</div>
        <div class="swap-example__card swap-example__card--alt">
            <span class="swap-example__label">Current</span>
            <strong>Registered Nurse</strong>
            <p>Ndola Central Hospital</p>
            <small>Ndola, Copperbelt Province</small>
        </div>
    </div>
    <p class="muted">If their professional requirements are compatible, they can discuss the exchange and seek approval from their respective institutions before arranging any move.</p>
</section>
<section class="faq-section">
    <div class="section-heading section-heading--center"><p class="eyebrow">COMMON QUESTIONS</p><h2>Frequently Asked Questions</h2></div>
    <div class="faq-grid">
        <details open><summary>Is StaffExchangeHub free?</summary><p>Yes. You can create a profile, browse listings and explore potential exchanges for free.</p></details>
        <details><summary>Does StaffExchangeHub approve transfers?</summary><p>No. StaffExchangeHub helps connect professionals and facilitates conversations, but any transfer still depends on employer or institutional approval.</p></details>
        <details><summary>Can I swap with someone in a nearby town?</summary><p>Yes. Many members specify nearby towns or districts as flexible options when planning a move.</p></details>
        <details><summary>Does finding a match guarantee a swap?</summary><p>No. A match shows compatibility and potential interest; both parties still need to agree and complete any required approval process.</p></details>
    </div>
</section>
<section class="split-callout">
    <div>
        <p class="eyebrow">READY TO MOVE?</p>
        <h2><?php echo is_user_logged_in() ? esc_html__( 'Post your request and start exploring potential workplace matches.', 'staffswap' ) : esc_html__( 'Create your free account and start exploring potential workplace matches.', 'staffswap' ); ?></h2>
    </div>
    <div class="split-callout__actions">
        <?php if ( is_user_logged_in() ) : ?>
            <a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Post a Swap', 'staffswap' ); ?></a>
        <?php else : ?>
            <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html__( 'Sign Up Free', 'staffswap' ); ?></a>
        <?php endif; ?>
        <a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'Browse Available Swaps', 'staffswap' ); ?></a>
    </div>
</section>
<?php get_footer(); ?>
