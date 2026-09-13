<?php
/** Car data helpers — single source of truth for field names. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function das_car_meta_keys() {
	return array(
		'car_vin' => 'string', 'car_price' => 'string', 'car_price_plus' => 'string',
		'car_year' => 'string', 'car_mileage' => 'string', 'car_body' => 'string',
		'car_fuel_type' => 'string', 'car_transmission' => 'string',
		'car_engine_size' => 'string', 'car_engine_additional' => 'string',
		'car_exterior_color' => 'string', 'car_interrior_color' => 'string',
		'car_doors_count' => 'string', 'car_owner_number' => 'string',
		'car_is_featured' => 'string', 'car_is_sold' => 'string',
	);
}

function das_body_types() {
	return array( 'suv' => 'SUV', 'pickup_truck' => 'Pickup Truck', 'sedan' => 'Sedan', 'hatchback' => 'Hatchback', 'wagon' => 'Wagon', 'coupe' => 'Coupe', 'minivan' => 'Minivan' );
}
function das_fuel_types() {
	return array( 'petrol' => 'Petrol', 'gas' => 'Gas', 'diesel' => 'Diesel', 'hybrid' => 'Hybrid', 'electro' => 'Electric' );
}
function das_transmissions() { return array( 'automatic' => 'Automatic', 'manual' => 'Manual' ); }

function das_label( $value, $map ) {
	$value = (string) $value;
	return $map[ $value ] ?? ( $value === '' ? '' : ucwords( str_replace( '_', ' ', $value ) ) );
}

function das_get_car( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) { return null; }
	$id = $post->ID;
	$m = function ( $k ) use ( $id ) { return trim( (string) get_post_meta( $id, $k, true ) ); };
	$price   = (int) preg_replace( '/\D/', '', $m( 'car_price' ) );
	$mileage = (int) preg_replace( '/\D/', '', $m( 'car_mileage' ) );
	// Make and model come from the term hierarchy, not from term order. `fields => names`
	// returns them alphabetically, so an Audi A3 reported its make as "A3" — which is also
	// what any make filter would then group it under.
	$terms = wp_get_post_terms( $id, 'carproducer' );
	if ( is_wp_error( $terms ) ) { $terms = array(); }
	$make = '';
	$model = '';
	foreach ( $terms as $tm ) {
		if ( $tm->parent ) { if ( ! $model ) { $model = $tm->name; } }
		elseif ( ! $make ) { $make = $tm->name; }
	}
	// A vehicle tagged with a model but not its make still has one: the term's parent.
	if ( ! $make ) {
		foreach ( $terms as $tm ) {
			if ( ! $tm->parent ) { continue; }
			$parent = get_term( $tm->parent );
			if ( $parent && ! is_wp_error( $parent ) ) { $make = $parent->name; break; }
		}
	}
	$engine = trim( $m( 'car_engine_size' ) . ( $m( 'car_engine_size' ) ? 'L ' : '' ) . $m( 'car_engine_additional' ) );
	return array(
		'id' => $id,
		'title' => get_the_title( $id ),
		'url' => get_permalink( $id ),
		'price' => $price,
		'price_txt' => $price ? '$' . number_format_i18n( $price ) : 'Call for price',
		'plus' => $m( 'car_price_plus' ),
		'year' => $m( 'car_year' ),
		'vin' => $m( 'car_vin' ),
		'mileage' => $mileage,
		'miles_txt' => $mileage ? number_format_i18n( $mileage ) . ' mi' : '—',
		'body' => $m( 'car_body' ),
		'body_txt' => das_label( $m( 'car_body' ), das_body_types() ),
		'fuel' => $m( 'car_fuel_type' ),
		'fuel_txt' => das_label( $m( 'car_fuel_type' ), das_fuel_types() ),
		'trans_txt' => das_label( $m( 'car_transmission' ), das_transmissions() ),
		'engine' => $engine ?: '—',
		'ext_color' => $m( 'car_exterior_color' ),
		'int_color' => $m( 'car_interrior_color' ),
		'doors' => $m( 'car_doors_count' ),
		'owners' => $m( 'car_owner_number' ),
		'featured' => '1' === $m( 'car_is_featured' ),
		'sold' => '1' === $m( 'car_is_sold' ),
		'make' => $make,
		'model' => $model,
		'thumb' => das_car_photo( $id, 'thumb' ),
		'thumb_big' => das_car_photo( $id, 'full' ),
	);
}

function das_get_cars( $args = array() ) {
	$args = wp_parse_args( $args, array( 'posts_per_page' => 60, 'include_sold' => false, 'featured_only' => false ) );

	// NOTE: car_is_sold / car_is_featured are written by the tmm_theme_features
	// plugin and can be SQL NULL. Any meta_query comparison against NULL yields
	// NULL (never true), which silently excludes every row - so filter in PHP.
	$q = new WP_Query( array(
		'post_type'      => 'car',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		// No meta_key here on purpose. In WP_Query meta_key FILTERS as well as
		// sorts - a vehicle saved without a price would vanish from the site
		// entirely, with no warning. Sort in PHP instead.
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );

	$out = array();
	foreach ( $q->posts as $p ) {
		$c = das_get_car( $p );
		if ( ! $c ) { continue; }
		if ( ! $args['include_sold'] && $c['sold'] ) { continue; }
		if ( $args['featured_only'] && ! $c['featured'] ) { continue; }
		$out[] = $c;
	}

	// Highest price first; vehicles still awaiting a price sit at the end
	// rather than disappearing.
	usort( $out, function ( $a, $b ) {
		$pa = (float) $a['price'];
		$pb = (float) $b['price'];
		if ( $pa === $pb ) { return 0; }
		if ( 0.0 === $pa ) { return 1; }
		if ( 0.0 === $pb ) { return -1; }
		return $pb <=> $pa;
	} );

	if ( $args['posts_per_page'] > 0 ) { $out = array_slice( $out, 0, (int) $args['posts_per_page'] ); }
	return $out;
}
function das_cars_for_js() {
	$out = array();
	foreach ( das_get_cars() as $c ) {
		$out[] = array( 'id' => $c['id'], 'name' => $c['title'], 'price' => $c['price'], 'eng' => $c['engine'], 'fuel' => $c['fuel_txt'], 'mi' => $c['miles_txt'], 'body' => $c['body'], 'feat' => $c['featured'], 'img' => $c['thumb'], 'url' => $c['url'] );
	}
	return $out;
}

function das_car_count() { return count( das_get_cars( array( 'posts_per_page' => 200 ) ) ); }

function das_car_icon() { return '<svg aria-hidden="true"><use href="#carIcon"/></svg>'; }

function das_car_card( $c ) {
	$types = array_filter( array( $c['body'], $c['fuel'] ) );
	ob_start(); ?>
	<article class="card rv in" data-type="<?php echo esc_attr( implode( ' ', $types ) ); ?>">
		<a class="card-img" href="<?php echo esc_url( $c['url'] ); ?>">
			<?php if ( $c['thumb'] ) : ?>
				<img src="<?php echo esc_url( $c['thumb'] ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy" width="720" height="480">
			<?php else : echo das_car_icon(); endif; ?>
			<?php if ( $c['featured'] ) : ?><span class="badge">Featured</span><?php endif; ?>
		</a>
		<div class="card-body">
			<div class="card-top">
				<h3><a href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['title'] ); ?></a></h3>
				<span class="price"><?php echo esc_html( $c['price_txt'] ); ?></span>
			</div>
			<?php
			// The captions are gone and the icons carry them. "Engine: 4 Cylinder" needed
			// reading; a gearbox beside "Automatic" does not, and four labels repeated on
			// every card in a grid of twenty is twenty times the same four words.
			// A spec with no value drops out entirely rather than printing an em dash —
			// a blank line told a buyer nothing except that we do not know our own car.
			$card_specs = array(
				'mileage'      => $c['miles_txt'],
				'transmission' => $c['trans_txt'],
				'fuel'         => $c['fuel_txt'],
				'body'         => $c['body_txt'],
				'engine'       => $c['engine'],
			);
			?>
			<ul class="card-specs">
				<?php foreach ( $card_specs as $key => $value ) : ?>
					<?php if ( '' === trim( (string) $value ) || '&mdash;' === $value ) { continue; } ?>
					<li title="<?php echo esc_attr( $key ); ?>">
						<?php echo das_spec_icon( $key, $value ); ?><span><?php echo esc_html( $value ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<a class="btn btn-dark" href="<?php echo esc_url( $c['url'] ); ?>">View details</a>
		</div>
	</article>
	<?php
	return ob_get_clean();
}
/**
 * Vehicle photos.
 *
 * Legacy stock imported by the tmm_theme_features plugin does NOT use WordPress
 * attachments; files live in uploads/thememakers/cardealer/1/{post_id}/{size}/.
 * Sizes on disk: main 1130x732, thumb 460x290, single_thumb_widget 80x70.
 * New vehicles (e.g. pushed from DAS Manager) will use a normal featured image,
 * so we check attachments first and fall back to the legacy folders.
 *
 * @return array List of array( 'full' => url, 'thumb' => url ).
 */
function das_car_photos( $post_id ) {
	$post_id = (int) $post_id;
	$photos  = array();

	// 1. Modern path: featured image + attached images.
	$ids   = array();
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb ) { $ids[] = $thumb; }
	foreach ( get_posts( array(
		'post_parent'    => $post_id,
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'posts_per_page' => 30,
		'orderby'        => 'menu_order ID',
		'order'          => 'ASC',
		'fields'         => 'ids',
	) ) as $id ) {
		if ( ! in_array( (int) $id, $ids, true ) ) { $ids[] = (int) $id; }
	}
	foreach ( $ids as $id ) {
		$photos[] = array(
			'full'  => function_exists( 'das_watermark_url' ) ? das_watermark_url( $id, 'das_hero' ) : wp_get_attachment_image_url( $id, 'das_hero' ),
			'thumb' => wp_get_attachment_image_url( $id, 'thumbnail' ),
		);
	}
	if ( $photos ) { return $photos; }

	// 2. Legacy ThemeMakers folders.
	$up   = wp_upload_dir();
	$dir  = $up['basedir'] . '/thememakers/cardealer/1/' . $post_id . '/';
	$url  = $up['baseurl'] . '/thememakers/cardealer/1/' . $post_id . '/';
	if ( ! is_dir( $dir . 'main' ) ) { return $photos; }

	$files = array_values( array_diff( (array) scandir( $dir . 'main' ), array( '.', '..' ) ) );
	sort( $files );

	// Cover image first, if the meta names one.
	$cover = trim( (string) get_post_meta( $post_id, 'car_cover_image', true ) );
	if ( $cover && in_array( $cover, $files, true ) ) {
		$files = array_merge( array( $cover ), array_diff( $files, array( $cover ) ) );
	}

	$small = is_dir( $dir . 'thumb' ) ? 'thumb' : 'main';
	foreach ( $files as $file ) {
		$photos[] = array(
			'full'  => $url . 'main/' . rawurlencode( $file ),
			'thumb' => $url . $small . '/' . rawurlencode( $file ),
		);
	}
	return $photos;
}

/** First photo for a vehicle, or '' when there is none. */
function das_car_photo( $post_id, $which = 'thumb' ) {
	$photos = das_car_photos( $post_id );
	return $photos ? ( $photos[0][ $which ] ?? '' ) : '';
}
/**
 * What the inventory can actually be filtered by, right now.
 *
 * Built from the vehicles on the lot rather than from a fixed list, and a field with
 * fewer than two distinct values is left out entirely: a Make box holding one make, or a
 * Fuel box every car answers the same way, is a control that cannot narrow anything. With
 * fourteen vehicles that is the difference between a filter that helps and one that only
 * produces empty pages.
 */
function das_filter_options( $cars ) {
	$fields = array( 'make' => array(), 'fuel_txt' => array(), 'trans_txt' => array() );
	foreach ( $cars as $c ) {
		foreach ( $fields as $k => $_ ) {
			$v = isset( $c[ $k ] ) ? trim( (string) $c[ $k ] ) : '';
			if ( '' === $v || '—' === $v ) { continue; }
			$fields[ $k ][ $v ] = isset( $fields[ $k ][ $v ] ) ? $fields[ $k ][ $v ] + 1 : 1;
		}
	}
	foreach ( $fields as $k => $vals ) {
		if ( count( $vals ) < 2 ) { unset( $fields[ $k ] ); continue; }
		ksort( $fields[ $k ] );
	}

	// Price and year steps come from the inventory too, so "Under $10,000" never appears
	// on a lot whose cheapest vehicle is $12,500.
	$prices = array_filter( wp_list_pluck( $cars, 'price' ) );
	$years  = array_filter( array_map( 'intval', wp_list_pluck( $cars, 'year' ) ) );
	$steps  = array();
	if ( $prices ) {
		foreach ( array( 5000, 10000, 15000, 20000, 25000, 30000, 40000, 50000 ) as $p ) {
			if ( $p > min( $prices ) && $p < max( $prices ) ) { $steps[] = $p; }
		}
	}
	return array(
		'make'   => isset( $fields['make'] ) ? $fields['make'] : array(),
		'fuel'   => isset( $fields['fuel_txt'] ) ? $fields['fuel_txt'] : array(),
		'trans'  => isset( $fields['trans_txt'] ) ? $fields['trans_txt'] : array(),
		'prices' => $steps,
		'years'  => $years ? range( max( $years ), min( $years ), -1 ) : array(),
	);
}

/** The words a search box should match. Nobody types the body type; everybody types the make. */
function das_car_haystack( $c ) {
	return strtolower( implode( ' ', array(
		$c['title'], $c['make'], isset( $c['model'] ) ? $c['model'] : '',
		$c['year'], $c['body_txt'], $c['fuel_txt'], $c['ext_color'], $c['vin'],
	) ) );
}

/**
 * Filtering happens in PHP for the same reason das_get_cars() avoids meta_query: the
 * car_* meta can be SQL NULL, and any comparison against NULL drops the row without a
 * word. Every filter is a plain GET parameter, so the whole thing works with JavaScript
 * switched off and every result is a link somebody can send to a friend.
 */
function das_filter_cars( $cars, $args ) {
	$q = isset( $args['q'] ) ? strtolower( trim( (string) $args['q'] ) ) : '';
	$words = $q ? preg_split( '/\s+/', $q ) : array();

	$out = array();
	foreach ( $cars as $c ) {
		if ( ! empty( $args['body'] ) && $c['body'] !== $args['body'] ) { continue; }
		if ( ! empty( $args['make'] ) && $c['make'] !== $args['make'] ) { continue; }
		if ( ! empty( $args['fuel'] ) && $c['fuel_txt'] !== $args['fuel'] ) { continue; }
		if ( ! empty( $args['trans'] ) && $c['trans_txt'] !== $args['trans'] ) { continue; }
		// A vehicle with no price is never hidden by a price filter. "Call for price" is a
		// real listing, and dropping it would hide the most expensive cars on the lot.
		if ( ! empty( $args['price_max'] ) && $c['price'] && $c['price'] > (int) $args['price_max'] ) { continue; }
		if ( ! empty( $args['year_min'] ) && $c['year'] && (int) $c['year'] < (int) $args['year_min'] ) { continue; }
		if ( $words ) {
			$hay = das_car_haystack( $c );
			foreach ( $words as $w ) {
				if ( '' !== $w && false === strpos( $hay, $w ) ) { continue 2; }
			}
		}
		$out[] = $c;
	}
	return das_sort_cars( $out, isset( $args['sort'] ) ? $args['sort'] : '' );
}

/** Sorted in PHP, again because a meta_key in WP_Query filters as well as sorts. */
function das_sort_cars( $cars, $sort ) {
	$cmp = array(
		'price_asc'  => function ( $a, $b ) { return ( $a['price'] ?: PHP_INT_MAX ) <=> ( $b['price'] ?: PHP_INT_MAX ); },
		'price_desc' => function ( $a, $b ) { return $b['price'] <=> $a['price']; },
		'miles_asc'  => function ( $a, $b ) { return ( $a['mileage'] ?: PHP_INT_MAX ) <=> ( $b['mileage'] ?: PHP_INT_MAX ); },
		'year_desc'  => function ( $a, $b ) { return (int) $b['year'] <=> (int) $a['year']; },
	);
	if ( isset( $cmp[ $sort ] ) ) { usort( $cars, $cmp[ $sort ] ); }
	return $cars;
}

/** The sort choices, in one place so the template and the validation cannot disagree. */
function das_sort_options() {
	return array(
		''           => 'Newest first',
		'price_asc'  => 'Price: low to high',
		'price_desc' => 'Price: high to low',
		'miles_asc'  => 'Mileage: lowest first',
		'year_desc'  => 'Year: newest first',
	);
}


/**
 * A small line icon for one vehicle spec.
 *
 * Stroke only, a 24-unit box, `currentColor` — the same recipe the desktop app's icon set
 * uses (src/components/icons.jsx), so the two halves of the product look like one thing.
 * Inline rather than a sprite: there are twelve on one page and none anywhere else.
 *
 * An unknown key returns a neutral dot rather than nothing. A row with a hole where an
 * icon should be reads as broken; a dot reads as "no icon for this one".
 */
function das_spec_icon( $key, $value = '' ) {
	// Body type draws the body it names. A generic car beside "Pickup Truck" is a
	// picture that tells a buyer nothing — which is what Gianni called it. An
	// unrecognised body falls through to the generic car rather than to nothing.
	if ( 'body' === $key && '' !== trim( (string) $value ) ) {
		$shape = das_body_icon_key( $value );
		if ( $shape ) { $key = 'body_' . $shape; }
	}
	$paths = array(
		'year'         => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'make'         => '<path d="M4.4 10.2 6 6.4a2 2 0 0 1 1.9-1.2h8.2A2 2 0 0 1 18 6.4l1.6 3.8"/><path d="M3.6 10.2h16.8a1.4 1.4 0 0 1 1.4 1.4v5.2a1.4 1.4 0 0 1-1.4 1.4H3.6a1.4 1.4 0 0 1-1.4-1.4v-5.2a1.4 1.4 0 0 1 1.4-1.4Z"/><path d="M5.6 13.2h2.2M16.2 13.2h2.2M9.4 16h5.2"/>',
		'mileage'      => '<circle cx="12" cy="13" r="1.6"/><path d="m13.2 11.8 3.3-3.3"/><path d="M4 18a9 9 0 1 1 16 0"/>',
		'body'         => '<path d="M2.6 15.4h18.8"/><path d="M4.4 15.4 6 10.6a1.8 1.8 0 0 1 1.7-1.2h8.6a1.8 1.8 0 0 1 1.7 1.2l1.6 4.8"/><path d="M3.4 15.4h17.2a1 1 0 0 1 1 1v2.2h-2.2M5.4 18.6H2.4v-2.2"/><circle cx="7.2" cy="18.6" r="1.7"/><circle cx="16.8" cy="18.6" r="1.7"/>',
		'engine'       => '<path d="M4.4 11.4h2.2l2.2-2.2h4.6l2.2 2.2h2.2a1.6 1.6 0 0 1 1.6 1.6v3.6a1.6 1.6 0 0 1-1.6 1.6H6a1.6 1.6 0 0 1-1.6-1.6Z"/><path d="M9.2 9.2V6.4h5.4v2.8"/><path d="M2.6 12.6v3.4M21.4 12v4.4M11 6.4h2.2"/>',
		'fuel'         => '<path d="M4 20.2V5.4a2 2 0 0 1 2-2h4.6a2 2 0 0 1 2 2v14.8"/><path d="M2.6 20.2h11.4"/><rect x="6" y="6" width="4.6" height="3.4" rx="1"/><path d="M15.6 9.4h2a2 2 0 0 1 2 2v4.8a1.6 1.6 0 0 0 3.2 0V9.6L20.2 6.6"/>',
		'transmission' => '<circle cx="12" cy="4" r="2.4"/><path d="M12 6.4v3.4"/><path d="M6 9.8h12"/><path d="M6 9.8v6.4M12 9.8v6.4M18 9.8v6.4"/><path d="M6 16.2h12"/>',
		'drivetrain'   => '<path d="M6 7h12M6 17h12M8 7v10M16 7v10"/><circle cx="6" cy="7" r="1.6"/><circle cx="18" cy="7" r="1.6"/><circle cx="6" cy="17" r="1.6"/><circle cx="18" cy="17" r="1.6"/>',
		'ext_color'    => '<rect x="2.8" y="4.2" width="10.4" height="5.2" rx="1.3"/><path d="M13.2 6.8h4.4a1.6 1.6 0 0 1 1.6 1.6v2.2a1.6 1.6 0 0 1-1.6 1.6h-6.2a1.6 1.6 0 0 0-1.6 1.6v1"/><rect x="8.6" y="14.6" width="3.6" height="5.6" rx="1.5"/><path d="M2.8 6.8h10.4"/>',
		'int_color'    => '<path d="M9.2 3.6h3.6a1.4 1.4 0 0 1 1.4 1.4v1.4H7.8V5a1.4 1.4 0 0 1 1.4-1.4Z"/><path d="M8.4 8.2h5.2a1.6 1.6 0 0 1 1.6 1.6v4.6H6.8V9.8a1.6 1.6 0 0 1 1.6-1.6Z"/><path d="M5.6 14.4h11.2a1.8 1.8 0 0 1 1.8 1.8v1.2a1.8 1.8 0 0 1-1.8 1.8H7.4a1.8 1.8 0 0 1-1.8-1.8Z"/><path d="M7.6 19.2v1.4M16.4 19.2v1.4"/>',
		'body_pickup'  => '<path d="M2.6 15.6h18.8"/><path d="M2.6 15.6v-3.4h7l1.4-3.2a1.6 1.6 0 0 1 1.5-1h2.2a1.6 1.6 0 0 1 1.5 1l1.4 3.2h3.8v3.4"/><path d="M12.2 8v4.2"/><path d="M2.6 15.6h18.8v2.4h-2M5 18h-2.4v-2.4"/><circle cx="6.6" cy="18" r="1.7"/><circle cx="17" cy="18" r="1.7"/>',
		'body_suv'     => '<path d="M3.4 15.4 4.6 9.8A2 2 0 0 1 6.6 8.2h10.8a2 2 0 0 1 2 1.6l1.2 5.6"/><path d="M2.6 15.4h18.8a.9.9 0 0 1 .9.9v2.3h-2.3M5.2 18.6H2.6a.9.9 0 0 1-.9-.9v-2.3"/><path d="M6.2 12.6h11.6"/><circle cx="7" cy="18.6" r="1.7"/><circle cx="17" cy="18.6" r="1.7"/>',
		'body_sedan'   => '<path d="M2.6 15.6h18.8"/><path d="M3.8 15.6 5 12.4h14l1.2 3.2"/><path d="M6.4 12.4 8.2 9.4a1.6 1.6 0 0 1 1.4-.8h4.8a1.6 1.6 0 0 1 1.4.8l1.8 3"/><path d="M2.8 15.6h18.4v2.3h-2.2M5.2 17.9H2.8v-2.3"/><circle cx="7" cy="17.9" r="1.6"/><circle cx="17" cy="17.9" r="1.6"/>',
		'body_hatch'   => '<path d="M2.6 15.6h18.8"/><path d="M3.8 15.6 5 12.4h14.2l1 3.2"/><path d="M6.4 12.4 8.4 9.4a1.6 1.6 0 0 1 1.3-.7h3.6l4.4 3.7"/><path d="M2.8 15.6h18.4v2.3h-2.2M5.2 17.9H2.8v-2.3"/><circle cx="7" cy="17.9" r="1.6"/><circle cx="17" cy="17.9" r="1.6"/>',
		'doors'        => '<path d="M3.6 19.6V8.4a2 2 0 0 1 .9-1.7l5.2-3.3a2 2 0 0 1 1.1-.3h6a2 2 0 0 1 2 2v14.5Z"/><path d="M6.2 9.6 10 7.2h6v3.6H6.2Z"/><circle cx="14.6" cy="15" r="1.2"/>',
		'owners'       => '<circle cx="12" cy="8" r="3.2"/><path d="M5.5 20a6.5 6.5 0 0 1 13 0"/>',
		'vin'          => '<path d="M4 7v10M7 7v10M10.5 7v10M14 7v10M17.5 7v10M20 7v10"/>',
		'condition'    => '<path d="M12 3.2 19 6v5.2c0 4.2-2.8 7.4-7 9.6-4.2-2.2-7-5.4-7-9.6V6Z"/><path d="m9 12 2.2 2.2L15.4 10"/>',
		'price'        => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5v9M14.5 10a2.5 2.5 0 0 0-2.5-1.6c-1.4 0-2.5.8-2.5 2s1.1 1.7 2.5 2 2.5.8 2.5 2-1.1 2-2.5 2A2.5 2.5 0 0 1 9.5 14"/>',
	);
	$d = isset( $paths[ $key ] ) ? $paths[ $key ] : '<circle cx="12" cy="12" r="3"/>';
	return '<svg class="spec-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

/**
 * Which silhouette a stored body type gets.
 *
 * Matched on a substring rather than an exact value, because the old plugin stored
 * `Wagon/SUV` and `Pickup Truck` beside `Pickup` and `SUV`, and a table of exact
 * strings is a table that misses the next spelling somebody types. An unrecognised
 * body returns '' and falls back to the generic car — never to nothing.
 */
function das_body_icon_key( $value ) {
	$v = strtolower( (string) $value );
	if ( false !== strpos( $v, 'pickup' ) || false !== strpos( $v, 'truck' ) ) { return 'pickup'; }
	if ( false !== strpos( $v, 'suv' ) || false !== strpos( $v, 'wagon' )
		|| false !== strpos( $v, 'van' ) || false !== strpos( $v, 'crossover' ) ) { return 'suv'; }
	if ( false !== strpos( $v, 'hatch' ) ) { return 'hatch'; }
	if ( false !== strpos( $v, 'sedan' ) || false !== strpos( $v, 'coupe' )
		|| false !== strpos( $v, 'convertible' ) ) { return 'sedan'; }
	return '';
}

/**
 * Vehicle equipment, as the old ThemeMakers plugin stored it.
 *
 * 85 of the imported cars carry a full feature list in the `advanced` meta and
 * nothing had read it since the theme was rebuilt - so a Forester with four-wheel
 * drive and heated seats advertised neither. The map below is iterated in its own
 * order rather than the data's, which is how four-wheel drive comes first on a lot
 * that sells to Alaskans.
 *
 * A value of '1' renders the label; a recognised text value renders its own phrase;
 * anything unrecognised falls back to the label alone, so a raw machine string like
 * `manualorautomaticclimatisation` can never reach the page.
 */
function das_feature_groups() {
	return array(
		'environment' => array(
			'label'  => 'Safety & drivetrain',
			'fields' => array(
				'fourwheeldrive'       => array( 'Four-wheel drive' ),
				'abs'                  => array( 'ABS' ),
				'esp'                  => array( 'Stability control (ESP)' ),
				'tractioncontrol'      => array( 'Traction control' ),
				'airbags'              => array( 'Airbags', array(
					'frontandsideandmoreairbags' => 'Front, side and additional airbags',
					'frontandsideairbags'        => 'Front and side airbags',
					'frontairbags'               => 'Front airbags',
				) ),
				'immobilizer'          => array( 'Immobiliser' ),
				'daytimerunninglights' => array( 'Daytime running lights' ),
				'xenonheadlights'      => array( 'Xenon headlights' ),
				'adaptivelighting'     => array( 'Adaptive headlights' ),
				'foglamp'              => array( 'Fog lights' ),
				'lightsensor'          => array( 'Light sensor' ),
				'roofbars'             => array( 'Roof bars' ),
			),
		),
		'interior' => array(
			'label'  => 'Comfort & interior',
			'fields' => array(
				'electricheatedseats'        => array( 'Heated seats' ),
				'climatecontrol'             => array( 'Air conditioning', array(
					'automaticairconditioning'       => 'Automatic air conditioning',
					'manualorautomaticclimatisation' => 'Air conditioning',
				) ),
				'navigationsystem'           => array( 'Navigation system' ),
				'bluetooth'                  => array( 'Bluetooth' ),
				'cruisecontrol'              => array( 'Cruise control' ),
				'sunroof'                    => array( 'Sunroof' ),
				'parkingsensors'             => array( 'Parking sensors', array(
					'frontandrear' => 'Parking sensors (front and rear)',
					'rear'         => 'Parking sensors (rear)',
					'front'        => 'Parking sensors (front)',
				) ),
				'interiordesign'             => array( 'Upholstery', array(
					'cloth'       => 'Cloth upholstery',
					'fullleather' => 'Full leather upholstery',
					'partleather' => 'Part leather upholstery',
					'alcantara'   => 'Alcantara upholstery',
				) ),
				'numberofseats'              => array( 'Seats', 'count' ),
				'electricseatadjustment'     => array( 'Electric seat adjustment' ),
				'multifunctionsteeringwheel' => array( 'Multifunction steering wheel' ),
				'on_boardcomputer'           => array( 'On-board computer' ),
				'powerassistedsteering'      => array( 'Power steering' ),
				'electricwindows'            => array( 'Electric windows' ),
				'electricsidemirror'         => array( 'Electric mirrors' ),
				'centrallocking'             => array( 'Central locking' ),
				'isofixchildseats'           => array( 'ISOFIX child seat mounts' ),
				'rainsensor'                 => array( 'Rain sensor' ),
				'hands_freekit'              => array( 'Hands-free kit' ),
				'tunerradio'                 => array( 'Radio' ),
				'mp3interface'               => array( 'MP3 interface' ),
				'cdplayer'                   => array( 'CD player' ),
				'start_stopsystem'           => array( 'Start-stop system' ),
				'head_updisplay'             => array( 'Head-up display' ),
				'auxiliaryheating'           => array( 'Auxiliary heating' ),
			),
		),
		'exterior' => array(
			'label'  => 'Exterior',
			'fields' => array(
				'alloywheels'     => array( 'Alloy wheels' ),
				'metallic'        => array( 'Metallic paint' ),
				'panoramicroof'   => array( 'Panoramic roof' ),
				'trailercoupling' => array( 'Tow hitch' ),
				'roofrack'        => array( 'Roof rack' ),
			),
		),
		'extras' => array(
			'label'  => 'Extras',
			'fields' => array(
				'sportspackage'    => array( 'Sports package' ),
				'sportseats'       => array( 'Sports seats' ),
				'sportssuspension' => array( 'Sports suspension' ),
			),
		),
	);
}

/** The `advanced` meta is double-serialised on some rows, hence the second pass. */
function das_car_advanced( $post_id ) {
	$data = maybe_unserialize( get_post_meta( (int) $post_id, 'advanced', true ) );
	if ( is_string( $data ) ) { $data = maybe_unserialize( $data ); }
	return is_array( $data ) ? $data : array();
}

/**
 * The features a buyer would pay extra for, in the order they sell a car here.
 *
 * Carsforsale splits its equipment into a short starred list and a long plain one, and
 * Gianni is right that it reads better: thirty-five checked lines all weighted the same
 * make somebody hunt for the two that matter. Four-wheel drive twentieth in an
 * alphabetical list sells nothing.
 *
 * Near-universal equipment is deliberately absent — Bluetooth, cruise control, alloy
 * wheels, air conditioning. A star on almost everything is a star that means nothing.
 */
function das_key_features() {
	return array(
		array( 'environment', 'fourwheeldrive' ),
		array( 'interior', 'electricheatedseats' ),
		array( 'exterior', 'panoramicroof' ),
		array( 'interior', 'sunroof' ),
		array( 'interior', 'navigationsystem' ),
		array( 'interior', 'interiordesign' ),
		array( 'interior', 'parkingsensors' ),
		array( 'environment', 'xenonheadlights' ),
		array( 'exterior', 'trailercoupling' ),
		array( 'interior', 'head_updisplay' ),
		array( 'extras', 'sportspackage' ),
		array( 'extras', 'sportseats' ),
	);
}

/** One stored value turned into the phrase a person reads, or null if it says nothing. */
function das_feature_label( $spec, $value ) {
	$value = trim( (string) $value );
	if ( '' === $value || '0' === $value ) { return null; }
	$rule = isset( $spec[1] ) ? $spec[1] : null;
	if ( 'count' === $rule ) {
		if ( ! ctype_digit( $value ) || 0 === (int) $value ) { return null; }
		return $value . ' ' . strtolower( $spec[0] );
	}
	if ( is_array( $rule ) ) {
		// An unrecognised machine string falls back to the label alone, so that
		// `manualorautomaticclimatisation` can never reach a public page.
		return isset( $rule[ $value ] ) ? $rule[ $value ] : $spec[0];
	}
	return $spec[0];
}

/**
 * Equipment, as sections: Highlights first, then the groups.
 *
 * A starred feature is removed from its own group rather than repeated there. Printing
 * "Four-wheel drive" twice on one page to make a point about it is how a list stops
 * being read at all — and it would lengthen the left column, which the symmetry pass of
 * 20 August spent a change getting level with the aside.
 *
 * Returns an ordered list rather than a label-keyed map, because the order is a decision
 * (§ Alaska sells four-wheel drive) and a map keyed by a display label is a map that
 * breaks the day two groups are renamed to the same thing.
 */
function das_car_feature_sections( $post_id ) {
	$data = das_car_advanced( $post_id );
	if ( ! $data ) { return array(); }
	$groups = das_feature_groups();

	// The starred set first, and it is removed from what follows rather than repeated.
	$starred = array();
	$premium = array();
	foreach ( das_key_features() as $k ) {
		list( $group, $field ) = $k;
		if ( ! isset( $data[ $group ][ $field ], $groups[ $group ]['fields'][ $field ] ) ) { continue; }
		$label = das_feature_label( $groups[ $group ]['fields'][ $field ], $data[ $group ][ $field ] );
		if ( null === $label ) { continue; }
		$premium[]                        = $label;
		$starred[ $group . '|' . $field ] = true;
	}

	// Everything else, flat, in the order `das_feature_groups()` declares — so related
	// equipment still sits together without the group headings that made the block
	// ragged. The groups are wildly uneven on real vehicles (Comfort 18, Exterior 2),
	// and two columns of blocks that size cannot balance. A flat list can.
	$standard = array();
	foreach ( $groups as $group => $conf ) {
		if ( empty( $data[ $group ] ) || ! is_array( $data[ $group ] ) ) { continue; }
		foreach ( $conf['fields'] as $key => $spec ) {
			if ( ! isset( $data[ $group ][ $key ] ) ) { continue; }
			if ( isset( $starred[ $group . '|' . $key ] ) ) { continue; }
			$label = das_feature_label( $spec, $data[ $group ][ $key ] );
			if ( null !== $label ) { $standard[] = $label; }
		}
	}

	$out = array();
	if ( $premium ) {
		$out[] = array( 'label' => 'Premium features', 'star' => true, 'items' => $premium );
	}
	if ( $standard ) {
		// `clamp` asks the template for a Show-all control. Only this tier gets one:
		// the starred set runs to ten at most and is the half worth reading.
		$out[] = array( 'label' => 'Standard features', 'star' => false, 'items' => $standard, 'clamp' => true );
	}
	return $out;
}

/** Kept for anything still asking for the flat map. */
function das_car_features( $post_id ) {
	$out = array();
	foreach ( das_car_feature_sections( $post_id ) as $section ) {
		$out[ $section['label'] ] = $section['items'];
	}
	return $out;
}

/**
 * The few features worth repeating beside the price. Four-wheel drive and heated
 * seats sell a car in Anchorage; twentieth in an alphabetical list they sell nothing.
 */
function das_car_highlights( $post_id, $limit = 4 ) {
	$data = das_car_advanced( $post_id );
	if ( ! $data ) { return array(); }
	$want = array(
		array( 'environment', 'fourwheeldrive', 'Four-wheel drive' ),
		array( 'interior', 'electricheatedseats', 'Heated seats' ),
		array( 'exterior', 'panoramicroof', 'Panoramic roof' ),
		array( 'interior', 'sunroof', 'Sunroof' ),
		array( 'interior', 'navigationsystem', 'Navigation' ),
		array( 'interior', 'cruisecontrol', 'Cruise control' ),
		array( 'interior', 'bluetooth', 'Bluetooth' ),
	);
	$out = array();
	foreach ( $want as $w ) {
		if ( count( $out ) >= $limit ) { break; }
		$v = isset( $data[ $w[0] ][ $w[1] ] ) ? trim( (string) $data[ $w[0] ][ $w[1] ] ) : '';
		if ( '' === $v || '0' === $v ) { continue; }
		$out[] = $w[2];
	}
	return $out;
}

function das_conditions() {
	return array( 'car_is_used' => 'Used', 'car_is_new' => 'New' );
}

function das_car_condition( $post_id ) {
	$v = trim( (string) get_post_meta( (int) $post_id, 'car_condition', true ) );
	if ( '' === $v ) { return ''; }
	$map = das_conditions();
	return isset( $map[ $v ] ) ? $map[ $v ] : ucfirst( str_replace( array( 'car_is_', '_' ), array( '', ' ' ), $v ) );
}

/**
 * Three vehicles worth looking at next. Scored rather than filtered: a lot of
 * sixteen will not always hold three of the same body type, and an empty
 * "similar vehicles" row is worse than a loosely matched one.
 */
function das_similar_cars( $car, $limit = 3 ) {
	$scored = array();
	foreach ( das_get_cars( array( 'posts_per_page' => 200 ) ) as $o ) {
		if ( (int) $o['id'] === (int) $car['id'] ) { continue; }
		$score = 0;
		if ( $o['body'] && $o['body'] === $car['body'] ) { $score += 3; }
		if ( $o['make'] && $o['make'] === $car['make'] ) { $score += 2; }
		if ( $car['price'] && $o['price'] ) {
			$diff = abs( $o['price'] - $car['price'] ) / $car['price'];
			if ( $diff <= 0.25 ) { $score += 2; } elseif ( $diff <= 0.5 ) { $score += 1; }
		}
		$scored[] = array( 'score' => $score, 'car' => $o );
	}
	usort( $scored, function ( $a, $b ) { return $b['score'] <=> $a['score']; } );
	$out = array();
	foreach ( $scored as $s ) {
		if ( count( $out ) >= $limit ) { break; }
		$out[] = $s['car'];
	}
	return $out;
}

/**
 * Vehicle videos.
 *
 * The old ThemeMakers form had a repeatable video field and the new theme read it nowhere,
 * so the meta sat on 53 vehicles being ignored. Every one of those rows is empty, which is
 * worth knowing before anybody goes looking for lost footage: the box existed, nothing was
 * ever typed into it.
 *
 * The key and the shape are the old ones — `cars_videos`, a flat list of URLs — because
 * changing either would strand whatever somebody does add before the next change.
 *
 * `advanced` is double-serialised on some rows (see das_car_advanced), so this unwraps
 * twice as well, and flattens: the old plugin nested its repeatable rows and a flat list
 * and a nested one both have to come out as URLs.
 */
function das_car_videos( $post_id ) {
	$data = maybe_unserialize( get_post_meta( (int) $post_id, 'cars_videos', true ) );
	if ( is_string( $data ) ) {
		$maybe = maybe_unserialize( $data );
		$data  = is_string( $maybe ) ? array( $maybe ) : $maybe;
	}
	if ( ! is_array( $data ) ) { return array(); }

	$urls = array();
	array_walk_recursive( $data, function ( $value ) use ( &$urls ) {
		$value = trim( (string) $value );
		if ( '' !== $value && ! in_array( $value, $urls, true ) ) { $urls[] = $value; }
	} );
	return $urls;
}

/**
 * The player address for a YouTube or Vimeo link, or '' for anything else.
 *
 * Built by hand rather than through wp_oembed_get(), which makes an HTTP request the first
 * time it sees a URL. A vehicle page that waits on youtube.com to render is a vehicle page
 * that is slow exactly when the network is bad, and oembed answers `false` on a failure —
 * so the video would silently vanish from a listing for a reason nobody could see.
 *
 * Anything unrecognised returns '' and is simply not rendered. A dealership pasting a
 * Facebook link should get no player rather than a broken frame.
 */
function das_video_embed_url( $url ) {
	$url  = trim( (string) $url );
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$host = preg_replace( '/^www\./', '', $host );

	if ( 'youtu.be' === $host ) {
		$id = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	} elseif ( 'youtube.com' === $host || 'm.youtube.com' === $host || 'youtube-nocookie.com' === $host ) {
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $q );
		if ( ! empty( $q['v'] ) ) {
			$id = $q['v'];
		} elseif ( preg_match( '~^/(?:embed|shorts|v|live)/([^/]+)~', $path, $m ) ) {
			$id = $m[1];
		} else {
			$id = '';
		}
	} elseif ( 'vimeo.com' === $host || 'player.vimeo.com' === $host ) {
		// vimeo.com/123456789, and player.vimeo.com/video/123456789
		if ( preg_match( '~/(\d+)~', (string) wp_parse_url( $url, PHP_URL_PATH ), $m ) ) {
			return 'https://player.vimeo.com/video/' . $m[1];
		}
		return '';
	} else {
		return '';
	}

	$id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $id );
	return $id ? 'https://www.youtube.com/embed/' . $id : '';
}

/** The videos on a vehicle that can actually be played, as embed addresses. */
function das_car_video_embeds( $post_id ) {
	$out = array();
	foreach ( das_car_videos( $post_id ) as $url ) {
		$embed = das_video_embed_url( $url );
		if ( $embed && ! in_array( $embed, $out, true ) ) { $out[] = $embed; }
	}
	return $out;
}

/**
 * How many vehicles on the lot carry each body type.
 *
 * Counted through das_get_cars() rather than with a SQL query of its own, so this
 * and the body chips on the inventory page cannot come to disagree: that is the one
 * function which already knows a sold vehicle is out of the grid, and that
 * car_is_sold can be SQL NULL.
 */
function das_body_counts() {
	static $counts = null;
	if ( null !== $counts ) { return $counts; }
	$counts = array();
	foreach ( das_get_cars( array( 'posts_per_page' => -1 ) ) as $c ) {
		$body = (string) $c[ 'body' ];
		if ( '' === $body ) { continue; }
		$counts[ $body ] = isset( $counts[ $body ] ) ? $counts[ $body ] + 1 : 1;
	}
	return $counts;
}

/** The body-type key a menu item filters on, or empty when it is not one of those links. */
function das_menu_item_body( $item ) {
	$url = isset( $item->url ) ? (string) $item->url : '';
	if ( '' === $url || false === strpos( $url, 'body=' ) ) { return ''; }
	$query = (string) wp_parse_url( $url, PHP_URL_QUERY );
	if ( '' === $query ) { return ''; }
	$args = array();
	wp_parse_str( $query, $args );
	$body  = isset( $args[ 'body' ] ) ? sanitize_key( $args[ 'body' ] ) : '';
	$types = das_body_types();
	return isset( $types[ $body ] ) ? $body : '';
}

/**
 * The body-type links in the nav menu count the lot, they do not state a number.
 *
 * Those items carried their counts inside the menu item titles themselves — SUV (7) —
 * so they froze at whatever the lot held on the day somebody typed them, and went on
 * advertising seven SUVs over a lot of three. Same fault as "Three of us, one promise"
 * standing over a roster of four: a number written beside a list is a number that goes
 * stale, and the only cure is deriving it.
 *
 * The label is rebuilt from das_body_types() rather than from the stored title, or
 * SUV (7) would simply become SUV (7) (3). A body type with no vehicles is dropped
 * entirely — the same rule das_filter_options() applies to every other control on this
 * site: one that cannot narrow anything is not shown.
 */
add_filter( 'wp_nav_menu_objects', function ( $items ) {
	if ( is_admin() ) { return $items; }
	$counts = das_body_counts();
	$labels = das_body_types();
	$drop   = array();
	foreach ( $items as $item ) {
		$body = das_menu_item_body( $item );
		if ( '' === $body ) { continue; }
		$n = isset( $counts[ $body ] ) ? (int) $counts[ $body ] : 0;
		if ( 0 === $n ) { $drop[ (int) $item->ID ] = true; continue; }
		$item->title = das_label( $body, $labels ) . ' (' . $n . ')';
	}
	if ( ! $drop ) { return $items; }
	// Never orphan a submenu: an item something surviving hangs off stays, whatever its
	// own count says.
	foreach ( $items as $item ) {
		$parent = (int) $item->menu_item_parent;
		if ( $parent && isset( $drop[ $parent ] ) && ! isset( $drop[ (int) $item->ID ] ) ) {
			unset( $drop[ $parent ] );
		}
	}
	return array_values( array_filter( $items, function ( $item ) use ( $drop ) {
		return ! isset( $drop[ (int) $item->ID ] );
	} ) );
} );
