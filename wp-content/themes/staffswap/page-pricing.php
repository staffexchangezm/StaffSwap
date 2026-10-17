<?php
get_header();
$staffswap_pricing_elementor_data = get_post_meta( get_the_ID(), '_elementor_data', true );
if ( ! empty( $staffswap_pricing_elementor_data ) || isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) :
	while ( have_posts() ) : the_post(); the_content(); endwhile;
	get_footer();
	return;
endif;
?>
<section class="page-hero">
    <p class="eyebrow"><?php echo esc_html__( 'SIMPLE, TRANSPARENT PRICING', 'staffswap' ); ?></p>
    <h1><?php echo esc_html__( 'Find Your Swap. Choose Your Plan.', 'staffswap' ); ?></h1>
    <p><?php echo esc_html__( 'Start for free and explore workplace exchange opportunities across Zambia. Upgrade for enhanced visibility, additional contact requests and membership benefits.', 'staffswap' ); ?></p>
</section>
<section class="pricing-grid">
    <article class="pricing-card">
        <p class="eyebrow">Basic</p>
        <h2>Free Forever</h2>
        <p class="pricing-price">K0 <small>/ free</small></p>
        <ul>
            <li>Create your profile</li>
            <li>Browse swap listings</li>
            <li>Post 1 active swap request</li>
            <li>3 interest requests monthly</li>
            <li>Access free resources</li>
        </ul>
        <a class="button button--outline" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">Create Free Account</a>
    </article>
    <article class="pricing-card pricing-card--featured">
        <span class="pricing-badge">BEST VALUE</span>
        <p class="eyebrow">VIP Gold</p>
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
    <article class="pricing-card">
        <p class="eyebrow">Founding VIP Lifetime</p>
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
</section>
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
<?php get_footer(); ?>
