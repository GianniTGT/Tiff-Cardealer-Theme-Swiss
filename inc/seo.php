<?php
/**
 * SEO — what a search engine and a social network read.
 *
 * Written 7 September 2026, the day after the domain moved to this server. Measured
 * first rather than assumed: the site had a real <title> per page, a canonical on
 * singular views, one <h1> and alt text on 92% of images — and then nothing at all.
 * No meta description anywhere, zero og: tags, zero structured data.
 *
 * ── Why no plugin ──────────────────────────────────────────────────────────
 * Yoast or Rank Math would add a second place where somebody has to type the year,
 * make, model and price of a car this theme already knows. Every value below is
 * derived from `das_info()` (the dealer profile) or `das_get_car()` (the vehicle),
 * so a corrected price corrects the search snippet with it and the two cannot drift.
 * That is the same split `das_feature_groups()` and `das_filter_options()` already use.
 *
 * ── The dealer is not written into this file ───────────────────────────────
 * Name, street, phones and hours come from the profile, so the next dealership
 * running this theme gets their own. Nothing here is typed (CLAUDE.md §5).
 *
 * ── The privacy boundary is untouched ──────────────────────────────────────
 * Everything emitted is already on the page a customer is reading: the asking price,
 * the VIN, the mileage. No cost, no profit, no margin — those never leave the office
 * machine and this file cannot reach them (§6).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** A value from the dealer profile, or '' — never a placeholder. */
function das_seo_info( $key ) {
	return function_exists( 'das_info' ) ? trim( (string) das_info( $key ) ) : '';
}

/**
 * Cut to a whole word. 155 is what Google shows before it truncates; a description
 * cut mid-word looks like a fault rather than a summary.
 */
function das_seo_clamp( $s, $max = 155 ) {
	$s = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $s ) ) );
	if ( $s === '' || mb_strlen( $s ) <= $max ) { return $s; }
	$cut = mb_substr( $s, 0, $max );
	$sp  = mb_strrpos( $cut, ' ' );
	return rtrim( $sp ? mb_substr( $cut, 0, $sp ) : $cut, " ,.;:-" ) . '…';
}

/** "Anchorage, AK" — whichever halves exist. */
function das_seo_place() {
	$p = array_filter( array( das_seo_info( 'city' ), das_seo_info( 'state' ) ) );
	return implode( ', ', $p );
}

/**
 * The sentence under the title in a search result.
 *
 * Deliberately carries no vehicle count. The count was taken off the site on
 * 19 August because a small lot demotivates rather than sells (§11), and a search
 * snippet is the most public place it could reappear.
 */
function das_seo_description() {
	$name  = das_seo_info( 'name' );
	$place = das_seo_place();
	$phone = das_seo_info( 'phone1' );

	if ( is_singular( 'car' ) && function_exists( 'das_get_car' ) ) {
		$c    = das_get_car( get_the_ID() );
		$bits = array_filter( array(
			$c['miles_txt'] ?? '',
			$c['fuel_txt']  ?? '',
			$c['trans_txt'] ?? '',
			$c['body_txt']  ?? '',
		) );
		$title = html_entity_decode( wp_strip_all_tags( $c['title'] ?? get_the_title() ), ENT_QUOTES );
		$s  = $title;
		$s .= $place ? ' for sale in ' . $place : ' for sale';
		if ( $bits )  { $s .= ' — ' . implode( ', ', $bits ); }
		if ( ! empty( $c['sold'] ) )            { $s .= '. Sold'; }
		elseif ( ! empty( $c['price_txt'] ) )   { $s .= '. ' . $c['price_txt']; }
		if ( $name )  { $s .= '. ' . $name; }
		if ( $phone ) { $s .= ' ' . $phone; }
		return das_seo_clamp( $s . '.' );
	}

	if ( is_post_type_archive( 'car' ) || is_tax( 'carproducer' ) ) {
		$s = 'Browse used cars, trucks and SUVs for sale';
		if ( $name )  { $s .= ' at ' . $name; }
		if ( $place ) { $s .= ' in ' . $place; }
		$s .= '. Financing available';
		if ( $phone ) { $s .= ' — call ' . $phone; }
		return das_seo_clamp( $s . '.' );
	}

	if ( is_front_page() ) {
		$s = ( $name ? $name . ' — ' : '' ) . 'used cars, trucks and SUVs';
		if ( $place ) { $s .= ' in ' . $place; }
		$s .= '. Financing available';
		if ( $phone ) { $s .= ' — call ' . $phone; }
		return das_seo_clamp( $s . '.' );
	}

	// An ordinary page says what it says. Its own words beat anything invented here.
	if ( is_singular() ) {
		$p = get_post();
		$x = $p ? ( $p->post_excerpt ?: $p->post_content ) : '';
		$x = das_seo_clamp( strip_shortcodes( (string) $x ) );
		if ( $x !== '' ) { return $x; }
	}

	return das_seo_clamp( get_option( 'blogdescription' ) );
}

/**
 * The picture a shared link shows.
 *
 * On a vehicle it is that vehicle's own photograph — the watermarked copy the site
 * already serves, which is right here: a link pasted into Facebook or Messenger
 * carrying the dealership's mark is the point. §21's rule forbids a stamped image in
 * a **Google Merchant Center feed**, which this is not and which does not exist yet.
 */
function das_seo_image() {
	if ( is_singular( 'car' ) && function_exists( 'das_get_car' ) ) {
		$c = das_get_car( get_the_ID() );
		if ( ! empty( $c['thumb_big'] ) ) { return $c['thumb_big']; }
	}
	if ( is_singular() && has_post_thumbnail() ) {
		$u = get_the_post_thumbnail_url( null, 'large' );
		if ( $u ) { return $u; }
	}
	// Elsewhere: a real car off the lot, rather than nothing. Omitted entirely when the
	// lot is empty — a broken og:image is worse than none.
	if ( function_exists( 'das_get_cars' ) && function_exists( 'das_get_car' ) ) {
		$cars = das_get_cars( array( 'limit' => 1 ) );
		if ( $cars ) {
			$first = is_array( $cars ) ? reset( $cars ) : null;
			if ( is_array( $first ) && ! empty( $first['thumb_big'] ) ) { return $first['thumb_big']; }
			if ( is_object( $first ) && ! empty( $first->ID ) ) {
				$c = das_get_car( $first->ID );
				if ( ! empty( $c['thumb_big'] ) ) { return $c['thumb_big']; }
			}
		}
	}
	return '';
}

/**
 * "Mon-Sat 10:30 AM - 7:00 PM" → "Mo-Sa 10:30-19:00".
 *
 * Returns '' for anything it does not recognise. An unparsed opening time is better
 * omitted than guessed at: hours are the one field a customer acts on by driving over.
 */
function das_seo_hours() {
	$h = das_seo_info( 'hours' );
	if ( $h === '' ) { return ''; }
	$d = array( 'mon'=>'Mo','tue'=>'Tu','wed'=>'We','thu'=>'Th','fri'=>'Fr','sat'=>'Sa','sun'=>'Su' );
	$re = '/^([a-z]{3})[a-z]*\s*-\s*([a-z]{3})[a-z]*\s+(\d{1,2}):(\d{2})\s*(am|pm)\s*-\s*(\d{1,2}):(\d{2})\s*(am|pm)$/i';
	if ( ! preg_match( $re, trim( $h ), $m ) ) { return ''; }
	$a = strtolower( $m[1] ); $b = strtolower( $m[2] );
	if ( ! isset( $d[ $a ], $d[ $b ] ) ) { return ''; }
	$to24 = function ( $hh, $mm, $ap ) {
		$hh = (int) $hh;
		if ( strtolower( $ap ) === 'pm' && $hh < 12 ) { $hh += 12; }
		if ( strtolower( $ap ) === 'am' && $hh === 12 ) { $hh = 0; }
		return sprintf( '%02d:%s', $hh, $mm );
	};
	return $d[ $a ] . '-' . $d[ $b ] . ' ' . $to24( $m[3], $m[4], $m[5] ) . '-' . $to24( $m[6], $m[7], $m[8] );
}

/** The dealership itself, with a stable @id so a vehicle can name its seller. */
function das_seo_dealer_node() {
	$node = array(
		'@type' => 'AutoDealer',
		'@id'   => home_url( '/#dealer' ),
		'name'  => das_seo_info( 'name' ) ?: get_option( 'blogname' ),
		'url'   => home_url( '/' ),
	);
	$phones = array_values( array_filter( array( das_seo_info( 'phone1' ), das_seo_info( 'phone2' ) ) ) );
	if ( $phones ) { $node['telephone'] = $phones[0]; }
	if ( count( $phones ) > 1 ) { $node['contactPoint'] = array( array(
		'@type' => 'ContactPoint', 'telephone' => $phones[1], 'contactType' => 'sales',
	) ); }
	$email = das_seo_info( 'email' );
	if ( $email ) { $node['email'] = $email; }

	$addr = array_filter( array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => das_seo_info( 'street' ),
		'addressLocality' => das_seo_info( 'city' ),
		'addressRegion'   => das_seo_info( 'state' ),
		'postalCode'      => das_seo_info( 'zip' ),
		'addressCountry'  => 'US',
	) );
	if ( count( $addr ) > 2 ) { $node['address'] = $addr; }

	$hours = das_seo_hours();
	if ( $hours ) { $node['openingHours'] = $hours; }
	$founded = das_seo_info( 'founded' );
	if ( preg_match( '/^\d{4}$/', $founded ) ) { $node['foundingDate'] = $founded; }
	$img = das_seo_image();
	if ( $img ) { $node['image'] = $img; }

	return $node;
}

/** One vehicle, with its offer. Only fields that actually carry a value. */
function das_seo_vehicle_node( $post_id ) {
	if ( ! function_exists( 'das_get_car' ) ) { return null; }
	$c = das_get_car( $post_id );
	if ( ! $c ) { return null; }

	$node = array_filter( array(
		'@type'       => 'Vehicle',
		'name'        => html_entity_decode( wp_strip_all_tags( (string) ( $c['title'] ?? '' ) ), ENT_QUOTES ),
		'url'         => $c['url'] ?? get_permalink( $post_id ),
		'vehicleIdentificationNumber' => $c['vin'] ?? '',
		'bodyType'          => $c['body_txt'] ?? '',
		'fuelType'          => $c['fuel_txt'] ?? '',
		'vehicleTransmission' => $c['trans_txt'] ?? '',
		'color'             => $c['ext_color'] ?? '',
		'vehicleInteriorColor' => $c['int_color'] ?? '',
	) );

	if ( ! empty( $c['make'] ) )  { $node['brand'] = array( '@type' => 'Brand', 'name' => $c['make'] ); }
	if ( ! empty( $c['model'] ) ) { $node['model'] = $c['model']; }
	if ( preg_match( '/^\d{4}$/', (string) ( $c['year'] ?? '' ) ) ) {
		$node['modelDate'] = (string) $c['year'];
	}
	if ( ! empty( $c['mileage'] ) && (int) $c['mileage'] > 0 ) {
		$node['mileageFromOdometer'] = array(
			'@type' => 'QuantitativeValue', 'value' => (int) $c['mileage'], 'unitCode' => 'SMI',
		);
	}
	if ( ! empty( $c['doors'] ) && (int) $c['doors'] > 0 ) { $node['numberOfDoors'] = (int) $c['doors']; }
	if ( ! empty( $c['thumb_big'] ) ) { $node['image'] = $c['thumb_big']; }

	// An Offer with no price is not an offer. A car with none says "call for price"
	// on the page, and inventing a figure for a machine to read would be a lie.
	if ( ! empty( $c['price'] ) && (float) $c['price'] > 0 ) {
		$node['offers'] = array(
			'@type'         => 'Offer',
			'price'         => (string) ( 0 + $c['price'] ),
			'priceCurrency' => 'USD',
			'availability'  => empty( $c['sold'] )
				? 'https://schema.org/InStock'
				: 'https://schema.org/SoldOut',
			'url'           => $node['url'],
			'itemCondition' => 'https://schema.org/UsedCondition',
			'seller'        => array( '@id' => home_url( '/#dealer' ) ),
		);
	}
	return $node;
}

/**
 * The trail a search result prints instead of a raw address.
 *
 * A result reading `Home > Vehicles > 2019 Toyota Tacoma` says where the page sits;
 * the same result reading `downtownautosale.com/car/2019-toyota-tacoma-2/` says nothing
 * and spends the same line saying it.
 *
 * Built from what WordPress already knows -- the archive link and the page's own title --
 * so it cannot come to disagree with the menu, and omitted entirely on the front page,
 * where a one-item trail is a trail that says nothing.
 */
function das_seo_breadcrumb_node() {
	if ( is_front_page() ) { return null; }

	$items = array( array( 'Home', home_url( '/' ) ) );

	if ( is_singular( 'car' ) ) {
		$archive = get_post_type_archive_link( 'car' );
		if ( $archive ) { $items[] = array( 'Vehicles', $archive ); }
		$items[] = array( html_entity_decode( wp_strip_all_tags( get_the_title() ), ENT_QUOTES ), get_permalink() );
	} elseif ( is_post_type_archive( 'car' ) ) {
		$items[] = array( 'Vehicles', get_post_type_archive_link( 'car' ) );
	} elseif ( is_singular() ) {
		$items[] = array( html_entity_decode( wp_strip_all_tags( get_the_title() ), ENT_QUOTES ), get_permalink() );
	} else {
		return null;
	}

	if ( count( $items ) < 2 ) { return null; }

	$list = array();
	foreach ( $items as $i => $it ) {
		$list[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $it[0],
			'item'     => $it[1],
		);
	}

	return array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
}
/**
 * Which address a filtered inventory view belongs to.
 *
 * The inventory takes eight parameters, so one page multiplies into hundreds of
 * near-identical addresses -- each of which was declaring itself canonical, which is
 * how a lot of twelve vehicles spends a search engine's whole visit on one page shown
 * a hundred ways.
 *
 * Two of the eight name a kind of vehicle somebody actually searches for -- a make and
 * a body type -- and earn a page of their own. The rest are the same inventory sliced:
 * a search phrase, a sort order, a price ceiling. Those point back at the page they
 * came from.
 *
 * Sorted, so ?body=suv&make=Toyota and ?make=Toyota&body=suv are one address rather
 * than two pages with identical content.
 */
function das_seo_archive_url() {
	$keep = array();
	foreach ( array( 'make', 'body' ) as $k ) {
		$v = isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : '';
		if ( '' !== $v ) { $keep[ $k ] = $v; }
	}
	$base = get_post_type_archive_link( 'car' );
	if ( ! $base ) { $base = home_url( '/car/' ); }
	if ( ! $keep ) { return $base; }
	ksort( $keep );
	return add_query_arg( $keep, $base );
}

/**
 * True when this view is the inventory sliced rather than a page in its own right.
 *
 * Judged on the raw parameter, never on a cast. archive-car.php reads price_max and
 * year_min through (int), where an absent parameter and a real zero are the same
 * value -- the conversion that has cost this project four separate bugs.
 *
 * noindex,follow rather than plain noindex: the vehicles linked from a filtered view
 * are the same vehicles, and they still have to be found.
 */
function das_seo_archive_is_slice() {
	foreach ( array( 'q', 'fuel', 'trans', 'price_max', 'year_min', 'sort' ) as $k ) {
		if ( isset( $_GET[ $k ] ) && '' !== trim( (string) wp_unslash( $_GET[ $k ] ) ) ) {
			return true;
		}
	}
	return false;
}

/**
 * The font stylesheet is what holds up the first paint, and core preconnects only to
 * the host the font files come from. The stylesheet is requested first and from a
 * different host, so that is the connection worth opening early.
 *
 * Through the filter rather than an echo into the head: core prints its hints at
 * priority 2 and ours would land after them.
 */
add_filter( 'wp_resource_hints', function ( $hints, $relation ) {
	if ( 'preconnect' === $relation ) { $hints[] = 'https://fonts.googleapis.com'; }
	return $hints;
}, 10, 2 );
/**
 * Everything above, into <head>.
 *
 * Priority 5, ahead of inc/analytics.php at 10: the description and the card belong
 * near the top of the head where a scraper that stops early still finds them.
 */
function das_seo_head() {
	if ( is_admin() || is_feed() || is_robots() ) { return; }

	$desc  = das_seo_description();
	$img   = das_seo_image();
	$title = html_entity_decode( wp_strip_all_tags( wp_get_document_title() ), ENT_QUOTES );
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );

	echo "\n<!-- Tiff Cardealer SEO -->\n";
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}

	// The archive gets no canonical from core, and its eight filters multiply one page
	// into hundreds of addresses. das_seo_archive_url() decides which one a view belongs
	// to, and a mere slice of the inventory is told not to be indexed at all.
	if ( is_front_page() ) {
		echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
	} elseif ( is_post_type_archive( 'car' ) ) {
		echo '<link rel="canonical" href="' . esc_url( das_seo_archive_url() ) . '">' . "\n";
		if ( das_seo_archive_is_slice() ) {
			echo '<meta name="robots" content="noindex,follow">' . "\n";
		}
	}

	echo '<meta property="og:type" content="' . ( is_singular( 'car' ) ? 'product' : 'website' ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( das_seo_info( 'name' ) ?: get_option( 'blogname' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) { echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n"; }
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:locale" content="en_US">' . "\n";
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
		echo '<meta property="og:image:alt" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	} else {
		echo '<meta name="twitter:card" content="summary">' . "\n";
	}
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) { echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n"; }
	if ( $img )  { echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n"; }

	$graph = array( das_seo_dealer_node() );
	if ( is_singular( 'car' ) ) {
		$v = das_seo_vehicle_node( get_the_ID() );
		if ( $v ) { $graph[] = $v; }
	}
	$crumbs = das_seo_breadcrumb_node();
	if ( $crumbs ) { $graph[] = $crumbs; }
	$ld = array( '@context' => 'https://schema.org', '@graph' => $graph );
	echo '<script type="application/ld+json">'
		. wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. '</script>' . "\n";
	echo "<!-- /Tiff Cardealer SEO -->\n\n";
}
add_action( 'wp_head', 'das_seo_head', 5 );
/**
 * The sitemap served correct XML under a 404 header — and the move was not the cause.
 *
 * Measured 7 September 2026: /wp-sitemap.xml rendered a valid index, and robots.txt
 * advertised it, while every request came back 404. So search engines were being told
 * where the sitemap is and then told it does not exist.
 *
 * The cause is a property of being a dealership rather than a blog. The rewrite maps
 * /wp-sitemap.xml to index.php?sitemap=index, which sets no other query var — so WP
 * parses it as the blog home and runs the posts query. **This site has zero `post`
 * rows**: 12 vehicles, 6 pages, and no blog. An empty main query makes WP::handle_404()
 * set the 404 header, and WP_Sitemaps::render_sitemaps() then renders and exits without
 * ever setting 200. Core never notices because a stock install has posts.
 *
 * `pre_handle_404` is the documented way out — core's own comment in
 * wp-includes/sitemaps/class-wp-sitemaps.php names it. Returning true short-circuits
 * handle_404() for this one request and leaves the default 200 in place.
 *
 * It is deliberately narrow: only a request that already carries a sitemap query var,
 * and only when nothing else has already decided. A plugin that gets there first keeps
 * its answer.
 */
add_filter( 'pre_handle_404', function ( $bypass, $wp_query ) {
	if ( ! empty( $bypass ) ) { return $bypass; }
	if ( $wp_query->get( 'sitemap' ) || $wp_query->get( 'sitemap-stylesheet' ) ) {
		return true;
	}
	return $bypass;
}, 10, 2 );