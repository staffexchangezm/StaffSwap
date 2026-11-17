<?php get_header(); if ( staffswap_has_builder_content() ) : while ( have_posts() ) : the_post(); the_content(); endwhile; get_footer(); return; endif; ?>
<section class="about-page">
	<header class="about-page__hero">
		<div>
			<p class="eyebrow"><?php echo esc_html__( 'ABOUT US', 'staffswap' ); ?></p>
			<h1><?php echo esc_html__( 'Connecting Professionals. Making Workplace Mobility Easier.', 'staffswap' ); ?></h1>
			<p><?php echo esc_html__( 'StaffExchangeHub is a Zambian workplace mobility platform designed to help professionals discover and connect with potential workplace exchange partners across districts and provinces.', 'staffswap' ); ?></p>
			<div class="about-page__actions">
				<a class="button button--primary" href="<?php echo esc_url( home_url( '/swaps/' ) ); ?>"><?php echo esc_html__( 'Find Swaps', 'staffswap' ); ?></a>
				<?php if ( ! is_user_logged_in() ) : ?><a class="button button--outline" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php echo esc_html__( 'Create Free Account', 'staffswap' ); ?></a><?php endif; ?>
			</div>
		</div>
		<div class="about-page__route" aria-hidden="true"><span><span class="dashicons dashicons-location"></span><strong>Lusaka</strong></span><b class="dashicons dashicons-randomize"></b><span><span class="dashicons dashicons-location"></span><strong>Ndola</strong></span></div>
	</header>
	<section class="about-page__section">
		<p class="eyebrow"><?php echo esc_html__( 'WHY WE EXIST', 'staffswap' ); ?></p>
		<h2><?php echo esc_html__( 'Make workplace exchange opportunities easier to discover.', 'staffswap' ); ?></h2>
		<p>Finding a professional who wants to exchange workplaces can be challenging when opportunities are scattered across social media groups and personal networks. StaffExchangeHub brings exchange requests together so professionals can explore potential matches by profession, current location and preferred destination.</p>
	</section>
	<section class="about-page__features">
		<article><span class="dashicons dashicons-id-alt" aria-hidden="true"></span><h3><?php echo esc_html__( 'Create a profile', 'staffswap' ); ?></h3><p>Share your profession and workplace exchange preferences.</p></article>
		<article><span class="dashicons dashicons-search" aria-hidden="true"></span><h3><?php echo esc_html__( 'Explore listings', 'staffswap' ); ?></h3><p>Review available requests and identify compatible routes.</p></article>
		<article><span class="dashicons dashicons-format-chat" aria-hidden="true"></span><h3><?php echo esc_html__( 'Connect securely', 'staffswap' ); ?></h3><p>Discuss a possible exchange with another professional.</p></article>
	</section>
	<section class="about-page__commitment">
		<div><p class="eyebrow"><?php echo esc_html__( 'OUR PURPOSE', 'staffswap' ); ?></p><h2><?php echo esc_html__( 'A more organised way to explore workplace mobility.', 'staffswap' ); ?></h2><p>Our purpose is to make workplace mobility more accessible and transparent by helping professionals discover potential exchange partners across Zambia.</p></div>
		<div><p class="eyebrow"><?php echo esc_html__( 'OUR COMMITMENT', 'staffswap' ); ?></p><h2><?php echo esc_html__( 'Connections, not transfer approvals.', 'staffswap' ); ?></h2><p>We support the process of finding potential exchange partners. Every workplace transfer remains subject to the policies, requirements and approval procedures of the relevant employers or authorities.</p></div>
	</section>
	<section class="about-page__who">
		<div><p class="eyebrow"><?php echo esc_html__( 'WHO WE ARE', 'staffswap' ); ?></p><h2><?php echo esc_html__( 'Built in Zambia for professionals exploring their next move.', 'staffswap' ); ?></h2><p>StaffExchangeHub is a product of JM Exponential Trading, developed to provide practical digital solutions that support workplace mobility opportunities across Zambia.</p></div>
		<address><span class="dashicons dashicons-location" aria-hidden="true"></span><div><strong><?php echo esc_html__( 'Business address', 'staffswap' ); ?></strong><br>JM Exponential Trading<br>Plot 3620, Njoka Road<br>Olympia Park<br>Lusaka, Zambia</div></address>
	</section>
</section>
<?php get_footer(); ?>
