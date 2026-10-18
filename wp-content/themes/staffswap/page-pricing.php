<?php
get_header();
$staffswap_pricing_elementor_data = get_post_meta( get_the_ID(), '_elementor_data', true );
if ( ! empty( $staffswap_pricing_elementor_data ) || isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) :
	while ( have_posts() ) : the_post(); the_content(); endwhile;
	get_footer();
	return;
endif;
?>
<section class="pricing-page">
    <div class="pricing-page__hero">
        <div class="pricing-page__copy">
            <p class="eyebrow"><?php echo esc_html__( 'SIMPLE, TRANSPARENT PRICING', 'staffswap' ); ?></p>
            <h1><?php echo esc_html__( 'Find Your Swap. Choose Your Plan.', 'staffswap' ); ?></h1>
            <p><?php echo esc_html__( 'Start for free and explore workplace exchange opportunities across Zambia. Upgrade for enhanced visibility, additional contact requests and membership benefits.', 'staffswap' ); ?></p>
            <div class="pricing-page__badges">
                <span>Safe &amp; Secure</span>
                <span>Connect with verified professionals</span>
                <span>Access real opportunities</span>
                <span>Affordable memberships</span>
            </div>
        </div>
        <div class="pricing-page__photo" aria-hidden="true">
            <div class="pricing-page__person">
                <div class="pricing-page__avatar">S</div>
                <div>
                    <strong>Ndola</strong>
                    <small>Teacher</small>
                </div>
            </div>
        </div>
    </div>

    <div class="pricing-grid">
        <article class="pricing-card pricing-card--basic">
            <div class="pricing-card__top">
                <div class="pricing-card__icon">◔</div>
                <p class="eyebrow">Basic</p>
            </div>
            <h2>Free Forever</h2>
            <p class="pricing-price">K0 <small>/ free</small></p>
            <ul>
                <li>Create your profile</li>
                <li>Browse swap listings</li>
                <li>Post 1 active swap request</li>
                <li>3 interest requests monthly</li>
                <li>Access free resources</li>
            </ul>
            <?php if ( is_user_logged_in() ) : ?>
                <a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'Browse Swap Listings', 'staffswap' ); ?></a>
            <?php else : ?>
                <a class="button button--outline" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html__( 'Sign Up Free', 'staffswap' ); ?></a>
            <?php endif; ?>
        </article>

        <article class="pricing-card pricing-card--featured">
            <div class="pricing-card__top">
                <div class="pricing-card__icon">★</div>
                <p class="eyebrow">VIP Gold</p>
            </div>
            <div class="pricing-card__badge">BEST VALUE</div>
            <h2>30 Days</h2>
            <p class="pricing-price">K99 <small>/ one-time</small></p>
            <ul>
                <li>All Basic benefits</li>
                <li>Up to 20 interest requests</li>
                <li>Priority listing visibility</li>
                <li>Direct contact requests</li>
                <li>Match notifications</li>
            </ul>
            <?php echo shortcode_exists( 'staffswap_upgrade' ) ? do_shortcode( '[staffswap_upgrade plan="month"]' ) : ''; ?>
        </article>

        <article class="pricing-card pricing-card--lifetime">
            <div class="pricing-card__top">
                <div class="pricing-card__icon">✓</div>
                <p class="eyebrow">Founding VIP Lifetime</p>
            </div>
            <h2>Lifetime Access</h2>
            <p class="pricing-price">K199 <small>/ once</small></p>
            <ul>
                <li>All VIP Gold benefits</li>
                <li>Lifetime core VIP access</li>
                <li>Founding member badge</li>
                <li>Priority listing visibility</li>
                <li>Match notifications</li>
            </ul>
            <?php echo shortcode_exists( 'staffswap_upgrade' ) ? do_shortcode( '[staffswap_upgrade plan="lifetime"]' ) : ''; ?>
        </article>
    </div>

    <div class="membership-compare">
        <h2>Compare Membership Benefits</h2>
        <table>
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Basic (Free)</th>
                    <th>VIP Gold (K99)</th>
                    <th>Founding Lifetime (K199)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Membership duration</td><td>Unlimited</td><td>30 days</td><td>Lifetime</td></tr>
                <tr><td>Create profile</td><td>✓</td><td>✓</td><td>✓</td></tr>
                <tr><td>Browse swap listings</td><td>✓</td><td>✓</td><td>✓</td></tr>
                <tr><td>Post active swap request</td><td>1</td><td>1</td><td>1</td></tr>
                <tr><td>Interest requests</td><td>3</td><td>20</td><td>20</td></tr>
                <tr><td>Priority listing visibility</td><td>—</td><td>✓</td><td>✓</td></tr>
                <tr><td>Direct contact requests</td><td>Limited</td><td>Expanded</td><td>Expanded</td></tr>
                <tr><td>Match notifications</td><td>—</td><td>✓</td><td>✓</td></tr>
                <tr><td>Access free resources</td><td>✓</td><td>✓</td><td>✓</td></tr>
                <tr><td>Premium transfer templates</td><td>—</td><td>✓</td><td>✓</td></tr>
                <tr><td>Founding member badge</td><td>—</td><td>—</td><td>✓</td></tr>
            </tbody>
        </table>
    </div>

    <div class="trust-row">
        <div class="trust-item">
            <span class="trust-item__icon">✓</span>
            <h3>Secure and Reliable</h3>
            <p>Your information is kept safe and used only with your permission.</p>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon">✓</span>
            <h3>Verified Professionals</h3>
            <p>Connect with genuine professionals across Zambia.</p>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon">✓</span>
            <h3>More Opportunities</h3>
            <p>Increase your visibility and reach with VIP membership.</p>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon">✓</span>
            <h3>Helpful Resources</h3>
            <p>Access templates, guides and career support.</p>
        </div>
    </div>

    <p class="pricing-note">StaffExchangeHub helps professionals discover potential workplace exchange partners. Membership fees cover platform features and do not guarantee successful exchanges or institutional transfer approval.</p>

    <section class="faq-section">
        <div class="section-heading section-heading--center">
            <p class="eyebrow">NEED TO KNOW</p>
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="faq-grid">
            <details open><summary>Is StaffExchangeHub free to join?</summary><p>Yes. Basic access is free and allows you to create a profile, browse listings and explore potential matches.</p></details>
            <details><summary>What happens when my K99 membership expires?</summary><p>VIP Gold is a one-time 30-day access option for enhanced visibility and stronger engagement tools. Renewals are managed according to the active plan settings.</p></details>
            <details><summary>What does lifetime membership mean?</summary><p>Founding VIP Lifetime gives you continued access to the core VIP features for as long as StaffExchangeHub continues operating under the published product terms.</p></details>
            <details><summary>Does VIP membership guarantee a swap?</summary><p>No. Membership helps you discover and contact potential exchange partners and increases visibility, but any workplace transfer still depends on matching, consent and employer approval.</p></details>
            <details><summary>Can I get a refund?</summary><p>Refunds are handled according to the active membership policy and the platform’s purchase terms.</p></details>
        </div>
    </section>

    <div class="final-cta final-cta--compact">
        <div class="final-cta__content">
            <span class="final-cta__badge">Ready to join?</span>
            <h2>Join StaffExchangeHub today and connect with professionals across Zambia.</h2>
        </div>
        <div class="final-cta__actions">
            <?php if ( is_user_logged_in() ) : ?>
                <a class="button button--primary" href="<?php echo esc_url( home_url( '/create-swap/' ) ); ?>"><?php echo esc_html__( 'Post a Swap', 'staffswap' ); ?></a>
                <a class="button button--outline button--outline-light" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'Find Swaps', 'staffswap' ); ?></a>
            <?php else : ?>
                <a class="button button--primary" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html__( 'Sign Up Free', 'staffswap' ); ?></a>
                <a class="button button--outline button--outline-light" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'View Pricing Plans', 'staffswap' ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php get_footer(); ?>
