<?php
/**
 * Front page.
 *
 * Deliberately short: hero, a trimmed inventory preview, about, values,
 * financing, trade band, visit. The team roster and the enquiry form live on
 * their own pages via the [das_team] and [das_lead_form] shortcodes.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$all      = das_get_cars( array( 'posts_per_page' => 200 ) );
$total    = count( $all );
$preview  = array_slice( $all, 0, 8 );
$featured = array_values( array_filter( $all, function ( $c ) { return $c['featured']; } ) );
if ( ! $featured ) { $featured = array_slice( $all, 0, 4 ); }
?>

<section class="hero">
	<div class="wrap hero-grid">
		<div class="hero-copy">
			<span class="kicker">Dependable. Alaskan. Straightforward.</span>
			<h1>Cars built for <em>Alaska roads.</em><br>Prices built for everyone.</h1>
			<div class="hero-meta">
				<div><b>Since <?php echo esc_html( das_info( 'founded' ) ); ?></b>Serving the Anchorage community</div>
				<div><b>Financing available</b>Estimate your payment below</div>
				<div><b>Walk-ins welcome</b>No appointment needed</div>
			</div>
		</div>

		<?php if ( $featured ) : ?>
		<div class="hero-right">
		<div class="showcase" id="showcase" aria-live="polite">
			<div class="sc-stage" id="scStage">
				<?php if ( $featured[0]['thumb_big'] ) : ?>
					<img id="scImg" src="<?php echo esc_url( $featured[0]['thumb_big'] ); ?>" alt="<?php echo esc_attr( $featured[0]['title'] ); ?>">
				<?php else : ?>
					<svg aria-hidden="true"><use href="#carIcon"/></svg>
				<?php endif; ?>
				<span class="badge">Featured</span>
			</div>
			<div id="scBody">
				<div class="sc-info">
					<h3 id="scName"><?php echo esc_html( $featured[0]['title'] ); ?></h3>
					<span class="sc-price" id="scPrice"><?php echo esc_html( $featured[0]['price_txt'] ); ?></span>
				</div>
				<div class="sc-chips" id="scChips"></div>
			</div>
			<div class="sc-foot">
				<div class="sc-dots" id="scDots"></div>
				<a class="sc-cta" href="<?php echo esc_url( das_inventory_url() ); ?>">View all &rarr;</a>
			</div>
		</div>
		<?php das_quick_search( $all ); ?>
		</div>
		<?php endif; ?>
	</div>
</section>
<hr class="roadline" aria-hidden="true">

<section id="inventory">
	<div class="wrap">
		<?php das_sec_head( 'Current inventory', 'On the lot right now', 'Every vehicle is inspected before it goes on sale. What you see is what you pay. Call to confirm availability.' ); ?>

		<div class="grid" id="carGrid">
			<?php
			if ( $preview ) {
				foreach ( $preview as $c ) { echo das_car_card( $c ); }
			} else {
				echo '<p class="grid-empty">No vehicles listed right now &mdash; call us, new inventory arrives weekly.</p>';
			}
			?>
		</div>

		<?php if ( $total > count( $preview ) ) : ?>
			<div class="grid-more">
				<a class="btn btn-quiet" href="<?php echo esc_url( das_inventory_url() ); ?>">Browse all inventory</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<section id="about" style="background:var(--white)">
	<div class="wrap">
		<?php /* Short on purpose. The story, the promise and the team are on the About Us
		   page; this is the taste of it and the link is how somebody gets the rest. Before
		   this the homepage carried the whole story while the page named About Us carried
		   only four staff cards — the wrong way round: whoever clicked About us after
		   reading the homepage learned nothing new. */ ?>
		<div class="about-lead rv">
			<div class="eyebrow">About us</div>
			<h2>Started from scratch.<br>Built with Anchorage.</h2>
			<p>We're a <strong>small Alaskan family business</strong> with one goal: helping our neighbors find the vehicle that fits &mdash; a family car, a first car, a work truck, or a weekend rig for the backcountry.</p>
		</div>
		<div class="stats rv">
			<div class="stat"><b><?php echo esc_html( das_info( 'founded' ) ); ?></b><span>Established</span></div>
			<div class="stat"><b>100%</b><span>Inspected before sale</span></div>
			<div class="stat"><b>6</b><span>Days a week</span></div>
		</div>
		<p class="sec-more"><a class="btn btn-quiet" href="<?php echo esc_url( das_about_url() ); ?>">More about us &rarr;</a></p>
	</div>
</section>

<section class="dark" id="why">
	<div class="wrap">
		<?php das_sec_head( 'Why Downtown Auto Sales', 'An Alaskan family business, not a sales floor' ); ?>
		<div class="vals">
			<div class="val rv"><div class="ico">&#129309;</div><h3>Honest by default</h3><p>Straight answers about every vehicle's history and condition &mdash; because in a town like ours, reputation is everything.</p></div>
			<div class="val rv"><div class="ico">&#10052;&#65039;</div><h3>Alaska-ready picks</h3><p>We stock what works up here: AWD Subarus, 4x4 Toyotas, dependable trucks. Chosen for our roads and our winters.</p></div>
			<div class="val rv"><div class="ico">&#127968;</div><h3>One stop, no pressure</h3><p>Tell us what you need and we'll help you find it. Warm welcome, zero pressure, coffee's on us.</p></div>
		</div>
	</div>
</section>

<?php das_reviews_section(); ?>

<section id="financing">
	<div class="wrap">
		<?php das_sec_head( 'Financing', 'Estimate your monthly payment', 'An estimate only, not a credit offer. Talk to us and we will walk you through the real numbers with no obligation.' ); ?>
		<?php das_finance_estimator(); ?>
		<p class="sec-more"><a class="btn btn-quiet" href="<?php echo esc_url( das_financing_url() ); ?>">How financing works &rarr;</a></p>
	</div>
</section>

<section class="band band-navy">
	<div class="wrap">
		<div>
			<h2>Got a <em>trade-in?</em></h2>
			<p>Bring it by and we'll appraise it while you test drive. Fair value, no haggling theatre.</p>
		</div>
		<a class="btn btn-light" href="<?php echo esc_url( das_contact_url() ); ?>">Get an appraisal</a>
	</div>
</section>

<section id="visit">
	<div class="wrap">
		<?php das_sec_head( 'Visit us', 'Come see the lot', 'We are on Ingra Street in downtown Anchorage. Walk-ins welcome, no appointment needed.' ); ?>
		<div class="visit-grid">
			<div class="info-card rv" id="contact">
				<div class="info-row">
					<div class="info-ico">&#128205;</div>
					<div>
						<b>Address</b>
						<div class="sub"><?php echo esc_html( das_info( 'addr' ) ); ?></div>
					</div>
				</div>
				<div class="info-row">
					<div class="info-ico">&#128222;</div>
					<div>
						<b>Phone</b>
						<div class="sub">
							<a href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><?php echo esc_html( das_info( 'phone1' ) ); ?></a> &middot;
							<a href="tel:<?php echo esc_attr( das_info( 'tel2' ) ); ?>"><?php echo esc_html( das_info( 'phone2' ) ); ?></a>
						</div>
					</div>
				</div>
				<div class="info-row">
					<div class="info-ico">&#9993;</div>
					<div>
						<b>Email</b>
						<div class="sub">
							<a href="mailto:<?php echo esc_attr( das_info( 'email' ) ); ?>"><?php echo esc_html( das_info( 'email' ) ); ?></a>
							<?php if ( das_info( 'email2' ) ) : ?><br>
							<a href="mailto:<?php echo esc_attr( das_info( 'email2' ) ); ?>"><?php echo esc_html( das_info( 'email2' ) ); ?></a><?php endif; ?>
						</div>
					</div>
				</div>
				<div class="info-row">
					<div class="info-ico">&#128337;</div>
					<div style="flex:1">
						<b>Hours</b>
						<?php das_hours_table(); ?>
					</div>
				</div>
			</div>
			<div class="map-shell rv">
				<iframe title="Map to Downtown Auto Sales" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=<?php echo rawurlencode( das_info( 'addr' ) ); ?>&output=embed"></iframe>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>