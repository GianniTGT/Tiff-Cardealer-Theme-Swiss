<?php
/**
 * AutoScout24 → Webseite.
 *
 * Sabit erfasst seine Fahrzeuge heute in AutoScout24. Dieser Import holt
 * die Inserate ueber die AutoScout24-API (DMS API, OpenAPI 1.0.1) und legt
 * sie als Fahrzeuge auf der Webseite an bzw. aktualisiert sie. Anker ist die
 * AutoScout24-ID (Feld «as24_id»); was im Feed fehlt, wird auf «verkauft» gesetzt.
 *
 * STAND: vorbereitet und mit erfundenen Daten getestet (tests/fixtures/as24-demo.json).
 * Vor dem ersten echten Abgleich pruefen, sobald Zugangsdaten da sind:
 *   1. den Pfad der Inseratsliste (Standard unten) gegen die Spezifikation,
 *   2. die Feldnamen in bit_as24_map_listing() gegen eine echte Antwort,
 *   3. zuerst gegen die Preproduktion (api.preprod.autoscout24.dev).
 * Zugangsdaten stehen nur in der Datenbank (Werkzeuge → AutoScout24), nie im Code.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BIT_AS24_OPTION = 'bit_as24_settings';
const BIT_AS24_REPORT = 'bit_as24_last_report';

/** Einstellungen mit Standardwerten. */
function bit_as24_settings() {
	return wp_parse_args(
		get_option( BIT_AS24_OPTION, array() ),
		array(
			'base_url'      => 'https://api.preprod.autoscout24.dev',
			'seller_id'     => '',
			'client_id'     => '',
			'client_secret' => '',
			'list_path'     => '/public/v1/sellers/{sellerId}/listings',
			'auto_sync'     => 0,
		)
	);
}

function bit_as24_is_configured() {
	$s = bit_as24_settings();
	return $s['seller_id'] && $s['client_id'] && $s['client_secret'];
}

/* ====================================================================
 * API-Zugriff
 * ==================================================================== */

/** Bearer-Token holen (24 h gueltig, zwischengespeichert). */
function bit_as24_token() {
	$cached = get_transient( 'bit_as24_token' );
	if ( $cached ) {
		return $cached;
	}
	$s   = bit_as24_settings();
	$res = wp_remote_post(
		untrailingslashit( $s['base_url'] ) . '/public/v1/clients/oauth/token',
		array(
			'timeout' => 20,
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => $s['client_id'],
				'client_secret' => $s['client_secret'],
				'audience'      => 'https://api.autoscout24.ch',
			),
		)
	);
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( 200 !== wp_remote_retrieve_response_code( $res ) || empty( $body['access_token'] ) ) {
		return new WP_Error( 'bit_as24_auth', 'AutoScout24-Anmeldung fehlgeschlagen (HTTP ' . wp_remote_retrieve_response_code( $res ) . ').' );
	}
	$ttl = max( 60, (int) ( $body['expires_in'] ?? 86400 ) - 300 );
	set_transient( 'bit_as24_token', $body['access_token'], $ttl );
	return $body['access_token'];
}

/** Alle Inserate des Haendlers holen (seitenweise). */
function bit_as24_fetch_listings() {
	if ( ! bit_as24_is_configured() ) {
		return new WP_Error( 'bit_as24_config', 'AutoScout24 ist noch nicht eingerichtet (Seller-ID, Client-ID, Secret).' );
	}
	$token = bit_as24_token();
	if ( is_wp_error( $token ) ) {
		return $token;
	}
	$s     = bit_as24_settings();
	$path  = str_replace( '{sellerId}', rawurlencode( $s['seller_id'] ), $s['list_path'] );
	$all   = array();
	$page  = 0;
	do {
		$url = add_query_arg(
			array(
				'page' => $page,
				'size' => 50,
			),
			untrailingslashit( $s['base_url'] ) . $path
		);
		$res = wp_remote_get(
			$url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Accept'        => 'application/json',
				),
			)
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$code = wp_remote_retrieve_response_code( $res );
		if ( 200 !== $code ) {
			return new WP_Error( 'bit_as24_http', 'AutoScout24 antwortet mit HTTP ' . $code . '.' );
		}
		$body  = json_decode( wp_remote_retrieve_body( $res ), true );
		$items = bit_as24_extract_items( $body );
		$all   = array_merge( $all, $items );
		++$page;
		$more = count( $items ) >= 50 && $page < 40;
	} while ( $more );
	return $all;
}

/** Die Liste aus einer Antwort ziehen – als nacktes Array oder unter content/items/listings. */
function bit_as24_extract_items( $body ) {
	if ( ! is_array( $body ) ) {
		return array();
	}
	if ( array_is_list( $body ) ) {
		return $body;
	}
	foreach ( array( 'content', 'items', 'listings', 'data' ) as $key ) {
		if ( isset( $body[ $key ] ) && is_array( $body[ $key ] ) ) {
			return $body[ $key ];
		}
	}
	return array();
}

/* ====================================================================
 * Zuordnung AutoScout24 → Fahrzeugfelder
 * ==================================================================== */

function bit_as24_labels() {
	return array(
		'transmissionType' => array(
			'automatic'           => 'Automat',
			'automatic-stepless'  => 'Automat (stufenlos)',
			'semi-automatic'      => 'Halbautomat',
			'manual'              => 'Schaltgetriebe',
		),
		'fuelType'         => array(
			'petrol'             => 'Benzin',
			'diesel'             => 'Diesel',
			'electric'           => 'Elektro',
			'hydrogen'           => 'Wasserstoff',
			'cng-petrol'         => 'Erdgas (CNG)/Benzin',
			'lpg-petrol'         => 'Flüssiggas (LPG)/Benzin',
			'ethanol-petrol'     => 'Ethanol/Benzin',
			'hev-petrol'         => 'Vollhybrid Benzin',
			'hev-diesel'         => 'Vollhybrid Diesel',
			'mhev-petrol'        => 'Mild-Hybrid Benzin',
			'mhev-diesel'        => 'Mild-Hybrid Diesel',
			'phev-petrol'        => 'Plug-in-Hybrid Benzin',
			'phev-diesel'        => 'Plug-in-Hybrid Diesel',
			'two-stroke-mixture' => 'Zweitakt-Gemisch',
		),
		'driveType'        => array(
			'rear'  => 'Hinterrad',
			'front' => 'Vorderrad',
			'all'   => 'Allrad',
		),
		'bodyColor'        => array(
			'anthracite' => 'Anthrazit', 'beige' => 'Beige', 'black' => 'Schwarz', 'blue' => 'Blau',
			'bordeaux' => 'Bordeaux', 'brown' => 'Braun', 'gray' => 'Grau', 'gold' => 'Gold',
			'green' => 'Grün', 'multicolored' => 'Mehrfarbig', 'orange' => 'Orange', 'pink' => 'Pink',
			'red' => 'Rot', 'silver' => 'Silber', 'turquoise' => 'Türkis', 'violet' => 'Violett',
			'white' => 'Weiss', 'yellow' => 'Gelb', 'other' => 'Andere',
		),
		'warrantyType'     => array(
			'from-date'               => 'Garantie ab Datum',
			'from-delivery'           => 'Garantie ab Übergabe',
			'from-first-registration' => 'Werksgarantie ab 1. Inverkehrsetzung',
			'none'                    => '',
		),
	);
}

/** Ein Wert aus der Liste, sonst der Rohwert. */
function bit_as24_label( $group, $value ) {
	$labels = bit_as24_labels();
	return $labels[ $group ][ $value ] ?? (string) $value;
}

/** Marke als lesbarer Name: «mercedes-benz» → «Mercedes-Benz», «bmw» → «BMW». */
function bit_as24_make_name( $l ) {
	if ( ! empty( $l['makeName'] ) ) {
		return $l['makeName'];
	}
	$key   = (string) ( $l['makeKey'] ?? '' );
	$known = array( 'bmw' => 'BMW', 'vw' => 'VW', 'volkswagen' => 'VW', 'mercedes-benz' => 'Mercedes-Benz', 'mercedes-amg' => 'Mercedes-AMG', 'mini' => 'MINI', 'ds' => 'DS' );
	if ( isset( $known[ $key ] ) ) {
		return $known[ $key ];
	}
	return implode( '-', array_map( 'ucfirst', explode( '-', $key ) ) );
}

/**
 * Ein AutoScout24-Inserat → Titel, Marke, Beschreibung, Felder, Bilder.
 * Gibt null zurueck, wenn das Inserat nicht auf die Seite gehoert.
 */
function bit_as24_map_listing( array $l ) {
	$id = (string) ( $l['id'] ?? $l['listingId'] ?? '' );
	if ( '' === $id ) {
		return null;
	}
	$make  = bit_as24_make_name( $l );
	$model = $l['modelName'] ?? ucfirst( (string) ( $l['modelKey'] ?? '' ) );
	$title = trim( $l['title'] ?? ( $make . ' ' . $model ) );

	// Erstzulassung: Datum «2018-04-12» → «04.2018», Jahr getrennt.
	$first_reg = '';
	$year      = (int) ( $l['firstRegistrationYear'] ?? 0 );
	if ( ! empty( $l['firstRegistrationDate'] ) && preg_match( '/^(\d{4})-(\d{2})/', $l['firstRegistrationDate'], $m ) ) {
		$first_reg = $m[2] . '.' . $m[1];
		$year      = $year ? $year : (int) $m[1];
	}

	$power = '';
	if ( ! empty( $l['horsePower'] ) ) {
		$power = (int) $l['horsePower'] . ' PS';
	} elseif ( ! empty( $l['kiloWatts'] ) ) {
		$power = (int) round( $l['kiloWatts'] * 1.35962 ) . ' PS';
	}

	$consumption = '';
	if ( isset( $l['consumptionCombined'] ) && '' !== $l['consumptionCombined'] ) {
		$consumption = str_replace( '.', ',', (string) round( (float) $l['consumptionCombined'], 1 ) ) . ' l/100 km';
	}

	$colour = trim( (string) ( $l['bodyColorText'] ?? '' ) );
	if ( '' === $colour && ! empty( $l['bodyColor'] ) ) {
		$colour = bit_as24_label( 'bodyColor', $l['bodyColor'] );
	}

	$warranty = '';
	if ( ! empty( $l['warrantyType'] ) && 'none' !== $l['warrantyType'] ) {
		$warranty = bit_as24_label( 'warrantyType', $l['warrantyType'] );
		if ( ! empty( $l['warrantyDuration'] ) ) {
			$warranty .= ', ' . (int) $l['warrantyDuration'] . ' Monate';
		}
	}

	// Bilder: Liste von URLs oder Objekten mit url/uri/key.
	$images = array();
	foreach ( (array) ( $l['images'] ?? array() ) as $img ) {
		$src = is_array( $img ) ? ( $img['url'] ?? $img['uri'] ?? $img['key'] ?? '' ) : (string) $img;
		if ( $src ) {
			$images[] = $src;
		}
	}

	$status = 'verfuegbar';
	$state  = strtolower( (string) ( $l['state'] ?? $l['status'] ?? 'active' ) );
	if ( in_array( $state, array( 'inactive', 'deactivated', 'removed', 'sold' ), true ) ) {
		$status = 'verkauft';
	} elseif ( 'reserved' === $state ) {
		$status = 'reserviert';
	}

	return array(
		'as24_id'     => $id,
		'title'       => $title,
		'brand'       => $make,
		'description' => (string) ( $l['description'] ?? '' ),
		'images'      => $images,
		'fields'      => array(
			'price'       => isset( $l['price'] ) ? (string) (int) $l['price'] : '',
			'subtitle'    => (string) ( $l['versionFullName'] ?? $l['subtitle'] ?? '' ),
			'year'        => $year ? (string) $year : '',
			'first_reg'   => $first_reg,
			'km'          => isset( $l['mileage'] ) ? (string) (int) $l['mileage'] : '',
			'power'       => $power,
			'gearbox'     => ! empty( $l['transmissionText'] ) ? $l['transmissionText'] : ( ! empty( $l['transmissionType'] ) ? bit_as24_label( 'transmissionType', $l['transmissionType'] ) : '' ),
			'drive'       => ! empty( $l['driveType'] ) ? bit_as24_label( 'driveType', $l['driveType'] ) : '',
			'fuel'        => ! empty( $l['fuelType'] ) ? bit_as24_label( 'fuelType', $l['fuelType'] ) : '',
			'consumption' => $consumption,
			'doors'       => isset( $l['doors'] ) ? (string) (int) $l['doors'] : '',
			'seats'       => isset( $l['seats'] ) ? (string) (int) $l['seats'] : '',
			'colour'      => $colour,
			'stamm'       => '', // Stammnummer bewusst nicht uebernehmen; oeffentlich nur, wenn Sabit sie selbst eintraegt.
			'mfk'         => ! empty( $l['inspected'] ) ? '1' : '0',
			'warranty'    => $warranty,
			'status'      => $status,
		),
	);
}

/* ====================================================================
 * Import
 * ==================================================================== */

/** Fahrzeug zur AutoScout24-ID finden. */
function bit_as24_find_post( $as24_id ) {
	$ids = get_posts(
		array(
			'post_type'      => 'fahrzeug',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_bit_as24_id', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => (string) $as24_id, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Bild in die Mediathek holen (einmal pro Quelle).
 * «theme:img/x.jpg» nimmt eine Datei aus dem Theme (Testdaten), sonst Download.
 */
function bit_as24_attach_image( $src, $post_id, $demo = false ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_bit_src', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $src, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	if ( str_starts_with( $src, 'theme:' ) ) {
		$file = BIT_DIR . '/assets/' . ltrim( substr( $src, 6 ), '/' );
		if ( ! is_readable( $file ) ) {
			return new WP_Error( 'bit_img', 'Bild fehlt: ' . $src );
		}
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );
		$name = basename( $file );
	} else {
		$tmp = download_url( $src, 30 );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}
		$name = basename( (string) wp_parse_url( $src, PHP_URL_PATH ) );
		if ( ! preg_match( '/\.(jpe?g|png|gif|webp)$/i', $name ) ) {
			$name .= '.jpg';
		}
	}
	$att = media_handle_sideload(
		array(
			'name'     => $name,
			'tmp_name' => $tmp,
		),
		$post_id
	);
	if ( is_wp_error( $att ) ) {
		wp_delete_file( $tmp );
		return $att;
	}
	update_post_meta( $att, '_bit_src', $src );
	if ( $demo ) {
		update_post_meta( $att, '_bit_demo', '1' );
	}
	return (int) $att;
}

/**
 * Inserate abgleichen.
 *
 * @param array $listings Rohdaten von AutoScout24.
 * @param array $opts     dry_run (nichts schreiben), demo (als Testdaten markieren),
 *                        mark_missing (fehlende auf «verkauft»), images (Bilder holen).
 * @return array Bericht: created, updated, unchanged, sold, skipped, errors, log.
 */
function bit_as24_import( array $listings, array $opts = array() ) {
	$opts   = wp_parse_args(
		$opts,
		array(
			'dry_run'      => false,
			'demo'         => false,
			'mark_missing' => true,
			'images'       => true,
		)
	);
	$report = array(
		'created'   => 0,
		'updated'   => 0,
		'unchanged' => 0,
		'sold'      => 0,
		'skipped'   => 0,
		'errors'    => array(),
		'log'       => array(),
		'time'      => current_time( 'mysql' ),
		'dry_run'   => (bool) $opts['dry_run'],
	);
	$seen = array();

	foreach ( $listings as $raw ) {
		$item = is_array( $raw ) ? bit_as24_map_listing( $raw ) : null;
		if ( ! $item ) {
			++$report['skipped'];
			continue;
		}
		$seen[]  = $item['as24_id'];
		$post_id = bit_as24_find_post( $item['as24_id'] );

		// Gleich geblieben? Pruefsumme ueber alle Daten.
		$hash = md5( wp_json_encode( $item ) );
		if ( $post_id && get_post_meta( $post_id, '_bit_as24_hash', true ) === $hash ) {
			++$report['unchanged'];
			continue;
		}
		if ( $opts['dry_run'] ) {
			$report[ $post_id ? 'updated' : 'created' ]++;
			$report['log'][] = ( $post_id ? 'würde ändern: ' : 'würde anlegen: ' ) . $item['title'];
			continue;
		}

		$postarr = array(
			'post_type'    => 'fahrzeug',
			'post_title'   => $item['title'],
			'post_content' => wp_kses_post( wpautop( $item['description'] ) ),
			'post_status'  => 'publish',
		);
		if ( $post_id ) {
			$postarr['ID'] = $post_id;
			$result        = wp_update_post( $postarr, true );
		} else {
			$result = wp_insert_post( $postarr, true );
		}
		if ( is_wp_error( $result ) ) {
			$report['errors'][] = $item['title'] . ': ' . $result->get_error_message();
			continue;
		}
		$is_new  = ! $post_id;
		$post_id = (int) $result;

		foreach ( $item['fields'] as $key => $value ) {
			// Stammnummer und Markierung gehoeren Sabit – der Import ueberschreibt sie nicht.
			if ( in_array( $key, array( 'stamm' ), true ) && ! $is_new ) {
				continue;
			}
			update_post_meta( $post_id, '_bit_' . $key, $value );
		}
		update_post_meta( $post_id, '_bit_as24_id', $item['as24_id'] );
		update_post_meta( $post_id, '_bit_as24_hash', $hash );
		if ( $opts['demo'] ) {
			update_post_meta( $post_id, '_bit_demo', '1' );
		}
		if ( $item['brand'] ) {
			wp_set_object_terms( $post_id, $item['brand'], 'marke' );
		}

		if ( $opts['images'] && $item['images'] ) {
			$att_ids = array();
			foreach ( $item['images'] as $src ) {
				$att = bit_as24_attach_image( $src, $post_id, $opts['demo'] );
				if ( is_wp_error( $att ) ) {
					$report['errors'][] = $item['title'] . ': ' . $att->get_error_message();
					continue;
				}
				$att_ids[] = $att;
			}
			if ( $att_ids ) {
				set_post_thumbnail( $post_id, $att_ids[0] );
				update_post_meta( $post_id, '_bit_gallery', implode( ',', array_slice( $att_ids, 1 ) ) );
			}
		}

		$report[ $is_new ? 'created' : 'updated' ]++;
		$report['log'][] = ( $is_new ? 'angelegt: ' : 'geändert: ' ) . $item['title'];
	}

	// Was nicht mehr bei AutoScout24 ist, ist verkauft (bleibt als Seite erreichbar).
	if ( $opts['mark_missing'] ) {
		$linked = get_posts(
			array(
				'post_type'      => 'fahrzeug',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => '_bit_as24_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);
		foreach ( $linked as $pid ) {
			$aid = (string) get_post_meta( $pid, '_bit_as24_id', true );
			if ( '' === $aid || in_array( $aid, $seen, true ) ) {
				continue;
			}
			// Demo-Daten und echte Inserate getrennt halten: ein echter Abgleich
			// fasst keine Demo-Fahrzeuge an und umgekehrt.
			if ( (bool) get_post_meta( $pid, '_bit_demo', true ) !== (bool) $opts['demo'] ) {
				continue;
			}
			if ( 'verkauft' === get_post_meta( $pid, '_bit_status', true ) ) {
				continue;
			}
			if ( ! $opts['dry_run'] ) {
				update_post_meta( $pid, '_bit_status', 'verkauft' );
				delete_post_meta( $pid, '_bit_as24_hash' );
			}
			++$report['sold'];
			$report['log'][] = 'verkauft (nicht mehr bei AutoScout24): ' . get_the_title( $pid );
		}
	}

	if ( ! $opts['dry_run'] && ! $opts['demo'] ) {
		update_option( BIT_AS24_REPORT, $report, false );
	}
	return $report;
}

/** Echter Abgleich: holen und importieren. */
function bit_as24_sync() {
	$listings = bit_as24_fetch_listings();
	if ( is_wp_error( $listings ) ) {
		$report = array(
			'time'   => current_time( 'mysql' ),
			'errors' => array( $listings->get_error_message() ),
		);
		update_option( BIT_AS24_REPORT, $report, false );
		return $report;
	}
	return bit_as24_import( $listings );
}

/* ---------- Automatischer Abgleich stuendlich (wenn eingeschaltet) ---------- */
add_action( 'bit_as24_cron', 'bit_as24_sync' );
add_action(
	'init',
	function () {
		$on        = bit_as24_settings()['auto_sync'] && bit_as24_is_configured();
		$scheduled = wp_next_scheduled( 'bit_as24_cron' );
		if ( $on && ! $scheduled ) {
			wp_schedule_event( time() + 300, 'hourly', 'bit_as24_cron' );
		} elseif ( ! $on && $scheduled ) {
			wp_unschedule_event( $scheduled, 'bit_as24_cron' );
		}
	}
);

/* ====================================================================
 * Einstellungsseite: Werkzeuge → AutoScout24
 * ==================================================================== */
add_action(
	'admin_menu',
	function () {
		add_management_page( 'AutoScout24', 'AutoScout24', 'manage_options', 'bit-as24', 'bit_as24_page' );
	}
);

function bit_as24_page() {
	$s      = bit_as24_settings();
	$report = get_option( BIT_AS24_REPORT );
	?>
	<div class="wrap">
		<h1>AutoScout24</h1>
		<p>Holt die Inserate von AutoScout24 und zeigt sie als Fahrzeuge auf der Webseite. Anker ist die AutoScout24-ID; was dort verschwindet, wird hier auf «verkauft» gesetzt.</p>
		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore ?>
			<div class="notice notice-success"><p>Gespeichert.</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="bit_as24_save">
			<?php wp_nonce_field( 'bit_as24_save' ); ?>
			<table class="form-table" role="presentation">
				<tr><th><label for="as24_base">Server</label></th><td>
					<select id="as24_base" name="base_url">
						<option value="https://api.preprod.autoscout24.dev" <?php selected( $s['base_url'], 'https://api.preprod.autoscout24.dev' ); ?>>Preproduktion (Test)</option>
						<option value="https://api.autoscout24.ch" <?php selected( $s['base_url'], 'https://api.autoscout24.ch' ); ?>>Produktion</option>
					</select></td></tr>
				<tr><th><label for="as24_seller">Seller-ID</label></th><td><input class="regular-text" id="as24_seller" name="seller_id" value="<?php echo esc_attr( $s['seller_id'] ); ?>"></td></tr>
				<tr><th><label for="as24_client">Client-ID</label></th><td><input class="regular-text" id="as24_client" name="client_id" value="<?php echo esc_attr( $s['client_id'] ); ?>" autocomplete="off"></td></tr>
				<tr><th><label for="as24_secret">Client-Secret</label></th><td><input class="regular-text" type="password" id="as24_secret" name="client_secret" value="" placeholder="<?php echo $s['client_secret'] ? '•••••••• (gespeichert)' : ''; ?>" autocomplete="new-password"><p class="description">Leer lassen = unverändert.</p></td></tr>
				<tr><th><label for="as24_path">Pfad Inseratsliste</label></th><td><input class="regular-text code" id="as24_path" name="list_path" value="<?php echo esc_attr( $s['list_path'] ); ?>"><p class="description">Gegen die Spezifikation prüfen, bevor es live geht.</p></td></tr>
				<tr><th>Automatisch</th><td><label><input type="checkbox" name="auto_sync" value="1" <?php checked( $s['auto_sync'] ); ?>> Stündlich abgleichen</label></td></tr>
			</table>
			<?php submit_button( 'Speichern' ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="bit_as24_run">
			<?php wp_nonce_field( 'bit_as24_run' ); ?>
			<?php submit_button( 'Jetzt abgleichen', 'secondary', 'submit', false, bit_as24_is_configured() ? array() : array( 'disabled' => 'disabled' ) ); ?>
		</form>
		<?php if ( $report ) : ?>
			<h2>Letzter Abgleich</h2>
			<p><?php echo esc_html( $report['time'] ?? '' ); ?> –
				<?php printf( 'neu %d, geändert %d, gleich %d, verkauft %d', (int) ( $report['created'] ?? 0 ), (int) ( $report['updated'] ?? 0 ), (int) ( $report['unchanged'] ?? 0 ), (int) ( $report['sold'] ?? 0 ) ); ?></p>
			<?php foreach ( (array) ( $report['errors'] ?? array() ) as $err ) : ?>
				<p style="color:#b32d2e"><?php echo esc_html( $err ); ?></p>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php
}

add_action(
	'admin_post_bit_as24_save',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Keine Berechtigung.' );
		}
		check_admin_referer( 'bit_as24_save' );
		$s = bit_as24_settings();
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput
		$s['base_url']  = in_array( $_POST['base_url'] ?? '', array( 'https://api.preprod.autoscout24.dev', 'https://api.autoscout24.ch' ), true ) ? $_POST['base_url'] : $s['base_url'];
		$s['seller_id'] = sanitize_text_field( wp_unslash( $_POST['seller_id'] ?? '' ) );
		$s['client_id'] = sanitize_text_field( wp_unslash( $_POST['client_id'] ?? '' ) );
		$secret         = trim( (string) wp_unslash( $_POST['client_secret'] ?? '' ) );
		if ( '' !== $secret ) {
			$s['client_secret'] = $secret;
		}
		$path           = sanitize_text_field( wp_unslash( $_POST['list_path'] ?? '' ) );
		$s['list_path'] = str_starts_with( $path, '/' ) ? $path : $s['list_path'];
		$s['auto_sync'] = empty( $_POST['auto_sync'] ) ? 0 : 1;
		// phpcs:enable
		update_option( BIT_AS24_OPTION, $s, false );
		delete_transient( 'bit_as24_token' );
		wp_safe_redirect( admin_url( 'tools.php?page=bit-as24&saved=1' ) );
		exit;
	}
);

add_action(
	'admin_post_bit_as24_run',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Keine Berechtigung.' );
		}
		check_admin_referer( 'bit_as24_run' );
		bit_as24_sync();
		wp_safe_redirect( admin_url( 'tools.php?page=bit-as24' ) );
		exit;
	}
);
