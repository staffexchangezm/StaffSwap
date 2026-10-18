<?php
get_header();
$staffswap_pricing_elementor_data = get_post_meta( get_the_ID(), '_elementor_data', true );
if ( ! empty( $staffswap_pricing_elementor_data ) || isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) :
	while ( have_posts() ) : the_post(); the_content(); endwhile;
	get_footer();
	return;
endif;
$pricing_page_image = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
?>
<section class="pricing-page">
    <div class="pricing-page__hero">
        <div class="pricing-page__copy">
            <p class="eyebrow"><?php echo esc_html__( 'SIMPLE, TRANSPARENT PRICING', 'staffswap' ); ?></p>
            <h1><?php echo esc_html__( 'Find Your Swap. Choose Your Plan.', 'staffswap' ); ?></h1>
            <p><?php echo esc_html__( 'Start for free and explore workplace exchange opportunities across Zambia. Upgrade for access to member messaging and formal swap offers.', 'staffswap' ); ?></p>
            <div class="pricing-page__badges">
                <span><span class="dashicons dashicons-lock" aria-hidden="true"></span>Private profiles</span>
                <span><span class="dashicons dashicons-location" aria-hidden="true"></span>Across Zambia</span>
                <span><span class="dashicons dashicons-update" aria-hidden="true"></span>Flexible plans</span>
                <span><span class="dashicons dashicons-info" aria-hidden="true"></span>No swap guarantees</span>
            </div>
        </div>
        <div class="pricing-page__photo<?php echo $pricing_page_image ? ' has-photo' : ''; ?>"<?php if ( $pricing_page_image ) : ?> style="--pricing-photo:url('<?php echo esc_url( $pricing_page_image ); ?>')"<?php endif; ?> aria-hidden="true">
            <?php if ( ! $pricing_page_image ) : ?>
            <div class="pricing-graphic">
                <div class="pricing-graphic__orbit pricing-graphic__orbit--outer"></div>
                <div class="pricing-graphic__orbit pricing-graphic__orbit--inner"></div>
                <div class="pricing-graphic__route pricing-graphic__route--from">
                    <span class="pricing-graphic__pin"><span class="dashicons dashicons-location" aria-hidden="true"></span></span>
                    <span><strong>Lusaka</strong><small>Current location</small></span>
                </div>
                <div class="pricing-graphic__exchange"><span class="dashicons dashicons-randomize" aria-hidden="true"></span></div>
                <div class="pricing-graphic__route pricing-graphic__route--to">
                    <span class="pricing-graphic__pin"><span class="dashicons dashicons-location" aria-hidden="true"></span></span>
                    <span><strong>Ndola</strong><small>Preferred location</small></span>
                </div>
                <div class="pricing-graphic__caption"><span class="dashicons dashicons-awards" aria-hidden="true"></span>Choose the plan that suits your search</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="pricing-grid">
        <article class="pricing-card pricing-card--basic">
            <div class="pricing-card__top">
                <div class="pricing-card__icon pricing-card__icon--basic"><span class="dashicons dashicons-admin-users" aria-hidden="true"></span></div>
                <p class="eyebrow">Basic</p>
            </div>
            <h2>Free Forever</h2>
            <p class="pricing-price">K0 <small>/ free</small></p>
            <ul>
                <li>Create your profile</li>
                <li>Browse swap listings</li>
                <li>Publish 1 active swap request</li>
                <li>3 member contact requests per month</li>
                <li>Access free resources</li>
                <li>Review potential matches</li>
            </ul>
            <?php if ( is_user_logged_in() ) : ?>
                <a class="button button--outline" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'Browse Swap Listings', 'staffswap' ); ?></a>
            <?php else : ?>
                <a class="button button--outline" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html__( 'Sign Up Free', 'staffswap' ); ?></a>
            <?php endif; ?>
        </article>

        <article class="pricing-card pricing-card--featured">
            <div class="pricing-card__top">
                <div class="pricing-card__icon pricing-card__icon--gold"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span></div>
                <p class="eyebrow">VIP Gold</p>
            </div>
            <div class="pricing-card__badge">BEST VALUE</div>
            <h2>30 Days</h2>
            <p class="pricing-price"><?php echo shortcode_exists( 'staffswap_plan_price' ) ? wp_kses_post( do_shortcode( '[staffswap_plan_price plan="month"]' ) ) : 'K99'; ?> <small>/ one-time</small></p>
            <ul>
                <li>All Basic benefits</li>
                <li>Member messaging access</li>
                <li>Formal swap offers</li>
                <li>20 member contact requests per month</li>
                <li>Priority listing visibility</li>
                <li>30 days of VIP features</li>
            </ul>
            <?php echo shortcode_exists( 'staffswap_upgrade' ) ? do_shortcode( '[staffswap_upgrade plan="month"]' ) : ''; ?>
        </article>

        <article class="pricing-card pricing-card--lifetime">
            <div class="pricing-card__top">
                <div class="pricing-card__icon pricing-card__icon--lifetime"><span class="dashicons dashicons-awards" aria-hidden="true"></span></div>
                <p class="eyebrow">Founding VIP Lifetime</p>
            </div>
            <h2>Lifetime Access</h2>
            <p class="pricing-price"><?php echo shortcode_exists( 'staffswap_plan_price' ) ? wp_kses_post( do_shortcode( '[staffswap_plan_price plan="lifetime"]' ) ) : 'K199'; ?> <small>/ once</small></p>
            <ul>
                <li>All VIP Gold benefits</li>
                <li>Lifetime core VIP access</li>
                <li>Member messaging access</li>
                <li>Formal swap offers</li>
                <li>20 member contact requests per month</li>
                <li>Priority listing visibility</li>
                <li>No recurring renewal</li>
            </ul>
            <p class="muted">Limited to the first 150 founding members. Founding Members receive a badge on their swap listing.</p>
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
                    <th>VIP Gold (<?php echo esc_html( shortcode_exists( 'staffswap_plan_price' ) ? wp_strip_all_tags( do_shortcode( '[staffswap_plan_price plan="month"]' ) ) : 'K99' ); ?>)</th>
                    <th>VIP Lifetime (<?php echo esc_html( shortcode_exists( 'staffswap_plan_price' ) ? wp_strip_all_tags( do_shortcode( '[staffswap_plan_price plan="lifetime"]' ) ) : 'K199' ); ?>)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Membership duration</td><td>Unlimited</td><td>30 days</td><td>Lifetime</td></tr>
                <tr><td>Create profile</td><td>✓</td><td>✓</td><td>✓</td></tr>
                <tr><td>Browse swap listings</td><td>✓</td><td>✓</td><td>✓</td></tr>
                <tr><td>Active swap listings</td><td>1</td><td>1</td><td>1</td></tr>
                <tr><td>Monthly member contact requests</td><td>3</td><td>20</td><td>20</td></tr>
                <tr><td>Member messaging and swap offers</td><td>Within monthly allowance</td><td>Within monthly allowance</td><td>Within monthly allowance</td></tr>
                <tr><td>Priority listing visibility</td><td>—</td><td>✓</td><td>✓</td></tr>
                <tr><td>Founding Member badge</td><td>—</td><td>—</td><td>✓ (first 150)</td></tr>
                <tr><td>Access free resources</td><td>✓</td><td>✓</td><td>✓</td></tr>
                <tr><td>Automatic renewal</td><td>No</td><td>No</td><td>No</td></tr>
            </tbody>
        </table>
    </div>

    <div class="trust-row">
        <div class="trust-item">
            <span class="trust-item__icon"><span class="dashicons dashicons-info" aria-hidden="true"></span></span>
            <h3>Clear plan benefits</h3>
            <p>Compare membership features before choosing a plan.</p>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon"><span class="dashicons dashicons-groups" aria-hidden="true"></span></span>
            <h3>Professional community</h3>
            <p>Explore workplace exchange opportunities with professionals across Zambia.</p>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon"><span class="dashicons dashicons-megaphone" aria-hidden="true"></span></span>
            <h3>Member messaging</h3>
            <p>Active VIP memberships unlock member messaging and formal swap offers.</p>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon"><span class="dashicons dashicons-book-alt" aria-hidden="true"></span></span>
            <h3>Helpful resources</h3>
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
            <details><summary>What happens when my VIP Gold membership expires?</summary><p>VIP Gold is a one-time 30-day purchase. Access to member messaging and swap offers ends when the membership expires; it does not renew automatically.</p></details>
            <details><summary>What does lifetime membership mean?</summary><p>VIP Lifetime is a one-time purchase, limited to the first 150 founding members, that provides access to core VIP features for as long as StaffExchangeHub continues operating. Standalone services may be offered separately.</p></details>
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
