<?php
/** Presentation helpers shared by the templates. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Logo image: Customizer custom logo wins, then the bundled asset. */
function das_logo_img() {
	$id = (int) get_theme_mod( 'custom_logo' );
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, 'full' );
		if ( $src ) {
			printf( '<img class="mark-img" src="%s" alt="%s logo">', esc_url( $src ), esc_attr( das_info( 'name' ) ) );
			return;
		}
	}
	$file = get_template_directory() . '/assets/img/logo.png';
	if ( file_exists( $file ) ) {
		printf(
			'<img class="mark-img" src="%s" width="178" height="96" alt="%s logo">',
			esc_url( get_template_directory_uri() . '/assets/img/logo.png' ),
			esc_attr( das_info( 'name' ) )
		);
	}
}

/** Nav used until a menu is assigned to the "primary" location. */
function das_fallback_menu() {
	$items = array(
		home_url( '/' )           => 'Home',
		das_inventory_url()       => 'Inventory',
		home_url( '/#financing' ) => 'Financing',
		home_url( '/#visit' )     => 'Visit',
		das_contact_url()         => 'Contact',
	);
	echo '<ul>';
	foreach ( $items as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/** Archive link for the car CPT, falling back to the homepage anchor. */
function das_inventory_url() {
	$url = get_post_type_archive_link( 'car' );
	return $url ? $url : home_url( '/#inventory' );
}

/** The Contacts page, falling back to the homepage visit anchor. */
function das_contact_url() {
	$page = get_page_by_path( 'contacts' );
	return $page ? get_permalink( $page ) : home_url( '/#visit' );
}

/** Opening hours, keyed by PHP day-of-week (0 = Sunday). */
function das_hours() {
	return array(
		1 => array( 'Monday',    '10:30 AM - 7:00 PM' ),
		2 => array( 'Tuesday',   '10:30 AM - 7:00 PM' ),
		3 => array( 'Wednesday', '10:30 AM - 7:00 PM' ),
		4 => array( 'Thursday',  '10:30 AM - 7:00 PM' ),
		5 => array( 'Friday',    '10:30 AM - 7:00 PM' ),
		6 => array( 'Saturday',  '10:30 AM - 7:00 PM' ),
		0 => array( 'Sunday',    'Closed' ),
	);
}

function das_hours_table() {
	echo '<table class="hours" id="hoursTable"><tbody>';
	foreach ( das_hours() as $dow => $row ) {
		list( $label, $value ) = $row;
		$closed = ( 'Closed' === $value );
		printf(
			'<tr data-day="%d"><td>%s</td><td%s>%s</td></tr>',
			(int) $dow,
			esc_html( $label ),
			$closed ? ' class="closed"' : '',
			esc_html( $value )
		);
	}
	echo '</tbody></table>';
}

/** Gallery image IDs for a car: featured image first, then attached images. */
function das_car_gallery_ids( $post_id ) {
	$ids   = array();
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb ) { $ids[] = $thumb; }
	$attached = get_posts( array(
		'post_parent'    => $post_id,
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'posts_per_page' => 24,
		'orderby'        => 'menu_order ID',
		'order'          => 'ASC',
		'fields'         => 'ids',
	) );
	foreach ( $attached as $id ) {
		if ( ! in_array( (int) $id, $ids, true ) ) { $ids[] = (int) $id; }
	}
	return $ids;
}

function das_pagination() {
	$links = paginate_links( array( 'type' => 'plain', 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) );
	if ( ! $links ) { return; }
	echo '<nav class="das-pagination" aria-label="Inventory pages">' . wp_kses_post( $links ) . '</nav>';
}

/** Section heading block. */
function das_sec_head( $eyebrow, $title, $intro = '' ) {
	echo '<div class="sec-head rv">';
	printf( '<div class="eyebrow">%s</div>', esc_html( $eyebrow ) );
	printf( '<h2>%s</h2>', wp_kses( $title, array( 'em' => array(), 'br' => array() ) ) );
	if ( $intro ) { printf( '<p>%s</p>', esc_html( $intro ) ); }
	echo '</div>';
}
/**
 * Team roster. Photos are optional: drop tif.jpg / os.jpg / aj.jpg / gianni.jpg into
 * assets/img/team/ and they are picked up automatically; otherwise an initial
 * avatar is rendered so the layout never breaks.
 */
function das_team() {
	return array(
		array(
			'name'  => 'Tif',
			'file'  => 'tif',
			'role'  => 'Owner & Founder',
			'sub'   => 'Founder - Vehicle sourcing & inspection',
			'bio'   => "Tif started Downtown Auto Sales from scratch with one conviction: Alaskans deserve a dealer who treats them like neighbors. He personally sources and inspects every vehicle that lands on the lot - if he wouldn't put his own family in it, it doesn't get sold.",
		),
		array(
			'name'  => 'Os',
			'file'  => 'os',
			'role'  => 'Sales & Lot Manager',
			'sub'   => 'Next generation - Customer service & test drives',
			'bio'   => "Os grew up around this business and represents its next generation. He's the friendly face you'll likely meet first on the lot - handling test drives, walking you through each vehicle, and making sure every car is spotless and ready.",
		),
		array(
			'name'  => 'AJ',
			'file'  => 'aj',
			'role'  => 'Salesman',
			'sub'   => 'Sales - Vehicle walkarounds & test drives',
			'bio'   => "AJ works the lot alongside the rest of the team, showing vehicles, answering questions and setting up test drives. Ask for him by name when you stop by.",
		),
		array(
			'name'  => 'Gianni',
			'file'  => 'gianni',
			'role'  => 'Sales - IT & Marketing',
			'sub'   => 'Technology - Online sales & marketing',
			'bio'   => "Gianni keeps Downtown Auto Sales running in the digital world - the website, the listings, the online inquiries. Whether you reach us by phone, email or online, you get a fast, clear answer.",
		),
	);
}

function das_team_section( $with_heading = true ) {
	$dir = get_template_directory() . '/assets/img/team/';
	$uri = get_template_directory_uri() . '/assets/img/team/';
	if ( $with_heading ) :
	?>
	<div class="team-head rv">
		<div class="eyebrow">Meet the team</div>
		<h2>The family behind the lot</h2>
		<p style="margin-top:12px;color:var(--steel)">Four of us, one promise: you'll always deal with a person who cares &mdash; never a script.</p>
	</div>
	<?php endif; ?>
	<div class="team-grid">
		<?php foreach ( das_team() as $m ) : ?>
			<div class="tm rv">
				<div class="tm-img">
					<?php
					$photo = '';
					foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
						if ( file_exists( $dir . $m['file'] . '.' . $ext ) ) {
							$photo = $uri . $m['file'] . '.' . $ext;
							break;
						}
					}
					if ( $photo ) {
						printf( '<img src="%s" alt="%s, %s at %s" loading="lazy">', esc_url( $photo ), esc_attr( $m['name'] ), esc_attr( strtolower( $m['role'] ) ), esc_attr( das_info( 'name' ) ) );
					} else {
						printf(
							'<div style="width:100%%;height:100%%;display:grid;place-items:center;background:linear-gradient(135deg,var(--night-3),var(--night));color:var(--aurora);font-family:var(--display);font-weight:800;font-size:72px">%s</div>',
							esc_html( mb_substr( $m['name'], 0, 1 ) )
						);
					}
					?>
					<span class="tm-role"><?php echo esc_html( $m['role'] ); ?></span>
				</div>
				<div class="tm-body">
					<h3><?php echo esc_html( $m['name'] ); ?></h3>
					<div class="tm-sub"><?php echo esc_html( $m['sub'] ); ?></div>
					<p><?php echo esc_html( $m['bio'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
/** Legacy pages can carry a _das_redirect_to meta so old URLs keep working. */
add_action( 'template_redirect', function () {
	if ( ! is_page() ) { return; }
	$target = get_post_meta( get_queried_object_id(), '_das_redirect_to', true );
	if ( $target && $target !== home_url( add_query_arg( array() ) ) ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
} );
/* ---------------------------------------------------------------------------
 * Shortcodes - the team roster and the enquiry form now live on their own
 * pages instead of the homepage.
 *
 *   [das_team]                 optional: heading="no"
 *   [das_lead_form]            optional: title, intro, source, button, car_select
 * ------------------------------------------------------------------------- */

add_shortcode( 'das_team', function ( $atts ) {
	$atts = shortcode_atts( array( 'heading' => 'yes' ), $atts, 'das_team' );
	ob_start();
	das_team_section( 'no' !== strtolower( $atts['heading'] ) );
	return ob_get_clean();
} );

add_shortcode( 'das_lead_form', function ( $atts ) {
	$atts = shortcode_atts( array(
		'title'      => 'Send us a message',
		'intro'      => 'Tell us what you are looking for and we will get back to you the same business day.',
		'source'     => 'page',
		'button'     => 'Send message',
		'car_select' => 'yes',
		'type'       => 'inquiry',
		'financing'  => 'no',
	), $atts, 'das_lead_form' );

	ob_start();
	das_lead_form( array(
		'id'              => 'contactform',
		'title'           => $atts['title'],
		'intro'           => $atts['intro'],
		'source'          => sanitize_key( $atts['source'] ),
		'button'          => $atts['button'],
		'show_car_select' => 'no' !== strtolower( $atts['car_select'] ),
		'type'            => sanitize_key( $atts['type'] ),
		'financing'       => 'no' !== strtolower( $atts['financing'] ),
	) );
	return ob_get_clean();
} );

/**
 * Quick search for the homepage hero.
 *
 * A GET form aimed straight at the inventory archive, so it reuses that page's
 * parameters (q, make, price_max) and its filtering rules rather than growing a
 * second set - every result is a link somebody can send. The dropdowns come from
 * das_filter_options(), built from the vehicles actually on the lot: a make with
 * no cars behind it never appears. Renders nothing when the lot is empty,
 * because a search over nothing is a promise the results page cannot keep.
 */
function das_quick_search( $cars ) {
	if ( ! $cars ) { return; }
	$options = das_filter_options( $cars );
	?>
	<form class="hero-search" method="get" action="<?php echo esc_url( das_inventory_url() ); ?>" role="search">
		<label class="hs-q">
			<span class="screen-reader-text">Search the inventory</span>
			<input type="search" name="q" placeholder="Search make, model or year">
		</label>
		<?php if ( ! empty( $options['make'] ) ) : ?>
			<label>
				<span class="screen-reader-text">Make</span>
				<select name="make">
					<option value="">Any make</option>
					<?php foreach ( $options['make'] as $name => $n ) : ?>
						<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?> (<?php echo (int) $n; ?>)</option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endif; ?>
		<?php if ( ! empty( $options['prices'] ) ) : ?>
			<label>
				<span class="screen-reader-text">Maximum price</span>
				<select name="price_max">
					<option value="">Any price</option>
					<?php foreach ( $options['prices'] as $p ) : ?>
						<option value="<?php echo (int) $p; ?>">Under $<?php echo esc_html( number_format_i18n( $p ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endif; ?>
		<button class="btn btn-brand" type="submit">Search</button>
	</form>
	<?php
}

/**
 * Customer reviews. The summary is the dealership's real Google aggregate
 * (checked 19 August 2026); the quotes are filled verbatim from real reviews,
 * never invented. An empty quote list renders the badge and the link alone.
 */
function das_reviews_summary() {
	return array(
		'rating' => '4.6',
		'count'  => 92,
		'url'    => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( das_info( 'name' ) . ' ' . das_info( 'addr' ) ),
	);
}

function das_reviews() {
	// Verbatim from the dealership's Google reviews. Never edited, never invented:
	// a paraphrased review is the seller's words in a customer's mouth.
	return array(
		array( 'name' => 'Dawn Martin', 'stars' => 5, 'text' => 'This place is the only place to go get a used car in Anchorage. They\'re honest, polite, and super informative. AJ was our salesman and he knocked it out of the park. We love our Toyota RAV4. They had great Deals too!!! Thanks everyone for all of your kindness and thoroughness in getting our car.' ),
		array( 'name' => 'Juan Sr', 'stars' => 5, 'text' => 'I bought a 2017 focus from these guys and they made it SO easy! Great team of guys and they definitely make you feel like family. Highly recommend if you want reliable wheels!' ),
		array( 'name' => 'C Cooper', 'stars' => 5, 'text' => 'Fantastic customer service from start to finish. Everyone at Downtown Auto Sales was welcoming and professional. They answered all my questions, and made me feel like a valued customer. I absolutely love my new car and recommend Downtown Auto Sales to all my friends and family.' ),
	);
}

function das_reviews_section() {
	$s = das_reviews_summary();
	$quotes = das_reviews();
	?>
	<section id="reviews">
		<div class="wrap">
			<?php das_sec_head( 'Reviews', 'What our customers say' ); ?>
			<div class="rev-summary rv">
				<span class="rev-stars" aria-hidden="true"><span class="rev-stars-on" style="width:<?php echo esc_attr( round( (float) $s['rating'] / 5 * 100, 1 ) ); ?>%">&#9733;&#9733;&#9733;&#9733;&#9733;</span>&#9733;&#9733;&#9733;&#9733;&#9733;</span>
				<span class="rev-score"><b><?php echo esc_html( $s['rating'] ); ?></b>&thinsp;/&thinsp;5</span>
				<span class="rev-count"><?php echo esc_html( $s['count'] ); ?> Google reviews</span>
				<a class="btn btn-quiet" href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener">Read them on Google</a>
			</div>
			<?php if ( $quotes ) : ?>
			<?php /* The quotes fold away on a phone. Gianni, 24 Aug 2026: "rewies ne mobil
			   version futi ne sandwich perndryshe behet shume e gjate." Three cards stacked
			   is ~800px, and the summary above them — 4.6 from 92, with the Google link — is
			   the part that does the persuading. So the score stays and the reading is
			   offered. The count is derived, never typed: a number beside a list is a number
			   that goes stale the first time the list changes. */ ?>
			<details class="rev-more" open>
				<summary><span>Read <?php echo (int) count( $quotes ); ?> of them</span></summary>
				<div class="rev-grid">
					<?php foreach ( $quotes as $q ) : ?>
						<figure class="rev-card rv">
							<div class="rev-card-stars" aria-label="<?php echo esc_attr( $q['stars'] ); ?> out of 5"><?php echo str_repeat( '&#9733;', (int) $q['stars'] ); ?></div>
							<blockquote><?php echo esc_html( $q['text'] ); ?></blockquote>
							<figcaption>&mdash; <?php echo esc_html( $q['name'] ); ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</details>
			<?php /* Rendered open, then closed on a phone — that order is deliberate. With
			   scripting off, or for a crawler, nothing is hidden and the page is exactly what
			   it was. `currentScript` runs during parse, before the first paint, so there is
			   no flash of an open panel snapping shut. */ ?>
			<script>(function(){var d=document.currentScript.previousElementSibling;if(d&&d.tagName==='DETAILS'&&window.matchMedia('(max-width:940px)').matches){d.open=false;}})();</script>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/** The Financing page, falling back to the homepage section while no page exists. */
function das_financing_url() {
	$page = get_page_by_path( 'financing' );
	return $page ? get_permalink( $page ) : home_url( '/#financing' );
}

/**
 * The payment estimator, in one place so the homepage and the Financing page cannot
 * drift apart. The element IDs are what assets/js/site.js binds to, so they are
 * load-bearing: renaming one stops the calculator silently rather than loudly.
 */
function das_finance_estimator() {
	?>
<div class="fin-grid">
			<div class="fin-card rv">
				<label for="finCar">Vehicle</label>
				<select id="finCar"></select>
				<div class="fin-row">
					<div>
						<label for="finDown">Down payment ($)</label>
						<input type="number" id="finDown" value="2000" min="0" step="100">
					</div>
					<div>
						<label for="finTerm">Term</label>
						<select id="finTerm">
							<option value="36">36 months</option>
							<option value="48">48 months</option>
							<option value="60" selected>60 months</option>
							<option value="72">72 months</option>
						</select>
					</div>
				</div>
				<label for="finApr">APR (%)</label>
				<input type="number" id="finApr" value="8.5" min="0" max="30" step="0.1">
				<p class="fin-note">Estimates only. Actual terms depend on credit approval, taxes, title and dealer fees.</p>
			</div>
			<div class="fin-result rv">
				<div class="fr-label">Estimated payment</div>
				<div class="fr-amount"><span id="frMonthly">$0</span><small>/mo</small></div>
				<div class="fr-sub" id="frCarName">&mdash;</div>
				<hr>
				<div class="fin-break">
					<div><span>Vehicle price</span><b id="frPrice">&mdash;</b></div>
					<div><span>Down payment</span><b id="frDown">&mdash;</b></div>
					<div><span>Amount financed</span><b id="frLoan">&mdash;</b></div>
					<div><span>Total of payments</span><b id="frTotal">&mdash;</b></div>
				</div>
			</div>
		</div>
	<?php
}

add_shortcode( 'das_finance_estimator', function () {
	ob_start();
	das_finance_estimator();
	return ob_get_clean();
} );

/**
 * The promise card, as a shortcode.
 *
 * It was markup inside the About block on the front page. It moved to the About Us page,
 * and a shortcode is how it gets there without the words becoming theme code — the same
 * split the Financing page already uses (CLAUDE.md §11): a page's prose is content in
 * the database, the designed pieces are shortcodes.
 */
function das_promise_card() {
	ob_start();
	?>
	<div class="about-visual rv">
		<h3>Our promise to you</h3>
		<ul>
			<li><span class="tick">&#10003;</span><span><b>Service we&rsquo;d want ourselves.</b> We serve you the way we&rsquo;d like to be served.</span></li>
			<li><span class="tick">&#10003;</span><span><b>Friendly and reliable.</b> Efficient service in a warm, welcoming environment.</span></li>
			<li><span class="tick">&#10003;</span><span><b>Honesty and integrity.</b> With our customers and our employees, always.</span></li>
			<li><span class="tick">&#10003;</span><span><b>An Alaskan family business.</b> That&rsquo;s what we are, and that&rsquo;s how we treat you.</span></li>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'das_promise', 'das_promise_card' );

/** The About Us page, by slug, falling back to the home page rather than to nothing. */
function das_about_url() {
	$p = get_page_by_path( 'about-us' );
	return $p ? get_permalink( $p ) : home_url( '/' );
}
