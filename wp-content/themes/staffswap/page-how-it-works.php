<?php
get_header();
if ( staffswap_has_builder_content() ) : while ( have_posts() ) : the_post(); the_content(); endwhile; get_footer(); return; endif;
$how_page_image = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
?>
<section class="how-page">
	<header class="how-page__hero<?php echo $how_page_image ? ' has-photo' : ''; ?>"<?php if ( $how_page_image ) : ?> style="--how-page-photo:url('<?php echo esc_url( $how_page_image ); ?>')"<?php endif; ?>>
		<div class="how-page__hero-copy">
			<p class="eyebrow"><?php echo esc_html__( 'THE STAFFEXCHANGEHUB METHOD', 'staffswap' ); ?></p>
			<h1><?php echo esc_html__( 'How StaffExchangeHub Works', 'staffswap' ); ?></h1>
			<p><?php echo esc_html__( 'StaffExchangeHub helps professionals across Zambia find and connect with potential workplace exchange partners. Follow these simple steps to explore opportunities, make a match and work with your employer to complete the exchange.', 'staffswap' ); ?></p>
			<div class="how-page__actions">
				<?php if ( is_user_logged_in() ) : ?>
					<a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><span class="dashicons dashicons-plus-alt2"></span><?php echo esc_html__( 'Post Your Swap', 'staffswap' ); ?></a>
				<?php else : ?>
					<a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><span class="dashicons dashicons-admin-users"></span><?php echo esc_html__( 'Sign Up Free', 'staffswap' ); ?></a>
				<?php endif; ?>
				<a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><span class="dashicons dashicons-search"></span><?php echo esc_html__( 'Browse Available Swaps', 'staffswap' ); ?></a>
			</div>
		</div>
		<?php if ( $how_page_image ) : ?><div class="how-page__visual" aria-hidden="true"></div><?php else : ?><div class="how-page__map" aria-hidden="true"><span class="how-page__map-shape"></span><span class="how-page__pin how-page__pin--one"><span class="dashicons dashicons-location"></span></span><span class="how-page__pin how-page__pin--two"><span class="dashicons dashicons-location"></span></span><span class="how-page__route how-page__route--green"></span><span class="how-page__route how-page__route--blue"></span></div><?php endif; ?>
	</header>
	<div class="how-page__steps-heading"><h2><?php echo esc_html__( 'The 5 Steps to a Successful Workplace Exchange', 'staffswap' ); ?></h2><p><?php echo esc_html__( 'From creating your profile to completing an exchange, here is how it works.', 'staffswap' ); ?></p></div>
<section class="process-grid">
    <?php $steps = array(
        array( '01', 'Create Your Free Profile', 'Register and provide your profession, current workplace and location.', 'dashicons-admin-users' ),
        array( '02', 'Post Your Swap Request', 'Tell the network where you work now, where you want to move and any flexible destinations.', 'dashicons-edit' ),
        array( '03', 'Discover Potential Matches', 'Search listings and identify professionals with compatible exchange preferences.', 'dashicons-search' ),
        array( '04', 'Connect and Discuss', 'Express interest, communicate securely and confirm whether both parties are interested.', 'dashicons-format-chat' ),
        array( '05', 'Seek Approval and Arrange Your Move', 'Follow the required employer or institutional procedures before finalising any transfer.', 'dashicons-yes-alt' )
    ); foreach ( $steps as $step ) : ?>
        <article class="process-card">
            <span class="process-card__number"><?php echo esc_html( $step[0] ); ?></span>
            <span class="dashicons <?php echo esc_attr( $step[3] ); ?>" aria-hidden="true"></span>
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
            <span class="swap-example__label">Nurse A</span>
            <strong>Registered Nurse</strong>
            <div class="swap-example__route"><span><small>Current location</small><b>Lusaka</b></span><span class="swap-example__direction" aria-hidden="true">&rarr;</span><span><small>Wants to move</small><b>Ndola</b></span></div>
        </div>
        <div class="swap-example__reciprocal"><span aria-hidden="true">&harr;</span><strong>Potential reciprocal match</strong></div>
        <div class="swap-example__card swap-example__card--alt">
            <span class="swap-example__label">Nurse B</span>
            <strong>Registered Nurse</strong>
            <div class="swap-example__route"><span><small>Current location</small><b>Ndola</b></span><span class="swap-example__direction" aria-hidden="true">&rarr;</span><span><small>Wants to move</small><b>Lusaka</b></span></div>
        </div>
    </div>
    <p class="example-section__note">Both professionals must agree before they approach their respective employers or institutions. StaffExchangeHub facilitates connections but cannot approve transfers.</p>
</section>
<section class="how-guidance">
    <article class="how-guidance__card how-guidance__card--important">
        <h2><span class="dashicons dashicons-shield" aria-hidden="true"></span>Important to know</h2>
        <ul>
            <li>StaffExchangeHub helps professionals connect; employers and institutions decide whether transfers are approved.</li>
            <li>Keep personal contact information private until you choose to share it. Use platform messaging to start conversations.</li>
            <li>A Verified badge means the member's account was reviewed by the platform. It is not employer approval.</li>
        </ul>
    </article>
    <article class="how-guidance__card how-guidance__card--tips">
        <h2><span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>Tips for a successful exchange</h2>
        <ul>
            <li>Keep your profession, current location and preferred destination accurate.</li>
            <li>Discuss role requirements and timing before seeking formal approval.</li>
            <li>Confirm the official procedures with both institutions before making arrangements.</li>
        </ul>
    </article>
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
</section>
<?php get_footer(); ?>
