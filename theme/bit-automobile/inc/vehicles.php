<?php
/**
 * Fahrzeuge: Beitragstyp, Marken, Felder und Hilfsfunktionen.
 *
 * Adressen: /fahrzeuge/ (Liste) und /fahrzeuge/<name>/ (Detail).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Alle Fahrzeugfelder an einer Stelle: Schluessel => [Beschriftung, Typ, Hilfe]. */
function bit_vehicle_fields() {
	return array(
		'price'       => array( 'Preis (CHF)', 'number', 'Ganze Franken, ohne Apostroph. Leer = «Preis auf Anfrage».' ),
		'subtitle'    => array( 'Untertitel', 'text', 'z.B. «Roadster · V8 Biturbo»' ),
		'year'        => array( 'Jahrgang', 'number', '' ),
		'first_reg'   => array( '1. Inverkehrsetzung', 'text', 'MM.JJJJ, z.B. 04.2018' ),
		'km'          => array( 'Kilometer', 'number', 'Ganze Zahl, ohne Apostroph.' ),
		'power'       => array( 'Leistung', 'text', 'z.B. «557 PS»' ),
		'gearbox'     => array( 'Getriebe', 'text', 'z.B. «Automat» oder «PDK»' ),
		'drive'       => array( 'Antrieb', 'text', 'Vorderrad, Hinterrad oder Allrad' ),
		'fuel'        => array( 'Treibstoff', 'text', 'Benzin, Diesel, Hybrid, Elektro …' ),
		'consumption' => array( 'Verbrauch', 'text', 'z.B. «8,4 l/100 km»' ),
		'doors'       => array( 'Türen', 'number', '' ),
		'seats'       => array( 'Plätze', 'number', '' ),
		'colour'      => array( 'Farbe', 'text', '' ),
		'stamm'       => array( 'Stammnummer', 'text', 'Optional. Wird öffentlich angezeigt, wenn ausgefüllt.' ),
		'mfk'         => array( 'Ab MFK', 'checkbox', 'Fahrzeug wird ab MFK verkauft.' ),
		'warranty'    => array( 'Garantie', 'text', 'z.B. «Garantieschein 12 Monate»' ),
		'flag'        => array( 'Markierung auf dem Foto', 'text', 'z.B. «Neu». Leer = keine.' ),
		'status'      => array( 'Status', 'select', '' ),
		'gallery'     => array( 'Weitere Bilder', 'gallery', 'Das Beitragsbild ist das Hauptbild.' ),
		'as24_id'     => array( 'AutoScout24-ID', 'text', 'Wird vom Import gesetzt.' ),
	);
}

/** Status-Werte. */
function bit_vehicle_statuses() {
	return array(
		'verfuegbar' => 'Verfügbar',
		'reserviert' => 'Reserviert',
		'verkauft'   => 'Verkauft',
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			'fahrzeug',
			array(
				'labels'        => array(
					'name'               => 'Fahrzeuge',
					'singular_name'      => 'Fahrzeug',
					'add_new'            => 'Neues Fahrzeug',
					'add_new_item'       => 'Neues Fahrzeug erfassen',
					'edit_item'          => 'Fahrzeug bearbeiten',
					'new_item'           => 'Neues Fahrzeug',
					'view_item'          => 'Fahrzeug ansehen',
					'search_items'       => 'Fahrzeuge suchen',
					'not_found'          => 'Keine Fahrzeuge gefunden',
					'not_found_in_trash' => 'Keine Fahrzeuge im Papierkorb',
					'all_items'          => 'Alle Fahrzeuge',
					'menu_name'          => 'Fahrzeuge',
					'featured_image'     => 'Hauptbild',
					'set_featured_image' => 'Hauptbild festlegen',
				),
				'public'        => true,
				'has_archive'   => 'fahrzeuge',
				'rewrite'       => array(
					'slug'       => 'fahrzeuge',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-car',
				'menu_position' => 4,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'revisions' ),
				'show_in_rest'  => true,
			)
		);

		register_taxonomy(
			'marke',
			'fahrzeug',
			array(
				'labels'            => array(
					'name'          => 'Marken',
					'singular_name' => 'Marke',
					'add_new_item'  => 'Neue Marke',
					'search_items'  => 'Marken suchen',
					'all_items'     => 'Alle Marken',
					'menu_name'     => 'Marken',
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'marke' ),
			)
		);

		foreach ( array_keys( bit_vehicle_fields() ) as $key ) {
			register_post_meta(
				'fahrzeug',
				'_bit_' . $key,
				array(
					'single'        => true,
					'show_in_rest'  => false,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
);

/** Liste: alle verfuegbaren und reservierten Fahrzeuge, neueste zuerst, ohne Seiten. */
add_action(
	'pre_get_posts',
	function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->is_post_type_archive( 'fahrzeug' ) || $q->is_tax( 'marke' ) ) {
			$q->set( 'posts_per_page', -1 );
			$q->set( 'meta_query', bit_available_meta_query() );
		}
	}
);

/** Meta-Abfrage: nicht verkauft. */
function bit_available_meta_query() {
	return array(
		'relation' => 'OR',
		array(
			'key'     => '_bit_status',
			'value'   => 'verkauft',
			'compare' => '!=',
		),
		array(
			'key'     => '_bit_status',
			'compare' => 'NOT EXISTS',
		),
	);
}

/** Verfuegbare Fahrzeuge fuer Startseite und «Ähnlich». */
function bit_get_vehicles( $args = array() ) {
	$defaults = array(
		'post_type'      => 'fahrzeug',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => bit_available_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery
		'no_found_rows'  => true,
	);
	return get_posts( wp_parse_args( $args, $defaults ) );
}

/** Anzahl der verfuegbaren Fahrzeuge (fuer «Der Platz»). */
function bit_vehicle_count() {
	return count( bit_get_vehicles( array( 'fields' => 'ids' ) ) );
}

/** Ein Feldwert. */
function bit_meta( $post_id, $key ) {
	return get_post_meta( $post_id, '_bit_' . $key, true );
}

/** Schweizer Zahl: 129900 → 129’900. */
function bit_num( $n ) {
	if ( '' === $n || null === $n ) {
		return '';
	}
	return number_format( (float) $n, 0, '.', '’' );
}

/** CHF 129’900 oder «Preis auf Anfrage». */
function bit_chf( $n ) {
	return ( '' === $n || null === $n || 0 === (int) $n ) ? 'Preis auf Anfrage' : 'CHF ' . bit_num( $n );
}

/** Alles, was die Vorlagen ueber ein Fahrzeug brauchen, in einem Array. */
function bit_vehicle( $post_id ) {
	$post  = get_post( $post_id );
	$brand = get_the_terms( $post_id, 'marke' );
	$v     = array(
		'id'     => $post_id,
		'title'  => get_the_title( $post ),
		'url'    => get_permalink( $post ),
		'brand'  => ( $brand && ! is_wp_error( $brand ) ) ? $brand[0]->name : '',
		'brand_slug' => ( $brand && ! is_wp_error( $brand ) ) ? $brand[0]->slug : '',
		'demo'   => (bool) bit_meta( $post_id, 'demo' ),
	);
	foreach ( array_keys( bit_vehicle_fields() ) as $key ) {
		$v[ $key ] = bit_meta( $post_id, $key );
	}
	$v['status']    = $v['status'] ? $v['status'] : 'verfuegbar';
	$v['price_txt'] = bit_chf( $v['price'] );
	$v['km_txt']    = '' !== $v['km'] ? bit_num( $v['km'] ) . ' km' : '';

	$images = array();
	if ( has_post_thumbnail( $post_id ) ) {
		$images[] = (int) get_post_thumbnail_id( $post_id );
	}
	foreach ( array_filter( array_map( 'intval', explode( ',', (string) $v['gallery'] ) ) ) as $att ) {
		if ( ! in_array( $att, $images, true ) ) {
			$images[] = $att;
		}
	}
	$v['images'] = $images;
	return $v;
}

/** Ein Satz ueber das Fahrzeug (Meta-Beschreibung). */
function bit_vehicle_summary( $post_id ) {
	$v     = bit_vehicle( $post_id );
	$parts = array_filter( array( $v['title'], $v['subtitle'], $v['year'], $v['km_txt'], $v['power'], $v['price_txt'] ) );
	return implode( ' · ', $parts ) . ' – BIT Automobile, Oberwangen b. Bern.';
}

/** Alle Marken, die gerade ein Fahrzeug auf dem Platz haben. */
function bit_active_brands() {
	$ids = bit_get_vehicles( array( 'fields' => 'ids' ) );
	if ( ! $ids ) {
		return array();
	}
	$terms = wp_get_object_terms( $ids, 'marke', array( 'orderby' => 'name' ) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/** Gibt es noch Demo-Fahrzeuge (erfundene Testdaten)? */
function bit_has_demo_vehicles() {
	$ids = get_posts(
		array(
			'post_type'      => 'fahrzeug',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_bit_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return ! empty( $ids );
}

/** Strukturierte Daten fuer Suchmaschinen (schema.org Car). */
add_action(
	'wp_head',
	function () {
		if ( ! is_singular( 'fahrzeug' ) ) {
			return;
		}
		$v    = bit_vehicle( get_the_ID() );
		$data = array(
			'@context'     => 'https://schema.org',
			'@type'        => 'Car',
			'name'         => $v['title'],
			'brand'        => $v['brand'] ? array( '@type' => 'Brand', 'name' => $v['brand'] ) : null,
			'vehicleModelDate' => $v['year'] ? (string) $v['year'] : null,
			'mileageFromOdometer' => $v['km'] ? array( '@type' => 'QuantitativeValue', 'value' => (int) $v['km'], 'unitCode' => 'KMT' ) : null,
			'fuelType'     => $v['fuel'] ? $v['fuel'] : null,
			'color'        => $v['colour'] ? $v['colour'] : null,
			'image'        => $v['images'] ? wp_get_attachment_image_url( $v['images'][0], 'bit-large' ) : null,
			'offers'       => $v['price'] ? array(
				'@type'         => 'Offer',
				'price'         => (int) $v['price'],
				'priceCurrency' => 'CHF',
				'availability'  => 'verkauft' === $v['status'] ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
				'seller'        => array( '@type' => 'AutoDealer', 'name' => bit_info( 'brand' ) ),
			) : null,
		);
		echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $data ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
);
